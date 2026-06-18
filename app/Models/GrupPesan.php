<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GrupPesan extends Model
{
    use BelongsToTenant;

    public $timestamps = false;
    protected $table = 'grup_pesan';

    protected $fillable = [
        'grup_id', 'grup_nama', 'pengirim',
        'nama_pengirim', 'pesan', 'waktu'
    ];

    // Simpan pesan masuk dari grup
    public static function simpan(string $grupId, string $pengirim, string $pesan, ?string $grupNama = null, ?string $namaPengirim = null): void
    {
        self::create([
            'grup_id'       => $grupId,
            'grup_nama'     => $grupNama,
            'pengirim'      => $pengirim,
            'nama_pengirim' => $namaPengirim,
            'pesan'         => $pesan,
            'waktu'         => now(),
        ]);
    }

    // Cari pesan berdasarkan kata kunci
    public static function cari(string $grupId, string $keyword): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('grup_id', $grupId)
            ->where('pesan', 'like', "%{$keyword}%")
            ->orderBy('waktu', 'desc')
            ->limit(10)
            ->get();
    }
}
