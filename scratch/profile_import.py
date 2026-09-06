import sys
import os
import time
import json
import sqlite3
import pyarrow.csv as pcsv
import pandas as pd
from datetime import datetime

csv_path = 'src-dtsen/sample_1_juta_data.csv'
db_path = 'scratch/test_profile.sqlite'

if os.path.exists(db_path):
    os.remove(db_path)

conn = sqlite3.connect(db_path)
cursor = conn.cursor()
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

cursor.execute("PRAGMA page_size = 65536;")
cursor.execute("PRAGMA journal_mode = OFF;")
cursor.execute("PRAGMA synchronous = OFF;")
cursor.execute("PRAGMA temp_store = MEMORY;")
cursor.execute("PRAGMA cache_size = -4000000;")
cursor.execute("PRAGMA mmap_size = 30000000000;")

print("=== START PROFILING ===", flush=True)

# Step 1: Read CSV with PyArrow & Pandas
t0 = time.time()
parse_options = pcsv.ParseOptions(invalid_row_handler=lambda e: 'skip')
table = pcsv.read_csv(csv_path, parse_options=parse_options)
df = table.to_pandas()
t1 = time.time()
print(f"Step 1: PyArrow Read CSV & to_pandas: {t1 - t0:.3f} sec", flush=True)

# Step 2: Vectorized validation
norm_map = {c: str(c).strip().lower().replace(' ', '_').replace('.', '_') for c in df.columns}
df.rename(columns=norm_map, inplace=True)
norm_headers = list(df.columns)

def get_col_name(candidates):
    for cand in candidates:
        if cand in norm_headers:
            return cand
    return None

nik_c = get_col_name(['nomor_induk_kependudukan', 'nik'])
kk_c = get_col_name(['nomor_kartu_keluarga', 'no_kk', 'kk'])
nama_c = get_col_name(['nama', 'nama_lengkap'])
tgl_c = get_col_name(['tanggal_lahir', 'tgl_lahir'])
jk_c = get_col_name(['jenis_kelamin', 'jk', 'gender'])
st_kawin_c = get_col_name(['status_kawin', 'status_pernikahan'])
st_kerja_c = get_col_name(['status_bekerja', 'pekerjaan', 'status_kerja'])
gaji_c = get_col_name(['gaji', 'gaji_bulanan', 'pendapatan', 'salary', 'income'])
usia_c = get_col_name(['usia', 'umur', 'age'])
desil_c = get_col_name(['desil_nasional', 'desil', 'desil_kesejahteraan'])
kec_c = get_col_name(['kecamatan'])
prov_c = get_col_name(['provinsi'])

total_rows = len(df)

nik_s = df[nik_c].astype(str).str.strip() if nik_c else pd.Series([f"320101{i%28+1:02d}90{i:06d}" for i in range(total_rows)])
kk_s = df[kk_c].astype(str).str.strip() if kk_c else pd.Series([f"320101201018{i%9000+1000:04d}" for i in range(total_rows)])
nama_s = df[nama_c].astype(str).str.strip() if nama_c else pd.Series(['Masyarakat'] * total_rows)

tgl_s = df[tgl_c].astype(str).str.strip() if tgl_c else pd.Series([None] * total_rows)
jk_s = df[jk_c].astype(str).str.strip() if jk_c else pd.Series([None] * total_rows)
st_kawin_s = df[st_kawin_c].astype(str).str.strip() if st_kawin_c else pd.Series([None] * total_rows)
st_kerja_s = df[st_kerja_c].astype(str).str.strip() if st_kerja_c else pd.Series([None] * total_rows)
gaji_s = pd.to_numeric(df[gaji_c], errors='coerce') if gaji_c else pd.Series([None] * total_rows)
usia_s = pd.to_numeric(df[usia_c], errors='coerce') if usia_c else pd.Series([None] * total_rows)
desil_s = pd.to_numeric(df[desil_c], errors='coerce').fillna(1).astype(int) if desil_c else pd.Series([1] * total_rows)

kec_s = df[kec_c].astype(str).str.strip() if kec_c else pd.Series(['Cilandak'] * total_rows)
prov_s = df[prov_c].astype(str).str.strip() if prov_c else pd.Series(['DKI Jakarta'] * total_rows)

bad_nik = (nik_s.str.len() != 16) | (~nik_s.str.isdigit())
bad_kk = (kk_s.str.len() != 16) | (~kk_s.str.isdigit())
bad_nama = nama_s.str.contains(r'\d', regex=True)
bad_desil = (desil_s < 1) | (desil_s > 10)

status_arr = ['Valid'] * total_rows
issues_arr = ['[]'] * total_rows

critical_mask = bad_nik | bad_kk | bad_nama | bad_desil
critical_indices = df.index[critical_mask]

for idx in critical_indices:
    status_arr[idx] = 'Critical'
    row_issues = []
    if bad_nik.iat[idx]:
        row_issues.append(f"[CRITICAL] NIK tidak valid ('{nik_s.iat[idx]}'). Panjang harus persis 16 digit angka")
    if bad_kk.iat[idx]:
        row_issues.append(f"[CRITICAL] Nomor KK tidak valid ('{kk_s.iat[idx]}'). Panjang harus persis 16 digit angka")
    if bad_nama.iat[idx]:
        row_issues.append(f"[CRITICAL] Nama Lengkap ('{nama_s.iat[idx]}') mengandung angka")
    if bad_desil.iat[idx]:
        row_issues.append(f"[CRITICAL] Desil Kesejahteraan '{desil_s.iat[idx]}' di luar jangkauan valid (1-10)")
    issues_arr[idx] = json.dumps(row_issues)

t2 = time.time()
print(f"Step 2: Vectorized validation & issues json: {t2 - t1:.3f} sec", flush=True)

# Step 3: Constructing Python List of Tuples
now_str = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
batch_individus = [
    (
        nik_s.iat[i], kk_s.iat[i], nama_s.iat[i],
        float(gaji_s.iat[i]) if pd.notna(gaji_s.iat[i]) else None,
        float(gaji_s.iat[i]) if pd.notna(gaji_s.iat[i]) else None,
        int(usia_s.iat[i]) if pd.notna(usia_s.iat[i]) else None,
        tgl_s.iat[i] if tgl_s.iat[i] != 'None' else None,
        jk_s.iat[i] if jk_s.iat[i] != 'None' else None,
        'Kepala keluarga',
        st_kawin_s.iat[i] if st_kawin_s.iat[i] != 'None' else None,
        st_kerja_s.iat[i] if st_kerja_s.iat[i] != 'None' else None,
        'Perdagangan besar', '001', '001', 'Jl. Mawar No. 1',
        '{}', status_arr[i], issues_arr[i], now_str, now_str
    )
    for i in range(total_rows)
]
t3 = time.time()
print(f"Step 3: Constructing list of tuples via python loop (.iat[i]): {t3 - t2:.3f} sec", flush=True)

# Step 4: Database Executemany Inserts
sql_individu = """
    INSERT OR IGNORE INTO individus (
        nomor_induk_kependudukan, nomor_kartu_keluarga, nama, gaji, gaji_bulanan,
        usia, tanggal_lahir, jenis_kelamin, status_hubungan_keluarga, status_kawin,
        status_bekerja, lapangan_usaha_dari_pekerjaan_utama, rt_ktp, rw_ktp,
        alamat_ktp, extra_attributes, quality_status, quality_issues, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
"""
cursor.executemany(sql_individu, batch_individus)
conn.commit()
t4 = time.time()
print(f"Step 4: SQLite executemany 1M rows: {t4 - t3:.3f} sec", flush=True)

# Step 5: Index Creation
cursor.execute("CREATE INDEX IF NOT EXISTS idx_individus_nik ON individus(nomor_induk_kependudukan);")
cursor.execute("CREATE INDEX IF NOT EXISTS idx_individus_kk ON individus(nomor_kartu_keluarga);")
cursor.execute("CREATE INDEX IF NOT EXISTS idx_individus_quality ON individus(quality_status);")
conn.commit()
t5 = time.time()
print(f"Step 5: Index creation: {t5 - t4:.3f} sec", flush=True)
print(f"=== TOTAL TIME: {t5 - t0:.3f} sec ===", flush=True)

conn.close()
if os.path.exists(db_path):
    os.remove(db_path)
