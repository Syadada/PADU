<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\AccountProtectionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan penyiapan akun resmi awal (Pastikan Akun Administrator Selalu Tersedia Tanpa Menghapus Akun Lain)
     */
    public function run(): void
    {
        // 1. Cek apakah akun superadmin utama sudah ada
        $admin = User::where('email', 'padu@local.id')->orWhere('role', 'superadmin')->first();

        if (!$admin) {
            User::create([
                'name' => 'PADU',
                'email' => 'padu@local.id',
                'password' => Hash::make('PaduKalkulator1#'),
                'role' => 'superadmin',
                'first_login' => true,
                'failed_login_attempts' => 0,
                'two_factor_enabled' => false,
            ]);
        }

        // 2. Amankan seluruh akun ke brankas
        AccountProtectionService::backupAccounts();
    }
}
