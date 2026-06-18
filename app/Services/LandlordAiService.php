<?php

namespace App\Services;

use App\Models\ChatbotHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LandlordAiService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function handleMessage(string $nomorWa, string $pesanUser): ?string
    {
        if (empty($this->apiKey)) {
            Log::error("Gemini API Key is missing for LandlordAiService!");
            return "Maaf, sistem AI Pusat belum dikonfigurasi dengan benar (API Key hilang).";
        }

        // 1. Simpan pesan user ke history (akan masuk ke DB landlord karena fallback)
        ChatbotHistory::create([
            'nomor_wa' => $nomorWa,
            'role' => 'user',
            'content' => $pesanUser
        ]);

        // 2. Siapkan Context (System Instruction & History)
        $systemInstruction = $this->getSystemInstruction();
        $history = ChatbotHistory::where('nomor_wa', $nomorWa)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->reverse()
            ->values();

        $contents = [];
        foreach ($history as $h) {
            $contents[] = [
                'role' => $h->role,
                'parts' => [['text' => $h->content]]
            ];
        }

        // Tidak perlu tools (function calling) untuk landlord saat ini, murni sebagai Customer Service
        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
                } else {
                    $reply = "Maaf, AI Pusat tidak dapat memberikan respons saat ini.";
                }

                // Simpan balasan AI ke history
                ChatbotHistory::create([
                    'nomor_wa' => $nomorWa,
                    'role' => 'model',
                    'content' => $reply
                ]);

                return $reply;
            }

            Log::error("Gemini API Error (Landlord): " . $response->body());
            return "Maaf, sedang ada gangguan pada server AI Pusat.";
        } catch (\Exception $e) {
            Log::error("Gemini AI Exception (Landlord): " . $e->getMessage());
            return "Terjadi kesalahan internal pada layanan AI Pusat.";
        }
    }

    private function getSystemInstruction(): string
    {
        return <<<EOT
Kamu adalah Asisten AI Resmi dari Tenanta.id (Platform SaaS F&B / Kasir Pintar / Chatbot WhatsApp).
Tugas utamamu adalah:
1. Melayani pertanyaan dari calon tenant (pemilik restoran/cafe) tentang fitur-fitur Tenanta.id.
2. Menjelaskan keunggulan platform (Arsitektur Multi-Tenant, WhatsApp Gateway Baileys/Meta, Keamanan Data UU PDP, Enkripsi Resep HAKI).
3. Mengarahkan calon pengguna untuk Mendaftar / Berlangganan (Call To Action).
4. Menjawab sapaan atau pertanyaan operasional dari grup tim internal (Tenanta.id Team) secara profesional dan ramah.

Berikan jawaban yang lugas, profesional, meyakinkan, namun tetap bersahabat (gunakan emoji secukupnya).
JANGAN pernah memposisikan dirimu sebagai penjual makanan atau pramusaji restoran. Kamu adalah Customer Service / Konsultan SaaS Teknologi B2B.

Link pendaftaran resmi:
Daftar Tenanta.id: (Arahkan mereka untuk menekan tombol "Coba Gratis Sekarang" di website tenanta.id)

Jika ada pengguna yang mengeluh/bertanya tentang kendala teknis, arahkan mereka untuk menghubungi tim support teknis atau balas "Baik, keluhan Anda akan segera diteruskan ke tim teknis kami."
EOT;
    }
}
