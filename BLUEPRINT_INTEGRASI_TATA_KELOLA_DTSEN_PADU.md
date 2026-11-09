# BLUEPRINT INTEGRASI TATA KELOLA DTSEN KE DALAM SISTEM INFORMASI PADU
## Rencana Aksi Pembaruan Sistem: Kepatuhan UU PDP No. 27/2022, Peraturan BSSN No. 4/2021 & SOP Air-Gapped V2.0
**Tanggal Penyusunan**: 12 Oktober 2026  
**Status**: Usulan Arsitektur & Ide Pengembangan Terarsip (*Archived System Blueprint*)  
**Konteks**: Integrasi Ekosistem Tata Kelola Folder `belajar/` ke Dalam Mesin Analitik PADU v2.0 Enterprise  

---

## 📌 Ringkasan Eksekutif
Dokumen ini merekam secara permanen seluruh ide, konsep arsitektur, dan peta jalan (*roadmap*) pengintegrasian instrumen tata kelola **Data Tunggal Sosial dan Ekonomi Nasional (DTSEN) Level 4** ke dalam **Sistem Informasi PADU (Pengolah dan Analisis Data Terpadu)**.

Dengan diterapkannya cetak biru ini di masa mendatang, PADU tidak hanya berfungsi sebagai mesin komputasi analitik cepat (*DuckDB OLAP Engine*), tetapi juga bertindak sebagai benteng pertahanan hukum (*legal defense*) dan keamanan data yang sepenuhnya memenuhi standar **Badan Siber dan Sandi Negara (BSSN)** serta **Undang-Undang Pelindungan Data Pribadi (UU PDP)**.

---

## 1. Lima Pilar Fitur Baru yang Direncanakan

### 🛡️ Pilar 1: Modul Ingesti Air-Gapped (Dual-Hash & 7-Zip Gatekeeper)
*Mengadopsi SOP-V2-Dtsen & BPMN-V2 Double Envelope Protocol.*
1. **Validasi Integritas Dual-Hash SHA-256**:
   - Operator memasukkan file transfer `.7z` terenkripsi ke folder `src-dtsen/`.
   - Form Ingesti PADU mewajibkan pengisian `HASH-ORIGINAL` dan `HASH-ENCRYPTED` yang tertera pada BAST fisik.
   - PADU secara otomatis mengkalkulasi hash file masukan sebelum membuka form kata sandi.
2. **Dekripsi Otomatis Berbasis Password Out-of-Band**:
   - Operator memasukkan kata sandi 16 karakter acak yang diterima via amplop fisik bersegel.
   - Backend PADU mengeksekusi ekstraksi luring dan menghitung `HASH-DECRYPTED`.
3. **Mekanisme Fail-Safe Auto-Purge**:
   - Jika `HASH-DECRYPTED` tidak 100% sama dengan `HASH-ORIGINAL`, proses dibatalkan seketika, berkas sementara dihapus permanen, dan insiden otomatis dicatat ke log forensik.

---

### 👥 Pilar 2: Restrukturisasi 4 Peran RBAC & Enforced NIK Masking
*Mengadopsi Lampiran-Ropa-Rbac & BAST-V2.*
PADU yang sebelumnya hanya memiliki peran *Superadmin* dan *Operator* ditingkatkan menjadi 4 peran formal:

| Peran Pengguna | Kewenangan Utama | Pembatasan Akses Data Pribadi |
| :--- | :--- | :--- |
| **👑 Data Owner**<br>*(Kepala OPD)* | Menyetujui KAK, menandatangani BAST, dashboard eksekutif capaian program, otorisasi ekspor data agregat. | Membaca ringkasan statistik makro & agregat wilayah. |
| **🔑 Data Custodian**<br>*(Kabid Data / Admin Teknis)* | Manajemen ingesti data, validasi dual-hash, enkripsi cadangan sistem AES-256, pemeliharaan basis data. | Membaca metadata teknis, struktur kolom, dan hash integritas. |
| **📊 Data User / Analyst**<br>*(Operator / Staf Analis)* | Mengakses 12 modul analisis DuckDB OLAP, filter data, pencarian kriteria bansos, visualisasi metrik. | **Strict NIK Masking**: NIK dikunci hanya tampil 4 digit terakhir (`************1234`). Tombol unmasking dan ekspor data mentah dimatikan. |
| **🛡️ Security Auditor**<br>*(DPO Provinsi & CSIRT)* | Memeriksa log audit forensik, mengunduh rekam jejak aktivitas, verifikasi checklist steril dan sertifikat TTE. | **Zero-Data Policy**: Sama sekali diblokir dari melihat tabel data penduduk. |

---

### ⏳ Pilar 3: Manajemen Siklus Hidup Data & Pemusnahan (NIST SP 800-88)
*Mengadopsi Klausul Pasal 6 BAST-V2 & Standar Sanitasi Data.*
1. **Retention Countdown Timer**:
   - Setiap kali dataset di-ingest, sistem mencatat masa retensi sesuai kesepakatan KAK/ROPA (contoh: 90 hari).
   - Muncul indikator visual di header PADU: *"Sisa Masa Retensi Dataset: 38 Hari"*.
2. **Fitur Secure Purge (Pemusnahan Mandiri)**:
   - Ketika masa retensi kedaluwarsa, modul analisis dikunci dan beralih ke mode pemusnahan data.
   - Tombol *Musnahkan Data* menjalankan prosedur *secure file wipe / multi-pass overwrite* pada file `dataset.duckdb` dan file sumber (menggunakan algoritma setara NIST SP 800-88 / SDelete).
3. **Generator Berita Acara Pemusnahan**:
   - PADU secara otomatis menerbitkan dokumen Word/PDF draf Berita Acara Pemusnahan Data lengkap dengan timestamp eksekusi, hash konfirmasi penghapusan, dan kolom tanda tangan saksi Inspektorat & DPO.

---

### 📋 Pilar 4: Tab Tata Kelola Digital & Verifikasi Stop-Gate
*Mengadopsi SE-Sekda-Juknis-Dtsen.*
Menambahkan menu baru di navigasi utama PADU: **"Tata Kelola & Kepatuhan"**:
1. **Stop-Gate Document Reader**:
   - Repositori pencatat nomor registrasi dan berkas legalitas: Nomor KAK, ROPA, hasil penilaian risiko DPIA, dan BAST V2.
   - Ingesti data terkunci sampai nomor registrasi legalitas diisi dengan sah.
2. **Katalog Aset BMD & Host Verifier**:
   - Mencatat identitas laptop operasional: Hostname, MAC Address, dan Nomor Registrasi Stiker BMD Pemprov Banten.
3. **Generator Laporan Triwulan Walidata**:
   - Satu tombol untuk mengompilasi laporan triwulan kemajuan pemanfaatan data ke Diskominfo tanpa membocorkan identitas pribadi warga (hanya jumlah record, hasil seleksi program, dan rekapitulasi hash).

---

### 🔍 Pilar 5: Audit Trail Forensik Kebal Manipulasi (BSSN-Grade)
*Mengadopsi Compliance-Bssn-2021.*
1. **Immutable Chained-Hash Audit Log**:
   - Setiap entri log aktivitas SQLite di-hash berantai dengan entri sebelumnya (`Hash = SHA256(Row_Data + Previous_Hash)`).
   - Modifikasi atau penghapusan manual pada file database akan merusak integritas rantai hash dan langsung terdeteksi saat audit.
2. **Retensi Log 5 Tahun**:
   - Skrip pengarsipan berkala otomatis membungkus log tahunan ke dalam arsip terenkripsi permanen.
3. **Tombol Tanggap Darurat (1x24H Breach Alert Generator)**:
   - Tombol darurat di panel Superadmin yang langsung menyusun ringkasan forensik jika terjadi indikasi anomali (brute force, tampering) untuk dilaporkan ke Walidata & CSIRT Provinsi dalam kurun 1x24 jam.

---

## 2. Peta Jalan Implementasi Bertahap (Roadmap)

```
+---------------------------------------------------------------------------------------------------+
| FASE 1: HARDENING KEAMANAN INGESTI & PRIVASI (Prioritas Utama)                                    |
| • Pembuatan form verifikasi Dual-Hash SHA-256 pada tab Ingesti.                                   |
| • Integrasi modul dekripsi 7-Zip AES-256 lokal via PHP/Python.                                   |
| • Penegakan NIK Masking permanen (4 digit terakhir) untuk role staf/operator.                     |
+---------------------------------------------------------------------------------------------------+
                                                  │
                                                  ▼
+---------------------------------------------------------------------------------------------------+
| FASE 2: TATA KELOLA RETENSI & RBAC ENTERPRISE                                                     |
| • Pemekaran role RBAC menjadi 4 tingkat (Data Owner, Custodian, Analyst, Security Auditor).       |
| • Widget Retention Countdown Timer pada dashboard.                                                |
| • Fitur Pemusnahan Data Standar NIST SP 800-88 & cetak BA Pemusnahan otomatis.                    |
+---------------------------------------------------------------------------------------------------+
                                                  │
                                                  ▼
+---------------------------------------------------------------------------------------------------+
| FASE 3: INTEGRASI TATA KELOLA RESMI & AUDIT FORENSIK BSSN                                         |
| • Tab baru "Tata Kelola & Kepatuhan" (Stop-Gate Reader & Generator Laporan Triwulan).            |
| • Peningkatan arsitektur log SQLite menjadi Immutable Chained-Hash Ledger.                         |
| • Generator Laporan Insiden Darurat 1x24 Jam ke CSIRT Banten.                                    |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Kesimpulan Penyimpanan Ide
Seluruh butir dalam cetak biru ini telah disimpan dan diarsipkan sebagai panduan resmi pengembangan lanjutan PADU. Ide ini siap dieksekusi kapan pun tim pengembang dan pemangku kepentingan menjadwalkan siklus pembaruan fitur (*next development sprint*).
