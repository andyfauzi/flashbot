<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\IdentitasToko;
use App\Services\TenantManager;

class MetaWebhookController extends Controller
{
    // =============================================
    // VERIFIKASI WEBHOOK META (GET)
    // Dipanggil saat pertama kali daftarkan webhook di Meta Developer Console
    // =============================================
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifikasi($request);
        }

        if ($request->isMethod('post')) {
            return $this->terima($request);
        }

        return response('Method not allowed', 405);
    }

    protected function verifikasi(Request $request)
    {
        $mode      = $request->query('hub_mode')      ?? $request->query('hub.mode');
        $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge')  ?? $request->query('hub.challenge');

        $identitas = IdentitasToko::first();
        if (!$identitas || $identitas->whatsapp_gateway !== 'meta_mandiri') {
            Log::warning('❌ Meta Webhook Verifikasi Ditolak — Tenant tidak menggunakan mode Meta Mandiri.');
            return response('Forbidden', 403);
        }

        $verifyToken = $identitas->meta_webhook_token;

        if (empty($verifyToken)) {
            Log::warning('❌ Meta Webhook Verifikasi GAGAL — Webhook token belum diset di pengaturan toko.');
            return response('Forbidden', 403);
        }

        Log::info('Meta Webhook Verifikasi Mandiri:', [
            'mode'      => $mode,
            'token'     => $token,
            'challenge' => $challenge,
            'tenant'    => TenantManager::getTenant()->id ?? 'unknown'
        ]);

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('✅ Meta Webhook Mandiri terverifikasi!');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('❌ Meta Webhook Verifikasi GAGAL — token tidak cocok.');
        return response('Forbidden', 403);
    }

    // =============================================
    // TERIMA PESAN DARI META (POST)
    // Format payload Meta WhatsApp Cloud API
    // =============================================
    protected function terima(Request $request)
    {
        try {
            $identitas = IdentitasToko::first();
            if (!$identitas || $identitas->whatsapp_gateway !== 'meta_mandiri') {
                return response()->json(['status' => 'ignored', 'reason' => 'gateway not active'], 200);
            }

            $body = $request->all();

            // Pastikan ini dari WhatsApp Meta Cloud API
            if (($body['object'] ?? '') !== 'whatsapp_business_account') {
                return response()->json(['status' => 'ignored'], 200);
            }

            foreach ($body['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? [];

                    // Abaikan status update (delivered, read, dll)
                    if (!empty($value['statuses'])) {
                        continue;
                    }

                    $messages  = $value['messages']  ?? [];
                    $contacts  = $value['contacts']  ?? [];
                    $metadata  = $value['metadata']  ?? [];

                    // Phone Number ID penerima pesan (seharusnya sama dengan milik tenant)
                    $devicePhoneId = $metadata['phone_number_id'] ?? null;
                    if ($devicePhoneId !== $identitas->meta_phone_number_id) {
                        Log::warning("⚠️ Meta Webhook Mandiri: Phone Number ID payload tidak sama dengan konfigurasi toko.");
                    }

                    foreach ($messages as $msg) {
                        $metaMsgId = $msg['id'] ?? null;
                        if ($metaMsgId) {
                            $cacheKey = 'meta_msg_mandiri_' . $metaMsgId;
                            if (Cache::has($cacheKey)) {
                                Log::info("Idempotency: Pesan Meta {$metaMsgId} sudah diproses, abaikan.");
                                continue;
                            }
                            Cache::put($cacheKey, true, now()->addHours(1));
                        }

                        // Abaikan pesan bukan tipe text/button_reply/list_reply/image
                        $type = $msg['type'] ?? 'unknown';

                        $pesan  = '';
                        $mediaUrl  = null;
                        $mediaType = null;

                        switch ($type) {
                            case 'text':
                                $pesan = $msg['text']['body'] ?? '';
                                break;

                            case 'interactive':
                                // Balasan dari tombol interaktif
                                $intType = $msg['interactive']['type'] ?? '';
                                if ($intType === 'button_reply') {
                                    $pesan = $msg['interactive']['button_reply']['id'] ?? '';
                                } elseif ($intType === 'list_reply') {
                                    $pesan = $msg['interactive']['list_reply']['id'] ?? '';
                                }
                                break;

                            case 'image':
                                $pesan    = $msg['image']['caption'] ?? '(gambar)';
                                $mediaUrl = $msg['image']['id'] ?? null; // Media ID Meta
                                $mediaType = 'image';
                                break;

                            case 'document':
                                $pesan    = $msg['document']['caption'] ?? '(dokumen)';
                                $mediaUrl = $msg['document']['id'] ?? null;
                                $mediaType = 'document';
                                break;

                            default:
                                Log::info("Meta Mandiri: Abaikan tipe pesan '{$type}'");
                                continue 2;
                        }

                        if (empty($pesan)) {
                            continue;
                        }

                        $nomor = $msg['from'] ?? '';
                        
                        // Ambil nama pengirim dari contacts
                        $nama = 'User';
                        foreach ($contacts as $contact) {
                            if (($contact['wa_id'] ?? '') === $nomor) {
                                $nama = $contact['profile']['name'] ?? 'User';
                                break;
                            }
                        }

                        Log::channel('webhook')->debug("Processing Meta Mandiri message for {$nomor}");

                        // Proses pesan chatbot. Konteks tenant sudah otomatis aktif oleh IdentifyTenant
                        \App\Jobs\ProcessWhatsAppMessageJob::dispatch('personal', [
                            'nomor' => $nomor,
                            'pesan' => $pesan,
                            'mediaUrl' => $mediaUrl,
                            'mediaType' => $mediaType,
                            'deviceId' => $devicePhoneId // Dapat kita simpan, meskipun di mode mandiri ini tidak krusial lagi
                        ])->onConnection('database');
                    }
                }
            }

            // Meta butuh respons 200 segera
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Meta Mandiri Webhook error: ' . $e->getMessage());
            // Tetap return 200 agar Meta tidak menganggap webhook gagal dan retry terus
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}
