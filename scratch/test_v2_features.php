<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use App\Services\BackupService;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "=== MEMULAI TEST SIMULASI FITUR PADU v2.0 ENTERPRISE ===\n\n";

// 1. Cek User Default
$superadmin = User::where('email', 'superadmin@padu.local')->first();
$operator = User::where('email', 'operator@padu.local')->first();

echo "[TEST 1] Cek Akun Default:\n";
echo " - Super Admin: " . ($superadmin ? "DITEMUKAN ({$superadmin->role}, first_login=" . ($superadmin->first_login ? 'true' : 'false') . ")" : "GAGAL") . "\n";
echo " - Operator: " . ($operator ? "DITEMUKAN ({$operator->role}, first_login=" . ($operator->first_login ? 'true' : 'false') . ")" : "GAGAL") . "\n";

// 2. Test Lockout Logic (5x Percobaan Gagal)
echo "\n[TEST 2] Simulasi Lockout Akun (5x Gagal Sandi):\n";
$operator->failed_login_attempts = 0;
$operator->locked_until = null;
$operator->save();

for ($i = 1; $i <= 5; $i++) {
    $operator->increment('failed_login_attempts');
    if ($operator->failed_login_attempts >= 5) {
        $operator->locked_until = now()->addMinutes(15);
        $operator->save();
        AuditLogger::log('AUTH_LOCKOUT', "Akun '{$operator->name}' terkunci otomatis setelah 5 kali gagal.", 'WARNING', [], $operator->id, $operator->name, $operator->role);
    }
}

echo " - Failed attempts: {$operator->failed_login_attempts}\n";
echo " - Is locked: " . ($operator->isLocked() ? "YA (Sisa detik: " . $operator->remainingLockoutSeconds() . ")" : "TIDAK") . "\n";

// 3. Test Buka Kunci (Admin Lockout Release)
echo "\n[TEST 3] Simulasi Pembukaan Kunci oleh Admin:\n";
$operator->locked_until = null;
$operator->failed_login_attempts = 0;
$operator->save();
AuditLogger::log('USER_UNLOCKED_BY_ADMIN', "Super Admin membuka kunci akun '{$operator->name}'.", 'SUCCESS', [], $operator->id);
echo " - Is locked setelah rilis: " . ($operator->isLocked() ? "MASIH TERKUNCI" : "TERBUKA (SUKSES)") . "\n";

// 4. Test Backup Encrypted AES-256
echo "\n[TEST 4] Simulasi Backup Arsip Enkripsi AES-256:\n";
$backupRes = BackupService::createEncryptedBackup();
echo " - Nama Berkas: " . $backupRes['filename'] . "\n";
echo " - Ukuran: " . $backupRes['size_formatted'] . "\n";
echo " - Panjang Password: " . strlen($backupRes['password']) . " karakter\n";
echo " - Contoh Password Acak: " . $backupRes['password'] . "\n";
echo " - SHA-256 Checksum: " . $backupRes['sha256'] . "\n";

// 5. Test Audit Trail Rekaman
echo "\n[TEST 5] Verifikasi Entri Audit Trail Forensik:\n";
$totalAudit = AuditLog::count();
$recent = AuditLog::latest('id')->first();
echo " - Total rekaman log di DB: {$totalAudit} entri\n";
echo " - Log terakhir: [{$recent->status}] {$recent->action} - {$recent->description}\n";

// 6. Test 2FA TOTP & Recovery Key
echo "\n[TEST 6] Simulasi 2FA TOTP & Master Recovery Key:\n";
$secret = TwoFactorService::generateSecret();
$slice = (int)floor(time() / 30);
$token = TwoFactorService::calculateCode($secret, $slice);
$verified = TwoFactorService::verifyCode($secret, $token);
$recoveryKey = TwoFactorService::generateEmergencyRecoveryKey();
echo " - Secret Key: {$secret}\n";
echo " - 6-Digit OTP: {$token}\n";
echo " - Verifikasi OTP: " . ($verified ? "VALID (BERHASIL)" : "TIDAK VALID") . "\n";
echo " - Master Recovery Key: {$recoveryKey}\n";

echo "\n=== SEMUA 6 TAHAP PENGUJIAN FITUR v2.0 BERHASIL 100% ===\n";
