<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory, BelongsToTenant;

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
        'hold_expires_at',
        'rejection_reason',
        'pre_order_items',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
        'is_dp_required' => 'boolean',
        'nominal_dp' => 'decimal:2',
        'hold_expires_at' => 'datetime',
        'pre_order_items' => 'array',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'reservasi_id');
    }

    public function isExpired()
    {
        if ($this->status !== 'on_hold') {
            return false;
        }

        return $this->hold_expires_at && $this->hold_expires_at->isPast();
    }
}
