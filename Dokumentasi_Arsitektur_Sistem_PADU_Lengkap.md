# DOKUMENTASI LENGKAP & SPESIFIKASI TEKNIS SISTEM
# PADU v1.02 — Pengolah & Analisis Data Terpadu
### DTSEN 2026 Analytics Engine (Standalone 100% Offline Local Network Architecture)

---

## 1. Ringkasan Eksekutif & Karakteristik Sistem

**PADU v1.02** adalah sistem analitik data skala besar (*Big Data Analytics*) yang dirancang khusus untuk memproses, membersihkan, menstandardisasi, dan menyajikan **13 Juta+ baris data DTSEN 2026** secara instan dengan waktu respons kueri di bawah **0,05 detik**.

### Prinsip Operasional Utama:
1. **100% Offline & Mandiri (*Air-Gapped / Zero-Internet*)**:
   Sistem tidak memerlukan kuota data, modem, ataupun koneksi internet publik. Hal ini menjamin data kependudukan sensitif (NIK, Nomor KK, Penghasilan, dan Kondisi Kemiskinan) tidak pernah bocor keluar jaringan kantor.
2. **Topologi *On-Premise Dedicated Local Server* (Jaringan LAN / Wi-Fi Kantor)**:
   Aplikasi dan database utama ditempatkan pada 1 unit komputer kantor (PC Host). Seluruh pemangku kepentingan (Super Admin / Bos dan Operator / Pegawai) dapat mengakses sistem secara fleksibel menggunakan browser di laptop masing-masing selama berada dalam satu jaringan lokal (LAN/Wi-Fi kantor).
3. **Dual-Engine Architecture (Laravel 12 + Python DuckDB)**:
   - **Laravel 12 (OLTP)**: Menangani manajemen sesi, otentikasi RBAC, antarmuka web modern, validasi bisnis, dan log audit.
   - **Python DuckDB (OLAP)**: Menangani *vectorized columnar slicing*, pembacaan *chunking* PyArrow berkemampuan >1,5 juta baris/detik, serta kalkulasi agregasi statistik regional secara paralel.

---

## 2. Topologi Jaringan & Arsitektur Distribusi (100% Offline)

```
                            ┌─────────────────────────────────────────────────────────┐
                            │               KOMPUTER SERVER MANDIRI KANTOR            │
                            │           (Menjalankan jalankan_padu.bat)               │
                            │                                                         │
                            │   ┌───────────────────────┐   ┌─────────────────────┐   │
                            │   │  LARAVEL 12 WEB CORE  │   │  PYTHON DUCKDB OLAP │   │
                            │   │  (Port 8000 Web App)  │   │  (dataset.duckdb)   │   │
                            │   └───────────┬───────────┘   └──────────┬──────────┘   │
                            │               │                          │              │
                            │   ┌───────────▼───────────┐              │              │
                            │   │ SQLite (database.db)  │◄─────────────┘              │
                            │   │ (users, kamus, audit) │  (Live Memory Hot-Reload)   │
                            │   └───────────────────────┘                             │
                            └───────────────────────────▲─────────────────────────────┘
                                                        │
                                    Jaringan Wi-Fi / LAN Kantor (Offline)
                                                        │
                         ┌──────────────────────────────┴──────────────────────────────┐
                         │                                                             │
                         ▼                                                             ▼
         ┌───────────────────────────────┐                             ┌───────────────────────────────┐
         │     LAPTOP SUPER ADMIN        │                             │        LAPTOP OPERATOR        │
         │         (BOS / OPD)           │                             │           (PEGAWAI)           │
         │  http://192.168.1.10:8000     │                             │  http://192.168.1.10:8000     │
         │  Role: super_admin            │                             │  Role: operator               │
         │  - First-Login Force Reset    │                             │  - Tab Data Mikro Terpadu     │
         │  - Panel Library Metadata     │                             │  - Tab Data Statistik 7 Metrik│
         │  - Reset Password Pegawai     │                             │  - Ekspor Paket Arsip ZIP     │
         └───────────────────────────────┘                             └───────────────────────────────┘
```

---

## 3. Tata Kelola Pengguna & Mekanisme First-Login Force Reset

### 3.1 Role-Based Access Control (RBAC)
Sistem membedakan dua tingkatan peran (*role*):
1. **Super Admin (`super_admin`)**: Memiliki hak istimewa untuk mengelola kamus referensi, mengubah definisi variabel resmi Bappenas/BPS, mereset password operator, dan melihat log audit sistem.
2. **Operator (`operator`)**: Memiliki hak operasional analitik untuk mengunggah dataset mentah, memfilter data, melihat data mikro (ter-masking PII), menganalisis 7 metrik wilayah, dan mengunduh berkas ZIP.

### 3.2 Spesifikasi *One-Time Force Password Reset* (via Metadata)
Sesuai dengan praktik keamanan enterprise (seperti WordPress dan AWS IAM), pengguna baru yang menggunakan kata sandi awal default **wajib** mengubah kata sandi pada login pertama mereka.

#### A. Struktur Data pada Tabel `users`:
Status login pertama disimpan secara fleksibel pada kolom JSON `metadata`:
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'operator', -- 'super_admin' | 'operator'
    metadata JSON DEFAULT '{"first_login": true, "password_reset_required": true}',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### B. Diagram Alur Kerja Autentikasi & Reset Password Pertama Kali:

```mermaid
flowchart TD
    Start([1. Pengguna Buka Browser & Akses /login]) --> InputCred[2. Masukkan Email & Password Bawaan]
    InputCred --> CheckHash{3. Verifikasi Hash Kredensial di DB}
    
    CheckHash -->|Salah| Reject[Tampilkan Peringatan: Kredensial Tidak Valid]
    CheckHash -->|Valid| ReadMeta[4. Baca Atribut users.metadata]
    
    ReadMeta --> CheckFirstLogin{5. Apakah first_login == true?}
    
    CheckFirstLogin -->|TRUE (Login Pertama)| ForceRedirect[6. Redirect Paksa ke /auth/force-change-password]
    ForceRedirect --> InputNewPass[7. Pengguna Masukkan Kata Sandi Baru & Konfirmasi]
    InputNewPass --> ValidateStrength{8. Validasi: Min 8 Karakter & Cocok?}
    
    ValidateStrength -->|Tidak| InputNewPass
    ValidateStrength -->|Valid| SaveNewPass[9. Hash Bcrypt Password Baru<br/>Update metadata: first_login = false<br/>Catat password_changed_at]
    
    SaveNewPass --> RouteSession[10. Sesi Diotentikasi Penuh]
    CheckFirstLogin -->|FALSE (Login ke-2 dst)| RouteSession
    
    RouteSession --> CheckRole{11. Evaluasi Role Pengguna}
    CheckRole -->|super_admin| AdminPanel([12. Buka Halaman Khusus Manajemen Library])
    CheckRole -->|operator| OperatorDash([13. Buka Dashboard Analisis: Mikro & Statistik])

    classDef proc fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef warn fill:#fef08a,stroke:#ca8a04,stroke-width:2px,color:#713f12;
    classDef succ fill:#bbf7d0,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef err fill:#fecaca,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    class InputCred,CheckHash,ReadMeta,CheckFirstLogin,CheckRole proc;
    class ForceRedirect,InputNewPass,ValidateStrength warn;
    class SaveNewPass,RouteSession,AdminPanel,OperatorDash succ;
    class Reject err;
```

#### C. Alur Pemulihan Lupa Password (Admin-Assisted Reset 100% Offline):
Jika operator/pegawai lupa kata sandi di kemudian hari:
1. Operator melapor ke Super Admin secara langsung.
2. Super Admin membuka menu **Manajemen Pengguna** di panel admin, lalu mengeklik tombol **Reset Password** pada baris akun operator tersebut.
3. Sistem menetapkan password acak sementara (misal: `Sementara2026!`) dan secara otomatis memperbarui kembali atribut `users.metadata`:
   ```json
   { "first_login": true, "password_reset_required": true }
   ```
4. Saat operator login kembali dengan kata sandi sementara tersebut, sistem kembali mendeteksi `first_login == true` dan **memaksa operator membuat kata sandi baru pribadinya sendiri**.
5. Dengan metode ini, kerahasiaan kata sandi operator tetap terjamin (Super Admin tidak mengetahui kata sandi baru operator).

---

## 4. Modul Fungsional Utama PADU v1.02

Sistem menyajikan pemisahan fitur yang jelas antara operasional harian data dan tata kelola master referensi:

### 4.1 Modul 1: Tab Data Mikro Terpadu (Level Individu & Keluarga)
- **Integrasi Master-Detail**: Menggabungkan 52 variabel data rumah tangga (`keluargas`) dengan 48 variabel anggota keluarga (`individus`) dalam satu tampilan tabel kolumnar yang responsif.
- **Dynamic PII Masking Transformer**:
  Menyembunyikan informasi identitas pribadi sensitif secara otomatis pada antarmuka web:
  - **NIK**: Disensor menjadi `3201************` (hanya menampilkan 4 digit awal).
  - **Nama Lengkap**: Disensor menjadi `B*** S******` (hanya menampilkan inisial pertama tiap kata).
  - **Gaji / Penghasilan**: Disensor parsial menjadi `Rp 8.xxx.xxx`.
  - **Alamat / RT / RW**: Disensor sebagian untuk perlindungan privasi.
- **Transliterasi Foreign Key (Penerjemahan Kode ke Label)**:
  Menerjemahkan kode numerik mentah BPS menjadi label teks deskriptif melalui tabel referensi metadata secara instan:
  - `sumber_air_minum_utama = "01"` $\rightarrow$ **"Air Kemasan Bermerk"**
  - `jenis_lantai_terluas = "01"` $\rightarrow$ **"Marmer / Granit"**
  - `status_kepemilikan_rumah = "1"` $\rightarrow$ **"Milik Sendiri"**

### 4.2 Modul 2: Tab Data Statistik Regional (Agregasi 7 Metrik)
Menyajikan rekapitulasi data agregat multi-tingkat (Kabupaten/Kota, Kecamatan, dan Kelurahan/Desa) dengan kalkulasi **7 Metrik Statistik Dinamis**:
1. **COUNT**: Jumlah Total Jiwa dan Kepala Keluarga.
2. **SUM**: Akumulasi Nominal (Total Estimasi Bansos / Penghasilan Wilayah).
3. **AVG (Average)**: Rata-rata Usia, Rata-rata Penghasilan per Kapita.
4. **MIN**: Nilai Terendah pada kelompok desil/penghasilan.
5. **MAX**: Nilai Tertinggi pada kelompok desil/penghasilan.
6. **MEDIAN**: Nilai Titik Tengah sebaran data desil/penghasilan wilayah.
7. **MODUS**: Nilai / Kategori yang paling sering muncul (misal: jenis pekerjaan dominan atau kategori sanitasi terbanyak).

### 4.3 Modul 3: Manajemen & Pembaruan Library Metadata (Khusus Super Admin)
- Halaman web khusus bagi Super Admin untuk menambah, mengubah, atau memperbarui kamus kode referensi dan pemetaan sinonim kolom BPS.
- **Prinsip Non-Destructive**: Pembaruan kamus dilakukan **tanpa mengubah skema tabel fisik database (*No ALTER TABLE / DDL*)**, sehingga tidak ada risiko kerusakan pada 13 Juta+ baris data yang sudah ada.
- **Atomic Versioning & Audit Trail**: Setiap perubahan diberi nomor versi inkremental (`version = version + 1`) dan dicatat detail pengubahnya pada tabel `library_audit_logs`.
- **In-Memory Hot-Reload**: Saat Super Admin mengeklik simpan, perubahan langsung disuntikkan ke memori runtime DuckDB tanpa perlu me-restart komputer server.

### 4.4 Modul 4: Pengelolaan Ekspor Berkas Arsip ZIP
- Mengemas 3 berkas resmi secara otomatis dengan algoritma kompresi DEFLATE berkecepatan tinggi:
  1. `data_dtsen.csv`: Data bersih hasil filter.
  2. `audit_error.csv`: Log rincian baris data anomali (*Warning & Critical*).
  3. `metadata.txt`: Berkas manifest integritas berisi nama operator pengeksekusi, parameter filter aktif, stempel waktu ekspor, dan checksum hash SHA-256 berkas.

---

## 5. Arsitektur Data Relasional & Kolumnar (Dual-Storage)

```mermaid
erDiagram
    users ||--o{ library_audit_logs : "creates_audit"
    users ||--o{ metadata_libraries : "manages"
    metadata_libraries ||--o{ library_audit_logs : "tracked_by"
    keluargas ||--o{ individus : "has_members"
    referensi_kamus_data ||--o{ individus : "translates_labels"

    users {
        bigint id PK
        string name
        string email UK
        string password
        string role "ENUM('super_admin', 'operator')"
        json metadata "first_login, password_reset_required"
        timestamp created_at
        timestamp updated_at
    }

    metadata_libraries {
        bigint id PK
        string library_key UK "Contoh: transliterasi_fk, sinonim_bps"
        string library_name "Nama Display Kamus"
        string category "Perumahan, Sanitasi, Wilayah"
        json schema_definition "Definisi Label dan Opsi Pilihan"
        integer version "Nomor Versi Inkremental"
        boolean is_active "Status Keaktifan (1=Aktif)"
        bigint updated_by_user_id FK
        timestamp created_at
        timestamp updated_at
    }

    library_audit_logs {
        bigint id PK
        bigint user_id FK "ID Super Admin"
        bigint library_id FK "ID Library"
        string action "CREATE / UPDATE / ROLLBACK"
        json old_values "Snapshot Nilai Lama"
        json new_values "Snapshot Nilai Baru"
        string ip_address "IP Akses Klien"
        timestamp created_at
    }

    referensi_kamus_data {
        bigint id PK
        string nama_variabel IDX "nama kolom di individus/keluargas"
        string kode_nilai IDX "kode angka (01, 02, 1, 2)"
        string label_deskriptif "label teks resmi Bappenas/BPS"
        string kategori_variabel "Perumahan, Pendidikan, dsb"
        boolean is_active "1=Aktif, 0=Non-aktif"
    }

    keluargas {
        bigint id PK
        string nomor_kartu_keluarga UK "16 Digit Unik KK"
        string nama_kepala_keluarga
        integer desil_nasional IDX
        string kode_provinsi
        string kode_kabupaten_kota
        string status_kepemilikan_rumah
        string sumber_air_minum_utama
    }

    individus {
        bigint id PK
        string nomor_induk_kependudukan UK "16 Digit NIK"
        string nomor_kartu_keluarga FK
        string nama "Mendukung Masking PII"
        string jenis_kelamin
        integer usia
        decimal gaji_bulanan
        string quality_status IDX "Valid, Warning, Critical"
    }
```

---

## 6. Blueprint Tambahan: Implementasi Offline Two-Factor Authentication (2FA)

Jika instansi Anda ingin menambahkan lapisan keamanan tingkat tinggi berupa **Two-Factor Authentication (2FA)** tanpa menggunakan internet, sistem ini dapat mengimplementasikan **Time-Based One-Time Password (TOTP)** berstandar **RFC 6238** (kompatibel dengan aplikasi smartphone gratis seperti **Google Authenticator, Microsoft Authenticator, atau FreeOTP**).

### 6.1 Mengapa TOTP Bisa Bekerja 100% Offline Tanpa Pulsa & Tanpa Internet?
Mekanisme TOTP **sama sekali tidak mengirimkan SMS atau Email**. TOTP murni bekerja menggunakan **perhitungan matematika waktu (*timestamp*) dan sebuah kunci rahasia (*shared secret key*)**:
1. Server dan Smartphone Pengguna memiliki jam waktu yang sama (sinkron).
2. Setiap interval 30 detik, algoritma HMAC-SHA1 menghitung 6 digit kode unik:
   $$\text{Token 6 Digit} = \text{Truncate}(\text{HMAC-SHA1}(\text{SecretKey}, \lfloor\text{Timestamp} / 30\rfloor))$$
3. Smartphone pengguna menghitung kodenya sendiri secara offline. Server juga menghitung kodenya sendiri secara offline. Keduanya akan menghasilkan 6 digit angka yang **pasti identik**.

```
    [LAPTOP SERVER (OFFLINE)]                       [HP SUPER ADMIN (OFFLINE)]
  Punya Kunci: 'JBSWY3DPEHPK3PXP'                 Punya Kunci: 'JBSWY3DPEHPK3PXP'
  Jam: 14:20:15                                   Jam: 14:20:15
         │                                               │
         ▼ (Rumus HMAC-SHA1 30s)                         ▼ (Google Authenticator)
  Kode Server: 482 910                            Kode HP: 482 910
         │                                               │
         └───────────── Keduanya Cocok! ─────────────────┘
                      (Login Berhasil)
```

### 6.2 Alur Penyiapan (Setup 2FA Pertama Kali untuk Super Admin)

1. **Aktivasi di Panel Super Admin**:
   - Super Admin membuka menu *Profil Keamanan* $\rightarrow$ klik **"Aktifkan Autentikasi 2 Langkah (2FA)"**.
2. **Generate Kunci Rahasia Lokal**:
   - Sistem menghasilkan kunci acak Base32 16 karakter (misal: `JBSWY3DPEHPK3PXP`) dan merender gambar **QR Code lokal** menggunakan pustaka JavaScript offline (misal `qrcode.js`).
3. **Pindai QR Code via Smartphone**:
   - Super Admin membuka aplikasi Google Authenticator di HP (tanpa butuh koneksi internet pada HP).
   - Arahkan kamera HP ke QR Code di layar laptop.
   - Akun *PADU DTSEN (admin@padu.go.id)* otomatis terdaftar di Google Authenticator.
4. **Verifikasi & Konfirmasi**:
   - Super Admin memasukkan 6 digit kode yang tampil di HP untuk verifikasi pertama.
   - Jika valid, status 2FA pada akun Super Admin diaktifkan permanen.
5. **Penyimpanan Emergency Backup Codes**:
   - Sistem membuat 8 baris kode pemulihan darurat (*Recovery Codes*, misal: `A8F2-99B1`, `C3D4-55E6`).
   - Kode ini dicetak/disimpan oleh Bos jika sewaktu-waktu HP rusak atau tertinggal.

### 6.3 Alur Login Harian Menggunakan 2FA

```mermaid
flowchart TD
    A[1. Masukkan Email & Password] --> B{Validasi Kredensial}
    B -->|Gagal| Err[Login Ditolak]
    B -->|Sukses| C{Apakah Akun Mengaktifkan 2FA?}
    
    C -->|Tidak (Operator Biasa)| D[Langsung Masuk Dashboard]
    C -->|Ya (Super Admin)| E[Tampilkan Layar Masukkan Token 2FA]
    
    E --> F[Super Admin Buka Google Authenticator di HP]
    F --> G[Input 6 Digit Token yang Berputar Tiap 30 Detik]
    G --> H{Server Menghitung HMAC Offline Cocok?}
    
    H -->|Cocok| I([Autentikasi Sukses: Masuk Panel Super Admin])
    H -->|Salah / Kadaluarsa| J[Tampilkan Error: Token 2FA Tidak Valid]
    
    E -.->|Opsi Darurat (HP Hilang)| K[Gunakan 1 dari 8 Recovery Backup Codes]
    K --> I

    classDef proc fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef warn fill:#fef08a,stroke:#ca8a04,stroke-width:2px,color:#713f12;
    classDef succ fill:#bbf7d0,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef err fill:#fecaca,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    class A,B,C proc;
    class E,F,G,K warn;
    class D,I succ;
    class Err,J err;
```

### 6.4 Penambahan Skema Database untuk Mendukung 2FA
Cukup menambahkan 3 kolom pada tabel `users` (atau disimpan di dalam JSON `users.metadata`):
```sql
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes JSON NULL;
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL;
```

---

## 7. Rangkuman Panduan Serah Terima ke Klien / Bos

1. **Paket Berkas Aplikasi**:
   - Berikan folder proyek PADU dalam format `.zip` (misal `PADU_Server_v1.02.zip`).
2. **Instalasi & Menjalankan Aplikasi**:
   - Ekstrak berkas ZIP di satu PC kantor yang menjadi host.
   - Dobel-klik berkas `jalankan_padu.bat`.
   - Server lokal otomatis aktif di latar belakang.
3. **Akses dari Laptop Kerja**:
   - Pastikan laptop Bos dan Pegawai tersambung ke router Wi-Fi/LAN kantor yang sama.
   - Buka Google Chrome dan ketik alamat IP PC server: `http://[IP_SERVER]:8000`.
4. **Pengalaman Pertama Bos (Super Admin)**:
   - Login dengan akun awal `admin@padu.go.id` / `Admin123!`.
   - Sistem seketika meminta Bos membuat kata sandi baru miliknya sendiri.
   - Setelah selesai, Bos memegang kendali penuh atas sistem PADU DTSEN 2026.
