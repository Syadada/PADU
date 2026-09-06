# Requirements & Persyaratan Sistem - PADU v1.02 (DTSEN 2026 Analytics)

Aplikasi **PADU (Pengolah dan Analisis Data Terpadu)** dirancang agar dapat langsung berjalan **100% OFFLINE** di laptop Windows mana pun dengan spesifikasi minimal berikut:

---

## 💻 1. Persyaratan Perangkat Keras & OS (Hardware & OS):
- **Sistem Operasi**: Windows 10 / Windows 11 (64-bit).
- **RAM**: Minimal 4 GB (Penggunaan RAM aplikasi sangat efisien & stabil).
- **Ruang Penyimpanan (Disk)**: Minimal 2 GB (tergantung ukuran dataset CSV/XLSX yang diimpor).

---

## 🐍 2. Persyaratan Python (High-Speed Engine):
- **Versi Python**: Python 3.8+ (Direkomendasikan **Python 3.11+**).
- **Pustaka Python**: Built-in standard library (`sqlite3`, `csv`, `json`, `time`, `datetime`, `os`, `sys`) + Pustaka Opsional Excel (`pandas`, `pyarrow`, `openpyxl`).
- **File Konfigurasi**: Catatan pustaka tersedia di `requirements.txt`.

---

## 🐘 3. Persyaratan Server Web & PHP:
- **Server Web / PHP**: Portable PHP 8.2 **SUDAH TERBAWA OTOMATIS** di folder `php/` di dalam folder proyek ini.
- **Ekstensi PHP Aktif**: `zip`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, `curl` *(Semua sudah diaktifkan di `php/php.ini`)*.
- **Port Server**: **Port 8000** (`http://127.0.0.1:8000/`).

---

## 🚀 4. Cara Peluncuran Instan (Zero-Config & 100% Offline):
1. Ekstrak folder ZIP proyek di laptop pengguna.
2. Klik ganda berkas peluncur **`JALANKAN_PADU.bat`**.
3. Aplikasi akan otomatis mengaktifkan server di Port 8000 dan membuka browser di **http://127.0.0.1:8000/**!
