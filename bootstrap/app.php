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
        //di sini user bisa mengedit atau membolehkan url tertentu untuuk bisa bypasss CSRF protection contoh:
        //   $middleware->validateCsrfTokens(except: [
        //     'api/pembayaran',      // Bebas CSRF untuk URL spesifik ini
        //     'webhook/*',           // Bebas CSRF untuk semua URL berawalan webhook/
        //     'midtrans/callback',   // Contoh URL callback dari Payment Gateway
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
