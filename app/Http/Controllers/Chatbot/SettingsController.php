<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\WhatsAppService;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SettingsController extends Controller
{
    protected WhatsAppService $wa;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    private function maskSensitiveValue(?string $value): string
    {
        if (empty($value)) return '';
        $visible = min(6, (int) floor(strlen($value) * 0.2));
        return str_repeat('•', 8) . substr($value, -$visible);
    }

    public function index()
    {
        $status     = $this->wa->statusGateway();
        $ngrokUrl   = config('chatbot.ngrok_public_url', env('NGROK_PUBLIC_URL', ''));
        $webhookUrl = $ngrokUrl
            ? rtrim($ngrokUrl, '/') . '/webhook/whatsapp'
            : url('/webhook/whatsapp');

        // Retrieve token from tenant if available, else config
        $tenant = TenantManager::current();
        $rawToken = '';
        if ($tenant && $tenant->meta_access_token_encrypted) {
            try {
                $rawToken = Crypt::decryptString($tenant->meta_access_token_encrypted);
            } catch (\Exception $e) {}
        }
        if (empty($rawToken)) {
            $rawToken = config('chatbot.meta_access_token', env('META_ACCESS_TOKEN', ''));
        }

        $config = [
            'phone_number_id'    => config('chatbot.meta_phone_number_id', env('META_PHONE_NUMBER_ID', '')),
            'access_token'       => $this->maskSensitiveValue($rawToken),
            'verify_token'       => config('chatbot.meta_verify_token', env('META_WEBHOOK_VERIFY_TOKEN', 'maleboot_webhook_secret_2024')),
            'api_version'        => config('chatbot.meta_api_version', env('META_API_VERSION', 'v20.0')),
            'ngrok_public_url'   => $ngrokUrl,
            'bank_transfer_info' => env('BANK_TRANSFER_INFO', "Bank BCA\nNo Rekening: 123456789\na/n Toko Flashbot"),
            'group_id_seller'    => env('WHATSAPP_GROUP_ID_SELLER', ''),
        ];

        // Ambil daftar grup WhatsApp untuk dropdown
        $daftarGrup = \App\Models\GrupPesan::select('grup_id', 'grup_nama')
            ->groupBy('grup_id', 'grup_nama')
            ->get();

        return view('chatbot.settings.index', compact('status', 'webhookUrl', 'config', 'daftarGrup'));
    }

    public function update(UpdateSettingsRequest $request)
    {
        $envData = [
            'META_PHONE_NUMBER_ID'       => $request->phone_number_id,
            'META_WEBHOOK_VERIFY_TOKEN'  => $request->verify_token,
            'META_API_VERSION'           => $request->api_version,
            'NGROK_PUBLIC_URL'           => $request->ngrok_public_url ?? '',
            'BANK_TRANSFER_INFO'         => $request->bank_transfer_info ?? '',
            'WHATSAPP_GROUP_ID_SELLER'   => $request->group_id_seller ?? '',
        ];

        $tenant = TenantManager::current();
        
        // Cek apakah access token diupdate
        if ($request->filled('access_token')) {
            $newToken = $request->access_token;
            
            // Simpan ke database landlord (encrypted)
            if ($tenant) {
                $tenant->update([
                    'meta_access_token_encrypted' => Crypt::encryptString($newToken),
                ]);
            }
            
            // Update .env secara lokal untuk legacy fallback
            $envData['META_ACCESS_TOKEN'] = $newToken;

            // Log aktivitas sensitif
            $user = auth()->user();
            $oldTokenMasked = $this->maskSensitiveValue($rawToken);
            Log::channel('tenant_security')->info('Meta Access Token diperbarui', [
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Unknown',
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
                'tenant_id' => $tenant ? $tenant->id : null,
                'previous_token' => $oldTokenMasked
            ]);
        }

        // Handle QRIS File Upload
        if ($request->hasFile('qris_file')) {
            $file = $request->file('qris_file');
            $destinationPath = public_path('uploads');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, 'qris.png');
        }

        // Update .env
        $this->updateEnv($envData);

        // Hapus cache config (aman dipanggil dari web)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return redirect()->route('chatbot.settings')
            ->with('sukses', '✅ Konfigurasi berhasil disimpan!');
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Jika nilai mengandung spasi, bungkus dengan tanda kutip
            $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

            if (preg_match("/^{$key}=.*/m", $content)) {
                // Update nilai yang sudah ada
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
            } else {
                // Tambahkan jika belum ada
                $content .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
