<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AccountProtectionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Terapkan pengikatan perangkat (Device Binding) & perlindungan akun
        AccountProtectionService::enforceDeviceBinding();

        // 2. Pasang pengawas otomatis: setiap perubahan akun/password langsung dicadangkan ke brankas
        User::saved(function () {
            AccountProtectionService::backupAccounts();
        });
        User::deleted(function () {
            AccountProtectionService::backupAccounts();
        });
    }
}
