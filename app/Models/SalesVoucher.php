<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesVoucher extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'kode_voucher',
        'nama_sales',
        'no_wa_sales',
        'diskon_persen',
        'komisi_persen',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(TenantPayment::class, 'sales_voucher_id', 'id');
    }
}
