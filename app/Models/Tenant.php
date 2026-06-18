<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'owner_email',
        'subdomain',
        'database_name',
        'plan',
        'meta_access_token_encrypted',
        'feature_flags',
        'trial_ends_at',
        'plan_expires_at',
        'is_active',
    ];

    protected $casts = [
        'feature_flags' => 'array',
        'trial_ends_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
