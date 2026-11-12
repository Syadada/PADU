<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TransactionLogger
{
    /**
     * Dapatkan path direktori khusus log transaksi
     */
    public static function getLogDirectory(): string
    {
        $dir = base_path('logs-transaksi');
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * Dapatkan path berkas buffer sesi aktif
     */
    protected static function getActiveSessionFilePath(): string
    {
        return self::getLogDirectory() . DIRECTORY_SEPARATOR . '.active_session.json';
    }

    /**
     * Inisialisasi atau ambil data sesi yang sedang aktif
     */
    protected static function getOrCreateActiveSession(): array
    {
        $file = self::getActiveSessionFilePath();
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            $data = json_decode($content, true);
            if (is_array($data) && isset($data['session_id'])) {
                // Update info user jika baru saja login
                if (Auth::check() && empty($data['user_email'])) {
                    $u = Auth::user();
                    $data['user_name'] = $u->name;
                    $data['user_email'] = $u->email;
                    $data['user_role'] = $u->isSuperAdmin() ? 'Super Administrator' : 'Operator Data';
                }
                return $data;
            }
        }

        $user = Auth::user();
        $newSession = [
            'session_id' => 'SESI_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)),
            'started_at' => date('Y-m-d H:i:s'),
            'user_name' => $user ? $user->name : 'PADU',
            'user_email' => $user ? $user->email : 'padu@local.id',
            'user_role' => $user ? ($user->isSuperAdmin() ? 'Super Administrator' : 'Operator Data') : 'Super Administrator',
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'events' => [],
        ];

        @file_put_contents($file, json_encode($newSession, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $newSession;
    }

    /**
     * Catat kejadian / transaksi / pencarian ke sesi aktif saat ini
     *
     * @param string $action Jenis Aksi (PENCARIAN, EKSPOR, LOGIN, GANTI_DATASET, FILTER, RESET_PASSWORD, dll)
     * @param string $summary Ringkasan singkat aksi
     * @param array $details Rincian parameter (filter, query, hasil temuan, berkas, dll)
     */
    public static function record(string $action, string $summary, array $details = []): void
    {
        try {
            $session = self::getOrCreateActiveSession();
            $timestamp = date('Y-m-d H:i:s');

            if (Auth::check()) {
                $u = Auth::user();
                $session['user_name'] = $u->name;
                $session['user_email'] = $u->email;
                $session['user_role'] = $u->isSuperAdmin() ? 'Super Administrator' : 'Operator Data';
            }

            // Cegah duplikasi pencarian berulang yang persis sama dalam selang < 2 detik
            if (!empty($session['events'])) {
                $lastEvent = end($session['events']);
                if (
                    $lastEvent['action'] === strtoupper($action) &&
                    $lastEvent['summary'] === $summary &&
                    (strtotime($timestamp) - strtotime($lastEvent['timestamp'])) <= 2
                ) {
                    return;
                }
            }

            $session['events'][] = [
                'timestamp' => $timestamp,
                'action' => strtoupper($action),
                'summary' => $summary,
                'details' => $details,
            ];

            @file_put_contents(self::getActiveSessionFilePath(), json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {}
    }

    /**
     * Finalisasi sesi dan cetak berkas Log_Transaksi_YYYY-MM-DD_HH-mm-ss.txt
     * Dipanggil saat aplikasi/exe ditutup atau sesi berakhir.
     */
    public static function finalizeSession(): ?string
    {
        try {
            $file = self::getActiveSessionFilePath();
            if (!file_exists($file)) {
                return null;
            }

            $content = @file_get_contents($file);
            $session = json_decode($content, true);
            if (!is_array($session) || empty($session['events'])) {
                @unlink($file);
                return null;
            }

            $closedAt = date('Y-m-d H:i:s');
            $startedAt = $session['started_at'] ?? $closedAt;
            $userName = $session['user_name'] ?? 'PADU';
            $userEmail = $session['user_email'] ?? 'padu@local.id';
            $userRole = $session['user_role'] ?? 'Super Administrator';
            $ipAddress = $session['ip_address'] ?? '127.0.0.1';
            $events = $session['events'] ?? [];
            $totalEvents = count($events);

            $dateSlug = date('Y-m-d_His', strtotime($startedAt));
            $txtFileName = "Log_Transaksi_{$dateSlug}.txt";
            $txtFilePath = self::getLogDirectory() . DIRECTORY_SEPARATOR . $txtFileName;

            // Susun Konten Berkas Teks Log Transaksi
            $sep = str_repeat('=', 82);
            $subSep = str_repeat('-', 82);

            $out = [];
            $out[] = $sep;
            $out[] = "             CATATAN LOG TRANSAKSI & AKTIVITAS HARIAN PENGGUNA";
            $out[] = "               PADU - PENGOLAH & ANALISIS DATA TERPADU (DTSEN)";
            $out[] = $sep;
            $out[] = sprintf("%-22s: %s", "Waktu Sesi Dimulai", $startedAt);
            $out[] = sprintf("%-22s: %s", "Waktu Sesi Ditutup", $closedAt);
            $out[] = sprintf("%-22s: %s (%s)", "Pengguna", $userName, $userEmail);
            $out[] = sprintf("%-22s: %s", "Hak Akses / Peran", $userRole);
            $out[] = sprintf("%-22s: %s", "Alamat IP Klien", $ipAddress);
            $out[] = sprintf("%-22s: %d Transaksi / Tindakan", "Total Catatan Aksi", $totalEvents);
            $out[] = $subSep;
            $out[] = "DAFTAR RIWAYAT TRANSAKSI, PENCARIAN, FILTER, DAN PENGOLAHAN DATA:";
            $out[] = $subSep;
            $out[] = "";

            foreach ($events as $idx => $ev) {
                $no = $idx + 1;
                $t = $ev['timestamp'];
                $act = $ev['action'];
                $sum = $ev['summary'];
                $det = $ev['details'] ?? [];

                $out[] = sprintf("[%s] #%02d [%s] %s", $t, $no, $act, $sum);

                if (!empty($det)) {
                    foreach ($det as $k => $v) {
                        if (is_array($v)) {
                            $out[] = sprintf("    - %-18s: %s", ucwords(str_replace('_', ' ', $k)), implode(', ', $v));
                        } elseif (is_bool($v)) {
                            $out[] = sprintf("    - %-18s: %s", ucwords(str_replace('_', ' ', $k)), $v ? 'Ya' : 'Tidak');
                        } elseif ($v !== '' && $v !== null) {
                            $out[] = sprintf("    - %-18s: %s", ucwords(str_replace('_', ' ', $k)), (string)$v);
                        }
                    }
                }
                $out[] = "";
            }

            $out[] = $sep;
            $out[] = "SESI APLIKASI SELESAI & DITUTUP";
            $out[] = "Status Pembersihan: Memori RAM DuckDB, cache sesi, dan residu telah dibersihkan.";
            $out[] = $sep;
            $out[] = "";

            @file_put_contents($txtFilePath, implode(PHP_EOL, $out));
            @unlink($file);

            // Bersihkan sirkulasi memori
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            return $txtFileName;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
