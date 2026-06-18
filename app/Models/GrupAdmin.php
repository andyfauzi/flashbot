<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class GrupAdmin extends Model
{
    use BelongsToTenant;

    protected $table = 'grup_admin';

    protected $fillable = [
        'grup_id', 'nomor_admin', 'nama_admin', 'ditambahkan_oleh'
    ];

    // Cek apakah nomor adalah admin grup
    public static function isAdmin(string $grupId, string $nomor): bool
    {
        return self::where('grup_id', $grupId)
            ->where('nomor_admin', $nomor)
            ->exists();
    }

    // Ambil semua admin grup
    public static function ambilSemuaAdmin(string $grupId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('grup_id', $grupId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Tambah admin baru
    public static function tambahAdmin(string $grupId, string $nomor, ?string $nama = null, ?string $ditambahkanOleh = null): self
    {
        return self::create([
            'grup_id'          => $grupId,
            'nomor_admin'      => $nomor,
            'nama_admin'       => $nama,
            'ditambahkan_oleh' => $ditambahkanOleh,
        ]);
    }

    // Hapus admin
    public static function hapusAdmin(string $grupId, string $nomor): bool
    {
        return self::where('grup_id', $grupId)
            ->where('nomor_admin', $nomor)
            ->delete() > 0;
    }
}