# UPDATE SELANJUTNYA: CATATAN LENGKAP & BLUEPRINT PENGEMBANGAN SISTEM
## PADU v2.0 Enterprise — Pengolah & Analisis Data Terpadu
### Dokumen Rekaman Kesepakatan, Arsitektur Jaringan, Mekanisme Autentikasi & Rencana Eksekusi

---

> [!NOTE]
> **Tujuan Dokumen Ini**: Berkas ini dibuat khusus untuk merekam secara permanen seluruh hasil diskusi, keputusan arsitektur, spesifikasi alur kerja baru, dan rencana langkah kerja teknis (*action plan*) yang telah disepakati bersama. Dokumen ini menjadi acuan mutlak untuk implementasi pada sesi pembaruan berikutnya (*next update*).

---

## DAFTAR ISI
1. [Ringkasan Keputusan Utama (Key Decisions)](#1-ringkasan-keputusan-utama-key-decisions)
2. [Topologi Arsitektur Jaringan (100% Offline On-Premise Server)](#2-topologi-arsitektur-jaringan-100-offline-on-premise-server)
3. [Mekanisme Autentikasi: One-Time Force Reset Password (Super Admin)](#3-mekanisme-autentikasi-one-time-force-reset-password-super-admin)
4. [Tata Cara Pencadangan Aplikasi & Data (Backup SOP)](#4-tata-cara-pencadangan-aplikasi--data-backup-sop)
5. [Protokol Pemulihan Bencana Fisik Murni (Amplop Bersegel)](#5-protokol-pemulihan-bencana-fisik-murni-amplop-bersegel)
6. [Mekanisme Pemulihan Lupa Password (Admin-Assisted Offline Reset)](#6-mekanisme-pemulihan-lupa-password-admin-assisted-offline-reset)
7. [Fitur Utama Analisis Data (Tetap Dipertahankan & Ditingkatkan)](#7-fitur-utama-analisis-data-tetap-dipertahankan--ditingkatkan)
8. [Checklist Berkas & Action Plan untuk Update v2.0](#8-checklist-berkas--action-plan-untuk-update-v20)

---

## 1. Ringkasan Keputusan Utama (Key Decisions)

Berdasarkan diskusi mendalam, berikut adalah butir-butir kesepakatan final:

| No | Komponen Sistem | Status Keputusan | Alasan & Rincian Teknis |
| :--- | :--- | :--- | :--- |
| **1** | **Modul Biometrik** | **DIHAPUS TOTAL** | Menghilangkan ketergantungan perangkat keras webcam/OpenCV, MediaPipe, loop pemantauan 0.2s anti-shoulder surfing, lock screen Windows `user32.dll`, dan penghapusan foto wajah acuan di RAM. Sistem murni menjadi aplikasi web berbasis otentikasi standar industri (*clean web app*). |
| **2** | **Alur Otentikasi** | **TWO-STEP EMAIL-FIRST LOGIN** | Langkah 1: Input Email. Langkah 2: Sistem mengidentifikasi role (Badge Super Admin/Operator), input kata sandi (min 15 karakter), dan percabangan khusus saat klik Lupa Sandi. |
| **3** | **Lupa Sandi Super Admin** | **2FA OFFLINE HP (GOOGLE AUTH)** | Saat lupa sandi, Bos memasukkan 6 digit token dari HP pribadinya. Terverifikasi cocok $\rightarrow$ langsung muncul pop-up ganti password baru (min 15 karakter). |
| **4** | **Cadangan 2FA & Ganti HP** | **KUNCI CETAK BERBEDA (AUTO-REVOKE)** | Kunci cadangan cetak **BERBEDA TOTAL** dari kunci di HP. Saat kunci cetak dimasukkan, sistem mendeteksi HP lama hilang/rusak $\rightarrow$ **langsung MENGHANGUSKAN kunci 2FA di HP lama**, menerbitkan QR Code baru yang berbeda untuk HP baru Bos, dan mencetak ulang kunci darurat baru. |
| **5** | **Password ZIP Folder Aplikasi** | **20 KARAKTER ACAK (AUTO-RESET)** | Berkas arsip aplikasi dikunci password 20 karakter acak tingkat tinggi. Begitu folder ZIP diekstrak/dibuka, sistem otomatis mereset password 20 karakter baru secara instan. |
| **6** | **Standar Panjang Password** | **MINIMAL 15 KARAKTER** | Kebijakan kata sandi dinaikkan menjadi minimal 15 karakter kombinasi kuat (huruf besar, kecil, angka, simbol) sesuai standar NIST SP 800-63B. |
| **7** | **Model Serah Terima** | **LEPAS KUNCI (ZERO-KNOWLEDGE)** | Setelah BAST, developer 100% lepas tangan tanpa backdoor. Kedaulatan kunci dan pengelolaan beralih penuh ke klien. |
| **8** | **Enkripsi Database** | **BERLAPIS (AES-256 LARAVEL)** | Kolom sensitif (NIK, Nama, Gaji, Alamat, Metadata) dienkripsi otomatis via Eloquent `encrypted` casting (AES-256-CBC) dan diproteksi ACL di level file. |
| **9** | **Pencadangan Sistem (Backup)** | **DUAL-METHOD BACKUP** | 1. One-Click Backup dari panel Super Admin (mengunduh berkas arsip `.padubak` terenkripsi).<br>2. Skrip otomatis offline `cadangkan_padu.bat` terjadwal di Windows Server. |
| **10** | **Lupa Sandi Operator** | **ADMIN-ASSISTED RESET** | Operator tidak bisa reset mandiri. Reset dilakukan oleh Super Admin via panel User Management. |
| **11** | **Developer Baru & Patch SOP** | **KODE BERSIH + DATA DUMMY** | Developer baru koding di laptopnya dengan data dummy (`APP_ENV=local`). Pembaruan dikemas jadi `update_patch.zip` dan diunggah via menu Super Admin tanpa merusak data asli atau hardware lock. |

---

## 2. Topologi Arsitektur Jaringan (100% Offline On-Premise Server)

### 2.1 Skema Koneksi Fisik & Jaringan

```
                     ┌─────────────────────────────────────────────────────────┐
                     │          1 UNIT KOMPUTER SERVER MANDIRI KANTOR          │
                     │                 (Host Aplikasi PADU)                    │
                     │                                                         │
                     │  1. Menjalankan skrip: 'jalankan_padu.bat'              │
                     │  2. Engine Web OLTP: Laravel 12 (Port 8000)             │
                     │  3. Engine Analitik OLAP: Python DuckDB                 │
                     │  4. Database Terpusat:                                  │
                     │     - database.sqlite (Akun, Hak Akses, Kamus Data)     │
                     │     - dataset.duckdb (13M+ Baris Vektor DTSEN)          │
                     └───────────────────────────▲─────────────────────────────┘
                                                 │
                             Kabel LAN Kantor / Router Wi-Fi Lokal
                                (100% Offline - Tanpa Akses Internet)
                                                 │
                ┌────────────────────────────────┴────────────────────────────────┐
                │                                                                 │
                ▼                                                                 ▼
┌──────────────────────────────────────┐                       ┌──────────────────────────────────────┐
│       LAPTOP PRIBADI BOS             │                       │       LAPTOP / PC PEGAWAI            │
│          (SUPER ADMIN)               │                       │            (OPERATOR)                │
│                                      │                       │                                      │
│ • Buka Google Chrome di laptopnya    │                       │ • Buka Google Chrome di laptopnya    │
│ • Akses: http://192.168.1.10:8000    │                       │ • Akses: http://192.168.1.10:8000    │
│ • Login Akun: admin@padu.go.id       │                       │ • Login Akun: operator@padu.go.id    │
│ • Hak Akses:                         │                       │ • Hak Akses:                         │
│   - Force Reset Password (Login 1x)  │                       │   - Tab 1: Data Mikro Terpadu        │
│   - Panel Library Metadata Bappenas  │                       │   - Tab 2: Data Statistik 7 Metrik   │
│   - Manajemen Pengguna / Reset Pass  │                       │   - Ekspor Paket Arsip ZIP           │
│   - Pemantauan Audit Trail Log       │                       │   - Read-Only Kualitas Data          │
└──────────────────────────────────────┘                       └──────────────────────────────────────┘
```

### 2.2 Keunggulan Topologi Ini:
1. **Keamanan Maksimal**: Database tidak pernah tersimpan di laptop pegawai yang rentan dibawa pulang atau hilang.
2. **Kompak & Hemat Biaya**: Tidak perlu membeli server rak mahal, cukup 1 PC desktop kantor biasa yang dijadikan host.
3. **Pembaruan Seketika (*Real-Time Synchronization*)**: Begitu Bos memperbarui kamus label atau aturan data di panel Super Admin, detik itu juga seluruh layar pegawai langsung menampilkan data terbaru tanpa perlu colok flashdisk.

---

## 3. Mekanisme Autentikasi: One-Time Force Reset Password (Super Admin)

### 3.1 Skema Database (`users.metadata`)
Pengecekan status login pertama disimpan pada kolom JSON `metadata` di tabel `users`:
```json
{
  "first_login": true,
  "password_reset_required": true,
  "password_last_changed_at": null,
  "created_by": "developer_seeder"
}
```

### 3.2 Diagram Alur Langkah Demi Langkah (*Step-by-Step Flow*)

```mermaid
flowchart TD
    A([1. Bos Buka Browser Laptop & Akses /login]) --> B[2. Input: admin@padu.go.id & Password Default]
    B --> C{3. Verifikasi Hash Kredensial di Database}
    
    C -->|Kredensial Salah| Err[Tampilkan Peringatan: Email / Password Salah]
    C -->|Kredensial Cocok| D{4. Evaluasi Role Pengguna}
    
    D -->|Role == 'operator'| DashOp([Langsung Masuk Dashboard Analisis Data])
    D -->|Role == 'super_admin'| E{5. Baca users.metadata: is_first_login == true?}
    
    E -->|FALSE (Login ke-2, ke-3, dst)| PanelAdmin([Langsung Masuk Panel Library Metadata])
    
    E -->|TRUE (Hanya 1x Pertama Kali)| F[6. Sesi Ditahan Sementara & Redirect Paksa ke /auth/force-change-password]
    F --> G[7. Tampil Form: Password Baru & Konfirmasi Password]
    G --> H{8. Validasi Password: Min 15 Karakter & Cocok?}
    
    H -->|Tidak Sesuai| G
    H -->|Valid| I[9. Hash Password Baru Bcrypt<br/>Simpan ke users.password<br/>Update metadata: first_login = false<br/>Catat password_last_changed_at]
    
    I --> J[10. Catat Jejak ke Audit Trail Log]
    J --> PanelAdmin

    classDef proc fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef warn fill:#fef08a,stroke:#ca8a04,stroke-width:2px,color:#713f12;
    classDef succ fill:#bbf7d0,stroke:#16a34a,stroke-width:2px,color:#14532d;
    classDef err fill:#fecaca,stroke:#dc2626,stroke-width:2px,color:#991b1b;
    class A,B,C,D,E,H proc;
    class F,G warn;
    class I,J,PanelAdmin,DashOp succ;
    class Err err;
```

### 3.3 Penjelasan Detail Alur:
1. **Langkah 1 (Serah Terima Developer ke Bos)**:
   - Developer menyerahkan aplikasi dengan akun awal bawaan: `admin@padu.go.id` / `Admin123!`.
2. **Langkah 2 (Pendeteksian Sistem)**:
   - Saat Bos login pertama kali, sistem mendeteksi `role == 'super_admin'` DAN `metadata.first_login == true`.
3. **Langkah 3 (Pengalihan Paksa / *Force Redirect*)**:
   - Sistem **menolak akses** ke halaman admin dan secara otomatis mengalihkan browser ke `/auth/force-change-password`.
   - Muncul instruksi: *"Demi alasan keamanan, Anda diwajibkan mengganti kata sandi bawaan sistem pada saat pertama kali login (minimal 15 karakter kompleks)."*
4. **Langkah 4 (Penyimpanan Password Baru Pribadi)**:
   - Bos mengetik kata sandi barunya yang rahasia (misal: `KombinasiAmanPADU2026!#` - 23 karakter).
   - Sistem mengenkripsi kata sandi tersebut dengan algoritma Bcrypt.
   - Kolom `metadata` diperbarui: `"first_login": false`.
5. **Langkah 5 (Login Hari-Hari Berikutnya)**:
   - Untuk login ke-2 dan seterusnya, karena `first_login` sudah bernilai `false`, Bos **tidak akan pernah diganggu lagi** oleh formulir ganti kata sandi dan langsung masuk ke halaman utama.

---

## 4. Mekanisme Pemulihan Lupa Password (Admin-Assisted Offline Reset)

Karena sistem beroperasi di lingkungan jaringan tertutup tanpa internet (tidak bisa mengirim tautan email atau SMS), pemulihan kata sandi dilakukan secara mandiri melalui wewenang Super Admin:

```
[Pegawai / Operator Lupa Kata Sandi]
              │
              ▼ (1. Melapor langsung ke Super Admin di kantor)
[Super Admin Login ke Panel Pengelolaan Pengguna]
              │
              ▼ (2. Cari akun pegawai tersebut -> Klik tombol 'Reset Password')
[Sistem Server Mengeksekusi 2 Aksi Otomatis]
              ├── A. Memberikan kata sandi sementara acak (misal: 'ResetPass123!')
              └── B. Mengubah kembali metadata akun: { "first_login": true }
              │
              ▼ (3. Bos memberikan kata sandi sementara 'ResetPass123!' ke pegawai)
[Pegawai Login di Laptopnya Pakai 'ResetPass123!']
              │
              ▼ (4. Sistem mendeteksi first_login == true)
[Layar Pegawai Otomatis Dipaksa Membuat Kata Sandi Baru Miliknya Sendiri]
              │
              ▼ (5. Status metadata kembali menjadi first_login: false)
[Pegawai Berhasil Masuk Dashboard & Kata Sandi Kembali Rahasia]
```

> **Catatan Darurat Developer**: Jika akun Super Admin yang lupa kata sandi, disediakan skrip terminal darurat di server kantor:
> `php artisan padu:reset-superadmin` $\rightarrow$ mengembalikan akun Bos ke default awal dan mengubah `first_login: true`.

---

## 5. Blueprint Teknis Tambahan: Offline Two-Factor Authentication (2FA TOTP)

Sebagai fitur peningkatan keamanan masa depan, sistem disiapkan untuk mendukung **Two-Factor Authentication (2FA)** berbasis standar **RFC 6238 (TOTP - Time-based One-Time Password)** yang kompatibel dengan aplikasi smartphone gratis (**Google Authenticator, Microsoft Authenticator, FreeOTP**).

### 5.1 Mengapa Bisa Berjalan 100% Offline Tanpa Pulsa & Tanpa Internet?
Mekanisme ini tidak menggunakan SMS OTP ataupun email OTP. Cara kerjanya murni matematis:
1. Server komputer dan Smartphone memiliki jam waktu yang cocok/sinkron.
2. Server dan Smartphone sama-sama menyimpan satu *Kunci Rahasia Bersama* (*Shared Secret Key*, misal: `JBSWY3DPEHPK3PXP`).
3. Setiap interval 30 detik, rumus matematis menghitung 6 digit angka:
   $$\text{Token 6 Digit} = \text{Truncate}(\text{HMAC-SHA1}(\text{SecretKey}, \lfloor\text{Timestamp} / 30\rfloor))$$
4. Smartphone menghitung secara offline di saku Bos. Server menghitung secara offline di laptop kantor. Keduanya menghasilkan 6 angka yang **pasti sama persis pada detik yang sama**.

```
    [KOMPUTER SERVER (OFFLINE)]                    [SMARTPHONE BOS (OFFLINE)]
  Punya Kunci: 'JBSWY3DPEHPK3PXP'                 Punya Kunci: 'JBSWY3DPEHPK3PXP'
  Waktu: 14:20:15                                 Waktu: 14:20:15
         │ (Hitung HMAC-SHA1)                            │ (Google Authenticator)
         ▼                                               ▼
  Token: 482 910                                  Token: 482 910
         │                                               │
         └───────────── COCOK! AUTENTIKASI DITERIMA ─────┘
```

### 5.2 Alur Penyiapan (Setup Pertama Kali):
1. Super Admin membuka menu *Profil Keamanan* $\rightarrow$ klik **"Aktifkan 2FA"**.
2. Server merender gambar **QR Code lokal** di layar (berisi kunci rahasia acak Base32).
3. Bos membuka aplikasi Google Authenticator di HP $\rightarrow$ tekan tanda `+` $\rightarrow$ scan QR Code di layar.
4. Sistem mencetak/menampilkan **8 Recovery Backup Codes** darurat (misal `B4E2-99A1`) untuk disimpan di catatan aman.

### 5.3 Alur Login Harian Menggunakan 2FA:
1. Masukkan Email & Kata Sandi di browser laptop.
2. Muncul formulir: *"Masukkan 6 Digit Token dari Google Authenticator"*.
3. Bos membuka HP $\rightarrow$ membaca 6 digit angka yang sedang berputar $\rightarrow$ mengetik angka tersebut di laptop.
4. Sistem mencocokkan hitungan matematika $\rightarrow$ Login berhasil.
*(Jika HP ketinggalan atau baterai habis, Bos bisa memasukkan 1 dari 8 Recovery Codes darurat)*.

### 5.4 Skema Database Pendukung 2FA:
```sql
ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL;
ALTER TABLE users ADD COLUMN two_factor_recovery_codes JSON NULL;
ALTER TABLE users ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL;
```

---

## 6. Fitur Utama Analisis Data DTSEN (Tetap Dipertahankan & Ditingkatkan)

Seluruh keunggulan komputasi data yang telah dibangun sebelumnya tetap menjadi pondasi utama sistem:

### 6.1 Ingesti Cepat & Standarisasi Vektor BPS
- Mesin pembaca *chunking* PyArrow berkemampuan streaming **> 1,5 Juta baris per detik**.
- Kamus sinonim 48 variabel resmi individu dan 52 variabel keluarga (KK).
- Validasi integritas kualitas data otomatis: *Valid*, *Warning* (field opsional kosong), dan *Critical* (NIK cacat, desil di luar 1-10).

### 6.2 Dual-Module Views
1. **Tab 1: Data Mikro Terpadu**:
   - Integrasi data kepala keluarga dan anggota dalam 1 baris terpadu.
   - **Dynamic PII Masking Transformer**: Sensor otomatis NIK (`3201************`), inisial nama (`B*** S******`), sensor parsial nominal gaji dan alamat RT/RW.
   - **Transliterasi Foreign Key**: Penerjemahan kode numerik BPS menjadi label deskriptif teks resmi (*Air Kemasan*, *Marmer/Granit*, *Milik Sendiri*) via tabel referensi metadata.
2. **Tab 2: Data Statistik Regional**:
   - Agregasi multi-wilayah (Kabupaten/Kota, Kecamatan, Kelurahan/Desa).
   - **Kalkulasi 7 Metrik Statistik Dinamis**: `COUNT`, `SUM`, `AVG`, `MIN`, `MAX`, `MEDIAN`, dan `MODUS`.

### 6.3 Dedicated Library Management (Panel Super Admin)
- Antarmuka CRUD kamus variabel resmi Bappenas/BPS.
- **Non-Destructive Update**: Penambahan opsi kode baru tanpa mengubah skema tabel fisik (*No DDL*).
- **In-Memory Hot-Reload**: Pembaruan langsung aktif di memori DuckDB tanpa perlu restart server.
- **Audit Trail Log**: Pencatatan riwayat revisi kamus secara mendalam (`library_audit_logs`).

### 6.4 Ekspor Arsip Berkas ZIP Resmi
- Kompresi tingkat tinggi DEFLATE untuk menghasilkan paket ZIP:
  1. `data_dtsen.csv` (data bersih).
  2. `audit_error.csv` (catatan anomali).
  3. `metadata.txt` (manifest integritas berkas & checksum hash SHA-256).

---

## 7. Checklist Berkas & Action Plan untuk Update Selanjutnya

Berikut adalah panduan eksekusi yang akan langsung dijalankan saat Anda menginstruksikan pembaruan berkas:

### Tahap 1: Pembaruan Berkas Diagram Draw.io (`.drawio`)
- [ ] **`Padu_Diagram.drawio` (Berkas Utama - Standar Operator)**:
  - Tab TO-BE Level 0: Hapus Entitas Kamera & OS Windows. Hubungkan Operator Data ke Sistem 0.0.
  - Tab TO-BE Level 1: Hapus proses Biometrik (1.0), Guard Loop (4.0), dan Purge (6.0). Restrukturisasi menjadi 4 proses sekuensial bersih (1.0 Autentikasi, 2.0 Ingesti, 3.0 Analitik Dual-Modul & 7 Metrik, 4.0 Ekspor ZIP).
  - Tab TO-BE Level 2: Bersihkan sub-proses biometrik.
  - Tab ERD: Hapus data store `sesi_wajah_aktif.jpg`.
- [ ] **`Padu_Diagram_SuperAdmin.drawio` (Berkas Enterprise Super Admin)**:
  - Tab TO-BE Level 0: Tampilkan 2 Entitas Manusia: Operator Data dan Super Admin.
  - Tab TO-BE Level 1: Tampilkan 5 proses inti (1.0 Autentikasi & First-Login Reset, 2.0 Ingesti, 3.0 Analitik Dual-Modul, 4.0 Ekspor ZIP, 5.0 Manajemen Library Metadata).
  - Tab TO-BE Level 2 P1.0: Buat sub-proses dekomposisi baru untuk First-Login Reset Password Super Admin (1.1 Form Login, 1.2 Hash Validator, 1.3 Check `metadata.first_login`, 1.4 Force Reset Form, 1.5 Update DB `first_login=false`, 1.6 Session Dispatcher).
  - Tab ERD RBAC: Pastikan tabel `users` memiliki kolom `role`, `metadata` JSON, dan kolom persiapan `two_factor_*`.
- [ ] **`swimlane_tobe_padu.drawio`**:
  - Hapus jalur Python AI Biometric & OS Lockout. Sesuaikan menjadi 4 jalur murni analitik.
- [ ] **`swimlane_tobe_superadmin.drawio`**:
  - Hapus jalur Biometrik. Tambahkan percabangan jalur Super Admin:
    `[Login] -> [Check first_login == true?] -> Ya (Force Reset Password 1x) -> [Panel Library Admin]`.
  - Pastikan dimensi kanvas tetap terkunci pas 1 halaman (`pageWidth="1740" pageHeight="1510"`) agar saat diekspor ke PDF tidak terpotong.

### Tahap 2: Pembaruan Berkas Dokumentasi Markdown (`.md`)
- [ ] **`Dokumentasi_DFD_PADU.md`**: Update diagram Mermaid Level 0, Level 1, dan Level 2 tanpa elemen biometrik.
- [ ] **`Dokumentasi_SuperAdmin_PADU.md`**: Lengkapi spesifikasi alur First-Login Reset Password dan integrasi RBAC.

---

> **Status Dokumen**: ✅ **Lengkap, Tervalidasi, dan Siap Eksekusi**.  
> Kapanpun Anda siap melakukan pembaruan berkas diagram, cukup berikan perintah: *"Mulai eksekusi update selanjutnya"*.
