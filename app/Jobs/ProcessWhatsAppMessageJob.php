<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use App\Services\GrupService;
use Illuminate\Support\Facades\Log;

use App\Traits\TenantAwareJob;

class ProcessWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public $tries = 3; // Retry up to 3 times
    public $timeout = 120; // Allow 2 minutes for processing

    protected $tipe;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tipe, $data)
    {
        $this->tipe = $tipe;
        $this->data = $data;
        $this->initializeTenantContext();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $wa, GrupService $grup)
    {
        $this->restoreTenantContext();
        try {
            if ($this->tipe === 'grup') {
                $grup->prosesPesanGrup(
                    $this->data['nomor'] ?? '',
                    $this->data['member'] ?? '',
                    $this->data['pesan'] ?? '',
                    $this->data['nama'] ?? 'User',
                    $this->data['is_admin'] ?? false
                );
            } else {
                $wa->prosesPesan(
                    $this->data['nomor'] ?? '',
                    $this->data['pesan'] ?? '',
                    $this->data['mediaUrl'] ?? null,
                    $this->data['mediaType'] ?? null,
                    $this->data['deviceId'] ?? null
                );
            }
        } catch (\Exception $e) {
            Log::error("ProcessWhatsAppMessageJob Failed: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry/failed job
        } finally {
            $this->forgetTenantContext();
        }
    }
}
