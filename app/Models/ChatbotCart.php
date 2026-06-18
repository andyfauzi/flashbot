<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotCart extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['nomor_wa', 'nama_draft', 'alamat_draft', 'tanggal_diambil_draft'];

    public function items()
    {
        return $this->hasMany(ChatbotCartItem::class, 'cart_id');
    }
}
