<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Jika user ditandai wajib ganti password (first_login == true)
            if ($user->first_login) {
                // Izinkan akses hanya ke halaman pergantian password dan logout
                if (!$request->routeIs('auth.force-reset', 'auth.force-reset.process', 'logout')) {
                    return redirect()->route('auth.force-reset')->with('warning', '⚠️ Sesuai kebijakan tata kelola keamanan BSSN, Anda diwajibkan memperbarui kata sandi awal dengan kata sandi baru (minimal 15 karakter) sebelum dapat menggunakan aplikasi.');
                }
            }
        }

        return $next($request);
    }
}
