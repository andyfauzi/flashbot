<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageMenu extends Model
{
    use HasFactory;

    protected $connection = 'landlord';
    protected $table = 'package_menus';

    protected $fillable = [
        'menu_key',
        'menu_label',
        'category',
        'gratis_enabled',
        'starter_enabled',
        'pro_enabled',
        'business_enabled',
    ];

    protected $casts = [
        'gratis_enabled' => 'boolean',
        'starter_enabled' => 'boolean',
        'pro_enabled' => 'boolean',
        'business_enabled' => 'boolean',
    ];
}
