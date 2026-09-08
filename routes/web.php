<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DtsenController;

/*
|--------------------------------------------------------------------------
| Web Routes - DTSEN Desil 2026 Beranda Utama & Analytics
|--------------------------------------------------------------------------
*/

// BERANDA UTAMA (HOMEPAGE): DTSEN 2026 Analytics & Data Quality Check
Route::get('/', [DtsenController::class, 'index'])->name('dtsen.index');
Route::get('/dtsen', [DtsenController::class, 'index']);
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
Route::post('/dtsen/logs/clear', [DtsenController::class, 'clearLogs'])->name('dtsen.logs.clear');
