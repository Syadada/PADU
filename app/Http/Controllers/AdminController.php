<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\BackupService;
use App\Services\TwoFactorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard Audit Trail Forensik Digital (BSSN No. 4/2021)
     */
    public function auditLogsIndex(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', $search)
                  ->where('description', 'like', $search)
                  ->orWhere('ip_address', 'like', $search);
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Statistik Cepat Forensik
        $totalLogsToday = AuditLog::whereDate('created_at', Carbon::today())->count();
        $totalFailedAttempts = AuditLog::whereIn('action', ['AUTH_FAILED', 'AUTH_LOCKOUT', '2FA_FAILED'])->count();
        $totalLockouts = AuditLog::where('action', 'AUTH_LOCKOUT')->count();
        $totalBackups = AuditLog::where('action', 'BACKUP_CREATED')->count();

        $actionTypes = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs', compact(
            'logs',
            'totalLogsToday',
            'totalFailedAttempts',
            'totalLockouts',
            'totalBackups',
            'actionTypes'
        ));
    }

    /**
     * Ekspor Berkas Audit Trail ke CSV / TXT untuk Kepatuhan Audit Siber
     */
    public function exportAuditLogs(Request $request)
    {
        $format = $request->get('format', 'csv');
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->limit(5000)->get();

        AuditLogger::log(
            'AUDIT_LOG_EXPORT',
            "Super Admin mengekspor " . count($logs) . " entri audit trail format {$format}.",
            'SUCCESS'
        );

        $timestamp = date('Ymd_His');
        $fileName = "PADU_Audit_Trail_{$timestamp}.{$format}";

        if ($format === 'txt') {
            $content = "=== REKAMAN AUDIT TRAIL FORENSIK DIGITAL SISTEM INFORMASI PADU v2.0 ===\n"
                     . "Waktu Ekspor : " . date('Y-m-d H:i:s T') . "\n"
                     . "Otoritas     : " . Auth::user()->name . " (" . Auth::user()->email . ")\n"
                     . "Standar      : BSSN No. 4/2021 & UU PDP No. 27/2022\n"
                     . "Total Entri  : " . count($logs) . "\n"
                     . "========================================================================\n\n";

            foreach ($logs as $l) {
                $content .= sprintf(
                    "[%s] [%s] [%s] Aktor: %s (%s) | IP: %s | %s\n",
                    $l->created_at->format('Y-m-d H:i:s'),
                    $l->status,
                    $l->action,
                    $l->user_name ?? 'Anonim',
                    $l->user_role ?? '-',
                    $l->ip_address ?? '-',
                    $l->description
                );
            }

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        // CSV Format
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID', 'Waktu Kejadian', 'Status', 'Aksi Forensik', 'Nama Pengguna', 'Hak Akses / Role', 'IP Address', 'User Agent', 'Keterangan']);

            foreach ($logs as $l) {
                fputcsv($file, [
                    $l->id,
                    $l->created_at->format('Y-m-d H:i:s'),
                    $l->status,
                    $l->action,
                    $l->user_name ?? '-',
                    $l->user_role ?? '-',
                    $l->ip_address ?? '-',
                    $l->user_agent ?? '-',
                    $l->description,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Halaman Backup & Proteksi ZIP AES-256
     */
    public function backupIndex()
    {
        $backups = BackupService::listBackups();
        $disasterKey = 'PADU-DR-' . strtoupper(substr(hash('sha256', config('app.key') . 'DISASTER_RECOVERY'), 0, 16));
        return view('admin.backup', compact('backups', 'disasterKey'));
    }

    /**
     * Buat Cadangan Baru Terenkripsi AES-256 dengan Password Sekali Pakai 20 Karakter
     */
    public function createBackup()
    {
        try {
            $result = BackupService::createEncryptedBackup();

            AuditLogger::log(
                'BACKUP_CREATED',
                "Super Admin membuat arsip cadangan terenkripsi AES-256 ({$result['filename']}, {$result['size_formatted']}). Nilai hash SHA-256: {$result['sha256']}",
                'SUCCESS',
                [
                    'filename' => $result['filename'],
                    'sha256' => $result['sha256'],
                    'size' => $result['size_formatted'],
                ]
            );

            return redirect()->route('admin.backup')->with([
                'success' => 'Arsip cadangan data berhasil dibuat dan dilindungi standar enkripsi AES-256 BSSN!',
                'new_backup_password' => $result['password'],
                'new_backup_filename' => $result['filename'],
                'new_backup_sha256' => $result['sha256'],
                'new_backup_size' => $result['size_formatted'],
            ]);
        } catch (\Exception $e) {
            AuditLogger::log(
                'BACKUP_FAILED',
                "Pembuatan arsip cadangan GAGAL: " . $e->getMessage(),
                'FAILED'
            );

            return back()->withErrors(['backup' => 'Gagal membuat arsip backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Unduh Berkas ZIP Cadangan
     */
    public function downloadBackup($filename)
    {
        // Sanitasi nama file mencegah path traversal
        $filename = basename($filename);
        $filepath = storage_path('app/backups/' . $filename);

        if (!file_exists($filepath)) {
            abort(404, 'Berkas cadangan tidak ditemukan.');
        }

        AuditLogger::log(
            'BACKUP_DOWNLOADED',
            "Super Admin mengunduh berkas cadangan terenkripsi '{$filename}'.",
            'SUCCESS'
        );

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Manajemen Direktori Pengguna & Operator Staf
     */
    public function usersIndex()
    {
        $users = User::orderBy('role', 'desc')->orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Admin-Assisted Offline Password Reset untuk Staf Operator
     */
    public function resetOperatorPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin() && $user->id !== Auth::id()) {
            abort(403, 'Tidak dapat mereset akun sesama Super Admin.');
        }

        // Buat password acak min. 15 karakter
        $tempPassword = 'PADU-' . Str::random(5) . '!' . rand(100, 999) . strtoupper(Str::random(4));

        $user->password = Hash::make($tempPassword);
        $user->first_login = true; // Paksa ganti password saat login berikutnya
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        AuditLogger::log(
            'USER_RESET_BY_ADMIN',
            "Super Admin mereset kata sandi akun '{$user->name}' ({$user->email}). Akun diatur ke mode wajib ubah kata sandi pertama kali.",
            'SUCCESS',
            ['target_user_id' => $user->id]
        );

        return back()->with([
            'success' => "Kata sandi untuk pengguna '{$user->name}' berhasil di-reset!",
            'temp_user_name' => $user->name,
            'temp_user_email' => $user->email,
            'temp_password' => $tempPassword,
        ]);
    }

    /**
     * Buka Kunci Akun yang Terblokir Otomatis (Lockout Release)
     */
    public function unlockUser($id)
    {
        $user = User::findOrFail($id);
        $user->locked_until = null;
        $user->failed_login_attempts = 0;
        $user->save();

        AuditLogger::log(
            'USER_UNLOCKED_BY_ADMIN',
            "Super Admin membuka kunci akun '{$user->name}' ({$user->email}).",
            'SUCCESS',
            ['unlocked_user_id' => $user->id]
        );

        return back()->with('success', "Status blokir pada akun '{$user->name}' berhasil dibuka. Staf kini dapat masuk kembali.");
    }

    /**
     * Tampilkan Konfigurasi 2FA TOTP Super Admin
     */
    public function setupTwoFactorIndex()
    {
        $user = Auth::user();
        
        $secret = session('setup_2fa_secret') ?? TwoFactorService::generateSecret();
        session(['setup_2fa_secret' => $secret]);

        $recoveryKey = session('setup_2fa_recovery_key') ?? TwoFactorService::generateEmergencyRecoveryKey();
        session(['setup_2fa_recovery_key' => $recoveryKey]);

        $otpUri = TwoFactorService::getProvisioningUri('PADU Enterprise', $user->email, $secret);

        return view('admin.two-factor-setup', compact('user', 'secret', 'recoveryKey', 'otpUri'));
    }

    /**
     * Simpan & Aktifkan 2FA setelah Pengujian Token Berhasil
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secret = session('setup_2fa_secret');
        $recoveryKey = session('setup_2fa_recovery_key');

        if (!$secret || !$recoveryKey) {
            return back()->withErrors(['code' => 'Sesi setup 2FA telah kedaluwarsa. Silakan refresh halaman.']);
        }

        $isValid = TwoFactorService::verifyCode($secret, $request->code);

        if (!$isValid) {
            return back()->withErrors([
                'code' => 'Kode token 6 digit salah. Pastikan jam di ponsel dan komputer Anda sinkron.',
            ]);
        }

        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_hash = Hash::make($recoveryKey);
        $user->two_factor_enabled = true;
        $user->save();

        session()->forget(['setup_2fa_secret', 'setup_2fa_recovery_key']);

        AuditLogger::log(
            'TWO_FACTOR_ENABLED',
            "Super Admin '{$user->name}' berhasil mengaktifkan autentikasi dua faktor (2FA TOTP RFC 6238) dan mencetak lembar kunci fisik.",
            'SUCCESS'
        );

        return redirect()->route('admin.two-factor')->with('success', 'Autentikasi Dua Faktor (2FA HP Cerdas Offline) BERHASIL DIAKTIFKAN!');
    }

    /**
     * Nonaktifkan 2FA dengan Konfirmasi Kata Sandi
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi akun yang Anda masukkan salah.']);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_hash = null;
        $user->save();

        AuditLogger::log(
            'TWO_FACTOR_DISABLED',
            "Super Admin '{$user->name}' menonaktifkan fitur 2FA.",
            'WARNING'
        );

        return redirect()->route('admin.two-factor')->with('warning', 'Autentikasi Dua Faktor (2FA) telah dinonaktifkan.');
    }
}
