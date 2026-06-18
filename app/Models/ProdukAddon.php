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
}
