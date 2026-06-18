<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Services\GrupService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WhatsAppService $wa;
    protected GrupService $grup;

    public function __construct(WhatsAppService $wa, GrupService $grup)
    {
        $this->wa   = $wa;
        $this->grup = $grup;
    }

    // =============================================
    // VERIFIKASI WEBHOOK META (GET)
    // Dipanggil saat pertama kali daftarkan webhook di Meta Developer Console
    // =============================================
    public function verifikasi(Request $request)
    {
        $mode      = $request->query('hub_mode')      ?? $request->query('hub.mode');
        $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge')  ?? $request->query('hub.challenge');

        $verifyToken = config('chatbot.meta_verify_token');

        Log::info('Meta Webhook Verifikasi:', [
            'mode'      => $mode,
            'token'     => $token,
            'challenge' => $challenge,
        ]);

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('✅ Meta Webhook terverifikasi!');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('❌ Meta Webhook verifikasi GAGAL — token tidak cocok.');
        return response('Forbidden', 403);
    }

    // =============================================
    // TERIMA PESAN DARI META (POST)
    // Format payload Meta WhatsApp Cloud API
    // =============================================
    public function terima(Request $request)
    {

        try {
            Log::channel('webhook')->info('Webhook received', [
                'timestamp'  => now()->toIso8601String(),
                'ip'         => $request->ip(),
                'gateway'    => $this->detectGateway($request),
                'has_message'=> $this->hasMessage($request),
                'tenant_id'  => app()->bound('current_tenant') ? app('current_tenant')->id : null,
            ]);

            $body = $request->all();

            // Cek apakah ini payload Baileys (memiliki key 'message' dan 'sender' atau 'device')
            if (isset($body['sender'])) {
                $nomor = $body['sender'];
                $pesan = $body['message'] ?? '';
                $deviceId = $body['device'] ?? null;
                $nama = $body['name'] ?? 'User';
                $mediaUrl = $body['mediaUrl'] ?? null;
                $mediaType = $body['mediaType'] ?? null;
                $messageId = $body['message_id'] ?? null;
                
                // Idempotency Check (Hindari proses ganda jika Baileys retry webhook yang sama)
                if ($messageId) {
                    $cacheKey = 'webhook_msg_' . $messageId;
                    if (Cache::has($cacheKey)) {
                        Log::info("Idempotency: Pesan {$messageId} sudah diproses sebelumnya, abaikan.");
                        return response()->json(['status' => 'ignored', 'reason' => 'duplicate'], 200);
                    }
                    // Simpan di cache selama 1 jam
                    Cache::put($cacheKey, true, now()->addHours(1));
                }

                // Server.js mengirim "true" atau "false" sebagai string untuk isgroup
                $isGroup = filter_var($body['isgroup'] ?? false, FILTER_VALIDATE_BOOLEAN);

                Log::channel('webhook')->debug("Processing Baileys message");

                // Identifikasi Tenant dari Device ID
                $this->findAndSwitchTenant($deviceId);

                if ($isGroup) {
                    $member = $body['member'] ?? $nomor;
                    $isAdmin = filter_var($body['is_admin'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    \App\Jobs\ProcessWhatsAppMessageJob::dispatch('grup', [
                        'nomor' => $nomor,
                        'member' => $member,
                        'pesan' => $pesan,
                        'nama' => $nama,
                        'is_admin' => $isAdmin
                    ])->onConnection('database');
                } else {
                    // Proses pesan chatbot via Baileys (Personal Chat)
                    \App\Jobs\ProcessWhatsAppMessageJob::dispatch('personal', [
                        'nomor' => $nomor,
                        'pesan' => $pesan,
                        'mediaUrl' => $mediaUrl,
                        'mediaType' => $mediaType,
                        'deviceId' => $deviceId
                    ])->onConnection('database');
                }

                return response()->json(['status' => 'ok'], 200);
            }

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

                    // Phone Number ID milik kita (device/nomor bot)
                    $devicePhoneId = $metadata['phone_number_id'] ?? null;

                    foreach ($messages as $msg) {
                        $metaMsgId = $msg['id'] ?? null;
                        if ($metaMsgId) {
                            $cacheKey = 'meta_msg_' . $metaMsgId;
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
                                Log::info("Meta: Abaikan tipe pesan '{$type}'");
                                continue 2;
                        }

                        if (empty($pesan)) {
                            continue;
                        }

                        $nomor    = $msg['from'] ?? '';
                        
                        // Ambil nama pengirim dari contacts
                        $nama = 'User';
                        foreach ($contacts as $contact) {
                            if (($contact['wa_id'] ?? '') === $nomor) {
                                $nama = $contact['profile']['name'] ?? 'User';
                                break;
                            }
                        }

                        Log::channel('webhook')->debug("Processing Meta message");

                        $this->findAndSwitchTenant($devicePhoneId);

                        // Proses pesan chatbot
                        \App\Jobs\ProcessWhatsAppMessageJob::dispatch('personal', [
                            'nomor' => $nomor,
                            'pesan' => $pesan,
                            'mediaUrl' => $mediaUrl,
                            'mediaType' => $mediaType,
                            'deviceId' => $devicePhoneId
                        ])->onConnection('database');
                    }
                }
            }

            // Meta butuh respons 200 segera
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Meta Webhook error: ' . $e->getMessage());
            // Tetap return 200 agar Meta tidak menganggap webhook gagal dan retry terus
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    private function findAndSwitchTenant($deviceId)
    {
        if (!$deviceId) return false;

        $tenantId = Cache::remember('device_tenant_' . $deviceId, now()->addDays(1), function () use ($deviceId) {
            $tenants = \App\Models\Tenant::where('is_active', true)->get();
            foreach ($tenants as $tenant) {
                \App\Services\TenantManager::switchTo($tenant);
                try {
                    $device = \App\Models\ChatbotDevice::where('session_id', $deviceId)
                                ->orWhere('nomor', $deviceId)
                                ->first();
                    if ($device) {
                        return $tenant->id;
                    }
                } catch (\Exception $e) {
                    continue; // Skip if database or table not ready
                }
            }
            return null;
        });

        if ($tenantId) {
            $tenant = \App\Models\Tenant::find($tenantId);
            if ($tenant) {
                \App\Services\TenantManager::switchTo($tenant);
                return true;
            }
        }

        \App\Services\TenantManager::switchToLandlord();
        return false;
    }

    private function detectGateway(Request $request): string
    {
        // Deteksi dari struktur payload, BUKAN dari isi pesan
        return isset($request->input('entry')[0]) ? 'meta' : 'baileys';
    }

    private function hasMessage(Request $request): bool
    {
        // Hanya boolean — ada atau tidak ada pesan
        return !empty($request->input('message')) 
            || !empty($request->input('entry.0.changes.0.value.messages'));
    }
}