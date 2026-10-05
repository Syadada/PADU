<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Tampilkan Halaman Profil Akun Pengguna
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil riwayat audit trail aktivitas pengguna ini sendiri (5 entri terakhir)
        $recentActivities = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentLogs = $recentActivities;
        return view('profile', compact('user', 'recentActivities', 'recentLogs'));
    }

    /**
     * Perbarui Informasi Profil Dasar (Nama Lengkap)
     * Sesuai Standar BSSN No. 4/2021 & UU PDP
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100|min:3',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 3 karakter.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
        ]);

        $oldName = $user->name;
        $user->name = trim($request->name);
        $user->save();

        AuditLogger::log(
            'PROFILE_UPDATED',
            "Pengguna '{$user->email}' memperbarui nama profil dari '{$oldName}' menjadi '{$user->name}'.",
            'SUCCESS',
            ['old_name' => $oldName, 'new_name' => $user->name]
        );

        return back()->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    /**
     * Ubah Kata Sandi Mandiri oleh Pengguna
     * Wajib Verifikasi Kata Sandi Saat Ini & Memenuhi Standar Min. 15 Karakter BSSN
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:15',
                'confirmed',
                'regex:/[A-Z]/',      // Minimal 1 huruf besar
                'regex:/[a-z]/',      // Minimal 1 huruf kecil
                'regex:/[0-9]/',      // Minimal 1 angka
                'regex:/[@$!%*#?&-_+=]/', // Minimal 1 simbol
            ],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Sesuai Standar BSSN No. 4/2021, kata sandi baru harus memiliki panjang minimal 15 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru.',
            'password.regex' => 'Kata sandi harus mengandung kombinasi huruf besar, huruf kecil, angka, dan simbol (@$!%*#?&-_+=).',
        ]);

        // 1. Verifikasi kata sandi lama
        if (!Hash::check($request->current_password, $user->password)) {
            AuditLogger::log(
                'PASSWORD_CHANGE_FAILED',
                "Percobaan ubah sandi mandiri untuk '{$user->name}' ({$user->email}) GAGAL: kata sandi saat ini salah.",
                'FAILED'
            );

            return back()->withErrors([
                'current_password' => 'Kata sandi saat ini yang Anda masukkan tidak sesuai.',
            ]);
        }

        // 2. Cek apakah sama dengan sandi lama
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini.',
            ]);
        }

        // 3. Simpan kata sandi baru
        $user->password = Hash::make($request->password);
        $user->password_changed_at = Carbon::now();
        $user->first_login = false;
        $user->save();

        AuditLogger::log(
            'PASSWORD_CHANGED_SELF',
            "Pengguna '{$user->name}' ({$user->email}) berhasil memperbarui kata sandi mandiri (kebijakan min. 15 karakter BSSN terpenuhi).",
            'SUCCESS'
        );

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui dengan standar keamanan BSSN v2.0!');
    }
}
