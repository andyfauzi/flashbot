<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'meja_id',
        'nama_pelanggan',
        'nomor_telepon',
        'tanggal_waktu',
        'jumlah_orang',
        'catatan',
        'is_dp_required',
        'nominal_dp',
        'status_pembayaran_dp',
        'status',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
        'is_dp_required' => 'boolean',
        'nominal_dp' => 'decimal:2',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'reservasi_id');
    }
}
