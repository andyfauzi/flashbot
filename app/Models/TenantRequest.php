<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantRequest extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'store_name',
        'subdomain',
        'owner_name',
        'email',
        'whatsapp_number',
        'store_address',
        'jenis_layanan',
        'skala_bisnis',
        'plan',
        'is_trial',
        'google_id',
        'status',
    ];

    protected $casts = [
        'is_trial' => 'boolean',
    ];
}
