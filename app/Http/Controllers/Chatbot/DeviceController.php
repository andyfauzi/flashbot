<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\ChatbotDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index()
    {

        $user = auth()->user();
        if ($user && $user->isUser()) {
            if ($user->device_id) {
                $devices = ChatbotDevice::where('id', $user->device_id)->orderBy('created_at', 'desc')->get();
            } else {
                $devices = collect();
            }
        } else {
            $devices = ChatbotDevice::orderBy('created_at', 'desc')->get();
        }

        $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');

        // Update status for all devices
        foreach ($devices as $device) {
            try {
                $response = Http::timeout(2)->get("{$baileysUrl}/device/status/{$device->session_id}");
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['status'])) {
                        $device->update(['status' => $data['status']]);
                    }
                } else {
                    $device->update(['status' => 'disconnected']);
                }
            } catch (\Exception $e) {
                $device->update(['status' => 'disconnected']);
            }
        }

        return view('chatbot.device.index', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_device' => 'required|max:100',
        ]);

        $tenant = app('current_tenant');
        $plan = $tenant ? strtolower($tenant->plan) : 'basic';
        
        $deviceLimit = 1;
        if (in_array($plan, ['pro', 'premium'])) {
            $deviceLimit = 3;
        } elseif (in_array($plan, ['enterprise', 'unlimited'])) {
            $deviceLimit = 10;
        }

        if (ChatbotDevice::count() >= $deviceLimit) {
            return back()->with('error', "Gagal menambah device! Paket Anda (" . ucfirst($plan) . ") maksimal hanya mengizinkan {$deviceLimit} device. Silakan hubungi admin untuk upgrade paket.");
        }

        $session_id = Str::slug($request->nama_device) . '-' . Str::random(5);
        $is_default = ChatbotDevice::count() === 0;

        $device = ChatbotDevice::create([
            'nama_device' => $request->nama_device,
            'session_id'  => $session_id,
            'status'      => 'disconnected',
            'is_default'  => $is_default
        ]);

        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            Http::timeout(3)->post("{$baileysUrl}/device/start", ['sessionId' => $session_id]);
        } catch (\Exception $e) {
            return back()->with('error', 'Device tersimpan, tapi server Node.js Baileys tidak aktif.');
        }

        return redirect()->route('chatbot.device.index')->with('sukses', 'Device berhasil ditambahkan! Silakan refresh untuk scan QR.');
    }

    public function setAsDefault(ChatbotDevice $device)
    {
        ChatbotDevice::where('id', '!=', $device->id)->update(['is_default' => false]);
        $device->update(['is_default' => true]);
        return back()->with('sukses', 'Device utama berhasil diubah.');
    }

    public function destroy(ChatbotDevice $device)
    {
        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            Http::timeout(3)->post("{$baileysUrl}/device/logout", ['sessionId' => $device->session_id]);
        } catch (\Exception $e) {
            // Abaikan jika server mati
        }

        $device->delete();
        return redirect()->route('chatbot.device.index')->with('sukses', 'Device berhasil dihapus.');
    }

    public function statusQr($sessionId)
    {
        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            $response = Http::timeout(2)->get("{$baileysUrl}/device/status/{$sessionId}");
            if ($response->successful()) {
                $data = $response->json();

                // Jika sesi tidak ada atau disconnected, otomatis start untuk generate QR baru
                if (isset($data['status']) && in_array($data['status'], ['not_found', 'disconnected'])) {
                    Http::timeout(3)->post("{$baileysUrl}/device/start", ['sessionId' => $sessionId]);
                    return response()->json(['status' => 'starting', 'message' => 'Memulai sesi, QR akan segera muncul...']);
                }

                $device = ChatbotDevice::where('session_id', $sessionId)->first();
                if ($device && isset($data['status'])) {
                    $device->update(['status' => $data['status']]);
                }
                
                return response()->json($data);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected']);
        }
        return response()->json(['status' => 'disconnected']);
    }


    public function reconnect(ChatbotDevice $device)
    {
        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');

            // Logout dulu (clear old session state di Node), lalu start ulang
            Http::timeout(3)->post("{$baileysUrl}/device/logout", ['sessionId' => $device->session_id]);

            // Beri jeda singkat agar Node.js sempat bersih
            sleep(1);

            // Start sesi baru
            Http::timeout(3)->post("{$baileysUrl}/device/start", ['sessionId' => $device->session_id]);

            $device->update(['status' => 'connecting']);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reconnect: server Node.js tidak aktif.');
        }

        return back()->with('sukses', "Device '{$device->nama_device}' sedang melakukan reconnect. Tunggu QR Code muncul.");
    }

    public function disconnect(ChatbotDevice $device)
    {
        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            Http::timeout(3)->post("{$baileysUrl}/device/logout", ['sessionId' => $device->session_id]);
            $device->update(['status' => 'disconnected']);
        } catch (\Exception $e) {
            // Update status di DB meskipun Node.js tidak menjawab
            $device->update(['status' => 'disconnected']);
        }

        return back()->with('sukses', "Device '{$device->nama_device}' berhasil diputus.");
    }

    public function simpanSapaan(ChatbotDevice $device, Request $request)
    {
        $request->validate([
            'pesan_sapaan' => 'nullable|string|max:2000',
        ]);

        $device->update([
            'pesan_sapaan' => $request->pesan_sapaan,
        ]);

        return back()->with('sukses', "Pesan sapaan device '{$device->nama_device}' berhasil disimpan!");
    }

    public function updateSettings(Request $request)
    {
        $identitas = \App\Models\IdentitasToko::first() ?? new \App\Models\IdentitasToko();
        $action = $request->input('action');

        if ($action === 'save_gateway') {
            $validated = $request->validate([
                'whatsapp_gateway' => 'required|in:sistem,meta_mandiri',
                'meta_phone_number_id' => 'nullable|string',
                'meta_access_token' => 'nullable|string',
                'meta_webhook_token' => 'nullable|string',
            ]);
            $identitas->whatsapp_gateway = $validated['whatsapp_gateway'];
            $identitas->meta_phone_number_id = $validated['meta_phone_number_id'];
            $identitas->meta_access_token = $validated['meta_access_token'];
            $identitas->meta_webhook_token = $validated['meta_webhook_token'];
            $identitas->save();
            return back()->with('sukses', 'Pengaturan Gateway WhatsApp berhasil disimpan!');
        }

        if ($action === 'save_bot_identity') {
            $validated = $request->validate([
                'nama_bot' => 'nullable|string|max:100',
                'karakter_bot' => 'nullable|string|max:255',
            ]);
            $identitas->nama_bot = $validated['nama_bot'] ?? 'Teta Assistant';
            $identitas->karakter_bot = $validated['karakter_bot'] ?? 'Customer Service Virtual (AI) ramah';
            $identitas->save();
            return back()->with('sukses', 'Identitas Bot AI berhasil disimpan!');
        }

        if ($action === 'save_gemini_key') {
            $validated = $request->validate([
                'gemini_api_key' => 'nullable|string',
            ]);
            $identitas->gemini_api_key = $validated['gemini_api_key'];
            $identitas->save();
            return back()->with('sukses', 'Google Gemini API Key berhasil disimpan!');
        }

        return back()->with('error', 'Aksi tidak dikenali.');
    }
}
