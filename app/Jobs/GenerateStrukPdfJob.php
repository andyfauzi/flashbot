<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

use App\Traits\TenantAwareJob;

class GenerateStrukPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public $tries = 3;
    public $timeout = 60;

    protected $nomorWa;
    protected $nomorOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($nomorWa, $nomorOrder)
    {
        $this->nomorWa = $nomorWa;
        $this->nomorOrder = $nomorOrder;
        $this->initializeTenantContext();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $orderService)
    {
        $this->restoreTenantContext();
        try {
            $orderService->kirimStrukPdf($this->nomorWa, $this->nomorOrder);
        } catch (\Exception $e) {
            Log::error("GenerateStrukPdfJob Failed: " . $e->getMessage());
            throw $e;
        } finally {
            $this->forgetTenantContext();
        }
    }
}
