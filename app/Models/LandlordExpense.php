<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordExpense extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $fillable = [
        'tanggal',
        'nama_pengeluaran',
        'kategori',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];
}
