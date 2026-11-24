import sys
import os
import json
import time
import csv
import struct
import re
import zipfile
import xml.etree.ElementTree as ET
import duckdb

SYNONYM_MAP = {
    'nomor_induk_kependudukan': ['nik', 'nomor_induk_kependudukan', 'no_nik', 'id_penduduk', 'no_ktp', 'nomor_ktp', 'nik_ktp', 'nomor_induk_kependudukan_nik', 'kd_nik', 'sandi_nik', 'id_individu'],
    'nomor_kartu_keluarga': ['kk', 'no_kk', 'nomor_kartu_keluarga', 'no_kartu_keluarga', 'id_kk', 'nokk', 'nomor_kk', 'nomor_kartu_keluarga_kk', 'kd_kk', 'sandi_kk', 'id_keluarga'],
    'nama': ['nama', 'nama_lengkap', 'nama_subjek', 'nama_warga', 'fullname', 'nama_art', 'nama_penduduk', 'nama_kepala_keluarga'],
    'gaji_bulanan': ['gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income', 'gaji_pokok', 'upah', 'take_home_pay', 'pendapatan_bulanan', 'gaji_bulanan_rp', 'b4_r409', 'r409', 'gaji_val', 'v409'],
    'desil_nasional': ['desil', 'desil_nasional', 'desil_kesejahteraan', 'desil_ekonomi', 'desil_nas', 'desil_kesejahteraan_nasional'],
    'usia': ['usia', 'umur', 'age', 'tahun_usia', 'usia_tahun', 'b4_r405', 'r405', 'umur_thn', 'v405'],
    'jenis_kelamin': ['jenis_kelamin', 'jk', 'gender', 'sex', 'kelamin', 'b4_r403', 'r403', 'kd_jk', 'v403'],
    'status_bekerja': ['status_bekerja', 'pekerjaan', 'pekerjaan_utama', 'status_kerja', 'work_status', 'occupation', 'b4_r408', 'r408', 'is_working', 'st_kerja', 'v408'],
    'status_kawin': ['status_kawin', 'status_pernikahan', 'marital_status', 'st_kawin', 'st_nikah', 'b4_r406', 'r406', 'v406'],
    'status_hubungan_keluarga': ['status_hubungan_keluarga', 'hub_keluarga', 'hub_kel', 'shdk', 'hubungan_keluarga', 'relationship', 'b4_r402', 'r402', 'v402'],
    'provinsi': ['provinsi', 'prov', 'provinsi_ktp', 'kd_prov', 'b1_r101_prov', 'kode_prov', 'b1_r101', 'r101', 'v101'],
    'kabupaten_kota': ['kabupaten_kota', 'kabupaten', 'kota', 'kab', 'kab_kota', 'kota_kabupaten', 'kd_kab', 'kode_kab', 'b1_r102', 'r102', 'v102'],
    'kecamatan': ['kecamatan', 'kec', 'kd_kec', 'kode_kec', 'b1_r103', 'r103', 'v103'],
    'kelurahan_desa': ['kelurahan_desa', 'kelurahan', 'desa', 'kel', 'kd_kel', 'kd_desa', 'kode_desa', 'b1_r104', 'r104', 'v104'],
    'alamat': ['alamat', 'alamat_ktp', 'alamat_lengkap', 'domisili', 'address', 'alamat_rumah', 'nama_jalan'],
    'status_daerah': ['status_daerah', 'status_daerah_pencacahan', 'r105', 'b1_r105'],
    'status_pemerintahan_desa': ['status_pemerintahan_desa', 'status_desa', 'status_kelurahan', 'r301', 'b1_r301']
}

def map_canonical_header(raw, idx=0):
    if not raw or not str(raw).strip():
        return f"col_{idx+1}"
    
    raw_str = str(raw).strip()
    # Strip DBF/FoxPro/ODS type precision suffixes like ",C,2" or ",N,1,0" or ",N,19,17"
    base = raw_str.split(',')[0].strip()
    
    # Strictly alphanumeric and underscore only
    clean = re.sub(r'[^a-zA-Z0-9_]', '_', base)
    clean = re.sub(r'_+', '_', clean).strip('_').lower()
    if not clean:
        return f"col_{idx+1}"
    if len(clean) > 40:
        clean = clean[:40].rstrip('_')
    if clean[0].isdigit():
        clean = f"col_{clean}"
        
    for canonical, synonyms in SYNONYM_MAP.items():
        if clean == canonical or clean in synonyms:
            return canonical
            
    for canonical, synonyms in SYNONYM_MAP.items():
        for syn in synonyms:
            if len(syn) >= 3 and (clean.startswith(syn + '_') or clean.endswith('_' + syn)):
                # Khusus PODES: pertahankan r101n - r104n sebagai companion name columns
                if clean in ['r101n', 'r102n', 'r103n', 'r104n']:
                    return clean
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
        with open(prog_file, 'w', encoding='utf-8') as f:
            json.dump(data, f)
    except Exception:
        pass

def is_ods_source(file_path):
    if not os.path.exists(file_path):
        return False
    if os.path.isdir(file_path):
        return os.path.exists(os.path.join(file_path, 'content.xml'))
    if file_path.lower().endswith('.ods'):
        return True
    try:
        if zipfile.is_zipfile(file_path):
            with zipfile.ZipFile(file_path, 'r') as z:
                return 'content.xml' in z.namelist()
    except Exception:
        pass
    return False

def convert_ods_to_temp_csv(source_path):
    dir_target = os.path.dirname(source_path) if os.path.isfile(source_path) else source_path
    temp_csv = os.path.join(dir_target, f"_ods_{int(time.time())}.csv")
    ns = {'table': 'urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'text': 'urn:oasis:names:tc:opendocument:xmlns:text:1.0'}
    t_ns = ns['table']
    rep_attr = '{' + t_ns + '}number-columns-repeated'
    
    content_xml_bytes = None
    if os.path.isdir(source_path):
        c_path = os.path.join(source_path, 'content.xml')
        with open(c_path, 'rb') as f:
            content_xml_bytes = f.read()
    else:
        with zipfile.ZipFile(source_path, 'r') as z:
            content_xml_bytes = z.read('content.xml')
            
    root = ET.fromstring(content_xml_bytes)
    rows = []
    for row in root.findall('.//table:table-row', ns):
        r_data = []
        for cell in row.findall('.//table:table-cell', ns):
            rep = int(cell.attrib.get(rep_attr, 1))
            text_elems = cell.findall('.//text:p', ns)
            val = ''.join([t.text for t in text_elems if t.text])
            if rep > 1 and rep < 50:
                for _ in range(rep):
                    r_data.append(val)
            else:
                r_data.append(val)
        if any(r_data):
            rows.append(r_data)
            
    if not rows:
        raise ValueError("Tabel ODS / content.xml kosong atau tidak terbaca.")
        
    # Pangkas kolom kosong di ujung kanan (trailing empty columns)
    header_row = rows[0]
    last_non_empty = len(header_row) - 1
    while last_non_empty >= 0 and not str(header_row[last_non_empty]).strip():
        last_non_empty -= 1
    valid_cols_count = last_non_empty + 1
    if valid_cols_count > 0:
        rows = [r[:valid_cols_count] for r in rows]

    with open(temp_csv, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.writer(f)
        for r in rows:
            writer.writerow(r)
            
    return temp_csv, rows[0]

def is_dbf_file(file_path):
    if not os.path.exists(file_path) or os.path.getsize(file_path) < 32:
        return False
    if file_path.lower().endswith('.dbf'):
        return True
    try:
        with open(file_path, 'rb') as f:
            sig = f.read(4)
            if len(sig) < 4:
                return False
            valid_magics = {0x02, 0x03, 0x04, 0x05, 0x30, 0x31, 0x32, 0x43, 0x63, 0x7b, 0x83, 0x8b, 0x8e, 0xba, 0xcb, 0xe5, 0xf5, 0xfb}
            if sig[0] in valid_magics:
                mm = sig[2]
                dd = sig[3]
                if 1 <= mm <= 12 and 1 <= dd <= 31:
                    return True
    except Exception:
        pass
    return False

def parse_dbf_header(f):
    header_bytes = f.read(32)
    if len(header_bytes) < 32:
        raise ValueError("Berkas DBF terlalu pendek atau tidak valid.")
    
    version, yy, mm, dd, num_records, header_len, record_len = struct.unpack('<BBBBIHH', header_bytes[:12])
    
    fields = []
    while True:
        b = f.read(1)
        if not b or b == b'\r':
            break
        desc_bytes = b + f.read(31)
        if len(desc_bytes) < 32:
            break
        raw_name = desc_bytes[:11].split(b'\x00')[0]
        field_name = raw_name.decode('cp1252', errors='replace').strip()
        field_name = re.sub(r'[^a-zA-Z0-9_]', '_', field_name).strip('_')
        if not field_name:
            field_name = f"col_{len(fields)+1}"
        field_type = chr(desc_bytes[11])
        field_len = desc_bytes[16]
        field_dec = desc_bytes[17]
        fields.append({
            'name': field_name,
            'type': field_type,
            'len': field_len,
            'dec': field_dec
        })
    return {
        'version': version,
        'num_records': num_records,
        'header_len': header_len,
        'record_len': record_len,
        'fields': fields
    }

def convert_dbf_to_temp_csv(dbf_path):
    temp_csv = os.path.join(os.path.dirname(dbf_path), f"_dbf_{int(time.time())}.csv")
    file_size = os.path.getsize(dbf_path)
    with open(dbf_path, 'rb') as f:
        meta = parse_dbf_header(f)
        fields = meta['fields']
        
        # Deduplicate and ensure clean field names
        field_names = []
        seen_names = set()
        for idx, fld in enumerate(fields):
            fn = fld['name']
            if fn.lower() in seen_names:
                fn = f"{fn}_{idx+1}"
            seen_names.add(fn.lower())
            field_names.append(fn)

        field_lens = [fld['len'] for fld in fields]
        record_len = meta['record_len']
        header_len = meta['header_len']
        
        calc_records = 0
        if record_len > 0 and file_size > header_len:
            calc_records = (file_size - header_len) // record_len
        num_records = max(meta['num_records'], calc_records)
        
        f.seek(header_len)
        
        with open(temp_csv, 'w', encoding='utf-8-sig', newline='') as out_f:
            writer = csv.writer(out_f)
            writer.writerow(field_names)
            
            chunk_size = 5000
            read_records = 0
            while read_records < num_records:
                curr_batch = min(chunk_size, num_records - read_records)
                raw_chunk = f.read(record_len * curr_batch)
                if not raw_chunk:
                    break
                actual_batch = len(raw_chunk) // record_len
                if actual_batch == 0:
                    break
                for rec_i in range(actual_batch):
                    rec_bytes = raw_chunk[rec_i * record_len : (rec_i + 1) * record_len]
                    if not rec_bytes or rec_bytes[0:1] == b'*':
                        continue # Skip deleted records
                    if rec_bytes[0:1] == b'\x1a':
                        break # EOF marker
                    row = []
                    offset = 1
                    for flen in field_lens:
                        val_bytes = rec_bytes[offset : offset + flen]
                        offset += flen
                        try:
                            val = val_bytes.decode('cp1252', errors='replace').strip()
                        except Exception:
                            val = val_bytes.decode('latin-1', errors='replace').strip()
                        row.append(val)
                    writer.writerow(row)
                read_records += actual_batch
                    
    return temp_csv, field_names

def detect_csv_specs(csv_path):
    encodings = ['utf-8-sig', 'utf-8', 'cp1252', 'latin-1', 'iso-8859-1']
    sample = ""
    used_enc = 'utf-8'
    for enc in encodings:
        try:
            with open(csv_path, 'r', encoding=enc, errors='replace') as f:
                sample = f.read(131072)
                if sample:
                    used_enc = enc
                    break
        except Exception:
            continue
            
    lines = [l for l in sample.splitlines() if l.strip()][:30]
    delimiters = [',', ';', '\t', '|']
    best_delim = ','
    best_score = -1
    for d in delimiters:
        counts = [l.count(d) for l in lines]
        if counts and max(counts) > 0:
            avg = sum(counts) / len(counts)
            consistency = sum(1 for c in counts if c == round(avg))
            score = avg * 10 + consistency * 5
            if score > best_score:
                best_score = score
                best_delim = d
    return best_delim, used_enc

def sanitize_csv_file(src_path, dst_path, delimiter, encoding):
    raw_cols = []
    with open(src_path, 'r', encoding=encoding, errors='replace') as infile, \
         open(dst_path, 'w', encoding='utf-8-sig', newline='') as outfile:
        reader = csv.reader(infile, delimiter=delimiter)
        writer = csv.writer(outfile, delimiter=',')
        for i, row in enumerate(reader):
            if not row:
                continue
            if i == 0:
                raw_cols = [c.strip() or f"col_{idx+1}" for idx, c in enumerate(row)]
                writer.writerow(raw_cols)
            else:
                writer.writerow(row)
    return raw_cols

def resolve_csv_source(con, csv_path):
    csv_escaped = csv_path.replace('\\', '/')
    best_delim, used_enc = detect_csv_specs(csv_path)

    escaped_delim = "\\t" if best_delim == "\t" else best_delim
    candidates = [
        f"read_csv('{csv_escaped}', delim='{escaped_delim}', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)",
        f"read_csv('{csv_escaped}', delim=';', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)",
        f"read_csv('{csv_escaped}', delim=',', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)",
        f"read_csv('{csv_escaped}', delim='\\t', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)",
        f"read_csv('{csv_escaped}', delim='|', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)",
        f"read_csv_auto('{csv_escaped}', all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false, sample_size=10000, max_line_size=20971520)"
    ]

    for cand in candidates:
        try:
            cols_info = con.execute(f"DESCRIBE SELECT * FROM {cand} LIMIT 5;").fetchall()
            if cols_info and len(cols_info) > 0:
                raw_cols = [r[0] for r in cols_info]
                if len(raw_cols) == 1 and (';' in raw_cols[0] or ',' in raw_cols[0]):
                    continue
                return cand, raw_cols, None
        except Exception:
            continue

    # Fallback: Sanitize file via Python RFC-4180 CSV Writer
    temp_clean = os.path.join(os.path.dirname(csv_path), f"_sanitized_{int(time.time())}.csv")
    raw_cols = sanitize_csv_file(csv_path, temp_clean, best_delim, used_enc)
    clean_escaped = temp_clean.replace('\\', '/')
    cand = f"read_csv('{clean_escaped}', delim=',', header=true, all_varchar=true, ignore_errors=true, null_padding=true, strict_mode=false)"
    return cand, raw_cols, temp_clean

def run_fast_import(input_path, db_path, original_file_name=None):
    if not os.path.exists(input_path):
        print(f"Error: File '{input_path}' tidak ditemukan.")
        sys.exit(1)

    start_time = time.time()
    if not original_file_name:
        original_file_name = os.path.basename(input_path)
    dbf_cleanup = None

    is_ods = is_ods_source(input_path) or original_file_name.lower().endswith('.ods')
    is_dbf = not is_ods and (original_file_name.lower().endswith('.dbf') or input_path.lower().endswith('.dbf') or is_dbf_file(input_path))

    if is_ods:
        update_progress(10, "Membaca & mengonversi tabel OpenDocument (ODS / content.xml)...", start_time)
        print(f"[*] Mendeteksi format ODS / Koleksi XML: Mengonversi {original_file_name} ke CSV...", flush=True)
        try:
            temp_csv, ods_cols = convert_ods_to_temp_csv(input_path)
            csv_path = temp_csv
            dbf_cleanup = temp_csv
        except Exception as e_ods:
            print(f"[ERROR] Gagal membaca tabel ODS: {e_ods}", flush=True)
            sys.exit(1)
    elif is_dbf:
        update_progress(10, "Membaca & mengonversi tabel biner DBF (dBase / FoxPro)...", start_time)
        print(f"[*] Mendeteksi format DBF: Mengonversi {original_file_name} secara stream...", flush=True)
        try:
            temp_csv, dbf_cols = convert_dbf_to_temp_csv(input_path)
            csv_path = temp_csv
            dbf_cleanup = temp_csv
        except Exception as e_dbf:
            print(f"[ERROR] Gagal membaca berkas DBF: {e_dbf}", flush=True)
            sys.exit(1)
    else:
        csv_path = input_path
        update_progress(10, "Menganalisis header & format berkas (Multi-Encoding/Delimiter)...", start_time)

    print(f"[*] Memulai Pure DuckDB Ultra-Fast Import untuk: {original_file_name}", flush=True)

    db_dir = os.path.dirname(db_path)
    duck_file = os.path.join(db_dir, 'dataset.duckdb')
    duck_incoming = os.path.join(db_dir, f'dataset_incoming_{int(time.time())}.duckdb')

    if os.path.exists(duck_incoming):
        try:
            os.remove(duck_incoming)
        except Exception:
            pass

    cpu_count = max(4, min(16, os.cpu_count() or 8))
    con = duckdb.connect(duck_incoming)
    con.execute("SET enable_progress_bar = false;")
    con.execute(f"PRAGMA threads = {cpu_count};")
    con.execute("PRAGMA preserve_insertion_order = false;")
    con.execute("PRAGMA memory_limit = '4GB';")

    update_progress(25, "Mendeteksi dialek & memuat tabel data...", start_time)

    temp_cleanup = None
    try:
        csv_source_sql, raw_cols, temp_cleanup = resolve_csv_source(con, csv_path)
    except Exception as e_sniff:
        print(f"[ERROR] Sniffing failed: {e_sniff}", flush=True)
        sys.exit(1)

    # 1. Ingest raw data directly into raw_stream table
    update_progress(45, f"Vectorized ingestion multi-threaded ({cpu_count} CPU Threads)...", start_time)
    con.execute(f"CREATE OR REPLACE TABLE raw_stream AS SELECT * FROM {csv_source_sql};")

    if temp_cleanup and os.path.exists(temp_cleanup):
        try:
            os.remove(temp_cleanup)
        except Exception:
            pass

    # 2. Inspect actual ingested columns and sanitize column names
    cols_info = con.execute("PRAGMA table_info('raw_stream');").fetchall()
    raw_col_names = [c[1] for c in cols_info]

    norm_map = {}
    used_cleans = set()
    for idx, c in enumerate(raw_col_names):
        canonical = map_canonical_header(c, idx)
        if canonical in used_cleans:
            canonical = f"{canonical}_{idx+1}"
        used_cleans.add(canonical)
        norm_map[c] = canonical
        if canonical != c:
            try:
                c_esc = c.replace('"', '""')
                con.execute(f'ALTER TABLE raw_stream RENAME COLUMN "{c_esc}" TO "{canonical}";')
            except Exception:
                pass

    # Drop any ghost columns that have no real header and are entirely NULL/empty
    active_cols_after_rename = [c[1] for c in con.execute("PRAGMA table_info('raw_stream');").fetchall()]
    for c in active_cols_after_rename:
        if re.match(r'^(col_\d+|column\d+)$', c, re.I):
            non_null_cnt = con.execute(f'SELECT COUNT(*) FROM raw_stream WHERE "{c}" IS NOT NULL AND TRIM(CAST("{c}" AS VARCHAR)) != \'\';').fetchone()[0]
            if non_null_cnt == 0:
                try:
                    con.execute(f'ALTER TABLE raw_stream DROP COLUMN "{c}";')
                    if c in used_cleans:
                        used_cleans.remove(c)
                except Exception:
                    pass

    # 3. Create table individus with sequential id, and pre-initialize quality_issues and quality_status
    con.execute("CREATE OR REPLACE TABLE individus AS SELECT row_number() OVER () AS id, []::VARCHAR[] AS quality_issues, 'Valid'::VARCHAR AS quality_status, * FROM raw_stream;")
    con.execute("DROP TABLE raw_stream;")

    current_cols = {c[1].lower(): c[1] for c in con.execute("PRAGMA table_info('individus');").fetchall()}

    # 4. Ensure standard aliases exist (nik, no_kk, kk, nama_lengkap, gaji, desil, umur)
    update_progress(65, "Memetakan relasi kolom NIK, KK, & variabel demografi...", start_time)

    # NIK
    if 'nik' not in current_cols:
        nik_cand = None
        for cand in ['nomor_induk_kependudukan', 'no_nik', 'nik', 'id_penduduk', 'no_ktp', 'nomor_ktp']:
            if cand in current_cols:
                nik_cand = current_cols[cand]
                break
        if not nik_cand:
            nik_cand = next((c for c in current_cols.values() if 'nik' in c.lower() or 'ktp' in c.lower() or 'kependudukan' in c.lower()), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN nik VARCHAR;")
        if nik_cand:
            con.execute(f'UPDATE individus SET nik = TRIM(CAST("{nik_cand}" AS VARCHAR));')
        else:
            con.execute("UPDATE individus SET nik = '320101' || LPAD(CAST(id AS VARCHAR), 10, '0');")

    # NO_KK
    if 'no_kk' not in current_cols:
        kk_cand = None
        for cand in ['nomor_kartu_keluarga', 'no_kk', 'kk', 'id_kk', 'nomor_kk', 'nokk']:
            if cand in current_cols:
                kk_cand = current_cols[cand]
                break
        if not kk_cand:
            kk_cand = next((c for c in current_cols.values() if 'kartu_keluarga' in c.lower() or c.lower().startswith('kk_') or c.lower().endswith('_kk')), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN no_kk VARCHAR;")
        if kk_cand:
            con.execute(f'UPDATE individus SET no_kk = TRIM(CAST("{kk_cand}" AS VARCHAR));')
        else:
            con.execute("UPDATE individus SET no_kk = '320101' || LPAD(CAST(id AS VARCHAR), 10, '0');")

    # KK
    if 'kk' not in current_cols:
        con.execute("ALTER TABLE individus ADD COLUMN kk VARCHAR;")
        con.execute("UPDATE individus SET kk = no_kk;")

    # NAMA_LENGKAP
    if 'nama_lengkap' not in current_cols:
        nama_cand = None
        for cand in ['nama', 'nama_lengkap', 'nama_subjek', 'nama_warga', 'nama_art', 'fullname']:
            if cand in current_cols:
                nama_cand = current_cols[cand]
                break
        if not nama_cand:
            nama_cand = next((c for c in current_cols.values() if 'nama' in c.lower() or 'warga' in c.lower() or c.lower() == 'nama_penduduk'), None)
        if not nama_cand:
            # Fallback for survey/village records (e.g. PODES r108 / r107)
            nama_cand = next((c for c in current_cols.values() if c.lower() in ('r108', 'r107', 'r106') or ('desa' in c.lower() and 'kelurahan' not in c.lower() and 'kd' not in c.lower())), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN nama_lengkap VARCHAR;")
        if nama_cand:
            con.execute(f'UPDATE individus SET nama_lengkap = TRIM(CAST("{nama_cand}" AS VARCHAR));')
        else:
            con.execute("UPDATE individus SET nama_lengkap = 'Masyarakat #' || CAST(id AS VARCHAR);")

    # GAJI
    if 'gaji' not in current_cols:
        gaji_cand = None
        for cand in ['gaji_bulanan', 'gaji', 'pendapatan', 'penghasilan', 'salary', 'income', 'upah']:
            if cand in current_cols:
                gaji_cand = current_cols[cand]
                break
        if not gaji_cand:
            gaji_cand = next((c for c in current_cols.values() if 'gaji' in c.lower() or 'pendapatan' in c.lower() or 'upah' in c.lower() or 'penghasilan' in c.lower()), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN gaji DOUBLE DEFAULT 0.0;")
        if gaji_cand:
            con.execute(f'UPDATE individus SET gaji = COALESCE(TRY_CAST(REGEXP_REPLACE(CAST("{gaji_cand}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE), 0.0);')

    # DESIL
    if 'desil' not in current_cols:
        desil_cand = None
        for cand in ['desil_nasional', 'desil', 'desil_kesejahteraan', 'desil_ekonomi']:
            if cand in current_cols:
                desil_cand = current_cols[cand]
                break
        if not desil_cand:
            desil_cand = next((c for c in current_cols.values() if 'desil' in c.lower()), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN desil INTEGER DEFAULT 1;")
        if desil_cand:
            con.execute(f'UPDATE individus SET desil = COALESCE(TRY_CAST("{desil_cand}" AS INTEGER), 1);')

    # UMUR
    if 'umur' not in current_cols:
        umur_cand = None
        for cand in ['usia', 'umur', 'age', 'tahun_usia', 'usia_tahun']:
            if cand in current_cols:
                umur_cand = current_cols[cand]
                break
        if not umur_cand:
            umur_cand = next((c for c in current_cols.values() if 'umur' in c.lower() or 'usia' in c.lower()), None)
            
        con.execute("ALTER TABLE individus ADD COLUMN umur INTEGER DEFAULT 0;")
        if umur_cand:
            con.execute(f'UPDATE individus SET umur = COALESCE(TRY_CAST(REGEXP_REPLACE(CAST("{umur_cand}" AS VARCHAR), \'[^0-9]\', \'\', \'g\') AS INTEGER), 0);')

    if 'quality_status' not in current_cols:
        con.execute("ALTER TABLE individus ADD COLUMN quality_status VARCHAR DEFAULT 'Valid';")

    if 'quality_issues' not in current_cols:
        con.execute("ALTER TABLE individus ADD COLUMN quality_issues VARCHAR[];")
        con.execute("UPDATE individus SET quality_issues = [];")

    # 4b. Ensure human-readable alamat exists and is not raw integer/status code
    # If alamat is missing, OR if alamat contains only digits (e.g. 1, 2 from r105), synthesize clean geographic address
    r104_cand = current_cols.get('r104n') or current_cols.get('nama_desa') or current_cols.get('desa') or current_cols.get('kelurahan_desa')
    r103_cand = current_cols.get('r103n') or current_cols.get('nama_kecamatan') or current_cols.get('kecamatan')
    r102_cand = current_cols.get('r102n') or current_cols.get('nama_kabupaten') or current_cols.get('kabupaten_kota')
    r101_cand = current_cols.get('r101n') or current_cols.get('nama_provinsi') or current_cols.get('provinsi')

    if 'alamat' not in current_cols:
        con.execute("ALTER TABLE individus ADD COLUMN alamat VARCHAR;")
        if r104_cand and r103_cand:
            con.execute(f"""
                UPDATE individus 
                SET alamat = 'Desa/Kel. ' || COALESCE(CAST("{r104_cand}" AS VARCHAR), '') || 
                             ', Kec. ' || COALESCE(CAST("{r103_cand}" AS VARCHAR), '') || 
                             CASE WHEN '{r102_cand}' != 'None' AND '{r102_cand}' != '' THEN ', ' || COALESCE(CAST("{r102_cand}" AS VARCHAR), '') ELSE '' END || 
                             CASE WHEN '{r101_cand}' != 'None' AND '{r101_cand}' != '' THEN ', ' || COALESCE(CAST("{r101_cand}" AS VARCHAR), '') ELSE '' END;
            """)
        else:
            con.execute("UPDATE individus SET alamat = 'Wilayah Binaan DTSEN #1';")
    else:
        # Check if alamat contains only digits (misaligned code)
        try:
            check_numeric = con.execute("SELECT COUNT(*) FROM individus WHERE alamat IS NOT NULL AND REGEXP_MATCHES(TRIM(CAST(alamat AS VARCHAR)), '^[0-9]+$');").fetchone()[0]
            if check_numeric > 0 and r104_cand and r103_cand:
                con.execute(f"""
                    UPDATE individus 
                    SET alamat = 'Desa/Kel. ' || COALESCE(CAST("{r104_cand}" AS VARCHAR), '') || 
                                 ', Kec. ' || COALESCE(CAST("{r103_cand}" AS VARCHAR), '') || 
                                 CASE WHEN '{r102_cand}' != 'None' AND '{r102_cand}' != '' THEN ', ' || COALESCE(CAST("{r102_cand}" AS VARCHAR), '') ELSE '' END || 
                                 CASE WHEN '{r101_cand}' != 'None' AND '{r101_cand}' != '' THEN ', ' || COALESCE(CAST("{r101_cand}" AS VARCHAR), '') ELSE '' END
                    WHERE REGEXP_MATCHES(TRIM(CAST(alamat AS VARCHAR)), '^[0-9]+$');
                """)
        except Exception:
            pass

    total_rows = con.execute("SELECT COUNT(*) FROM individus;").fetchone()[0]

    update_progress(80, "Menghitung agregasi statistik dataset...", start_time)

    # Summary stats
    stats_row = con.execute("""
        SELECT 
            COUNT(*) as total_rows,
            COUNT(DISTINCT kk) as total_kk,
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

    # Distinct options map for filter dropdowns
    distinct_map = {}
    for norm in list(used_cleans)[:30]:
        if norm not in ['id', 'quality_status', 'quality_issues', 'nik', 'kk', 'no_kk']:
            try:
                rows = con.execute(f'SELECT DISTINCT "{norm}" FROM (SELECT "{norm}" FROM individus WHERE "{norm}" IS NOT NULL LIMIT 2000) WHERE TRIM(CAST("{norm}" AS VARCHAR)) != \'\' LIMIT 30;').fetchall()
                vals = [str(r[0]).strip() for r in rows if r[0] is not None]
                if vals:
                    distinct_map[norm] = vals
            except Exception:
                pass

    headers = [c[1] for c in con.execute("PRAGMA table_info('individus');").fetchall() if c[1] not in ['id', 'quality_issues', 'quality_status']]
    con.close()

    update_progress(90, "Menyimpan dataset ke database lokal...", start_time)

    # Atomic Swap to dataset.duckdb
    swap_success = False
    for attempt in range(15):
        try:
            if os.path.exists(duck_file):
                os.remove(duck_file)
            os.replace(duck_incoming, duck_file)
            swap_success = True
            break
        except Exception:
            time.sleep(0.4)

    if not swap_success:
        try:
            import shutil
            shutil.copy2(duck_incoming, duck_file)
            os.remove(duck_incoming)
            swap_success = True
        except Exception as e_swap:
            print(f"[ERROR] Atomic swap failed: {e_swap}", flush=True)

    # Save persistent active dataset metadata JSON
    try:
        active_meta_file = os.path.join(os.path.dirname(db_path), 'active_dataset.json')
        with open(active_meta_file, 'w', encoding='utf-8') as amf:
            json.dump({
                'active_dataset_file': original_file_name,
                'total_rows': total_rows,
                'imported_at': time.strftime('%Y-%m-%d %H:%M:%S')
            }, amf)
    except Exception:
        pass

    # Clean up temporary DBF converted CSV if any
    if dbf_cleanup and os.path.exists(dbf_cleanup):
        try:
            os.remove(dbf_cleanup)
        except Exception:
            pass

    update_progress(100, f"Pemrosesan dataset {total_rows:,} baris selesai!", start_time)

    print(f"[SUMMARY_STATS] {json.dumps(summary_stats)}", flush=True)
    print(f"[HEADERS_JSON] {json.dumps(headers)}", flush=True)
    print(f"[DISTINCT_MAP_JSON] {json.dumps({'distinct_map': distinct_map})}", flush=True)
    print("[DUCKDB_READY] Pure DuckDB Native dataset created successfully.", flush=True)

    elapsed = time.time() - start_time
    print(f"[SUCCESS] Impor {total_rows:,} baris data selesai dalam {elapsed:.2f} detik! ({total_rows/max(0.01, elapsed):,.0f} baris/detik)", flush=True)

if __name__ == '__main__':
    csv_f = sys.argv[1] if len(sys.argv) > 1 else 'src-dtsen/sample_13_juta_data.csv'
    db_f = sys.argv[2] if len(sys.argv) > 2 else 'database/database.sqlite'
    orig_name = sys.argv[3] if len(sys.argv) > 3 else os.path.basename(csv_f)
    run_fast_import(csv_f, db_f, orig_name)
