<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBahanHistory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'bahan_baku_id',
        'user_id',
        'tipe',
        'qty',
        'keterangan'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}