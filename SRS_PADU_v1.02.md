# Software Requirements Specification (SRS)
## PADU v1.02 — Pengolah & Analisis Data Terpadu (DTSEN 2026 Analytics Engine)

---

### 1. Pendahuluan (Introduction)

#### 1.1 Tujuan (Purpose)
Dokumen **Software Requirements Specification (SRS)** ini menyajikan spesifikasi kebutuhan perangkat lunak untuk aplikasi **PADU (Pengolah dan Analisis Data Terpadu) v1.02**. Dokumentasi ini menetapkan persyaratan fungsional, non-fungsional, arsitektur data, keamanan privasi, serta aturan validasi kualitas data untuk mengolah dataset **DTSEN (Data Terpadu Sosial Ekonomi Nasional) 2026** dengan skala hingga 13+ juta baris data secara **100% Offline**.

#### 1.2 Cakupan Produk (Product Scope)
PADU v1.02 dirancang sebagai mesin analitik performa tinggi (*High-Speed Offline Analytics Engine*) berbasis arsitektur *Hybrid Dual-Engine* (Laravel 12 PHP Web Interface + Python DuckDB Vectorized Processing Engine). Aplikasi ini memungkinkan pengguna:
- Melakukan impor dataset CSV/XLSX berukuran besar (13M+ baris) dalam hitungan detik (kecepatan > 1.5 juta baris/detik).
- Mengevaluasi dan mengaudit kualitas data secara *real-time* (*Valid*, *Warning*, *Critical*).
- Melakukan pemrosesan masking data (*PII Data Masking*) untuk melindungi NIK, Nama, Tanggal Lahir, Email, Gaji, dan Alamat.
- Memfiltrasi data dinamis multi-kolom (*Dynamic Multi-Column Filtering*) dengan kecepatan respons agregasi < 0.05 detik.
- Mengespor paket data bersih/as-is dalam format terkompresi ZIP (CSV + audit metadata log) atau laporan audit kesalahan CSV.

#### 1.3 Definisi, Akronim, dan Singkatan
| Istilah | Definisi |
| :--- | :--- |
| **PADU** | Pengolah dan Analisis Data Terpadu |
| **DTSEN** | Data Terpadu Sosial Ekonomi Nasional (Registrasi Sosial Ekonomi 2026) |
| **PII** | *Personally Identifiable Information* (Informasi Identitas Pribadi yang Sensitif) |
| **NIK** | Nomor Induk Kependudukan (16 digit angka unik) |
| **KK** | Nomor Kartu Keluarga (16 digit angka) |
| **DuckDB** | Database OLAP in-process berkinerja tinggi berbasis eksekusi vektor (*vectorized query execution engine*) |
| **HyperLogLog** | Algoritma perkiraan kardinalitas probabilistik untuk menghitung nilai unik secara instan pada dataset besar |

---

### 2. Deskripsi Umum (Overall Description)

#### 2.1 Perspektif Produk (Product Perspective)
PADU v1.02 merupakan aplikasi berdiri sendiri (*Standalone Web Application*) yang dapat dijalankan secara instan (*Zero-Config*) tanpa memerlukan koneksi internet, database server eksternal (MySQL/PostgreSQL), maupun instalasi ketergantungan *cloud*.

```
 +-----------------------------------------------------------------------+
 |                         PADU v1.02 ARCHITECTURE                       |
 +-----------------------------------------------------------------------+
 |  [ Client Web Browser ] <---> [ Portable PHP 8.2 Server (Port 8000) ] |
 |                                                |                      |
 |                                      (Subprocess Execution)           |
 |                                                v                      |
 |                                   [ Python 3.11 Engine ]              |
 |                                                |                      |
 |                                    (Direct Vectorized Query)          |
 |                                                v                      |
 |                                   [ Fast DuckDB Storage ]             |
 +-----------------------------------------------------------------------+
```

#### 2.2 Fungsi Pengguna (User Classes & Characteristics)
- **Data Analyst / Auditor Data**: Pengguna utama yang memerlukan analisis agregat instan, evaluasi *quality score*, serta ekspor laporan audit kesalahan.
- **Operator Lapangan / Administrator**: Pengguna yang melakukan unggah/impor file CSV/XLSX, pencarian NIK/KK, serta manipulasi filter dinamis.

#### 2.3 Lingkungan Operasi & Persyaratan Hardware
- **Sistem Operasi**: Windows 10 / Windows 11 (64-bit).
- **RAM**: Minimal 4 GB (Alokasi RAM sangat efisien via DuckDB memory-mapped vector pages).
- **Penyimpanan Disk**: Minimal 2 GB ruang kosong.
- **Port**: Port 8000 (`http://127.0.0.1:8000/`).

---

### 3. Persyaratan Fungsional (Functional Requirements)

#### 3.1 FR-01: Vectorized Data Ingestion (Import Engine)
- **Deskripsi**: Sistem harus mampu membaca dan memasukkan berkas CSV/XLSX ke dalam engine analitik DuckDB.
- **Spesifikasi**:
  - Mendukung normalisasi header otomatis (*synonym header mapping* untuk 48 variabel individu & 52 variabel keluarga).
  - Kecepatan pemrosesan minimal 500.000 baris/detik.
  - Menghasilkan ringkasan total baris, total KK, baris valid, warning, dan critical secara instan.

#### 3.2 FR-02: Quality Check & Audit Rules Engine
- **Deskripsi**: Setiap baris data yang masuk harus dievaluasi tingkat keabsahannya.
- **Aturan Evaluasi**:
  1. **Critical Status**:
     - Nama kosong, mengandung angka, atau mengandung karakter khusus selain `[a-zA-Z .,'-]`.
     - NIK kosong atau panjang digit != 16 angka.
     - Nomor KK != 16 digit angka (jika terisi).
     - Desil Kesejahteraan < 1 atau > 10.
     - Usia < 0 atau > 120 tahun.
     - Gaji / Pendapatan < 0 (negatif).
  2. **Warning Status**: Terjadi jika terdapat nilai kosong (*missing value*) pada variabel atribut pendukung.
  3. **Valid Status**: Seluruh aturan keabsahan dan keutuhan variabel terpenuhi.

#### 3.3 FR-03: Data Masking & PII Security Protection
- **Deskripsi**: Sistem harus dapat menyamarkan (*masking*) informasi sensitif pada tampilan layar untuk mencegah kebocoran data.
- **Aturan Masking**:
  - **NIK**: `3201021508900001` -> `3201************` (Menampilkan 4 digit awal provinsi/kabupaten).
  - **Nama Lengkap**: `Budi Santoso` -> `B*** S******` (Menampilkan huruf pertama tiap kata).
  - **Tanggal Lahir**: `1992-05-14` -> `1992-**-**` (Menampilkan tahun saja).
  - **Nominal Gaji**: `Rp 8.500.000` -> `Rp 8.xxx.xxx`.
  - **Alamat**: `Jl. Sudirman No. 45` -> `Jl. S******* No. **`.
  - **RT/RW**: `RT 003 / RW 005` -> `RT *** / RW ***`.

#### 3.4 FR-04: Instant Aggregation & KPI Analytics
- **Deskripsi**: Sistem harus mampu menghitung metrik statistik (Max, Min, Average, Total Sum, Count) pada variabel finansial/numerik secara *real-time*.
- **Spesifikasi**: Menggunakan query terurai DuckDB dengan jaminan waktu respon < 0.05 detik pada 13M+ baris.

#### 3.5 FR-05: Dynamic Multi-Column Filtering & Search
- **Deskripsi**: Pencarian instan berdasarkan Nama, NIK, KK, serta kombinasi multi-checkbox filter (Desil, Rentang Usia, Rentang Gaji, Status Kerja, Wilayah).

#### 3.6 FR-06: Export Engine (ZIP Package & Audit CSV)
- **Deskripsi**: Mendukung ekspor data hasil olahan ke dalam bentuk file terkompresi `.zip` yang berisi `data_dtsen.csv` dan file audit `metadata.txt`, atau ekspor laporan error `.csv`.

---

### 4. Persyaratan Non-Fungsional (Non-Functional Requirements)

#### 4.1 Performa (Performance)
- **Kecepatan Ingesti**: > 1.500.000 baris/detik untuk file CSV.
- **Respons Query Filter**: < 0.05 detik (50 milidetik).
- **Waktu Peluncuran**: < 3 detik melalui `JALANKAN_PADU.bat`.

#### 4.2 Keamanan & Privasi (Security & Privacy)
- **100% Offline & Isolated**: Tidak melakukan koneksi outbound, telemetri, atau dependensi API pihak ketiga.
- **Data Protection**: Penyimpanan data bersifat lokal pada file `database/dataset.duckdb`.
- **Parameterization**: Seluruh query Python DuckDB menggunakan parameterized filtering untuk mencegah SQL Injection.

#### 4.3 Keandalan & Portabilitas (Reliability & Portability)
- **Portable Runtime**: PHP 8.2 Portable dan Python 3.11 Portable bawaan menjamin aplikasi langsung berjalan di laptop Windows tanpa perlu hak akses Administrator.

---

### 5. Matriks Pemetaan Variabel DTSEN 2026

#### 5.1 Variabel Set Data Individu (48 Variabel)
`nomor_induk_kependudukan`, `nomor_kartu_keluarga`, `nama`, `tanggal_lahir`, `jenis_kelamin`, `status_hubungan_keluarga`, `pbi_nas`, `pbi_pemda`, `status_kawin`, `partisipasi_sekolah`, `jenjang_tertinggi_yang_diduduki`, `kelas_tertinggi_yang_diduduki`, `ijazah_tertinggi_yang_dimiliki`, `status_bekerja`, `lapangan_usaha_dari_pekerjaan_utama`, `status_dalam_pekerjaan_utama`, `kepemilikan_usaha`, `jumlah_usaha`, `lapangan_usaha_dari_usaha_utama`, `gaji_bulanan`, `gaji`, `usia`, `kondisi_gizi`, `penglihatan`, `pendengaran`, `berjalan_atau_naik_tangga`, `menggunakan_tangan_jari`, `belajar_kemampuan_intelektual`, `pengendalian_perilaku`, `berbicara_komunikasi`, `mengurus_diri`, `mengingat_berkonsentrasi`, `kesedihan_depresi`, `penyakit_kronis`, `kode_provinsi_ktp`, `provinsi_ktp`, `kode_kabupaten_kota_ktp`, `kabupaten_kota_ktp`, `kode_kecamatan_ktp`, `kecamatan_ktp`, `kode_kelurahan_desa_ktp`, `kelurahan_desa_ktp`, `rt_ktp`, `rw_ktp`, `dusun_ktp`, `alamat_ktp`, `pekerjaan_ktp`, `pendidikan_akhir_ktp`.

#### 5.2 Variabel Set Data Keluarga (52 Variabel)
`nomor_kartu_keluarga`, `kode_provinsi`, `provinsi`, `kode_kabupaten_kota`, `kabupaten_kota`, `kode_kecamatan`, `kecamatan`, `kode_kelurahan_desa`, `kelurahan_desa`, `alamat`, `jumlah_anggota_keluarga`, `nama_kepala_keluarga`, `desil_nasional`, `desil_provinsi`, `desil_kabupaten_kota`, `pbi_nas`, `pbi_pemda`, `id_pelanggan_pln`, `status_kepemilikan_rumah`, `jenis_lantai_terluas`, `luas_lantai`, `jenis_dinding_terluas`, `jenis_atap_terluas`, `sumber_air_minum_utama`, `sumber_penerangan_utama`, `daya_terpasang`, `bahan_bakar_utama_memasak`, `fasilitas_bab`, `jenis_kloset`, `pembuangan_akhir_tinja`, `kepemilikan_aset`, `aset_bergerak_tabung_gas`, `aset_bergerak_lemari_es`, `aset_bergerak_ac`, `aset_bergerak_pemanas_air`, `aset_bergerak_telepon_rumah`, `aset_bergerak_tv_datar`, `aset_bergerak_emas_perhiasan`, `aset_bergerak_komputer_laptop_tablet`, `aset_bergerak_sepeda_motor`, `aset_bergerak_sepeda`, `aset_bergerak_mobil`, `aset_bergerak_perahu`, `aset_bergerak_kapal_perahu_motor`, `aset_bergerak_smartphone`, `aset_tidak_bergerak_lahan_lainnya`, `aset_tidak_bergerak_rumah_lainnya`, `jumlah_ternak_sapi`, `jumlah_ternak_kerbau`, `jumlah_ternak_kuda`, `jumlah_ternak_babi`, `jumlah_ternak_kambing_domba`.
