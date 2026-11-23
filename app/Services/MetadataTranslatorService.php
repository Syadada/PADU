<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MetadataTranslatorService
{
    private static ?array $cachedDictionary = null;
    private static ?array $aliasMap = null;

    /**
     * Dapatkan kamus terjemahan kode lengkap dari active_rules.json (BAST Metadata DTSEN / BPS)
     * Ditambah aturan standar BPS (PODES, Susenas, Sakernas, Regsosek).
     */
    public static function getDictionary(): array
    {
        if (self::$cachedDictionary !== null) {
            return self::$cachedDictionary;
        }

        $dict = [];

        // 1. Muat dari active_rules.json jika tersedia
        $rulesFile = storage_path('app/bast_metadata/active_rules.json');
        if (file_exists($rulesFile)) {
            try {
                $rules = json_decode(file_get_contents($rulesFile), true);
                if (is_array($rules) && !empty($rules['datasets'])) {
                    foreach ($rules['datasets'] as $ds) {
                        foreach ($ds['variables'] ?? [] as $v) {
                            $varKey = strtolower(trim($v['key'] ?? ''));
                            if (empty($varKey)) continue;

                            $codes = $v['rules']['allowed_codes'] ?? [];
                            if (!empty($codes)) {
                                if (!isset($dict[$varKey])) {
                                    $dict[$varKey] = [];
                                }
                                foreach ($codes as $c) {
                                    $cCode = trim((string)($c['code'] ?? ''));
                                    $cLabel = trim((string)($c['label'] ?? ''));
                                    if ($cCode !== '' && $cLabel !== '') {
                                        $dict[$varKey][$cCode] = $cLabel;
                                        // Normalisasi zero-padding (misal '1' <-> '01')
                                        if (ctype_digit($cCode)) {
                                            $intCode = (string)(int)$cCode;
                                            $padCode = str_pad($intCode, 2, '0', STR_PAD_LEFT);
                                            $dict[$varKey][$intCode] = $cLabel;
                                            $dict[$varKey][$padCode] = $cLabel;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[TRANSLATOR] Gagal memuat active_rules.json: ' . $e->getMessage());
            }
        }

        // 2. Tambahkan aturan terjemahan umum standar BPS / DTSEN / PODES jika belum ada
        $builtin = [
            'jenis_kelamin' => [
                '1' => 'Laki-laki',
                '2' => 'Perempuan',
                'L' => 'Laki-laki',
                'P' => 'Perempuan',
                '01' => 'Laki-laki',
                '02' => 'Perempuan',
            ],
            'status_kawin' => [
                '1' => 'Belum Kawin',
                '2' => 'Kawin/Nikah',
                '3' => 'Cerai Hidup',
                '4' => 'Cerai Mati',
                '01' => 'Belum Kawin',
                '02' => 'Kawin/Nikah',
                '03' => 'Cerai Hidup',
                '04' => 'Cerai Mati',
            ],
            'status_bekerja' => [
                '1' => 'Bekerja / Ya',
                '2' => 'Tidak Bekerja',
                '0' => 'Tidak Bekerja',
                '01' => 'Bekerja / Ya',
                '02' => 'Tidak Bekerja',
            ],
            'partisipasi_sekolah' => [
                '1' => 'Tidak/Belum Pernah Sekolah',
                '2' => 'Masih Sekolah',
                '3' => 'Tidak Bersekolah Lagi',
                '01' => 'Tidak/Belum Pernah Sekolah',
                '02' => 'Masih Sekolah',
                '03' => 'Tidak Bersekolah Lagi',
            ],
            'status_hubungan_keluarga' => [
                '1' => 'Kepala Keluarga',
                '2' => 'Suami',
                '3' => 'Istri',
                '4' => 'Anak',
                '5' => 'Menantu',
                '6' => 'Cucu',
                '7' => 'Orang Tua',
                '8' => 'Mertua',
                '9' => 'Famili Lain',
                '10' => 'Pembantu',
                '11' => 'Lainnya',
            ],
            'status_kepemilikan_rumah' => [
                '1' => 'Milik Sendiri',
                '2' => 'Kontrak/Sewa',
                '3' => 'Bebas Sewa',
                '4' => 'Dinas',
                '5' => 'Lainnya',
            ],
            'sumber_penerangan_utama' => [
                '1' => 'Listrik PLN dengan meteran',
                '2' => 'Listrik PLN tanpa meteran',
                '3' => 'Listrik non-PLN',
                '4' => 'Bukan listrik',
            ],
            'penerima_pbi_apbn' => [
                '1' => 'Ya (Penerima)',
                '0' => 'Tidak',
                '2' => 'Tidak',
            ],
            'penerima_pbi_apbd' => [
                '1' => 'Ya (Penerima)',
                '0' => 'Tidak',
                '2' => 'Tidak',
            ],
            'status_penerima_bantuan' => [
                '1' => 'Penerima Bantuan',
                '0' => 'Bukan Penerima',
                '2' => 'Bukan Penerima',
            ],
            'status_pemerintahan_desa' => [
                '1' => 'Desa',
                '2' => 'Kelurahan',
                '01' => 'Desa',
                '02' => 'Kelurahan',
            ],
            'provinsi' => [
                '11' => 'Aceh',
                '12' => 'Sumatera Utara',
                '13' => 'Sumatera Barat',
                '14' => 'Riau',
                '15' => 'Jambi',
                '16' => 'Sumatera Selatan',
                '17' => 'Bengkulu',
                '18' => 'Lampung',
                '19' => 'Kep. Bangka Belitung',
                '21' => 'Kep. Riau',
                '31' => 'DKI Jakarta',
                '32' => 'Jawa Barat',
                '33' => 'Jawa Tengah',
                '34' => 'DI Yogyakarta',
                '35' => 'Jawa Timur',
                '36' => 'Banten',
                '51' => 'Bali',
                '52' => 'Nusa Tenggara Barat',
                '53' => 'Nusa Tenggara Timur',
                '61' => 'Kalimantan Barat',
                '62' => 'Kalimantan Tengah',
                '63' => 'Kalimantan Selatan',
                '64' => 'Kalimantan Timur',
                '65' => 'Kalimantan Utara',
                '71' => 'Sulawesi Utara',
                '72' => 'Sulawesi Tengah',
                '73' => 'Sulawesi Selatan',
                '74' => 'Sulawesi Tenggara',
                '75' => 'Gorontalo',
                '76' => 'Sulawesi Barat',
                '81' => 'Maluku',
                '82' => 'Maluku Utara',
                '91' => 'Papua',
                '92' => 'Papua Barat',
                '93' => 'Papua Selatan',
                '94' => 'Papua Tengah',
                '95' => 'Papua Pegunungan',
                '96' => 'Papua Barat Daya',
            ],
            'kabupaten_kota' => [
                // Banten (36)
                '3601' => 'Pandeglang', '01' => 'Pandeglang', '1' => 'Pandeglang',
                '3602' => 'Lebak', '02' => 'Lebak', '2' => 'Lebak',
                '3603' => 'Tangerang', '03' => 'Tangerang', '3' => 'Tangerang',
                '3604' => 'Serang', '04' => 'Serang', '4' => 'Serang',
                '3671' => 'Kota Tangerang', '71' => 'Kota Tangerang',
                '3672' => 'Kota Cilegon', '72' => 'Kota Cilegon',
                '3673' => 'Kota Serang', '73' => 'Kota Serang',
                '3674' => 'Kota Tangerang Selatan', '74' => 'Kota Tangerang Selatan',
                // DKI Jakarta (31)
                '3101' => 'Kep. Seribu', '3171' => 'Kota Jakarta Selatan', '3172' => 'Kota Jakarta Timur',
                '3173' => 'Kota Jakarta Pusat', '3174' => 'Kota Jakarta Barat', '3175' => 'Kota Jakarta Utara',
                // Jawa Barat (32)
                '3201' => 'Bogor', '3202' => 'Sukabumi', '3203' => 'Cianjur', '3204' => 'Bandung',
                '3205' => 'Garut', '3206' => 'Tasikmalaya', '3207' => 'Ciamis', '3208' => 'Kuningan',
                '3209' => 'Cirebon', '3210' => 'Majalengka', '3211' => 'Sumedang', '3212' => 'Indramayu',
                '3213' => 'Subang', '3214' => 'Purwakarta', '3215' => 'Karawang', '3216' => 'Bekasi',
                '3217' => 'Bandung Barat', '3218' => 'Pangandaran', '3271' => 'Kota Bogor',
                '3272' => 'Kota Sukabumi', '3273' => 'Kota Bandung', '3274' => 'Kota Cirebon',
                '3275' => 'Kota Bekasi', '3276' => 'Kota Depok', '3277' => 'Kota Cimahi',
                '3278' => 'Kota Tasikmalaya', '3279' => 'Kota Banjar',
            ],
        ];

        foreach ($builtin as $k => $cMap) {
            if (!isset($dict[$k])) {
                $dict[$k] = $cMap;
            } else {
                // Gunakan assignment langsung agar PHP tidak me-renumber numeric string keys ('1', '2') menjadi (0, 1)
                foreach ($cMap as $code => $lbl) {
                    if (!isset($dict[$k][$code])) {
                        $dict[$k][$code] = $lbl;
                    }
                }
            }
        }

        self::$cachedDictionary = $dict;
        return self::$cachedDictionary;
    }

    /**
     * Resolusi sinonim kolom kependudukan / sensus ke kunci kanonikal kamus
     */
    public static function resolveCanonicalKey(string $rawKey): string
    {
        $k = strtolower(trim($rawKey));
        $k = preg_replace('/[^a-z0-9_]/', '_', $k);
        $k = trim(preg_replace('/_+/', '_', $k), '_');

        if (self::$aliasMap === null) {
            self::$aliasMap = [
                'jk' => 'jenis_kelamin',
                'gender' => 'jenis_kelamin',
                'kelamin' => 'jenis_kelamin',
                'kd_jk' => 'jenis_kelamin',
                'st_kawin' => 'status_kawin',
                'status_pernikahan' => 'status_kawin',
                'st_nikah' => 'status_kawin',
                'st_kerja' => 'status_bekerja',
                'pekerjaan' => 'status_bekerja',
                'status_kerja' => 'status_bekerja',
                'pekerjaan_utama' => 'status_bekerja',
                'hub_keluarga' => 'status_hubungan_keluarga',
                'hub_kel' => 'status_hubungan_keluarga',
                'shdk' => 'status_hubungan_keluarga',
                'hubungan_keluarga' => 'status_hubungan_keluarga',
                'pendidikan' => 'ijazah_tertinggi_yang_dimiliki',
                'pendidikan_terakhir' => 'ijazah_tertinggi_yang_dimiliki',
                'ijazah' => 'ijazah_tertinggi_yang_dimiliki',
                'jenjang_pendidikan' => 'jenjang_tertinggi_yang_diduduki',
                'sekolah' => 'partisipasi_sekolah',
                'lantai' => 'jenis_lantai_terluas',
                'dinding' => 'jenis_dinding_terluas',
                'atap' => 'jenis_atap_terluas',
                'air' => 'sumber_air_minum_utama',
                'air_minum' => 'sumber_air_minum_utama',
                'listrik' => 'sumber_penerangan_utama',
                'penerangan' => 'sumber_penerangan_utama',
                'kloset' => 'jenis_kloset',
                'jamban' => 'fasilitas_bab',
                'bab' => 'fasilitas_bab',
                'tinja' => 'pembuangan_akhir_tinja',
                'septik' => 'pembuangan_akhir_tinja',
                'pbi' => 'pbi_nas',
                'pbi_apbn' => 'pbi_nas',
                'pbi_apbd' => 'pbi_pemda',
                'gizi' => 'kondisi_gizi',
                'status_gizi' => 'kondisi_gizi',
                'status_rumah' => 'status_kepemilikan_rumah',
                'daya_listrik' => 'daya_terpasang',
                'daya' => 'daya_terpasang',
                'bahan_bakar' => 'bahan_bakar_utama_memasak',
                'kd_prov' => 'provinsi',
                'kode_provinsi' => 'provinsi',
                'kd_kab' => 'kabupaten_kota',
                'kode_kabupaten' => 'kabupaten_kota',
                'kabupaten' => 'kabupaten_kota',
                'kota' => 'kabupaten_kota',
                'kd_kec' => 'kecamatan',
                'kode_kecamatan' => 'kecamatan',
                'kd_desa' => 'kelurahan_desa',
                'kode_desa' => 'kelurahan_desa',
                'desa' => 'kelurahan_desa',
                'kelurahan' => 'kelurahan_desa',
                'status_desa' => 'status_pemerintahan_desa',
                'status_kelurahan' => 'status_pemerintahan_desa',
                'r105' => 'status_pemerintahan_desa',
                'b1_r105' => 'status_pemerintahan_desa',
            ];
        }

        $dict = self::getDictionary();
        if (isset($dict[$k])) {
            return $k;
        }

        if (isset(self::$aliasMap[$k]) && isset($dict[self::$aliasMap[$k]])) {
            return self::$aliasMap[$k];
        }

        foreach (array_keys($dict) as $dictKey) {
            if ($k === $dictKey || str_ends_with($k, '_' . $dictKey) || str_starts_with($k, $dictKey . '_')) {
                return $dictKey;
            }
        }

        return $k;
    }

    /**
     * Terjemahkan kode angka ke label teks manusiawi
     * Contoh: translate('jenis_kelamin', '1') -> 'Laki-laki'
     *         translate('provinsi', '36', $row) -> 'Banten'
     *         translate('r103', '070', $row) -> 'Serpong Utara'
     */
    public static function translate(string $colKey, $rawVal, $row = null, bool $includeCode = false): ?string
    {
        if (is_bool($row)) {
            $includeCode = $row;
            $row = null;
        }

        if ($rawVal === null || $rawVal === '') {
            return null;
        }

        $valStr = trim((string)$rawVal);
        if ($valStr === '' || $valStr === '-') {
            return null;
        }

        // Normalisasi format float bulat seperti "1.0" -> "1"
        if (is_numeric($valStr) && str_contains($valStr, '.') && (float)$valStr == (int)(float)$valStr) {
            $valStr = (string)(int)(float)$valStr;
        }

        $lowerCol = strtolower(trim($colKey));
        
        // Lewati kolom identitas unik atau nilai numerik murni
        if (in_array($lowerCol, [
            'id', 'nik', 'nomor_induk_kependudukan', 'no_nik', 'no_ktp',
            'kk', 'no_kk', 'nomor_kartu_keluarga',
            'nama', 'nama_lengkap', 'fullname',
            'gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan',
            'umur', 'usia', 'tanggal_lahir', 'tgl_lahir', 'alamat',
            'latitude', 'longitude', 'lat', 'long', 'telepon', 'hp'
        ])) {
            return null;
        }

        // 0. Resolusi Kolom Wilayah dari Kolom Pendamping (Companion Column) jika $row diberikan
        if ($row !== null) {
            $rowArr = is_array($row) ? $row : (array)$row;
            $lowerRow = [];
            foreach ($rowArr as $rk => $rv) {
                $lowerRow[strtolower(trim((string)$rk))] = $rv;
            }

            $getComp = function(array $candidateKeys) use ($lowerRow): ?string {
                foreach ($candidateKeys as $ck) {
                    if (isset($lowerRow[$ck])) {
                        $cv = $lowerRow[$ck];
                        if (!empty($cv) && is_string($cv) && !is_numeric($cv)) {
                            return trim($cv);
                        }
                    }
                }
                return null;
            };

            if (in_array($lowerCol, ['provinsi', 'r101', 'kd_prov', 'kode_provinsi', 'provinsi_ktp', 'kode_provinsi_ktp'])) {
                $comp = $getComp(['r101n', 'provinsi_nama', 'nama_provinsi', 'prov_nama', 'nama_prov']);
                if ($comp !== null) return $includeCode ? "{$comp} ({$valStr})" : $comp;
            } elseif (in_array($lowerCol, ['kabupaten_kota', 'kabupaten', 'kota', 'r102', 'kd_kab', 'kode_kabupaten', 'kabupaten_kota_ktp', 'kode_kabupaten_kota_ktp'])) {
                $comp = $getComp(['r102n', 'kabupaten_nama', 'nama_kabupaten', 'nama_kabupaten_kota', 'kab_nama', 'nama_kab', 'nama_kota']);
                if ($comp !== null) return $includeCode ? "{$comp} ({$valStr})" : $comp;
            } elseif (in_array($lowerCol, ['kecamatan', 'r103', 'kd_kec', 'kode_kecamatan', 'kecamatan_ktp', 'kode_kecamatan_ktp'])) {
                $comp = $getComp(['r103n', 'kecamatan_nama', 'nama_kecamatan', 'kec_nama', 'nama_kec']);
                if ($comp !== null) return $includeCode ? "{$comp} ({$valStr})" : $comp;
            } elseif (in_array($lowerCol, ['kelurahan_desa', 'kelurahan', 'desa', 'r104', 'kd_desa', 'kode_desa', 'kelurahan_desa_ktp', 'kode_kelurahan_desa_ktp'])) {
                $comp = $getComp(['r104n', 'kelurahan_nama', 'desa_nama', 'nama_desa', 'nama_kelurahan', 'des_nama', 'nama_des']);
                if ($comp !== null) return $includeCode ? "{$comp} ({$valStr})" : $comp;
            }
        }

        $dict = self::getDictionary();
        $canonKey = self::resolveCanonicalKey($lowerCol);

        // 1. Cek kamus spesifik variabel dari active_rules.json
        if (isset($dict[$canonKey])) {
            $codeMap = $dict[$canonKey];
            if (isset($codeMap[$valStr])) {
                $label = $codeMap[$valStr];
                return $includeCode ? "{$label} ({$valStr})" : $label;
            }
            if (ctype_digit($valStr)) {
                $intStr = (string)(int)$valStr;
                $padStr = str_pad($intStr, 2, '0', STR_PAD_LEFT);
                if (isset($codeMap[$intStr])) {
                    return $includeCode ? "{$codeMap[$intStr]} ({$valStr})" : $codeMap[$intStr];
                }
                if (isset($codeMap[$padStr])) {
                    return $includeCode ? "{$codeMap[$padStr]} ({$valStr})" : $codeMap[$padStr];
                }
            }
        }

        // 2. Variabel biner kepemilikan aset & bantuan kependudukan
        if (str_starts_with($lowerCol, 'penerima_') || str_starts_with($lowerCol, 'aset_') || str_starts_with($lowerCol, 'kepemilikan_') || str_contains($lowerCol, '_bantuan')) {
            if ($valStr === '1') {
                return $includeCode ? 'Ya / Penerima (1)' : 'Ya';
            } elseif ($valStr === '0' || $valStr === '2') {
                return $includeCode ? 'Tidak (0/2)' : 'Tidak';
            }
        }

        // 3. Khusus Kolom Survei PODES / BPS (format r[nomor_kuesioner], misal r801_1, r802, r804a1, r1001a, r1201a, dll.)
        if (preg_match('/^r[0-9]+[a-z0-9_]*$/i', $lowerCol)) {
            // Standar binary coding BPS kuesioner desa
            if ($valStr === '1') {
                return $includeCode ? 'Ada / Ya (1)' : 'Ada / Ya';
            } elseif ($valStr === '2') {
                return $includeCode ? 'Tidak Ada / Tidak (2)' : 'Tidak Ada / Tidak';
            } elseif ($valStr === '0') {
                return $includeCode ? 'Tidak Ada (0)' : 'Tidak Ada';
            }
        }

        return null;
    }

    /**
     * Terjemahkan seluruh field dalam 1 baris record
     */
    public static function translateRecord(array $row): array
    {
        $translated = [];
        foreach ($row as $k => $val) {
            $trans = self::translate((string)$k, $val, $row);
            if ($trans !== null) {
                $translated[$k] = $trans;
            }
        }
        return $translated;
    }

    /**
     * Dapatkan rincian seluruh field record lengkap dengan label dan terjemahan (untuk Modal Preview)
     */
    public static function getAllFieldsDetailed(array $row, array $columnsMap = []): array
    {
        $fields = [];
        $dict = self::getDictionary();

        foreach ($row as $k => $val) {
            $colName = (string)$k;
            $lowerKey = strtolower($colName);
            
            // Lewati metadata sistem internal
            if (in_array($lowerKey, ['id', 'quality_status', 'quality_issues', 'extra_attributes', 'created_at', 'updated_at'])) {
                continue;
            }

            $label = $columnsMap[$lowerKey] ?? ucwords(str_replace(['_', '-'], ' ', $colName));
            $rawVal = $val !== null ? (string)$val : '-';
            $transVal = self::translate($lowerKey, $val, $row);

            $fields[] = [
                'key' => $colName,
                'label' => $label,
                'raw_value' => $rawVal,
                'translated_value' => $transVal,
                'has_translation' => ($transVal !== null && $transVal !== $rawVal)
            ];
        }

        return $fields;
    }
}
