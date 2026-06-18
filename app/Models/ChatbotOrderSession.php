<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotOrderSession extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'chatbot_order_sessions';

    protected $fillable = [
        'nomor_wa',
        'langkah',
        'produk_id',
        'produk_varian_id',
        'tanggal_diambil',
        'jumlah',
        'nama_penerima',
        'alamat_penerima',
        'tipe_pengiriman',
        'metode_pembayaran',
        'addons'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'addons' => 'array'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function produkVarian()
    {
        return $this->belongsTo(ProdukVarian::class, 'produk_varian_id');
    }
}
