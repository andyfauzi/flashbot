<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, \App\Traits\EnforcesLimits;

    /**
     * Tentukan koneksi database secara dinamis.
     * Jika diakses dari subdomain tenant, gunakan koneksi tenant.
     * Jika diakses dari domain utama, gunakan koneksi landlord.
     */
    public function getConnectionName()
    {
        if (app()->bound('current_tenant_connection')) {
            return app('current_tenant_connection');
        }
        return 'landlord';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'device_id',
        'google_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(ChatbotDevice::class, 'device_id');
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->role === 'owner';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    /**
     * Kompatibilitas dengan pengecekan permission lawas
     */
    public function hasPermission($permission)
    {
        if ($this->role === 'owner') {
            return true; // Owner selalu bisa
        }

        $map = [
            'pos' => 'akses_pos',
            'jadwal' => 'akses_laporan',
            'produk' => 'akses_hpp',
            'stok' => 'akses_hpp',
            'keuangan' => 'akses_kas'
        ];

        $spatiePerm = $map[$permission] ?? 'akses_' . $permission;

        try {
            return $this->hasPermissionTo($spatiePerm);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

}
