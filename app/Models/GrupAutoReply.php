<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupAutoReply extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['grup_id', 'keyword', 'balasan', 'is_exact_match', 'aktif'];

    public static function cariBalasan(string $grupId, string $teks)
    {
        // Cari exact match dulu
        $exactMatch = self::where('grup_id', $grupId)
            ->where('aktif', true)
            ->where('is_exact_match', true)
            ->where('keyword', $teks)
            ->first();

        if ($exactMatch) return $exactMatch->balasan;

        // Cari partial match (keyword is a substring of teks)
        $partialMatches = self::where('grup_id', $grupId)
            ->where('aktif', true)
            ->where('is_exact_match', false)
            ->get();

        foreach ($partialMatches as $pm) {
            if (stripos($teks, $pm->keyword) !== false) {
                return $pm->balasan;
            }
        }

        return null;
    }
}
