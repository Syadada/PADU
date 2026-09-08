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
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        \Illuminate\Support\Facades\Log::info('[DTSEN CONTROLLER] Index loaded', [
            'url' => $request->fullUrl(),
            'query_params' => $request->all(),
        ]);

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

        $page = (int)$request->input('page', 1);
        $perPage = 15;

        // Execute Page Query via Pure DuckDB High-Speed Engine
        $duckPageData = $this->runDuckDbQuery('page_data', [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'quality_status' => $qualityStatus,
            'filters' => $request->all()
        ]) ?? [
            'total_system_rows' => 0,
            'filtered_total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'items' => [],
            'stats' => ['total_rows' => 0, 'total_kk' => 0, 'valid_count' => 0, 'warning_count' => 0, 'critical_count' => 0, 'multi_error_count' => 0, 'error_count' => 0],
            'issue_items' => []
        ];

        $totalSystemRows = (int)($duckPageData['total_system_rows'] ?? 0);

        if ($totalSystemRows === 0) {
            $activeColumnsMap = [];
        } else {
            $masterDict = self::getAllOfficialVariables();
            $duckDbCols = $duckPageData['db_columns'] ?? session('uploaded_headers', []);
            if (empty($duckDbCols) && !empty($duckPageData['items'][0])) {
                $duckDbCols = array_keys((array)$duckPageData['items'][0]);
            }

            // Exclude system internal / alias columns
            $systemAliases = ['id', 'created_at', 'updated_at', 'extra_attributes', 'quality_status', 'quality_issues', 'nik', 'no_kk', 'kk', 'nama_lengkap', 'gaji', 'desil', 'umur'];
            $activeColumnsMap = [];

            if (!empty($duckDbCols)) {
                foreach ($duckDbCols as $k => $v) {
                    $colKey = is_int($k) ? strtolower(trim((string)$v)) : strtolower(trim((string)$k));
                    if (in_array($colKey, $systemAliases)) {
                        continue;
                    }
                    if (isset($masterDict[$colKey])) {
                        $activeColumnsMap[$colKey] = $masterDict[$colKey];
                    } else {
                        $label = (is_string($v) && !is_numeric($k)) ? $v : ucwords(str_replace(['_', '-'], ' ', $colKey));
                        $activeColumnsMap[$colKey] = $label;
                    }
                }
            }

            if (empty($activeColumnsMap)) {
                $activeColumnsMap = $masterDict;
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

        $totalRows = (int)($duckPageData['stats']['total_rows'] ?? $totalSystemRows);
        $totalKk = (int)($duckPageData['stats']['total_kk'] ?? 0);
        $validCount = (int)($duckPageData['stats']['valid_count'] ?? 0);
        $warningCount = (int)($duckPageData['stats']['warning_count'] ?? 0);
        $criticalCount = (int)($duckPageData['stats']['critical_count'] ?? 0);
        $multiErrorCount = (int)($duckPageData['stats']['multi_error_count'] ?? 0);
        $errorCount = $criticalCount + $warningCount;

        // Map items to stdClass objects with masked NIK & Nama
        $items = collect($duckPageData['items'] ?? [])->map(function($item) {
            $obj = (object)$item;
            $nik = (string)($item['nomor_induk_kependudukan'] ?? $item['nik'] ?? '');
            $nama = (string)($item['nama'] ?? $item['nama_lengkap'] ?? '');
            $obj->masked_nik = strlen($nik) >= 8 ? (substr($nik, 0, 4) . '********' . substr($nik, -4)) : '****************';
            $obj->masked_nama = strlen($nama) >= 2 ? (substr($nama, 0, 2) . str_repeat('*', max(1, strlen($nama) - 2))) : '***';
            
            $issues = $item['quality_issues'] ?? [];
            if (is_string($issues)) {
                $issues = json_decode($issues, true) ?: [$issues];
            }
            $obj->quality_issues = $issues ?: [];
            return $obj;
        });

        $filteredTotal = (int)($duckPageData['filtered_total'] ?? 0);

        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filteredTotal,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $records->fragment('tabel-data');

        // Audit Trail Error List
        $issueRecords = collect($duckPageData['issue_items'] ?? [])->map(function($item) {
            $obj = (object)$item;
            $nik = (string)($item['nomor_induk_kependudukan'] ?? $item['nik'] ?? ($obj->nomor_induk_kependudukan ?? ($obj->nik ?? '')));
            $nama = (string)($item['nama'] ?? $item['nama_lengkap'] ?? ($obj->nama ?? ($obj->nama_lengkap ?? '')));
            $obj->nama = $nama;
            $obj->nomor_induk_kependudukan = $nik;
            $obj->masked_nik = strlen($nik) >= 8 ? (substr($nik, 0, 4) . '********' . substr($nik, -4)) : ($nik ?: '****************');
            $obj->masked_nama = strlen($nama) >= 2 ? (substr($nama, 0, 2) . str_repeat('*', max(1, strlen($nama) - 2))) : ($nama ?: '***');
            $obj->id = $obj->id ?? ($nik ?: rand(1000, 9999));
            $issues = $item['quality_issues'] ?? [];
            if (is_string($issues)) {
                $issues = json_decode($issues, true) ?: [$issues];
            }
            $obj->quality_issues = $issues ?: [];
            return $obj;
        });

        // Filterable columns
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

        $hasJenisKelamin = array_key_exists('jenis_kelamin', $activeColumnsMap) || array_key_exists('gender', $activeColumnsMap) || array_key_exists('jk', $activeColumnsMap);
        $hasPendidikan = array_key_exists('pendidikan', $activeColumnsMap) || array_key_exists('ijazah_tertinggi_yang_dimiliki', $activeColumnsMap) || array_key_exists('jenjang_tertinggi_yang_diduduki', $activeColumnsMap) || array_key_exists('pendidikan_terakhir', $activeColumnsMap);
        $hasDesil = array_key_exists('desil_nasional', $activeColumnsMap) || array_key_exists('desil', $activeColumnsMap);
        $hasStatusBekerja = array_key_exists('status_bekerja', $activeColumnsMap) || array_key_exists('pekerjaan', $activeColumnsMap) || array_key_exists('status_kerja', $activeColumnsMap);
        $hasStatusKawin = array_key_exists('status_kawin', $activeColumnsMap) || array_key_exists('status_pernikahan', $activeColumnsMap);
        $hasSalaryColumn = array_key_exists('gaji_bulanan', $activeColumnsMap) || array_key_exists('gaji', $activeColumnsMap);

        // Distinct values for filter dropdowns
        $importColumnDistinctValues = session('import_distinct_values', null);
        if (!is_array($importColumnDistinctValues) || empty($importColumnDistinctValues)) {
            $duckDbRes = $this->runDuckDbQuery('distinct_values', [
                'columns' => array_keys($activeColumnsMap)
            ]);
            $importColumnDistinctValues = $duckDbRes['distinct_map'] ?? [];
            session(['import_distinct_values' => $importColumnDistinctValues]);
        }

        // KPI Metrik via DuckDB
        $kpiTargetVar = $request->input('kpi_var', 'gaji_bulanan');
        if (!isset($activeColumnsMap[$kpiTargetVar]) && !empty($activeColumnsMap)) {
            $kpiTargetVar = array_key_first($activeColumnsMap);
        }

        $duckKpi = $this->runDuckDbQuery('kpi_metrics', [
            'kpi_var' => $kpiTargetVar,
            'search' => $search,
            'quality_status' => $qualityStatus,
            'filters' => $request->all()
        ]);

        $gajiMax = (float)($duckKpi['max'] ?? 0);
        $gajiMin = (float)($duckKpi['min'] ?? 0);
        $gajiAvg = round((float)($duckKpi['avg'] ?? 0), 2);
        $gajiSum = (float)($duckKpi['sum'] ?? 0);
        $gajiCount = (int)($duckKpi['count'] ?? 0);

        $kpiMax = $gajiMax;
        $kpiMin = $gajiMin;
        $kpiAvg = $gajiAvg;
        $kpiSum = $gajiSum;

        $mapRecordWithMasking = function($item) {
            $obj = (object)$item;
            $nik = (string)($item['nomor_induk_kependudukan'] ?? $item['nik'] ?? ($obj->nomor_induk_kependudukan ?? ($obj->nik ?? '')));
            $nama = (string)($item['nama'] ?? $item['nama_lengkap'] ?? ($obj->nama ?? ($obj->nama_lengkap ?? '')));
            $obj->nama = $nama;
            $obj->nomor_induk_kependudukan = $nik;
            $obj->masked_nik = strlen($nik) >= 8 ? (substr($nik, 0, 4) . '********' . substr($nik, -4)) : ($nik ?: '****************');
            $obj->masked_nama = strlen($nama) >= 2 ? (substr($nama, 0, 2) . str_repeat('*', max(1, strlen($nama) - 2))) : ($nama ?: '***');
            $obj->id = $obj->id ?? ($nik ?: rand(1000, 9999));
            $obj->val = $item['val'] ?? ($obj->val ?? null);
            return $obj;
        };

        $top5Records = collect($duckKpi['top5'] ?? [])->map($mapRecordWithMasking);
        $bottom5Records = collect($duckKpi['bot5'] ?? [])->map($mapRecordWithMasking);
        $gajiMaxSubjek = $top5Records->first() ?: null;
        $gajiMinSubjek = $bottom5Records->first() ?: null;
        $isSalaryFallback = ($gajiCount === 0);

        $salaryFilterCol = $request->input('salary_filter_col');
        $salaryFilterVal = $request->input('salary_filter_val');

        $allIndividuDict = self::getOfficialIndividuVariables();
        $allKeluargaDict = self::getOfficialKeluargaVariables();

        $officialIndividuVars = array_intersect_key($allIndividuDict, $activeColumnsMap);
        $officialKeluargaVars = array_intersect_key($allKeluargaDict, $activeColumnsMap);

        $viewData = compact(
            'records',
            'totalRows',
            'totalSystemRows',
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
        );

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $html = view('dtsen.index', $viewData)->render();
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        }

        return view('dtsen.index', $viewData);
    }

    /**
     * Helper proses output dari Python Fast Import Engine
     */
    private function processImportOutput(string $output, string $fileName, int $fileSize): void
    {
        $uploadedHeaders = self::getAllOfficialVariables();

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

        if (preg_match('/\[DISTINCT_MAP_JSON\]\s*(\{.*\})/', $output, $dm)) {
            $parsedMap = json_decode($dm[1], true);
            if (is_array($parsedMap) && isset($parsedMap['distinct_map'])) {
                session(['import_distinct_values' => $parsedMap['distinct_map']]);
            }
        }

        session([
            'uploaded_headers' => $uploadedHeaders,
            'uploaded_file_metrics' => [
                'original_name' => $fileName,
                'file_size_formatted' => DtsenImportService::formatBytes($fileSize),
                'file_size_bytes' => $fileSize,
                'total_headers' => count($uploadedHeaders),
                'mapped_variables' => count($uploadedHeaders),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]
        ]);
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
                    $this->processImportOutput($output, $originalName, $fileSize);
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
                                $this->processImportOutput($output, $fileName, $fileSize);
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
                    $this->processImportOutput($output, $fileName, $fileSize);

                    $rowCountStr = '';
                    if (preg_match('/Impor\s+([0-9.,]+)\s+baris/', $output, $matches)) {
                        $rowCountStr = $matches[1];
                    }

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
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
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

                $zipInfo = !empty($zipName) ? " ke 'src-export/{$zipName}' ({$sizeStr})" : "";
                return redirect()->route('dtsen.index')->with('success', "🟢 Data ({$rowCountStr} baris) selesai diekspor{$zipInfo} dalam {$elapsedT} detik.");
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
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $pyScript = base_path('scratch/fast_export.py');
        $dbPath = database_path('database.sqlite');
        $exportDir = base_path('src-export');

        if (!file_exists($exportDir)) {
            @mkdir($exportDir, 0777, true);
        }

        $pyBin = $this->getPythonBinary();
        if (file_exists($pyScript) && $pyBin) {
            $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($dbPath) . " " . escapeshellarg($exportDir) . " 'errors_only' '{}' 2>&1";
            $startT = microtime(true);
            $output = shell_exec($cmd);
            $elapsedT = round(microtime(true) - $startT, 2);

            if (str_contains($output, '[EXPORT_SUCCESS]')) {
                $fileName = '';
                $rowCountStr = '';
                $sizeStr = '';

                if (preg_match('/\[EXPORT_SUCCESS\]\s*([^\s|]+)\s*\|\s*([^\s|]+)\s*\|\s*([^\s|]+)/', $output, $matches)) {
                    $fileName = trim($matches[1]);
                    $rowCountStr = trim($matches[2]);
                    $sizeStr = trim($matches[3]);
                }
                
                return redirect()->route('dtsen.index')->with('success', "🟢 Laporan Audit Kualitas Data ({$rowCountStr} baris) berhasil diekspor ke 'src-export/{$fileName}' ({$sizeStr}) dalam {$elapsedT} detik.");
            } else {
                return redirect()->route('dtsen.index')->with('error', "Gagal mengekspor Laporan Audit Data: " . substr($output, 0, 300));
            }
        }

        return redirect()->route('dtsen.index')->with('error', "Script Python fast_export.py tidak ditemukan.");
    }

    /**
     * Preview Single Individu & Aset KK Detail Modal
     */
    public function preview($id)
    {
        $duckPreview = $this->runDuckDbQuery('preview', ['id' => (int)$id]);
        $row = $duckPreview['item'] ?? null;

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        $nik = (string)($row['nomor_induk_kependudukan'] ?? $row['nik'] ?? '-');
        $nama = (string)($row['nama'] ?? $row['nama_lengkap'] ?? '-');
        $maskedNik = strlen($nik) >= 8 ? (substr($nik, 0, 4) . '********' . substr($nik, -4)) : '****************';
        $maskedNama = strlen($nama) >= 2 ? (substr($nama, 0, 2) . str_repeat('*', max(1, strlen($nama) - 2))) : '***';

        $qualityIssues = $row['quality_issues'] ?? [];
        if (is_string($qualityIssues)) {
            $qualityIssues = json_decode($qualityIssues, true) ?: [$qualityIssues];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row['id'] ?? $id,
                'nik' => $nik,
                'masked_nik' => $maskedNik,
                'nama' => $nama,
                'masked_nama' => $maskedNama,
                'nomor_kartu_keluarga' => $row['nomor_kartu_keluarga'] ?? $row['no_kk'] ?? '-',
                'jenis_kelamin' => $row['jenis_kelamin'] ?? '-',
                'tanggal_lahir' => $row['tanggal_lahir'] ?? '-',
                'usia' => ($row['usia'] ?? '-') . ' Tahun',
                'status_hubungan' => $row['status_hubungan_keluarga'] ?? '-',
                'status_bekerja' => $row['status_bekerja'] ?? '-',
                'quality_status' => $row['quality_status'] ?? 'Valid',
                'quality_issues' => $qualityIssues ?: [],
                'keluarga' => [
                    'desil_nasional' => $row['desil_nasional'] ?? $row['desil'] ?? '-',
                    'provinsi' => $row['provinsi'] ?? '-',
                    'kabupaten_kota' => $row['kabupaten_kota'] ?? '-',
                    'kecamatan' => $row['kecamatan'] ?? '-',
                    'alamat' => $row['alamat'] ?? '-',
                    'jenis_lantai' => $row['jenis_lantai_terluas'] ?? '-',
                    'jenis_atap' => $row['jenis_atap_terluas'] ?? '-',
                    'jumlah_ternak_sapi' => $row['jumlah_ternak_sapi'] ?? 0,
                    'jumlah_ternak_kambing' => $row['jumlah_ternak_kambing_domba'] ?? 0,
                ]
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

        session(['uploaded_headers' => null, 'uploaded_file_metrics' => null, 'dataset_summary_stats' => null, 'import_distinct_values' => null]);

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
     * Helper Eksekusi Query Instant DuckDB OLAP Engine
     */
    private function runDuckDbQuery(string $mode, array $params = [])
    {
        $pyBin = $this->getPythonBinary();
        $pyScript = base_path('scratch/fast_duckdb.py');
        $dbPath = database_path('database.sqlite');

        if (file_exists($pyScript) && $pyBin) {
            $base64Params = base64_encode(json_encode($params));
            $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($dbPath) . " " . escapeshellarg($mode) . " " . escapeshellarg($base64Params) . " 2>&1";
            $output = shell_exec($cmd);

            if ($mode === 'page_data' && preg_match('/\[DUCKDB_PAGE_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            } elseif ($mode === 'distinct_values' && preg_match('/\[DUCKDB_DISTINCT_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            } elseif ($mode === 'kpi_metrics' && preg_match('/\[DUCKDB_KPI_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            } elseif ($mode === 'top_bottom_5' && preg_match('/\[DUCKDB_RANKING_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            } elseif ($mode === 'preview' && preg_match('/\[DUCKDB_PREVIEW_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            } elseif ($mode === 'total_system_rows' && preg_match('/\[DUCKDB_COUNT_RESULT\]\s*(\{.*\})/', $output, $m)) {
                return json_decode($m[1], true);
            }
        }
        return null;
    }

    /**
     * Cari perintah/path Python yang valid di sistem (support Portable Python, Windows, Linux, Mac).
     */
    private function getPythonBinary(): ?string
    {
        static $cachedPyBin = null;
        if ($cachedPyBin !== null) {
            return $cachedPyBin;
        }

        if ($envPy = env('PYTHON_BINARY')) {
            return $cachedPyBin = $envPy;
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
                // Verifikasi modul duckdb
                $duckCheck = @shell_exec("{$escaped} -c \"import duckdb\" 2>&1");
                if ($duckCheck && str_contains($duckCheck, 'ModuleNotFoundError')) {
                    // Otomatis install duckdb jika belum ada
                    @shell_exec("{$escaped} -m pip install duckdb 2>&1");
                }
                return $cachedPyBin = $escaped;
            }
        }

        return null;
    }
}


