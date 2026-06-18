<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Tenant;

class PortalTenantValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'landlord'];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_portal_index_returns_404_when_tenant_not_found()
    {
        $response = $this->get('http://nonexistent.localhost/portal');

        $response->assertStatus(404);
        $response->assertSee('Toko ini tidak terdaftar di Flashbot.');
    }

    public function test_portal_store_returns_404_when_tenant_not_found()
    {
        $response = $this->postJson('http://nonexistent.localhost/portal/order', [
            'nama_penerima' => 'John',
            'cart' => []
        ]);

        $response->assertStatus(404);
        $response->assertSee('Toko ini tidak terdaftar di Flashbot.');
    }

    public function test_portal_returns_403_when_tenant_inactive()
    {
        // Clean up any left-over tenant from previous failed runs before starting
        Tenant::where('subdomain', 'inaktif')->delete();

        // Create an inactive tenant
        Tenant::create([
            'name' => 'Toko Inaktif',
            'subdomain' => 'inaktif',
            'database_name' => 'tenant_inaktif',
            'plan' => 'starter',
            'is_active' => false,
        ]);

        $response = $this->get('http://inaktif.localhost/portal');

        if ($response->status() !== 403) {
            $response->dump();
        }

        $response->assertStatus(403);
        $response->assertSee('Langganan toko ini sedang tidak aktif. Hubungi pemilik toko.');
    }
}
