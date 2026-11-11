import sys
import os
import json
import base64
import duckdb
import re

def resolve_target_column(key, ex_cols):
    k = str(key).strip().lower()
    if k.startswith('salary_'):
        k = k[7:]
    if k in ex_cols:
        return ex_cols[k]
    syns = {
        'gaji': 'gaji_bulanan', 'pendapatan': 'gaji_bulanan', 'income': 'gaji_bulanan', 'salary': 'gaji_bulanan', 'penghasilan': 'gaji_bulanan',
        'gaji_bulanan': 'gaji',
        'desil': 'desil_nasional', 'desil_kesejahteraan': 'desil_nasional',
        'desil_nasional': 'desil',
        'jk': 'jenis_kelamin', 'gender': 'jenis_kelamin', 'sex': 'jenis_kelamin',
        'jenis_kelamin': 'jk',
        'nik': 'nomor_induk_kependudukan', 'no_nik': 'nomor_induk_kependudukan',
        'nomor_induk_kependudukan': 'nik',
        'kk': 'nomor_kartu_keluarga', 'no_kk': 'nomor_kartu_keluarga',
        'nomor_kartu_keluarga': 'kk',
        'umur': 'usia', 'age': 'usia',
        'usia': 'umur',
        'pekerjaan': 'status_bekerja', 'status_kerja': 'status_bekerja',
        'status_bekerja': 'pekerjaan',
        'status_kawin': 'status_pernikahan', 'status_pernikahan': 'status_kawin',
        'nama': 'nama_lengkap', 'nama_lengkap': 'nama',
        'kabupaten': 'kabupaten_kota', 'kota': 'kabupaten_kota',
        'desa': 'kelurahan_desa', 'kelurahan': 'kelurahan_desa',
        'hubungan_keluarga': 'status_hubungan_keluarga', 'shdk': 'status_hubungan_keluarga', 'status_hubungan': 'status_hubungan_keluarga'
    }
    if k in syns and syns[k] in ex_cols:
        return ex_cols[syns[k]]
    # Only search substrings for longer tokens, and NEVER match small aliases like 'id', 'kk', 'nik'
    if len(k) >= 4:
        for exist_k, exist_name in ex_cols.items():
            if exist_k in ('id', 'nik', 'kk', 'no_kk') or len(exist_k) < 4:
                continue
            if k in exist_k or exist_k in k:
                return exist_name
    return None

def clean_str(val):
    if val is None:
        return ''
    s = str(val).strip()
    return '' if s.lower() in ('none', 'null', 'semua') else s

def parse_semantic_nlq(search_str, existing_cols, table_ref):
    if not search_str or len(search_str.strip()) < 2:
        return None, [], None
    
    q_low = search_str.lower().strip()
    col_map = {c.lower(): c for c in existing_cols.values()}
    
    clauses = []
    having_clauses = []
    tags = []
    is_kk_scope = False
    
    # Text to strip for residual detection
    cleaned_for_residuals = q_low

    def strip_from_residual(pattern):
        nonlocal cleaned_for_residuals
        cleaned_for_residuals = re.sub(pattern, ' ', cleaned_for_residuals)

    # Check for KK Scope trigger
    if any(k in q_low for k in ['per kk', 'dalam satu kk', 'per keluarga', 'tiap kk', 'tiap keluarga', 'dalam 1 kk', '1 kk', 'satu kk']):
        is_kk_scope = True
        strip_from_residual(r'\b(?:per|dalam|tiap|satu)?\s*(?:kk|keluarga|rumah\s*tangga)\b')

    # Wilayah Columns
    wilayah_cols = []
    for hint in ['kecamatan', 'kabupaten_kota', 'kabupaten', 'provinsi', 'desa_kelurahan', 'desa', 'kelurahan', 'alamat']:
        for c_low, c_orig in col_map.items():
            if hint == c_low or hint in c_low:
                if c_orig not in wilayah_cols:
                    wilayah_cols.append(c_orig)

    # 1. Wilayah / Lokasi explicit: "di wilayah X", "di kecamatan X", "wilayah X", "di X"
    w_match = re.search(r'\b(?:di\s+wilayah|di\s+daerah|di\s+kecamatan|di\s+kabupaten|di\s+kota|di\s+desa|di\s+kelurahan|wilayah|daerah|kecamatan|kabupaten|desa|kelurahan)\s+([a-z0-9\s]+?)(?:(?=\s+(?:dan|dengan|yang|status|gaji|pendidikan|desil|kk|umur|usia|$))|$)', q_low)
    
    found_wilayah = None
    if w_match:
        raw_val = w_match.group(1).strip()
        if raw_val and len(raw_val) >= 2 and raw_val not in ('yang', 'dan', 'dengan', 'ada'):
            found_wilayah = raw_val
            strip_from_residual(re.escape(w_match.group(0)))
    
    if not found_wilayah:
        di_match = re.search(r'\bdi\s+([a-z0-9\s]+?)(?:(?=\s+(?:dan|dengan|yang|status|gaji|pendidikan|desil|kk|umur|usia|$))|$)', q_low)
        if di_match:
            raw_val = di_match.group(1).strip()
            if raw_val and len(raw_val) >= 2 and raw_val not in ('bawah', 'atas', 'antara', 'luar', 'dalam', 'yang', 'dan', 'dengan'):
                found_wilayah = raw_val
                strip_from_residual(re.escape(di_match.group(0)))

    if found_wilayah and wilayah_cols:
        clean_w = found_wilayah.replace("'", "''")
        no_space_w = clean_w.replace(" ", "")
        
        w_terms = []
        for wc in wilayah_cols:
            w_terms.append(f'LOWER(CAST("{wc}" AS VARCHAR)) LIKE \'%{clean_w}%\'')
            if len(clean_w.split()) > 1:
                w_terms.append(f'REPLACE(LOWER(CAST("{wc}" AS VARCHAR)), \' \', \'\') LIKE \'%{no_space_w}%\'')
        
        if w_terms:
            clauses.append('(' + ' OR '.join(w_terms) + ')')
            tags.append(f'Wilayah: {found_wilayah.title()}')

    # 2. Livestock / Sapi (e.g. "100 sapi", "sapi >= 100", "sapi 100", "sapi")
    sapi_match = re.search(r'(\d+)\s*(?:ekor\s*)?(?:sapi|lembu|ternak)|(?:sapi|lembu|ternak)\s*(?:>=|>|minimal|di atas|sebanyak|punya)?\s*(\d+)', q_low)
    sapi_col = None
    for hint in ['jumlah_sapi', 'sapi', 'ternak_sapi', 'ternak', 'aset']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                sapi_col = c_orig
                break
        if sapi_col: break

    if sapi_col:
        if sapi_match:
            val = sapi_match.group(1) or sapi_match.group(2)
            if is_kk_scope or 'kk' in q_low or 'keluarga' in q_low:
                having_clauses.append(f'MAX(COALESCE(TRY_CAST("{sapi_col}" AS DOUBLE), 0)) >= {val}')
                tags.append(f'Aset Sapi KK: ≥ {val} ekor')
                is_kk_scope = True
            else:
                clauses.append(f'COALESCE(TRY_CAST("{sapi_col}" AS DOUBLE), 0) >= {val}')
                tags.append(f'Sapi: ≥ {val} ekor')
            strip_from_residual(re.escape(sapi_match.group(0)))
        elif 'sapi' in q_low or 'ternak' in q_low:
            clauses.append(f'COALESCE(TRY_CAST("{sapi_col}" AS DOUBLE), 0) > 0')
            tags.append('Memiliki Ternak Sapi')
            strip_from_residual(r'\b(sapi|ternak)\b')

    # 3. Gender & Count in KK or Individual
    p_num = re.search(r'(?:minimal|paling sedikit|di atas|>=)?\s*(\d+)\s*(?:orang\s*)?(?:perempuan|wanita)', q_low)
    l_num = re.search(r'(?:minimal|paling sedikit|di atas|>=)?\s*(\d+)\s*(?:orang\s*)?(?:laki|pria)', q_low)
    
    jk_col = None
    for hint in ['jenis_kelamin', 'jk', 'gender']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                jk_col = c_orig
                break
        if jk_col: break

    if jk_col:
        if p_num and (is_kk_scope or 'kk' in q_low or 'keluarga' in q_low):
            val = p_num.group(1)
            having_clauses.append(f'COUNT(CASE WHEN LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%perempuan%\' THEN 1 END) >= {val}')
            tags.append(f'Perempuan dalam KK: ≥ {val} orang')
            is_kk_scope = True
            strip_from_residual(re.escape(p_num.group(0)))
        elif l_num and (is_kk_scope or 'kk' in q_low or 'keluarga' in q_low):
            val = l_num.group(1)
            having_clauses.append(f'COUNT(CASE WHEN LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%laki%\' THEN 1 END) >= {val}')
            tags.append(f'Laki-laki dalam KK: ≥ {val} orang')
            is_kk_scope = True
            strip_from_residual(re.escape(l_num.group(0)))
        elif 'perempuan per kk' in q_low or 'wanita per kk' in q_low:
            having_clauses.append(f'COUNT(CASE WHEN LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%perempuan%\' THEN 1 END) >= 1')
            tags.append('Memiliki Anggota Perempuan dalam KK')
            is_kk_scope = True
            strip_from_residual(r'\b(?:perempuan|wanita)\s+per\s+kk\b')
        else:
            # Individual Gender Filter
            m_p = re.search(r'\b(perempuan|wanita|cewek|ibu)\b', q_low)
            m_l = re.search(r'\b(laki|laki-laki|pria|cowok|bapak)\b', q_low)
            if m_p and not m_l:
                clauses.append(f'LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%perempuan%\'')
                tags.append('Jenis Kelamin: Perempuan')
                strip_from_residual(r'\b(perempuan|wanita|cewek|ibu)\b')
            elif m_l and not m_p:
                clauses.append(f'LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%laki%\'')
                tags.append('Jenis Kelamin: Laki-laki')
                strip_from_residual(r'\b(laki|laki-laki|pria|cowok|bapak)\b')

    # 4. Education / Pendidikan
    pdd_col = None
    for hint in ['ijazah_tertinggi', 'pendidikan', 'ijazah', 'pendidikan_terakhir']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                pdd_col = c_orig
                break
        if pdd_col: break
    
    if pdd_col:
        if re.search(r'\b(sma|slta|smk|aliyah|menengah atas)\b', q_low):
            clauses.append(f'(LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%sma%\' OR LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%slta%\' OR LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%smk%\')')
            tags.append('Pendidikan: SMA / Sederajat')
            strip_from_residual(r'\b(pendidikan\s+)?(sma|slta|smk|aliyah|menengah atas)\b')
        elif re.search(r'\b(smp|sltp|mts|menengah pertama)\b', q_low):
            clauses.append(f'(LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%smp%\' OR LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%sltp%\')')
            tags.append('Pendidikan: SMP / Sederajat')
            strip_from_residual(r'\b(pendidikan\s+)?(smp|sltp|mts|menengah pertama)\b')
        elif re.search(r'\b(sd|sekolah dasar|mi)\b', q_low):
            clauses.append(f'LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%sd%\'')
            tags.append('Pendidikan: SD / Sederajat')
            strip_from_residual(r'\b(pendidikan\s+)?(sd|sekolah dasar|mi)\b')
        elif re.search(r'\b(s1|s2|s3|diploma|sarjana|kuliah|perguruan tinggi)\b', q_low):
            clauses.append(f'(LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%s1%\' OR LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%diploma%\' OR LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%sarjana%\')')
            tags.append('Pendidikan: Perguruan Tinggi')
            strip_from_residual(r'\b(s1|s2|s3|diploma|sarjana|kuliah|perguruan tinggi)\b')
        elif re.search(r'\b(tidak sekolah|belum sekolah|putus sekolah)\b', q_low):
            clauses.append(f'LOWER(CAST("{pdd_col}" AS VARCHAR)) LIKE \'%tidak%\'')
            tags.append('Pendidikan: Tidak / Belum Sekolah')
            strip_from_residual(r'\b(tidak sekolah|belum sekolah|putus sekolah)\b')

    # 5. Marital Status
    kawin_col = None
    for hint in ['status_kawin', 'status_pernikahan', 'perkawinan']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                kawin_col = c_orig
                break
        if kawin_col: break
    
    if kawin_col:
        m_bk = re.search(r'\b(?:belum\s+(?:menikah|kawin|nikah)|lajang|single|bujang)\b', q_low)
        m_k = re.search(r'\b(?:sudah\s+menikah|menikah|kawin|nikah|berkeluarga)\b', q_low)
        m_c = re.search(r'\b(?:cerai|janda|duda)\b', q_low)
        if m_bk:
            clauses.append(f'LOWER(CAST("{kawin_col}" AS VARCHAR)) LIKE \'%belum%\'')
            tags.append('Status: Belum Kawin')
            strip_from_residual(re.escape(m_bk.group(0)))
        elif m_k:
            clauses.append(f'(LOWER(CAST("{kawin_col}" AS VARCHAR)) LIKE \'%kawin%\' OR LOWER(CAST("{kawin_col}" AS VARCHAR)) LIKE \'%nikah%\')')
            tags.append('Status: Sudah Menikah')
            strip_from_residual(re.escape(m_k.group(0)))
        elif m_c:
            clauses.append(f'LOWER(CAST("{kawin_col}" AS VARCHAR)) LIKE \'%cerai%\'')
            tags.append('Status: Cerai')
            strip_from_residual(re.escape(m_c.group(0)))

    # 6. Employment / Status Bekerja
    kerja_col = None
    for hint in ['status_bekerja', 'pekerjaan_utama', 'pekerjaan']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                kerja_col = c_orig
                break
        if kerja_col: break
    
    if kerja_col:
        if re.search(r'\b(?:tidak\s+bekerja|menganggur|belum\s+kerja|pengangguran)\b', q_low):
            clauses.append(f'(LOWER(CAST("{kerja_col}" AS VARCHAR)) LIKE \'%tidak%\' OR LOWER(CAST("{kerja_col}" AS VARCHAR)) LIKE \'%belum%\')')
            tags.append('Status Kerja: Tidak Bekerja')
            strip_from_residual(r'\b(?:tidak\s+bekerja|menganggur|belum\s+kerja|pengangguran)\b')
        elif re.search(r'\b(?:sudah\s+kerja|aktif\s+bekerja|bekerja|kerja)\b', q_low) and not ('tidak' in q_low or 'belum' in q_low):
            clauses.append(f'(LOWER(CAST("{kerja_col}" AS VARCHAR)) = \'ya\' OR LOWER(CAST("{kerja_col}" AS VARCHAR)) LIKE \'%bekerja%\')')
            tags.append('Status Kerja: Bekerja')
            strip_from_residual(r'\b(?:sudah\s+kerja|aktif\s+bekerja|bekerja|kerja)\b')
        
        for prof in ['pns', 'asn', 'tni', 'polri', 'petani', 'nelayan', 'buruh', 'wiraswasta', 'pedagang', 'karyawan', 'guru']:
            if re.search(r'\b' + prof + r'\b', q_low):
                clauses.append(f'LOWER(CAST("{kerja_col}" AS VARCHAR)) LIKE \'%{prof}%\'')
                tags.append(f'Pekerjaan: {prof.upper()}')
                strip_from_residual(r'\b' + prof + r'\b')
                break

    # 7. Roof / Atap
    atap_col = None
    for hint in ['atap', 'jenis_atap']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                atap_col = c_orig
                break
        if atap_col: break
    
    if atap_col:
        for atap_val in ['genteng', 'seng', 'asbes', 'rumbia']:
            if atap_val in q_low:
                clauses.append(f'LOWER(CAST("{atap_col}" AS VARCHAR)) LIKE \'%{atap_val}%\'')
                tags.append(f'Atap: {atap_val.capitalize()}')
                strip_from_residual(r'\b(atap\s+rumah|atap)?\s*' + atap_val + r'\b')
                break

    # 8. Vehicle / Mobil / Motor
    kendaraan_col = None
    for hint in ['kendaraan', 'mobil', 'motor', 'aset_kendaraan']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                kendaraan_col = c_orig
                break
        if kendaraan_col: break
    
    if kendaraan_col:
        if 'mobil' in q_low:
            clauses.append(f'(LOWER(CAST("{kendaraan_col}" AS VARCHAR)) LIKE \'%mobil%\' OR LOWER(CAST("{kendaraan_col}" AS VARCHAR)) = \'ya\' OR LOWER(CAST("{kendaraan_col}" AS VARCHAR)) = \'1\')')
            tags.append('Kendaraan: Mobil')
            strip_from_residual(r'\b(kendaraan\s+)?mobil\b')
        elif 'motor' in q_low:
            clauses.append(f'(LOWER(CAST("{kendaraan_col}" AS VARCHAR)) LIKE \'%motor%\' OR LOWER(CAST("{kendaraan_col}" AS VARCHAR)) = \'ya\' OR LOWER(CAST("{kendaraan_col}" AS VARCHAR)) = \'1\')')
            tags.append('Kendaraan: Motor')
            strip_from_residual(r'\b(kendaraan\s+)?motor\b')

    # 9. Facility / AC
    if re.search(r'\bac\b', q_low):
        ac_col = None
        for hint in ['fasilitas', 'ac', 'aset']:
            for c_low, c_orig in col_map.items():
                if hint in c_low:
                    ac_col = c_orig
                    break
            if ac_col: break
        if ac_col:
            clauses.append(f'LOWER(CAST("{ac_col}" AS VARCHAR)) LIKE \'%ac%\'')
            tags.append('Fasilitas: AC')
            strip_from_residual(r'\b(fasilitas\s+)?ac\b')

    # 10. Poverty & Desil
    desil_col = None
    for hint in ['desil_nasional', 'desil']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                desil_col = c_orig
                break
        if desil_col: break
    
    desil_match = re.search(r'desil\s*(\d+)', q_low)
    if desil_match and desil_col:
        d_val = desil_match.group(1)
        clauses.append(f'TRY_CAST("{desil_col}" AS INTEGER) = {d_val}')
        tags.append(f'Desil: {d_val}')
        strip_from_residual(re.escape(desil_match.group(0)))
    elif re.search(r'\b(miskin|prasejahtera)\b', q_low) and desil_col:
        clauses.append(f'TRY_CAST("{desil_col}" AS INTEGER) IN (1, 2)')
        tags.append('Kategori: Miskin (Desil 1-2)')
        strip_from_residual(r'\b(miskin|prasejahtera)\b')

    # 11. Bansos
    bansos_col = None
    for hint in ['status_bantuan', 'bantuan', 'bansos']:
        for c_low, c_orig in col_map.items():
            if hint in c_low:
                bansos_col = c_orig
                break
        if bansos_col: break
    
    if bansos_col:
        if re.search(r'\b(?:penerima\s+bansos|dapat\s+bansos|pkh|bpnt)\b', q_low) and not 'bukan' in q_low:
            clauses.append(f'LOWER(CAST("{bansos_col}" AS VARCHAR)) LIKE \'%penerima%\'')
            tags.append('Bansos: Penerima')
            strip_from_residual(r'\b(?:penerima\s+bansos|dapat\s+bansos|pkh|bpnt)\b')
        elif re.search(r'\bbukan\s+penerima\b', q_low):
            clauses.append(f'LOWER(CAST("{bansos_col}" AS VARCHAR)) LIKE \'%bukan%\'')
            tags.append('Bansos: Bukan Penerima')
            strip_from_residual(r'\bbukan\s+penerima\b')

    # 12. Salary / Gaji
    gaji_col = col_map.get('gaji_bulanan', col_map.get('gaji', None))
    if gaji_col:
        gaji_match = re.search(r'(?:gaji|penghasilan|pendapatan)\s*(?:>=|>|minimal|di atas|lebih dari)?\s*(\d+(?:[.,]\d+)?)\s*(juta|jt|ribu|rb)?', q_low)
        if gaji_match:
            raw_num = float(gaji_match.group(1).replace(',', '.'))
            unit = (gaji_match.group(2) or '').lower()
            if unit in ('juta', 'jt'):
                gaji_val = raw_num * 1000000
            elif unit in ('ribu', 'rb'):
                gaji_val = raw_num * 1000
            else:
                gaji_val = raw_num if raw_num > 10000 else raw_num * 1000000
            
            clauses.append(f'COALESCE(TRY_CAST("{gaji_col}" AS DOUBLE), 0) >= {gaji_val}')
            tags.append(f'Gaji: ≥ Rp {int(gaji_val):,}')
            strip_from_residual(re.escape(gaji_match.group(0)))

    # 13. Residual keywords handling (Safety net for unparsed location or search terms)
    stop_words = {'dan', 'dengan', 'yang', 'di', 'ke', 'dari', 'pada', 'untuk', 'ada', 'nya', 'atau', 'wilayah', 'daerah', 'kecamatan', 'kabupaten', 'desa', 'kota'}
    tokens = [w.strip() for w in re.split(r'[^a-zA-Z0-9]+', cleaned_for_residuals) if w.strip()]
    residual_words = [w for w in tokens if len(w) >= 3 and w not in stop_words]

    if residual_words and not found_wilayah:
        res_terms = []
        for rw in residual_words:
            rw_clean = rw.replace("'", "''")
            target_cols_for_residual = wilayah_cols if wilayah_cols else list(col_map.values())
            sub_terms = [f'LOWER(CAST("{c}" AS VARCHAR)) LIKE \'%{rw_clean}%\'' for c in target_cols_for_residual]
            if sub_terms:
                res_terms.append('(' + ' OR '.join(sub_terms) + ')')
                tags.append(f'Kata: {rw.title()}')
        if res_terms:
            clauses.append('(' + ' AND '.join(res_terms) + ')')

    if not tags:
        return None, [], None

    kk_col = col_map.get('nomor_kartu_keluarga', col_map.get('no_kk', col_map.get('kk', 'nomor_kartu_keluarga')))
    
    if is_kk_scope and having_clauses:
        having_sql = " AND ".join(having_clauses)
        where_sub = f'"{kk_col}" IN (SELECT "{kk_col}" FROM {table_ref} GROUP BY "{kk_col}" HAVING {having_sql})'
        if clauses:
            final_sql = f"({where_sub}) AND (" + " AND ".join(clauses) + ")"
        else:
            final_sql = where_sub
    elif clauses:
        final_sql = " AND ".join(clauses)
    else:
        final_sql = None

    explanation = "Filter cerdas alami: " + ", ".join(tags)
    return final_sql, tags, explanation

def build_where_clause(con, table_ref, params):
    try:
        cols_info = con.execute(f"DESCRIBE {table_ref};").fetchall()
        existing_cols = {r[0].lower(): r[0] for r in cols_info}
    except Exception:
        existing_cols = {}

    where_clauses = []
    nlq_info = {"is_nlq": False, "tags": [], "explanation": ""}

    # 1. Search filter & Search By
    search = clean_str(params.get('search'))
    search_by = clean_str(params.get('search_by', 'all')).lower()
    if not search:
        filters_map = params.get('filters', {})
        if isinstance(filters_map, dict):
            search = clean_str(filters_map.get('salary_search'))
            if not search:
                search = clean_str(filters_map.get('search'))
            if not search_by or search_by == 'all':
                search_by = clean_str(filters_map.get('search_by', 'all')).lower()

    if search:
        search_clean = search.lower().replace("'", "''")
        nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
        kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
        nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', 'nama'))

        if search_by == 'nik':
            digits = ''.join(c for c in search if c.isdigit())
            target_str = digits if digits else search_clean
            where_clauses.append(f'CAST("{nik_col}" AS VARCHAR) LIKE \'%{target_str}%\'')
        elif search_by in ('kk', 'no_kk'):
            digits = ''.join(c for c in search if c.isdigit())
            target_str = digits if digits else search_clean
            where_clauses.append(f'CAST("{kk_col}" AS VARCHAR) LIKE \'%{target_str}%\'')
        elif search_by in ('nama', 'nama_lengkap'):
            where_clauses.append(f'LOWER(CAST("{nama_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        elif search_by in ('wilayah', 'daerah'):
            prov_col = existing_cols.get('provinsi')
            kab_col = existing_cols.get('kabupaten_kota', existing_cols.get('kabupaten'))
            kec_col = existing_cols.get('kecamatan')
            desa_col = existing_cols.get('desa_kelurahan', existing_cols.get('desa'))
            alamat_col = existing_cols.get('alamat')
            w_parts = []
            if prov_col: w_parts.append(f'LOWER(CAST("{prov_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
            if kab_col: w_parts.append(f'LOWER(CAST("{kab_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
            if kec_col: w_parts.append(f'LOWER(CAST("{kec_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
            if desa_col: w_parts.append(f'LOWER(CAST("{desa_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
            if alamat_col: w_parts.append(f'LOWER(CAST("{alamat_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
            if w_parts:
                where_clauses.append('(' + ' OR '.join(w_parts) + ')')
        elif search_by in ('jenis_kelamin', 'jk', 'gender'):
            jk_col = existing_cols.get('jenis_kelamin', existing_cols.get('jk', existing_cols.get('gender')))
            if jk_col:
                where_clauses.append(f'LOWER(CAST("{jk_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        elif search_by in ('status_kawin', 'status_pernikahan', 'pernikahan'):
            sk_col = existing_cols.get('status_kawin', existing_cols.get('status_pernikahan'))
            if sk_col:
                where_clauses.append(f'LOWER(CAST("{sk_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        elif search_by in ('status_bekerja', 'pekerjaan'):
            sb_col = existing_cols.get('status_bekerja', existing_cols.get('pekerjaan'))
            if sb_col:
                where_clauses.append(f'LOWER(CAST("{sb_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        elif search_by in ('status_bantuan', 'bantuan'):
            sba_col = existing_cols.get('status_bantuan')
            if sba_col:
                where_clauses.append(f'LOWER(CAST("{sba_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        elif search_by in existing_cols:
            actual_col = existing_cols[search_by]
            where_clauses.append(f'LOWER(CAST("{actual_col}" AS VARCHAR)) LIKE \'%{search_clean}%\'')
        else:
            # Global 'all' search: first check Smart Semantic NLQ Parser
            nlq_sql, nlq_tags, nlq_expl = parse_semantic_nlq(search, existing_cols, table_ref)
            if nlq_sql and nlq_tags:
                where_clauses.append(f'({nlq_sql})')
                nlq_info = {
                    "is_nlq": True,
                    "tags": nlq_tags,
                    "explanation": nlq_expl
                }
            elif len(search) == 16 and search.isdigit():
                where_clauses.append(f'(CAST("{nik_col}" AS VARCHAR) = \'{search}\' OR CAST("{kk_col}" AS VARCHAR) = \'{search}\')')
            else:
                skip_cols = {'id', 'quality_issues', 'extra_attributes', 'created_at', 'updated_at'}
                searchable_cols = [col_orig for col_lower, col_orig in existing_cols.items() if col_lower not in skip_cols]
                if searchable_cols:
                    or_terms = [f'LOWER(CAST("{c}" AS VARCHAR)) LIKE \'%{search_clean}%\'' for c in searchable_cols]
                    where_clauses.append('(' + ' OR '.join(or_terms) + ')')

    # 1b. Bulk Search (NIK / KK)
    bulk_raw = params.get('bulk_search')
    if not bulk_raw:
        filters_map = params.get('filters', {})
        if isinstance(filters_map, dict):
            bulk_raw = filters_map.get('bulk_search')
    
    if bulk_raw:
        bulk_field = clean_str(params.get('bulk_field', 'nik')).lower()
        if not bulk_field or bulk_field == 'nik':
            filters_map = params.get('filters', {})
            if isinstance(filters_map, dict):
                bulk_field = clean_str(filters_map.get('bulk_field', 'nik')).lower()
        
        import re
        if isinstance(bulk_raw, list):
            raw_items = bulk_raw
        else:
            raw_items = re.split(r'[\r\n,;\s]+', str(bulk_raw).strip())
        
        cleaned_items = []
        for it in raw_items:
            c = re.sub(r'[^0-9A-Za-z]', '', str(it).strip())
            if c:
                cleaned_items.append(c)
        
        # Deduplicate preserving order (up to 5000 items)
        cleaned_items = list(dict.fromkeys(cleaned_items))[:5000]
        if cleaned_items:
            if bulk_field in ('kk', 'no_kk', 'nomor_kartu_keluarga'):
                target_bulk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            else:
                target_bulk_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            
            in_vals = ", ".join(["'" + v.replace("'", "''") + "'" for v in cleaned_items])
            where_clauses.append(f'CAST("{target_bulk_col}" AS VARCHAR) IN ({in_vals})')

    # 2. Quality status filter
    quality_status = clean_str(params.get('quality_status'))
    if not quality_status:
        filters_map_qs = params.get('filters', {})
        if isinstance(filters_map_qs, dict):
            quality_status = clean_str(filters_map_qs.get('quality_status'))

    if quality_status and quality_status.lower() != 'semua':
        qs_col = existing_cols.get('quality_status', 'quality_status')
        if quality_status.lower() in ('error', 'bermasalah', 'anomali', 'issues'):
            where_clauses.append(f'("{qs_col}" != \'Valid\' AND "{qs_col}" IS NOT NULL)')
        else:
            qs_escaped = quality_status.replace("'", "''")
            where_clauses.append(f'"{qs_col}" = \'{qs_escaped}\'')

    # 3. Dynamic multi-checkbox / column filters
    filters_map = params.get('filters', {})
    if isinstance(filters_map, dict):
        # Dynamic custom filter pair
        c_col = clean_str(filters_map.get('salary_filter_col', filters_map.get('custom_filter_col', '')))
        c_val = clean_str(filters_map.get('salary_filter_val', filters_map.get('custom_filter_val', '')))
        if c_col and c_val and c_val.lower() != 'semua':
            target_custom = resolve_target_column(c_col, existing_cols)
            if target_custom:
                c_clean = c_val.lower().replace("'", "''")
                where_clauses.append(f'LOWER(CAST("{target_custom}" AS VARCHAR)) LIKE \'%{c_clean}%\'')

        excluded_param_keys = (
            'page', 'search', 'search_by', 'bulk_search', 'bulk_field', 'quality_status', '_token', 
            'salary_filter_col', 'salary_filter_val', 'custom_filter_col', 'custom_filter_val',
            'order_by', 'sort_by', 'salary_sort', 'per_page', 'limit', 'salary_limit',
            'salary_metric_var', 'salary_group_col', 'salary_search', 'export'
        )

        for req_key, user_val in filters_map.items():
            if user_val is None or req_key in excluded_param_keys:
                continue
            
            if isinstance(user_val, list):
                val_arr = [clean_str(v) for v in user_val if clean_str(v) and clean_str(v).lower() != 'semua']
            else:
                val_arr = [clean_str(v) for v in str(user_val).split(',') if clean_str(v) and clean_str(v).lower() != 'semua']
            
            if not val_arr:
                continue

            raw_key = str(req_key).strip().lower()
            col_key = raw_key[7:] if raw_key.startswith('salary_') else raw_key

            # Wilayah / Daerah multi-column filter handling
            if col_key in ('wilayah', 'daerah') or raw_key in ('wilayah', 'daerah', 'salary_wilayah', 'salary_daerah'):
                prov_col = existing_cols.get('provinsi')
                kab_col = existing_cols.get('kabupaten_kota', existing_cols.get('kabupaten'))
                kec_col = existing_cols.get('kecamatan')
                w_parts = []
                for item in val_arr:
                    item_clean = str(item).strip().lower().replace("'", "''")
                    sub_parts = []
                    if prov_col: sub_parts.append(f'LOWER(CAST("{prov_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    if kab_col: sub_parts.append(f'LOWER(CAST("{kab_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    if kec_col: sub_parts.append(f'LOWER(CAST("{kec_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    if sub_parts:
                        w_parts.append('(' + ' OR '.join(sub_parts) + ')')
                if w_parts:
                    where_clauses.append('(' + ' OR '.join(w_parts) + ')')
                continue

            target_col = resolve_target_column(col_key, existing_cols)
            if not target_col:
                continue

            # Process filter values for target_col
            or_parts = []
            for item in val_arr:
                item_str = str(item).strip()
                item_clean = item_str.lower().replace("'", "''")
                
                # Age / Umur filter handling
                if any(x in col_key for x in ['usia', 'umur', 'age']) or target_col in ('usia', 'umur'):
                    if 'balita' in item_clean or '< 6' in item_clean or '<6' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) < 6')
                    elif 'anak' in item_clean or 'sekolah' in item_clean or '6-17' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) BETWEEN 6 AND 17')
                    elif 'produktif' in item_clean or 'kerja' in item_clean or 'dewasa' in item_clean or '18-59' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) BETWEEN 18 AND 59')
                    elif 'lansia' in item_clean or '> 60' in item_clean or '>= 60' in item_clean or '>=60' in item_clean or '>60' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) >= 60')
                    elif '<' in item_clean:
                        num_str = ''.join(c for c in item_clean if c.isdigit())
                        if num_str:
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) < {int(num_str)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif '>' in item_clean or '≥' in item_clean or '>=' in item_clean:
                        num_str = ''.join(c for c in item_clean if c.isdigit())
                        if num_str:
                            op = '>=' if ('≥' in item_clean or '>=' in item_clean) else '>'
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) {op} {int(num_str)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif '-' in item_str:
                        parts = item_str.split('-')
                        p0 = ''.join(c for c in parts[0] if c.isdigit())
                        p1 = ''.join(c for c in parts[1] if c.isdigit())
                        if p0 and p1:
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) BETWEEN {int(p0)} AND {int(p1)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif item_str.isdigit():
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) = {int(item_str)}')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # Desil filter handling
                elif 'desil' in col_key or target_col in ('desil', 'desil_nasional'):
                    if '-' in item_str:
                        parts = item_str.split('-')
                        p0 = ''.join(c for c in parts[0] if c.isdigit())
                        p1 = ''.join(c for c in parts[1] if c.isdigit())
                        if p0 and p1:
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) BETWEEN {int(p0)} AND {int(p1)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    else:
                        digits = ''.join(c for c in item_str if c.isdigit())
                        if digits and 1 <= int(digits) <= 10:
                            or_parts.append(f'(TRY_CAST("{target_col}" AS INTEGER) = {int(digits)} OR CAST("{target_col}" AS VARCHAR) = \'{digits}\')')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # Gender / Jenis Kelamin filter handling
                elif any(x in col_key for x in ['jenis_kelamin', 'gender', 'jk', 'sex']) or target_col in ('jenis_kelamin', 'jk'):
                    if item_clean in ('laki-laki', 'l', 'pria', 'laki'):
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) IN (\'laki-laki\', \'l\', \'pria\')')
                    elif item_clean in ('perempuan', 'p', 'wanita'):
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) IN (\'perempuan\', \'p\', \'wanita\')')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # NIK / KK digit prefix or exact match
                elif any(x in col_key for x in ['nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga']) or target_col in ('nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga', 'no_kk'):
                    digits = ''.join(c for c in item_str if c.isdigit())
                    if digits:
                        or_parts.append(f'CAST("{target_col}" AS VARCHAR) LIKE \'%{digits}%\'')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # Salary / Gaji filter handling (ONLY when col_key is really a salary dimension)
                elif any(x in col_key for x in ['gaji', 'pendapatan', 'penghasilan', 'salary', 'income']) or target_col in ('gaji', 'gaji_bulanan'):
                    num_only = item_str.replace('.', '').replace(',', '')
                    if '< 1.5' in item_clean or '< 1,5' in item_clean or '< 1500000' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) < 1500000')
                    elif '< 3' in item_clean or '<3' in item_clean or '< 3000000' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) < 3000000')
                    elif ('1.5' in item_clean or '1,5' in item_clean) and '3' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN 1500000 AND 3000000')
                    elif '3' in item_clean and '5' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN 3000000 AND 5000000')
                    elif '5' in item_clean and '10' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN 5000000 AND 10000000')
                    elif '> 10' in item_clean or '>10' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) > 10000000')
                    elif '> 5' in item_clean or '>5' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) > 5000000')
                    elif '-' in item_str and not any(c.isalpha() for c in item_str):
                        parts = item_str.split('-')
                        p0 = ''.join(c for c in parts[0] if c.isdigit())
                        p1 = ''.join(c for c in parts[1] if c.isdigit())
                        if p0 and p1:
                            or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN {float(p0)} AND {float(p1)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif num_only.isdigit():
                        exact_val = float(num_only)
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) = {exact_val}')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # General text filter (Nama, Alamat, Wilayah, Pekerjaan, dll)
                else:
                    or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

            if or_parts:
                where_clauses.append('(' + ' OR '.join(or_parts) + ')')

    where_sql = (' WHERE ' + ' AND '.join(where_clauses)) if where_clauses else ''
    return where_sql, existing_cols, nlq_info

def run_duckdb_query():
    db_path = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    mode = sys.argv[2] if len(sys.argv) > 2 else 'distinct_values'
    params_str = sys.argv[3] if len(sys.argv) > 3 else '{}'

    try:
        if params_str.startswith('{'):
            params = json.loads(params_str)
        else:
            params = json.loads(base64.b64decode(params_str).decode('utf-8'))
    except Exception:
        params = {}

    duck_file = os.path.join(os.path.dirname(db_path), 'dataset.duckdb')
    
    if os.path.exists(duck_file) and os.path.getsize(duck_file) > 0:
        con = duckdb.connect(duck_file, read_only=True)
        table_ref = "individus"
    else:
        # Fallback empty response if DuckDB dataset does not exist
        print(f"[DUCKDB_EMPTY] Dataset duckdb file missing", flush=True)
        sys.exit(0)

    where_sql, existing_cols, nlq_info = build_where_clause(con, table_ref, params)

    if mode == 'total_system_rows':
        try:
            total = con.execute(f"SELECT COUNT(*) FROM {table_ref};").fetchone()[0]
            print(f"[DUCKDB_COUNT_RESULT] {json.dumps({'total_system_rows': total})}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] Count failed: {e}", flush=True)

    elif mode == 'page_data':
        page = int(params.get('page', 1))
        per_page = int(params.get('per_page', 15))
        offset = (page - 1) * per_page

        try:
            # 1. Total system rows
            total_system_rows = con.execute(f"SELECT COUNT(*) FROM {table_ref};").fetchone()[0]

            # 2. Filtered total count
            filtered_total = con.execute(f"SELECT COUNT(*) FROM {table_ref}{where_sql};").fetchone()[0]

            # 3. Paginated items
            cols_list = list(existing_cols.values())
            select_cols = ", ".join([f'"{c}"' for c in cols_list])
            
            order_param = str(params.get('order_by', params.get('sort_by', ''))).lower().strip()
            salary_col = existing_cols.get('gaji_bulanan', existing_cols.get('gaji', None))
            usia_col = existing_cols.get('usia', existing_cols.get('umur', None))
            nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', None))
            
            if 'gaji' in existing_cols:
                num_salary = '"gaji"'
            elif salary_col:
                num_salary = f'TRY_CAST(REGEXP_REPLACE(CAST("{salary_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)'
            else:
                num_salary = 'id'

            if 'umur' in existing_cols:
                num_usia = '"umur"'
            elif usia_col:
                num_usia = f'TRY_CAST(REGEXP_REPLACE(CAST("{usia_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS INTEGER)'
            else:
                num_usia = 'id'

            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            tgl_col = existing_cols.get('tanggal_lahir', existing_cols.get('tgl_lahir', None))
            desil_col = existing_cols.get('desil_nasional', existing_cols.get('desil', None))
            num_desil = f'TRY_CAST("{desil_col}" AS INTEGER)' if desil_col else 'id'

            if order_param in ('gaji_desc', 'salary_desc', 'max_gaji', 'avg_desc', 'tertinggi'):
                order_clause = f"{num_salary} DESC NULLS LAST, id DESC"
            elif order_param in ('gaji_asc', 'salary_asc', 'min_gaji', 'avg_asc', 'terendah'):
                order_clause = f"{num_salary} ASC NULLS LAST, id ASC"
            elif order_param in ('usia_desc', 'umur_desc'):
                order_clause = f"{num_usia} DESC NULLS LAST, id DESC"
            elif order_param in ('usia_asc', 'umur_asc'):
                order_clause = f"{num_usia} ASC NULLS LAST, id ASC"
            elif order_param in ('nama_asc', 'name_asc') and nama_col:
                order_clause = f'"{nama_col}" ASC, id ASC'
            elif order_param in ('nama_desc', 'name_desc') and nama_col:
                order_clause = f'"{nama_col}" DESC, id DESC'
            elif order_param in ('nik_asc', 'nik_up'):
                order_clause = f'CAST("{nik_col}" AS VARCHAR) ASC, id ASC'
            elif order_param in ('nik_desc', 'nik_down'):
                order_clause = f'CAST("{nik_col}" AS VARCHAR) DESC, id DESC'
            elif order_param in ('tgl_asc', 'tgl_lahir_asc', 'lahir_asc') and tgl_col:
                order_clause = f'"{tgl_col}" ASC NULLS LAST, id ASC'
            elif order_param in ('tgl_desc', 'tgl_lahir_desc', 'lahir_desc') and tgl_col:
                order_clause = f'"{tgl_col}" DESC NULLS LAST, id DESC'
            elif order_param in ('desil_asc', 'desil_up') and desil_col:
                order_clause = f"{num_desil} ASC NULLS LAST, id ASC"
            elif order_param in ('desil_desc', 'desil_down') and desil_col:
                order_clause = f"{num_desil} DESC NULLS LAST, id DESC"
            elif order_param == 'id_asc':
                order_clause = "id ASC"
            else:
                order_clause = "id DESC"

            rows = con.execute(f"SELECT {select_cols} FROM {table_ref}{where_sql} ORDER BY {order_clause} LIMIT {per_page} OFFSET {offset};").fetchall()

            items = []
            for r in rows:
                row_dict = {}
                for idx, col_name in enumerate(cols_list):
                    val = r[idx]
                    if isinstance(val, (list, dict)):
                        row_dict[col_name] = val
                    elif val is None:
                        row_dict[col_name] = None
                    else:
                        row_dict[col_name] = str(val)
                items.append(row_dict)

            # 4. Filtered summary stats
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            stats_row = con.execute(f"""
                SELECT 
                    COUNT(*) as total_rows,
                    COUNT(DISTINCT "{kk_col}") as total_kk,
                    SUM(CASE WHEN quality_status = 'Valid' THEN 1 ELSE 0 END) as valid_cnt,
                    SUM(CASE WHEN quality_status = 'Warning' THEN 1 ELSE 0 END) as warn_cnt,
                    SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_cnt,
                    SUM(CASE WHEN len(quality_issues) >= 3 THEN 1 ELSE 0 END) as multi_err_cnt
                FROM {table_ref}{where_sql};
            """).fetchone()

            stats = {
                "total_rows": int(stats_row[0] or 0),
                "total_kk": int(stats_row[1] or 0),
                "valid_count": int(stats_row[2] or 0),
                "warning_count": int(stats_row[3] or 0),
                "critical_count": int(stats_row[4] or 0),
                "multi_error_count": int(stats_row[5] or 0),
                "error_count": int((stats_row[3] or 0) + (stats_row[4] or 0))
            }

            # 5. Top 6 issue records for audit sidebar
            issue_rows = con.execute(f"SELECT {select_cols} FROM {table_ref} WHERE quality_status = 'Critical' ORDER BY id DESC LIMIT 6;").fetchall()
            issue_items = []
            for r in issue_rows:
                row_dict = {}
                for idx, col_name in enumerate(cols_list):
                    val = r[idx]
                    row_dict[col_name] = val if isinstance(val, (list, dict)) else (str(val) if val is not None else None)
                issue_items.append(row_dict)

            result = {
                "total_system_rows": total_system_rows,
                "filtered_total": filtered_total,
                "page": page,
                "per_page": per_page,
                "items": items,
                "stats": stats,
                "issue_items": issue_items,
                "db_columns": cols_list,
                "nlq_info": nlq_info
            }

            print(f"[DUCKDB_PAGE_RESULT] {json.dumps(result)}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] Page data failed: {e}", flush=True)

    elif mode == 'distinct_values':
        target_cols = params.get('columns', [])
        result = {}
        for col in target_cols:
            col_clean = str(col).strip().lower()
            target_name = resolve_target_column(col_clean, existing_cols)
            if target_name and col_clean not in ('id', 'created_at', 'updated_at', 'extra_attributes', 'quality_issues', 'nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga', 'no_nik', 'no_kk'):
                try:
                    rows = con.execute(f'SELECT DISTINCT "{target_name}" FROM {table_ref} WHERE "{target_name}" IS NOT NULL AND TRIM(CAST("{target_name}" AS VARCHAR)) != \'\' ORDER BY "{target_name}" ASC LIMIT 100;').fetchall()
                    vals = [str(r[0]).strip() for r in rows if r[0] is not None and str(r[0]).strip() != '']
                    if vals:
                        result[col] = vals
                except Exception:
                    pass

        print(f"[DUCKDB_DISTINCT_RESULT] {json.dumps({'distinct_map': result})}", flush=True)

    elif mode == 'kpi_metrics':
        kpi_var = params.get('kpi_var', 'gaji_bulanan').lower()
        col_name = existing_cols.get(kpi_var, existing_cols.get('gaji_bulanan', existing_cols.get('gaji', None)))

        if not col_name:
            # Fallback scan for any numeric-like column
            for candidate in ['penghasilan', 'pendapatan', 'gaji_pokok', 'upah', 'salary', 'income', 'usia', 'umur', 'desil_nasional', 'desil']:
                if candidate in existing_cols:
                    col_name = existing_cols[candidate]
                    break

        if not col_name:
            # Fallback to first non-id column
            for k, v in existing_cols.items():
                if k not in ['id', 'quality_status', 'quality_issues', 'nik', 'no_kk', 'kk', 'nama_lengkap']:
                    col_name = v
                    break

        if not col_name:
            res = {'max': 0.0, 'min': 0.0, 'avg': 0.0, 'sum': 0.0, 'count': 0, 'top5': [], 'bot5': []}
            print(f"[DUCKDB_KPI_RESULT] {json.dumps(res)}", flush=True)
            con.close()
            return

        try:
            if 'gaji' in existing_cols and col_name in ('gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income'):
                num_expr = '"gaji"'
            elif 'umur' in existing_cols and col_name in ('umur', 'usia', 'age'):
                num_expr = '"umur"'
            elif 'desil' in existing_cols and col_name in ('desil', 'desil_nasional'):
                num_expr = '"desil"'
            else:
                num_expr = f'TRY_CAST(REGEXP_REPLACE(CAST("{col_name}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)'
            
            where_not_null = f"{where_sql} AND {num_expr} IS NOT NULL" if where_sql else f" WHERE {num_expr} IS NOT NULL"
            where_gt_zero = f"{where_sql} AND {num_expr} > 0" if where_sql else f" WHERE {num_expr} > 0"

            row = con.execute(f"""
                SELECT 
                    MAX({num_expr}) AS k_max,
                    MIN({num_expr}) AS k_min,
                    AVG({num_expr}) AS k_avg,
                    SUM({num_expr}) AS k_sum,
                    COUNT(CASE WHEN {num_expr} > 0 THEN 1 END) AS k_count
                FROM {table_ref}{where_not_null};
            """).fetchone()

            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', 'nama'))

            # 1. Distinct Top 5 Values (Peringkat 1 s/d 5 berjenjang dengan angka tertinggi di kelompok terfilter)
            top_rows = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_not_null} ORDER BY {num_expr} DESC NULLS LAST, id ASC LIMIT 5;').fetchall()

            # 2. Distinct Bottom 5 Values (Peringkat 1 s/d 5 berjenjang dengan angka terendah > 0 di kelompok terfilter)
            bot_rows = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_gt_zero} ORDER BY {num_expr} ASC NULLS LAST, id ASC LIMIT 5;').fetchall()

            # 3. Macro Demographics Summary
            demographics = {}
            try:
                jk_col = existing_cols.get('jenis_kelamin', existing_cols.get('jk', existing_cols.get('gender', None)))
                if jk_col:
                    demographics['gender'] = [{'name': str(r[0]), 'count': int(r[1])} for r in con.execute(f'SELECT COALESCE(CAST("{jk_col}" AS VARCHAR), \'Lainnya\'), COUNT(*) FROM {table_ref}{where_sql} GROUP BY 1 ORDER BY 2 DESC;').fetchall()]

                desil_col = existing_cols.get('desil_nasional', existing_cols.get('desil', None))
                if desil_col:
                    demographics['desil'] = [{'name': 'Desil ' + str(r[0]), 'count': int(r[1])} for r in con.execute(f'SELECT COALESCE(CAST("{desil_col}" AS VARCHAR), \'-\') AS d_name, COUNT(*) AS d_cnt FROM {table_ref}{where_sql} GROUP BY d_name ORDER BY TRY_CAST(REGEXP_REPLACE(d_name, \'[^0-9]\', \'\', \'g\') AS INTEGER) ASC;').fetchall()]

                usia_col = existing_cols.get('usia', existing_cols.get('umur', None))
                if usia_col:
                    u_expr = f'TRY_CAST(REGEXP_REPLACE(CAST("{usia_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)'
                    u_row = con.execute(f'''
                        SELECT 
                            ROUND(AVG({u_expr}), 1),
                            COUNT(CASE WHEN {u_expr} < 6 THEN 1 END),
                            COUNT(CASE WHEN {u_expr} BETWEEN 6 AND 17 THEN 1 END),
                            COUNT(CASE WHEN {u_expr} BETWEEN 18 AND 59 THEN 1 END),
                            COUNT(CASE WHEN {u_expr} >= 60 THEN 1 END)
                        FROM {table_ref}{where_sql}
                        WHERE {u_expr} IS NOT NULL;
                    ''').fetchone()
                    if u_row:
                        demographics['age'] = {
                            'avg_age': float(u_row[0]) if u_row[0] is not None else 0.0,
                            'balita': int(u_row[1] or 0),
                            'anak': int(u_row[2] or 0),
                            'produktif': int(u_row[3] or 0),
                            'lansia': int(u_row[4] or 0)
                        }

                kerja_col = existing_cols.get('status_bekerja', existing_cols.get('pekerjaan', None))
                if kerja_col:
                    demographics['employment'] = [{'name': str(r[0]), 'count': int(r[1])} for r in con.execute(f'SELECT COALESCE(CAST("{kerja_col}" AS VARCHAR), \'Lainnya\'), COUNT(*) FROM {table_ref}{where_sql} GROUP BY 1 ORDER BY 2 DESC LIMIT 6;').fetchall()]

                kawin_col = existing_cols.get('status_kawin', existing_cols.get('status_pernikahan', None))
                if kawin_col:
                    demographics['marital'] = [{'name': str(r[0]), 'count': int(r[1])} for r in con.execute(f'SELECT COALESCE(CAST("{kawin_col}" AS VARCHAR), \'Lainnya\'), COUNT(*) FROM {table_ref}{where_sql} GROUP BY 1 ORDER BY 2 DESC LIMIT 6;').fetchall()]
            except Exception as e_demo:
                demographics['error'] = str(e_demo)

            res = {
                'max': float(row[0]) if row and row[0] is not None else 0.0,
                'min': float(row[1]) if row and row[1] is not None else 0.0,
                'avg': round(float(row[2]), 2) if row and row[2] is not None else 0.0,
                'sum': float(row[3]) if row and row[3] is not None else 0.0,
                'count': int(row[4]) if row and row[4] is not None else 0,
                'top5': [{'id': r[0], 'nik': r[1], 'kk': r[2], 'nama': r[3], 'val': r[4]} for r in top_rows],
                'bot5': [{'id': r[0], 'nik': r[1], 'kk': r[2], 'nama': r[3], 'val': r[4]} for r in bot_rows],
                'demographics': demographics
            }
            print(f"[DUCKDB_KPI_RESULT] {json.dumps(res)}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] KPI Query failed: {e}", flush=True)

    elif mode == 'preview':
        record_id = int(params.get('id', 0))
        try:
            cols_list = list(existing_cols.values())
            select_cols = ", ".join([f'"{c}"' for c in cols_list])
            r = con.execute(f"SELECT {select_cols} FROM {table_ref} WHERE id = {record_id};").fetchone()
            if r:
                row_dict = {}
                for idx, col_name in enumerate(cols_list):
                    val = r[idx]
                    row_dict[col_name] = val if isinstance(val, (list, dict)) else (str(val) if val is not None else None)
                print(f"[DUCKDB_PREVIEW_RESULT] {json.dumps({'item': row_dict})}", flush=True)
            else:
                print(f"[DUCKDB_PREVIEW_RESULT] {json.dumps({'item': None})}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] Preview Query failed: {e}", flush=True)

    elif mode == 'salary_breakdown':
        group_key = params.get('group_col', '').lower().strip()
        group_col = existing_cols.get(group_key)
        if not group_col:
            for cand in ['jenis_kelamin', 'status_bekerja', 'desil_nasional', 'status_kawin', 'pendidikan', 'kecamatan']:
                if cand in existing_cols:
                    group_col = existing_cols[cand]
                    group_key = cand
                    break
        if not group_col and existing_cols:
            group_col = list(existing_cols.values())[0]
            group_key = list(existing_cols.keys())[0]

        salary_key = params.get('salary_var', 'gaji_bulanan').lower().strip()
        salary_col = existing_cols.get(salary_key, existing_cols.get('gaji_bulanan', existing_cols.get('gaji', None)))
        if not salary_col:
            for cand in ['penghasilan', 'pendapatan', 'gaji_pokok', 'upah', 'salary', 'income']:
                if cand in existing_cols:
                    salary_col = existing_cols[cand]
                    break

        if not group_col or not salary_col:
            res = {'group_col': group_key, 'group_label': group_key, 'salary_col': salary_key, 'items': [], 'summary': {}}
            print(f"[DUCKDB_BREAKDOWN_RESULT] {json.dumps(res)}", flush=True)
            con.close()
            return

        try:
            if 'gaji' in existing_cols and salary_col in ('gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income'):
                num_expr = '"gaji"'
            else:
                num_expr = f'TRY_CAST(REGEXP_REPLACE(CAST("{salary_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)'
            where_cond = f"{where_sql} AND {num_expr} IS NOT NULL" if where_sql else f" WHERE {num_expr} IS NOT NULL"

            sort_by = params.get('sort_by', 'avg_desc')
            if sort_by == 'avg_asc':
                order_clause = "avg_salary ASC"
            elif sort_by == 'count_desc':
                order_clause = "subject_count DESC"
            elif sort_by == 'count_asc':
                order_clause = "subject_count ASC"
            elif sort_by == 'sum_desc':
                order_clause = "total_salary DESC"
            elif sort_by == 'sum_asc':
                order_clause = "total_salary ASC"
            elif sort_by == 'category_asc':
                order_clause = "category ASC"
            else:
                order_clause = "avg_salary DESC"

            limit = int(params.get('limit', 100))

            query = f"""
                SELECT 
                    COALESCE(NULLIF(TRIM(CAST("{group_col}" AS VARCHAR)), ''), 'Tidak Terisi') AS category,
                    COUNT(*) AS subject_count,
                    ROUND(AVG({num_expr}), 2) AS avg_salary,
                    MIN({num_expr}) AS min_salary,
                    MAX({num_expr}) AS max_salary,
                    SUM({num_expr}) AS total_salary,
                    ROUND(MEDIAN({num_expr}), 2) AS median_salary
                FROM {table_ref}
                {where_cond}
                GROUP BY 1
                ORDER BY {order_clause}
                LIMIT {limit};
            """
            rows = con.execute(query).fetchall()

            tot_query = f"""
                SELECT 
                    COUNT(*) AS total_count,
                    ROUND(AVG({num_expr}), 2) AS total_avg,
                    MIN({num_expr}) AS total_min,
                    MAX({num_expr}) AS total_max,
                    SUM({num_expr}) AS grand_sum,
                    COUNT(DISTINCT COALESCE(NULLIF(TRIM(CAST("{group_col}" AS VARCHAR)), ''), 'Tidak Terisi')) AS distinct_categories
                FROM {table_ref}
                {where_cond};
            """
            tot_row = con.execute(tot_query).fetchone()

            grand_count = int(tot_row[0] or 0) if tot_row else 0
            grand_sum = float(tot_row[4] or 0) if tot_row else 0.0

            items = []
            max_avg_in_items = max([float(r[2] or 0) for r in rows], default=1.0)
            if max_avg_in_items <= 0:
                max_avg_in_items = 1.0

            for r in rows:
                cnt = int(r[1] or 0)
                avg_val = float(r[2] or 0)
                min_val = float(r[3] or 0)
                max_val = float(r[4] or 0)
                sum_val = float(r[5] or 0)
                med_val = float(r[6] or 0)

                pct_count = round((cnt / grand_count) * 100, 2) if grand_count > 0 else 0.0
                pct_sum = round((sum_val / grand_sum) * 100, 2) if grand_sum > 0 else 0.0
                bar_pct = round((avg_val / max_avg_in_items) * 100, 1)

                items.append({
                    'category': str(r[0]),
                    'count': cnt,
                    'pct_count': pct_count,
                    'avg': avg_val,
                    'min': min_val,
                    'max': max_val,
                    'sum': sum_val,
                    'pct_sum': pct_sum,
                    'median': med_val,
                    'bar_pct': bar_pct
                })

            summary = {
                'total_count': grand_count,
                'total_avg': float(tot_row[1] or 0) if tot_row else 0.0,
                'total_min': float(tot_row[2] or 0) if tot_row else 0.0,
                'total_max': float(tot_row[3] or 0) if tot_row else 0.0,
                'grand_sum': grand_sum,
                'category_count': int(tot_row[5] or 0) if tot_row else len(items)
            }

            res = {
                'group_col': group_key,
                'group_label': group_col,
                'salary_col': salary_col,
                'items': items,
                'summary': summary
            }
            print(f"[DUCKDB_BREAKDOWN_RESULT] {json.dumps(res)}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] Breakdown Query failed: {e}", flush=True)

    elif mode == 'export_search_csv':
        export_file = params.get('export_path')
        if not export_file:
            export_file = os.path.join(os.path.dirname(db_path), 'search_export.csv')
        
        limit_rows = int(params.get('limit', 100000))
        cols_list = list(existing_cols.values())
        select_cols = ", ".join([f'"{c}"' for c in cols_list])
        
        order_param = str(params.get('order_by', params.get('sort_by', ''))).lower().strip()
        salary_col = existing_cols.get('gaji_bulanan', existing_cols.get('gaji', None))
        nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', None))
        nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
        
        if 'gaji' in existing_cols:
            num_salary = '"gaji"'
        elif salary_col:
            num_salary = f'TRY_CAST(REGEXP_REPLACE(CAST("{salary_col}" AS VARCHAR), \'[^0-9.]\', \'\', \'g\') AS DOUBLE)'
        else:
            num_salary = 'id'

        if order_param in ('gaji_desc', 'salary_desc'):
            order_clause = f"{num_salary} DESC NULLS LAST, id DESC"
        elif order_param in ('gaji_asc', 'salary_asc'):
            order_clause = f"{num_salary} ASC NULLS LAST, id ASC"
        elif order_param in ('nama_asc', 'name_asc') and nama_col:
            order_clause = f'"{nama_col}" ASC, id ASC'
        elif order_param in ('nama_desc', 'name_desc') and nama_col:
            order_clause = f'"{nama_col}" DESC, id DESC'
        elif order_param in ('nik_asc', 'nik_up'):
            order_clause = f'CAST("{nik_col}" AS VARCHAR) ASC, id ASC'
        elif order_param in ('nik_desc', 'nik_down'):
            order_clause = f'CAST("{nik_col}" AS VARCHAR) DESC, id DESC'
        elif order_param == 'id_asc':
            order_clause = "id ASC"
        else:
            order_clause = "id DESC"

        try:
            export_clean_path = export_file.replace('\\', '/')
            con.execute(f"COPY (SELECT {select_cols} FROM {table_ref}{where_sql} ORDER BY {order_clause} LIMIT {limit_rows}) TO '{export_clean_path}' (HEADER, DELIMITER ',');")
            
            exported_count = con.execute(f"SELECT COUNT(*) FROM read_csv_auto('{export_clean_path}');").fetchone()[0]
            print(f"[DUCKDB_EXPORT_RESULT] {json.dumps({'export_path': export_file, 'rows': exported_count})}", flush=True)
        except Exception as e:
            print(f"[DUCKDB_ERROR] Export search CSV failed: {e}", flush=True)

    con.close()

if __name__ == '__main__':
    run_duckdb_query()
