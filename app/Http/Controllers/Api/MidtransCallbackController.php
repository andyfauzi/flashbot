<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Pesanan;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Webhook url: /api/webhook/midtrans
     * Format Order ID: INV-{ID_Pesanan}-{subdomain}
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $orderIdFull = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderIdFull) {
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        // Parse order_id
        // Format: INV-123-kopibudi
        $parts = explode('-', $orderIdFull);
        if (count($parts) < 3 || $parts[0] !== 'INV') {
            return response()->json(['status' => 'ignored', 'message' => 'Not a valid format'], 200);
        }

        // Reconstruct in case subdomain has dashes
        $pesananId = $parts[1];
        $subdomain = implode('-', array_slice($parts, 2));

        // Find tenant in landlord db
        TenantManager::switchToLandlord();
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            Log::warning("Midtrans Webhook: Tenant not found for subdomain $subdomain");
            return response()->json(['status' => 'error', 'message' => 'Tenant not found'], 404);
        }

        // Switch to tenant DB
        TenantManager::switchTo($tenant);

        // Cari pesanan
        $pesanan = Pesanan::find($pesananId);
        if (!$pesanan) {
            Log::warning("Midtrans Webhook: Pesanan $pesananId not found in tenant $subdomain");
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Verifikasi Signature (Opsional, tapi sangat disarankan jika public)
        // Jika server_key disimpan di identitas_toko
        $identitas = \App\Models\IdentitasToko::first();
        if ($identitas && $identitas->midtrans_server_key) {
            $serverKey = $identitas->midtrans_server_key;
            $grossAmount = $payload['gross_amount'] ?? '';
            $statusCode = $payload['status_code'] ?? '';
            $signatureKey = $payload['signature_key'] ?? '';
            
            $computedSignature = hash("sha512", $orderIdFull . $statusCode . $grossAmount . $serverKey);
            if ($computedSignature !== $signatureKey) {
                Log::warning("Midtrans Webhook: Invalid signature for order $orderIdFull");
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }
        }

        $isSuccess = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                // TODO set payment status in merchant's database to 'Challenge by FDS'
            } else if ($fraudStatus == 'accept') {
                $isSuccess = true;
            }
        } else if ($transactionStatus == 'settlement') {
            $isSuccess = true;
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $pesanan->status = 'batal';
            $pesanan->save();
        } else if ($transactionStatus == 'pending') {
            // Do nothing
        }

        if ($isSuccess) {
            // Update pesanan ke lunas
            // Jika pesanan dine-in, biarkan nunggu konfirmasi dapur, atau ubah ke 'diproses_dapur' atau 'selesai'
            if ($pesanan->status === 'pending_payment') {
                $pesanan->status = 'diproses_dapur';
                $pesanan->save();

                // Notifikasi ke WA
                try {
                    $waService = app(\App\Services\WhatsAppService::class);
                    $sellerGroupId = config('chatbot.whatsapp_group_id_seller', '');
                    if ($sellerGroupId) {
                        $waService->kirimPesan($sellerGroupId, "✅ Pembayaran Midtrans (Online) untuk pesanan *{$pesanan->nomor_order}* BERHASIL DITERIMA.\nPesanan otomatis diproses dapur.");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send WA notif after midtrans success: " . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
