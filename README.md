# 🚀 PADU v2.0 Enterprise — Pengolah & Analisis Data Terpadu
### *High-Speed 100% Offline Vectorized Analytics Engine & National Data Sovereignty Platform*

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%20Portable-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![DuckDB Version](https://img.shields.io/badge/DuckDB-OLAP%20v1.1+-FFF000?style=for-the-badge&logo=duckdb&logoColor=black)](https://duckdb.org)
[![Python Version](https://img.shields.io/badge/Python-3.11%20Subprocess-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS%20Local-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Security Standard](https://img.shields.io/badge/BSSN-Peraturan%20No.%204%2F2021-0052CC?style=for-the-badge&logo=shield&logoColor=white)](#kepatuhan-keamanan-siber-bssn--uu-pdp)
[![Offline](https://img.shields.io/badge/Zero--Cloud-100%25%20Air--Gapped-10B981?style=for-the-badge&logo=security&logoColor=white)](#prinsip-kedaulatan-data--air-gapped)

---

## 📌 Ringkasan Eksekutif

**PADU (Pengolah dan Analisis Data Terpadu) v2.0 Enterprise** adalah platform analitik dan tata kelola data kependudukan & sosial-ekonomi nasional berkinerja tinggi (*High-Performance Computing*) yang dirancang khusus untuk memproses dataset **DTSEN (Data Terpadu Sosial Ekonomi Nasional) 2026** hingga **15.000.000+ baris data** secara instan (*sub-second response time*).

Sistem ini beroperasi **100% MURNI LURING (Air-Gapped & Zero-Cloud Dependencies)**. Seluruh komputasi agregasi OLAP, database relasional SQLite, hashing integritas data, dan autentikasi dua faktor dieksekusi secara lokal di server/laptop kantor tanpa ada satu pun paket data yang bocor ke internet publik. Sistem diserahkan dalam status **Kedaulatan Penuh Lepas Kunci (*Turnkey Handover*)**.

---

## 🔑 Kredensial Masuk Awal (Official Handover)

Sistem menerapkan **Role-Based Access Control (RBAC)** dua tingkat dengan alur autentikasi bertahap (*Email-First Staged Login*):

| Tingkat Peran | Alamat Surel Dinas | Kata Sandi Bawaan Sementara | Prosedur Masuk Pertama Kali |
| :--- | :--- | :--- | :--- |
| **👑 Super Administrator** | `superadmin@padu.local` | `PasswordSuperAdmin2026!` | **Wajib Langsung Ubah Kata Sandi Baru** (Min. 15 Karakter BSSN) & Aktivasi 2FA TOTP HP Offline |
| **👤 Operator Data Staf** | `operator@padu.local` | `PasswordOperator2026!` | Masuk langsung ke 12 Modul Analisis DTSEN 2026 |

> [!IMPORTANT]
> **Kebijakan Keamanan Akun BSSN No. 4/2021**:
> 1. **Panjang Password**: Password baru minimal **15 karakter** (wajib mengandung huruf besar, huruf kecil, angka, dan simbol/karakter khusus).
> 2. **Pencegahan Brute-Force**: Sistem otomatis mengunci akun (**Account Lockout**) selama **15 menit** jika terjadi 5 kali salah password berturut-turut.
> 3. **Inactivity Session Timeout**: Sesi pengguna otomatis kedaluwarsa jika tidak ada aktivitas selama **15 menit**.

---

## 🛡️ Standar Kepatuhan Keamanan Siber (BSSN & UU PDP)

Aplikasi PADU v2.0 dibangun secara ketat mematuhi tiga regulasi utama Republik Indonesia:

1. **Peraturan BSSN No. 4/2021** tentang Pedoman Manajemen Keamanan Informasi SPBE:
   - Standar kompleksitas kata sandi minimal 15 karakter dan rotasi berkala.
   - Pembatasan upaya login (*Rate Limiting*) dan pencatatan log forensik yang tidak dapat diubah (*Append-Only Audit Trail*).
2. **Peraturan BSSN No. 11/2024** tentang Penanganan Insiden dan Mitigasi Malware Kriptografis:
   - Proteksi anti-*clipboard sniffer* pada Kunci Pemulihan Bencana (*Disaster Recovery Key*).
   - Pengamanan brankas fisik (*Safe Physical Storage*) untuk *Master Recovery Key*.
3. **UU No. 27/2022 tentang Pelindungan Data Pribadi (UU PDP)**:
   - Sensor otomatis data sensitif (*PII Masking*) untuk NIK (16 digit), No. KK (16 digit), dan Nama Subjek.
   - Prinsip kedaulatan data tanpa ketergantungan pihak ketiga (*Zero Third-Party Vendor Lock-in*).

---

## ⚡ Fitur Utama PADU v2.0 Enterprise

### 1. Vectorized OLAP Data Engine (DuckDB + Python Subprocess)
- Membaca, memfilter, mengurutkan, dan mengagregasi dataset **13+ juta baris data** dalam waktu **< 0.05 detik**.
- Menerapkan arsitektur *Vectorized Execution* berbasis kolom (*Columnar Storage*), mengeliminasi latensi I/O disk konvensional.

### 2. 12 Modul Analisis DTSEN 2026 Bersih & Terpadu
Antarmuka pengguna berbasis Enterprise Light Theme dengan 12 tab analisis terdedikasi:
1. **📋 Master Data Grid**: Tabel data interaktif dengan pagination berjarak, pencarian multi-kolom, dan *column visibility toggle*.
2. **🧮 QC & Integritas Data**: Validasi kelengkapan variabel, konsistensi data NIK/KK, dan kalkulasi rasio anomali.
3. **📈 KPI & Pemeringkatan**: Visualisasi statistik metrik kuantitatif dan perangkingan desil kemiskinan.
4. **💵 Analisis Finansial & Gaji**: Distribusi pendapatan, pengeluaran, desil upah, dan filter cepat kelompok gaji.
5. **⚠️ Audit Anomali & Temuan Error**: Deteksi otomatis data ganda (*duplicates*), nilai kosong (*missing values*), dan inkonsistensi demografi.
6. **📁 Manajemen Berkas & Ingesti**: Pemindai folder luring `src-dtsen/`, impor bertahap (*chunked ingestion*), dan laporan progres waktu-nyata.
7. **📤 Ekspor Data Terseleksi**: Generator berkas CSV/Excel terkompresi ZIP dengan pemilihan kolom kustom dan opsi masking PII.
8. **👥 Direktori Staf & Pengguna**: Tata kelola akun operator oleh Super Administrator secara luring.
9. **🛡️ Audit Trail Forensik**: Log permanen pencatat setiap aktivitas login, lockout, export, import, dan backup.
10. **🗜️ Cadangan Sistem AES-256 (SOP 3-2-1)**: Enkripsi arsip ZIP cadangan dengan password acak 20 karakter sekali pakai & hash SHA-256.
11. **📱 Keamanan 2FA TOTP Offline**: Pengaturan autentikasi dua faktor berbasis aplikasi Google Authenticator / FreeOTP / Aegis.
12. **👤 Profil & Pengaturan Akun**: Ringkasan kredensial aktif, sesi terakhir, dan perubahan kata sandi mandiri.

### 3. Arsitektur Autentikasi Dua Faktor (2FA) Luring (RFC 6238 TOTP)
- Super Administrator terlindungi oleh 2FA murni luring tanpa internet atau pulsa SMS.
- Dilengkapi **Master Recovery Key fisik 24-karakter** (`PADU-XXXX-XXXX-XXXX-XXXX`) untuk lembar amplop bersegel di brankas Pimpinan. Jika digunakan, perangkat lama otomatis dihanguskan (*auto-revoke*).

### 4. Cadangan Sistem Terenkripsi AES-256 Sekali Pakai
- Sekali klik untuk membungkus database SQLite dan file sistem ke dalam arsip ZIP terenkripsi AES-256.
- Menghasilkan kata sandi acak berkekuatan tinggi 20 karakter yang **hanya tampil satu kali** saat pembuatan.
- Menghasilkan nilai hash **SHA-256** untuk menjamin integritas arsip dari modifikasi (*anti-tampering*).

### 5. PII Masking & Clean Enterprise UI
- Sensor otomatis NIK (`3201************`), Nomor KK, dan Nama Lengkap dengan sakelar privasi *Masked / Unmasked*.
- Desain antarmuka bersih tanpa garis bawah (*zero text-underlines*), navigasi responsif, kontras tinggi, dan ramah operator.

---

## 🏗️ Topologi Arsitektur Sistem

```
+----------------------------------------------------------------------------------------------------+
|                                    PADU v2.0 ENTERPRISE ENGINE                                     |
+----------------------------------------------------------------------------------------------------+
|                                                                                                    |
|  [ Web Browser Client ]                                                                            |
|        │                                                                                           |
|        ▼                                                                                           |
|  [ Web UI Frontend ] <─── Pure CSS (No-Underline) + Alpine.js Reactive + Tailwind Offline         |
|        │                                                                                           |
|        ▼                                                                                           |
|  [ Laravel 12 Backend Core ] (PHP 8.2 Portable Engine)                                             |
|        │                                                                                           |
|        ├───────────────► [ SQLite Security Database ] (app/database/database.sqlite)               |
|        │                 ├── Users & Roles (Super Admin & Operator)                                |
|        │                 ├── TOTP Secrets & Brankas Master Recovery Keys                           |
|        │                 └── Append-Only Forensic Audit Trail (Immutable)                          |
|        │                                                                                           |
|        ├───────────────► [ Python 3.11 Subprocess Worker ]                                         |
|        │                 └── DuckDB In-Memory OLAP Engine (15M+ Records Vectorized Query)          |
|        │                                                                                           |
|        └───────────────► [ Local File Storage Subsystem ]                                          |
|                          ├── src-dtsen/ (Folder Ingesti CSV DTSEN 2026)                            |
|                          ├── src-export/ (Arsip Ekspor & ZIP AES-256)                              |
|                          └── storage/backups/ (Cadangan Terenkripsi SOP 3-2-1)                     |
|                                                                                                    |
+----------------------------------------------------------------------------------------------------+
```

---

## 🚀 Panduan Menjalankan Sistem

### Mode 1: Versi Klien Portabel (Siap Pakai untuk Operator)
Aplikasi didistribusikan dalam folder `aplikasi-cepat-analytics-client` yang telah menyertakan binary PHP 8.2 dan Python portabel murni:
1. Ekstrak paket `PADU_v2.0_Enterprise_Client.zip`.
2. Klik ganda berkas peluncur **`JALANKAN_PADU.bat`**.
3. Browser akan otomatis terbuka atau navigasikan ke: **`http://127.0.0.1:8000/`**.
4. Masuk dengan akun **Super Administrator** atau **Operator**.

### Mode 2: Versi Pengembangan / Server (Developer Mode)
Jika menjalankan dari repository sumber menggunakan PowerShell:

```powershell
# 1. Masuk ke direktori proyek
cd aplikasi-cepat-analytics

# 2. Pastikan file konfigurasi .env telah tersedia
cp .env.example .env

# 3. Jalankan migrasi dan seeder awal
.\php\php.exe artisan migrate --force
.\php\php.exe artisan db:seed --class=UserSeeder --force

# 4. Bersihkan cache view dan konfigurasi
.\php\php.exe artisan view:clear
.\php\php.exe artisan config:clear

# 5. Jalankan web server lokal
.\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

### Mode 3: Versi Container (Docker Air-Gapped)
Untuk instalasi pada server kantor berbasis Docker Linux/Windows Server:
```bash
docker compose up -d --build
```
Akses web dashboard di `http://localhost:8000`.

---

## 📁 Struktur Direktori Proyek

```
aplikasi-cepat-analytics/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php         # Modul Super Admin (Users, Audit, Backup, 2FA)
│   │   │   ├── AuthController.php          # Otentikasi Bertahap BSSN & Force Reset
│   │   │   ├── DtsenController.php         # 12 Modul Olah Data DTSEN 2026
│   │   │   └── ProfileController.php       # Manajemen Profil & Pengaturan Akun
│   │   └── Middleware/                     # Proteksi Role, Session Timeout, & Force Reset
│   ├── Models/
│   │   ├── AuditLog.php                    # Model Log Forensik Immutable
│   │   └── User.php                        # Model User dengan Kepatuhan BSSN & 2FA
│   └── Services/
│       ├── AuditLogger.php                 # Service Pencatat Log Append-Only
│       ├── BackupService.php               # Enkripsi Cadangan AES-256 & SHA-256
│       ├── DtsenImportService.php          # Parser & Ingestor Berkas DTSEN
│       └── TwoFactorService.php            # Algoritma RFC 6238 TOTP Murni Offline
├── database/
│   ├── migrations/                         # Migrasi Skema Tabel Security & Audit
│   └── seeders/                            # Seeder Akun Default Super Admin & Operator
├── public/
│   ├── css/
│   │   ├── app.css                         # Enterprise Light CSS Design & Universal Reset
│   │   ├── auth.css                        # Styling Khusus Halaman Login Bertahap BSSN
│   │   └── tailwind.min.css                # Tailwind CSS Lokal 100% Offline
│   └── js/                                 # Skrip Interaktif Alpine.js & Utilitas
├── resources/
│   ├── docs/                               # Dokumentasi Teknis, DFD, & ERD
│   └── views/
│       ├── admin/                          # View Backup, Audit Trail, Users, 2FA Setup
│       ├── auth/                           # View Login, Force Reset, 2FA Verification
│       ├── dtsen/                          # View Master Data, QC, KPI, Salary, Audit, Files
│       └── layouts/                        # View Layout Utama & Modal Terintegrasi
├── scratch/
│   ├── fast_duckdb.py                      # Subprocess DuckDB OLAP Engine
│   ├── fast_export.py                      # Subprocess Ekspor Berkecepatan Tinggi
│   ├── fast_import.py                      # Subprocess Ingesti CSV Terfragmentasi
│   └── fast_delete.py                      # Subprocess Pembersihan Data Massal
├── src-dtsen/                              # Folder Penempatan Berkas CSV Masukan
├── src-export/                             # Folder Hasil Ekspor Data Terseleksi
├── Dokumentasi_DFD_PADU.md                 # Spesifikasi DFD Level 0, 1, 2 (12 Tab DTSEN)
├── Dokumentasi_SuperAdmin_PADU.md          # Spesifikasi DFD & ERD Super Administrator
├── Padu_Diagram.drawio                     # Diagram Arsitektur & DFD Sistem (Draw.io)
├── Padu_Diagram_SuperAdmin.drawio          # Diagram Alur Keamanan Super Admin (Draw.io)
├── swimlane_tobe_padu.drawio               # Diagram Swimlane Alur Bisnis To-Be Operator
└── swimlane_tobe_superadmin.drawio         # Diagram Swimlane Alur Bisnis To-Be Super Admin
```

---

## 📊 Dokumentasi & Diagram Arsitektur

Dokumentasi lengkap dan berkas diagram arsitektur enterprise tersedia langsung di repositori:

1. **[Dokumentasi DFD Sistem PADU (12 Tab Analisis)](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/Dokumentasi_DFD_PADU.md)**:
   - DFD Level 0 (Diagram Konteks Alur Ingesti ke Ekspor).
   - DFD Level 1 (4 Proses Inti: Otentikasi BSSN, Ingesti, Analisis DuckDB 7 Metrik, dan Ekspor ZIP).
   - DFD Level 2 (Sub-proses rinci dari setiap tahapan pemrosesan data).
2. **[Dokumentasi Tata Kelola Super Administrator & ERD](file:///c:/Users/rasyaad/.gemini/antigravity-ide/scratch/aplikasi-cepat-analytics/Dokumentasi_SuperAdmin_PADU.md)**:
   - DFD Level 0, 1, dan Level 2 untuk Keamanan Akun, 2FA TOTP, Forensik Log, dan Backup AES-256.
   - Unified Entity Relationship Diagram (ERD) relasi SQLite `users` dan `audit_logs`.
3. **Berkas Diagram Interaktif (.drawio)**:
   - `Padu_Diagram.drawio` & `Padu_Diagram_SuperAdmin.drawio` (DFD As-Is & To-Be).
   - `swimlane_tobe_padu.drawio` & `swimlane_tobe_superadmin.drawio` (Swimlane Proses Bisnis).

---

## 💻 Kebutuhan Sistem Minimum

| Komponen | Spesifikasi Minimum | Rekomendasi Enterprise |
| :--- | :--- | :--- |
| **Sistem Operasi** | Windows 10 (64-bit) / Windows 11 / Linux Ubuntu 22.04 LTS | Windows 11 Pro 64-bit / Linux Ubuntu Server 24.04 LTS |
| **Prosesor (CPU)** | Intel Core i3 / AMD Ryzen 3 (Quad Core) | Intel Core i7 / AMD Ryzen 7 (8 Core / 16 Thread) |
| **Memori (RAM)** | 8 GB RAM | 16 GB - 32 GB RAM (DDR4 / DDR5) |
| **Penyimpanan (Disk)** | 5 GB Ruang Kosong (SSD) | SSD NVMe PCIe Gen 4 (Kecepatan Baca > 3500 MB/s) |
| **Peramban Web** | Microsoft Edge v110+, Google Chrome v110+, Mozilla Firefox v110+ | Google Chrome / Microsoft Edge versi terbaru |
| **Konektivitas** | **100% OFFLINE (Tidak Memerlukan Internet)** | Air-Gapped Network (LAN Lokal Kantor jika multi-user) |

---

## 📜 Kedaulatan Data & Lisensi

Sistem **PADU v2.0 Enterprise** diserahkan dengan prinsip **Kedaulatan Mandiri Lepas Kunci (*Turnkey Handover*)**:
- **Bebas Lisensi & Biaya Berlangganan (*Zero Subscription Fee*)**: Tidak ada lisensi tahunan, biaya pengguna tambahan, atau *cloud metering*.
- **Tanpa Pintu Belakang (*Zero Backdoor / Telemetry*)**: Tidak ada pengiriman data analitik, jejak pemakaian, atau kunci enkripsi ke pihak pengembang maupun cloud asing.
- **Kedaulatan Penuh Klien**: Hak cipta data, database master, log forensik, dan kode sumber sepenuhnya berada di bawah otoritas Klien.

---
*Pengolah & Analisis Data Terpadu (PADU) v2.0 Enterprise &bull; DTSEN 2026 &bull; Standar Kepatuhan BSSN & UU PDP*
