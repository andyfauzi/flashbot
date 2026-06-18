<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\IdentitasToko;
use App\Models\Tenant;
use App\Services\WhatsAppService;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Xendit Webhook Received: ' . json_encode($request->all()));

        $identitas = IdentitasToko::first();
        if (!$identitas || !$identitas->is_payment_gateway_active) {
            return response()->json(['error' => 'Payment gateway inactive'], 400);
        }

        // Validate token
        $token = $request->header('x-callback-token');
        if ($token !== $identitas->xendit_webhook_token) {
            Log::warning("Invalid Xendit Callback Token for tenant " . TenantManager::getTenant()->id);
            return response()->json(['error' => 'Invalid token'], 403);
        }

        $externalId = $request->input('external_id');
        $status = $request->input('status'); // e.g. 'PAID', 'SETTLED'

        if ($status === 'PAID' || $status === 'SETTLED') {
            $pesanan = Pesanan::where('nomor_order', $externalId)->first();
            
            if ($pesanan && $pesanan->status !== 'paid') {
                $pesanan->update([
                    'status' => 'paid',
                    'uang_muka' => $pesanan->total_biaya // Lunas
                ]);

                // Notify admin
                $waService = new WhatsAppService();
                $totalFmt = number_format($pesanan->total_biaya, 0, ',', '.');
                $pesanAdmin = "✅ *PEMBAYARAN DIGITAL BERHASIL (XENDIT)*\n\n" .
                    "Nomor Order: *{$pesanan->nomor_order}*\n" .
                    "Total Tagihan: Rp {$totalFmt}\n\n" .
                    "Sistem telah mengupdate pesanan menjadi LUNAS.";

                $groupId = env('WHATSAPP_GROUP_ID_SELLER');
                if ($groupId) {
                    $waService->kirimPesan($groupId, $pesanAdmin);
                }

                // Notify customer
                $pesanCustomer = "🎉 *Pembayaran Berhasil Diterima!*\n\n" .
                    "Terima kasih, pembayaran untuk pesanan *{$pesanan->nomor_order}* sebesar *Rp {$totalFmt}* telah berhasil diverifikasi secara otomatis.\n\n" .
                    "Pesanan Anda akan segera diproses!";
                $waService->kirimPesan($pesanan->nomor_wa, $pesanCustomer);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
