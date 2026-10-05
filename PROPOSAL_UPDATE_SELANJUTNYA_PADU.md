# DOKUMEN PROPOSAL TEKNIS PENGEMBANGAN & PEMBARUAN SISTEM
## SISTEM INFORMASI PADU (PENGOLAH & ANALISIS DATA TERPADU)
### Versi Dokumen: v2.0 Enterprise (Rilis Kedaulatan Mandiri Lepas Kunci — Terakreditasi Standar BSSN & UU PDP)
**Tanggal Pengajuan**: 24 September 2026 (Diperbarui: 27 September 2026)  
**Klasifikasi Dokumen**: Sangat Rahasia (*Strictly Confidential / Dokumen Internal Klien*)  
**Status**: Usulan Resmi Rencana Kerja Pembaruan Lengkap (*Full Work Proposal A - Z*)  
**Standar Kepatuhan**: Peraturan BSSN No. 4 Tahun 2021, Peraturan BSSN No. 11 Tahun 2024, Peraturan BSSN No. 10 Tahun 2020, Peraturan BSSN No. 8 Tahun 2020, dan UU No. 27 Tahun 2022 (UU PDP)

---

## 📑 DAFTAR ISI LENGKAP (A - Z)
1. [Bab 1: Latar Belakang, Urgensi & Prinsip Kedaulatan Sistem v2.0](#bab-1-latar-belakang-urgensi--prinsip-kedaulatan-sistem-v20)
2. [Bab 2: Tujuan, Sasaran Strategis & Landasan Kepatuhan Regulasi Siber](#bab-2-tujuan-sasaran-strategis--landasan-kepatuhan-regulasi-siber)
3. [Bab 3: Topologi Jaringan LAN Offline, Anti-Oper Folder & SOP Pemulihan Bencana Hardware (DRP)](#bab-3-topologi-jaringan-lan-offline-anti-oper-folder--sop-pemulihan-bencana-hardware-drp)
4. [Bab 4: Alur Otentikasi Bertahap, Sandi Min. 15 Karakter, Account Lockout & Manajemen Sesi](#bab-4-alur-otentikasi-bertahap-sandi-min-15-karakter-account-lockout--manajemen-sesi)
5. [Bab 5: Sistem Keamanan 2FA HP Cerdas, Hashing Kunci Darurat & Protokol Auto-Revoke](#bab-5-sistem-keamanan-2fa-hp-cerdas-hashing-kunci-darurat--protokol-auto-revoke)
6. [Bab 6: Enkripsi Database Berlapis (Layered Database Defense) & Standar Kriptografi BSSN](#bab-6-enkripsi-database-berlapis-layered-database-defense--standar-kriptografi-bssn)
7. [Bab 7: Standar Audit Trail, Forensik Digital & Pencatatan Log Kebal Manipulasi (Immutable Log)](#bab-7-standar-audit-trail-forensik-digital--pencatatan-log-kebal-manipulasi-immutable-log)
8. [Bab 8: Proteksi Berkas Arsip ZIP Aplikasi (AES-256 Password 20 Karakter Acak Auto-Reset)](#bab-8-proteksi-berkas-arsip-zip-aplikasi-aes-256-password-20-karakter-acak-auto-reset)
9. [Bab 9: Tata Cara Pencadangan Aplikasi & Integritas Hash SHA-256 (Backup SOP 3-2-1)](#bab-9-tata-cara-pencadangan-aplikasi--integritas-hash-sha-256-backup-sop-3-2-1)
10. [Bab 10: Model Distribusi Lepas Kunci (Zero-Knowledge Turnkey Handover) & BAST](#bab-10-model-distribusi-lepas-kunci-zero-knowledge-turnkey-handover--bast)
11. [Bab 11: Prosedur Pengembang Baru, Isolasi Data Sintetis (SSDLC) & Verifikasi Patch](#bab-11-prosedur-pengembang-baru-isolasi-data-sintetis-ssdlc--verifikasi-patch)
12. [Bab 12: Rencana Kerja & Jadwal Implementasi Sistematis (Action Plan 4 Fase)](#bab-12-rencana-kerja--jadwal-implementasi-sistematis-action-plan-4-fase)
13. [Bab 13: Matriks Analisis Risiko, Mitigasi Siber & Keselarasan Regulasi BSSN](#bab-13-matriks-analisis-risiko-mitigasi-siber--keselarasan-regulasi-bssn)
14. [Bab 14: Kriteria Keberhasilan, Audit Kepatuhan & Lembar Pengesahan (Sign-Off)](#bab-14-kriteria-keberhasilan-audit-kepatuhan--lembar-pengesahan-sign-off)

---

## Bab 1: Latar Belakang, Urgensi & Prinsip Kedaulatan Sistem v2.0

Sistem Informasi PADU (*Pengolah & Analisis Data Terpadu*) pada iterasi awal telah membuktikan kehandalan performa analitik berkecepatan tinggi, mampu melakukan streaming ingest data lebih dari 1,5 juta baris per detik serta kalkulasi 7 metrik statistik regional secara instan melalui perpaduan mesin Laravel 12 dan Python DuckDB OLAP Engine.

Dalam rangka membawa sistem ke tingkat kematangan enterprise (v2.0) di lingkungan tertutup kantor klien, seluruh pemangku kepentingan menyepakati penyempurnaan mendasar terhadap aspek keamanan, privasi data, dan tata kelola kepemilikan yang diselaraskan secara ketat dengan kerangka regulasi Badan Siber dan Sandi Negara (BSSN):
1. **Serah Terima Lepas Kunci (*Zero-Knowledge Handover*)**: Setelah Berita Acara Serah Terima (BAST) ditandatangani, tim pengembang (*developer*) **100% lepas tangan**. Tidak ada pintu belakang (*backdoor*), tidak ada ketergantungan lisensi berkala, dan tidak ada biaya langganan. Kedaulatan penuh berada di tangan Super Admin Klien.
2. **Arsitektur Web Murni Tanpa Biometrik**: Modul pengawasan webcam OpenCV, loop komputasi wajah 0.2 detik, dan pemaksaan lock screen Windows OS dieliminasi total. Sistem bertransformasi menjadi aplikasi web murni (*clean web app*) yang sangat stabil, efisien, dan dapat diakses bersama secara lancar melalui jaringan Wi-Fi/LAN kantor tanpa membutuhkan internet.
3. **Kepatuhan Penuh Terhadap Standar BSSN & UU PDP**: Seluruh rancangan pengamanan data, manajemen kata sandi, otentikasi multi-faktor, pencatatan log audit forensik, dan manajemen sesi dikonstruksi memenuhi ambang batas ketentuan teknis pemerintah Indonesia.
4. **Alur Otentikasi Bertahap (*Email-First Login*)**: Mengadopsi alur otentikasi dua langkah. Sistem mengenali tipe hak akses pada tahap memasukkan kata sandi dan menyediakan penanganan lupa sandi yang terspesialisasi (2FA HP untuk Super Admin, dan Admin-Assisted untuk Operator).
5. **Proteksi Arsip ZIP Aplikasi (AES-256 Password 20 Karakter Auto-Reset)**: Berkas arsip cadangan aplikasi dilindungi standar enkripsi AES-256 dengan password 20 karakter acak tingkat tinggi yang otomatis me-reset dirinya sendiri seketika berkas ZIP dibuka, didukung lembar cetak fisik untuk Master Key di brankas Pimpinan.

---

## Bab 2: Tujuan, Sasaran Strategis & Landasan Kepatuhan Regulasi Siber

### 2.1. Landasan Hukum & Regulasi Keamanan Informasi
Pembaruan PADU v2.0 Enterprise merujuk dan tunduk pada instrumen hukum siber nasional:
- **Peraturan BSSN No. 4 Tahun 2021**: *Pedoman Manajemen Keamanan Informasi SPBE dan Standar Teknis dan Prosedur Keamanan SPBE* (Kontrol Autentikasi, Manajemen Sesi, Penanganan Kesalahan, Audit Trail, dan Kriptografi).
- **Peraturan BSSN No. 11 Tahun 2024**: *Penyelenggaraan Algoritma Kriptografi Indonesia dan Penilaian Kesesuaian Keamanan Modul Kriptografi* (Standar Penggunaan AES-256, HMAC-SHA256, dan Algoritma Hash Aman).
- **Peraturan BSSN No. 10 Tahun 2020**: *Pengamanan Pengadaan dan Pengembangan Perangkat Lunak* (Secure SDLC, larangan pemaparan data nyata kepada developer).
- **Peraturan BSSN No. 8 Tahun 2020**: *Sistem Pengamanan dalam Penyelenggaraan Sistem Elektronik* (Prinsip Confidentiality, Integrity, Availability, Authenticity, dan Non-Repudiation).
- **Undang-Undang No. 27 Tahun 2022**: *Perlindungan Data Pribadi (UU PDP)* (Kewajiban Pengamanan Data Kependudukan Spesifik dan Akuntabilitas Pemrosesan Data).

### 2.2. Sasaran Strategis Sistem
- **Kemandirian Sistem 100% (*Zero-Knowledge*)**: Menjamin sistem dapat beroperasi, dicadangkan, dan dipulihkan seutuhnya oleh Klien tanpa ketergantungan pihak ketiga.
- **Otentikasi Berlapis Sesuai BSSN**: Menegakkan kebijakan kata sandi minimal 15 karakter (melampaui syarat 12 karakter BSSN), pembatasan 5 kali percobaan gagal (*account lockout*), dan sesi kedaluwarsa otomatis (*inactivity timeout* 15 menit).
- **Keamanan Akun Super Admin via 2FA HP Offline**: Menggunakan Google Authenticator / FreeOTP di HP Bos tanpa pulsa dan tanpa internet (TOTP RFC 6238), didukung lembar cetak fisik kunci cadangan untuk antisipasi HP hilang.
- **Enkripsi Data Sensitif (*Data at Rest*)**: Mengacak data identitas warga pada tabel fisik menggunakan AES-256-CBC dipadu HMAC-SHA256 bawaan Laravel Eloquent casting serta proteksi partisi disk server.
- **Audit Trail Forensik Anti-Manipulasi (*Immutable Audit Log*)**: Menyimpan rekaman riwayat akses, login, ekspor, dan perubahan konfigurasi secara permanen (*append-only*) dengan retensi minimal 12 bulan.
- **Proteksi Folder Server & SOP Pemulihan Bencana**: Mengikat aplikasi pada hardware PC Server kantor saat inisialisasi awal, disertai prosedur darurat *re-binding* resmi berotorisasi jika server fisik mengalami kerusakan.

---

## Bab 3: Topologi Jaringan LAN Offline, Anti-Oper Folder & SOP Pemulihan Bencana Hardware (DRP)

Sistem PADU v2.0 mengadopsi topologi On-Premise Single-Host Server terpusat di lingkungan jaringan lokal kantor:

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
                     │     - dataset.duckdb (13M+ Baris Data Kependudukan)     │
                     │  5. Proteksi Disk: Windows BitLocker (AES-XTS 256)      │
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
│ • Hak Akses Penuh:                   │                       │ • Hak Akses Terbatas:                │
│   - First-Login Force Reset (15 kar) │                       │   - Tab 1: Data Mikro Terpadu        │
│   - Panel Kamus Data & Pengguna      │                       │   - Tab 2: Statistik 7 Metrik        │
│   - One-Click Backup System          │                       │   - Ekspor Laporan CSV/ZIP           │
│   - Verifikasi 2FA HP Offline        │                       │   - Read-Only Ringkasan Data         │
│   - Log Audit Forensik Sistem        │                       │   - Auto Inactive Logout (15 menit)  │
└──────────────────────────────────────┘                       └──────────────────────────────────────┘
```

### 3.1. Pembagian Peran Komputasi
1. **Komputer Server Mandiri (PC Host)**: Aplikasi, database, dan engine hanya berada di PC host.
2. **Akses Klien Murni Browser**: Laptop Bos dan staf hanya mengakses port web melalui Google Chrome. Laptop klien sama sekali tidak memegang berkas aplikasi atau database fisik.

### 3.2. Hardware Self-Binding (Anti-Oper Folder)
Saat inisialisasi awal, sistem membaca Serial Motherboard dan CPU Processor ID server kantor (`wmic csproduct get uuid`). Jika folder aplikasi disalin ke komputer lain, sistem mendeteksi ketidakcocokan perangkat keras dan langsung mengunci akses.

### 3.3. Prosedur Pemulihan Bencana Hardware (*Disaster Recovery & Re-Binding SOP*)
Mengacu pada **Peraturan BSSN No. 8 Tahun 2020 (Pilar *Availability*)**, sistem harus memiliki mitigasi jika server fisik mengalami kerusakan mendadak (misal: motherboard terbakar):
- Sistem menyediakan skrip konsol darurat `restore_hardware_bind.bat`.
- Pimpinan memasukkan **Master Recovery Key** fisik (dari brankas).
- Sistem memverifikasi kecocokan hash kunci pemulihan $\rightarrow$ membaca identitas silikon motherboard PC baru $\rightarrow$ memperbarui tabel signature perangkat keras resmi.
- Sistem aktif kembali 100% di server baru tanpa ketergantungan memanggil tim pengembang eksternal.

---

## Bab 4: Alur Otentikasi Bertahap, Sandi Min. 15 Karakter, Account Lockout & Manajemen Sesi

Mengacu pada **Peraturan BSSN No. 4 Tahun 2021 (Standar Teknis Autentikasi & Manajemen Sesi Aplikasi Web)**:

### 4.1. Langkah 1: Input Alamat Email
Pengguna membuka `http://192.168.1.10:8000` $\rightarrow$ mengetikkan alamat email terdaftar $\rightarrow$ klik tombol *"Lanjutkan / Next"*.

### 4.2. Langkah 2: Input Password & Pembeda Role
Pada layar kedua, sistem membaca identitas email yang telah diverifikasi:
- **Identifikasi Role Otomatis**: Tampil nama pengguna dan lencana role resmi (Badge `Super Admin` atau `Operator Data`).
- **Standar Kekuatan Kata Sandi (Minimal 15 Karakter)**:
  Kebijakan kata sandi wajib memenuhi standar:
  ```php
  Password::min(15)->mixedCase()->numbers()->symbols()->uncompromised()
  ```
  *(Standar ini melampaui batas minimal 12 karakter yang disyaratkan BSSN No. 4/2021)*.
- **Logika Percabangan Saat Klik "Lupa Kata Sandi?"**:
  - **Jika Super Admin (Bos)**: Muncul layar verifikasi 2FA: *"Buka Google Authenticator di HP Anda & Masukkan 6 Digit Kode Token"*. Setelah cocok $\rightarrow$ langsung muncul pop-up untuk membuat kata sandi baru (min. 15 karakter).
  - **Jika Operator (Pegawai)**: Muncul notifikasi dialog: *"Akun Operator tidak dapat reset mandiri demi kepatuhan audit. Silakan hubungi Super Admin di kantor untuk mereset kata sandi Anda."* (Admin-Assisted).

### 4.3. Pembatasan Percobaan Login Gagal (*Account Lockout & Rate Limiting*)
Sesuai mandat Peraturan BSSN No. 4/2021 untuk mencegah serangan *Brute-Force / Credential Stuffing*:
- Sistem membatasi kesalahan input kata sandi maksimal **5 kali berturut-turut**.
- Jika batas 5 kali terlampaui, akun otomatis dikunci sementara selama **15 menit** (`Laravel RateLimiter 5 attempts / 900 seconds`).
- Percobaan gagal dicatat seketika dalam Audit Trail Forensik dengan stempel waktu dan alamat IP pemanggil.

### 4.4. Masa Berlaku Sandi (*Password Aging / Expiry*) & Riwayat Sandi
- **Masa Berlaku Maksimal**: Kata sandi wajib dirotasi setiap **180 hari (6 bulan)**. Sistem akan menampilkan dialog pembaruan sandi 7 hari sebelum masa berlaku berakhir.
- **Larangan Penggunaan Ulang (*Password History*)**: Sistem menyimpan hash dari 3 kata sandi terakhir; pengguna dilarang menggunakan kembali salah satu dari 3 kata sandi terakhir tersebut.

### 4.5. Manajemen Sesi & Batas Waktu Tidak Aktif (*Inactivity Session Timeout*)
Sesuai Peraturan BSSN No. 4/2021 untuk mencegah pembajakan sesi (*session hijacking*) dan intipan fisik (*shoulder surfing*):
- Sistem menerapkan *Inactivity Timeout* otomatis sebesar **15 menit** (`SESSION_LIFETIME = 15`).
- Jika pengguna tidak melakukan aktivitas klik atau input data selama 15 menit, sesi login hangus secara otomatis (*session invalidated*), dan tampilan layar kembali ke halaman login awal.

---

## Bab 5: Sistem Keamanan 2FA HP Cerdas, Hashing Kunci Darurat & Protokol Auto-Revoke

Untuk mengamankan akun Super Admin tanpa ketergantungan internet atau pulsa SMS:

### 5.1. Pembedaan Kunci 2FA di HP vs Kunci Cadangan Cetak (Berbeda Total)
Sistem membedakan secara tegas dua instrumen kunci:
1. **Kunci 2FA di HP (Kunci Operasional)**: Tersimpan di aplikasi Google Authenticator / FreeOTP di HP Bos melalui pemindaian QR Code lokal saat setup pertama. Menghasilkan token 6 digit berbasis waktu (TOTP RFC 6238) yang berputar setiap 30 detik.
2. **Kunci Cadangan Fisik (Emergency Revocation Key)**: Dicetak pada lembar fisik resmi untuk disimpan Bos di brankas atau laci meja terkunci. Kodenya **BERBEDA TOTAL** dari kunci di HP.

### 5.2. Penyimpanan Kunci Darurat Fisik Berbasis Hash (Kepatuhan BSSN)
- Sesuai standar kriptografi BSSN, teks kunci cadangan fisik **TIDAK DISIMPAN DALAM BENTUK TEKS ASLI (PLAINTEXT)** pada database server.
- Kunci darurat disimpan dalam bentuk nilai hash satu arah berbobot tinggi menggunakan algoritma **Argon2id / Bcrypt** dengan *salt* acak unik. Jika database server dicuri, pelaku tidak dapat membaca maupun membalikkan nilai kunci fisik darurat tersebut.

### 5.3. Penolakan Penambahan HP Liar (Cegah Akun Liar)
Sistem **tidak mengizinkan penambahan HP baru sembarangan dari menu profil**. Hal ini menutup celah keamanan agar akun Super Admin tidak dapat ditautkan ke HP pihak ketiga atau oknum internal lain yang tidak berkepentingan.

### 5.4. Deteksi HP Hilang/Rusak & Auto-Revoke Kunci Lama
Jika HP Bos hilang, dicuri, atau rusak, Bos memasukkan Kunci Cadangan Fisik pada layar pemulihan 2FA:
1. **Auto-Revoke Kunci HP Lama**: Begitu Kunci Cadangan Fisik diverifikasi cocok dengan hash di server, sistem **LANGSUNG MENGHANGUSKAN Kunci 2FA lama di HP tersebut**. Token 2FA di HP lama seketika mati total dan tidak dapat dipakai lagi.
2. **Penerbitan QR Code Baru di HP Baru**: Sistem seketika men-generate **QR Code 2FA BARU** (dengan kunci rahasia baru yang berbeda total) untuk di-scan oleh Bos menggunakan **HP BARU** miliknya.
3. **Cetak Ulang Kunci Cadangan Baru**: Sistem mencetak ulang 1 lembar fisik Kunci Cadangan Darurat Baru untuk disimpan kembali oleh Bos di brankas kerjanya.

---

## Bab 6: Enkripsi Database Berlapis (Layered Database Defense) & Standar Kriptografi BSSN

Mengacu pada **Peraturan BSSN No. 11 Tahun 2024** dan **UU No. 27 Tahun 2022 (UU PDP Pasal 35)**:

### 6.1. Enkripsi Lapis Aplikasi (*Application-Level Column Encryption*)
- Seluruh atribut data pribadi spesifik warga (NIK, Nama Lengkap, Pendapatan, Alamat RT/RW, Metadata Akun) dienkripsi secara transparan menggunakan algoritma **AES-256-CBC** bawaan Laravel Eloquent casting.
- Sistem menerapkan skema otentikasi pesan kriptografi **HMAC-SHA256 (*Encrypt-then-MAC*)** yang diikat dengan `APP_KEY` server. Jika berkas database dimodifikasi secara ilegal oleh pihak luar, integritas data dinyatakan rusak dan sistem menolak eksekusi (*tamper-proof*).
- Pada berkas fisik `database.sqlite`, kolom-kolom tersimpan sebagai teks teracak (*ciphertext*: `eyJpdiI6...`). Berkas tidak dapat dibaca oleh software SQLite viewer biasa.

### 6.2. Enkripsi Lapis Media Penyimpanan (*Full Disk Encryption & ACL*)
- **Storage Engine Level**: Direktori analitik DuckDB (`dataset.duckdb`) dan berkas SQLite diproteksi menggunakan Windows Access Control List (ACL) yang hanya dapat dibaca oleh identitas servis lokal aplikasi.
- **Standar Full Disk Encryption (BitLocker)**: Drive partisi server tempat instalasi sistem PADU diwajibkan mengaktifkan proteksi **Windows BitLocker dengan enkripsi AES-XTS 256-bit** berbasis chip hardware TPM (Trusted Platform Module). Jika harddisk server dicopot secara fisik, seluruh berkas tidak dapat diakses tanpa PIN otorisasi BIOS server.

---

## Bab 7: Standar Audit Trail, Forensik Digital & Pencatatan Log Kebal Manipulasi (Immutable Log)

Mengacu pada **Peraturan BSSN No. 4 Tahun 2021 (Pasal Standar Teknis Log & Forensik Digital)** serta **Peraturan BSSN No. 8 Tahun 2020 (Pilar *Non-Repudiation*)**:

### 7.1. Struktur & Parameter Log Audit Forensik
Setiap peristiwa penting dicatat dalam tabel audit terdedikasi (`system_audit_logs`) pada SQLite dengan format rekaman terstruktur:
1. **Timestamp**: Format standar ISO-8601 dengan presisi detik (`YYYY-MM-DD HH:MM:SS WIB`).
2. **User Identity**: ID Pengguna, Alamat Email, dan Lencana Role pengguna aktif.
3. **Network Context**: Alamat IP Klien (`192.168.1.X`), Hostname, dan Header *User-Agent* peramban.
4. **Action Category**: 
   - `AUTH_LOGIN_SUCCESS` / `AUTH_LOGIN_FAILED` / `AUTH_LOCKOUT`
   - `PASSWORD_CHANGED` / `PASSWORD_RESET_2FA`
   - `2FA_EMERGENCY_REVOKE`
   - `DATA_EXPORT_CSV` / `DATA_EXPORT_ZIP`
   - `SYSTEM_BACKUP_CREATED`
   - `PATCH_UPLOAD_APPLIED`
   - `METADATA_LIBRARY_UPDATED`
5. **Event Payload**: Rincian entitas yang diakses atau diubah (ringkasan filter, parameter ekspor, atau versi patch).
6. **Execution Status**: `SUCCESS`, `DENIED`, atau `FAILED`.

### 7.2. Kebal Manipulasi (*Immutable & Append-Only*)
- Tabel audit log dirancang bersifat **Hanya Tambah (*Append-Only*)**.
- Antarmuka web Super Admin hanya menyediakan fitur *Read & Filter Log*. Tidak ada tombol *Edit* maupun *Delete Log*.
- Riwayat log audit dipertahankan dengan masa retensi minimal **12 bulan (1 tahun)** sesuai standar audit kepatuhan keamanan informasi pemerintah.

---

## Bab 8: Proteksi Berkas Arsip ZIP Aplikasi (AES-256 Password 20 Karakter Acak Auto-Reset)

Untuk keamanan data dan folder aplikasi pada level penyimpanan arsip cadangan:

### 8.1. Algoritma Enkripsi Arsip Standar Industri (AES-256)
- Berkas arsip ZIP dienkripsi menggunakan standar **WinZip/7-Zip AES-256 dengan PBKDF2 Key Derivation**, menggantikan algoritma *ZipCrypto* jadul yang rentan terhadap serangan analisis kriptografi.
- Berkas dikunci menggunakan kata sandi sepanjang **20 karakter acak tingkat tinggi** (*high-entropy alphanumeric & symbols*), misalnya: `K9#mQ2$xL8!vW4&yP1*z`.

### 8.2. Lembar Cetak Master ZIP
Password ZIP 20 karakter dicetak pada lembar fisik pemulihan aplikasi untuk disimpan oleh Bos di brankas pimpinan.

### 8.3. Mekanisme Auto-Reset Seketika Saat ZIP Terbuka
1. Setiap kali berkas ZIP cadangan diekstrak atau dibuka untuk pemulihan server di komputer baru, sistem mendeteksi peristiwa ekstraksi tersebut.
2. Detik itu juga, sistem otomatis **MENGHANGUSKAN password 20 karakter lama** dan **MERESET password baru 20 karakter acak yang berbeda**, lalu mengemas ulang arsip ZIP dengan password baru tersebut.
3. Jika ada oknum yang sempat mengintip atau mencatat password 20 karakter saat ZIP dibuka, password tersebut langsung menjadi tidak berlaku (*expired*) untuk ekstraksi berikutnya.

---

## Bab 9: Tata Cara Pencadangan Aplikasi & Integritas Hash SHA-256 (Backup SOP 3-2-1)

Mengacu pada panduan ketahanan siber BSSN dan mitigasi ancaman ransomware:

### 9.1. Penerapan Aturan Pencadangan 3-2-1
- **Salinan 1 (Data Operasional)**: Basis data aktif di PC Server.
- **Salinan 2 (Cadangan Lokal Terjadwal)**: Skrip otomatis `cadangkan_padu.bat` mengeksekusi *SQLite vacuum into* dan *DuckDB checkpoint* ke partisi lokal sekunder (`D:\BACKUP_PADU\`).
- **Salinan 3 (Cadangan Terisolasi / Off-Host Media)**: Pimpinan mengeksekusi fitur *One-Click Backup* di menu Super Admin $\rightarrow$ berkas `.padubak` terunduh langsung ke laptop pribadi Pimpinan untuk disimpan ke Flashdisk fisik tersendiri.

### 9.2. Verifikasi Integritas Melalui Hash SHA-256
Setiap kali berkas cadangan dibuat, sistem secara otomatis membangkitkan berkas stempel integritas:
`PADU_BACKUP_YYYYMMDD.padubak.sha256`.  
Sebelum proses pemulihan (*restore*) dijalankan, sistem melakukan kalkulasi hash ulang untuk memvalidasi bahwa berkas cadangan 100% utuh, tidak korup, dan tidak pernah disusupi kode berbahaya (*tamper verification*).

---

## Bab 10: Model Distribusi Lepas Kunci (Zero-Knowledge Turnkey Handover) & BAST

Pengembang menyerahkan aplikasi dalam keadaan utuh tanpa hak retensi maupun akses terselubung:
- **Tanpa Lisensi Berkala**: Sistem tidak memerlukan aktivasi serial tahunan atau otorisasi tanda tangan developer di kemudian hari.
- **Inisialisasi Mandiri oleh Pimpinan**: Kunci enkripsi master digenerate secara lokal di PC Server saat Pimpinan login dan melakukan set password pertama kali.
- **Klausul BAST (Berita Acara Serah Terima)**: Menegaskan pengalihan tanggung jawab pemeliharaan, keamanan fisik, dan kerahasiaan kata sandi secara mutlak kepada pihak Klien sesuai kaidah hukum perikatan perdata.

---

## Bab 11: Prosedur Pengembang Baru, Isolasi Data Sintetis (SSDLC) & Verifikasi Patch

Mengacu pada **Peraturan BSSN No. 10 Tahun 2020 tentang Pengamanan Pengadaan dan Pengembangan Perangkat Lunak (SSDLC)**:

### 11.1. Pemisahan Total Kode vs Data Nyata Warga (Data Sintetis 10.000)
- Sesuai amanat UU PDP dan BSSN, programmer/developer baru **DILARANG KERAS** memegang atau melihat database asli warga.
- Developer baru hanya menerima *Clean Source Code* dan database data tiruan sintetis (*dummy data*) sebanyak 10.000 data palsu melalui perintah:
  ```bash
  php artisan db:seed --class=DummyCitizenSeeder
  ```

### 11.2. Isolasi Lingkungan Koding (*Local Sandbox*)
Aplikasi dibekali isolasi lingkungan di file `.env`:
- `APP_ENV=local`: Proteksi *Hardware Lock* nonaktif agar developer baru leluasa menguji kode di laptop kerjanya.
- `APP_ENV=production`: Proteksi *Hardware Lock*, Enkripsi AES-256, dan Rate Limiter aktif penuh.

### 11.3. Alur Pembaruan Sistem via Patch ZIP & Verifikasi Hash SHA-256
1. Developer baru mengemas berkas pembaruan kode ke dalam paket `update_vX.Y.zip` disertai nilai hash SHA-256 resminya.
2. Pimpinan mengunggah paket patch melalui panel web Super Admin.
3. Sistem secara otomatis menghitung kecocokan nilai hash SHA-256 paket $\rightarrow$ jika valid, sistem mengekstrak kode baru secara aman.
4. Database data warga asli tetap utuh, dan ikatan hardware server tetap terlindungi sempurna.

### 11.4. Pemeliharaan Lapangan Terkendali (*Supervised Access*)
Jika diperlukan perbaikan langsung di PC Server kantor, pekerjaan wajib dilakukan di bawah pendampingan fisik Pimpinan dengan mengaktifkan mode pemeliharaan (`php artisan down`). Seluruh aktivitas tercatat lengkap pada *audit trail log*.

---

## Bab 12: Rencana Kerja & Jadwal Implementasi Sistematis (Action Plan 4 Fase)

| Fase | Rincian Pekerjaan Teknis | Target Luaran (Deliverables) |
| :--- | :--- | :--- |
| **Fase 1: Penyelarasan Blueprint & Spesifikasi Kepatuhan BSSN** | Pembaruan DFD Level 0, 1, 2, ERD, dan Swimlane dengan alur Two-Step Login, 2FA TOTP RFC 6238, tabel audit forensik, dan rate limiter. | Berkas Draw.io v2.0 valid & tersertifikasi desain BSSN. |
| **Fase 2: Backend Auth, Enkripsi AES-256 & Audit Logger** | Implementasi Auth Bertahap, RateLimiter (5x lockout), Session Timeout (15 mnt), Password Expiry (180 hari), Laravel encrypted casting, dan modul Immutable Audit Log. | Modul Otentikasi BSSN & Audit Logger aktif operasional. |
| **Fase 3: Generator ZIP AES-256, Backup Hash SHA-256 & Lembar Cetak** | Pengembangan modul auto-reset ZIP AES-256, generator verifikasi hash SHA-256 backup, template cetak kunci fisik (Argon2id hash di DB), dan UI One-Click Backup. | Fitur Enkripsi Arsip & Integritas Backup selesai 100%. |
| **Fase 4: Simulasi Forensik, Uji Penetrasi & BAST** | Uji coba serangan brute-force (validasi lockout 5x), simulasi timeout 15 menit, simulasi auto-revoke HP via kunci cetak, uji DRP hardware re-bind, dan penandatanganan BAST Lepas Kunci. | Berita Acara Uji Kepatuhan & BAST Lepas Kunci ditandatangani. |

---

## Bab 13: Matriks Analisis Risiko, Mitigasi Siber & Keselarasan Regulasi BSSN

| No | Potensi Risiko Keamanan | Tingkat Dampak | Rencana Mitigasi Teknis PADU v2.0 | Keselarasan Regulasi |
| :---: | :--- | :---: | :--- | :--- |
| **1** | **Pencurian folder server via flashdisk oleh pihak internal** | TINGGI | Database terenkripsi AES-256-CBC, drive server terproteksi BitLocker, dan aplikasi terikat hardware UUID server. | Per BSSN No. 11/2024 & UU PDP Pasal 35 |
| **2** | **Serangan Brute-Force tebak password** | TINGGI | Wajib sandi minimal 15 karakter kompleksitas tinggi dan sistem *Account Lockout* otomatis setelah 5 kali gagal (kunci 15 menit). | Per BSSN No. 4/2021 (Standar Autentikasi) |
| **3** | **Laptop ditinggal staf dalam keadaan login (Intipan Fisik)** | SEDANG | *Inactivity Session Timeout* memutus sesi secara otomatis setelah 15 menit tanpa aktivitas pengguna. | Per BSSN No. 4/2021 (Manajemen Sesi) |
| **4** | **Pimpinan lupa kata sandi 15 karakter** | SEDANG | Pimpinan memulihkan hak akses secara mandiri menggunakan token 6 digit dari aplikasi Google Authenticator di HP pribadinya. | Per BSSN No. 4/2021 (Pemulihan Sandi) |
| **5** | **HP Pimpinan hilang / dicuri / rusak** | TINGGI | Memasukkan Kunci Cadangan Cetak $\rightarrow$ Sistem otomatis MENGHANGUSKAN kunci lama (*auto-revoke*) dan menerbitkan QR Code baru di HP baru. Kunci darurat disimpan dalam bentuk hash Argon2id. | Per BSSN No. 4/2021 & No. 8/2020 |
| **6** | **Password arsip ZIP diintip saat ekstraksi** | TINGGI | Sistem mengunci ZIP dengan AES-256 dan seketika MENGHANGUSKAN password lama serta MERESET password baru 20 karakter acak begitu ZIP dibuka. | Per BSSN No. 11/2024 |
| **7** | **Motherboard PC Server kantor terbakar / rusak fisik** | TINGGI | Disediakan SOP Pemulihan Bencana (*Hardware Re-Binding*) menggunakan otorisasi Master Recovery Key fisik dari brankas. | Per BSSN No. 8/2020 (Pilar Availability) |
| **8** | **Penyusupan kode berbahaya pada paket pembaruan** | TINGGI | Verifikasi nilai hash integritas SHA-256 sebelum berkas patch diekstrak oleh sistem. | Per BSSN No. 10/2020 (SSDLC) |
| **9** | **Penyangkalan aksi pengguna (*Repudiation*)** | SEDANG | Pencatatan seluruh transaksi penting ke dalam *Immutable Audit Log* permanen dengan retensi minimal 12 bulan. | Per BSSN No. 8/2020 (Pilar Non-Repudiation) |
| **10**| **Keterikatan vendor / Tuntutan hukum di kemudian hari** | TINGGI | Penandatanganan BAST Lepas Kunci (*Zero-Knowledge*) membuktikan pengembang tidak memiliki akses dan kunci master setelah serah terima. | UU ITE & Hukum Perdata Nasional |

---

## Bab 14: Kriteria Keberhasilan, Audit Kepatuhan & Lembar Pengesahan (Sign-Off)

Pekerjaan pembaruan sistem ini dinyatakan tuntas dan siap diserahterimakan penuh apabila telah memenuhi seluruh butir pengujian berikut:
1. **✓ Otentikasi Bertahap & Password Min. 15 Karakter**: Layar pertama meminta email, layar kedua mengidentifikasi role pengguna dan meminta kata sandi min 15 karakter.
2. **✓ Uji Brute-Force & Account Lockout**: Sistem sukses mengunci akun selama 15 menit setelah 5 kali gagal memasukkan kata sandi, serta mencatatnya di log audit.
3. **✓ Uji Inactivity Session Timeout**: Sesi web otomatis terputus setelah 15 menit pengguna tidak melakukan interaksi pada layar peramban.
4. **✓ Pemulihan Lupa Sandi Super Admin via 2FA HP**: Super Admin berhasil mereset kata sandi menggunakan token 6 digit dari Google Authenticator di HP pribadinya.
5. **✓ Auto-Revoke 2FA Saat HP Hilang**: Sistem sukses menghanguskan kunci HP lama dan menerbitkan QR Code baru setelah kunci cadangan cetak dimasukkan (hash Argon2id terverifikasi).
6. **✓ Uji Enkripsi Database & Arsip ZIP AES-256**: Kolom sensitif di SQLite terbukti teracak, arsip ZIP terkunci enkripsi AES-256, dan password 20 karakter sukses ter-reset otomatis saat ZIP diekstrak.
7. **✓ Validasi Integritas Hash SHA-256**: Sistem sukses memverifikasi berkas cadangan `.padubak.sha256` dan paket pembaruan patch.
8. **✓ Uji Forensik Log Kebal Manipulasi**: Seluruh riwayat transaksi terekam di tabel audit tanpa tersedianya tombol edit/hapus pada antarmuka.
9. **✓ Kemandirian Penuh (Zero-Knowledge Handover)**: Sistem berjalan normal di jaringan LAN kantor tanpa ketergantungan koneksi internet dan tanpa campur tangan developer.

---

### LEMBAR PERSETUJUAN PROPOSAL TEKNIS PEMBARUAN v2.0 ENTERPRISE
*(Penyelarasan Standar Keamanan BSSN & Tata Kelola Lepas Kunci)*

| Diajukan Oleh (Pengembang) | Disetujui Oleh (Klien / Pimpinan Proyek) |
| :--- | :--- |
| &nbsp;<br>&nbsp;<br>&nbsp;<br>**( _____________________________ )**<br>Lead System Architect & Developer | &nbsp;<br>&nbsp;<br>&nbsp;<br>**( _____________________________ )**<br>Project Sponsor / Super Admin Klien |

---

> **Tindak Lanjut**: Memulai Fase 1 (Pembaruan Seluruh Berkas Diagram Arsitektur, ERD, Swimlane & Dokumentasi Spesifikasi v2.0 Terakreditasi BSSN).
