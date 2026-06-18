<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GrupPengingat extends Model
{
    use BelongsToTenant;

    protected $table = 'grup_pengingat';

    protected $fillable = [
        'grup_id', 'dibuat_oleh', 'pesan',
        'waktu_ingatkan', 'sudah_dikirim'
    ];

    protected $casts = [
        'sudah_dikirim'  => 'boolean',
        'waktu_ingatkan' => 'datetime',
    ];

    // Buat pengingat baru
    public static function buat(string $grupId, string $dibuatOleh, string $pesan, string $waktuIngatkan): self
    {
        return self::create([
            'grup_id'        => $grupId,
            'dibuat_oleh'    => $dibuatOleh,
            'pesan'          => $pesan,
            'waktu_ingatkan' => $waktuIngatkan,
            'sudah_dikirim'  => false,
        ]);
    }

    // Ambil semua pengingat yang belum dikirim dan sudah H-10 menit
    public static function ambilYangHarusDikirim(): \Illuminate\Database\Eloquent\Collection
    {
        // now()->addMinutes(10) artinya jika sekarang jam 09:50, 
        // kita cari pengingat yang jam kegiatannya <= 10:00
        return self::where('sudah_dikirim', false)
            ->where('waktu_ingatkan', '<=', now()->addMinutes(10))
            ->get();
    }
}
