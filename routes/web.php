<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DtsenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes - PADU v2.0 Enterprise (BSSN & UU PDP Compliant)
|--------------------------------------------------------------------------
*/

// --- 1. OTENTIKASI & MANAJEMEN SESI (PUBLIC / GUEST) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin'])->name('login.process');
    Route::post('/auth/check-email', [AuthController::class, 'checkEmail'])->name('auth.check-email');
    
    // Alur Verifikasi Two-Factor Authentication (2FA) & Recovery Key
    Route::get('/login/2fa', [AuthController::class, 'showTwoFactor'])->name('auth.two-factor');
    Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('auth.two-factor.verify');
    Route::post('/login/2fa-recovery', [AuthController::class, 'verifyEmergencyRecovery'])->name('auth.two-factor.recovery');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- 2. AKSES PENGGUNA TEROTENTIKASI (SUPER ADMIN & OPERATOR) ---
Route::middleware('auth')->group(function () {
    
    // Wajib Ubah Password Awal (Force Reset Pertama Kali Sesuai BSSN)
    Route::get('/auth/force-reset-password', [AuthController::class, 'showForceResetPassword'])->name('auth.force-reset');
    Route::post('/auth/force-reset-password', [AuthController::class, 'processForceResetPassword'])->name('auth.force-reset.process');

    // Profil Akun Pribadi (BSSN Compliant Self-Service Profile & Password Update)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Beranda Utama DTSEN 2026 & Analisis Kependudukan (6 Modul Terintegrasi)
    Route::get('/', [DtsenController::class, 'dataTable'])->name('dtsen.index');
    Route::get('/dtsen', [DtsenController::class, 'dataTable']);
    Route::get('/dtsen/data', [DtsenController::class, 'dataTable'])->name('dtsen.data');
    Route::get('/dtsen/quality', [DtsenController::class, 'qualityCheck'])->name('dtsen.quality');
    Route::get('/dtsen/kpi', [DtsenController::class, 'kpiAnalytics'])->name('dtsen.kpi');
    Route::get('/dtsen/salary', [DtsenController::class, 'salaryAnalytics'])->name('dtsen.salary');
    Route::get('/dtsen/audit', [DtsenController::class, 'issueAudit'])->name('dtsen.audit');
    Route::get('/dtsen/files', [DtsenController::class, 'filesManagement'])->name('dtsen.files');
    Route::get('/dtsen/all', [DtsenController::class, 'index'])->name('dtsen.all');
    Route::get('/dtsen/preview/{id}', [DtsenController::class, 'preview'])->name('dtsen.preview');
    Route::post('/dtsen/import', [DtsenController::class, 'importFile'])->name('dtsen.import');
    Route::post('/dtsen/import-chunk', [DtsenController::class, 'importChunk'])->name('dtsen.import-chunk');
    Route::post('/dtsen/import-local', [DtsenController::class, 'importLocalPath'])->name('dtsen.import-local');
    Route::get('/dtsen/template', [DtsenController::class, 'downloadTemplate'])->name('dtsen.template');
    Route::get('/dtsen/export-errors', [DtsenController::class, 'exportErrors'])->name('dtsen.export-errors');
    Route::get('/dtsen/export', [DtsenController::class, 'exportCsv'])->name('dtsen.export');
    Route::post('/dtsen/clear', [DtsenController::class, 'clearData'])->name('dtsen.clear');
    Route::get('/dtsen/import-progress', [DtsenController::class, 'getImportProgress'])->name('dtsen.import-progress');
    Route::post('/dtsen/cancel-import', [DtsenController::class, 'cancelImport'])->name('dtsen.cancel-import');
    Route::get('/dtsen/logs', [DtsenController::class, 'getLogs'])->name('dtsen.logs');
    Route::get('/dtsen/logs/download', [DtsenController::class, 'downloadLogs'])->name('dtsen.logs.download');
    Route::post('/dtsen/logs/clear', [DtsenController::class, 'clearLogs'])->name('dtsen.logs.clear');


    Route::post('/dtsen/upload', [DtsenController::class, 'uploadDatasetFile'])->name('dtsen.upload');

    // --- 3. MODUL KHUSUS SUPER ADMINISTRATOR (PIMPINAN & AUDIT BSSN) ---
    Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
        
        // Audit Trail Forensik Digital (BSSN No. 4/2021)
        Route::get('/audit-logs', [AdminController::class, 'auditLogsIndex'])->name('audit-logs');
        Route::get('/audit-logs/export', [AdminController::class, 'exportAuditLogs'])->name('audit-logs.export');

        // Pencadangan Aplikasi & Proteksi ZIP AES-256
        Route::get('/backup', [AdminController::class, 'backupIndex'])->name('backup');
        Route::post('/backup/create', [AdminController::class, 'createBackup'])->name('backup.create');
        Route::get('/backup/download/{filename}', [AdminController::class, 'downloadBackup'])->name('backup.download');

        // Manajemen Staf & Operator (CRUD, Reset Password, Unlock)
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.destroy');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetOperatorPassword'])->name('users.reset-password');
        Route::post('/users/{id}/unlock', [AdminController::class, 'unlockUser'])->name('users.unlock');

        // Konfigurasi 2FA TOTP Ponsel Cerdas Super Admin
        Route::get('/two-factor', [AdminController::class, 'setupTwoFactorIndex'])->name('two-factor');
        Route::post('/two-factor/enable', [AdminController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::post('/two-factor/disable', [AdminController::class, 'disableTwoFactor'])->name('two-factor.disable');

        // Standar Metadata BAST & Engine Quality Check (Super Admin Only)
        Route::get('/metadata', [AdminController::class, 'metadataIndex'])->name('metadata');
        Route::post('/metadata/train', [AdminController::class, 'trainMetadata'])->name('metadata.train');
        Route::post('/metadata/simulate', [AdminController::class, 'simulateQualityCheck'])->name('metadata.simulate');
    });

});
