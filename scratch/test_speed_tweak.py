import time
import sqlite3
import os

db_path = 'scratch/test_tweak.sqlite'
if os.path.exists(db_path): os.remove(db_path)

conn = sqlite3.connect(db_path)
cursor = conn.cursor()
cursor.execute("PRAGMA page_size = 65536;")
cursor.execute("PRAGMA journal_mode = OFF;")
cursor.execute("PRAGMA synchronous = OFF;")
cursor.execute("PRAGMA temp_store = MEMORY;")
cursor.execute("PRAGMA cache_size = -4000000;")
cursor.execute("PRAGMA mmap_size = 30000000000;")

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

import pyarrow.csv as pcsv
import pandas as pd
import numpy as np

t0 = time.time()
table = pcsv.read_csv('src-dtsen/sample_1_juta_data.csv')
df = table.to_pandas()
t1 = time.time()
print(f"Read CSV: {t1 - t0:.2f}s")

# Vectorized PyArrow or Pandas checks
nik_a = df['nomor_induk_kependudukan'].astype(str).to_numpy()
kk_a = df['nomor_kartu_keluarga'].astype(str).to_numpy()
nama_a = df['nama'].astype(str).to_numpy()
gaji_a = pd.to_numeric(df['gaji_bulanan'], errors='coerce').to_numpy()
desil_a = pd.to_numeric(df['desil_nasional'], errors='coerce').fillna(1).astype(int).to_numpy()

total_rows = len(df)
status_a = np.full(total_rows, 'Valid', dtype=object)
issues_a = np.full(total_rows, '[]', dtype=object)
now_a = np.full(total_rows, '2026-09-06 00:00:00', dtype=object)
empty_a = np.full(total_rows, '{}', dtype=object)
null_a = np.full(total_rows, None, dtype=object)

t2 = time.time()
print(f"Vectorized Prep: {t2 - t1:.2f}s")

rows = list(zip(
    nik_a, kk_a, nama_a, gaji_a, gaji_a, null_a, null_a, null_a, null_a, null_a, null_a, null_a, null_a, null_a, null_a,
    empty_a, status_a, issues_a, now_a, now_a
))
t3 = time.time()
print(f"Zip Rows: {t3 - t2:.2f}s")

sql = """
    INSERT INTO individus (
        nomor_induk_kependudukan, nomor_kartu_keluarga, nama, gaji, gaji_bulanan,
        usia, tanggal_lahir, jenis_kelamin, status_hubungan_keluarga, status_kawin,
        status_bekerja, lapangan_usaha_dari_pekerjaan_utama, rt_ktp, rw_ktp,
        alamat_ktp, extra_attributes, quality_status, quality_issues, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
"""

cursor.executemany(sql, rows)
conn.commit()
t4 = time.time()
print(f"Executemany Insert: {t4 - t3:.2f}s")

cursor.execute("CREATE INDEX idx_individus_nik ON individus(nomor_induk_kependudukan);")
cursor.execute("CREATE INDEX idx_individus_kk ON individus(nomor_kartu_keluarga);")
cursor.execute("CREATE INDEX idx_individus_quality ON individus(quality_status);")
conn.commit()
t5 = time.time()
print(f"Index Create: {t5 - t4:.2f}s")

print(f"Total Time: {t5 - t0:.2f}s")
conn.close()
if os.path.exists(db_path): os.remove(db_path)
