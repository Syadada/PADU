<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BastMetadataQualityService
{
    private static string $rulesFilePath = 'bast_metadata/active_rules.json';
    private static string $defaultExcelPath = 'bast_metadata/Versi_3_2026_Lampiran_BAST_Metadata_DTSEN.xlsx';

    /**
     * Dapatkan aturan metadata BAST aktif
     */
    public static function getActiveRules(): array
    {
        $storagePath = storage_path('app/' . self::$rulesFilePath);
        
        if (file_exists($storagePath)) {
            $content = file_get_contents($storagePath);
            $json = json_decode($content, true);
            if (is_array($json) && !empty($json['datasets'])) {
                return $json;
            }
        }

        // Jika belum ada JSON, coba generate otomatis dari file Excel bawaan
        self::rebuildFromExcel(storage_path('app/' . self::$defaultExcelPath));

        if (file_exists($storagePath)) {
            $json = json_decode(file_get_contents($storagePath), true);
            if (is_array($json)) {
                return $json;
            }
        }

        return [
            'metadata_info' => [
                'version' => '3/2026',
                'title' => 'Metadata DTSEN Versi 3/2026 BPS-Bappenas',
                'source' => 'BPS-Bappenas'
            ],
            'datasets' => [
                'keluarga' => ['title' => 'Set Data Keluarga', 'variables' => []],
                'anggota_keluarga' => ['title' => 'Set Data Anggota Keluarga', 'variables' => []]
            ],
            'total_variables' => 0
        ];
    }

    /**
     * Dapatkan informasi ringkas versi metadata aktif
     */
    public static function getVersionInfo(): array
    {
        $rules = self::getActiveRules();
        $storagePath = storage_path('app/' . self::$rulesFilePath);
        $lastModified = file_exists($storagePath) ? date('d/m/Y H:i:s', filemtime($storagePath)) : date('d/m/Y H:i:s');

        return [
            'version' => $rules['metadata_info']['version'] ?? 'Versi 3/2026',
            'title' => $rules['metadata_info']['title'] ?? 'Lampiran BAST Metadata DTSEN BPS-Bappenas',
            'source' => $rules['metadata_info']['source'] ?? 'BPS-Bappenas',
            'total_variables' => $rules['total_variables'] ?? 100,
            'total_keluarga' => $rules['total_keluarga_vars'] ?? 52,
            'total_anggota' => $rules['total_anggota_vars'] ?? 48,
            'last_trained_at' => $rules['parsed_at'] ?? $lastModified,
            'rules_file_size' => file_exists($storagePath) ? DtsenImportService::formatBytes(filesize($storagePath)) : '0 KB',
            'excel_exists' => file_exists(storage_path('app/' . self::$defaultExcelPath))
        ];
    }

    /**
     * Latih & perbarui aturan dari file Excel BAST yang diunggah
     */
    public static function trainFromUploadedExcel(UploadedFile $file, ?string $customVersionName = null): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            throw new \InvalidArgumentException('Format berkas harus berupa Excel (.xlsx atau .xls).');
        }

        $dir = storage_path('app/bast_metadata');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $safeName = 'BAST_Metadata_' . ($customVersionName ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $customVersionName) : 'Upload') . '_' . $timestamp . '.' . $ext;
        $targetPath = $dir . DIRECTORY_SEPARATOR . $safeName;

        $file->move($dir, $safeName);

        // Update default link juga jika berhasil
        $result = self::rebuildFromExcel($targetPath);
        
        if ($result['success']) {
            // Perbarui file default dengan yang baru
            copy($targetPath, storage_path('app/' . self::$defaultExcelPath));
        }

        return array_merge($result, [
            'archived_file' => $safeName,
            'file_size' => DtsenImportService::formatBytes(file_exists($targetPath) ? filesize($targetPath) : 0),
        ]);
    }

    /**
     * Jalankan parser Python untuk membedah Excel BAST ke JSON rules
     */
    public static function rebuildFromExcel(string $excelFullPath): array
    {
        if (!file_exists($excelFullPath)) {
            return [
                'success' => false,
                'message' => 'Berkas Excel tidak ditemukan pada path: ' . $excelFullPath
            ];
        }

        $pyScript = base_path('scratch/parse_bast_metadata.py');
        $pyBin = self::getPythonBinary();

        if (!$pyBin) {
            return [
                'success' => false,
                'message' => 'Python runtime tidak ditemukan di server.'
            ];
        }

        $cmd = "{$pyBin} " . escapeshellarg($pyScript) . " " . escapeshellarg($excelFullPath) . " 2>&1";
        $output = shell_exec($cmd);

        $rulesFile = storage_path('app/' . self::$rulesFilePath);
        if (file_exists($rulesFile)) {
            $data = json_decode(file_get_contents($rulesFile), true);
            return [
                'success' => true,
                'message' => 'Berhasil melatih dan mengekstrak aturan metadata BAST DTSEN.',
                'version' => $data['metadata_info']['version'] ?? 'Versi 3',
                'title' => $data['metadata_info']['title'] ?? '',
                'total_variables' => $data['total_variables'] ?? 0,
                'total_keluarga' => $data['total_keluarga_vars'] ?? 0,
                'total_anggota' => $data['total_anggota_vars'] ?? 0,
                'output' => $output
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal memproses berkas Excel: ' . $output
        ];
    }

    /**
     * Evaluasi kualitas satu baris data berdasarkan kamus metadata BAST DTSEN aktif
     */
    public static function evaluateRow(array $row): array
    {
        $rules = self::getActiveRules();
        $issues = [];
        $status = 'Valid';

        // Index kamus variabel dari kedua sheet
        $varDict = [];
        foreach (['anggota_keluarga', 'keluarga'] as $dsKey) {
            foreach ($rules['datasets'][$dsKey]['variables'] ?? [] as $v) {
                $varDict[strtolower($v['key'])] = $v;
            }
        }

        // Resolusi input row ke canonical keys
        $cleanRow = [];
        foreach ($row as $k => $val) {
            $lowerKey = strtolower(trim((string)$k));
            $cleanRow[$lowerKey] = is_string($val) ? trim($val) : $val;
        }

        // 1. EVALUASI NIK (Nomor Induk Kependudukan)
        $nikVal = $cleanRow['nomor_induk_kependudukan'] ?? ($cleanRow['nik'] ?? null);
        if ($nikVal === null || $nikVal === '') {
            $status = 'Critical';
            $issues[] = '[CRITICAL] NIK kosong / belum terisi.';
        } else {
            $cleanNik = preg_replace('/\.0+$/', '', (string)$nikVal);
            if (!preg_match('/^[0-9]{16}$/', $cleanNik)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] NIK '{$nikVal}' tidak valid. Harus persis 16 digit angka murni.";
            }
        }

        // 2. EVALUASI NOMOR KARTU KELUARGA (KK)
        $kkVal = $cleanRow['nomor_kartu_keluarga'] ?? ($cleanRow['no_kk'] ?? ($cleanRow['kk'] ?? null));
        if ($kkVal !== null && $kkVal !== '') {
            $cleanKk = preg_replace('/\.0+$/', '', (string)$kkVal);
            if (!preg_match('/^[0-9]{16}$/', $cleanKk)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK '{$kkVal}' tidak valid. Harus persis 16 digit angka murni (tanpa titik desimal).";
            }
        }

        // 3. EVALUASI NAMA LENGKAP
        $namaVal = $cleanRow['nama'] ?? ($cleanRow['nama_lengkap'] ?? null);
        if ($namaVal === null || $namaVal === '') {
            $status = 'Critical';
            $issues[] = '[CRITICAL] Nama lengkap kosong.';
        } else {
            if (preg_match('/[0-9]/', (string)$namaVal)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama '{$namaVal}' tidak valid (mengandung angka).";
            } elseif (!preg_match('/^[a-zA-Z\s\.\,\'\-]+$/u', (string)$namaVal)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama '{$namaVal}' tidak valid (mengandung karakter simbol ilegal).";
            }
        }

        // 4. EVALUASI DESIL KESEJAHTERAAN
        $desilVal = $cleanRow['desil_nasional'] ?? ($cleanRow['desil'] ?? null);
        if ($desilVal !== null && $desilVal !== '') {
            $dInt = (int)$desilVal;
            if ($dInt < 1 || $dInt > 10) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Desil '{$desilVal}' di luar jangkauan valid (1 s.d. 10).";
            }
        }

        // 5. EVALUASI USIA / UMUR
        $usiaVal = $cleanRow['usia'] ?? ($cleanRow['umur'] ?? null);
        if ($usiaVal !== null && $usiaVal !== '') {
            if (!is_numeric($usiaVal) || (int)$usiaVal < 0 || (int)$usiaVal > 120) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nilai Usia '{$usiaVal}' tidak valid (harus 0 s.d. 120 tahun).";
            }
        }

        // 6. EVALUASI KODE KATEGORIKAL BERDASARKAN KAMUS BAST TERLATIH
        foreach ($cleanRow as $col => $val) {
            if ($val === null || $val === '') continue;
            if (isset($varDict[$col])) {
                $metaVar = $varDict[$col];
                $allowedCodes = $metaVar['rules']['allowed_codes'] ?? [];
                
                if (!empty($allowedCodes)) {
                    $validCodes = array_column($allowedCodes, 'code');
                    $validLabels = array_map('strtolower', array_column($allowedCodes, 'label'));
                    $strVal = strtolower((string)$val);
                    
                    $matched = in_array((string)$val, $validCodes) || in_array($strVal, $validLabels);
                    if (!$matched) {
                        // Cek substring label (misal 'pria' vs 'laki-laki')
                        foreach ($validLabels as $vLbl) {
                            if (str_contains($strVal, $vLbl) || str_contains($vLbl, $strVal)) {
                                $matched = true;
                                break;
                            }
                        }
                    }

                    if (!$matched) {
                        if ($status === 'Valid') $status = 'Warning';
                        $sampleCodes = implode(', ', array_slice(array_column($allowedCodes, 'label'), 0, 4));
                        $issues[] = "[WARNING] Nilai variabel '{$metaVar['label']}' ('{$val}') tidak sesuai pilihan standar BAST DTSEN ({$sampleCodes}).";
                    }
                }
            }
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'is_valid' => ($status === 'Valid'),
            'is_critical' => ($status === 'Critical'),
            'is_warning' => ($status === 'Warning'),
            'total_tested' => count($cleanRow)
        ];
    }

    /**
     * Dapatkan path executable Python yang tersedia
     */
    private static function getPythonBinary(): ?string
    {
        $bundled = base_path('php/python.exe');
        if (file_exists($bundled)) return $bundled;

        $projectPy = base_path('python.exe');
        if (file_exists($projectPy)) return $projectPy;

        // Cek PATH sistem
        $out = @shell_exec('python --version 2>&1');
        if ($out && str_contains(strtolower($out), 'python')) {
            return 'python';
        }

        return null;
    }
}
