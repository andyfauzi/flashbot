<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Reservasi;
use App\Models\IdentitasToko;
use App\Jobs\SendWhatsAppMessageJob;
use App\Services\TenantManager;

class CleanExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan reservasi on_hold yang sudah melewati batas waktu hold_expires_at.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            TenantManager::switchTo($tenant);

            $expiredReservations = Reservasi::where('status', 'on_hold')
                ->where('hold_expires_at', '<', now())
                ->get();

            foreach ($expiredReservations as $reservasi) {
                $reservasi->update(['status' => 'kedaluwarsa']);
                
                if ($reservasi->meja) {
                    $reservasi->meja->update(['status' => 'tersedia']);
                }

                $identitas = IdentitasToko::first();
                $namaToko = $identitas ? $identitas->nama_toko : 'Toko Kami';

                $pesan = "Maaf {$reservasi->nama_pelanggan}, reservasi Anda di {$namaToko} telah kedaluwarsa karena belum mendapat konfirmasi dari pihak toko dalam batas waktu yang ditentukan. Silakan hubungi kami langsung jika Anda membutuhkan bantuan.";
                
                SendWhatsAppMessageJob::dispatch(
                    $reservasi->nomor_telepon,
                    $pesan
                );

                $this->info("Reservasi ID {$reservasi->id} di Tenant {$tenant->subdomain} telah di-set kedaluwarsa.");
            }

            TenantManager::forgetTenant();
        }

        return 0;
    }
}
