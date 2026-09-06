<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Individu;
use App\Models\Keluarga;
use App\Models\Demographic;
use App\Services\DtsenImportService;
use App\Services\DataQualityCheckService;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DtsenController extends Controller
{
    /**
     * Set Data Anggota Keluarga / Individu (48 Variabel Resmi BPS-Bappenas DTSEN 2026)
     */
    public static function getOfficialIndividuVariables()
    {
        return [
            'nomor_induk_kependudukan' => '1. Nomor Induk Kependudukan (NIK)',
            'nomor_kartu_keluarga' => '2. Nomor Kartu Keluarga (KK)',
            'nama' => '3. Nama Lengkap',
            'tanggal_lahir' => '4. Tanggal Lahir',
            'jenis_kelamin' => '5. Jenis Kelamin',
            'status_hubungan_keluarga' => '6. Status Hubungan Keluarga',
            'pbi_nasional' => '7. PBI Nasional (Individu)',
            'pbi_pemda' => '8. PBI Pemda (Individu)',
            'status_kawin' => '9. Status Kawin',
            'partisipasi_sekolah' => '10. Partisipasi Sekolah',
            'jenjang_tertinggi_yang_diduduki' => '11. Jenjang Tertinggi Diduduki',
            'kelas_tertinggi_yang_diduduki' => '12. Kelas Tertinggi Diduduki',
            'ijazah_tertinggi_yang_dimiliki' => '13. Ijazah Tertinggi Dimiliki',
            'status_bekerja' => '14. Status Bekerja',
            'lapangan_usaha_dari_pekerjaan_utama' => '15. Lapangan Usaha Pekerjaan Utama',
            'status_dalam_pekerjaan_utama' => '16. Status Dalam Pekerjaan Utama',
            'kepemilikan_usaha' => '17. Kepemilikan Usaha',
            'jumlah_usaha' => '18. Jumlah Usaha',
            'lapangan_usaha_dari_usaha_utama' => '19. Lapangan Usaha Usaha Utama',
            'jumlah_pekerja_yang_dibayar_dari_usaha_utama' => '20. Jumlah Pekerja Dibayar',
            'jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama' => '21. Jumlah Pekerja Tidak Dibayar',
            'omzet_usaha_utama' => '22. Omzet Usaha Utama',
            'kondisi_gizi' => '23. Kondisi Gizi (Wasting/Stunting)',
            'penglihatan' => '24. Disabilitas Penglihatan',
            'pendengaran' => '25. Disabilitas Pendengaran',
            'berjalan_atau_naik_tangga' => '26. Disabilitas Berjalan / Naik Tangga',
            'menggunakan_tangan_jari' => '27. Disabilitas Menggunakan Tangan / Jari',
            'belajar_kemampuan_intelektual' => '28. Disabilitas Intelektual',
            'pengendalian_perilaku' => '29. Disabilitas Pengendalian Perilaku',
            'berbicara_komunikasi' => '30. Disabilitas Berbicara / Komunikasi',
            'mengurus_diri' => '31. Disabilitas Mengurus Diri',
            'mengingat_berkonsentrasi' => '32. Disabilitas Mengingat / Konsentrasi',
            'kesedihan_depresi' => '33. Disabilitas Kesedihan / Depresi',
            'penyakit_kronis' => '34. Penyakit Kronis',
            'kode_provinsi_ktp' => '35. Kode Provinsi (KTP)',
            'provinsi_ktp' => '36. Provinsi (KTP)',
            'kode_kabupaten_kota_ktp' => '37. Kode Kabupaten/Kota (KTP)',
            'kabupaten_kota_ktp' => '38. Kabupaten/Kota (KTP)',
            'kode_kecamatan_ktp' => '39. Kode Kecamatan (KTP)',
            'kecamatan_ktp' => '40. Kecamatan (KTP)',
            'kode_kelurahan_desa_ktp' => '41. Kode Kelurahan/Desa (KTP)',
            'kelurahan_desa_ktp' => '42. Kelurahan/Desa (KTP)',
            'rt_ktp' => '43. RT (KTP)',
            'rw_ktp' => '44. RW (KTP)',
            'dusun_ktp' => '45. Dusun (KTP)',
            'alamat_ktp' => '46. Alamat (KTP)',
            'pekerjaan_ktp' => '47. Pekerjaan (KTP)',
            'pendidikan_akhir_ktp' => '48. Pendidikan Akhir (KTP)',
        ];
    }

    /**
     * Set Data Keluarga (52 Variabel Resmi BPS-Bappenas DTSEN 2026)
     */
    public static function getOfficialKeluargaVariables()
    {
        return [
            'kode_provinsi' => '1. Kode Provinsi (Keluarga)',
            'provinsi' => '2. Provinsi (Keluarga)',
            'kode_kabupaten_kota' => '3. Kode Kabupaten/Kota (Keluarga)',
            'kabupaten_kota' => '4. Kabupaten/Kota (Keluarga)',
            'kode_kecamatan' => '5. Kode Kecamatan (Keluarga)',
            'kecamatan' => '6. Kecamatan (Keluarga)',
            'kode_kelurahan_desa' => '7. Kode Kelurahan/Desa (Keluarga)',
            'kelurahan_desa' => '8. Kelurahan/Desa (Keluarga)',
            'alamat' => '9. Alamat (Keluarga)',
            'nomor_kartu_keluarga_kel' => '10. Nomor Kartu Keluarga (Keluarga)',
            'jumlah_anggota_keluarga' => '11. Jumlah Anggota Keluarga',
            'nama_anggota_keluarga' => '12. Nama Anggota Keluarga',
            'desil_nasional' => '13. Desil Nasional (Keluarga)',
            'pbi_nasional_keluarga' => '14. PBI Nasional (Keluarga)',
            'pbi_pemda_keluarga' => '15. PBI Pemda (Keluarga)',
            'id_pelanggan_pln' => '16. ID Pelanggan PLN',
            'status_kepemilikan_rumah' => '17. Status Kepemilikan Rumah',
            'jenis_lantai_terluas' => '18. Jenis Lantai Terluas',
            'luas_lantai' => '19. Luas Lantai (m2)',
            'jenis_dinding_terluas' => '20. Jenis Dinding Terluas',
            'jenis_atap_terluas' => '21. Jenis Atap Terluas',
            'sumber_air_minum_utama' => '22. Sumber Air Minum Utama',
            'sumber_penerangan_utama' => '23. Sumber Penerangan Utama',
            'daya_terpasang' => '24. Daya Terpasang',
            'bahan_bakar_utama_memasak' => '25. Bahan Bakar Utama Memasak',
            'fasilitas_bab' => '26. Fasilitas BAB',
            'jenis_kloset' => '27. Jenis Kloset',
            'pembuangan_akhir_tinja' => '28. Pembuangan Akhir Tinja',
            'kepemilikan_aset' => '29. Kepemilikan Aset',
            'aset_bergerak_tabung_gas' => '30. Aset Tabung Gas (>=5.5kg)',
            'aset_bergerak_lemari_es' => '31. Aset Lemari Es / Kulkas',
            'aset_bergerak_ac' => '32. Aset AC',
            'aset_bergerak_pemanas_air' => '33. Aset Pemanas Air',
            'aset_bergerak_telepon_rumah' => '34. Aset Telepon Rumah',
            'aset_bergerak_tv_datar' => '35. Aset TV Datar',
            'aset_bergerak_emas_perhiasan' => '36. Aset Emas Perhiasan',
            'aset_bergerak_komputer_laptop_tablet' => '37. Aset Komputer / Laptop / Tablet',
            'aset_bergerak_sepeda_motor' => '38. Aset Sepeda Motor',
            'aset_bergerak_sepeda' => '39. Aset Sepeda',
            'aset_bergerak_mobil' => '40. Aset Mobil',
            'aset_bergerak_perahu' => '41. Aset Perahu',
            'aset_bergerak_kapal_perahu_motor' => '42. Aset Kapal / Perahu Motor',
            'aset_bergerak_smartphone' => '43. Aset Smartphone',
            'aset_tidak_bergerak_lahan_lainnya' => '44. Aset Lahan Lainnya',
            'aset_tidak_bergerak_rumah_lainnya' => '45. Aset Rumah Lainnya',
            'jumlah_ternak_sapi' => '46. Jumlah Ternak Sapi',
            'jumlah_ternak_kerbau' => '47. Jumlah Ternak Kerbau',
            'jumlah_ternak_kuda' => '48. Jumlah Ternak Kuda',
            'jumlah_ternak_babi' => '49. Jumlah Ternak Babi',
            'jumlah_ternak_kambing_domba' => '50. Jumlah Ternak Kambing / Domba',
            'desil_provinsi' => '51. Desil Provinsi (Keluarga)',
            'desil_kabupaten_kota' => '52. Desil Kabupaten/Kota (Keluarga)',
        ];
    }

    /**
     * Master Kamus Variabel Resmi (48 Variabel Individu + 52 Variabel Keluarga DTSEN 2026 BPS-Bappenas)
     */
    public static function getAllOfficialVariables()
    {
        return array_merge(
            ['quality_status' => 'QC Status (Quality Check)'],
            self::getOfficialIndividuVariables(),
            self::getOfficialKeluargaVariables(),
            [
                'gaji_bulanan' => 'Gaji Bulanan (Rp)',
                'gaji' => 'Gaji (Rp)',
                'usia' => 'Usia (Tahun)',
            ]
        );
    }

    /**
     * Halaman Utama Dashboard DTSEN 2026 Analytics & Multi-Checkbox Dynamic Data Table
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $desil = $request->input('desil', 'semua');
        $qualityStatus = $request->input('quality_status', 'semua');
        $jenisKelamin = $request->input('jenis_kelamin', 'semua');
        $pendidikan = $request->input('pendidikan', 'semua');
        $statusBekerja = $request->input('status_bekerja', 'semua');
        $statusKawin = $request->input('status_kawin', 'semua');
        $filterCol = $request->input('filter_col', '');
        $filterVal = $request->input('filter_val', '');

        // Scan folder src-dtsen di root folder project
        $srcDtsenDir = base_path('src-dtsen');
        if (!file_exists($srcDtsenDir)) {
            @mkdir($srcDtsenDir, 0777, true);
        }

        $srcDtsenFiles = [];
        if (file_exists($srcDtsenDir) && is_dir($srcDtsenDir)) {
            $files = array_diff(scandir($srcDtsenDir), ['.', '..', '.gitkeep', '.DS_Store']);
            foreach ($files as $fName) {
                $fPath = $srcDtsenDir . DIRECTORY_SEPARATOR . $fName;
                if (is_file($fPath)) {
                    $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                    $size = filesize($fPath);
                    $srcDtsenFiles[] = [
                        'name' => $fName,
                        'full_path' => $fPath,
                        'relative_path' => 'src-dtsen/' . $fName,
                        'extension' => $ext,
                        'size' => $size,
                        'size_formatted' => DtsenImportService::formatBytes($size),
                        'modified_at' => date('d/m/Y H:i', filemtime($fPath)),
                    ];
                }
            }
        }

        $totalSystemRows = Individu::count();

        // Jika sistem kosong (0 total baris), aktifkan activeColumnsMap sebagai array kosong agar tidak menampilkan tabel/header apapun
        if ($totalSystemRows === 0) {
            $activeColumnsMap = [];
        } else {
            $uploadedHeaders = session('uploaded_headers', null);
            if (!empty($uploadedHeaders) && is_array($uploadedHeaders)) {
                $activeColumnsMap = $uploadedHeaders;
            } else {
                $activeColumnsMap = [
                    'nomor_induk_kependudukan' => 'Nomor Induk Kependudukan (NIK)',
                    'nomor_kartu_keluarga' => 'Nomor Kartu Keluarga (KK)',
                    'nama' => 'Nama Lengkap',
                    'gaji_bulanan' => 'Gaji Bulanan (Rp)',
                    'desil_nasional' => 'Desil Kesejahteraan',
                    'usia' => 'Usia (Tahun)',
                    'jenis_kelamin' => 'Jenis Kelamin'
                ];
            }

            // Ekstrak otomatis seluruh Kunci Variabel Custom dari extra_attributes JSON hasil import
            $extraKeysMap = [];
            $sampleWithExtra = Individu::whereNotNull('extra_attributes')->take(50)->get();
            foreach ($sampleWithExtra as $sw) {
                if (is_array($sw->extra_attributes)) {
                    foreach ($sw->extra_attributes as $k => $v) {
                        $extraKeysMap[$k] = ucwords(str_replace('_', ' ', $k));
                    }
                }
            }
            if (!empty($extraKeysMap)) {
                $activeColumnsMap = array_merge($activeColumnsMap, $extraKeysMap);
            }
        }

        // Query Utama untuk Tabel Data Dataset & Summary Audit Overview
        $query = Individu::with('keluarga');

        // Filter Smart Search Dinamis (Ultra Fast B-Tree Indexed Search)
        if (!empty($search)) {
            $searchClean = trim($search);

            if (strlen($searchClean) === 16 && ctype_digit($searchClean)) {
                $query->where(function ($q) use ($searchClean) {
                    $q->where('nomor_induk_kependudukan', $searchClean)
                      ->orWhere('nomor_kartu_keluarga', $searchClean);
                });
            } elseif (is_numeric($searchClean) || preg_match('/^\d+$/', $searchClean)) {
                $query->where(function ($q) use ($searchClean) {
                    $q->where('nomor_induk_kependudukan', 'LIKE', $searchClean . '%')
                      ->orWhere('nomor_kartu_keluarga', 'LIKE', $searchClean . '%');
                });
            } else {
                $likeTerm = '%' . strtolower($searchClean) . '%';
                $query->where(function ($q) use ($likeTerm) {
                    $q->whereRaw('LOWER(nama) LIKE ?', [$likeTerm])
                      ->orWhereRaw('LOWER(nomor_induk_kependudukan) LIKE ?', [$likeTerm])
                      ->orWhereRaw('LOWER(nomor_kartu_keluarga) LIKE ?', [$likeTerm]);
                });
            }
        }

        // Filter Status Quality Test (Valid, Warning, Critical)
        if ($qualityStatus !== 'semua') {
            $query->where('quality_status', $qualityStatus);
        }

        // Filter Dinamis Berdasarkan Variabel Apapun dari File Upload
        if (!empty($filterCol) && !empty($filterVal) && $filterVal !== 'semua') {
            if ($filterCol === 'desil_nasional' || $filterCol === 'desil') {
                $desilInt = (int)preg_replace('/[^0-9]/', '', $filterVal);
                if ($desilInt >= 1 && $desilInt <= 10) {
                    $kkList = DB::table('keluargas')->where('desil_nasional', $desilInt)->pluck('nomor_kartu_keluarga')->toArray();
                } else {
                    $kkList = DB::table('keluargas')->whereRaw("LOWER(CAST(desil_nasional AS TEXT)) LIKE ?", ['%' . strtolower(trim($filterVal)) . '%'])->pluck('nomor_kartu_keluarga')->toArray();
                }
                $query->whereIn('nomor_kartu_keluarga', $kkList);
            } else {
                $query->where($filterCol, $filterVal);
            }
        }

        // Scan folder src-export di root folder project
        $srcExportDir = base_path('src-export');
        if (!file_exists($srcExportDir)) {
            @mkdir($srcExportDir, 0777, true);
        }

        $srcExportFiles = [];
        if (file_exists($srcExportDir) && is_dir($srcExportDir)) {
            $files = array_diff(scandir($srcExportDir), ['.', '..', '.gitkeep', '.DS_Store']);
            foreach ($files as $fName) {
                $fPath = $srcExportDir . DIRECTORY_SEPARATOR . $fName;
                if (is_file($fPath)) {
                    $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                    $size = filesize($fPath);
                    $srcExportFiles[] = [
                        'name' => $fName,
                        'full_path' => $fPath,
                        'relative_path' => 'src-export/' . $fName,
                        'extension' => $ext,
                        'size' => $size,
                        'size_formatted' => DtsenImportService::formatBytes($size),
                        'modified_at' => date('d/m/Y H:i', filemtime($fPath)),
                    ];
                }
            }
        }

        // Disable query log & increase memory limit for heavy aggregate calculations
        \Illuminate\Support\Facades\DB::disableQueryLog();
        ini_set('memory_limit', '512M');

        // Reaktif Calculation Metrik KPI Utama (Instant Session Cached or Fast Single SQL Aggregate Query)
        if (empty($search) && $qualityStatus === 'semua' && empty($filterCol)) {
            $cachedStats = session('dataset_summary_stats');
            if (is_array($cachedStats) && !empty($cachedStats)) {
                $totalRows = (int)($cachedStats['total_rows'] ?? 0);
                $totalKk = (int)($cachedStats['total_kk'] ?? 0);
                $validCount = (int)($cachedStats['valid_count'] ?? 0);
                $warningCount = (int)($cachedStats['warning_count'] ?? 0);
                $criticalCount = (int)($cachedStats['critical_count'] ?? 0);
                $multiErrorCount = (int)($cachedStats['multi_error_count'] ?? 0);
                $errorCount = $criticalCount + $warningCount;
            } else {
                $stats = DB::selectOne("
                    SELECT 
                        COUNT(*) as total_rows,
                        COUNT(DISTINCT nomor_kartu_keluarga) as total_kk,
                        SUM(CASE WHEN quality_status = 'Valid' THEN 1 ELSE 0 END) as valid_count,
                        SUM(CASE WHEN quality_status = 'Warning' THEN 1 ELSE 0 END) as warning_count,
                        SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_count,
                        SUM(CASE WHEN json_array_length(quality_issues) >= 3 THEN 1 ELSE 0 END) as multi_error_count
                    FROM individus
                ");

                $totalRows = (int)($stats->total_rows ?? 0);
                $totalKk = (int)($stats->total_kk ?? 0);
                $validCount = (int)($stats->valid_count ?? 0);
                $warningCount = (int)($stats->warning_count ?? 0);
                $criticalCount = (int)($stats->critical_count ?? 0);
                $multiErrorCount = (int)($stats->multi_error_count ?? 0);
                $errorCount = $criticalCount + $warningCount;
            }
        } else {
            $stats = (clone $query)->selectRaw("
                COUNT(*) as total_rows,
                COUNT(DISTINCT nomor_kartu_keluarga) as total_kk,
                SUM(CASE WHEN quality_status = 'Valid' THEN 1 ELSE 0 END) as valid_count,
                SUM(CASE WHEN quality_status = 'Warning' THEN 1 ELSE 0 END) as warning_count,
                SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_count
            ")->first();

            $totalRows = (int)($stats->total_rows ?? 0);
            $totalKk = (int)($stats->total_kk ?? 0);
            $validCount = (int)($stats->valid_count ?? 0);
            $warningCount = (int)($stats->warning_count ?? 0);
            $criticalCount = (int)($stats->critical_count ?? 0);
            $multiErrorCount = 0;
            $errorCount = $criticalCount + $warningCount;
        }

        // Ambil Seluruh Header/Kolom Hasil Import (Filter TANPA NIK, KK, atau ID Pelanggan)
        $importFilterableColumns = collect($activeColumnsMap)->reject(function($label, $key) {
            return in_array($key, [
                'nomor_induk_kependudukan',
                'nomor_kartu_keluarga',
                'id_pelanggan_pln',
                'gaji_bulanan',
                'gaji',
                'id',
                'created_at',
                'updated_at'
            ]);
        })->toArray();

        // 1. Dynamic Extraction of Actual Distinct Values directly from Database
        $tableColsListing = \Illuminate\Support\Facades\Schema::getColumnListing('individus');
        $keluargaColsListing = \Illuminate\Support\Facades\Schema::getColumnListing('keluargas');

        $getActualDistinctValues = function($colKey) use ($tableColsListing, $keluargaColsListing) {
            try {
                // Check on individus table core columns
                if (in_array($colKey, $tableColsListing)) {
                    $vals = DB::table('individus')
                        ->whereNotNull($colKey)
                        ->where($colKey, '!=', '')
                        ->distinct()
                        ->pluck($colKey)
                        ->map(fn($v) => (string)$v)
                        ->unique()
                        ->values()
                        ->toArray();
                    if (!empty($vals)) return $vals;
                }

                // Check on keluargas table core columns
                if (in_array($colKey, $keluargaColsListing)) {
                    $vals = DB::table('keluargas')
                        ->whereNotNull($colKey)
                        ->where($colKey, '!=', '')
                        ->distinct()
                        ->pluck($colKey)
                        ->map(fn($v) => (string)$v)
                        ->unique()
                        ->values()
                        ->toArray();
                    if (!empty($vals)) return $vals;
                }

                // Check inside extra_attributes JSON on individus table
                $jsonVals = DB::table('individus')
                    ->whereNotNull('extra_attributes')
                    ->whereRaw("LOWER(extra_attributes) LIKE ?", ['%' . strtolower($colKey) . '%'])
                    ->select('extra_attributes')
                    ->take(300)
                    ->get()
                    ->map(function($r) use ($colKey) {
                        $extra = is_array($r->extra_attributes) 
                            ? $r->extra_attributes 
                            : json_decode($r->extra_attributes, true);
                        if (is_array($extra)) {
                            if (isset($extra[$colKey]) && $extra[$colKey] !== '' && $extra[$colKey] !== null) {
                                return (string)$extra[$colKey];
                            }
                            $normK = strtolower(str_replace([' ', '.'], '_', trim($colKey)));
                            if (isset($extra[$normK]) && $extra[$normK] !== '' && $extra[$normK] !== null) {
                                return (string)$extra[$normK];
                            }
                        }
                        return null;
                    })
                    ->filter(fn($v) => $v !== null && $v !== '')
                    ->unique()
                    ->values()
                    ->toArray();

                return $jsonVals;
            } catch (\Throwable $e) {
                return [];
            }
        };

        // Deteksi Keberadaan Kolom Berdasarkan Skema Tabel Data Active Columns Map
        $hasJenisKelamin = array_key_exists('jenis_kelamin', $activeColumnsMap) || array_key_exists('gender', $activeColumnsMap) || array_key_exists('jk', $activeColumnsMap);
        $hasPendidikan = array_key_exists('pendidikan', $activeColumnsMap) || array_key_exists('ijazah_tertinggi_yang_dimiliki', $activeColumnsMap) || array_key_exists('jenjang_tertinggi_yang_diduduki', $activeColumnsMap) || array_key_exists('pendidikan_terakhir', $activeColumnsMap);
        $hasDesil = array_key_exists('desil_nasional', $activeColumnsMap) || array_key_exists('desil', $activeColumnsMap);
        $hasStatusBekerja = array_key_exists('status_bekerja', $activeColumnsMap) || array_key_exists('pekerjaan', $activeColumnsMap) || array_key_exists('status_kerja', $activeColumnsMap);
        $hasStatusKawin = array_key_exists('status_kawin', $activeColumnsMap) || array_key_exists('status_pernikahan', $activeColumnsMap);

        $importColumnDistinctValues = [];
        foreach ($activeColumnsMap as $colKey => $colLabel) {
            $isAgeCol = preg_match('/usia|umur|age/i', $colKey) || preg_match('/usia|umur|age/i', $colLabel);
            $isSalaryCol = preg_match('/gaji|pendapatan|omzet|upah|salary|income/i', $colKey) || preg_match('/gaji|pendapatan|omzet|upah|salary|income/i', $colLabel);

            if ($isAgeCol) {
                $importColumnDistinctValues[$colKey] = [
                    '< 18 Tahun',
                    '18 - 25 Tahun',
                    '26 - 35 Tahun',
                    '36 - 45 Tahun',
                    '46 - 55 Tahun',
                    '> 55 Tahun'
                ];
            } elseif ($isSalaryCol) {
                $importColumnDistinctValues[$colKey] = [
                    '< Rp 3.000.000',
                    'Rp 3.000.000 - 5.000.000',
                    'Rp 5.000.000 - 10.000.000',
                    '> Rp 10.000.000'
                ];
            } else {
                $actualVals = $getActualDistinctValues($colKey);
                if ($colKey === 'desil_nasional' || $colKey === 'desil') {
                    $actualVals = array_map(fn($v) => is_numeric($v) ? 'Desil ' . $v : $v, $actualVals);
                }
                $importColumnDistinctValues[$colKey] = $actualVals;
            }
        }

        // Helper Closure Filter Usia Berdasarkan Kelompok Demografi
        $applyAgeFilter = function($q, $val, $colKey) {
            $valClean = str_replace(' ', '', strtolower($val));
            $minAge = null;
            $maxAge = null;

            if (is_numeric($valClean)) {
                $q->where('usia', (int)$valClean);
                return;
            }

            if ($valClean === '<18' || str_contains($valClean, '<18') || str_contains($valClean, 'dibawah18')) {
                $maxAge = 17;
            } elseif (str_contains($valClean, '18-25') || str_contains($valClean, '18sampai25')) {
                $minAge = 18; $maxAge = 25;
            } elseif (str_contains($valClean, '26-35') || str_contains($valClean, '26sampai35')) {
                $minAge = 26; $maxAge = 35;
            } elseif (str_contains($valClean, '36-45') || str_contains($valClean, '36sampai45')) {
                $minAge = 36; $maxAge = 45;
            } elseif (str_contains($valClean, '46-55') || str_contains($valClean, '46sampai55')) {
                $minAge = 46; $maxAge = 55;
            } elseif (str_contains($valClean, '>55') || str_contains($valClean, 'diatas55')) {
                $minAge = 56;
            }

            if ($minAge !== null && $maxAge !== null) {
                $q->whereBetween('usia', [$minAge, $maxAge]);
            } elseif ($minAge !== null) {
                $q->where('usia', '>=', $minAge);
            } elseif ($maxAge !== null) {
                $q->where('usia', '<=', $maxAge);
            } else {
                $q->where('usia', $val);
            }
        };

        $tableColsListing = \Illuminate\Support\Facades\Schema::getColumnListing('individus');
        $keluargaColsListing = \Illuminate\Support\Facades\Schema::getColumnListing('keluargas');

        // Helper Closure Filter Gaji Berdasarkan Kelompok Nominal / Exact Value
        $applySalaryFilter = function($q, $val, $colKey) use ($tableColsListing) {
            $valClean = str_replace(['.', ' ', 'rp', 'Rp', 'RP'], '', strtolower($val));
            $targetCol = in_array($colKey, $tableColsListing) ? $colKey : 'gaji_bulanan';
            if (!in_array($targetCol, $tableColsListing) && in_array('gaji', $tableColsListing)) {
                $targetCol = 'gaji';
            }

            $minSal = null;
            $maxSal = null;

            if (str_contains($valClean, '<3000000') || str_contains($valClean, '<3m')) {
                $maxSal = 2999999;
            } elseif (str_contains($valClean, '3000000-5000000') || str_contains($valClean, '3m-5m')) {
                $minSal = 3000000; $maxSal = 5000000;
            } elseif (str_contains($valClean, '5000000-10000000') || str_contains($valClean, '5m-10m')) {
                $minSal = 5000000; $maxSal = 10000000;
            } elseif (str_contains($valClean, '>10000000') || str_contains($valClean, '>10m')) {
                $minSal = 10000001;
            } else {
                $digits = preg_replace('/[^0-9.]/', '', $valClean);
                if (is_numeric($digits) && strlen($digits) > 0) {
                    $exactVal = (float)$digits;
                    $q->where(function($sub) use ($targetCol, $exactVal) {
                        $sub->where($targetCol, $exactVal)
                            ->orWhereRaw("CAST({$targetCol} AS TEXT) LIKE ?", ['%' . $exactVal . '%']);
                    });
                    return;
                }
            }

            if ($minSal !== null && $maxSal !== null) {
                $q->where(function($sub) use ($minSal, $maxSal) {
                    $sub->whereBetween('gaji_bulanan', [$minSal, $maxSal])
                        ->orWhereBetween('gaji', [$minSal, $maxSal]);
                });
            } elseif ($minSal !== null) {
                $q->where(function($sub) use ($minSal) {
                    $sub->where('gaji_bulanan', '>=', $minSal)
                        ->orWhere('gaji', '>=', $minSal);
                });
            } elseif ($maxSal !== null) {
                $q->where(function($sub) use ($maxSal) {
                    $sub->where('gaji_bulanan', '<=', $maxSal)
                        ->orWhere('gaji', '<=', $maxSal);
                });
            }
        };

        // Salary Query Filters
        $salaryQuery = clone $query;

        $allFilterableColumns = collect($activeColumnsMap)->reject(function($label, $key) {
            return in_array($key, ['id', 'created_at', 'updated_at']);
        })->toArray();

        foreach ($allFilterableColumns as $colKey => $colLabel) {
            $userVal = $request->input($colKey);
            if (!empty($userVal) && $userVal !== 'semua') {
                if (is_array($userVal)) {
                    $valArr = array_values(array_filter(array_map('trim', $userVal), fn($v) => $v !== '' && $v !== 'semua'));
                } else {
                    $valArr = array_values(array_filter(array_map('trim', explode(',', (string)$userVal)), fn($v) => $v !== '' && $v !== 'semua'));
                }

                if (empty($valArr)) {
                    continue;
                }

                $filterMultiBlock = function($q) use ($colKey, $colLabel, $valArr, $tableColsListing, $keluargaColsListing, $applyAgeFilter, $applySalaryFilter) {
                    $q->where(function($subQ) use ($colKey, $colLabel, $valArr, $tableColsListing, $keluargaColsListing, $applyAgeFilter, $applySalaryFilter) {
                        foreach ($valArr as $valItem) {
                            $subQ->orWhere(function($itemQ) use ($colKey, $colLabel, $valItem, $tableColsListing, $keluargaColsListing, $applyAgeFilter, $applySalaryFilter) {
                                $likeVal = '%' . strtolower(trim($valItem)) . '%';
                                $isAgeField = preg_match('/usia|umur|age/i', $colKey) || preg_match('/usia|umur|age/i', $colLabel);
                                $isSalaryField = preg_match('/gaji|pendapatan|omzet|upah|salary|income/i', $colKey) || preg_match('/gaji|pendapatan|omzet|upah|salary|income/i', $colLabel);

                                if ($isAgeField) {
                                    $applyAgeFilter($itemQ, $valItem, $colKey);
                                } elseif ($isSalaryField) {
                                    $applySalaryFilter($itemQ, $valItem, $colKey);
                                } elseif ($colKey === 'desil_nasional' || $colKey === 'desil') {
                                    $desilInt = (int)preg_replace('/[^0-9]/', '', $valItem);
                                    if ($desilInt >= 1 && $desilInt <= 10) {
                                        $kkList = DB::table('keluargas')->where('desil_nasional', $desilInt)->pluck('nomor_kartu_keluarga')->toArray();
                                    } else {
                                        $kkList = DB::table('keluargas')->whereRaw("LOWER(CAST(desil_nasional AS TEXT)) LIKE ?", [$likeVal])->pluck('nomor_kartu_keluarga')->toArray();
                                    }
                                    $itemQ->whereIn('nomor_kartu_keluarga', $kkList);
                                } elseif ($colKey === 'jenis_kelamin' || $colKey === 'gender' || $colKey === 'jk') {
                                    $itemQ->whereRaw("LOWER(jenis_kelamin) LIKE ?", [$likeVal]);
                                } elseif (in_array($colKey, $tableColsListing)) {
                                    $itemQ->whereRaw("LOWER({$colKey}) LIKE ?", [$likeVal]);
                                } elseif (in_array($colKey, $keluargaColsListing)) {
                                    $kkList = DB::table('keluargas')->whereRaw("LOWER({$colKey}) LIKE ?", [$likeVal])->pluck('nomor_kartu_keluarga')->toArray();
                                    $itemQ->whereIn('nomor_kartu_keluarga', $kkList);
                                } else {
                                    $itemQ->whereRaw("LOWER(JSON_EXTRACT(extra_attributes, '$.{$colKey}')) LIKE ?", [$likeVal]);
                                }
                            });
                        }
                    });
                };

                $salaryQuery->where($filterMultiBlock);
                $query->where($filterMultiBlock);
            }
        }

        // Combined Single SQL Aggregate Query for Gaji (5ms Execution)
        $gajiStats = (clone $salaryQuery)->selectRaw("
            MAX(gaji_bulanan) as max1, MAX(gaji) as max2,
            MIN(gaji_bulanan) as min1, MIN(gaji) as min2,
            AVG(gaji_bulanan) as avg1, AVG(gaji) as avg2,
            SUM(gaji_bulanan) as sum1, SUM(gaji) as sum2,
            COUNT(CASE WHEN gaji_bulanan > 0 OR gaji > 0 THEN 1 END) as cnt
        ")->first();

        $gajiMax = (float)($gajiStats->max1 ?: ($gajiStats->max2 ?: 0));
        $gajiMin = (float)($gajiStats->min1 ?: ($gajiStats->min2 ?: 0));
        $gajiAvg = round((float)($gajiStats->avg1 ?: ($gajiStats->avg2 ?: 0)), 2);
        $gajiSum = (float)($gajiStats->sum1 ?: ($gajiStats->sum2 ?: 0));
        $gajiCount = (int)($gajiStats->cnt ?? 0);

        $gajiMaxSubjek = $gajiCount > 0 ? (clone $salaryQuery)->orderByDesc('gaji_bulanan')->first() : null;
        $gajiMinSubjek = $gajiCount > 0 ? (clone $salaryQuery)->whereNotNull('gaji_bulanan')->orderBy('gaji_bulanan')->first() : null;
        $isSalaryFallback = ($gajiCount === 0);

        $hasSalaryColumn = array_key_exists('gaji_bulanan', $activeColumnsMap) || array_key_exists('gaji', $activeColumnsMap);

        // KPI Metrik Dinamis Berdasarkan Variabel Pilihan User (Maksimum, Minimum, Rata-Rata)
        $kpiTargetVar = $request->input('kpi_var', 'gaji_bulanan');
        if (!isset($activeColumnsMap[$kpiTargetVar]) && !empty($activeColumnsMap)) {
            $kpiTargetVar = array_key_first($activeColumnsMap);
        }

        if (in_array($kpiTargetVar, $tableColsListing)) {
            $kpiStats = (clone $query)->selectRaw("
                MAX({$kpiTargetVar}) as k_max,
                MIN({$kpiTargetVar}) as k_min,
                AVG({$kpiTargetVar}) as k_avg,
                SUM({$kpiTargetVar}) as k_sum
            ")->first();

            $kpiMax = (float)($kpiStats->k_max ?? 0);
            $kpiMin = (float)($kpiStats->k_min ?? 0);
            $kpiAvg = round((float)($kpiStats->k_avg ?? 0), 2);
            $kpiSum = (float)($kpiStats->k_sum ?? 0);
            $top5Records = (clone $query)->orderByDesc($kpiTargetVar)->take(5)->get();
            $bottom5Records = (clone $query)->whereNotNull($kpiTargetVar)->orderBy($kpiTargetVar)->take(5)->get();
        } else {
            $kpiMax = $gajiMax;
            $kpiMin = $gajiMin;
            $kpiAvg = $gajiAvg;
            $kpiSum = $gajiSum;
            $top5Records = (clone $query)->orderByDesc('id')->take(5)->get();
            $bottom5Records = (clone $query)->orderBy('id')->take(5)->get();
        }

        // Audit Trail Error List
        $issueRecords = (clone $query)->whereIn('quality_status', ['Critical', 'Warning'])->orderBy('quality_status', 'desc')->limit(6)->get();

        $page = (int)$request->input('page', 1);
        $perPage = 15;

        // Calculate accurate filtered count if any filter/search is active
        $hasActiveFilter = !empty($search) || $qualityStatus !== 'semua' || (!empty($filterCol) && !empty($filterVal) && $filterVal !== 'semua');
        if (!$hasActiveFilter) {
            foreach ($allFilterableColumns as $cKey => $cLabel) {
                if (!empty($request->input($cKey)) && $request->input($cKey) !== 'semua') {
                    $hasActiveFilter = true;
                    break;
                }
            }
        }
        $filteredTotal = $hasActiveFilter ? (clone $query)->count() : $totalRows;

        $items = (clone $query)->orderBy('id', 'desc')->skip(($page - 1) * $perPage)->take($perPage)->get();
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filteredTotal,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $records->fragment('tabel-data');
        $salaryFilterCol = $request->input('salary_filter_col');
        $salaryFilterVal = $request->input('salary_filter_val');

        $officialIndividuVars = self::getOfficialIndividuVariables();
        $officialKeluargaVars = self::getOfficialKeluargaVariables();

        return view('dtsen.index', compact(
            'records',
            'totalRows',
            'totalKk',
            'validCount',
            'warningCount',
            'criticalCount',
            'multiErrorCount',
            'errorCount',
            'issueRecords',
            'search',
            'desil',
            'qualityStatus',
            'jenisKelamin',
            'pendidikan',
            'statusBekerja',
            'statusKawin',
            'filterCol',
            'filterVal',
            'activeColumnsMap',
            'officialIndividuVars',
            'officialKeluargaVars',
            'kpiTargetVar',
            'kpiMax',
            'kpiMin',
            'kpiAvg',
            'kpiSum',
            'top5Records',
            'bottom5Records',
            'gajiCount',
            'gajiMax',
            'gajiMin',
            'gajiAvg',
            'gajiSum',
            'gajiMaxSubjek',
            'gajiMinSubjek',
            'isSalaryFallback',
            'importFilterableColumns',
            'importColumnDistinctValues',
            'salaryFilterCol',
            'salaryFilterVal',
            'hasJenisKelamin',
            'hasPendidikan',
            'hasDesil',
            'hasStatusBekerja',
            'hasStatusKawin',
            'hasSalaryColumn',
            'srcDtsenFiles',
            'srcExportFiles'
        ));
    }

    /**
     * Handler Impor File CSV / XLS / XLSX Standard (100% Python Powered for CSV)
     */
    public function importFile(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        $pyBin = $this->getPythonBinary();
        if (in_array($extension, ['csv', 'txt']) && $pyBin) {
            $pyScript = base_path('scratch/fast_import.py');
            $dbPath = database_path('database.sqlite');
            if (file_exists($pyScript)) {
                $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($file->getRealPath()) . " " . escapeshellarg($dbPath) . " 2>&1";
                $startT = microtime(true);
                $output = shell_exec($cmd);
                $elapsedT = round(microtime(true) - $startT, 2);

                if ($output && str_contains($output, '[SUCCESS]')) {
                    if (preg_match('/\[SUMMARY_STATS\]\s*(\{.*\})/', $output, $sm)) {
                        $parsedStats = json_decode($sm[1], true);
                        if (is_array($parsedStats)) {
                            session(['dataset_summary_stats' => $parsedStats]);
                        }
                    }
                    return redirect()->route('dtsen.index')->with('success', "Berhasil mengimpor berkas '{$originalName}' dalam {$elapsedT} detik.");
                }
            }
        }

        try {
            $result = DtsenImportService::parseAndImportFile($file->getRealPath(), $extension, $originalName, $fileSize);
            return redirect()->route('dtsen.index')->with('success', "Berhasil mengimpor & kalkulasi " . number_format($result['total_rows']) . " baris data! ({$result['inserted']} baru, {$result['updated']} diperbarui, {$result['invalid']} terdeteksi warning/critical).");
        } catch (\Exception $e) {
            return redirect()->route('dtsen.index')->with('error', "Gagal mengimpor file: " . $e->getMessage());
        }
    }

    /**
     * Handler Impor File Chunked Stream (100% Python Powered)
     */
    public function importChunk(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        try {
            $fileId = $request->input('file_id');
            $chunkIndex = (int)$request->input('chunk_index');
            $totalChunks = (int)$request->input('total_chunks');
            $fileName = $request->input('file_name');
            $fileSize = (int)$request->input('file_size');
            $chunkFile = $request->file('chunk');

            if (empty($fileId) || !$chunkFile) {
                return response()->json(['success' => false, 'message' => 'Chunk data tidak valid.'], 400);
            }

            $tempDir = storage_path('app/temp_imports');
            if (!file_exists($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }

            $tempPath = $tempDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $fileId) . '.tmp';
            
            // Append chunk stream
            $chunkContent = file_get_contents($chunkFile->getRealPath());
            file_put_contents($tempPath, $chunkContent, FILE_APPEND);

            // Jika ini chunk terakhir, proses impor file utuh
            if ($chunkIndex >= $totalChunks - 1) {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'csv';
                try {
                    $pyBin = $this->getPythonBinary();
                    if (in_array($extension, ['csv', 'txt']) && $pyBin) {
                        $pyScript = base_path('scratch/fast_import.py');
                        $dbPath = database_path('database.sqlite');
                        if (file_exists($pyScript)) {
                            $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($tempPath) . " " . escapeshellarg($dbPath) . " 2>&1";
                            $startT = microtime(true);
                            $output = shell_exec($cmd);
                            $elapsedT = round(microtime(true) - $startT, 2);
                            @unlink($tempPath);

                            if ($output && str_contains($output, '[SUCCESS]')) {
                                if (preg_match('/\[SUMMARY_STATS\]\s*(\{.*\})/', $output, $sm)) {
                                    $parsedStats = json_decode($sm[1], true);
                                    if (is_array($parsedStats)) {
                                        session(['dataset_summary_stats' => $parsedStats]);
                                    }
                                }
                                return response()->json([
                                    'success' => true,
                                    'is_complete' => true,
                                    'message' => "Berhasil mengimpor data dalam {$elapsedT} detik."
                                ]);
                            }
                        }
                    }

                    $result = DtsenImportService::parseAndImportFile($tempPath, $extension, $fileName, $fileSize);
                    @unlink($tempPath);
                    return response()->json([
                        'success' => true,
                        'is_complete' => true,
                        'message' => "Berhasil mengimpor & kalkulasi " . number_format($result['total_rows']) . " baris data! ({$result['invalid']} temuan warning/critical)."
                    ]);
                } catch (\Throwable $e) {
                    @unlink($tempPath);
                    return response()->json(['success' => false, 'message' => "Gagal mengimpor file: " . $e->getMessage()], 500);
                }
            }

            return response()->json([
                'success' => true,
                'is_complete' => false,
                'progress' => round((($chunkIndex + 1) / $totalChunks) * 100, 1)
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => "Gagal memproses chunk upload: " . $e->getMessage()], 500);
        }
    }

    /**
     * Handler Impor Berkas dari Folder src-dtsen / Disk Lokal (Python High-Speed Engine)
     */
    public function importLocalPath(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $selectedFile = trim($request->input('selected_file'));
        $localPath = trim($request->input('local_path'));
        
        $targetFile = !empty($selectedFile) ? $selectedFile : $localPath;

        if (empty($targetFile)) {
            $msg = 'Harap pilih berkas data dari folder src-dtsen.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->route('dtsen.index')->with('error', $msg);
        }

        // Resolusi jalur file ke folder src-dtsen atau base_path
        $fullPath = null;

        // 1. Cek di folder src-dtsen
        $srcPath = base_path('src-dtsen/' . ltrim($targetFile, '/\\'));
        if (file_exists($srcPath) && is_file($srcPath)) {
            $fullPath = $srcPath;
        } elseif (file_exists($targetFile) && is_file($targetFile)) {
            $fullPath = $targetFile;
        } elseif (file_exists(base_path($targetFile)) && is_file(base_path($targetFile))) {
            $fullPath = base_path($targetFile);
        }

        if (!$fullPath || !file_exists($fullPath)) {
            $msg = "Berkas '{$targetFile}' tidak ditemukan di folder src-dtsen. Pastikan berkas sudah dipindahkan ke folder src-dtsen/.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return redirect()->route('dtsen.index')->with('error', $msg);
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) ?: 'csv';
        $fileName = basename($fullPath);
        $fileSize = filesize($fullPath);

        // Jika file CSV/TXT, gunakan High-Speed Python Engine jika ada, atau fallback ke PHP
        $pyBin = $this->getPythonBinary();
        if (in_array($extension, ['csv', 'txt']) && $pyBin) {
            $pyScript = base_path('scratch/fast_import.py');
            $dbPath = database_path('database.sqlite');
            if (file_exists($pyScript)) {
                $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($fullPath) . " " . escapeshellarg($dbPath) . " 2>&1";
                $startT = microtime(true);
                $output = shell_exec($cmd);
                $elapsedT = round(microtime(true) - $startT, 2);

                if ($output && str_contains($output, '[SUCCESS]')) {
                    $uploadedHeaders = [
                        'nomor_induk_kependudukan' => 'NIK',
                        'nomor_kartu_keluarga' => 'Nomor KK',
                        'nama' => 'Nama Lengkap',
                        'gaji_bulanan' => 'Gaji Bulanan',
                        'desil_nasional' => 'Desil Kesejahteraan',
                        'usia' => 'Usia',
                        'jenis_kelamin' => 'Jenis Kelamin',
                    ];

                    if (preg_match('/\[HEADERS_JSON\]\s*(\[.*\])/', $output, $matches)) {
                        $parsedHeaders = json_decode($matches[1], true);
                        if (is_array($parsedHeaders) && count($parsedHeaders) > 0) {
                            $dynamicHeadersMap = [];
                            foreach ($parsedHeaders as $h) {
                                $k = strtolower(trim(str_replace([' ', '.'], '_', $h)));
                                $dynamicHeadersMap[$k] = ucwords(str_replace('_', ' ', $k));
                            }
                            if (!empty($dynamicHeadersMap)) {
                                $uploadedHeaders = array_merge($uploadedHeaders, $dynamicHeadersMap);
                            }
                        }
                    }

                    if (preg_match('/\[SUMMARY_STATS\]\s*(\{.*\})/', $output, $sm)) {
                        $parsedStats = json_decode($sm[1], true);
                        if (is_array($parsedStats)) {
                            session(['dataset_summary_stats' => $parsedStats]);
                        }
                    }

                    $rowCountStr = '';
                    if (preg_match('/Impor\s+([0-9.,]+)\s+baris/', $output, $matches)) {
                        $rowCountStr = $matches[1];
                    }

                    session([
                        'uploaded_headers' => $uploadedHeaders,
                        'uploaded_file_metrics' => [
                            'original_name' => $fileName,
                            'extension' => strtoupper($extension),
                            'file_size_formatted' => DtsenImportService::formatBytes($fileSize),
                            'file_size_bytes' => $fileSize,
                            'total_headers' => count($uploadedHeaders),
                            'mapped_variables' => count($uploadedHeaders),
                            'uploaded_at' => date('Y-m-d H:i:s'),
                        ]
                    ]);

                    $rowMsg = !empty($rowCountStr) ? "{$rowCountStr} baris data" : "data";
                    $successMsg = "Berhasil mengimpor {$rowMsg} dari 'src-dtsen/{$fileName}' dalam {$elapsedT} detik.";

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => $successMsg
                        ]);
                    }

                    return redirect()->route('dtsen.index')->with('success', $successMsg);
                }
            }
        }

        try {
            $result = DtsenImportService::parseAndImportFile($fullPath, $extension, $fileName, $fileSize);
            $msg = "Berhasil mengimpor & kalkulasi " . number_format($result['total_rows']) . " baris data dari folder 'src-dtsen/{$fileName}'! ({$result['invalid']} temuan warning/critical).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->route('dtsen.index')->with('success', $msg);
        } catch (\Throwable $e) {
            $msg = "Gagal mengimpor berkas dari folder src-dtsen: " . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->route('dtsen.index')->with('error', $msg);
        }
    }

    /**
     * Download Template Sample CSV / Excel (Lengkap dengan Sheet Kamus Variabel Metadata)
     */
    public function downloadTemplate(Request $request)
    {
        $format = strtolower($request->query('format', 'xlsx')); // xlsx atau csv

        if ($format === 'csv') {
            $csvContent = DtsenImportService::generateSampleCsvContent();
            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="template_dtsen_2026.csv"',
            ]);
        }

        return DtsenImportService::generateExcelTemplateResponse($format);
    }

    /**
     * Export Data Bundle (.ZIP Paket Multi-File: Data Utama + Metadata.txt + Audit Logs, Lalu Reset Sistem)
     */
    /**
     * Export Data Bundle (.ZIP Paket Multi-File ke Folder src-export/ via High-Speed Python Engine)
     */
    public function exportCsv(Request $request)
    {
        $mode = $request->input('mode', 'as_is');
        $filters = [
            'search' => trim($request->input('search', '')),
            'desil' => $request->input('desil', 'semua'),
            'jenis_kelamin' => $request->input('jenis_kelamin', 'semua'),
            'quality_status' => $request->input('quality_status', 'semua'),
        ];
        $paramsJson = json_encode($filters);

        $pyScript = base_path('scratch/fast_export.py');
        $dbPath = database_path('database.sqlite');
        $exportDir = base_path('src-export');

        if (!file_exists($exportDir)) {
            @mkdir($exportDir, 0777, true);
        }

        $pyBin = $this->getPythonBinary();
        if (file_exists($pyScript) && $pyBin) {
            $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($dbPath) . " " . escapeshellarg($exportDir) . " " . escapeshellarg($mode) . " " . escapeshellarg($paramsJson) . " 2>&1";
            $startT = microtime(true);
            $output = shell_exec($cmd);
            $elapsedT = round(microtime(true) - $startT, 2);

            if (str_contains($output, '[EXPORT_SUCCESS]')) {
                $zipName = '';
                $rowCountStr = '';
                $sizeStr = '';

                if (preg_match('/\[EXPORT_SUCCESS\]\s*([^\s|]+)\s*\|\s*([^\s|]+)\s*\|\s*([^\s|]+)/', $output, $matches)) {
                    $zipName = trim($matches[1]);
                    $rowCountStr = trim($matches[2]);
                    $sizeStr = trim($matches[3]);
                }

                session(['uploaded_headers' => null, 'uploaded_file_metrics' => null, 'dataset_summary_stats' => null]);
                
                $msgStr = !empty($zipName) ? "ke 'src-export/{$zipName}' ({$sizeStr})" : "ke folder 'src-export/'";
                return redirect()->route('dtsen.index')->with('success', "Berhasil mengekspor {$rowCountStr} baris data {$msgStr} dalam {$elapsedT} detik.");
            } else {
                return redirect()->route('dtsen.index')->with('error', "Gagal mengekspor data via Python Engine: " . substr($output, 0, 300));
            }
        }

        return redirect()->route('dtsen.index')->with('error', "Script Python fast_export.py tidak ditemukan.");
    }

    /**
     * Export Laporan Audit Baris Data Error / Warning (.csv) Langsung ke Folder src-export/
     */
    public function exportErrors()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $exportDir = base_path('src-export');
        if (!file_exists($exportDir)) {
            @mkdir($exportDir, 0777, true);
        }

        $fileName = 'laporan_audit_kualitas_data_dtsen_' . date('Ymd_His') . '.csv';
        $filePath = $exportDir . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($filePath, 'w');
        fputs($file, "\xEF\xBB\xBF");

        fputcsv($file, [
            'No. Baris',
            'Tingkat Validitas',
            'NIK',
            'No. KK',
            'Nama Lengkap',
            'Desil Kesejahteraan',
            'Wilayah (Prov/Kab/Kec)',
            'Rincian Temuan Error / Corrupt',
            'Rekomendasi Tindakan Perbaikan'
        ]);

        $no = 1;
        Individu::with('keluarga')
            ->whereIn('quality_status', ['Critical', 'Warning'])
            ->orderBy('quality_status', 'desc')
            ->chunk(2000, function($rows) use ($file, &$no) {
                foreach ($rows as $row) {
                    $issues = is_array($row->quality_issues) ? implode('; ', $row->quality_issues) : (string)$row->quality_issues;
                    
                    $rekomendasi = [];
                    if (str_contains($issues, 'NIK')) {
                        $rekomendasi[] = 'Perbaiki NIK di file master agar persis 16 digit angka';
                    }
                    if (str_contains($issues, 'KK')) {
                        $rekomendasi[] = 'Perbaiki Nomor KK di file master agar persis 16 digit angka';
                    }
                    if (str_contains($issues, 'Nama')) {
                        $rekomendasi[] = 'Isi nama lengkap subjek tanpa angka/simbol khusus';
                    }
                    if (str_contains($issues, 'RT/RW')) {
                        $rekomendasi[] = 'Lengkapi nomor RT dan RW KTP';
                    }
                    if (empty($rekomendasi)) {
                        $rekomendasi[] = 'Periksa kelengkapan variabel pendukung';
                    }

                    $wilayahStr = ($row->keluarga ? $row->keluarga->provinsi : 'DKI Jakarta') . ' / ' . ($row->keluarga ? $row->keluarga->kabupaten_kota : 'Jakarta');

                    fputcsv($file, [
                        $no++,
                        $row->quality_status === 'Critical' ? '🔴 CRITICAL ERROR' : '🟡 WARNING (MISSING VALUE)',
                        "'" . $row->nomor_induk_kependudukan,
                        "'" . $row->nomor_kartu_keluarga,
                        $row->nama,
                        'Desil ' . ($row->keluarga ? $row->keluarga->desil_nasional : '-'),
                        $wilayahStr,
                        $issues,
                        implode(' | ', $rekomendasi)
                    ]);
                }
            });

        fclose($file);

        $formattedSize = DtsenImportService::formatBytes(filesize($filePath));
        return redirect()->route('dtsen.index')->with('success', "🟢 Berhasil mengekspor Laporan Audit Kualitas Data ke folder 'src-export/{$fileName}' ({$formattedSize})!");
    }

    /**
     * Preview Single Individu & Aset KK Detail Modal
     */
    public function preview($id)
    {
        $individu = Individu::with('keluarga')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $individu->id,
                'nik' => $individu->nomor_induk_kependudukan,
                'masked_nik' => $individu->masked_nik,
                'nama' => $individu->nama,
                'masked_nama' => $individu->masked_nama,
                'nomor_kartu_keluarga' => $individu->nomor_kartu_keluarga,
                'jenis_kelamin' => $individu->jenis_kelamin,
                'tanggal_lahir' => $individu->tanggal_lahir ? $individu->tanggal_lahir->format('d F Y') : '-',
                'usia' => $individu->usia . ' Tahun',
                'status_hubungan' => $individu->status_hubungan_keluarga,
                'status_bekerja' => $individu->status_bekerja,
                'quality_status' => $individu->quality_status,
                'quality_issues' => $individu->quality_issues ?: [],
                'keluarga' => $individu->keluarga ? [
                    'desil_nasional' => $individu->keluarga->desil_nasional,
                    'provinsi' => $individu->keluarga->provinsi,
                    'kabupaten_kota' => $individu->keluarga->kabupaten_kota,
                    'kecamatan' => $individu->keluarga->kecamatan,
                    'alamat' => $individu->keluarga->alamat,
                    'jenis_lantai' => $individu->keluarga->label_jenis_lantai,
                    'jenis_atap' => $individu->keluarga->label_jenis_atap,
                    'jumlah_ternak_sapi' => $individu->keluarga->jumlah_ternak_sapi,
                    'jumlah_ternak_kambing' => $individu->keluarga->jumlah_ternak_kambing_domba,
                ] : null
            ]
        ]);
    }

    /**
     * Generate Data Demo DTSEN
     */
    public function generateDemo()
    {
        $count = DtsenImportService::generateDemoData(100);
        return redirect()->route('dtsen.index')->with('success', "Berhasil membuat {$count} data demo (100 baris dengan 30 error)!");
    }

    /**
     * Clear / Reset Data Sistem (100% Python Fast Engine - Kosongkan 15M+ Data dalam 0.2 Detik)
     */
    public function clearData()
    {
        $pyScript = base_path('scratch/fast_delete.py');
        $dbPath = database_path('database.sqlite');

        $pyBin = $this->getPythonBinary();
        if (file_exists($pyScript) && $pyBin) {
            $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($dbPath) . " 2>&1";
            shell_exec($cmd);
        } else {
            Individu::query()->delete();
            Keluarga::query()->delete();
            Demographic::query()->delete();
        }

        session(['uploaded_headers' => null, 'uploaded_file_metrics' => null, 'dataset_summary_stats' => null]);

        return redirect()->route('dtsen.index')->with('success', 'Seluruh data dataset berhasil dikosongkan.');
    }

    /**
     * Ambil Status Progress Realtime Impor Data & Estimasi Sisa Waktu (ETA)
     */
    public function getImportProgress()
    {
        $progFile = base_path('scratch/import_progress.json');
        if (file_exists($progFile)) {
            $content = file_get_contents($progFile);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return response()->json($data);
            }
        }
        return response()->json([
            'percent' => 0,
            'message' => 'Menyiapkan pemrosesan berkas...',
            'eta_seconds' => 0
        ]);
    }

    /**
     * Cari perintah/path Python yang valid di sistem (support Portable Python, Windows, Linux, Mac).
     */
    private function getPythonBinary(): ?string
    {
        if ($envPy = env('PYTHON_BINARY')) {
            return $envPy;
        }

        $candidates = [];

        // 1. Cek Portable Python lokal di folder proyek
        $localPy = base_path('python/python.exe');
        if (file_exists($localPy)) {
            $candidates[] = '"' . $localPy . '"';
        }

        // 2. Command standar
        $candidates = array_merge($candidates, ['python', 'py', 'python3']);

        // 3. Path populer Python di Windows jika tidak ada di System PATH
        $userProfile = getenv('USERPROFILE') ?: getenv('HOME');
        if ($userProfile) {
            foreach (range(12, 8, -1) as $ver) {
                $candidates[] = "{$userProfile}\\AppData\\Local\\Programs\\Python\\Python3{$ver}\\python.exe";
            }
        }
        foreach (range(12, 8, -1) as $ver) {
            $candidates[] = "C:\\Python3{$ver}\\python.exe";
        }

        foreach ($candidates as $bin) {
            $escaped = (str_starts_with($bin, '"') || (!str_contains($bin, ' ') && !str_contains($bin, '\\'))) ? $bin : '"' . $bin . '"';
            $out = @shell_exec("{$escaped} --version 2>&1");
            if ($out && preg_match('/Python\s+3\./i', $out)) {
                return $escaped;
            }
        }

        return null;
    }
}


