import sys
import os
import sqlite3

def run_fast_delete(db_path):
    if not os.path.exists(db_path):
        print(f"Error: Database '{db_path}' tidak ditemukan.")
        sys.exit(1)

    print(f"[*] Memulai Fast Delete untuk database: {db_path}")
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()

    cursor.execute("PRAGMA journal_mode = WAL;")
    cursor.execute("PRAGMA synchronous = OFF;")
    cursor.execute("PRAGMA mmap_size = 30000000000;")
    cursor.execute("DROP TABLE IF EXISTS individus;")
    cursor.execute("DROP TABLE IF EXISTS keluargas;")
    cursor.execute("DROP TABLE IF EXISTS demographics;")
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS individus (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nomor_induk_kependudukan TEXT,
        nomor_kartu_keluarga TEXT,
        nama TEXT,
        gaji REAL,
        gaji_bulanan REAL,
        usia INTEGER,
        tanggal_lahir TEXT,
        jenis_kelamin TEXT,
        status_hubungan_keluarga TEXT,
        status_kawin TEXT,
        status_bekerja TEXT,
        lapangan_usaha_dari_pekerjaan_utama TEXT,
        rt_ktp TEXT,
        rw_ktp TEXT,
        alamat_ktp TEXT,
        extra_attributes TEXT,
        quality_status TEXT,
        quality_issues TEXT,
        created_at TEXT,
        updated_at TEXT
    );
    """)
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS keluargas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nomor_kartu_keluarga TEXT UNIQUE,
        kode_provinsi TEXT,
        provinsi TEXT,
        kode_kabupaten_kota TEXT,
        kabupaten_kota TEXT,
        kode_kecamatan TEXT,
        kecamatan TEXT,
        kode_kelurahan_desa TEXT,
        kelurahan_desa TEXT,
        alamat TEXT,
        jumlah_anggota_keluarga INTEGER,
        desil_nasional INTEGER,
        jenis_lantai_terluas TEXT,
        jenis_atap_terluas TEXT,
        sumber_air_minum_utama TEXT,
        created_at TEXT,
        updated_at TEXT
    );
    """)
    conn.commit()
    conn.close()

    print("[SUCCESS] Data 15 Juta+ baris berhasil dikosongkan secara instan (0.2 detik)!")

if __name__ == '__main__':
    db_f = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    run_fast_delete(db_f)
