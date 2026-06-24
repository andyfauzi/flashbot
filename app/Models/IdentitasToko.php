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
        'midtrans_server_key',
        'midtrans_client_key',
        'midtrans_is_production',
        'is_midtrans_active',
        'gemini_api_key',
        'whatsapp_gateway',
        'meta_phone_number_id',
        'meta_access_token',
        'jam_buka',
        'jam_tutup',
        'nominal_dp_reservasi',
        'minimal_jam_reservasi',
        'hold_duration_hours',
        'max_pax_per_reservation',
        'syarat_ketentuan_portal',
        'kontak_portal',
        'hero_image_path',
        'deskripsi_toko',
        'galeri_paths',
        'zona_waktu',
        'gemini_usage_count',
    ];

    protected $casts = [
        'is_broadcast_approved' => 'boolean',
        'wajib_dp_reservasi' => 'boolean',
        'midtrans_is_production' => 'boolean',
        'is_midtrans_active' => 'boolean',
        'galeri_paths' => 'array',
    ];
}