# DOKUMENTASI EKSTENSI KHUSUS: SUPER ADMIN & TATA KELOLA ENTERPRISE
## PADU v2.0 Enterprise — Sistem Pengolah & Analisis Data Terpadu (DTSEN 2026 Analytics Engine)
### Berkas Arsitektur Terpisah: DFD Level 0, Level 1 (5 Proses Inti), Level 2 (First-Login Reset & Library), ERD RBAC, dan Swimlane Terpisah

---

> [!IMPORTANT]
> **Pemisahan Berkas Resmi**: Dokumen ini mendokumentasikan modul terpisah khusus **SUPER ADMIN** dan **Tata Kelola Sistem Enterprise (Library Metadata, Audit Log Forensik BSSN, Dual Backup AES-256, & Admin-Assisted Offline Password Reset)**. Modul ini bebas total dari ketergantungan perangkat keras biometrik/kamera, bertransformasi menjadi aplikasi web murni berstandar industri (*clean web app*) yang mematuhi standar BSSN No. 4/2021 dan UU PDP No. 27/2022.

---

## 1. Ringkasan Arsitektur Super Admin

Modul Super Admin menghadirkan kendali penuh terhadap pembaruan kamus metadata (*dictionary library*), skema sinonim 48 variabel BPS, aturan validasi data, hak akses staf operator, manajemen lupa kata sandi offline, audit trail forensik, serta pencadangan sistem terenkripsi AES-256.

| Parameter | Operator Biasa / Analis | Super Admin (Pimpinan / Auditor) |
| :--- | :--- | :--- |
| **Identitas Aktor** | `E1: OPERATOR DATA / ANALIS` | `E2: SUPER ADMIN` |
| **Autentikasi** | Two-Step Login (Email + Password min 15 char) | Two-Step Login + **One-Time Force Reset Password** saat login perdana + **2FA TOTP Ponsel Offline** |
| **Akses Modul** | Tab Data Mikro & Tab Data Statistik Regional | **Panel Khusus Super Admin**: Library Metadata, Manajemen User, Log Audit Forensik, & Backup AES-256 |
| **Hak Modifikasi** | Hanya Read & Filter Data (Read-Only) | **Create, Update, Versioning & Hot-Reload Kamus**, Reset Password Operator, Eksekusi Backup |
| **Pencadangan Data** | Ekspor Paket ZIP DTSEN (`src-export/`) | **Dual-Method Backup Sistem** (ZIP AES-256 Ber-password Acak 20 Karakter & Kunci Brankas DRP) |
| **Pencatatan Jejak** | Audit log baris data anomali | **Audit trail forensik digital kebal manipulasi** (siapa, aksi, IP, payload sebelum/sesudah, stempel waktu) |

---

## 2. Berkas Diagram Terkait (Tersimpan Terpisah)

1. **DFD & ERD Terintegrasi Super Admin**:
   - `Padu_Diagram_SuperAdmin.drawio` (Tab AS-IS, Tab TO-BE Enterprise, Tab ERD RBAC, Tab Arsitektur Jaringan LAN Offline).
2. **Swimlane Alur Super Admin**:
   - `swimlane_tobe_superadmin.drawio` (Alur sekuensial verifikasi RBAC, percabangan *First-Login Reset Password 1x*, pembaruan kamus metadata, hot-reload cache, hingga pencatatan audit log forensik).

---

## 3. DFD TO-BE SUPER ADMIN LEVEL 0 (DIAGRAM KONTEKS)

Menampilkan 2 entitas manusia: **Operator Data** dan **Super Admin**, yang berinteraksi secara aman melalui jaringan LAN kantor offline.

```mermaid
flowchart LR
    E1["👤 E1: OPERATOR DATA / ANALIS"]
    E2["🔑 E2: SUPER ADMIN (PIMPINAN)"]

    P0_SA(("0.0<br/><b>SISTEM ANALISIS DATA DTSEN,<br/>TATA KELOLA METADATA &<br/>KEAMANAN ENTERPRISE (PADU v2.0)</b>"))

    %% Operator
    E1 -->|"1. Kredensial Login Operator (Min 15 Char)<br/>2. Berkas Mentah DTSEN (CSV/XLSX 13M+ Baris)<br/>3. Kriteria Filter Dinamis & Pilihan Modul<br/>4. Permintaan Ekspor Paket ZIP Arsip"| P0_SA
    P0_SA -->|"1. Sesi Kerja Operator Valid & Terproteksi<br/>2. Tab Data Mikro (PII-Masked & FK Labels)<br/>3. Tab Data Statistik (7 Metrik Regional)<br/>4. Paket Unduhan ZIP (Clean/Error CSV + SHA-256)"| E1

    %% Super Admin
    E2 -->|"1. Kredensial Super Admin & 2FA TOTP Offline<br/>2. Kata Sandi Baru Pribadi (One-Time Force Reset)<br/>3. Payload Perubahan Kamus Metadata & Aturan Validasi<br/>4. Perintah Reset Password Operator (Admin-Assisted)<br/>5. Perintah Pencadangan Sistem (AES-256 ZIP)"| P0_SA
    P0_SA -->|"1. Form Wajib Ganti Kata Sandi (Login Perdana)<br/>2. Antarmuka Panel Tata Kelola Metadata & Hot-Reload<br/>3. Dashboard Pengelolaan Operator & Kunci Sementara<br/>4. Laporan Log Audit Forensik Digital (BSSN No. 4/2021)<br/>5. Arsip Cadangan .padubak (Enkripsi AES-256)"| E2

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    class E1,E2 entity;
    class P0_SA process;
```

---

## 4. DFD TO-BE SUPER ADMIN LEVEL 1 (DEKOMPOSISI 5 PROSES INTI)

Arsitektur TO-BE Super Admin mengintegrasikan 5 proses utama dan data store terpusat:
- **`D1: src-dtsen/`**: Folder penampungan berkas mentah.
- **`D2: dtsen_data DuckDB`**: Database kolumnar analitik cepat.
- **`D3: quality_audit_logs`**: Tabel log anomali kualitas baris data.
- **`D4: sqlite_master & sessions`**: Database akun pengguna, RBAC, sesi, dan konfigurasi.
- **`D5: src-export/ & ZIP`**: Repositori arsip unduhan hasil olahan.
- **`D6: metadata_libraries`**: Kamus sinonim variabel, aturan validasi, dan transliterasi FK.
- **`D7: forensic_audit_logs`**: Log audit forensik digital kebal manipulasi (*immutable audit trail*).
- **`D8: system_backups`**: Repositori cadangan sistem terenkripsi AES-256 militer.

```mermaid
flowchart LR
    %% Entitas
    E1["👤 E1: OPERATOR DATA"]
    E2["🔑 E2: SUPER ADMIN"]

    %% Data Stores
    D1[("D1: src-dtsen/")]
    D2[("D2: dtsen_data DuckDB")]
    D3[("D3: quality_audit_logs")]
    D4[("D4: sqlite_master & sessions")]
    D5[("D5: src-export/ & ZIP")]
    D6[("D6: metadata_libraries")]
    D7[("D7: forensic_audit_logs")]
    D8[("D8: system_backups")]

    %% 5 Sub-Proses Inti
    P1(("1.0<br/><b>Autentikasi, RBAC &<br/>Force Reset Password</b>"))
    P2(("2.0<br/><b>Ingesti, Normalisasi<br/>& Audit Kualitas Data</b>"))
    P3(("3.0<br/><b>Pemrosesan Analitik, Dual-Modul,<br/>Transliterasi & 7 Metrik</b>"))
    P4(("4.0<br/><b>Ekspor Data Olahan &<br/>Pencadangan Sistem AES-256</b>"))
    P5(("5.0<br/><b>Tata Kelola Metadata Library<br/>& Manajemen Staf Operator</b>"))

    %% Aliran P1.0
    E1 -->|"Login Operator"| P1
    E2 -->|"Login Super Admin / 2FA"| P1
    P1 <-->|"Verifikasi Hash Bcrypt & metadata.first_login"| D4
    P1 -->|"Wajib Buat Password Baru (Super Admin 1x)"| E2
    P1 -->|"Dispatch Sesi Terverifikasi"| E1
    P1 -->|"Catat Sesi Akses"| D7

    %% Aliran P2.0 & P3.0 (Analitik Data)
    E1 --> D1 --> P2 --> D2
    P2 --> D3
    E1 --> P3 <--> D2
    D6 -->|"Kamus Variabel Aktif"| P2
    D6 -->|"Label Deskriptif FK"| P3
    P3 -->|"Tab Data Mikro & Tab Data Statistik"| E1

    %% Aliran P4.0 (Ekspor & Backup)
    E1 -->|"Unduh Paket ZIP Data"| P4
    P4 --> D5
    E2 -->|"Perintah Backup Sistem"| P4
    P4 <-->|"Snapshot DB SQLite & DuckDB"| D4
    P4 -->|"Arsip Cadangan .padubak AES-256"| D8
    P4 -->|"Unduh Cadangan Berenkripsi"| E2

    %% Aliran P5.0 (Tata Kelola Admin)
    E2 -->|"Pembaruan Kamus / Aturan Validasi"| P5
    P5 -->|"Versi Library Aktif Baru"| D6
    P5 -->|"Catat Riwayat Revisi (Diff JSON)"| D7
    E2 -->|"Reset Password Staf Operator"| P5
    P5 -->|"Set Password Sementara & first_login: true"| D4
    P5 -->|"Status Operasi & Audit Log"| E2

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E1,E2 entity;
    class P1,P2,P3,P4,P5 process;
    class D1,D2,D3,D4,D5,D6,D7,D8 store;
```

---

## 5. DFD TO-BE LEVEL 2 — SUB-PROSES 1.0: ALUR ONE-TIME FORCE RESET PASSWORD

Dekomposisi rinci dari mekanisme wajib ganti kata sandi bawaan pertama kali bagi akun Super Admin:

```mermaid
flowchart TD
    A([1. Super Admin Akses /login di Browser]) --> B[2. Input: admin@padu.go.id & Sandi Default]
    B --> C{3. Verifikasi Hash Kredensial Bcrypt}
    
    C -->|Hash Tidak Cocok| Err[Tampilkan Peringatan: Kredensial Salah & Catat Percobaan]
    C -->|Hash Cocok| D{4. Evaluasi Role Pengguna}
    
    D -->|Role == 'operator'| DashOp([Langsung Masuk Dashboard Analisis Data])
    D -->|Role == 'super_admin'| E{5. Periksa users.metadata: first_login == true?}
    
    E -->|FALSE (Login ke-2, ke-3, dst)| PanelAdmin([Langsung Masuk Panel Super Admin])
    
    E -->|TRUE (Hanya 1x Pertama Kali)| F[6. Tahan Sesi & Redirect Paksa ke /auth/force-reset-password]
    F --> G[7. Tampil Form: Password Baru Pribadi & Konfirmasi Password]
    G --> H{8. Validasi Password: Min 15 Karakter & Kompleks?}
    
    H -->|Tidak Sesuai / Lemah| G
    H -->|Valid| I[9. Hash Password Baru Bcrypt<br/>Simpan ke users.password<br/>Update metadata: first_login = false<br/>Catat password_last_changed_at]
    
    I --> J[10. Rekam Audit Trail Forensik: FIRST_LOGIN_PASSWORD_RESET]
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

---

## 6. DFD TO-BE LEVEL 2 — SUB-PROSES 5.0: TATA KELOLA METADATA & MANAJEMEN PENGGUNA

Dekomposisi rinci dari proses tata kelola kamus metadata, hot-reload, dan administrasi pemulihan lupa sandi operator:

```mermaid
flowchart LR
    E2["🔑 SUPER ADMIN"]
    D4[("D4: sqlite_master & sessions")]
    D6[("D6: metadata_libraries")]
    D7[("D7: forensic_audit_logs")]

    P51(("5.1<br/>RBAC Gatekeeper &<br/>Role Authority Check"))
    P52(("5.2<br/>Metadata Library<br/>Dictionary Editor"))
    P53(("5.3<br/>JSON Schema &<br/>Integrity Validator"))
    P54(("5.4<br/>Hot-Reload Engine &<br/>Memory Invalidator"))
    P55(("5.5<br/>Operator Management &<br/>Admin-Assisted Reset"))
    P56(("5.6<br/>Forensic Audit Trail<br/>Logger (BSSN)"))

    E2 -->|"Permintaan Akses Admin"| P51
    P51 -->|"Otorisasi Sah (Role=super_admin)"| P52
    P51 -->|"Otorisasi Sah (Role=super_admin)"| P55
    P51 -.->|"Akses Ilegal (403 Forbidden)"| E2

    %% Alur Kamus Data
    P52 -->|"Form Edit / Tambah Opsi Label"| P53
    P53 -->|"Skema Tervalidasi"| P54
    P54 -->|"Tulis Versi Aktif Baru"| D6
    P54 -->|"Trigger Catat Jejak Revisi"| P56
    P54 -->|"Live Hot-Reload ke DuckDB Engine"| E2

    %% Alur Reset Password Operator
    P55 -->|"Pilih Operator & Klik Reset Password"| D4
    P55 -->|"Terbitkan Password Sementara & first_login: true"| D4
    P55 -->|"Trigger Catat Audit Reset User"| P56
    P55 -->|"Tampilkan Password Sementara ke Layar"| E2

    %% Alur Audit Log
    P56 -->|"Insert Immutable Audit Row (Who, What, Diff, IP)"| D7

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E2 entity;
    class P51,P52,P53,P54,P55,P56 process;
    class D4,D6,D7 store;
```

---

## 7. SKEMA ERD TERPADU RBAC, FORENSIK & BACKUP

Skema relasional lengkap pada SQLite dan DuckDB yang mengimplementasikan standar BSSN No. 4/2021 dan UU PDP No. 27/2022:

```mermaid
erDiagram
    users ||--o{ audit_logs : "generates_event"
    users ||--o{ metadata_libraries : "manages_library"
    users ||--o{ backups : "triggers_backup"
    metadata_libraries ||--o{ audit_logs : "tracked_in"

    users {
        bigint id PK
        string name "Nama Lengkap Pengguna"
        string email UK "Alamat Email Dinas (Login ID)"
        string role "ENUM('super_admin', 'operator')"
        string password "Bcrypt Hash (Cost >= 12)"
        json metadata "JSON: first_login, password_last_changed_at, dsb."
        string two_factor_secret "Kunci Rahasia 2FA RFC 6238 (Encrypted)"
        json two_factor_recovery_codes "Kunci Cadangan 2FA (Encrypted)"
        timestamp two_factor_confirmed_at "Stempel Aktivasi 2FA"
        timestamp last_login_at "Waktu Login Terakhir"
        string last_login_ip "IP Akses Terakhir"
        timestamp created_at
        timestamp updated_at
    }

    audit_logs {
        bigint id PK
        bigint user_id FK "ID Pengguna Pelaku Aksi"
        string action "LOGIN, LOGOUT, FORCE_RESET, UPDATE_LIBRARY, BACKUP, RESET_USER"
        string description "Deskripsi Rinci Peristiwa Forensik"
        json metadata "Payload Sebelum/Sesudah & Parameter Tambahan"
        string ip_address "Alamat IP Klien"
        string user_agent "Identitas Peramban / OS"
        timestamp created_at "Stempel Waktu Presisi (UTC+7)"
    }

    metadata_libraries {
        bigint id PK
        string library_key UK "Contoh: sinonim_bps_48, transliterasi_fk"
        string library_name "Label Display Kamus"
        string category "Kategori: Sanitasi, Aset, Demografi, Wilayah"
        json schema_definition "Definisi Struktur Aturan / Label Deskriptif"
        integer version "Nomor Versi Inkremental"
        boolean is_active "Status Aktif (1=Aktif, 0=Arsip)"
        bigint updated_by_user_id FK "Super Admin Pengubah"
        timestamp created_at
        timestamp updated_at
    }

    backups {
        bigint id PK
        bigint user_id FK "Super Admin Pembuat Cadangan"
        string filename UK "Nama Berkas Cadangan (.padubak)"
        string file_path "Lokasi Penyimpanan di Server"
        bigint file_size "Ukuran Berkas dalam Byte"
        string checksum_sha256 "Hash Integritas SHA-256"
        string encryption_method "AES-256-CBC (Militer)"
        timestamp created_at "Waktu Selesai Pencadangan"
    }
```

---

## 8. Rangkuman Panduan Penggunaan Berkas

| Kebutuhan Pembaca / Auditor | Berkas yang Digunakan |
| :--- | :--- |
| **Pemeriksaan Alur Standar Operator Saja** | `Padu_Diagram.drawio`, `swimlane_tobe_padu.drawio`, `Dokumentasi_DFD_PADU.md` |
| **Pemeriksaan Alur Lengkap Termasuk Super Admin & RBAC** | `Padu_Diagram_SuperAdmin.drawio`, `swimlane_tobe_superadmin.drawio`, `Dokumentasi_SuperAdmin_PADU.md` |
| **Dokumen Proposal Formal & Standar Regulasi** | `PROPOSAL_UPDATE_SELANJUTNYA_PADU.md`, `UPDATE_SELANJUTNYA.md` |

Dengan arsitektur ini, seluruh tata kelola hak istimewa (*privileged administrative access*), audit forensik siber, pemulihan bencana server, dan perlindungan privasi data kependudukan telah terisolasi, terdokumentasi rapi, dan terbukti siap operasional.
