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

        $nik = preg_replace('/[^0-9]/', '', $cIndividu['nomor_induk_kependudukan'] ?? '');
        $kk = preg_replace('/[^0-9]/', '', $cIndividu['nomor_kartu_keluarga'] ?? '');
        $nama = trim($cIndividu['nama'] ?? '');

        // =========================================================================
        // 1. EVALUASI CRITICAL ERROR (FORMAT RUSAK / KANONIKAL FAKTOR UTAMA)
        // =========================================================================

        // A. Validasi Nama Lengkap
        if (empty($nama)) {
            $status = 'Critical';
            $issues[] = '[CRITICAL] Variabel Nama Lengkap kosong / belum diisi.';
        } else {
            if (preg_match('/[0-9]/', $nama)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung angka.";
            } elseif (!preg_match('/^[a-zA-Z\s\.\,\'\-]+$/u', $nama)) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nama tidak valid ('{$nama}'). Mengandung karakter simbol khusus.";
            }
        }

        // B. Validasi NIK (Nomor Induk Kependudukan)
        if (empty($nik)) {
            $status = 'Critical';
            $issues[] = '[CRITICAL] Variabel NIK (Nomor Induk Kependudukan) kosong / belum diisi.';
        } elseif (strlen($nik) !== 16) {
            $status = 'Critical';
            $issues[] = "[CRITICAL] NIK tidak valid ('{$nik}'). Panjang harus persis 16 digit angka.";
        }

        // C. Validasi Nomor Kartu Keluarga (KK) jika ada
        if (!empty($cIndividu['nomor_kartu_keluarga'])) {
            if (strlen($kk) !== 16) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nomor KK tidak valid ('{$kk}'). Panjang harus persis 16 digit angka.";
            }
        }

        // D. Validasi Desil Kesejahteraan (1 - 10)
        $desilVal = $cKeluarga['desil_nasional'] ?? ($cIndividu['desil_nasional'] ?? null);
        if ($desilVal !== null && $desilVal !== '') {
            $desil = (int) $desilVal;
            if ($desil < 1 || $desil > 10) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Desil Kesejahteraan '{$desilVal}' di luar jangkauan valid (1 s.d. 10).";
            }
        }

        // E. Validasi Usia (0 - 120 Tahun) jika ada di file
        $usiaVal = $cIndividu['usia'] ?? null;
        if ($usiaVal !== null && $usiaVal !== '') {
            if (!is_numeric($usiaVal) || (int)$usiaVal < 0 || (int)$usiaVal > 120) {
                $status = 'Critical';
                $issues[] = "[CRITICAL] Nilai Usia/Umur '{$usiaVal}' di luar jangkauan valid (0 s.d. 120 tahun).";
            }
        }

        // F. Validasi Nilai Gaji / Pendapatan (Harus angka non-negatif) jika ada
        $gajiVal = $cIndividu['gaji'] ?? null;
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
                $status = 'Warning';
                $issues[] = "[WARNING] Nilai variabel '{$fieldLabel}' kosong / belum terisi.";
            }
        }

        return [
            'status' => $status,
            'issues' => $issues
        ];
    }
}
