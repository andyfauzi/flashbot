<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResepVarian extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'resep_varians';

    protected $fillable = [
        'produk_varian_id',
        'bahan_baku_id',
        'qty_dipakai'
    ];

    protected $casts = [
        // 'qty_dipakai' => 'float' // Di-handle oleh accessor
    ];

    public function setQtyDipakaiAttribute($value)
    {
        $this->attributes['qty_dipakai'] = \Illuminate\Support\Facades\Crypt::encryptString((string)$value);
    }

    public function getQtyDipakaiAttribute($value)
    {
        try {
            return (float) \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Exception $e) {
            return (float) $value;
        }
    }

    public function varian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
