<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GrupCatatan extends Model
{
    use BelongsToTenant;

    public $timestamps = false;
    protected $table = 'grup_catatan';

    protected $fillable = [
        'grup_id', 'disimpan_oleh', 'isi', 'tag', 'waktu'
    ];

    // Simpan catatan baru
    public static function simpan(string $grupId, string $disimpanOleh, string $isi, ?string $tag = null): self
    {
        return self::create([
            'grup_id'      => $grupId,
            'disimpan_oleh'=> $disimpanOleh,
            'isi'          => $isi,
            'tag'          => $tag,
            'waktu'        => now(),
        ]);
    }

    // Ambil semua catatan grup
    public static function ambilSemua(string $grupId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('grup_id', $grupId)
            ->orderBy('waktu', 'desc')
            ->get();
    }

    // Hapus catatan berdasarkan ID
    public static function hapusById(string $grupId, int $id): bool
    {
        return self::where('grup_id', $grupId)->where('id', $id)->delete() > 0;
    }
}
