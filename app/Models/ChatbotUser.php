<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ChatbotUser extends Model
{
    use BelongsToTenant;

    public $allowLandlord = true;

    public $timestamps = false;
    protected $table = 'chatbot_users';

    protected $fillable = [
        'nomor', 'nama', 'langkah', 'pertama_chat', 'terakhir_chat'
    ];

    // Relasi ke pesan
    public function pesan()
    {
        return $this->hasMany(ChatbotPesan::class, 'nomor', 'nomor');
    }

    // Ambil atau buat user baru
    public static function ambilAtauBuat(string $nomor): self
    {
        $user = self::where('nomor', $nomor)->first();

        if (!$user) {
            $user = self::create([
                'nomor'        => $nomor,
                'langkah'      => 'menu',
                'pertama_chat' => now(),
                'terakhir_chat'=> now(),
            ]);
        } else {
            $user->update(['terakhir_chat' => now()]);
        }

        return $user;
    }
}
