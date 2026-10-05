<?php

namespace App\Services;

use ZipArchive;
use Exception;
use Illuminate\Support\Facades\File;

class BackupService
{
    /**
     * Generate password acak 20 karakter dengan kompleksitas tinggi (Uppercase, Lowercase, Numbers, Symbols)
     */
    public static function generateRandomPassword(int $length = 20): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%^&*-_+=';

        $all = $uppercase . $lowercase . $numbers . $symbols;
        
        // Pastikan minimal ada 1 dari setiap kategori
        $password = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        return implode('', $password);
    }

    /**
     * Buat arsip cadangan (backup) terenkripsi AES-256
     * Berisi: Database SQLite, Database DuckDB (jika ada), file konfigurasi .env, dan catatan manifest
     *
     * @return array [ 'success' => bool, 'filename' => string, 'filepath' => string, 'password' => string, 'sha256' => string, 'size_formatted' => string ]
     */
    public static function createEncryptedBackup(): array
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "PADU_Enterprise_Backup_{$timestamp}.zip";
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $password = self::generateRandomPassword(20);

        $zip = new ZipArchive();
        $res = $zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res !== true) {
            throw new Exception("Gagal membuat arsip ZIP backup (Kode error: {$res})");
        }

        // Set master password untuk enkripsi
        $zip->setPassword($password);

        // 1. Masukkan Database SQLite
        $sqlitePath = database_path('database.sqlite');
        if (file_exists($sqlitePath)) {
            $entry = 'database/database.sqlite';
            $zip->addFile($sqlitePath, $entry);
            $zip->setEncryptionName($entry, ZipArchive::EM_AES_256);
        }

        // 2. Masukkan Database DuckDB jika ada
        $duckdbPath = database_path('dataset.duckdb');
        if (file_exists($duckdbPath)) {
            $entry = 'database/dataset.duckdb';
            $zip->addFile($duckdbPath, $entry);
            $zip->setEncryptionName($entry, ZipArchive::EM_AES_256);
        }

        // 3. Masukkan .env
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $entry = 'config/.env.backup';
            $zip->addFile($envPath, $entry);
            $zip->setEncryptionName($entry, ZipArchive::EM_AES_256);
        }

        // 4. Masukkan Manifest Catatan Integritas
        $manifestContent = "========================================================\n"
            . "PADU v2.0 Enterprise - ENCRYPTED BACKUP ARCHIVE\n"
            . "========================================================\n"
            . "Waktu Pembuatan : " . date('Y-m-d H:i:s T') . "\n"
            . "Standar Keamanan: BSSN No. 4/2021 & Per BSSN No. 11/2024\n"
            . "Metode Enkripsi : AES-256 (WinZip Advanced Encryption Standard)\n"
            . "Integritas Arsip: Kunci enkripsi 20 karakter tunggal\n"
            . "Hak Milik       : Kedaulatan Data Klien (Lepas Kunci)\n"
            . "========================================================\n";
        $manifestEntry = 'MANIFEST.txt';
        $zip->addFromString($manifestEntry, $manifestContent);
        $zip->setEncryptionName($manifestEntry, ZipArchive::EM_AES_256);

        $zip->close();

        // Hitung SHA-256 Checksum
        $sha256 = hash_file('sha256', $filepath);
        $sizeBytes = filesize($filepath);

        $sizeFormatted = self::formatBytes($sizeBytes);

        // Simpan metadata backup ke JSON log
        $metaPath = $backupDir . DIRECTORY_SEPARATOR . 'backups_meta.json';
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        if (!is_array($meta)) $meta = [];

        $record = [
            'filename' => $filename,
            'sha256' => $sha256,
            'size' => $sizeFormatted,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        array_unshift($meta, $record);
        file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT));

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'password' => $password,
            'sha256' => $sha256,
            'size_formatted' => $sizeFormatted,
        ];
    }

    /**
     * Dapatkan riwayat daftar berkas backup
     */
    public static function listBackups(): array
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            return [];
        }

        $metaPath = $backupDir . DIRECTORY_SEPARATOR . 'backups_meta.json';
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        if (!is_array($meta)) $meta = [];

        $existingFiles = [];
        foreach (File::files($backupDir) as $file) {
            if ($file->getExtension() === 'zip') {
                $fname = $file->getFilename();
                $existingFiles[$fname] = [
                    'filename' => $fname,
                    'size' => self::formatBytes($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                ];
            }
        }

        // Sinkronisasi dengan meta JSON
        $result = [];
        foreach ($meta as $m) {
            if (isset($existingFiles[$m['filename']])) {
                $result[] = [
                    'filename' => $m['filename'],
                    'size' => $m['size'] ?? $existingFiles[$m['filename']]['size'],
                    'created_at' => $m['created_at'] ?? $existingFiles[$m['filename']]['created_at'],
                    'sha256' => $m['sha256'] ?? $existingFiles[$m['filename']]['sha256'],
                ];
                unset($existingFiles[$m['filename']]);
            }
        }

        foreach ($existingFiles as $f) {
            $result[] = $f;
        }

        return $result;
    }

    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
