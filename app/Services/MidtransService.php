<?php

namespace App\Services;

use App\Models\IdentitasToko;

class MidtransService
{
    protected $serverKey;
    protected $isProduction;
    protected $isActive;

    public function __construct()
    {
        $identitas = IdentitasToko::first();
        if ($identitas) {
            $this->serverKey = $identitas->midtrans_server_key;
            $this->isProduction = $identitas->midtrans_is_production;
            $this->isActive = $identitas->is_midtrans_active;
        }

        // Set konfigurasi Midtrans
        \Midtrans\Config::$serverKey = $this->serverKey;
        \Midtrans\Config::$isProduction = $this->isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function isActive()
    {
        return $this->isActive && !empty($this->serverKey);
    }

    public function getClientKey()
    {
        $identitas = IdentitasToko::first();
        return $identitas ? $identitas->midtrans_client_key : null;
    }

    /**
     * Meminta Snap Token dari Midtrans
     */
    public function getSnapToken($pesanan)
    {
        if (!$this->isActive()) {
            return null;
        }

        // Generate Order ID khusus agar tidak bentrok antar tenant
        // Format: INV-{PesananID}-{Subdomain}
        $subdomain = app('current_tenant')->subdomain;
        $orderId = 'INV-' . $pesanan->id . '-' . $subdomain;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $pesanan->total_biaya,
            ],
            'customer_details' => [
                'first_name' => $pesanan->nama_penerima,
                'phone' => $pesanan->nomor_wa ?? '081234567890',
            ]
        ];

        // Jika ada nama meja atau catatan bisa ditambah di custom field jika perlu
        
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return null;
        }
    }
}
