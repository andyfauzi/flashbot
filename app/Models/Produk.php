<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory, \App\Traits\EnforcesLimits, BelongsToTenant;

    protected $table = 'produks';

    protected static function booted()
    {
        static::saved(function ($produk) {
            if (app()->bound('current_tenant')) {
                $tenantId = app('current_tenant')->id;
                \Illuminate\Support\Facades\Cache::tags(["tenant_{$tenantId}_produk"])->forget("pos_produks_{$tenantId}");
            }
        });
        static::deleted(function ($produk) {
            if (app()->bound('current_tenant')) {
                $tenantId = app('current_tenant')->id;
                \Illuminate\Support\Facades\Cache::tags(["tenant_{$tenantId}_produk"])->forget("pos_produks_{$tenantId}");
            }
        });
    }

    protected $fillable = [
        'kode',
        'kategori_id',
        'is_made_to_order',
        'nama',
        'deskripsi',
        'size_chart',
        'harga',
        'foto',
        'stok',
        'stok_proses_dapur',
        'aktif',
        'promo_min_qty',
        'promo_harga'
    ];

    protected $casts = [
        'harga' => 'float',
        'stok' => 'integer',
        'stok_proses_dapur' => 'integer',
        'aktif' => 'boolean',
        'is_made_to_order' => 'boolean',
        'promo_min_qty' => 'integer',
        'promo_harga' => 'float'
    ];

    public function varians()
    {
        return $this->hasMany(ProdukVarian::class, 'produk_id');
    }

    public function addons()
    {
        return $this->hasMany(ProdukAddon::class, 'produk_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function getAverageRatingAttribute()
    {
        $avg = \Illuminate\Support\Facades\DB::table('pesanans')
            ->join('pesanan_items', 'pesanans.id', '=', 'pesanan_items.pesanan_id')
            ->where('pesanan_items.produk_id', $this->id)
            ->whereNotNull('pesanans.rating')
            ->avg('pesanans.rating');

        return $avg ? round($avg, 1) : 0;
    }

    public function getReviewCountAttribute()
    {
        return \Illuminate\Support\Facades\DB::table('pesanans')
            ->join('pesanan_items', 'pesanans.id', '=', 'pesanan_items.pesanan_id')
            ->where('pesanan_items.produk_id', $this->id)
            ->whereNotNull('pesanans.rating')
            ->count();
    }
}
