<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'waktu_buka',
        'waktu_tutup',
        'modal_awal',
        'pengeluaran_kasir',
        'penambahan_kasir',
        'total_penjualan_tunai',
        'selisih_uang',
        'status',
    ];

    protected $casts = [
        'waktu_buka' => 'datetime',
        'waktu_tutup' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashFlows()
    {
        return $this->hasMany(CashFlow::class);
    }
}
