<?php

namespace App\Services;

use Carbon\Carbon;

class DataMaskingService
{
    /**
     * Sensor Nama Lengkap. Contoh: "Budi Santoso" -> "B*** S******"
     */
    public static function maskNama(?string $nama): string
    {
        if (empty($nama)) return '-';
        $words = explode(' ', trim($nama));
        $maskedWords = array_map(function ($word) {
            $len = mb_strlen($word);
            if ($len <= 1) return '*';
            if ($len == 2) return mb_substr($word, 0, 1) . '*';
            return mb_substr($word, 0, 1) . str_repeat('*', $len - 1);
        }, $words);

        return implode(' ', $maskedWords);
    }

    /**
     * Sensor NIK 16 digit. Contoh: "3201021508900001" -> "3201************"
     */
    public static function maskNik(?string $nik): string
    {
        if (empty($nik)) return '-';
        $clean = preg_replace('/[^0-9]/', '', $nik);
        $len = strlen($clean);
        if ($len < 6) return str_repeat('*', $len);
        return substr($clean, 0, 4) . str_repeat('*', max(0, $len - 4));
    }

    /**
     * Sensor Tanggal Lahir. Contoh: "1992-05-14" -> "1992-**-**"
     */
    public static function maskTanggalLahir($date): string
    {
        if (empty($date)) return '-';
        $year = Carbon::parse($date)->format('Y');
        return $year . '-**-**';
    }

    /**
     * Sensor Email. Contoh: "budi.santoso@gmail.com" -> "b***@gmail.com"
     */
    public static function maskEmail(?string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return '***@***.com';
        list($local, $domain) = explode('@', $email, 2);
        $len = strlen($local);
        if ($len <= 2) {
            $maskedLocal = substr($local, 0, 1) . '*';
        } else {
            $maskedLocal = substr($local, 0, 1) . str_repeat('*', $len - 1);
        }
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Sensor Nominal Gaji. Contoh: 8500000 -> "Rp 8.xxx.xxx"
     */
    public static function maskGaji($gaji): string
    {
        if (is_null($gaji)) return 'Rp 0';
        $val = (float) $gaji;
        $formatted = number_format($val, 0, ',', '.');
        $parts = explode('.', $formatted);
        if (count($parts) > 1) {
            for ($i = 1; $i < count($parts); $i++) {
                $parts[$i] = str_repeat('x', strlen($parts[$i]));
            }
            return 'Rp ' . implode('.', $parts);
        }
        return 'Rp ' . substr($formatted, 0, 1) . str_repeat('x', strlen($formatted) - 1);
    }

    /**
     * Sensor Alamat Lengkap. Contoh: "Jl. Sudirman No. 45" -> "Jl. S******* No. **"
     */
    public static function maskAlamat(?string $alamat): string
    {
        if (empty($alamat)) return '-';
        $words = explode(' ', trim($alamat));
        $maskedWords = array_map(function ($word) {
            $len = mb_strlen($word);
            if ($len <= 2) return $word;
            return mb_substr($word, 0, 1) . str_repeat('*', $len - 1);
        }, $words);

        return implode(' ', $maskedWords);
    }

    /**
     * Sensor RT/RW. Contoh: "RT 003 / RW 005" -> "RT *** / RW ***"
     */
    public static function maskRtRw(?string $rtRw): string
    {
        if (empty($rtRw)) return '-';
        return preg_replace('/[0-9]/', '*', $rtRw);
    }
}
