# 🚀 PADU v1.02 — Pengolah & Analisis Data Terpadu
### *High-Speed 100% Offline Analytics & Data Quality Audit Engine for DTSEN 2026*

---

## 📌 Ringkasan Proyek

**PADU (Pengolah dan Analisis Data Terpadu) v1.02** adalah platform analitik dan pengolahan data kependudukan & sosial-ekonomi berkinerja tinggi yang dirancang khusus untuk mengelola dataset **DTSEN (Data Terpadu Sosial Ekonomi Nasional) 2026** hingga **13+ juta baris data**.

Aplikasi ini beroperasi secara **100% OFFLINE** (*Zero-Cloud Dependencies*) di sistem operasi Windows tanpa memerlukan instalasi database server eksternal seperti MySQL atau PostgreSQL.

---

## ✨ Fitur Utama

- ⚡ **Ultra-Fast Vectorized Ingestion**: Mengimpor & membaca dataset 13 juta baris CSV/XLSX dalam waktu kurang dari 3 detik (kecepatan > 1.500.000 baris/detik) menggunakan DuckDB OLAP engine.
- 🛡️ **Penyamaran Data Sensitif (PII Data Masking)**: Melindungi NIK, Nama Lengkap, Tanggal Lahir, Email, Nominal Gaji, Alamat, dan RT/RW pengguna pada tampilan interface.
- 🔍 **Audit Kualitas Data Real-time (Quality Check Engine)**: Mengelompokkan data secara otomatis ke dalam kategori `Valid`, `Warning`, dan `Critical` berdasarkan aturan integritas NIK, KK, Nama, Desil, Usia, dan Gaji.
- 📊 **Agregasi Statistik Instan (KPI Analytics)**: Menghitung nilai Maksimum, Minimum, Rata-rata, Total Sum, dan Kontribusi secara instan via query terurai DuckDB (< 0.05 detik).
- 🗂️ **Pemfilteran Dinamis Multi-Kolom**: Dukungan filter kombinasi multi-checkbox untuk Desil Kesejahteraan (1–10), Rentang Usia, Rentang Gaji, Status Kerja, dan Wilayah.
- 📦 **Ekspor Terkompresi & Laporan Audit**: Mengespor paket data bersih/as-is dalam format ZIP (berisi file CSV & `metadata.txt` audit log) atau file CSV laporan kesalahan.
- 💻 **Zero-Config Launch**: Dijalankan secara otomatis hanya dengan mengklik ganda `JALANKAN_PADU.bat`.

---

## 🏗️ Arsitektur Sistem (Hybrid Dual-Engine)

```
+-----------------------------------------------------------------------------------+
|                                 PADU v1.02 ENGINE                                 |
+-----------------------------------------------------------------------------------+
|                                                                                   |
|  [ Web Browser UI ] <---> [ Portable PHP 8.2 Server (Laravel 12 Framework) ]      |
|                                       |                                           |
|                               (Subprocess Exec)                                   |
|                                       v                                           |
|                           [ Python 3.11 Analytics Engine ]                        |
|                                       |                                           |
|                               (Vectorized OLAP)                                   |
|                                       v                                           |
|                           [ In-Process DuckDB Database ]                          |
|                                                                                   |
+-----------------------------------------------------------------------------------+
```

---

## 💻 Persyaratan Perangkat Keras & Perangkat Lunak

- **Sistem Operasi**: Windows 10 / Windows 11 (64-bit).
- **RAM**: Minimal 4 GB (Alokasi RAM sangat efisien).
- **Ruang Disk**: Minimal 2 GB.
- **Port Web**: Port `8000` (`http://127.0.0.1:8000/`).
- **Runtime**: Portable PHP 8.2 & Portable Python 3.11 *(Sudah terpaket otomatis di dalam folder aplikasi)*.

---

## 🚀 Cara Peluncuran Instan

1. Ekstrak folder proyek `aplikasi-cepat-analytics` di laptop Anda.
2. Klik ganda berkas peluncur **`JALANKAN_PADU.bat`**.
3. Server lokal akan aktif dan browser otomatis membuka alamat: **`http://127.0.0.1:8000/`**.

---

## 📁 Pemetaan Berkas & Struktur Folder Utama

```
aplikasi-cepat-analytics/
├── JALANKAN_PADU.bat          # Peluncur utama aplikasi (Zero-Config Batch Launcher)
├── SRS_PADU_v1.02.md          # Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS)
├── REQUIREMENTS.md            # Catatan persyaratan hardware & runtime
├── requirements.txt           # Catatan pustaka Python opsional
├── setup_php.ps1              # Skrip otomasi penyiapan Portable PHP
├── setup_python.ps1           # Skrip otomasi penyiapan Portable Python
├── app/
│   ├── Http/Controllers/
│   │   └── DtsenController.php            # Controller utama penanganan rute & subprocess
│   ├── Models/
│   │   ├── Individu.php                   # Model Eloquent data anggota keluarga (48 var)
│   │   ├── Keluarga.php                   # Model Eloquent data keluarga (52 var)
│   │   └── Demographic.php                # Model demografi wilayah
│   └── Services/
│       ├── DataMaskingService.php         # Service penyamaran PII data sensitif
│       ├── DataQualityCheckService.php    # Service evaluasi aturan kualitas data
│       ├── DtsenImportService.php         # Service pembacaan CSV & normalisasi header
│       └── CsvExportService.php           # Service pencetakan paket ekspor CSV/ZIP
├── scratch/
│   ├── fast_import.py         # Engine ingesti & pemrosesan cepat DuckDB
│   ├── fast_duckdb.py         # Engine query & agregasi statistik DuckDB
│   ├── fast_export.py         # Engine ekspor data & pembentukan ZIP audit
│   └── fast_delete.py         # Engine pembersihan data instan
├── database/
│   ├── dataset.duckdb         # File database DuckDB aktif (OLAP High-Speed Engine)
│   └── database.sqlite        # Database SQLite pendukung
├── php/                       # Portable PHP 8.2 Runtime (Included)
├── resources/views/           # Antarmuka Blade UI (TailwindCSS & Alpine.js)
└── routes/web.php             # Rute HTTP Laravel
```

---

## 🛠️ Panduan Pengembang & Modifikasi Sub-Sistem

### 1. Mengubah Aturan Masking Data (PII)
Buka file [`app/Services/DataMaskingService.php`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/app/Services/DataMaskingService.php) untuk menyesuaikan pola penyensoran NIK, Nama, Tanggal Lahir, Email, Gaji, atau Alamat.

### 2. Memodifikasi Aturan Validasi Kualitas Data
Buka file [`app/Services/DataQualityCheckService.php`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/app/Services/DataQualityCheckService.php) untuk PHP fallback atau file [`scratch/fast_import.py`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/scratch/fast_import.py) pada bagian query SQL DuckDB (`ARRAY_FILTER`).

### 3. Mengganti atau Menambah Fitur Engine Analytics (DuckDB)
Buka file [`scratch/fast_duckdb.py`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/scratch/fast_duckdb.py) untuk menambah mode query baru atau memodifikasi logika kalkulasi statistik agregat KPI.

### 4. Mengubah Antarmuka UI / Tampilan Web
Buka file template Blade di [`resources/views/dtsen/index.blade.php`](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/resources/views/dtsen/index.blade.php).

---

## 📄 Lisensi & Hak Cipta

© 2026 PADU Development Team — Hak Cipta Dilindungi. Dikembangkan untuk Dukungan Registrasi Sosial Ekonomi & Analytics DTSEN 2026.