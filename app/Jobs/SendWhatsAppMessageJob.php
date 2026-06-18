<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Exception;
use App\Traits\TenantAwareJob;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public $tries = 3; // Maximum retries

    protected $nomor;
    protected $teks;
    protected $mediaUrl;
    protected $mediaType;
    protected $deviceId;
    protected $interactiveType;
    protected $interactiveOptions;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($nomor, $teks, $mediaUrl = null, $mediaType = null, $deviceId = null, $interactiveType = null, $interactiveOptions = null)
    {
        $this->nomor = $nomor;
        $this->teks = $teks;
        $this->mediaUrl = $mediaUrl;
        $this->mediaType = $mediaType;
        $this->deviceId = $deviceId;
        $this->interactiveType = $interactiveType;
        $this->interactiveOptions = $interactiveOptions;
        $this->initializeTenantContext();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $waService)
    {
        $this->restoreTenantContext();

        // 30 messages max per minute per tenant to avoid Meta Ban
        Redis::throttle('wa-messaging-tenant-' . ($this->tenant_id ?? 'landlord'))
            ->allow(30)
            ->every(60)
            ->then(function () use ($waService) {
                try {
                    $success = $waService->kirimPesanSekarang(
                        $this->nomor,
                        $this->teks,
                        $this->mediaUrl,
                        $this->mediaType,
                        $this->deviceId,
                        $this->interactiveType,
                        $this->interactiveOptions
                    );

                    if (!$success) {
                        throw new Exception("Gagal mengirim pesan WhatsApp ke {$this->nomor}. Akan dicoba lagi.");
                    }
                } catch (Exception $e) {
                    if (str_contains($e->getMessage(), 'FORBIDDEN_ERROR')) {
                        \Illuminate\Support\Facades\Log::warning("Job Dibatalkan (Graceful Failure): " . $e->getMessage());
                        return; // Abort tanpa melempar ulang, mencegah retry loop
                    }
                    throw $e;
                } finally {
                    $this->forgetTenantContext();
                }
            }, function () {
                // Throttled: release back to queue with 10s delay
                $this->release(10);
            });
    }
}
