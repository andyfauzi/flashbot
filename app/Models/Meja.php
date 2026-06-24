<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Meja extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nomor_meja',
        'nama_meja',
        'kapasitas',
        'status',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function reservasis()
    {
        return $this->hasMany(Reservasi::class);
    }

    public function isAvailableFor($date, $time, $pax)
    {
        if (!$this->is_active || $this->kapasitas < $pax) {
            return false;
        }

        $datetime = Carbon::parse($date . ' ' . $time);
        $startWindow = $datetime->copy()->subHour();
        $endWindow = $datetime->copy()->addHours(2);

        $conflicts = $this->reservasis()
            ->whereDate('tanggal_waktu', $date)
            ->whereTime('tanggal_waktu', '>', $startWindow->format('H:i:s'))
            ->whereTime('tanggal_waktu', '<', $endWindow->format('H:i:s'))
            ->where(function ($q) {
                $q->where('status', 'dikonfirmasi')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'on_hold')
                         ->where('hold_expires_at', '>', now());
                  });
            })
            ->count();

        return $conflicts === 0;
    }
}
