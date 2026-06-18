<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['grup_id', 'kunci', 'nilai'];

    public static function ambil(string $grupId, string $kunci, string $default = '')
    {
        $setting = self::where('grup_id', $grupId)->where('kunci', $kunci)->first();
        return $setting ? $setting->nilai : $default;
    }

    public static function simpan(string $grupId, string $kunci, string $nilai)
    {
        return self::updateOrCreate(
            ['grup_id' => $grupId, 'kunci' => $kunci],
            ['nilai' => $nilai]
        );
    }
}
