<?php

namespace App\Services;

use App\Models\Keluarga;
use App\Models\Individu;
use App\Models\Demographic;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class DtsenImportService
{
    /**
     * Kamus Lengkap Sinonim & Sandi Kode Variabel Acuan Metadata BAST DTSEN 2026
     */
    public static function getSynonymDictionary(): array
    {
        return [
            // Demografi Utama (Set Individu)
            'nomor_induk_kependudukan' => ['nik', 'nomor_induk_kependudukan', 'no_nik', 'id_penduduk', 'no_ktp', 'nomor_ktp', 'nik_ktp', 'b1_r101', 'r101', 'kd_nik', 'sandi_nik', 'v101', '101'],
            'nomor_kartu_keluarga' => ['kk', 'no_kk', 'nomor_kartu_keluarga', 'no_kartu_keluarga', 'id_kk', 'nokk', 'b1_r102', 'r102', 'kd_kk', 'sandi_kk', 'v102', '102'],
            'nama' => ['nama', 'nama_lengkap', 'nama_subjek', 'nama_warga', 'name', 'fullname', 'nama_penduduk', 'b4_r401', 'r401', 'nama_art', 'v401', '401'],
            'tanggal_lahir' => ['tanggal_lahir', 'tgl_lahir', 'dob', 'birth_date', 'tgl_birth', 'b4_r404', 'r404', 'tgl_lhr', 'v404', '404'],
            'jenis_kelamin' => ['jenis_kelamin', 'jk', 'gender', 'sex', 'kelamin', 'b4_r403', 'r403', 'kd_jk', 'v403', '403'],
            'status_hubungan_keluarga' => ['status_hubungan_keluarga', 'hub_keluarga', 'hub_kel', 'shdk', 'hubungan_keluarga', 'relationship', 'b4_r402', 'r402', 'v402', '402'],
            'status_kawin' => ['status_kawin', 'status_pernikahan', 'marital_status', 'st_kawin', 'st_nikah', 'b4_r406', 'r406', 'v406', '406'],
            'usia' => ['usia', 'umur', 'age', 'tahun_usia', 'b4_r405', 'r405', 'umur_thn', 'v405', '405'],
            'gaji' => ['gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income', 'gaji_pokok', 'upah', 'take_home_pay', 'pendapatan_bulanan', 'b4_r409', 'r409', 'gaji_val', 'v409', '409'],

            // Jaminan Sosial & Pendidikan
            'pbi_nas' => ['pbi_nas', 'pbi_nasional', 'pbi_apbn', 'pbi_jkn', 'bpjs_pbi_nasional', 'b4_r407a', 'r407a', 'pbi_n'],
            'pbi_pemda' => ['pbi_pemda', 'pbi_daerah', 'pbi_apbd', 'bpjs_pbi_pemda', 'b4_r407b', 'r407b', 'pbi_d'],
            'partisipasi_sekolah' => ['partisipasi_sekolah', 'partisipasi_sekolah_tingkat', 'status_sekolah', 'sekolah', 'school_participation', 'b4_r407', 'r407', 'v407', '407'],
            'jenjang_tertinggi_yang_diduduki' => ['jenjang_tertinggi_yang_diduduki', 'jenjang_tertinggi', 'jenjang_diduduki', 'tingkat_sekolah', 'b4_r408a', 'r408a'],
            'kelas_tertinggi_yang_diduduki' => ['kelas_tertinggi_yang_diduduki', 'kelas_tertinggi', 'kelas_diduduki', 'kelas_sekolah', 'b4_r408b', 'r408b'],
            'ijazah_tertinggi_yang_dimiliki' => ['ijazah_tertinggi_yang_dimiliki', 'ijazah_tertinggi', 'ijazah', 'pendidikan_terakhir', 'education_level', 'b4_r408c', 'r408c'],

            // Pekerjaan & Ekonomi
            'status_bekerja' => ['status_bekerja', 'pekerjaan', 'pekerjaan_utama', 'status_kerja', 'work_status', 'occupation', 'b4_r408', 'r408', 'is_working', 'st_kerja', 'v408', '408'],
            'lapangan_usaha_dari_pekerjaan_utama' => ['lapangan_usaha_dari_pekerjaan_utama', 'lapangan_usaha', 'sektor_pekerjaan', 'bidang_usaha', 'industry', 'b4_r410', 'r410', 'v410', '410'],
            'status_dalam_pekerjaan_utama' => ['status_dalam_pekerjaan_utama', 'status_pekerjaan', 'kedudukan_bekerja', 'job_status', 'b4_r411', 'r411'],
            'kepemilikan_usaha' => ['kepemilikan_usaha', 'memiliki_usaha', 'punya_usaha', 'has_business', 'b4_r412', 'r412'],
            'jumlah_usaha' => ['jumlah_usaha', 'jml_usaha', 'banyak_usaha', 'business_count', 'b4_r413', 'r413'],
            'lapangan_usaha_dari_usaha_utama' => ['lapangan_usaha_dari_usaha_utama', 'lapangan_usaha_omzet', 'sektor_usaha', 'b4_r414', 'r414'],
            'jumlah_pekerja_yang_dibayar_dari_usaha_utama' => ['jumlah_pekerja_yang_dibayar_dari_usaha_utama', 'pekerja_dibayar', 'karyawan_dibayar', 'b4_r415', 'r415'],
            'jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama' => ['jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama', 'pekerja_tidak_dibayar', 'karyawan_keluarga', 'b4_r416', 'r416'],
            'omzet_usaha_utama' => ['omzet_usaha_utama', 'omzet', 'omset', 'pendapatan_usaha', 'business_turnover', 'b4_r417', 'r417'],

            // Kesehatan & Disabilitas
            'kondisi_gizi' => ['kondisi_gizi', 'stunting', 'wasting', 'gizi', 'nutritional_status', 'b4_r423', 'r423'],
            'penglihatan' => ['penglihatan', 'gangguan_penglihatan', 'mata', 'vision', 'b4_r424a', 'r424a'],
            'pendengaran' => ['pendengaran', 'gangguan_pendengaran', 'telinga', 'hearing', 'b4_r424b', 'r424b'],
            'berjalan_atau_naik_tangga' => ['berjalan_atau_naik_tangga', 'berjalan', 'mobilitas', 'walking', 'b4_r424c', 'r424c'],
            'menggunakan_tangan_jari' => ['menggunakan_tangan_jari', 'penggunaan_tangan', 'motorik_halus', 'hands_use', 'b4_r424d', 'r424d'],
            'belajar_kemampuan_intelektual' => ['belajar_kemampuan_intelektual', 'kemampuan_belajar', 'intelektual', 'learning_ability', 'b4_r424e', 'r424e'],
            'pengendalian_perilaku' => ['pengendalian_perilaku', 'perilaku', 'behavior_control', 'b4_r424f', 'r424f'],
            'berbicara_komunikasi' => ['berbicara_komunikasi', 'berbicara', 'komunikasi', 'speaking', 'b4_r424g', 'r424g'],
            'mengurus_diri' => ['mengurus_diri', 'kemandirian', 'self_care', 'b4_r424h', 'r424h'],
            'mengingat_berkonsentrasi' => ['mengingat_berkonsentrasi', 'mengingat', 'konsentrasi', 'memory_focus', 'b4_r424i', 'r424i'],
            'kesedihan_depresi' => ['kesedihan_depresi', 'depresi', 'kesehatan_mental', 'depression', 'b4_r424j', 'r424j'],
            'penyakit_kronis' => ['penyakit_kronis', 'penyakit_menahun', 'chronic_disease', 'b4_r425', 'r425'],

            // Alamat KTP & Wilayah
            'provinsi' => ['provinsi', 'prov', 'provinsi_ktp', 'kd_prov', 'b1_r101_prov'],
            'kabupaten_kota' => ['kabupaten_kota', 'kabupaten', 'kota', 'kab', 'kab_kota', 'kota_kabupaten', 'kd_kab'],
            'kecamatan' => ['kecamatan', 'kec', 'kd_kec'],
            'kelurahan_desa' => ['kelurahan_desa', 'kelurahan', 'desa', 'kel', 'kd_kel', 'kd_desa'],
            'rt_ktp' => ['rt_ktp', 'rt', 'no_rt'],
            'rw_ktp' => ['rw_ktp', 'rw', 'no_rw'],
            'alamat_ktp' => ['alamat_ktp', 'alamat', 'alamat_lengkap', 'domisili', 'address', 'alamat_rumah', 'b1_r105', 'r105'],
            'pekerjaan_ktp' => ['pekerjaan_ktp', 'pekerjaan_ktp_elektronik', 'pekerjaan_ktpel'],
            'pendidikan_akhir_ktp' => ['pendidikan_akhir_ktp', 'pendidikan_ktp', 'ijazah_ktp'],

            // Variabel Rumah Tangga / Keluarga
            'desil_nasional' => ['desil', 'desil_nasional', 'desil_kesejahteraan', 'desil_ekonomi', 'quintile', 'kategori_desil', 'desil_pdt', 'desil_dtsen', 'desil_sosial', 'b1_r104', 'r104'],
            'desil_provinsi' => ['desil_provinsi', 'desil_prov', 'desil_p'],
            'desil_kabupaten_kota' => ['desil_kabupaten_kota', 'desil_kab', 'desil_kota', 'desil_k'],
            'id_pelanggan_pln' => ['id_pelanggan_pln', 'id_pln', 'no_pln', 'nomor_pln', 'pln_id', 'b3_r301', 'r301'],
            'status_kepemilikan_rumah' => ['status_kepemilikan_rumah', 'kepemilikan_bangunan', 'status_rumah', 'house_ownership', 'b2_r204', 'r204'],
            'jenis_lantai_terluas' => ['jenis_lantai_terluas', 'jenis_lantai', 'lantai', 'lantai_terluas', 'floor_type', 'b2_r201', 'r201', '201'],
            'luas_lantai' => ['luas_lantai', 'luas_bangunan', 'luas_rumah', 'floor_area', 'b2_r205', 'r205'],
            'jenis_dinding_terluas' => ['jenis_dinding_terluas', 'jenis_dinding', 'dinding', 'wall_type', 'b2_r203', 'r203'],
            'jenis_atap_terluas' => ['jenis_atap_terluas', 'jenis_atap', 'atap', 'atap_terluas', 'roof_type', 'b2_r202', 'r202', '202'],
            'sumber_air_minum_utama' => ['sumber_air_minum_utama', 'sumber_air_minum', 'sumber_air', 'air_minum', 'water_source', 'b2_r206', 'r206', '206'],
            'sumber_penerangan_utama' => ['sumber_penerangan_utama', 'sumber_penerangan', 'penerangan', 'listrik', 'light_source', 'b3_r302', 'r302'],
            'daya_terpasang' => ['daya_terpasang', 'daya_listrik', 'watt_pln', 'power_capacity', 'b3_r303', 'r303'],
            'bahan_bakar_utama_memasak' => ['bahan_bakar_utama_memasak', 'bahan_bakar_memasak', 'memasak', 'cooking_fuel', 'b2_r207', 'r207'],
            'fasilitas_bab' => ['fasilitas_bab', 'tempat_bab', 'sanitasi_bab', 'toilet', 'b2_r208', 'r208'],
            'jenis_kloset' => ['jenis_kloset', 'kloset', 'closet_type', 'b2_r209', 'r209'],
            'pembuangan_akhir_tinja' => ['pembuangan_akhir_tinja', 'septic_tank', 'pembuangan_tinja', 'b2_r210', 'r210'],

            // Aset Bergerak
            'aset_bergerak_tabung_gas' => ['aset_bergerak_tabung_gas', 'tabung_gas', 'gas_5kg', 'aset_gas', 'b3_r304a', 'r304a'],
            'aset_bergerak_lemari_es' => ['aset_bergerak_lemari_es', 'lemari_es', 'kulkas', 'refrigerator', 'b3_r304b', 'r304b'],
            'aset_bergerak_ac' => ['aset_bergerak_ac', 'ac', 'pendingin_ruangan', 'b3_r304c', 'r304c'],
            'aset_bergerak_pemanas_air' => ['aset_bergerak_pemanas_air', 'pemanas_air', 'water_heater', 'b3_r304d', 'r304d'],
            'aset_bergerak_telepon_rumah' => ['aset_bergerak_telepon_rumah', 'telepon_rumah', 'telp_kabel', 'b3_r304e', 'r304e'],
            'aset_bergerak_tv_datar' => ['aset_bergerak_tv_datar', 'tv_datar', 'tv_lcd', 'tv_led', 'b3_r304f', 'r304f'],
            'aset_bergerak_emas_perhiasan' => ['aset_bergerak_emas_perhiasan', 'emas', 'perhiasan', 'gold', 'b3_r304g', 'r304g'],
            'aset_bergerak_komputer_laptop_tablet' => ['aset_bergerak_komputer_laptop_tablet', 'laptop', 'komputer', 'tablet', 'b3_r304h', 'r304h'],
            'aset_bergerak_sepeda_motor' => ['aset_bergerak_sepeda_motor', 'sepeda_motor', 'motor', 'motorcycle', 'b3_r304i', 'r304i'],
            'aset_bergerak_sepeda' => ['aset_bergerak_sepeda', 'sepeda', 'bicycle', 'b3_r304j', 'r304j'],
            'aset_bergerak_mobil' => ['aset_bergerak_mobil', 'mobil', 'car', 'b3_r304k', 'r304k'],
            'aset_bergerak_perahu' => ['aset_bergerak_perahu', 'perahu', 'boat', 'b3_r304l', 'r304l'],
            'aset_bergerak_kapal_perahu_motor' => ['aset_bergerak_kapal_perahu_motor', 'perahu_motor', 'kapal_motor', 'b3_r304m', 'r304m'],
            'aset_bergerak_smartphone' => ['aset_bergerak_smartphone', 'smartphone', 'hp', 'handphone', 'b3_r304n', 'r304n'],

            // Aset Tidak Bergerak & Ternak
            'aset_tidak_bergerak_lahan_lainnya' => ['aset_tidak_bergerak_lahan_lainnya', 'lahan_lain', 'tanah_lain', 'b3_r305a', 'r305a'],
            'aset_tidak_bergerak_rumah_lainnya' => ['aset_tidak_bergerak_rumah_lainnya', 'rumah_lain', 'properti_lain', 'b3_r305b', 'r305b'],
            'jumlah_ternak_sapi' => ['jumlah_ternak_sapi', 'ternak_sapi', 'sapi', 'b3_r306a', 'r306a'],
            'jumlah_ternak_kerbau' => ['jumlah_ternak_kerbau', 'ternak_kerbau', 'kerbau', 'b3_r306b', 'r306b'],
            'jumlah_ternak_kuda' => ['jumlah_ternak_kuda', 'ternak_kuda', 'kuda', 'b3_r306c', 'r306c'],
            'jumlah_ternak_babi' => ['jumlah_ternak_babi', 'ternak_babi', 'babi', 'b3_r306d', 'r306d'],
            'jumlah_ternak_kambing_domba' => ['jumlah_ternak_kambing_domba', 'ternak_kambing', 'kambing', 'domba', 'b3_r306e', 'r306e'],
        ];
    }

    /**
     * Label Resmi Berdasarkan Acuan Metadata BAST DTSEN 2026
     */
    public static function getCanonicalLabels(): array
    {
        return [
            'gaji' => 'Gaji Bulanan (Rp)',
            'nomor_induk_kependudukan' => 'Nomor Induk Kependudukan (NIK)',
            'nomor_kartu_keluarga' => 'Nomor Kartu Keluarga (KK)',
            'nama' => 'Nama Lengkap',
            'usia' => 'Usia (Tahun)',
            'desil_nasional' => 'Desil Kesejahteraan',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tanggal_lahir' => 'Tanggal Lahir',
            'alamat_ktp' => 'Alamat KTP',
            'provinsi' => 'Provinsi',
            'kabupaten_kota' => 'Kabupaten/Kota',
            'kecamatan' => 'Kecamatan',
            'kelurahan_desa' => 'Kelurahan/Desa',
            'rt_ktp' => 'RT',
            'rw_ktp' => 'RW',
            'status_bekerja' => 'Status Bekerja',
            'status_kawin' => 'Status Kawin',
            'status_hubungan_keluarga' => 'Status Hubungan Keluarga',
            'pbi_nas' => 'PBI Nasional',
            'pbi_pemda' => 'PBI Pemda',
            'partisipasi_sekolah' => 'Partisipasi Sekolah',
            'jenjang_tertinggi_yang_diduduki' => 'Jenjang Diduduki',
            'kelas_tertinggi_yang_diduduki' => 'Kelas Diduduki',
            'ijazah_tertinggi_yang_dimiliki' => 'Ijazah Dimiliki',
            'lapangan_usaha_dari_pekerjaan_utama' => 'Lapangan Usaha Utama',
            'status_dalam_pekerjaan_utama' => 'Status Pekerjaan Utama',
            'kepemilikan_usaha' => 'Kepemilikan Usaha',
            'jumlah_usaha' => 'Jumlah Usaha',
            'lapangan_usaha_dari_usaha_utama' => 'Lapangan Usaha Usaha Utama',
            'jumlah_pekerja_yang_dibayar_dari_usaha_utama' => 'Pekerja Dibayar',
            'jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama' => 'Pekerja Tidak Dibayar',
            'omzet_usaha_utama' => 'Omzet Usaha Utama',
            'kondisi_gizi' => 'Kondisi Gizi (Wasting/Stunting)',
            'penglihatan' => 'Penglihatan',
            'pendengaran' => 'Pendengaran',
            'berjalan_atau_naik_tangga' => 'Berjalan / Naik Tangga',
            'menggunakan_tangan_jari' => 'Penggunaan Tangan & Jari',
            'belajar_kemampuan_intelektual' => 'Kemampuan Intelektual',
            'pengendalian_perilaku' => 'Pengendalian Perilaku',
            'berbicara_komunikasi' => 'Berbicara / Komunikasi',
            'mengurus_diri' => 'Mengurus Diri',
            'mengingat_berkonsentrasi' => 'Mengingat & Konsentrasi',
            'kesedihan_depresi' => 'Depresi / Kesehatan Mental',
            'penyakit_kronis' => 'Penyakit Kronis',
            'provinsi_ktp' => 'Provinsi (KTP)',
            'kabupaten_kota_ktp' => 'Kabupaten/Kota (KTP)',
            'kecamatan_ktp' => 'Kecamatan (KTP)',
            'kelurahan_desa_ktp' => 'Kelurahan/Desa (KTP)',
            'pekerjaan_ktp' => 'Pekerjaan (KTP)',
            'pendidikan_akhir_ktp' => 'Pendidikan Akhir (KTP)',
            'desil_provinsi' => 'Desil Provinsi',
            'desil_kabupaten_kota' => 'Desil Kab/Kota',
            'id_pelanggan_pln' => 'ID Pelanggan PLN',
            'status_kepemilikan_rumah' => 'Status Kepemilikan Rumah',
            'jenis_lantai_terluas' => 'Jenis Lantai Terluas',
            'luas_lantai' => 'Luas Lantai (m2)',
            'jenis_dinding_terluas' => 'Jenis Dinding Terluas',
            'jenis_atap_terluas' => 'Jenis Atap Terluas',
            'sumber_air_minum_utama' => 'Sumber Air Minum Utama',
            'sumber_penerangan_utama' => 'Sumber Penerangan Utama',
            'daya_terpasang' => 'Daya Terpasang PLN',
            'bahan_bakar_utama_memasak' => 'Bahan Bakar Utama Memasak',
            'fasilitas_bab' => 'Fasilitas BAB',
            'jenis_kloset' => 'Jenis Kloset',
            'pembuangan_akhir_tinja' => 'Pembuangan Akhir Tinja',
            'aset_bergerak_tabung_gas' => 'Aset Tabung Gas (>=5.5kg)',
            'aset_bergerak_lemari_es' => 'Aset Lemari Es / Kulkas',
            'aset_bergerak_ac' => 'Aset AC',
            'aset_bergerak_pemanas_air' => 'Aset Pemanas Air',
            'aset_bergerak_telepon_rumah' => 'Aset Telepon Rumah',
            'aset_bergerak_tv_datar' => 'Aset TV Datar',
            'aset_bergerak_emas_perhiasan' => 'Aset Emas Perhiasan',
            'aset_bergerak_komputer_laptop_tablet' => 'Aset Laptop / Tablet',
            'aset_bergerak_sepeda_motor' => 'Aset Sepeda Motor',
            'aset_bergerak_sepeda' => 'Aset Sepeda',
            'aset_bergerak_mobil' => 'Aset Mobil',
            'aset_bergerak_perahu' => 'Aset Perahu',
            'aset_bergerak_kapal_perahu_motor' => 'Aset Kapal Perahu Motor',
            'aset_bergerak_smartphone' => 'Aset Smartphone',
            'aset_tidak_bergerak_lahan_lainnya' => 'Aset Lahan Lainnya',
            'aset_tidak_bergerak_rumah_lainnya' => 'Aset Rumah Lainnya',
            'jumlah_ternak_sapi' => 'Jumlah Ternak Sapi',
            'jumlah_ternak_kerbau' => 'Jumlah Ternak Kerbau',
            'jumlah_ternak_kuda' => 'Jumlah Ternak Kuda',
            'jumlah_ternak_babi' => 'Jumlah Ternak Babi',
            'jumlah_ternak_kambing_domba' => 'Jumlah Ternak Kambing / Domba',
        ];
    }

    /**
     * Normalisasi Header Variabel Menggunakan Kamus Sinonim BAST Metadata DTSEN 2026
     */
    public static function normalizeHeaderKey(string $header): array
    {
        $hStr = trim((string)$header);
        $cleaned = strtolower($hStr);
        $cleaned = str_replace([' ', '-', '.', '/', '\\', '(', ')'], '_', $cleaned);
        $cleanKey = preg_replace('/[^a-z0-9_]/', '', $cleaned);
        $cleanKey = preg_replace('/_+/', '_', trim($cleanKey, '_'));

        $synonymDictionary = self::getSynonymDictionary();
        $canonicalLabels = self::getCanonicalLabels();

        foreach ($synonymDictionary as $canonicalKey => $aliases) {
            if (in_array($cleanKey, $aliases)) {
                return [
                    'key' => $canonicalKey,
                    'label' => $canonicalLabels[$canonicalKey] ?? ucwords(str_replace('_', ' ', $canonicalKey)),
                    'original' => $hStr
                ];
            }
        }

        return [
            'key' => $cleanKey,
            'label' => ucwords(str_replace('_', ' ', $hStr)),
            'original' => $hStr
        ];
    }

    /**
     * Helper Format Ukuran Berkas Bytes Ke KB / MB
     */
    public static function formatBytes($bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max((int)$bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Parse dan Impor data dari file CSV / XLS / XLSX
     */
    public static function parseAndImportFile(string $filePath, string $extension, ?string $originalName = null, ?int $fileSize = null): array
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        DB::disableQueryLog();
        try {
            DB::statement('PRAGMA journal_mode = WAL;');
            DB::statement('PRAGMA synchronous = NORMAL;');
            DB::statement('PRAGMA temp_store = MEMORY;');
        } catch (\Throwable $e) {}

        $extension = strtolower($extension);
        $realSize = $fileSize ?? (is_file($filePath) ? filesize($filePath) : 0);
        $name = $originalName ?? ('dataset_dtsen.' . $extension);

        // Jika Excel (.xlsx/.xls), parse dengan PhpSpreadsheet, jika CSV gunakan generator stream
        $rowGenerator = null;
        if (in_array($extension, ['csv', 'txt'])) {
            $rowGenerator = self::streamCsvRows($filePath);
        } else if (in_array($extension, ['xls', 'xlsx'])) {
            $rowsArr = self::parseExcelFile($filePath);
            $rowGenerator = (function() use ($rowsArr) {
                foreach ($rowsArr as $r) { yield $r; }
            })();
        } else {
            throw new \Exception("Format file .{$extension} tidak didukung. Harap upload file .csv, .xls, atau .xlsx.");
        }

        // Ambil header baris pertama dari generator
        $headers = null;
        foreach ($rowGenerator as $firstRow) {
            $headers = $firstRow;
            break;
        }

        if (empty($headers)) {
            throw new \Exception("File kosong atau tidak berisi baris header data.");
        }

        $rawHeadersMap = [];
        $normalizedHeaders = [];

        foreach ($headers as $idx => $h) {
            $norm = self::normalizeHeaderKey((string)$h);
            $normalizedHeaders[$idx] = $norm['key'];
            $rawHeadersMap[$norm['key']] = $norm['label'];
        }

        session([
            'uploaded_headers' => $rawHeadersMap,
            'uploaded_file_metrics' => [
                'original_name' => $name,
                'extension' => strtoupper($extension),
                'file_size_formatted' => self::formatBytes($realSize),
                'file_size_bytes' => $realSize,
                'total_headers' => count($headers),
                'mapped_variables' => count($rawHeadersMap),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]
        ]);

        $insertedCount = 0;
        $updatedCount = 0;
        $invalidCount = 0;
        $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('individus');

        // Mengolah data dalam batch 500 baris langsung dari stream generator (Aman dari limit parameter SQLite)
        $chunk = [];
        $chunkSize = 500;

        foreach ($rowGenerator as $rowValues) {
            if (empty(array_filter($rowValues))) {
                continue;
            }
            $chunk[] = $rowValues;

            if (count($chunk) >= $chunkSize) {
                self::processBatchChunk($chunk, $normalizedHeaders, $tableColumns, $insertedCount, $invalidCount);
                $chunk = [];
                gc_collect_cycles();
            }
        }

        if (!empty($chunk)) {
            self::processBatchChunk($chunk, $normalizedHeaders, $tableColumns, $insertedCount, $invalidCount);
            $chunk = [];
            gc_collect_cycles();
        }

        return [
            'total_rows' => $insertedCount,
            'inserted' => $insertedCount,
            'updated' => $updatedCount,
            'invalid' => $invalidCount,
        ];
    }

    /**
     * Processing Batch Chunk Inserts ke Database
     */
    private static function processBatchChunk(array $batchRows, array $normalizedHeaders, array $tableColumns, int &$insertedCount, int &$invalidCount): void
    {
        DB::transaction(function () use ($batchRows, $normalizedHeaders, $tableColumns, &$insertedCount, &$invalidCount) {
            $batchIndividuInserts = [];
            $batchKeluargaInserts = [];
            $nowStr = date('Y-m-d H:i:s');

            foreach ($batchRows as $rowValues) {
                $rowMap = [];
                foreach ($normalizedHeaders as $idx => $headerKey) {
                    $rowMap[$headerKey] = isset($rowValues[$idx]) ? trim((string)$rowValues[$idx]) : '';
                }

                $nik = $rowMap['nomor_induk_kependudukan'] ?? ($rowMap['nik'] ?? '');
                $kk = $rowMap['nomor_kartu_keluarga'] ?? ($rowMap['no_kk'] ?? ($rowMap['kk'] ?? ''));
                $nama = $rowMap['nama'] ?? ($rowMap['nama_lengkap'] ?? '');
                $tglLahir = $rowMap['tanggal_lahir'] ?? ($rowMap['tgl_lahir'] ?? null);
                $jenisKelamin = $rowMap['jenis_kelamin'] ?? ($rowMap['jk'] ?? ($rowMap['gender'] ?? null));
                $hubKeluarga = $rowMap['status_hubungan_keluarga'] ?? ($rowMap['hub_keluarga'] ?? null);
                $statusKawin = $rowMap['status_kawin'] ?? ($rowMap['status_pernikahan'] ?? null);
                $statusBekerja = $rowMap['status_bekerja'] ?? ($rowMap['pekerjaan'] ?? null);
                $lapanganUsaha = $rowMap['lapangan_usaha_dari_pekerjaan_utama'] ?? ($rowMap['pekerjaan'] ?? 'Perdagangan besar');

                $desil = (int)($rowMap['desil_nasional'] ?? ($rowMap['desil'] ?? 1));
                $jenisLantai = $rowMap['jenis_lantai_terluas'] ?? ($rowMap['lantai'] ?? '01');
                $jenisAtap = $rowMap['jenis_atap_terluas'] ?? ($rowMap['atap'] ?? '1');
                $sumberAir = $rowMap['sumber_air_minum_utama'] ?? '01';

                $prov = $rowMap['provinsi'] ?? 'DKI Jakarta';
                $kota = $rowMap['kabupaten_kota'] ?? ($rowMap['kota'] ?? 'Jakarta Selatan');
                $kec = $rowMap['kecamatan'] ?? 'Cilandak';
                $kel = $rowMap['kelurahan_desa'] ?? 'Cilandak Barat';
                $alamat = $rowMap['alamat_ktp'] ?? ($rowMap['alamat'] ?? 'Jl. Mawar No. 1');
                $rt = $rowMap['rt_ktp'] ?? ($rowMap['rt'] ?? '001');
                $rw = $rowMap['rw_ktp'] ?? ($rowMap['rw'] ?? '001');

                if (empty($kk)) {
                    $kk = '3201' . rand(10, 99) . rand(1000000000, 9999999999);
                }
                if (empty($nik)) {
                    $nik = '3201' . rand(10, 99) . rand(1000000000, 9999999999);
                }

                if (!isset($batchKeluargaInserts[$kk])) {
                    $batchKeluargaInserts[$kk] = [
                        'nomor_kartu_keluarga' => $kk,
                        'kode_provinsi' => '32',
                        'provinsi' => $prov,
                        'kode_kabupaten_kota' => '3201',
                        'kabupaten_kota' => $kota,
                        'kode_kecamatan' => '3201010',
                        'kecamatan' => $kec,
                        'kode_kelurahan_desa' => '3201010001',
                        'kelurahan_desa' => $kel,
                        'alamat' => $alamat,
                        'jumlah_anggota_keluarga' => 1,
                        'desil_nasional' => max(1, min(10, $desil)),
                        'jenis_lantai_terluas' => $jenisLantai,
                        'jenis_atap_terluas' => $jenisAtap,
                        'sumber_air_minum_utama' => $sumberAir,
                        'created_at' => $nowStr,
                        'updated_at' => $nowStr,
                    ];
                }

                $gaji = $rowMap['gaji'] ?? ($rowMap['gaji_bulanan'] ?? ($rowMap['pendapatan'] ?? null));
                $usia = $rowMap['usia'] ?? ($rowMap['umur'] ?? null);

                $eval = DataQualityCheckService::evaluate($rowMap, [
                    'desil_nasional' => $rowMap['desil'] ?? $rowMap['desil_nasional'] ?? null,
                ]);

                if ($eval['status'] !== 'Valid') {
                    $invalidCount++;
                }

                $mainAttributes = [];
                $extraAttributes = [];

                foreach ($rowMap as $kKey => $kVal) {
                    if (in_array($kKey, $tableColumns)) {
                        $mainAttributes[$kKey] = $kVal;
                    } else {
                        $extraAttributes[$kKey] = $kVal;
                    }
                }

                $rowInsert = array_merge([
                    'nomor_induk_kependudukan' => $nik,
                    'nomor_kartu_keluarga' => $kk,
                    'nama' => $nama,
                    'gaji' => $gaji,
                    'gaji_bulanan' => $gaji,
                    'usia' => $usia,
                    'tanggal_lahir' => !empty($tglLahir) ? date('Y-m-d', strtotime($tglLahir)) : (!empty($usia) ? date('Y-m-d', strtotime("-{$usia} years")) : null),
                    'jenis_kelamin' => $jenisKelamin,
                    'status_hubungan_keluarga' => $hubKeluarga,
                    'status_kawin' => $statusKawin,
                    'status_bekerja' => $statusBekerja,
                    'lapangan_usaha_dari_pekerjaan_utama' => $lapanganUsaha,
                    'rt_ktp' => $rt,
                    'rw_ktp' => $rw,
                    'alamat_ktp' => $alamat,
                    'extra_attributes' => json_encode($extraAttributes),
                    'quality_status' => $eval['status'],
                    'quality_issues' => json_encode($eval['issues']),
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ], $mainAttributes);

                $batchIndividuInserts[] = $rowInsert;
                $insertedCount++;
            }

            if (!empty($batchKeluargaInserts)) {
                DB::table('keluargas')->insertOrIgnore(array_values($batchKeluargaInserts));
            }

            if (!empty($batchIndividuInserts)) {
                DB::table('individus')->insertOrIgnore($batchIndividuInserts);
            }
        });
    }

    /**
     * Helper Generator Stream File CSV (Per-baris tanpa membebani RAM)
     */
    private static function streamCsvRows(string $filePath): \Generator
    {
        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = fgets($handle);
            rewind($handle);
            $separator = (strpos($firstLine, ';') !== false) ? ';' : ',';

            while (($data = fgetcsv($handle, 4096, $separator)) !== false) {
                yield $data;
            }
            fclose($handle);
        }
    }

    /**
     * Helper Parser File Excel (XLS / XLSX) menggunakan PhpSpreadsheet
     */
    private static function parseExcelFile(string $filePath): array
    {
        if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            return $worksheet->toArray();
        }
        throw new \Exception("Library PhpSpreadsheet belum siap. Harap konfirmasi instalasi.");
    }

    /**
     * Content Template CSV Sampel untuk Di-download User
     */
    public static function generateSampleCsvContent(): string
    {
        $headers = [
            'nomor_induk_kependudukan',
            'nomor_kartu_keluarga',
            'nama',
            'tanggal_lahir',
            'jenis_kelamin',
            'status_hubungan_keluarga',
            'status_kawin',
            'status_bekerja',
            'desil_nasional',
            'jenis_lantai_terluas',
            'jenis_atap_terluas',
            'provinsi',
            'kabupaten_kota',
            'alamat_ktp'
        ];

        $sampleRow1 = [
            '3201011508840001',
            '3201012010180005',
            'Budi Santoso',
            '1984-08-15',
            'Laki-laki',
            'Kepala keluarga',
            'Kawin/nikah',
            'Ya',
            '2',
            '01',
            '1',
            'Jawa Barat',
            'Bogor',
            'Jl. Mawar No. 12'
        ];

        $sampleRow2 = [
            '3201014502900002',
            '3201012010180005',
            'Anisa Rahmawati',
            '1990-02-15',
            'Perempuan',
            'Istri',
            'Kawin/nikah',
            'Tidak',
            '2',
            '01',
            '1',
            'Jawa Barat',
            'Bogor',
            'Jl. Mawar No. 12'
        ];

        $output = "\xEF\xBB\xBF" . implode(',', $headers) . "\n";
        $output .= implode(',', $sampleRow1) . "\n";
        $output .= implode(',', $sampleRow2) . "\n";

        return $output;
    }

    /**
     * Respon Download Template File Excel (.xlsx / .xls) Lengkap dengan Sheet Kamus Variabel Metadata
     */
    public static function generateExcelTemplateResponse(string $format = 'xlsx')
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: Set Data Anggota Keluarga (Data Sheet)
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Set Data Anggota Keluarga');

        $headers = [
            'nomor_induk_kependudukan',
            'nomor_kartu_keluarga',
            'nama',
            'tanggal_lahir',
            'jenis_kelamin',
            'status_hubungan_keluarga',
            'status_kawin',
            'status_bekerja',
            'desil_nasional',
            'jenis_lantai_terluas',
            'jenis_atap_terluas',
            'provinsi',
            'kabupaten_kota',
            'alamat_ktp'
        ];

        $sheetData->fromArray($headers, NULL, 'A1');
        $sheetData->getStyle('A1:N1')->getFont()->setBold(true);
        $sheetData->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');

        // Sample Row 1
        $sheetData->setCellValueExplicit('A2', '3201011508840001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheetData->setCellValueExplicit('B2', '3201012010180005', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheetData->setCellValue('C2', 'Budi Santoso');
        $sheetData->setCellValue('D2', '1984-08-15');
        $sheetData->setCellValue('E2', 'Laki-laki');
        $sheetData->setCellValue('F2', 'Kepala keluarga');
        $sheetData->setCellValue('G2', 'Kawin/nikah');
        $sheetData->setCellValue('H2', 'Ya');
        $sheetData->setCellValue('I2', '2');
        $sheetData->setCellValue('J2', '01');
        $sheetData->setCellValue('K2', '1');
        $sheetData->setCellValue('L2', 'Jawa Barat');
        $sheetData->setCellValue('M2', 'Bogor');
        $sheetData->setCellValue('N2', 'Jl. Mawar No. 12');

        // Sample Row 2
        $sheetData->setCellValueExplicit('A3', '3201014502900002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheetData->setCellValueExplicit('B3', '3201012010180005', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheetData->setCellValue('C3', 'Anisa Rahmawati');
        $sheetData->setCellValue('D3', '1990-02-15');
        $sheetData->setCellValue('E3', 'Perempuan');
        $sheetData->setCellValue('F3', 'Istri');
        $sheetData->setCellValue('G3', 'Kawin/nikah');
        $sheetData->setCellValue('H3', 'Tidak');
        $sheetData->setCellValue('I3', '2');
        $sheetData->setCellValue('J3', '01');
        $sheetData->setCellValue('K3', '1');
        $sheetData->setCellValue('L3', 'Jawa Barat');
        $sheetData->setCellValue('M3', 'Bogor');
        $sheetData->setCellValue('N3', 'Jl. Mawar No. 12');

        // SHEET 2: Kamus Variabel & Persamaan Sandi
        $sheetKamus = $spreadsheet->createSheet();
        $sheetKamus->setTitle('Kamus & Persamaan Sandi');

        $kamusHeaders = ['No', 'Key Database', 'Nama / Label BAST Resmi', 'Sandi Kode Acuan', 'Daftar Persamaan Sandi & Sinonim Terdaftar'];
        $sheetKamus->fromArray($kamusHeaders, NULL, 'A1');
        $sheetKamus->getStyle('A1:E1')->getFont()->setBold(true);
        $sheetKamus->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

        $dictionary = self::getSynonymDictionary();
        $labels = self::getCanonicalLabels();
        $rowIdx = 2;
        $no = 1;

        foreach ($labels as $key => $label) {
            $aliases = $dictionary[$key] ?? [$key];
            $sandiCodes = array_filter($aliases, function($a) {
                return preg_match('/^(b\d+_r\d+|r\d+|v\d+|\d{3})$/i', $a);
            });
            $synonyms = array_diff($aliases, $sandiCodes);

            $sheetKamus->setCellValue("A{$rowIdx}", $no++);
            $sheetKamus->setCellValue("B{$rowIdx}", $key);
            $sheetKamus->setCellValue("C{$rowIdx}", $label);
            $sheetKamus->setCellValue("D{$rowIdx}", implode(', ', array_map('strtoupper', $sandiCodes)));
            $sheetKamus->setCellValue("E{$rowIdx}", implode(', ', $synonyms));
            $rowIdx++;
        }

        $writer = new Xlsx($spreadsheet);
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $ext = 'xlsx';

        $tempPath = tempnam(sys_get_temp_dir(), 'dtsen_template_');
        $writer->save($tempPath);

        return response()->download($tempPath, 'template_dtsen_2026.' . $ext, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true);
    }

    /**
     * Generasi Data Demo (100 Baris Data dengan 30 Baris Error Cacat untuk Pengujian Live)
     */
    public static function generateDemoData(int $totalRows = 100): int
    {
        $namaDepan = ['Budi', 'Siti', 'Ahmad', 'Rina', 'Eko', 'Dewi', 'Hendra', 'Maya', 'Joko', 'Fitri'];
        $namaBelakang = ['Santoso', 'Wijaya', 'Kusuma', 'Pratama', 'Nugroho', 'Lestari', 'Hidayat', 'Wibowo'];

        Individu::query()->delete();
        Keluarga::query()->delete();
        Demographic::query()->delete();

        $rawHeadersMap = [
            'nomor_induk_kependudukan' => 'Nomor Induk Kependudukan (NIK)',
            'nama' => 'Nama Lengkap',
            'gaji' => 'Gaji Bulanan (Rp)',
            'desil_nasional' => 'Desil Kesejahteraan',
            'usia' => 'Usia (Tahun)',
        ];
        session([
            'uploaded_headers' => $rawHeadersMap,
            'uploaded_file_metrics' => [
                'original_name' => 'Demo_Dataset_DTSEN_2026.csv',
                'extension' => 'CSV/XLSX',
                'file_size_formatted' => '42.50 KB',
                'file_size_bytes' => 43520,
                'total_headers' => count($rawHeadersMap),
                'mapped_variables' => count($rawHeadersMap),
                'uploaded_at' => date('Y-m-d H:i:s'),
            ]
        ]);

        $count = 0;
        $errorCount = 0;

        DB::transaction(function () use ($totalRows, $namaDepan, $namaBelakang, &$count, &$errorCount) {
            for ($i = 1; $i <= $totalRows; $i++) {
                $isError = false;
                if ($errorCount < 30 && ($i <= 30 || ($i % 3 === 0 && $errorCount < 30))) {
                    $isError = true;
                    $errorCount++;
                }

                $nikDigits = substr(str_pad((string)($i * 1234567), 10, '0', STR_PAD_LEFT), 0, 10);
                $nik = '320101' . $nikDigits;
                $nama = $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)];
                $gaji = rand(3500000, 15000000);
                $desil = rand(1, 10);
                $usia = rand(18, 65);

                if ($isError) {
                    $type = $errorCount % 5;
                    if ($type === 0) {
                        // Multi-Error Row (3-4 Eror Sekaligus dalam 1 Baris Data)
                        $nik = '320199' . sprintf('%04d', $i); // Error 1: NIK 10 digit
                        $kk = '320199' . sprintf('%04d', $i);  // Error 2: KK 10 digit
                        $desil = 15;                             // Error 3: Desil 15 (di luar 1-10)
                        $nama = $nama . $i . '@#';               // Error 4: Nama mengandung angka & simbol
                    } elseif ($type === 1) {
                        $nik = '320199' . sprintf('%04d', $i); // NIK Cacat (10 digit)
                    } elseif ($type === 2) {
                        $nama = $nama . $i; // Nama mengandung angka (Budi123)
                    } elseif ($type === 3) {
                        $nama = $nama . '@#'; // Nama mengandung simbol khusus
                    } else {
                        $desil = 15; // Desil > 10
                    }
                }

                $kk = '3201' . rand(10, 99) . rand(1000000000, 9999999999);

                $keluarga = Keluarga::create([
                    'nomor_kartu_keluarga' => $kk,
                    'kode_provinsi' => '32',
                    'provinsi' => 'DKI Jakarta',
                    'kode_kabupaten_kota' => '3201',
                    'kabupaten_kota' => 'Jakarta Selatan',
                    'kode_kecamatan' => '3201010',
                    'kecamatan' => 'Cilandak',
                    'kode_kelurahan_desa' => '3201010001',
                    'kelurahan_desa' => 'Cilandak Barat',
                    'alamat' => 'Jl. Kebon Mawar No. ' . $i,
                    'jumlah_anggota_keluarga' => 1,
                    'desil_nasional' => max(1, min(10, $desil)),
                ]);

                $pendidikanList = ['SD', 'SMP', 'SMA', 'Diploma', 'Sarjana', 'Pascasarjana'];
                $pendidikanVal = $pendidikanList[$i % count($pendidikanList)];

                $rowIndividuData = [
                    'nomor_induk_kependudukan' => $nik,
                    'nomor_kartu_keluarga' => $kk,
                    'nama' => $nama,
                    'gaji' => $gaji,
                    'gaji_bulanan' => $gaji,
                    'usia' => $usia,
                    'tanggal_lahir' => date('Y-m-d', strtotime("-{$usia} years")),
                    'jenis_kelamin' => ($i % 2 === 0) ? 'Laki-laki' : 'Perempuan',
                    'status_hubungan_keluarga' => ($i % 4 === 0) ? 'Kepala keluarga' : 'Anggota keluarga',
                    'status_kawin' => ($usia > 24) ? 'Kawin/nikah' : 'Belum kawin',
                    'status_bekerja' => ($usia >= 18) ? 'Ya' : 'Tidak',
                    'ijazah_tertinggi_yang_dimiliki' => $pendidikanVal,
                    'jenjang_tertinggi_yang_diduduki' => $pendidikanVal,
                    'lapangan_usaha_dari_pekerjaan_utama' => 'Perdagangan',
                    'rt_ktp' => '001',
                    'rw_ktp' => '001',
                    'alamat_ktp' => $keluarga->alamat,
                ];

                $eval = DataQualityCheckService::evaluate($rowIndividuData, ['desil_nasional' => $desil]);

                Individu::create(array_merge($rowIndividuData, [
                    'quality_status' => $eval['status'],
                    'quality_issues' => $eval['issues'],
                ]));

                try {
                    Demographic::updateOrCreate(
                        ['nik' => (string)$nik],
                        [
                            'nama_lengkap' => $nama,
                            'jenis_kelamin' => ($i % 2 === 0) ? 'Laki-laki' : 'Perempuan',
                            'pendidikan_terakhir' => 'SMA/K',
                            'tanggal_lahir' => date('Y-m-d', strtotime("-{$usia} years")),
                            'gaji_bulanan' => $gaji,
                            'provinsi' => 'DKI Jakarta',
                            'kota_kabupaten' => 'Jakarta Selatan',
                            'kota_domisili' => 'Jakarta Selatan',
                            'kecamatan' => 'Cilandak',
                            'kelurahan_desa' => 'Cilandak Barat',
                            'rt_rw' => 'RT 001 / RW 001',
                            'alamat_lengkap' => $keluarga->alamat,
                            'email' => strtolower(str_replace(' ', '.', $nama)) . rand(10, 99) . '@gmail.com',
                            'status_pernikahan' => 'Menikah',
                            'status_kehidupan' => 'Masih Hidup',
                        ]
                    );
                } catch (\Throwable $th) {
                }

                $count++;
            }
        });

        return $count;
    }
}
