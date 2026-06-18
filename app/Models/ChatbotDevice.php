<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'chatbot_devices';
    
    protected $fillable = [
        'nama_device', 'nomor', 'session_id', 'status', 'is_default', 'pesan_sapaan', 'menu_type'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];
}
