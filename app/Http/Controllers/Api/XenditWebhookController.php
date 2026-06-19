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

        $externalId = $request->input('external_id');
        $status     = $request->input('status'); // e.g. 'PAID', 'SETTLED'

        if (empty($externalId)) {
            return response()->json(['error' => 'Missing external_id'], 400);
        }

        // --- RESOLVE TENANT FROM external_id ---
        // Format yang kita gunakan: "{tenantId}-{nomor_order}"
        // Contoh: "5-ORD-20260619-ABCD"
        // Jika tidak ada prefix tenantId, coba tanpa prefix (fallback lama)
        $tenantId   = null;
        $nomorOrder = $externalId;

        if (preg_match('/^(\d+)-(.+)$/', $externalId, $matches)) {
            $tenantId   = (int) $matches[1];
            $nomorOrder = $matches[2];
        }

        // Cari tenant dan aktifkan konteksnya
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        if (!$tenant) {
            Log::error("Xendit Webhook: Tenant ID {$tenantId} tidak ditemukan untuk external_id: {$externalId}");
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Aktifkan koneksi database tenant yang benar
        TenantManager::switchTo($tenant);

        // --- Sekarang baru boleh akses model tenant ---
        $identitas = IdentitasToko::first();
        if (!$identitas || !$identitas->is_payment_gateway_active) {
            Log::warning("Xendit Webhook: Payment gateway tidak aktif untuk tenant {$tenant->id}");
            return response()->json(['error' => 'Payment gateway inactive'], 400);
        }

        // Validate token
        $token = $request->header('x-callback-token');
        if ($token !== $identitas->xendit_webhook_token) {
            Log::warning("Xendit Webhook: Invalid callback token untuk tenant {$tenant->id}");
            return response()->json(['error' => 'Invalid token'], 403);
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            $pesanan = Pesanan::where('nomor_order', $nomorOrder)->first();

            if ($pesanan && $pesanan->status !== 'paid') {
                $pesanan->update([
                    'status'    => 'paid',
                    'uang_muka' => $pesanan->total_biaya // Lunas
                ]);

                // Notify admin via WhatsApp
                try {
                    $waService  = new WhatsAppService();
                    $totalFmt   = number_format($pesanan->total_biaya, 0, ',', '.');
                    $pesanAdmin = "✅ *PEMBAYARAN DIGITAL BERHASIL (XENDIT)*\n\n" .
                        "Tenant: *{$tenant->name}* (ID: {$tenant->id})\n" .
                        "Nomor Order: *{$pesanan->nomor_order}*\n" .
                        "Total Tagihan: Rp {$totalFmt}\n\n" .
                        "Sistem telah mengupdate pesanan menjadi LUNAS.";

                    $groupId = env('WHATSAPP_GROUP_ID_SELLER');
                    if ($groupId) {
                        $waService->kirimPesan($groupId, $pesanAdmin);
                    }

                    // Notify customer
                    if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
                        $pesanCustomer = "🎉 *Pembayaran Berhasil Diterima!*\n\n" .
                            "Terima kasih, pembayaran untuk pesanan *{$pesanan->nomor_order}* " .
                            "sebesar *Rp {$totalFmt}* telah berhasil diverifikasi secara otomatis.\n\n" .
                            "Pesanan Anda akan segera diproses!";
                        $waService->kirimPesan($pesanan->nomor_wa, $pesanCustomer);
                    }
                } catch (\Exception $e) {
                    Log::error("Xendit Webhook: Gagal kirim notif WA: " . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
