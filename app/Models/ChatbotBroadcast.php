<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotBroadcast extends Model
{
    use HasFactory;

    protected $table = 'chatbot_broadcasts';

    protected $fillable = [
        'judul',
        'isi_pesan',
        'media_url',
        'media_type',
        'status',
        'total_penerima',
        'target_filter',
        'meta_template_name',
    ];
}
