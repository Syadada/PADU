<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Collection;

class CsvExportService
{
    /**
     * Daftar seluruh definisi kolom yang dapat dipilih untuk ekspor CSV
     */
    public static function availableColumns(): array
    {
        return [
            'id' => 'No ID',
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'status_kehidupan' => 'Status Kehidupan',
            'jenis_kelamin' => 'Jenis Kelamin',
            'pendidikan_terakhir' => 'Pendidikan Terakhir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'usia' => 'Usia (Tahun)',
            'usia_detail' => 'Usia Detail (Thn & Bln)',
            'gaji_bulanan' => 'Gaji Bulanan (Rp)',
            'provinsi' => 'Provinsi',
            'kota_kabupaten' => 'Kota/Kabupaten',
            'kecamatan' => 'Kecamatan',
            'kelurahan_desa' => 'Kelurahan/Desa',
            'rt_rw' => 'RT/RW',
            'alamat_lengkap' => 'Alamat Lengkap',
            'email' => 'Email',
            'status_pernikahan' => 'Status Pernikahan',
            'created_at' => 'Tanggal Ditambahkan',
        ];
    }

    /**
     * Stream CSV download dengan filter kolom kustom
     */
    public static function exportCsv(
        Collection $items, 
        bool $isMasked = false, 
        string $filename = 'export_data_demografi.csv',
        array $selectedColumns = []
    ): StreamedResponse {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $allCols = self::availableColumns();
        
        // Jika tidak ada kolom spesifik yang dipilih, gunakan seluruh kolom
        if (empty($selectedColumns)) {
            $colsToExport = array_keys($allCols);
        } else {
            $colsToExport = array_intersect(array_keys($allCols), $selectedColumns);
        }

        $callback = function () use ($items, $isMasked, $allCols, $colsToExport) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // CSV Header sesuai kolom yang dipilih
            $headerRow = [];
            foreach ($colsToExport as $colKey) {
                $headerRow[] = $allCols[$colKey];
            }
            $headerRow[] = 'Status Export';
            fputcsv($file, $headerRow);

            foreach ($items as $item) {
                $row = [];
                foreach ($colsToExport as $colKey) {
                    switch ($colKey) {
                        case 'id':
                            $row[] = $item->id;
                            break;
                        case 'nik':
                            $row[] = $isMasked ? $item->masked_nik : $item->nik;
                            break;
                        case 'nama_lengkap':
                            $row[] = $isMasked ? $item->masked_nama : $item->nama_lengkap;
                            break;
                        case 'status_kehidupan':
                            $row[] = $item->status_kehidupan ?: 'Masih Hidup';
                            break;
                        case 'jenis_kelamin':
                            $row[] = $item->jenis_kelamin;
                            break;
                        case 'pendidikan_terakhir':
                            $row[] = $item->pendidikan_terakhir;
                            break;
                        case 'tanggal_lahir':
                            $row[] = $isMasked ? $item->masked_tanggal_lahir : ($item->tanggal_lahir ? $item->tanggal_lahir->format('Y-m-d') : '');
                            break;
                        case 'usia':
                            $row[] = $item->usia;
                            break;
                        case 'usia_detail':
                            $row[] = $item->usia_detail;
                            break;
                        case 'gaji_bulanan':
                            $row[] = $isMasked ? $item->masked_gaji : $item->gaji_bulanan;
                            break;
                        case 'provinsi':
                            $row[] = $item->provinsi ?: 'DKI Jakarta';
                            break;
                        case 'kota_kabupaten':
                            $row[] = $item->kota_kabupaten ?: $item->kota_domisili;
                            break;
                        case 'kecamatan':
                            $row[] = $item->kecamatan ?: '-';
                            break;
                        case 'kelurahan_desa':
                            $row[] = $item->kelurahan_desa ?: '-';
                            break;
                        case 'rt_rw':
                            $row[] = $isMasked ? $item->masked_rt_rw : ($item->rt_rw ?: '-');
                            break;
                        case 'alamat_lengkap':
                            $row[] = $isMasked ? $item->masked_alamat : ($item->alamat_lengkap ?: '-');
                            break;
                        case 'email':
                            $row[] = $isMasked ? $item->masked_email : $item->email;
                            break;
                        case 'status_pernikahan':
                            $row[] = $item->status_pernikahan;
                            break;
                        case 'created_at':
                            $row[] = $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-';
                            break;
                    }
                }
                $row[] = $isMasked ? 'Disensor (Masked)' : 'Asli (Unmasked)';
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
