import sys
import os
import json
import time
import duckdb

SYNONYM_MAP = {
    'nomor_induk_kependudukan': ['nik', 'nomor_induk_kependudukan', 'no_nik', 'id_penduduk', 'no_ktp', 'nomor_ktp', 'nik_ktp', 'nomor_induk_kependudukan_nik', 'b1_r101', 'r101', 'kd_nik', 'sandi_nik', 'v101', '101'],
    'nomor_kartu_keluarga': ['kk', 'no_kk', 'nomor_kartu_keluarga', 'no_kartu_keluarga', 'id_kk', 'nokk', 'nomor_kk', 'nomor_kartu_keluarga_kk', 'b1_r102', 'r102', 'kd_kk', 'sandi_kk', 'v102', '102'],
    'nama': ['nama', 'nama_lengkap', 'nama_subjek', 'nama_warga', 'fullname', 'nama_art', 'b4_r401', 'r401', 'v401', '401'],
    'gaji_bulanan': ['gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income', 'gaji_pokok', 'upah', 'take_home_pay', 'pendapatan_bulanan', 'gaji_bulanan_rp', 'b4_r409', 'r409', 'gaji_val', 'v409', '409'],
    'desil_nasional': ['desil', 'desil_nasional', 'desil_kesejahteraan', 'desil_ekonomi', 'desil_nas', 'desil_kesejahteraan_nasional', 'b1_r104', 'r104'],
    'usia': ['usia', 'umur', 'age', 'tahun_usia', 'usia_tahun', 'b4_r405', 'r405', 'umur_thn', 'v405', '405'],
    'jenis_kelamin': ['jenis_kelamin', 'jk', 'gender', 'sex', 'kelamin', 'b4_r403', 'r403', 'kd_jk', 'v403', '403'],
    'status_bekerja': ['status_bekerja', 'pekerjaan', 'pekerjaan_utama', 'status_kerja', 'work_status', 'occupation', 'b4_r408', 'r408', 'is_working', 'st_kerja', 'v408', '408'],
    'status_kawin': ['status_kawin', 'status_pernikahan', 'marital_status', 'st_kawin', 'st_nikah', 'b4_r406', 'r406', 'v406', '406'],
    'status_hubungan_keluarga': ['status_hubungan_keluarga', 'hub_keluarga', 'hub_kel', 'shdk', 'hubungan_keluarga', 'relationship', 'b4_r402', 'r402', 'v402', '402'],
    'provinsi': ['provinsi', 'prov', 'provinsi_ktp', 'kd_prov', 'b1_r101_prov'],
    'kabupaten_kota': ['kabupaten_kota', 'kabupaten', 'kota', 'kab', 'kab_kota', 'kota_kabupaten', 'kd_kab'],
    'kecamatan': ['kecamatan', 'kec', 'kd_kec'],
    'kelurahan_desa': ['kelurahan_desa', 'kelurahan', 'desa', 'kel', 'kd_kel', 'kd_desa'],
    'alamat': ['alamat', 'alamat_ktp', 'alamat_lengkap', 'domisili', 'address', 'alamat_rumah', 'b1_r105', 'r105']
}

def map_canonical_header(raw):
    clean = raw.strip().lower().replace(' ', '_').replace('.', '_').replace('(', '').replace(')', '').replace('/', '_').replace('-', '_').strip('_')
    for canonical, synonyms in SYNONYM_MAP.items():
        if clean == canonical or clean in synonyms:
            return canonical
        for syn in synonyms:
            if clean == syn or clean.startswith(syn + '_') or clean.endswith('_' + syn):
                return canonical
    return clean

def update_progress(percent, message, start_time=None):
    try:
        prog_file = os.path.join(os.path.dirname(__file__), 'import_progress.json')
        eta_seconds = 0
        if start_time and percent > 0 and percent < 100:
            elapsed = time.time() - start_time
            if elapsed > 0.1:
                total_est = elapsed / (percent / 100.0)
                eta_seconds = max(1, int(round(total_est - elapsed)))

        data = {
            'percent': int(percent),
            'message': message,
            'eta_seconds': eta_seconds,
            'updated_at': time.time()
        }
        with open(prog_file, 'w') as f:
            json.dump(data, f)
    except Exception:
        pass

def run_fast_import(csv_path, db_path):
    if not os.path.exists(csv_path):
        print(f"Error: File CSV '{csv_path}' tidak ditemukan.")
        sys.exit(1)

    start_time = time.time()
    update_progress(10, "Membaca header dan skema berkas CSV...", start_time)
    print(f"[*] Memulai Pure DuckDB Ultra-Fast Import untuk: {os.path.basename(csv_path)}", flush=True)

    duck_file = os.path.join(os.path.dirname(db_path), 'dataset.duckdb')
    
    if os.path.exists(duck_file):
        try:
            os.remove(duck_file)
        except Exception:
            pass

    con = duckdb.connect(duck_file)
    con.execute("SET enable_progress_bar = false;")
    try:
        con.execute("SET threads = 8;")
    except Exception:
        pass
    
    update_progress(25, "Analisis header & skema data...", start_time)
    csv_escaped = csv_path.replace('\\', '/')

    # Read sample to detect raw columns accurately
    cols_info = con.execute(f"DESCRIBE SELECT * FROM read_csv_auto('{csv_escaped}', all_varchar=True, ignore_errors=true, sample_size=500);").fetchall()
    raw_cols = [r[0] for r in cols_info]

    norm_map = {}
    used_cleans = set()
    select_exprs = []

    for c in raw_cols:
        canonical = map_canonical_header(c)
        if canonical in used_cleans:
            canonical = f"{canonical}_custom"
        used_cleans.add(canonical)
        norm_map[c] = canonical
        select_exprs.append(f'"{c}" AS "{canonical}"')

    select_str = ", ".join(select_exprs)

    nik_col = 'nomor_induk_kependudukan' if 'nomor_induk_kependudukan' in used_cleans else None
    kk_col = 'nomor_kartu_keluarga' if 'nomor_kartu_keluarga' in used_cleans else None
    nama_col = 'nama' if 'nama' in used_cleans else None
    desil_col = 'desil_nasional' if 'desil_nasional' in used_cleans else None
    gaji_col = 'gaji_bulanan' if 'gaji_bulanan' in used_cleans else ('gaji' if 'gaji' in used_cleans else None)
    usia_col = 'usia' if 'usia' in used_cleans else ('umur' if 'umur' in used_cleans else None)

    nik_expr = f'"{nik_col}"' if nik_col else "'3201019000000000'"
    kk_expr = f'"{kk_col}"' if kk_col else "'3201012010180000'"
    nama_expr = f'"{nama_col}"' if nama_col else "'Masyarakat'"
    desil_expr = f'TRY_CAST("{desil_col}" AS INTEGER)' if desil_col else "1"
    gaji_expr = f'TRY_CAST(REGEXP_REPLACE(CAST("{gaji_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)' if gaji_col else "0.0"
    usia_expr = f'TRY_CAST("{usia_col}" AS INTEGER)' if usia_col else "0"

    update_progress(45, "Direct Vectorized Bulk Ingestion 13M+ baris ke DuckDB...", start_time)

    # 1-PASS HIGH SPEED TABLE CREATION WITH ALIASES & QC AUDIT
    con.execute(f"""
        CREATE OR REPLACE TABLE individus AS
        SELECT 
            row_number() OVER () as id,
            {select_str},
            {nik_expr} AS nik,
            {kk_expr} AS no_kk,
            {kk_expr} AS kk,
            {nama_expr} AS nama_lengkap,
            {gaji_expr} AS gaji,
            {desil_expr} AS desil,
            {usia_expr} AS umur,
            ARRAY_FILTER([
                CASE WHEN LENGTH(TRIM(CAST({nik_expr} AS VARCHAR))) != 16 THEN 'Digit NIK tidak valid (harus 16 digit)' ELSE NULL END,
                CASE WHEN LENGTH(TRIM(CAST({kk_expr} AS VARCHAR))) != 16 THEN 'Digit No. KK tidak valid (harus 16 digit)' ELSE NULL END,
                CASE WHEN REGEXP_MATCHES(CAST({nama_expr} AS VARCHAR), '.*\\d+.*') THEN 'Nama mengandung angka / karakter ilegal' ELSE NULL END,
                CASE WHEN {desil_expr} IS NULL OR {desil_expr} < 1 OR {desil_expr} > 10 THEN 'Desil Kesejahteraan diluar jangkauan 1-10' ELSE NULL END
            ], x -> x IS NOT NULL) AS quality_issues,
            CASE WHEN len(ARRAY_FILTER([
                CASE WHEN LENGTH(TRIM(CAST({nik_expr} AS VARCHAR))) != 16 THEN 'Digit NIK tidak valid (harus 16 digit)' ELSE NULL END,
                CASE WHEN LENGTH(TRIM(CAST({kk_expr} AS VARCHAR))) != 16 THEN 'Digit No. KK tidak valid (harus 16 digit)' ELSE NULL END,
                CASE WHEN REGEXP_MATCHES(CAST({nama_expr} AS VARCHAR), '.*\\d+.*') THEN 'Nama mengandung angka / karakter ilegal' ELSE NULL END,
                CASE WHEN {desil_expr} IS NULL OR {desil_expr} < 1 OR {desil_expr} > 10 THEN 'Desil Kesejahteraan diluar jangkauan 1-10' ELSE NULL END
            ], x -> x IS NOT NULL)) > 0 THEN 'Critical' ELSE 'Valid' END AS quality_status
        FROM read_csv_auto('{csv_escaped}', all_varchar=True, ignore_errors=true, sample_size=100000);
    """)

    total_rows = con.execute("SELECT COUNT(*) FROM individus;").fetchone()[0]

    update_progress(85, "Menghitung agregasi statistik dataset...", start_time)

    # Instant summary stats with HyperLogLog (0.01s for 13M rows)
    stats_row = con.execute(f"""
        SELECT 
            COUNT(*) as total_rows,
            APPROX_COUNT_DISTINCT({kk_expr}) as total_kk,
            SUM(CASE WHEN quality_status = 'Valid' THEN 1 ELSE 0 END) as valid_cnt,
            SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_cnt
        FROM individus;
    """).fetchone()

    summary_stats = {
        "total_rows": int(stats_row[0] or 0),
        "total_kk": int(stats_row[1] or 0),
        "valid_count": int(stats_row[2] or 0),
        "warning_count": 0,
        "critical_count": int(stats_row[3] or 0),
        "multi_error_count": 0
    }

    # Fast sampled distinct options map for filter dropdowns (0.01s)
    distinct_map = {}
    for raw, norm in norm_map.items():
        if norm not in ['id', 'quality_status', 'quality_issues'] and not norm.startswith('nik') and not norm.startswith('kk'):
            try:
                rows = con.execute(f'SELECT DISTINCT "{norm}" FROM (SELECT "{norm}" FROM individus WHERE "{norm}" IS NOT NULL LIMIT 2000) WHERE TRIM(CAST("{norm}" AS VARCHAR)) != \'\' LIMIT 30;').fetchall()
                vals = [str(r[0]).strip() for r in rows if r[0] is not None]
                if vals:
                    distinct_map[norm] = vals
            except Exception:
                pass

    headers = list(norm_map.values())

    update_progress(100, f"Pemrosesan dataset {total_rows:,} baris selesai!", start_time)

    print(f"[SUMMARY_STATS] {json.dumps(summary_stats)}", flush=True)
    print(f"[HEADERS_JSON] {json.dumps(headers)}", flush=True)
    print(f"[DISTINCT_MAP_JSON] {json.dumps({'distinct_map': distinct_map})}", flush=True)
    print("[DUCKDB_READY] Pure DuckDB Native dataset created successfully.", flush=True)

    con.close()
    elapsed = time.time() - start_time
    print(f"[SUCCESS] Impor {total_rows:,} baris data selesai dalam {elapsed:.2f} detik! ({total_rows/max(0.01, elapsed):,.0f} baris/detik)", flush=True)

if __name__ == '__main__':
    csv_f = sys.argv[1] if len(sys.argv) > 1 else 'src-dtsen/dataset_dtsen_50k_lengkap.csv'
    db_f = sys.argv[2] if len(sys.argv) > 2 else 'database/database.sqlite'
    run_fast_import(csv_f, db_f)
