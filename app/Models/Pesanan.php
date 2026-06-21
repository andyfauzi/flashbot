<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'pesanans';

    protected $fillable = [
        'nomor_order',
        'nomor_wa',
        'nama_penerima',
        'alamat_penerima',
        'tipe_pengiriman',
        'tanggal_diambil',
        'biaya_barang',
        'biaya_pengantaran',
        'total_biaya',
        'uang_muka',
        'metode_pembayaran',
        'status',
        'bukti_pembayaran',
        'source',
        'rating',
        'ulasan',
        'kurir_id',
        'nomor_hp',
        'meja_id',
        'nomor_antrian'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pesanan) {
            // Generate nomor antrian harian (berdasarkan tanggal server)
            if (empty($pesanan->nomor_antrian)) {
                $today = \Carbon\Carbon::today();
                $lastQueue = self::whereDate('created_at', $today)
                    ->when($pesanan->tenant_id, function($q) use ($pesanan) {
                        return $q->where('tenant_id', $pesanan->tenant_id);
                    })
                    ->max('nomor_antrian');
                
                $pesanan->nomor_antrian = $lastQueue ? $lastQueue + 1 : 1;
            }
        });
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function getNomorHpTampilAttribute()
    {
        if (!empty($this->nomor_hp)) {
            return $this->nomor_hp;
        }

        if (empty($this->nomor_wa) || $this->nomor_wa === '-') {
            return '-';
        }

        if (strpos($this->nomor_wa, '@') !== false) {
            $raw = explode('@', $this->nomor_wa)[0];
            if (strpos($this->nomor_wa, '@lid') !== false) {
                return "ID WhatsApp (LID): " . $raw;
            }
            return "+" . $raw;
        }

        return $this->nomor_wa;
    }

    protected $casts = [
        'biaya_barang' => 'float',
        'biaya_pengantaran' => 'float',
        'total_biaya' => 'float'
    ];

    public function items()
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }

    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'kurir_id');
    }

    public function getSisaPembayaranAttribute()
    {
        return max(0, $this->total_biaya - $this->uang_muka);
    }
}
