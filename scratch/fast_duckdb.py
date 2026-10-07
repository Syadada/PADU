import sys
import os
import json
import base64
import duckdb

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

def build_where_clause(con, table_ref, params):
    try:
        cols_info = con.execute(f"DESCRIBE {table_ref};").fetchall()
        existing_cols = {r[0].lower(): r[0] for r in cols_info}
    except Exception:
        existing_cols = {}

    where_clauses = []

    # 1. Search filter
    search = clean_str(params.get('search'))
    if not search:
        filters_map = params.get('filters', {})
        if isinstance(filters_map, dict):
            search = clean_str(filters_map.get('salary_search'))
            if not search:
                search = clean_str(filters_map.get('search'))
    if search:
        search_clean = search.lower()
        if len(search) == 16 and search.isdigit():
            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            where_clauses.append(f'(CAST("{nik_col}" AS VARCHAR) = \'{search}\' OR CAST("{kk_col}" AS VARCHAR) = \'{search}\')')
        elif search.isdigit():
            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            where_clauses.append(f'(CAST("{nik_col}" AS VARCHAR) LIKE \'{search}%\' OR CAST("{kk_col}" AS VARCHAR) LIKE \'{search}%\')')
        else:
            nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', 'nama'))
            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            search_escaped = search_clean.replace("'", "''")
            where_clauses.append(f'(LOWER(CAST("{nama_col}" AS VARCHAR)) LIKE \'%{search_escaped}%\' OR LOWER(CAST("{nik_col}" AS VARCHAR)) LIKE \'%{search_escaped}%\' OR LOWER(CAST("{kk_col}" AS VARCHAR)) LIKE \'%{search_escaped}%\')')

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
            'page', 'search', 'quality_status', '_token', 
            'salary_filter_col', 'salary_filter_val', 'custom_filter_col', 'custom_filter_val',
            'order_by', 'sort_by', 'salary_sort', 'per_page', 'limit', 'salary_limit',
            'salary_metric_var', 'salary_group_col', 'salary_search'
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
    return where_sql, existing_cols

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

    where_sql, existing_cols = build_where_clause(con, table_ref, params)

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
                    SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_cnt
                FROM {table_ref}{where_sql};
            """).fetchone()

            stats = {
                "total_rows": int(stats_row[0] or 0),
                "total_kk": int(stats_row[1] or 0),
                "valid_count": int(stats_row[2] or 0),
                "warning_count": 0,
                "critical_count": int(stats_row[3] or 0),
                "multi_error_count": 0,
                "error_count": int(stats_row[3] or 0)
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
                "db_columns": cols_list
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
            if target_name and col_clean not in ('id', 'created_at', 'updated_at', 'extra_attributes', 'quality_issues'):
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

    con.close()

if __name__ == '__main__':
    run_duckdb_query()
