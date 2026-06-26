<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['nama', 'deskripsi', 'aktif', 'foto', 'icon'];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
