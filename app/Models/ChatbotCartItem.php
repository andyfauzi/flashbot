<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotCartItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['cart_id', 'produk_id', 'produk_varian_id', 'jumlah', 'addons', 'catatan'];

    protected $casts = [
        'addons' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(ChatbotCart::class, 'cart_id');
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
