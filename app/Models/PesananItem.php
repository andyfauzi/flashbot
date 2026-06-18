<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pesanan_items';

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'produk_varian_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'addons',
        'catatan'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'float',
        'subtotal' => 'float',
        'addons' => 'array'
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function produkVarian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}
