<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan penyiapan akun resmi awal (Default Turnkey Handover)
     */
    public function run(): void
    {
        // 1. Akun Super Administrator (Pimpinan)
        // Dikonfigurasi dengan status first_login = true (Wajib ganti password saat login pertama kali)
        User::updateOrCreate(
            ['email' => 'superadmin@padu.local'],
            [
                'name' => 'Super Administrator (Pimpinan)',
                'password' => Hash::make('PasswordSuperAdmin2026!'),
                'role' => 'superadmin',
                'first_login' => true,
                'failed_login_attempts' => 0,
                'two_factor_enabled' => false,
            ]
        );

        // 2. Akun Staf Operator Data DTSEN
        User::updateOrCreate(
            ['email' => 'operator@padu.local'],
            [
                'name' => 'Operator Data DTSEN',
                'password' => Hash::make('PasswordOperator2026!'),
                'role' => 'operator',
                'first_login' => false,
                'failed_login_attempts' => 0,
                'two_factor_enabled' => false,
            ]
        );
    }
}
