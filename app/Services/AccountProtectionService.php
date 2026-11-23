<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AccountProtectionService
{
    private static string $vaultPath = '';
    private static string $backupVaultPath = '';
    private static string $lockPath = '';

    private static function getVaultPath(): string
    {
        if (empty(self::$vaultPath)) {
            self::$vaultPath = database_path('accounts_vault.json');
        }
        return self::$vaultPath;
    }

    private static function getBackupVaultPath(): string
    {
        if (empty(self::$backupVaultPath)) {
            self::$backupVaultPath = storage_path('app/accounts_vault.json');
        }
        return self::$backupVaultPath;
    }

    private static function getLockPath(): string
    {
        if (empty(self::$lockPath)) {
            self::$lockPath = database_path('device_lock.json');
        }
        return self::$lockPath;
    }

    /**
     * Dapatkan sidik jari perangkat keras / laptop unik saat ini (MachineGuid + ComputerName + Host IP)
     */
    public static function getDeviceFingerprint(): array
    {
        $machineGuid = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $regCmd = 'C:\\Windows\\System32\\reg.exe query "HKLM\\SOFTWARE\\Microsoft\\Cryptography" /v MachineGuid 2>nul';
            $out = @shell_exec($regCmd);
            if ($out && preg_match('/MachineGuid\s+REG_SZ\s+([a-zA-Z0-9-]+)/i', $out, $matches)) {
                $machineGuid = trim($matches[1]);
            }
        }

        $computerName = getenv('COMPUTERNAME') ?: (gethostname() ?: 'UNKNOWN_HOST');
        $computerName = strtoupper(trim($computerName));
        $hostIp = @gethostbyname($computerName) ?: '127.0.0.1';

        $rawIdentity = ($machineGuid ?: 'NO_GUID') . '###' . $computerName;
        $deviceId = hash('sha256', $rawIdentity);

        return [
            'device_id' => $deviceId,
            'machine_guid' => $machineGuid,
            'computer_name' => $computerName,
            'ip' => $hostIp,
        ];
    }

    /**
     * Terapkan pengikatan perangkat (Device Binding).
     * Jika aplikasi dibuka di laptop/komputer lain yang berbeda (misal hasil copy/share zip):
     * 1. Otomatis reset database akun: Hapus semua akun selain PADU@local.id
     * 2. Reset akun PADU@local.id ke status awal & kata sandi bawaan (PaduKalkulator1#)
     * 3. Kosongkan sisa dataset lama dan kunci ke laptop baru tersebut.
     */
    public static function enforceDeviceBinding(): void
    {
        try {
            $currentDevice = self::getDeviceFingerprint();
            $lockFile = self::getLockPath();

            $isDifferentLaptop = false;

            if (file_exists($lockFile)) {
                $storedData = json_decode(file_get_contents($lockFile), true);
                if (!empty($storedData['device_id']) && $storedData['device_id'] !== $currentDevice['device_id']) {
                    $isDifferentLaptop = true;
                }
            } else {
                // Belum ada file lock, buat baru dan ikat ke laptop ini
                self::saveDeviceLock($currentDevice);
            }

            if ($isDifferentLaptop) {
                Log::warning("[DEVICE_GUARD] Terdeteksi aplikasi dipindahkan/di-share ke laptop baru ({$currentDevice['computer_name']}). Menjalankan reset akun ke standar awal.");

                // 1. Pastikan tabel users siap
                self::ensureTableStructure();

                // 2. Hapus seluruh akun pengguna selain padu@local.id
                DB::table('users')->where('email', '!=', 'padu@local.id')->delete();

                // 3. Reset akun padu@local.id ke kata sandi awal
                DB::table('users')->updateOrInsert(
                    ['email' => 'padu@local.id'],
                    [
                        'id' => 1,
                        'name' => 'PADU',
                        'email' => 'padu@local.id',
                        'password' => Hash::make('PaduKalkulator1#'),
                        'role' => 'superadmin',
                        'first_login' => true,
                        'failed_login_attempts' => 0,
                        'locked_until' => null,
                        'two_factor_enabled' => false,
                        'two_factor_secret' => null,
                        'two_factor_recovery_hash' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // 4. Kosongkan residu dataset analitik dari laptop sebelumnya
                try {
                    \App\Http\Controllers\DtsenController::executeDataPurge();
                } catch (\Throwable $e) {}

                // 5. Perbarui brankas vault akun hanya berisi akun awal
                self::backupAccounts();

                // 6. Ikat lisensi/kunci ke laptop baru
                self::saveDeviceLock($currentDevice);

                Log::info("[DEVICE_GUARD] Reset perangkat baru selesai. Hanya akun awal PADU@local.id yang tersedia.");
            } else {
                // Laptop yang sama: pastikan akun default tetap ada & cadangkan
                self::ensureDefaultAccountExists();
            }
        } catch (\Throwable $e) {
            Log::error('[DEVICE_GUARD] Gagal menerapkan proteksi perangkat: ' . $e->getMessage());
        }
    }

    /**
     * Simpan status identitas laptop ke database/device_lock.json
     */
    public static function saveDeviceLock(array $deviceInfo): void
    {
        try {
            $lockFile = self::getLockPath();
            if (!file_exists(dirname($lockFile))) {
                @mkdir(dirname($lockFile), 0777, true);
            }

            $payload = [
                'device_id' => $deviceInfo['device_id'],
                'computer_name' => $deviceInfo['computer_name'],
                'machine_guid' => $deviceInfo['machine_guid'],
                'ip_address' => $deviceInfo['ip'],
                'bound_at' => date('Y-m-d H:i:s'),
                'note' => 'Aplikasi terkunci pada laptop ini. Jika di-share ke laptop lain, sistem akan otomatis mereset akun ke default PADU@local.id.',
            ];

            file_put_contents($lockFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            Log::warning('[DEVICE_GUARD] Gagal menyimpan device_lock.json: ' . $e->getMessage());
        }
    }

    /**
     * Pastikan struktur SQLite dan tabel users tersedia
     */
    public static function ensureTableStructure(): void
    {
        $sqlitePath = database_path('database.sqlite');
        if (!file_exists($sqlitePath) || filesize($sqlitePath) === 0) {
            if (!file_exists(dirname($sqlitePath))) {
                @mkdir(dirname($sqlitePath), 0777, true);
            }
            @touch($sqlitePath);
            Artisan::call('migrate', ['--force' => true]);
        }

        if (!Schema::hasTable('users')) {
            Artisan::call('migrate', ['--force' => true]);
        }
    }

    /**
     * Memastikan database SQLite memiliki tabel users dan minimal akun administrator utama selalu ada.
     */
    public static function ensureDefaultAccountExists(): void
    {
        try {
            self::ensureTableStructure();

            $userCount = DB::table('users')->count();

            if ($userCount === 0) {
                $restored = self::restoreFromVault();
                if (!$restored) {
                    self::createInitialDefaultAccount();
                }
            } else {
                $hasAdmin = DB::table('users')->where('email', 'padu@local.id')->exists();
                if (!$hasAdmin) {
                    self::createInitialDefaultAccount();
                }
                self::backupAccounts();
            }
        } catch (\Throwable $e) {
            Log::error('[ACCOUNT_GUARD] Gagal memastikan integritas akun: ' . $e->getMessage());
        }
    }

    /**
     * Buat akun administrator utama awal (PADU / padu@local.id / PaduKalkulator1#)
     */
    public static function createInitialDefaultAccount(): void
    {
        try {
            DB::table('users')->updateOrInsert(
                ['email' => 'padu@local.id'],
                [
                    'id' => 1,
                    'name' => 'PADU',
                    'email' => 'padu@local.id',
                    'password' => Hash::make('PaduKalkulator1#'),
                    'role' => 'superadmin',
                    'first_login' => false,
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'two_factor_enabled' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            self::backupAccounts();
            Log::info('[ACCOUNT_GUARD] Akun awal resmi PADU (padu@local.id) berhasil diamankan ke sistem.');
        } catch (\Throwable $e) {
            Log::error('[ACCOUNT_GUARD] Gagal membuat akun default: ' . $e->getMessage());
        }
    }

    /**
     * Simpan snapshot seluruh akun pengguna ke brankas persisten (accounts_vault.json)
     */
    public static function backupAccounts(): void
    {
        try {
            if (!Schema::hasTable('users')) {
                return;
            }

            $users = DB::table('users')->get()->map(function ($u) {
                return (array)$u;
            })->toArray();

            if (empty($users)) {
                return;
            }

            $jsonData = json_encode([
                'version' => '2.5',
                'updated_at' => date('Y-m-d H:i:s'),
                'users_count' => count($users),
                'users' => $users,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            file_put_contents(self::getVaultPath(), $jsonData);

            $backupPath = self::getBackupVaultPath();
            if (!file_exists(dirname($backupPath))) {
                @mkdir(dirname($backupPath), 0777, true);
            }
            file_put_contents($backupPath, $jsonData);
        } catch (\Throwable $e) {
            Log::warning('[ACCOUNT_GUARD] Gagal backup akun ke vault: ' . $e->getMessage());
        }
    }

    /**
     * Pulihkan seluruh akun pengguna dari brankas persisten (accounts_vault.json)
     */
    public static function restoreFromVault(): bool
    {
        try {
            $vaultPath = file_exists(self::getVaultPath()) 
                ? self::getVaultPath() 
                : (file_exists(self::getBackupVaultPath()) ? self::getBackupVaultPath() : null);

            if (!$vaultPath) {
                return false;
            }

            $content = file_get_contents($vaultPath);
            $data = json_decode($content, true);

            if (empty($data) || empty($data['users']) || !is_array($data['users'])) {
                return false;
            }

            foreach ($data['users'] as $u) {
                if (empty($u['email']) || empty($u['password'])) {
                    continue;
                }
                
                $email = $u['email'];
                $exists = DB::table('users')->where('email', $email)->exists();
                if (!$exists) {
                    DB::table('users')->insert([
                        'id' => $u['id'] ?? null,
                        'name' => $u['name'] ?? 'Pengguna PADU',
                        'email' => $email,
                        'password' => $u['password'],
                        'role' => $u['role'] ?? 'superadmin',
                        'first_login' => !empty($u['first_login']),
                        'failed_login_attempts' => (int)($u['failed_login_attempts'] ?? 0),
                        'locked_until' => $u['locked_until'] ?? null,
                        'two_factor_enabled' => !empty($u['two_factor_enabled']),
                        'two_factor_secret' => $u['two_factor_secret'] ?? null,
                        'two_factor_recovery_hash' => $u['two_factor_recovery_hash'] ?? null,
                        'created_at' => $u['created_at'] ?? now(),
                        'updated_at' => $u['updated_at'] ?? now(),
                    ]);
                }
            }

            Log::info('[ACCOUNT_GUARD] Berhasil memulihkan ' . count($data['users']) . ' akun dari brankas data.');
            return true;
        } catch (\Throwable $e) {
            Log::error('[ACCOUNT_GUARD] Gagal memulihkan dari vault: ' . $e->getMessage());
            return false;
        }
    }
}
