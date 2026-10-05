<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;
use Symfony\Component\HttpFoundation\Response;

class InactivityTimeoutMiddleware
{
    /**
     * Waktu kedaluwarsa sesi inaktif dalam detik (15 Menit = 900 Detik)
     * Mengacu pada Standar Peraturan BSSN No. 4 Tahun 2021
     */
    protected int $timeoutSeconds = 900;

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('padu_last_activity_time');

            if ($lastActivity && (time() - $lastActivity > $this->timeoutSeconds)) {
                $user = Auth::user();
                
                AuditLogger::log(
                    'AUTH_TIMEOUT',
                    "Sesi pengguna '{$user->name}' ({$user->email}) diputus otomatis karena tidak ada aktivitas selama 15 menit (Kepatuhan BSSN No. 4/2021).",
                    'WARNING',
                    ['last_activity' => date('Y-m-d H:i:s', $lastActivity)],
                    $user->id,
                    $user->name,
                    $user->role
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('warning', '⚠️ Sesi Anda telah berakhir secara otomatis karena tidak ada aktivitas selama 15 menit (Sesuai Standar Keamanan BSSN No. 4/2021). Silakan masuk kembali.');
            }

            session(['padu_last_activity_time' => time()]);
        }

        return $next($request);
    }
}
