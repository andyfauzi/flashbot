<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanKonversi extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'satuan_konversis';

    protected $fillable = [
        'satuan_awal',
        'satuan_akhir',
        'nilai_konversi',
        'keterangan',
    ];
}
