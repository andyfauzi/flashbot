<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukVarian extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'produk_varians';

    protected static function booted()
    {
        static::saved(function ($varian) {
            if (app()->bound('current_tenant')) {
                $tenantId = app('current_tenant')->id;
                \Illuminate\Support\Facades\Cache::tags(["tenant_{$tenantId}_produk"])->forget("pos_produks_{$tenantId}");
            }
        });
        static::deleted(function ($varian) {
            if (app()->bound('current_tenant')) {
                $tenantId = app('current_tenant')->id;
                \Illuminate\Support\Facades\Cache::tags(["tenant_{$tenantId}_produk"])->forget("pos_produks_{$tenantId}");
            }
        });
    }

    protected $fillable = [
        'produk_id',
        'nama_varian',
        'foto',
        'stok',
        'stok_proses_dapur',
        'harga',
        'hpp',
        'overhead_cost',
        'harga_kompetitor',
        'target_margin',
        'harga_rekomendasi'
    ];
    protected $casts = [
        'stok' => 'integer',
        'stok_proses_dapur' => 'integer',
        'harga' => 'float',
        'hpp' => 'float',
        'overhead_cost' => 'float',
        'harga_kompetitor' => 'float',
        'target_margin' => 'float',
        'harga_rekomendasi' => 'float'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepVarian::class, 'produk_varian_id');
    }
    
    // Helper untuk hitung ulang HPP
    public function hitungHpp()
    {
        $totalHpp = 0;
        foreach ($this->resep as $item) {
            if ($item->bahanBaku) {
                $totalHpp += ($item->qty_dipakai * $item->bahanBaku->harga_per_unit);
            }
        }
        $this->hpp = $totalHpp;
        
        $totalCost = $this->hpp + $this->overhead_cost;
        if ($this->target_margin > 0) {
            $marginValue = $totalCost * ($this->target_margin / 100);
            $this->harga_rekomendasi = $totalCost + $marginValue;
        } else {
            $this->harga_rekomendasi = $totalCost;
        }
        
        $this->save();
        return $this;
    }
}
