<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Catat aktivitas pengguna secara permanen ke Audit Trail Forensik
     *
     * @param string $action Kode aksi, contoh: AUTH_LOGIN, AUTH_LOCKOUT, DATA_IMPORT, BACKUP_CREATED
     * @param string $description Penjelasan naratif aksi
     * @param string $status SUCCESS | FAILED | WARNING
     * @param array $metadata Informasi tambahan konteks
     * @param int|null $forcedUserId ID user jika belum login (misal percobaan login gagal)
     * @param string|null $forcedUserName Nama user override
     * @param string|null $forcedRole Role user override
     * @return AuditLog
     */
    public static function log(
        string $action,
        string $description,
        string $status = 'SUCCESS',
        array $metadata = [],
        ?int $forcedUserId = null,
        ?string $forcedUserName = null,
        ?string $forcedRole = null
    ): AuditLog {
        $user = Auth::user();
        
        $userId = $forcedUserId ?? ($user ? $user->id : null);
        $userName = $forcedUserName ?? ($user ? $user->name : 'Sistem / Anonim');
        $userRole = $forcedRole ?? ($user ? $user->role : 'guest');

        return AuditLog::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => strtoupper($action),
            'description' => $description,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr(Request::userAgent() ?? 'Unknown Agent', 0, 500),
            'status' => strtoupper($status),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
