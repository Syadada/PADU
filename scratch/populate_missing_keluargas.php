<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- POPULATING MISSING KELUARGA RECORDS FOR ALL INDIVIDUS ---\n";

DB::statement("
    INSERT OR IGNORE INTO keluargas (
        nomor_kartu_keluarga, kode_provinsi, provinsi, kode_kabupaten_kota, kabupaten_kota,
        kode_kecamatan, kecamatan, kode_kelurahan_desa, kelurahan_desa, alamat,
        jumlah_anggota_keluarga, desil_nasional, jenis_lantai_terluas, jenis_atap_terluas,
        sumber_air_minum_utama, created_at, updated_at
    )
    SELECT 
        nomor_kartu_keluarga, 
        '32', 
        'DKI Jakarta', 
        '3201', 
        'Jakarta Selatan',
        '3201010', 
        'Cilandak', 
        '3201010001', 
        'Cilandak Barat', 
        'Jl. Mawar No. 1',
        1,
        (ABS(RANDOM()) % 10 + 1),
        '01', 
        '1', 
        '01', 
        CURRENT_TIMESTAMP, 
        CURRENT_TIMESTAMP
    FROM individus
    WHERE nomor_kartu_keluarga IS NOT NULL AND nomor_kartu_keluarga != ''
");

$indCount = DB::table('individus')->count();
$kelCount = DB::table('keluargas')->count();
$missingCount = DB::table('individus as i')->leftJoin('keluargas as k', 'i.nomor_kartu_keluarga', '=', 'k.nomor_kartu_keluarga')->whereNull('k.id')->count();

echo "Total Individus: {$indCount}\n";
echo "Total Keluargas: {$kelCount}\n";
echo "Missing relation count: {$missingCount}\n";
