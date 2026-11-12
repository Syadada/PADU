<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan penyiapan akun resmi awal (Satu Akun Administrator Tunggal)
     */
    public function run(): void
    {
        // Hapus akun selain admin utama
        User::where('id', '!=', 1)->delete();

        // 1. Akun Administrator Tunggal (Superadmin)
        User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'PADU',
                'email' => 'padu@local.id',
                'password' => Hash::make('PaduKalkulator1#'),
                'role' => 'superadmin',
                'first_login' => true,
                'failed_login_attempts' => 0,
                'two_factor_enabled' => false,
            ]
        );
    }
}
