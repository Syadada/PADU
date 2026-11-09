<?php

namespace App\Services;

class DataQualityCheckService
{
    /**
     * Memetakan seluruh key input ke key kanonikal menggunakan DtsenImportService
     */
    public static function resolveCanonicalRow(array $row): array
    {
        $resolved = [];
        foreach ($row as $k => $v) {
            $norm = DtsenImportService::normalizeHeaderKey((string)$k);
            $resolved[$norm['key']] = $v;
            if (!isset($resolved[$k])) {
                $resolved[$k] = $v;
            }
        }
        return $resolved;
    }

    /**
     * Memeriksa dan mengevaluasi kualitas data individu & keluarga (DTSEN 2026 Adaptif)
     * Sesuai Standar Nasional Ditjen Dukcapil Kemendagri (UU No. 24/2013) & Permendagri No. 73/2022:
     * - Nama Lengkap: Hanya huruf latin dan spasi. Dilarang memuat titik, koma, angka, atau simbol khusus.
     * - NIK & KK: Persis 16 digit angka murni numerik. Dilarang memuat titik/koma (cacat float), huruf, atau simbol.
     * - Desil: 1 s.d. 10.
     * 
     * @param array $rowIndividu Data baris individu / raw row upload map
     * @param array|null $rowKeluarga Data baris keluarga pengampu
     * @return array ['status' => 'Valid'|'Warning'|'Critical', 'issues' => array]
     */
    public static function evaluate(array $rowIndividu, ?array $rowKeluarga = null): array
    {
        $issues = [];
        $status = 'Valid';

        $cIndividu = self::resolveCanonicalRow($rowIndividu);
        $cKeluarga = $rowKeluarga ? self::resolveCanonicalRow($rowKeluarga) : [];

        $rawNik = trim((string)($cIndividu['nomor_induk_kependudukan'] ?? ($cIndividu['nik'] ?? ($rowIndividu['nomor_induk_kependudukan'] ?? ($rowIndividu['nik'] ?? '')))));
        $rawKk = trim((string)($cIndividu['nomor_kartu_keluarga'] ?? ($cIndividu['no_kk'] ?? ($cIndividu['kk'] ?? ($rowIndividu['nomor_kartu_keluarga'] ?? ($rowIndividu['no_kk'] ?? ($rowIndividu['kk'] ?? '')))))));
        $nama = trim((string)($cIndividu['nama'] ?? ($cIndividu['nama_lengkap'] ?? ($rowIndividu['nama'] ?? ($rowIndividu['nama_lengkap'] ?? '')))));

        // =========================================================================
        // 1. EVALUASI CRITICAL ERROR (FORMAT RUSAK / KANONIKAL FAKTOR UTAMA)
        // =========================================================================

        // A. Validasi Standar Nama Lengkap (Permendagri No. 73 Tahun 2022)
        // Aturan: Hanya boleh huruf alfabet dan spasi tunggal. Dilarang titik, koma, angka, atau karakter khusus.
        if (empty($nama)) {
            $status = 'Critical';
            $issues[] = '[CRITICAL] Variabel Nama Lengkap kosong / belum diisi.';
        } else {
            if (str_contains($nama, '.')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung tanda titik ('.'). Standar pencatatan sipil melarang tanda baca/singkatan pada nama kependudukan.";
            }
            if (str_contains($nama, ',')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung tanda koma (','). Standar pencatatan sipil melarang penulisan gelar pada nama kependudukan.";
            }
            if (preg_match('/[0-9]/', $nama)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung karakter angka/digit numerik.";
            }
            if (!preg_match('/^[a-zA-Z\s]+$/u', $nama)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung karakter simbol khusus atau format non-alfabet murni.";
            }
            if (strlen(trim($nama)) < 2) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Nama terlalu pendek (kurang dari 2 huruf).";
            } elseif (strlen(trim($nama)) > 60) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Melebihi batas maksimal standar Permendagri No. 73/2022 (maksimal 60 karakter).";
            }
        }

        // B. Validasi Standar NIK (UU No. 24 Tahun 2013 - Persis 16 Digit Angka Murni)
        if (empty($rawNik)) {
            $status = 'Critical';
            $issues[] = '[CRITICAL] Variabel NIK (Nomor Induk Kependudukan) kosong / belum diisi.';
        } else {
            if (str_contains($rawNik, '.')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] NIK tidak valid ('{$rawNik}'). Mengandung tanda titik ('.') akibat cacat format pecahan desimal/float Excel.";
            }
            if (str_contains($rawNik, ',')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] NIK tidak valid ('{$rawNik}'). Mengandung tanda koma (',') akibat format ribuan/desimal Excel.";
            }
            if (preg_match('/[a-zA-Z]/', $rawNik)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] NIK tidak valid ('{$rawNik}'). Mengandung karakter alfabet / notasi ilmiah (E+).";
            }
            if (preg_match('/[^0-9\.,a-zA-Z]/', $rawNik)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] NIK tidak valid ('{$rawNik}'). Mengandung karakter simbol atau spasi.";
            }
            if (!preg_match('/^[0-9]{16}$/', $rawNik)) {
                $status = 'Critical';
                $digitCount = strlen(preg_replace('/[^0-9]/', '', $rawNik));
                $issues[] = "[CRITICAL] NIK tidak valid ('{$rawNik}'). Standar kependudukan nasional Ditjen Dukcapil mewajibkan persis 16 digit angka murni (terdeteksi {$digitCount} digit).";
            }
        }

        // C. Validasi Standar Nomor Kartu Keluarga (KK - Persis 16 Digit Angka Murni)
        if (!empty($rawKk)) {
            if (str_contains($rawKk, '.')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$rawKk}'). Mengandung tanda titik ('.') akibat cacat format pecahan desimal/float Excel.";
            }
            if (str_contains($rawKk, ',')) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$rawKk}'). Mengandung tanda koma (',') akibat format ribuan/desimal Excel.";
            }
            if (preg_match('/[a-zA-Z]/', $rawKk)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$rawKk}'). Mengandung karakter alfabet / notasi ilmiah (E+).";
            }
            if (preg_match('/[^0-9\.,a-zA-Z]/', $rawKk)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$rawKk}'). Mengandung karakter simbol atau spasi.";
            }
            if (!preg_match('/^[0-9]{16}$/', $rawKk)) {
                $status = 'Critical';
                $digitCount = strlen(preg_replace('/[^0-9]/', '', $rawKk));
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$rawKk}'). Standar kependudukan nasional Ditjen Dukcapil mewajibkan persis 16 digit angka murni (terdeteksi {$digitCount} digit).";
            }
        }

        // D. Validasi Desil Kesejahteraan (1 - 10)
        $desilVal = $cKeluarga['desil_nasional'] ?? ($cIndividu['desil_nasional'] ?? ($rowIndividu['desil'] ?? null));
        if ($desilVal !== null && $desilVal !== '') {
            $desil = (int) $desilVal;
            if ($desil < 1 || $desil > 10) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Desil Kesejahteraan '{$desilVal}' di luar jangkauan valid nasional (1 s.d. 10).";
            }
        }

        // E. Validasi Usia (0 - 120 Tahun) jika ada di file
        $usiaVal = $cIndividu['usia'] ?? ($rowIndividu['umur'] ?? null);
        if ($usiaVal !== null && $usiaVal !== '') {
            if (!is_numeric($usiaVal) || (int)$usiaVal < 0 || (int)$usiaVal > 120) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nilai Usia/Umur '{$usiaVal}' di luar jangkauan logis (0 s.d. 120 tahun).";
            }
        }

        // F. Validasi Nilai Gaji / Pendapatan (Harus angka non-negatif) jika ada
        $gajiVal = $cIndividu['gaji'] ?? ($rowIndividu['gaji_bulanan'] ?? null);
        if ($gajiVal !== null && $gajiVal !== '') {
            if (!is_numeric($gajiVal) || (float)$gajiVal < 0) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nilai Gaji/Pendapatan '{$gajiVal}' tidak valid (harus angka non-negatif).";
            }
        }

        // =========================================================================
        // 2. EVALUASI WARNING (NILAI KOSONG / MISSING VALUE PADA VARIABEL LAIN)
        // =========================================================================

        foreach ($rowIndividu as $key => $val) {
            if (in_array($key, ['id', 'created_at', 'updated_at', 'quality_status', 'quality_issues'])) {
                continue;
            }

            if ($val === null || (is_string($val) && trim($val) === '')) {
                $norm = DtsenImportService::normalizeHeaderKey((string)$key);
                $fieldLabel = $norm['label'];
                if ($status !== 'Critical') {
                    $status = 'Warning';
                }
                $issues[] = "[WARNING] Nilai variabel '{$fieldLabel}' kosong / belum terisi.";
            }
        }

        return [
            'status' => $status,
            'issues' => array_values(array_unique($issues))
        ];
    }
}
