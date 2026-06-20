<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPayment extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function salesVoucher()
    {
        return $this->belongsTo(SalesVoucher::class, 'sales_voucher_id', 'id');
    }
}
