<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Support\Facades\Crypt;

class ResepAddon extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'produk_addon_id',
        'bahan_baku_id',
        'qty_dipakai',
    ];

    /**
     * Mutator untuk mengenkripsi qty_dipakai sebelum disimpan
     */
    public function setQtyDipakaiAttribute($value)
    {
        $this->attributes['qty_dipakai'] = Crypt::encryptString((string)$value);
    }

    /**
     * Accessor untuk mendekripsi qty_dipakai saat dipanggil
     */
    public function getQtyDipakaiAttribute($value)
    {
        try {
            return (float) Crypt::decryptString($value);
        } catch (\Exception $e) {
            return 0; // Fallback jika gagal dekripsi
        }
    }

    public function produkAddon()
    {
        return $this->belongsTo(ProdukAddon::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
