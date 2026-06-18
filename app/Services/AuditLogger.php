<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Catat aksi krusial ke tabel audit_logs.
     *
     * @param string $action Nama aksi (misal: 'tenant.suspended', 'admin.login.failed')
     * @param string $target Target entitas yang diubah (misal: 'tenant:1')
     * @param array $details Informasi ekstra (opsional)
     * @return void
     */
    public static function record(string $action, string $target = '', array $details = [])
    {
        try {
            $connection = config('database.default');
            // Pastikan menggunakan landlord connection tanpa mengganggu flow saat ini
            
            AuditLog::create([
                'action'     => $action,
                'actor_id'   => auth()->id() ?? null,
                'target'     => $target,
                'ip'         => Request::ip(),
                'user_agent' => Request::userAgent(),
                'details'    => empty($details) ? null : $details,
            ]);
        } catch (\Exception $e) {
            // Silently ignore audit log failures so it doesn't break the main flow
            // But log to laravel log just in case
            \Illuminate\Support\Facades\Log::error('Gagal mencatat audit log: ' . $e->getMessage());
        }
    }
}
