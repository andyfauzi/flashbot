<?php

namespace App\Jobs;

use App\Models\Reservasi;
use App\Models\IdentitasToko;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\TenantAwareJob;

class ExpireReservationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public $reservasiId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($reservasiId)
    {
        $this->reservasiId = $reservasiId;
        $this->initializeTenantContext();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->restoreTenantContext();

        try {
            $reservasi = Reservasi::find($this->reservasiId);

            if (!$reservasi) {
                return;
            }

            if ($reservasi->isExpired()) {
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
            }
        } finally {
            $this->forgetTenantContext();
        }
    }
}
