<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan middleware proteksi sesi inaktif dan wajib reset sandi pertama
        $middleware->web(append: [
            \App\Http\Middleware\InactivityTimeoutMiddleware::class,
            \App\Http\Middleware\EnsurePasswordNotExpired::class,
        ]);

        // Alias middleware hak akses Super Admin
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\SuperAdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tangani jika sesi kadaluarsa / CSRF token mismatch (POST, PUT, DELETE setelah timeout)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        });

        // Tangani HTTP 419 (Page Expired) atau HTTP 401 (Unauthorized)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if (in_array($e->getStatusCode(), [419, 401])) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
                        'redirect' => route('login'),
                    ], 401);
                }
                return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
            }
        });

        // Tangani sesi belum login atau terputus
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Silakan masuk untuk melanjutkan.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('warning', 'Sesi Anda telah berakhir atau belum masuk. Silakan masuk kembali.');
        });
    })->create();
