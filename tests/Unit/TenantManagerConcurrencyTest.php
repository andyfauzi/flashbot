<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantManagerConcurrencyTest extends TestCase
{
    /**
     * Uji apakah mekanisme per-request scoping berfungsi.
     * Simulasi di mana dua tenant disetup dan dipastikan bahwa set tenant A tidak mempengaruhi koneksi yang di-return untuk tenant A meskipun tenant B di-set secara paralel di wadah IoC lain.
     * Karena test berjalan dalam proses tunggal, kita menguji apakah context tersimpan dengan benar di instance container request tersebut.
     */
    public function test_tenant_context_is_scoped_correctly()
    {
        // 1. Buat mock tenant A
        $tenantA = new Tenant();
        $tenantA->id = 1;
        $tenantA->name = 'Tenant A';
        $tenantA->subdomain = 'tenanta';
        $tenantA->database_name = 'tenant_db_a';

        // 2. Buat mock tenant B
        $tenantB = new Tenant();
        $tenantB->id = 2;
        $tenantB->name = 'Tenant B';
        $tenantB->subdomain = 'tenantb';
        $tenantB->database_name = 'tenant_db_b';

        // 3. Simulasikan switchTo tenant A
        TenantManager::switchTo($tenantA);
        
        $connA = TenantManager::getTenantConnection();
        $this->assertEquals('tenant_1', $connA);
        $this->assertEquals('tenant_db_a', config("database.connections.{$connA}.database"));
        $this->assertEquals($tenantA, TenantManager::current());

        // 4. Di dalam flow aplikasi, setiap request akan memulai instance baru di IoC.
        // Simulasi request B dengan meng-overwrite state (seolah request lain berjalan di thread yang sama pada environment non-FPM).
        // Pada kenyataannya, app() pada PHP-FPM terisolasi. Kita pastikan saja logika switchTo benar membuat koneksi bernama unik.
        TenantManager::switchTo($tenantB);
        
        $connB = TenantManager::getTenantConnection();
        $this->assertEquals('tenant_2', $connB);
        $this->assertEquals('tenant_db_b', config("database.connections.{$connB}.database"));
        $this->assertEquals($tenantB, TenantManager::current());

        // Pastikan konfigurasi koneksi A tidak terhapus / terpengaruh oleh config koneksi B
        // Ini memastikan bahwa di memori worker, koneksi A masih menunjuk ke DB A
        $this->assertEquals('tenant_db_a', config("database.connections.tenant_1.database"));
        $this->assertEquals('tenant_db_b', config("database.connections.tenant_2.database"));
        
        // 5. Test forgetTenant() membersihkan koneksi
        TenantManager::forgetTenant();
        $this->assertFalse(TenantManager::hasTenant());
        $this->assertNull(config('database.connections.tenant_2')); // Harus dibersihkan dari config
    }

    /**
     * Memastikan Exception dilempar (fail-loud) jika model mencoba mengambil connection tanpa context.
     */
    public function test_fails_loudly_without_tenant_context()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tenant context belum diinisialisasi');

        TenantManager::switchToLandlord(); // Bersihkan context
        
        TenantManager::getTenantConnection(); // Harus melempar exception
    }
}
