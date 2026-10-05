<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TwoFactorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login Bertahap (Email-First)
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dtsen.index');
        }

        return view('auth.login');
    }

    /**
     * Langkah 1: Cek Status Surel & Deteksi Role / Status Kunci Akun (AJAX / Form)
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim(strtolower($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat surel tidak terdaftar dalam direktori pengguna PADU Enterprise.',
            ], 404);
        }

        if ($user->isLocked()) {
            $remaining = $user->remainingLockoutSeconds();
            $minutes = max(1, (int)ceil($remaining / 60));
            return response()->json([
                'success' => false,
                'locked' => true,
                'remaining' => $remaining,
                'message' => "⚠️ Akun terkunci sementara selama {$minutes} menit akibat 5 kali kegagalan autentikasi berturut-turut (Standar BSSN No. 4/2021).",
            ], 423);
        }

        return response()->json([
            'success' => true,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'role_label' => $user->isSuperAdmin() ? 'Super Administrator (Pimpinan)' : 'Operator Data Staf',
            'two_factor_enabled' => (bool)$user->two_factor_enabled,
        ]);
    }

    /**
     * Langkah 2: Proses Autentikasi Kata Sandi & Penegakan Kebijakan Lockout
     */
    public function processLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = trim(strtolower($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withInput()->withErrors([
                'email' => 'Alamat surel tidak terdaftar dalam direktori sistem.',
            ]);
        }

        // 1. Cek apakah akun sedang terkunci
        if ($user->isLocked()) {
            $remaining = $user->remainingLockoutSeconds();
            $minutes = max(1, (int)ceil($remaining / 60));
            return back()->withInput()->withErrors([
                'password' => "⚠️ Akun Anda sedang terkunci sementara selama {$minutes} menit karena 5 kali percobaan gagal berturut-turut (Kepatuhan BSSN No. 4/2021).",
            ]);
        }

        // 2. Verifikasi Kata Sandi
        if (!Hash::check($request->password, $user->password)) {
            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= 5) {
                $user->locked_until = Carbon::now()->addMinutes(15);
                $user->save();

                AuditLogger::log(
                    'AUTH_LOCKOUT',
                    "Akun '{$user->name}' ({$user->email}) TERKUNCI OTOMATIS selama 15 menit setelah 5 kali percobaan kata sandi gagal.",
                    'WARNING',
                    ['attempts' => 5],
                    $user->id,
                    $user->name,
                    $user->role
                );

                return back()->withInput()->withErrors([
                    'password' => '⚠️ Anda telah salah memasukkan kata sandi sebanyak 5 kali. Demi keamanan, akun Anda TERKUNCI secara otomatis selama 15 menit sesuai Standar BSSN No. 4/2021.',
                ]);
            }

            $user->save();
            $sisa = 5 - $user->failed_login_attempts;

            AuditLogger::log(
                'AUTH_FAILED',
                "Percobaan login gagal untuk '{$user->name}' ({$user->email}). Upaya gagal: {$user->failed_login_attempts}/5.",
                'FAILED',
                ['attempts' => $user->failed_login_attempts, 'remaining' => $sisa],
                $user->id,
                $user->name,
                $user->role
            );

            return back()->withInput()->withErrors([
                'password' => "Kata sandi yang Anda masukkan salah. Percobaan gagal: {$user->failed_login_attempts}/5. Sisa kesempatan sebelum akun terkunci: {$sisa} kali.",
            ]);
        }

        // Kata sandi benar -> Reset percobaan gagal
        $user->failed_login_attempts = 0;
        $user->locked_until = null;
        $user->save();

        // 3. Cek apakah Super Admin dengan 2FA Aktif
        if ($user->isSuperAdmin() && $user->two_factor_enabled && $user->two_factor_secret) {
            session(['2fa_pending_user_id' => $user->id]);
            return redirect()->route('auth.two-factor')->with('info', 'Masukkan kode verifikasi 6 digit dari aplikasi autentikator di ponsel cerdas Anda.');
        }

        // 4. Login Sesi Pengguna
        Auth::login($user);
        $request->session()->regenerate();
        session(['padu_last_activity_time' => time()]);

        AuditLogger::log(
            'AUTH_LOGIN',
            "Pengguna '{$user->name}' [{$user->role}] berhasil masuk ke sistem.",
            'SUCCESS'
        );

        // Jika akun ditandai first_login, arahkan ke form ganti sandi
        if ($user->first_login) {
            return redirect()->route('auth.force-reset')->with('warning', '⚠️ Sesuai kebijakan BSSN, Anda diwajibkan mengganti kata sandi awal dengan kata sandi baru (minimal 15 karakter) sebelum dapat melanjutkan.');
        }

        return redirect()->route('dtsen.index')->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Tampilkan Halaman Input 2FA TOTP (Khusus Super Admin)
     */
    public function showTwoFactor()
    {
        $userId = session('2fa_pending_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.two-factor', compact('user'));
    }

    /**
     * Verifikasi Kode 6-Digit TOTP
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('2fa_pending_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $isValid = TwoFactorService::verifyCode($user->two_factor_secret, $request->code);

        if (!$isValid) {
            AuditLogger::log(
                '2FA_FAILED',
                "Kode 2FA TOTP salah untuk akun '{$user->name}'.",
                'FAILED',
                ['code_entered' => $request->code],
                $user->id,
                $user->name,
                $user->role
            );

            return back()->withErrors([
                'code' => 'Kode autentikasi 6 digit tidak valid atau sudah kedaluwarsa. Pastikan jam pada perangkat Anda tepat.',
            ]);
        }

        session()->forget('2fa_pending_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        session(['padu_last_activity_time' => time()]);

        AuditLogger::log(
            '2FA_SUCCESS',
            "Pengguna '{$user->name}' berhasil melewati verifikasi 2FA HP Offline.",
            'SUCCESS'
        );

        if ($user->first_login) {
            return redirect()->route('auth.force-reset')->with('warning', '⚠️ Harap perbarui kata sandi awal Anda dengan kata sandi baru (minimal 15 karakter).');
        }

        return redirect()->route('dtsen.index')->with('success', "Verifikasi 2FA berhasil! Selamat datang, {$user->name}.");
    }

    /**
     * Verifikasi Master Recovery Key Fisik (Brankas Pimpinan)
     */
    public function verifyEmergencyRecovery(Request $request)
    {
        $request->validate([
            'recovery_key' => 'required|string',
        ]);

        $userId = session('2fa_pending_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $enteredKey = strtoupper(trim($request->recovery_key));

        if (!$user->two_factor_recovery_hash || !Hash::check($enteredKey, $user->two_factor_recovery_hash)) {
            AuditLogger::log(
                '2FA_RECOVERY_FAILED',
                "Upaya penggunaan Master Recovery Key fisik GAGAL untuk akun '{$user->name}'.",
                'FAILED',
                [],
                $user->id,
                $user->name,
                $user->role
            );

            return back()->withErrors([
                'recovery_key' => 'Kunci Pemulihan Darurat (Master Recovery Key) fisik tidak cocok.',
            ]);
        }

        // HANGUSKAN 2FA LAMA (AUTO-REVOKE)
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_hash = null;
        $user->save();

        session()->forget('2fa_pending_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        session(['padu_last_activity_time' => time()]);

        AuditLogger::log(
            '2FA_REVOKED_BY_RECOVERY',
            "PERINGATAN FORENSIK: Master Recovery Key Fisik digunakan untuk akun '{$user->name}'. Perangkat 2FA lama resmi dihanguskan (Auto-Revoke).",
            'WARNING'
        );

        return redirect()->route('admin.two-factor')->with('warning', '⚠️ Kunci Pemulihan Darurat berhasil diterima! Perangkat 2FA lama telah dinonaktifkan. Segera daftarkan kembali aplikasi autentikator di ponsel cerdas Anda.');
    }

    /**
     * Tampilkan Halaman Wajib Ubah Sandi (Force Reset Min. 15 Karakter)
     */
    public function showForceResetPassword()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        return view('auth.force-reset', compact('user'));
    }

    /**
     * Proses Perubahan Sandi Wajib Pertama Kali
     */
    public function processForceResetPassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:15',
                'confirmed',
                'regex:/[A-Z]/',      // Minimal 1 huruf besar
                'regex:/[a-z]/',      // Minimal 1 huruf kecil
                'regex:/[0-9]/',      // Minimal 1 angka
                'regex:/[@$!%*#?&-_+=]/', // Minimal 1 karakter spesial
            ],
        ], [
            'password.min' => 'Sesuai Standar BSSN No. 4/2021, kata sandi baru harus memiliki panjang minimal 15 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru.',
            'password.regex' => 'Kata sandi harus mengandung kombinasi huruf besar, huruf kecil, angka, dan karakter khusus/simbol (@$!%*#?&-_+=).',
        ]);

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama/sementara.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->first_login = false;
        $user->password_changed_at = Carbon::now();
        $user->save();

        AuditLogger::log(
            'PASSWORD_RESET_SUCCESS',
            "Pengguna '{$user->name}' ({$user->role}) berhasil memperbarui kata sandi baru (kebijakan min. 15 karakter terpenuhi).",
            'SUCCESS'
        );

        return redirect()->route('dtsen.index')->with('success', 'Kata sandi Anda berhasil diperbarui dengan standar keamanan BSSN v2.0!');
    }

    /**
     * Logout Pengguna & Penghapusan Sesi Aman
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            AuditLogger::log(
                'AUTH_LOGOUT',
                "Pengguna '{$user->name}' keluar dari sistem.",
                'SUCCESS'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem secara aman.');
    }
}
