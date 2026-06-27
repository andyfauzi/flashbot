<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'bahan_bakus';

    protected $fillable = [
        'nama_bahan',
        'kategori',
        'satuan',
        'harga_beli',
        'qty_beli',
        'harga_per_unit',
        'stok'
    ];

    protected $casts = [
        'harga_beli' => 'float',
        'qty_beli' => 'float',
        'harga_per_unit' => 'float',
        'stok' => 'float'
    ];

    public function histories()
    {
        return $this->hasMany(StokBahanHistory::class, 'bahan_baku_id');
    }
}
