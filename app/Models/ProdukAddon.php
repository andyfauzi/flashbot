<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukAddon extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['produk_id', 'nama_addon', 'harga', 'aktif', 'butuh_teks'];

    protected $casts = [
        'harga' => 'integer',
        'aktif' => 'boolean',
        'butuh_teks' => 'boolean'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function reseps()
    {
        return $this->hasMany(ResepAddon::class);
    }

    public function getHppAttribute()
    {
        $hpp = 0;
        if ($this->relationLoaded('reseps')) {
            foreach ($this->reseps as $resep) {
                if ($resep->relationLoaded('bahanBaku') && $resep->bahanBaku) {
                    $hpp += $resep->bahanBaku->harga_satuan * $resep->qty_dipakai;
                }
            }
        }
        return $hpp;
    }
}
