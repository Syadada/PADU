import sys
import os
import json
import base64
import duckdb

def build_where_clause(con, table_ref, params):
    try:
        cols_info = con.execute(f"DESCRIBE {table_ref};").fetchall()
        existing_cols = {r[0].lower(): r[0] for r in cols_info}
    except Exception:
        existing_cols = {}

    where_clauses = []

    # 1. Search filter
    search = str(params.get('search', '')).strip()
    if not search:
        filters_map = params.get('filters', {})
        if isinstance(filters_map, dict):
            search = str(filters_map.get('salary_search', '')).strip()
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
    quality_status = str(params.get('quality_status', 'semua')).strip()
    if quality_status and quality_status.lower() != 'semua':
        qs_col = existing_cols.get('quality_status', 'quality_status')
        qs_escaped = quality_status.replace("'", "''")
        where_clauses.append(f'"{qs_col}" = \'{qs_escaped}\'')

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
            'desa': 'kelurahan_desa', 'kelurahan': 'kelurahan_desa'
        }
        if k in syns and syns[k] in ex_cols:
            return ex_cols[syns[k]]
        for exist_k, exist_name in ex_cols.items():
            if k in exist_k or exist_k in k:
                return exist_name
        return None

    # 3. Dynamic multi-checkbox / column filters
    filters_map = params.get('filters', {})
    if isinstance(filters_map, dict):
        for req_key, user_val in filters_map.items():
            if not user_val or user_val == 'semua' or req_key in ('page', 'search', 'quality_status', '_token'):
                continue
            
            val_arr = user_val if isinstance(user_val, list) else [v.strip() for v in str(user_val).split(',') if v.strip() and v.strip() != 'semua']
            if not val_arr:
                continue

            col_key = str(req_key).strip().lower()
            target_col = resolve_target_column(col_key, existing_cols)
            if not target_col:
                continue

            # Process filter values for target_col
            or_parts = []
            for item in val_arr:
                item_str = str(item).strip()
                item_clean = item_str.lower().replace("'", "''")
                
                # Age / Umur filter handling
                if any(x in col_key for x in ['usia', 'umur', 'age']):
                    if '<' in item_clean:
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

                # Salary / Gaji filter handling
                elif any(x in col_key for x in ['gaji', 'pendapatan', 'penghasilan', 'salary', 'income']):
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

                # Desil filter handling
                elif 'desil' in col_key:
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
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) = {int(digits)}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # Gender / Jenis Kelamin filter handling
                elif any(x in col_key for x in ['jenis_kelamin', 'gender', 'jk', 'sex']):
                    if item_clean in ('laki-laki', 'l', 'pria', 'laki'):
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) IN (\'laki-laki\', \'l\', \'pria\')')
                    elif item_clean in ('perempuan', 'p', 'wanita'):
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) IN (\'perempuan\', \'p\', \'wanita\')')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')

                # NIK / KK digit prefix or exact match
                elif any(x in col_key for x in ['nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga']):
                    digits = ''.join(c for c in item_str if c.isdigit())
                    if digits:
                        or_parts.append(f'CAST("{target_col}" AS VARCHAR) LIKE \'%{digits}%\'')
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
            
            rows = con.execute(f"SELECT {select_cols} FROM {table_ref}{where_sql} ORDER BY id DESC LIMIT {per_page} OFFSET {offset};").fetchall()

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

            # 1. Distinct Top 5 Values (Peringkat 1 s/d 5 berjenjang dengan angka berbeda)
            top_vals = [r[0] for r in con.execute(f'SELECT DISTINCT {num_expr} FROM {table_ref}{where_not_null} ORDER BY {num_expr} DESC LIMIT 5;').fetchall()]
            top_rows = []
            for v in top_vals:
                where_v = f"{where_sql} AND {num_expr} = {v}" if where_sql else f" WHERE {num_expr} = {v}"
                r = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_v} ORDER BY id ASC LIMIT 1;').fetchone()
                if r:
                    top_rows.append(r)

            if len(top_rows) < 5:
                existing_top_ids = [r[0] for r in top_rows]
                id_filter = f" AND id NOT IN ({','.join(str(i) for i in existing_top_ids)})" if existing_top_ids else ""
                extra = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_not_null}{id_filter} ORDER BY {num_expr} DESC LIMIT {5 - len(top_rows)};').fetchall()
                top_rows.extend(extra)

            # 2. Distinct Bottom 5 Values (Peringkat 1 s/d 5 terendah berjenjang dengan angka berbeda)
            bot_vals = [r[0] for r in con.execute(f'SELECT DISTINCT {num_expr} FROM {table_ref}{where_gt_zero} ORDER BY {num_expr} ASC LIMIT 5;').fetchall()]
            bot_rows = []
            for v in bot_vals:
                where_bv = f"{where_sql} AND {num_expr} = {v}" if where_sql else f" WHERE {num_expr} = {v}"
                r = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_bv} ORDER BY id ASC LIMIT 1;').fetchone()
                if r:
                    bot_rows.append(r)

            if len(bot_rows) < 5:
                existing_bot_ids = [r[0] for r in bot_rows]
                id_bot_filter = f" AND id NOT IN ({','.join(str(i) for i in existing_bot_ids)})" if existing_bot_ids else ""
                extra_bot = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_gt_zero}{id_bot_filter} ORDER BY {num_expr} ASC LIMIT {5 - len(bot_rows)};').fetchall()
                bot_rows.extend(extra_bot)

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

    con.close()

if __name__ == '__main__':
    run_duckdb_query()
