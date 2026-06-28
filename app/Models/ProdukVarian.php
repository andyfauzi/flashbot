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
        'resep_yield',
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
        'resep_yield' => 'integer',
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
        // Hitung total modal bahan baku untuk 1x resep adonan
        $totalModalResep = 0;
        foreach ($this->resep as $r) {
            $bahan = $r->bahanBaku;
            if ($bahan && $bahan->harga_per_unit > 0) {
                // Harga = qty yang dipakai * harga_per_unit
                $totalModalResep += ($r->qty_dipakai * $bahan->harga_per_unit);
            }
        }
        
        // HPP = Total modal resep dibagi dengan porsi yang dihasilkan (yield)
        $yield = $this->resep_yield > 0 ? $this->resep_yield : 1;
        $this->hpp = $totalModalResep / $yield;
        
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
