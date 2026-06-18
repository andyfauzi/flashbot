<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Create an invoice link for an order.
     * 
     * @param string $orderId External ID (e.g. PRE-001)
     * @param float $amount Total amount
     * @param string $description Order description
     * @param array $customer Customer details ['name', 'phone']
     * @return string|null The invoice URL, or null if failed
     */
    public function createInvoice($orderId, $amount, $description, $customer)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $orderId,
                    'amount' => $amount,
                    'description' => $description,
                    'customer' => [
                        'given_names' => $customer['name'] ?? 'Pelanggan',
                        'mobile_number' => $customer['phone'] ?? null,
                    ],
                    'invoice_duration' => 86400, // 24 hours
                ]);

            if ($response->successful()) {
                return $response->json()['invoice_url'] ?? null;
            }

            Log::error('Xendit Create Invoice Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Xendit Create Invoice Exception: ' . $e->getMessage());
            return null;
        }
    }
}
