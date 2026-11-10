<?php

namespace App\Services;

class DatasetIntegrityService
{
    private static ?array $hashesCache = null;
    private static string $cacheFile = '';

    private static function initCache(): void
    {
        if (self::$hashesCache === null) {
            self::$cacheFile = database_path('dataset_hashes.json');
            if (file_exists(self::$cacheFile)) {
                self::$hashesCache = @json_decode(file_get_contents(self::$cacheFile), true) ?: [];
            } else {
                self::$hashesCache = [];
            }
        }
    }

    private static function saveCache(): void
    {
        if (self::$hashesCache !== null && !empty(self::$cacheFile)) {
            @file_put_contents(self::$cacheFile, json_encode(self::$hashesCache, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Hitung atau ambil nilai Hash SHA-256 dari berkas dengan cache cerdas berbasis mtime & filesize
     */
    public static function getFileHash(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        self::initCache();
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $fileMtime = filemtime($filePath);

        $cacheKey = $fileName;
        if (
            isset(self::$hashesCache[$cacheKey]['sha256']) &&
            (self::$hashesCache[$cacheKey]['size'] ?? 0) === $fileSize &&
            (self::$hashesCache[$cacheKey]['mtime'] ?? 0) === $fileMtime
        ) {
            return self::$hashesCache[$cacheKey]['sha256'];
        }

        // Hitung SHA-256 (Gunakan streaming hash untuk efisiensi memori)
        $hashCtx = hash_init('sha256');
        $handle = @fopen($filePath, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fread($handle, 1048576); // 1MB buffer
                if ($buffer !== false) {
                    hash_update($hashCtx, $buffer);
                }
            }
            fclose($handle);
            $sha256 = hash_final($hashCtx);
        } else {
            $sha256 = hash_file('sha256', $filePath);
        }

        if (!isset(self::$hashesCache[$cacheKey])) {
            self::$hashesCache[$cacheKey] = [];
        }
        self::$hashesCache[$cacheKey]['sha256'] = $sha256;
        self::$hashesCache[$cacheKey]['size'] = $fileSize;
        self::$hashesCache[$cacheKey]['mtime'] = $fileMtime;
        self::$hashesCache[$cacheKey]['calculated_at'] = date('Y-m-d H:i:s');
        self::saveCache();

        return $sha256;
    }

    /**
     * Baca header kolom baris pertama dari berkas CSV / TXT / XLSX
     */
    public static function sniffFileHeaders(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $headers = [];

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = @fopen($filePath, 'r');
            if ($handle) {
                $line = fgets($handle);
                fclose($handle);
                if ($line) {
                    $delimiter = strpos($line, ';') !== false ? ';' : ',';
                    $raw = str_getcsv($line, $delimiter);
                    foreach ($raw as $col) {
                        $c = trim((string)$col, "\xEF\xBB\xBF \t\n\r\0\x0B\"");
                        if ($c !== '') {
                            $headers[] = $c;
                        }
                    }
                }
            }
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            // Gunakan pembacaan ringan PHP/Python untuk XLSX
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($sheet->getRowIterator(1, 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $val = trim((string)$cell->getValue());
                        if ($val !== '') {
                            $headers[] = $val;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        return $headers;
    }

    /**
     * Evaluasi lingkup variabel berkas dataset:
     * - 'individu' : Hanya memuat variabel data anggota keluarga / individu
     * - 'keluarga' : Hanya memuat variabel data rumah tangga / keluarga
     * - 'gabungan' : Memuat variabel individu dan keluarga (lengkap)
     * - 'kustom'   : Dataset format khusus lainnya
     */
    public static function evaluateFileScope(string $filePath, array $individuDict, array $keluargaDict): array
    {
        self::initCache();
        $fileName = basename($filePath);
        $cacheKey = $fileName;
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
        $fileMtime = file_exists($filePath) ? filemtime($filePath) : 0;

        if (
            isset(self::$hashesCache[$cacheKey]['scope']) &&
            (self::$hashesCache[$cacheKey]['size'] ?? 0) === $fileSize &&
            (self::$hashesCache[$cacheKey]['mtime'] ?? 0) === $fileMtime
        ) {
            return self::$hashesCache[$cacheKey]['scope_info'];
        }

        $headers = self::sniffFileHeaders($filePath);
        $matchedIndividu = 0;
        $matchedKeluarga = 0;
        $matchedIndividuList = [];
        $matchedKeluargaList = [];

        foreach ($headers as $h) {
            $norm = DtsenImportService::normalizeHeaderKey($h)['key'];
            if (isset($individuDict[$norm])) {
                $matchedIndividu++;
                $matchedIndividuList[$norm] = $individuDict[$norm];
            }
            if (isset($keluargaDict[$norm])) {
                $matchedKeluarga++;
                $matchedKeluargaList[$norm] = $keluargaDict[$norm];
            }
        }

        $scope = 'kustom';
        $scopeLabel = 'Set Data Kustom';
        $scopeBadge = 'bg-slate-100 text-slate-700 border-slate-300';
        $scopeIcon = 'fi fi-rr-document';

        if ($matchedIndividu > 0 && $matchedKeluarga > 0) {
            $scope = 'gabungan';
            $scopeLabel = 'Set Terpadu (Keduanya)';
            $scopeBadge = 'bg-indigo-50 text-indigo-700 border-indigo-200';
            $scopeIcon = 'fi fi-rr-apps';
        } elseif ($matchedIndividu > 0 && $matchedKeluarga === 0) {
            $scope = 'individu';
            $scopeLabel = 'Set Anggota Keluarga';
            $scopeBadge = 'bg-blue-50 text-blue-700 border-blue-200';
            $scopeIcon = 'fi fi-rr-user';
        } elseif ($matchedIndividu === 0 && $matchedKeluarga > 0) {
            $scope = 'keluarga';
            $scopeLabel = 'Set Rumah Tangga';
            $scopeBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $scopeIcon = 'fi fi-rr-home';
        }

        $scopeInfo = [
            'scope' => $scope,
            'scope_label' => $scopeLabel,
            'scope_badge' => $scopeBadge,
            'scope_icon' => $scopeIcon,
            'total_headers' => count($headers),
            'matched_individu_count' => $matchedIndividu,
            'matched_keluarga_count' => $matchedKeluarga,
        ];

        self::$hashesCache[$cacheKey]['scope'] = $scope;
        self::$hashesCache[$cacheKey]['scope_info'] = $scopeInfo;
        self::saveCache();

        return $scopeInfo;
    }

    /**
     * Pindai seluruh berkas di folder src-dtsen dengan deteksi SHA-256 dan deteksi duplikat
     */
    public static function scanFolderWithIntegrity(string $folderPath, array $individuDict, array $keluargaDict): array
    {
        if (!is_dir($folderPath)) {
            return [];
        }

        $files = array_diff(scandir($folderPath), ['.', '..', '.gitkeep', '.DS_Store']);
        $result = [];
        $hashesSeen = [];

        foreach ($files as $fName) {
            $fPath = $folderPath . DIRECTORY_SEPARATOR . $fName;
            if (!is_file($fPath)) continue;

            $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
            $size = filesize($fPath);
            $hash = self::getFileHash($fPath);
            $scopeInfo = self::evaluateFileScope($fPath, $individuDict, $keluargaDict);

            $isDuplicate = false;
            $duplicateOf = null;
            if (!empty($hash)) {
                if (isset($hashesSeen[$hash])) {
                    $isDuplicate = true;
                    $duplicateOf = $hashesSeen[$hash];
                } else {
                    $hashesSeen[$hash] = $fName;
                }
            }

            $result[] = [
                'name' => $fName,
                'full_path' => $fPath,
                'relative_path' => 'src-dtsen/' . $fName,
                'extension' => $ext,
                'size' => $size,
                'size_formatted' => DtsenImportService::formatBytes($size),
                'modified_at' => date('d/m/Y H:i', filemtime($fPath)),
                'sha256' => $hash,
                'sha256_short' => substr($hash, 0, 12),
                'is_duplicate' => $isDuplicate,
                'duplicate_of' => $duplicateOf,
                'scope' => $scopeInfo['scope'],
                'scope_label' => $scopeInfo['scope_label'],
                'scope_badge' => $scopeInfo['scope_badge'],
                'scope_icon' => $scopeInfo['scope_icon'],
                'total_headers' => $scopeInfo['total_headers'],
                'matched_individu' => $scopeInfo['matched_individu_count'],
                'matched_keluarga' => $scopeInfo['matched_keluarga_count'],
            ];
        }

        // Tandai juga file pertama jika memiliki kembaran di folder
        $duplicatesFound = array_filter($result, fn($r) => $r['is_duplicate']);
        if (!empty($duplicatesFound)) {
            $dupSources = array_column($duplicatesFound, 'duplicate_of');
            foreach ($result as &$item) {
                if (!$item['is_duplicate'] && in_array($item['name'], $dupSources)) {
                    $item['has_duplicates'] = true;
                }
            }
        }

        return $result;
    }

    /**
     * Periksa apakah berkas yang baru diunggah memiliki hash identik dengan berkas yang sudah ada
     */
    public static function checkDuplicateUpload(string $uploadedTempPath, string $originalName, string $targetDir): ?array
    {
        $uploadedHash = hash_file('sha256', $uploadedTempPath);
        if (!$uploadedHash) {
            return null;
        }

        if (!is_dir($targetDir)) {
            return null;
        }

        $existingFiles = array_diff(scandir($targetDir), ['.', '..', '.gitkeep', '.DS_Store']);
        foreach ($existingFiles as $fName) {
            $fPath = $targetDir . DIRECTORY_SEPARATOR . $fName;
            if (!is_file($fPath)) continue;

            $existingHash = self::getFileHash($fPath);
            if ($existingHash === $uploadedHash) {
                return [
                    'is_duplicate' => true,
                    'matching_file' => $fName,
                    'sha256' => $uploadedHash,
                    'original_name' => $originalName,
                ];
            }
        }

        return [
            'is_duplicate' => false,
            'sha256' => $uploadedHash,
            'matching_file' => null,
            'original_name' => $originalName,
        ];
    }
}
