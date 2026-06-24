<?php

namespace App\Services;

use App\Models\ChatbotDevice;
use App\Models\ChatbotMenu;
use App\Models\ChatbotPesan;
use App\Models\ChatbotUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $metaPhoneNumberId = '';
    protected string $metaAccessToken   = '';
    protected string $metaApiVersion    = 'v20.0';
    public string $gatewayType       = 'baileys';

    public function __construct()
    {
        // Default to env
        $this->metaPhoneNumberId = env('META_PHONE_NUMBER_ID', '');
        $this->metaAccessToken   = env('META_ACCESS_TOKEN', '');
        $this->metaApiVersion    = env('META_API_VERSION', 'v20.0');
        $this->gatewayType       = env('WHATSAPP_GATEWAY', 'baileys');

        // Check Landlord Setting if Meta
        try {
            if (!\App\Services\TenantManager::hasTenant()) {
                $landlordPhoneId = \App\Models\LandlordSetting::get('meta_phone_number_id');
                $landlordToken = \App\Models\LandlordSetting::get('meta_access_token');
                if ($landlordPhoneId && $landlordToken) {
                    $this->metaPhoneNumberId = $landlordPhoneId;
                    $this->metaAccessToken = $landlordToken;
                    // Auto-switch to Meta if Landlord has configured it
                    $this->gatewayType = 'meta';
                }
            } else {
                $identitas = \App\Models\IdentitasToko::first();
                if ($identitas && $identitas->whatsapp_gateway === 'meta_mandiri') {
                    $this->gatewayType       = 'meta';
                    $this->metaPhoneNumberId = $identitas->meta_phone_number_id ?: $this->metaPhoneNumberId;
                    $this->metaAccessToken   = $identitas->meta_access_token ?: $this->metaAccessToken;
                }
            }
        } catch (\Exception $e) {
            // Ignore if DB is not ready
        }
    }

    protected function normalisasiNomor(string $nomor): string
    {
        $nomor = preg_replace('/\D/', '', $nomor);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }
        if (!str_starts_with($nomor, '62') && !str_contains($nomor, '-')) {
            $nomor = '62' . $nomor;
        }
        return $nomor;
    }

    /**
     * Kirim pesan via Gateway (Meta atau Baileys)
     */
    public function kirimPesan(
        string  $nomor,
        string  $teks,
        ?string $mediaUrl         = null,
        ?string $mediaType        = null,
        ?string $deviceId         = null,
        ?string $interactiveType  = null,
        ?array  $interactiveOptions = null
    ): bool {
        // Dispatch pesan utama ke queue
        \App\Jobs\SendWhatsAppMessageJob::dispatch(
            $nomor, $teks, $mediaUrl, $mediaType, $deviceId, $interactiveType, $interactiveOptions
        )->onConnection('database');

        // Otomatis lampirkan QRIS sebagai PESAN KEDUA TERPISAH jika mendiskusikan transfer/QRIS/rekening dan media belum diset
        if (empty($mediaUrl) && preg_match('/(qris|rekening|no\.?\s*rek|transfer)/i', $teks)) {
            $identitas = \App\Models\IdentitasToko::first();
            if ($identitas && $identitas->qris_path) {
                $qrisUrl = env('NGROK_PUBLIC_URL') 
                    ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $identitas->qris_path
                    : url('storage/' . $identitas->qris_path);

                $namaToko = $identitas->nama_toko ?? 'Toko';

                // Dispatch pesan QRIS secara terpisah agar pesan utama di atas tetap aman terkirim
                \App\Jobs\SendWhatsAppMessageJob::dispatch(
                    $nomor, 
                    "Berikut adalah QRIS Pembayaran {$namaToko}:", 
                    $qrisUrl, 
                    'image', 
                    $deviceId
                )->onConnection('database');
            }
        }

        return true;
    }

    /**
     * Eksekusi asli kirim pesan (Dipanggil dari Job)
     */
    public function kirimPesanSekarang(
        string  $nomor,
        string  $teks,
        ?string $mediaUrl         = null,
        ?string $mediaType        = null,
        ?string $deviceId         = null,
        ?string $interactiveType  = null,
        ?array  $interactiveOptions = null
    ): bool {
        // Normalisasi nomor (harus format internasional, tanpa +)
        if (str_contains($nomor, '@')) {
            // Hapus tanda + di awal JID jika ada agar tidak menyebabkan error di gateway Node.js
            if (str_starts_with($nomor, '+')) {
                $nomor = substr($nomor, 1);
            }
        } else {
            $nomor = $this->normalisasiNomor($nomor);
        }

        // ==========================================
        // CEK KUOTA (RATE LIMIT) BOT WA
        // ==========================================
        $tenant = \App\Services\TenantManager::current();
        if ($tenant) {
            $plan = $tenant->plan ?? 'gratis';
            $limit = \App\Models\LandlordSetting::get('limit_wa_' . $plan);
            if ($limit === null) {
                switch ($plan) {
                    case 'gratis': $limit = 100; break;
                    case 'starter': $limit = 1000; break;
                    case 'pro': $limit = 5000; break;
                    case 'business': $limit = 999999; break;
                    default: $limit = 100;
                }
            }
            
            $currentMonthCount = \App\Models\ChatbotPesan::where('arah', 'keluar')
                ->whereMonth('waktu', now()->month)
                ->whereYear('waktu', now()->year)
                ->count();
                
            if ($currentMonthCount >= $limit) {
                \Illuminate\Support\Facades\Log::warning("⚠️ Tenant {$tenant->name} ({$tenant->subdomain}) melebihi kuota bot WA bulan ini ({$currentMonthCount}/{$limit}). Pesan tidak dikirim.");
                return false;
            }
        }

        $gateway = env('WHATSAPP_GATEWAY', 'baileys');
        
        try {
            $identitas = \App\Models\IdentitasToko::first();
            if ($identitas && $identitas->whatsapp_gateway === 'meta_mandiri') {
                $gateway = 'meta'; // Paksa ke meta menggunakan credentials tenant
                $this->metaPhoneNumberId = $identitas->meta_phone_number_id ?: $this->metaPhoneNumberId;
                $this->metaAccessToken   = $identitas->meta_access_token ?: $this->metaAccessToken;
            }
        } catch (\Exception $e) {
            // Ignore if no tenant context
        }

        if ($gateway === 'meta') {
            // Meta API tidak mendukung pesan ke WhatsApp Grup (@g.us) atau Saluran (@newsletter)
            if (strpos($nomor, '@g.us') !== false || strpos($nomor, '@newsletter') !== false) {
                Log::info("🔄 Mengalihkan pengiriman ke Grup/Saluran ({$nomor}) melalui Baileys meskipun tenant menggunakan Meta.");
                $gateway = 'baileys';
            } else {
                return $this->kirimPesanMeta($nomor, $teks, $mediaUrl, $mediaType, $interactiveType, $interactiveOptions);
            }
        }

        return $this->kirimPesanBaileys($nomor, $teks, $mediaUrl, $mediaType, $deviceId, $interactiveType, $interactiveOptions);
    }

    /**
     * Kirim pesan menggunakan Baileys (Node.js Service)
     */
    protected function kirimPesanBaileys(
        string  $nomor,
        string  $teks,
        ?string $mediaUrl         = null,
        ?string $mediaType        = null,
        ?string $deviceId         = null,
        ?string $interactiveType  = null,
        ?array  $interactiveOptions = null
    ): bool {
        try {
            $baileysUrl = env('BAILEYS_API_URL', 'http://127.0.0.1:3000');
            
            // Jika deviceId kosong, ambil device default
            if (!$deviceId) {
                $defaultDevice = \App\Models\ChatbotDevice::where('is_default', true)->first();
                if (!$defaultDevice) {
                    $defaultDevice = \App\Models\ChatbotDevice::first();
                }
                
                if ($defaultDevice) {
                    $deviceId = $defaultDevice->session_id;
                } else {
                    Log::error("❌ Baileys: Tidak ada device yang aktif/default.");
                    return false;
                }
            }

            $payload = [
                'sessionId'          => $deviceId,
                'number'             => $nomor,
                'message'            => $teks,
                'mediaUrl'           => $mediaUrl,
                'mediaType'          => $mediaType,
                'interactiveType'    => $interactiveType,
                'interactiveOptions' => $interactiveOptions,
            ];

            $response = Http::timeout(10)->post("{$baileysUrl}/send-message", $payload);

            Log::info("Baileys API [{$nomor}]: " . $response->body());

            if ($response->successful()) {
                ChatbotPesan::simpanKeluar($nomor, $teks, $mediaUrl, $mediaType);
                return true;
            }

            Log::warning("⚠️ Baileys gagal: " . $response->body());
            if (str_contains(strtolower($response->body()), 'forbidden')) {
                throw new \Exception("FORBIDDEN_ERROR: Akses dilarang (bot mungkin tidak di dalam grup tujuan).");
            }
            return false;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'FORBIDDEN_ERROR')) {
                throw $e;
            }
            Log::error("❌ Baileys Exception [{$nomor}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan menggunakan Meta WhatsApp Cloud API
     */
    protected function kirimPesanMeta(
        string  $nomor,
        string  $teks,
        ?string $mediaUrl         = null,
        ?string $mediaType        = null,
        ?string $interactiveType  = null,
        ?array  $interactiveOptions = null
    ): bool {
        try {
            if (empty($this->metaPhoneNumberId) || empty($this->metaAccessToken)) {
                Log::error('❌ Meta API: META_PHONE_NUMBER_ID atau META_ACCESS_TOKEN belum diisi di .env');
                return false;
            }

            $apiUrl = "https://graph.facebook.com/{$this->metaApiVersion}/{$this->metaPhoneNumberId}/messages";

            // Interactive Buttons
            if ($interactiveType === 'button' && !empty($interactiveOptions)) {
                $buttons = [];
                foreach (array_slice($interactiveOptions, 0, 3) as $i => $opt) {
                    $buttons[] = [
                        'type'  => 'reply',
                        'reply' => [
                            'id'    => $opt['id'] ?? (string)($i + 1),
                            'title' => mb_substr($opt['text'] ?? "Pilihan " . ($i + 1), 0, 20),
                        ]
                    ];
                }

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $nomor,
                    'type'              => 'interactive',
                    'interactive'       => [
                        'type' => 'button',
                        'body' => ['text' => $teks],
                        'action' => ['buttons' => $buttons]
                    ]
                ];

                if ($mediaUrl) {
                    $payload['interactive']['header'] = [
                        'type'  => 'image',
                        'image' => ['link' => $mediaUrl]
                    ];
                }

                $response = Http::withToken($this->metaAccessToken)
                    ->timeout(10)
                    ->post($apiUrl, $payload);

                Log::info("Meta API Buttons [{$nomor}]: " . $response->body());

                if ($response->successful()) {
                    ChatbotPesan::simpanKeluar($nomor, $teks, $mediaUrl, $mediaType);
                    return true;
                }

                Log::warning("⚠️ Meta Buttons gagal: " . $response->body());
                return false;
            }

            // Interactive List
            if ($interactiveType === 'list' && !empty($interactiveOptions)) {
                $rows = [];
                foreach ($interactiveOptions as $i => $opt) {
                    $rows[] = [
                        'id'          => $opt['id'] ?? (string)($i + 1),
                        'title'       => mb_substr($opt['text'] ?? "Pilihan " . ($i + 1), 0, 24),
                        'description' => mb_substr($opt['desc'] ?? '', 0, 72),
                    ];
                }

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $nomor,
                    'type'              => 'interactive',
                    'interactive'       => [
                        'type' => 'list',
                        'body' => ['text' => $teks],
                        'action' => [
                            'button'   => 'Pilih Menu',
                            'sections' => [
                                ['title' => 'Menu', 'rows' => $rows]
                            ]
                        ]
                    ]
                ];

                $response = Http::withToken($this->metaAccessToken)
                    ->timeout(10)
                    ->post($apiUrl, $payload);

                Log::info("Meta API List [{$nomor}]: " . $response->body());

                if ($response->successful()) {
                    ChatbotPesan::simpanKeluar($nomor, $teks, $mediaUrl, $mediaType);
                    return true;
                }

                Log::warning("⚠️ Meta List gagal: " . $response->body());
                return false;
            }

            // Pesan dengan media
            if ($mediaUrl) {
                if ($mediaType === 'document') {
                    $payload = [
                        'messaging_product' => 'whatsapp',
                        'to'                => $nomor,
                        'type'              => 'document',
                        'document'          => [
                            'link'     => $mediaUrl,
                            'caption'  => $teks,
                            'filename' => basename(parse_url($mediaUrl, PHP_URL_PATH)) ?: 'document.pdf',
                        ]
                    ];
                } else {
                    $payload = [
                        'messaging_product' => 'whatsapp',
                        'to'                => $nomor,
                        'type'              => 'image',
                        'image'             => [
                            'link'    => $mediaUrl,
                            'caption' => $teks,
                        ]
                    ];
                }

                $response = Http::withToken($this->metaAccessToken)
                    ->timeout(10)
                    ->post($apiUrl, $payload);

                Log::info("Meta API Media [{$nomor}]: " . $response->body());

                if ($response->successful()) {
                    ChatbotPesan::simpanKeluar($nomor, $teks, $mediaUrl, $mediaType);
                    return true;
                }

                return false;
            }

            // Pesan teks biasa
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $nomor,
                'type'              => 'text',
                'text'              => [
                    'preview_url' => false,
                    'body'        => $teks,
                ]
            ];

            $response = Http::withToken($this->metaAccessToken)
                ->timeout(10)
                ->post($apiUrl, $payload);

            Log::info("Meta API Text [{$nomor}]: " . $response->body());

            if ($response->successful()) {
                ChatbotPesan::simpanKeluar($nomor, $teks, $mediaUrl, $mediaType);
                return true;
            }

            Log::warning("⚠️ Meta Text gagal [{$nomor}]: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("❌ Meta Exception [{$nomor}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Proses pesan masuk dan balas otomatis
     */
    public function prosesPesan(string $nomor, string $pesan, ?string $mediaUrl = null, ?string $mediaType = null, ?string $deviceId = null): void
    {
        $pesan = trim($pesan);
        $teks  = strtolower($pesan);

        $user = ChatbotUser::ambilAtauBuat($nomor);
        ChatbotPesan::simpanMasuk($nomor, $pesan, $mediaUrl, $mediaType);

        Log::info("📩 Pesan dari {$nomor}: {$pesan}");

        // =============================================
        // AI AGENT INTERCEPTOR (GEMINI)
        // =============================================
        if (env('USE_GEMINI_AI') === true || env('USE_GEMINI_AI') === 'true') {
            Log::info("🤖 Meneruskan pesan ke Gemini AI Agent...");
            try {
                if (!\App\Services\TenantManager::hasTenant()) {
                    // Konteks Landlord / Pusat
                    $aiService = app(\App\Services\LandlordAiService::class);
                } else {
                    // Konteks Tenant (Toko)
                    $aiService = app(\App\Services\GeminiAiService::class);
                }
                
                $reply = $aiService->handleMessage($nomor, $pesan);
                $this->kirimPesan($nomor, $reply, null, null, $deviceId);
            } catch (\Exception $e) {
                Log::error("Gemini AI Error: " . $e->getMessage());
                $this->kirimPesan($nomor, "Sistem AI sedang offline. Pesan error: " . $e->getMessage(), null, null, $deviceId);
            }
            return;
        }

        if (in_array($teks, ['cek order', 'pesanan saya', 'status order'])) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->cekStatusOrder($nomor, $teks);
            return;
        }

        if (preg_match('/^(cek|struk)\s+(ord-[a-z0-9\-]+)$/i', $teks, $matches)) {
            $orderService = app(\App\Services\OrderService::class);
            $aksi = strtolower($matches[1]);
            $nomorOrder = strtoupper($matches[2]);
            
            if ($aksi === 'struk') {
                $orderService->kirimStrukPdf($nomor, $nomorOrder);
            } else {
                $orderService->cekStatusOrder($nomor, $nomorOrder);
            }
            return;
        }

        if (str_starts_with($teks, 'bayar')) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->prosesOrderFlow($nomor, $pesan, $mediaUrl, $mediaType);
            return;
        }

        $langkahUser = $user->langkah ?? null;
        if ($langkahUser && (str_starts_with($langkahUser, 'order_') || $langkahUser === 'order')) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->prosesOrderFlow($nomor, $pesan, $mediaUrl, $mediaType);
            return;
        }

        if (in_array($teks, ['beli', 'order', 'belanja', 'belanja barang'])) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->mulaiBelanja($nomor, $user);
            return;
        }

        if (in_array($teks, ['cek stok', 'stok', 'info stok', 'katalog'])) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->kirimInfoStok($nomor);
            return;
        }

        $deviceObj = \App\Models\ChatbotDevice::where('session_id', $deviceId)->first();

        // =============================================
        // STATEFUL MENU MATCHING
        // Prioritas 1: Sub-menu sesuai konteks langkah user (parent_kode = langkah)
        // Prioritas 2: Menu top-level (parent_kode IS NULL)
        // Prioritas 3: Fallback/default
        // =============================================

        $langkahUser = $user->langkah ?? null;

        $baseQuery = ChatbotMenu::where('aktif', true)
            ->where(function ($q) use ($deviceObj) {
                if ($deviceObj) {
                    $q->where('device_id', $deviceObj->id)->orWhereNull('device_id');
                } else {
                    $q->whereNull('device_id');
                }
            })
            ->orderByRaw('device_id IS NULL, device_id DESC')
            ->orderBy('urutan');

        // Ambil semua menu sekali
        $allMenus = $baseQuery->get();

        // =============================================
        // NAVIGASI BATAL (batal, cancel, reset)
        // Membatalkan sesi aktif dan kembali ke awal
        // =============================================
        $cancelKeywords = ['batal', 'cancel', 'reset'];
        if (in_array($teks, $cancelKeywords)) {
            $user->update(['langkah' => 'menu']);
            Log::info("❌ [{$nomor}] Sesi dibatalkan oleh user");
            
            $this->kirimPesan($nomor, "✅ Aksi telah dibatalkan.\n\nKetik *menu* atau *halo* untuk melihat daftar layanan kembali.");
            return;
        }

        // =============================================
        // NAVIGASI KEMBALI (#, back, kembali)
        // Hapus segmen terakhir dari path langkah untuk naik satu level
        // Contoh: langkah="2.1" → cari menu kode="2" → tampilkan ulang
        //         langkah="2"   → pergi ke menu utama (halo/menu)
        // =============================================
        $backKeywords = ['#', 'back', 'kembali', 'balik'];
        if (in_array($teks, $backKeywords)) {
            $parentMenu = null;

            if ($langkahUser && str_contains($langkahUser, '.')) {
                // Ada sub-level: hapus segmen terakhir → naik satu level
                // Contoh: "2.1" → "2", "2.1.1" → "2.1"
                $parts      = explode('.', $langkahUser);
                array_pop($parts);
                $parentPath = implode('.', $parts);

                // Tentukan parent menu berdasarkan path
                $parentParts     = explode('.', $parentPath);
                $parentKode      = array_pop($parentParts);
                $parentParentKode = count($parentParts) ? implode('.', $parentParts) : null;

                foreach ($allMenus as $m) {
                    $mKodes = array_map('trim', explode(',', strtolower($m->kode)));
                    $mParent = strtolower($m->parent_kode ?? '');
                    $targetParent = strtolower($parentParentKode ?? '');

                    if (in_array($parentKode, $mKodes) && $mParent === $targetParent) {
                        $parentMenu = $m;
                        break;
                    }
                }

                if ($parentMenu) {
                    $user->update(['langkah' => $parentPath]);
                    Log::info("⬆️ [{$nomor}] Kembali ke: '{$parentPath}'");
                }

            } else {
                // Sudah di level 1 atau belum ada langkah → ke menu utama
                foreach ($allMenus as $m) {
                    if (!empty($m->parent_kode)) continue;
                    $mKodes = array_map('trim', explode(',', strtolower($m->kode)));
                    if (in_array('halo', $mKodes) || in_array('menu', $mKodes) || in_array('0', $mKodes)) {
                        $parentMenu = $m;
                        break;
                    }
                }
                $user->update(['langkah' => 'menu']);
                Log::info("⬆️ [{$nomor}] Kembali ke menu utama");
            }

            if ($parentMenu) {
                $options         = is_string($parentMenu->pilihan_interaktif)
                    ? json_decode($parentMenu->pilihan_interaktif, true)
                    : $parentMenu->pilihan_interaktif;
                $interactiveType = $parentMenu->tipe_pesan !== 'text' ? $parentMenu->tipe_pesan : null;
                $this->kirimPesan($nomor, $parentMenu->isi, $parentMenu->media_url, $parentMenu->media_type, $deviceId, $interactiveType, $options);
                return;
            }

            // Fallback jika tidak ada menu utama
            $this->kirimPesan($nomor, "Ketik *halo* atau *menu* untuk melihat daftar layanan.");
            return;
        }

        $matchedMenu  = null;
        $fallbackMenu = null;

        // === PASS 1: Cari di sub-menu konteks user (parent_kode = langkah user) ===
        if ($langkahUser) {
            foreach ($allMenus as $m) {
                // Hanya cek menu yang punya parent_kode sesuai langkah user
                if (strtolower($m->parent_kode ?? '') !== strtolower($langkahUser)) continue;

                $kodes = array_map('trim', explode(',', strtolower($m->kode)));
                if (in_array($teks, $kodes) || strtolower($m->judul) === $teks) {
                    $matchedMenu = $m;
                    Log::info("🎯 Sub-menu match [{$nomor}]: '{$teks}' dalam konteks '{$langkahUser}'");
                    break;
                }
            }
        }

        // === PASS 2: Cari di menu top-level (parent_kode IS NULL / kosong) ===
        if (!$matchedMenu) {
            foreach ($allMenus as $m) {
                // Hanya cek menu tanpa parent (top-level)
                if (!empty($m->parent_kode)) continue;

                $kodes = array_map('trim', explode(',', strtolower($m->kode)));

                if (in_array($teks, $kodes) || strtolower($m->judul) === $teks) {
                    $matchedMenu = $m;
                    Log::info("✅ Top-level menu match [{$nomor}]: '{$teks}'");
                    break;
                }

                if (in_array('default', $kodes) || in_array('fallback', $kodes)) {
                    $fallbackMenu = $m;
                }
            }
        }

        $menuToExecute = $matchedMenu ?? $fallbackMenu;

        if ($menuToExecute) {
            // =============================================
            // UPDATE LANGKAH (Full Path)
            // Setiap menu simpan path lengkap agar sub-sub-menu bisa bekerja
            //
            // Contoh:
            //   Menu top-level kode="2"         → langkah = "2"
            //   Sub-menu parent_kode="2" kode="1" → langkah = "2.1"
            //   Sub-sub parent_kode="2.1" kode="1" → langkah = "2.1.1"
            //
            // Kode reset (halo, menu, 0) tetap di langkah = kode itu sendiri
            // =============================================
            $kodePertama = trim(explode(',', $menuToExecute->kode)[0]);

            if (empty($menuToExecute->parent_kode)) {
                // Menu top-level: langkah = kode
                $newLangkah = $kodePertama;
            } else {
                // Sub-menu: langkah = parent_kode + "." + kode
                // Ini memungkinkan nesting tak terbatas
                $newLangkah = $menuToExecute->parent_kode . '.' . $kodePertama;
            }

            $user->update(['langkah' => $newLangkah]);
            Log::info("📍 Langkah user [{$nomor}] = '{$newLangkah}'");

            $options         = is_string($menuToExecute->pilihan_interaktif)
                ? json_decode($menuToExecute->pilihan_interaktif, true)
                : $menuToExecute->pilihan_interaktif;
            $interactiveType = $menuToExecute->tipe_pesan !== 'text' ? $menuToExecute->tipe_pesan : null;

            $this->kirimPesan($nomor, $menuToExecute->isi, $menuToExecute->media_url, $menuToExecute->media_type, $deviceId, $interactiveType, $options);
            return;
        }

        // Tidak ada menu yang cocok (Obrolan biasa / non-command)
        // Fallback: Jika user sedang berada di tengah alur (bukan di menu utama), beri peringatan format salah.
        if ($langkahUser && !in_array(strtolower($langkahUser), ['menu', 'halo', '0'])) {
            $fallbackMsg = "⚠️ Maaf, format atau pilihan tidak sesuai.\n\nSilakan pilih sesuai instruksi sebelumnya, atau ketik *batal* untuk kembali ke menu utama.";
            $this->kirimPesan($nomor, $fallbackMsg, null, null, $deviceId);
        } else {
            $fallbackMsg = "Halo! Maaf, pesan *{$pesan}* tidak dikenali sistem.\n\nSilakan ketik *menu* atau *halo* untuk melihat daftar layanan kami.";
            $this->kirimPesan($nomor, $fallbackMsg, null, null, $deviceId);
        }
        
        return;
    }

    /**
     * Cek status koneksi Gateway
     */
    public function statusGateway(): array
    {
        $gateway = $this->gatewayType;

        if ($gateway === 'baileys') {
            $baileysUrl = env('BAILEYS_API_URL', 'http://127.0.0.1:3000');
            try {
                // Ping the Baileys server
                $response = Http::timeout(3)->get("{$baileysUrl}/device/status/test");
                return [
                    'status'  => 'connected',
                    'gateway' => 'baileys',
                    'message' => 'Baileys Server Node.js Aktif',
                    'phone_number' => 'Multi-Device',
                    'verified_name'=> 'Baileys Server'
                ];
            } catch (\Exception $e) {
                return [
                    'status'  => 'disconnected',
                    'gateway' => 'baileys',
                    'message' => 'Baileys Server (Node.js) tidak dapat dihubungi. Pastikan server.js berjalan.'
                ];
            }
        }

        // Meta Gateway Status
        if (empty($this->metaPhoneNumberId) || empty($this->metaAccessToken)) {
            return [
                'status'  => 'disconnected',
                'gateway' => 'meta',
                'message' => 'META_PHONE_NUMBER_ID atau META_ACCESS_TOKEN belum diisi di .env'
            ];
        }

        try {
            $response = Http::withToken($this->metaAccessToken)
                ->timeout(5)
                ->get("https://graph.facebook.com/{$this->metaApiVersion}/{$this->metaPhoneNumberId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status'       => 'connected',
                    'gateway'      => 'meta',
                    'phone_number' => $data['display_phone_number'] ?? $data['id'] ?? 'Meta Cloud API',
                    'verified_name'=> $data['verified_name'] ?? 'WhatsApp Business',
                ];
            }

            $err = $response->json()['error']['message'] ?? $response->body();
            return [
                'status'  => 'disconnected',
                'gateway' => 'meta',
                'message' => 'Token tidak valid: ' . $err
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'disconnected',
                'gateway' => 'meta',
                'message' => $e->getMessage()
            ];
        }
    }
}