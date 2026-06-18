<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentitasToko extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nama_toko',
        'alamat_toko',
        'nomor_telepon',
        'pesan_footer',
        'logo_path',
        'nomor_rekening',
        'qris_path',
        'tema_portal',
        'tema_desktop',
        'is_broadcast_approved',
        'nama_bot',
        'karakter_bot',
        'jenis_layanan',
        'wajib_dp_reservasi',
    ];

    protected $casts = [
        'is_broadcast_approved' => 'boolean',
        'wajib_dp_reservasi' => 'boolean',
    ];
}