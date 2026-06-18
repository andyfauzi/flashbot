<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ChatbotPesan extends Model
{
    use BelongsToTenant;

    public $allowLandlord = true;

    public $timestamps = false;
    protected $table = 'chatbot_pesan';

    protected $fillable = ['nomor', 'arah', 'isi', 'waktu', 'media_url', 'media_type'];

    // Simpan pesan masuk dari user
    public static function simpanMasuk(string $nomor, string $isi, ?string $mediaUrl = null, ?string $mediaType = null): void
    {
        self::create([
            'nomor'      => $nomor,
            'arah'       => 'masuk',
            'isi'        => $isi,
            'media_url'  => $mediaUrl,
            'media_type' => $mediaType,
            'waktu'      => now(),
        ]);
    }

    // Simpan pesan keluar dari bot
    public static function simpanKeluar(string $nomor, string $isi, ?string $mediaUrl = null, ?string $mediaType = null): void
    {
        self::create([
            'nomor'      => $nomor,
            'arah'       => 'keluar',
            'isi'        => $isi,
            'media_url'  => $mediaUrl,
            'media_type' => $mediaType,
            'waktu'      => now(),
        ]);
    }
}
