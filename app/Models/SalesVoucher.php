<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesVoucher extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'user_id',
        'kode_voucher',
        'nama_sales',
        'no_wa_sales',
        'diskon_persen',
        'komisi_persen',
        'target_paket',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(TenantPayment::class, 'sales_voucher_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
