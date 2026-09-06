import time
import sqlite3
import os

db_path = 'scratch/test_index_benchmark.sqlite'

def run_test(drop_first):
    if os.path.exists(db_path): os.remove(db_path)
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute("PRAGMA page_size = 65536;")
    cursor.execute("PRAGMA journal_mode = OFF;")
    cursor.execute("PRAGMA synchronous = OFF;")
    
    cursor.execute("""
    CREATE TABLE individus (
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

    if not drop_first:
        # Create indexes BEFORE insert (simulates existing populated database)
        cursor.execute("CREATE INDEX idx_individus_nik ON individus(nomor_induk_kependudukan);")
        cursor.execute("CREATE INDEX idx_individus_kk ON individus(nomor_kartu_keluarga);")
        cursor.execute("CREATE INDEX idx_individus_quality ON individus(quality_status);")
        conn.commit()

    # Create 1M row batch tuple
    dummy_row = ('3201011234567890', '3201011234567890', 'Masyarakat', 5000000.0, 5000000.0, 30, '1995-01-01', 'Laki-laki', 'Kepala keluarga', 'Kawin', 'Ya', 'Perdagangan', '001', '001', 'Jl. Mawar', '{}', 'Valid', '[]', '2026-09-06', '2026-09-06')
    rows = [dummy_row] * 1000000

    sql = """
        INSERT INTO individus (
            nomor_induk_kependudukan, nomor_kartu_keluarga, nama, gaji, gaji_bulanan,
            usia, tanggal_lahir, jenis_kelamin, status_hubungan_keluarga, status_kawin,
            status_bekerja, lapangan_usaha_dari_pekerjaan_utama, rt_ktp, rw_ktp,
            alamat_ktp, extra_attributes, quality_status, quality_issues, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    """

    t0 = time.time()
    cursor.executemany(sql, rows)
    conn.commit()
    t_insert = time.time() - t0

    t1 = time.time()
    if drop_first:
        cursor.execute("CREATE INDEX idx_individus_nik ON individus(nomor_induk_kependudukan);")
        cursor.execute("CREATE INDEX idx_individus_kk ON individus(nomor_kartu_keluarga);")
        cursor.execute("CREATE INDEX idx_individus_quality ON individus(quality_status);")
        conn.commit()
    t_index = time.time() - t1

    print(f"[{'No Indexes During Insert' if drop_first else 'With Indexes Active During Insert'}] Insert Time: {t_insert:.2f}s | Index Build Time: {t_index:.2f}s | TOTAL: {t_insert + t_index:.2f}s")
    conn.close()
    if os.path.exists(db_path): os.remove(db_path)

print("Running test WITH indexes active during insert...")
run_test(drop_first=False)

print("Running test WITHOUT indexes during insert (Drop & Rebuild)...")
run_test(drop_first=True)
