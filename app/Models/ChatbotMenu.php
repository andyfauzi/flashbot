<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ChatbotMenu extends Model
{
    use BelongsToTenant;

    protected $table = 'chatbot_menu';

    protected $fillable = ['kode', 'parent_kode', 'judul', 'isi', 'aktif', 'urutan', 'media_url', 'media_type', 'tipe_pesan', 'pilihan_interaktif', 'device_id'];

    protected $casts = [
        'aktif' => 'boolean',
        'pilihan_interaktif' => 'array'
    ];

    public function device()
    {
        return $this->belongsTo(ChatbotDevice::class);
    }

    // Ambil semua menu aktif
    public static function ambilAktif(?string $deviceId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('aktif', true);
        if ($deviceId) {
            $device = \App\Models\ChatbotDevice::where('session_id', $deviceId)->first();
            if ($device) {
                $query->where(function($q) use ($device) {
                    $q->where('device_id', $device->id)
                      ->orWhereNull('device_id');
                });
            } else {
                $query->whereNull('device_id');
            }
        } else {
            $query->whereNull('device_id');
        }
        return $query->orderBy('urutan')->get();
    }

    // Bangun teks menu utama secara dinamis dari database
    public static function buildMenuUtama(?string $deviceId = null): string
    {
        $menus = self::ambilAktif($deviceId);
        $angka = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣'];

        $pesanSapaan = "🤖 *Selamat datang di Chatbot Kami!*\n\nSilakan pilih menu:\n";

        if ($deviceId) {
            $device = \App\Models\ChatbotDevice::where('session_id', $deviceId)->first();
            if ($device && $device->pesan_sapaan) {
                $pesanSapaan = $device->pesan_sapaan . "\n\n";
            }
        }

        $teks = $pesanSapaan;
        foreach ($menus as $i => $menu) {
            $emoji = $angka[$i] ?? "▪️";
            $teks .= "{$emoji} {$menu->judul}\n";
        }
        $teks .= "0️⃣ Kembali ke Menu Utama\n\nBalas dengan angka pilihan kamu.";

        return $teks;
    }

    // Ambil daftar menu sebagai array options untuk Button/List interaktif
    public static function getMenuOptions(?string $deviceId = null): array
    {
        $menus = self::ambilAktif($deviceId);
        $options = [];
        foreach ($menus as $menu) {
            $options[] = [
                'id'   => $menu->kode,
                'text' => $menu->judul,
                'desc' => '',
            ];
        }
        return $options;
    }
}
