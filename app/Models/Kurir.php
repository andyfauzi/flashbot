<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'kurirs';

    protected $fillable = [
        'nama',
        'nomor_hp'
    ];
}
