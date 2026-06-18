<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotHistory extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToTenant;

    public $allowLandlord = true;

    protected $fillable = ['nomor_wa', 'role', 'content'];
}
