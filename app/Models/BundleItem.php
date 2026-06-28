<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class BundleItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'bundle_items';

    protected $fillable = [
        'produk_id',
        'produk_varian_id',
        'qty'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function varian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}
