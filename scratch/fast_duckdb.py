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

    # 3. Dynamic multi-checkbox / column filters
    filters_map = params.get('filters', {})
    if isinstance(filters_map, dict):
        for req_key, user_val in filters_map.items():
            if not user_val or user_val == 'semua':
                continue
            
            val_arr = user_val if isinstance(user_val, list) else [v.strip() for v in str(user_val).split(',') if v.strip() and v.strip() != 'semua']
            if not val_arr:
                continue

            col_key = str(req_key).strip().lower()
            target_col = existing_cols.get(col_key, None)
            if not target_col:
                # Try finding synonym
                syn_map = {
                    'gaji': 'gaji_bulanan', 'pendapatan': 'gaji_bulanan', 'income': 'gaji_bulanan', 'salary': 'gaji_bulanan',
                    'desil': 'desil_nasional', 'desil_kesejahteraan': 'desil_nasional',
                    'jk': 'jenis_kelamin', 'gender': 'jenis_kelamin',
                    'nik': 'nomor_induk_kependudukan', 'no_nik': 'nomor_induk_kependudukan',
                    'kk': 'nomor_kartu_keluarga', 'no_kk': 'nomor_kartu_keluarga',
                    'umur': 'usia', 'age': 'usia'
                }
                syn_col = syn_map.get(col_key)
                if syn_col:
                    target_col = existing_cols.get(syn_col, None)

            if not target_col:
                continue

            # Process filter values for target_col
            or_parts = []
            for item in val_arr:
                item_str = str(item).strip()
                item_clean = item_str.lower().replace("'", "''")
                
                # Age filter handling
                if col_key in ['usia', 'umur', 'age']:
                    if '-' in item_str:
                        parts = item_str.split('-')
                        if len(parts) == 2 and parts[0].strip().isdigit() and parts[1].strip().isdigit():
                            min_v = int(parts[0].strip())
                            max_v = int(parts[1].strip())
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) BETWEEN {min_v} AND {max_v}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif '>' in item_str:
                        num_str = ''.join(c for c in item_str if c.isdigit())
                        if num_str:
                            num_v = int(num_str)
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) > {num_v}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif '<' in item_str:
                        num_str = ''.join(c for c in item_str if c.isdigit())
                        if num_str:
                            num_v = int(num_str)
                            or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) < {num_v}')
                        else:
                            or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                    elif item_str.isdigit():
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) = {int(item_str)}')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                # Salary filter handling
                elif col_key in ['gaji', 'gaji_bulanan', 'salary', 'income']:
                    if '<3' in item_clean or '3.000.000' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) < 3000000')
                    elif '3' in item_clean and '5' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN 3000000 AND 5000000')
                    elif '5' in item_clean and '10' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) BETWEEN 5000000 AND 10000000')
                    elif '>10' in item_clean or '10.000.000' in item_clean:
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) > 10000000')
                    elif item_str.replace('.', '').isdigit():
                        exact_val = float(item_str.replace('.', ''))
                        or_parts.append(f'TRY_CAST("{target_col}" AS DOUBLE) = {exact_val}')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                # Desil filter handling
                elif col_key in ['desil', 'desil_nasional']:
                    digits = ''.join(c for c in item_str if c.isdigit())
                    if digits and 1 <= int(digits) <= 10:
                        or_parts.append(f'TRY_CAST("{target_col}" AS INTEGER) = {int(digits)}')
                    else:
                        or_parts.append(f'LOWER(CAST("{target_col}" AS VARCHAR)) LIKE \'%{item_clean}%\'')
                # General text filter
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
            target_name = existing_cols.get(col_clean)
            if target_name and col_clean not in ('id', 'created_at', 'updated_at', 'extra_attributes'):
                try:
                    rows = con.execute(f'SELECT DISTINCT "{target_name}" FROM {table_ref} WHERE "{target_name}" IS NOT NULL AND TRIM(CAST("{target_name}" AS VARCHAR)) != \'\' LIMIT 50;').fetchall()
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
            row = con.execute(f"""
                SELECT 
                    MAX({num_expr}) AS k_max,
                    MIN({num_expr}) AS k_min,
                    AVG({num_expr}) AS k_avg,
                    SUM({num_expr}) AS k_sum,
                    COUNT(CASE WHEN {num_expr} > 0 THEN 1 END) AS k_count
                FROM {table_ref}{where_sql}
                WHERE {num_expr} IS NOT NULL;
            """).fetchone()

            nik_col = existing_cols.get('nomor_induk_kependudukan', existing_cols.get('nik', 'nik'))
            kk_col = existing_cols.get('nomor_kartu_keluarga', existing_cols.get('no_kk', existing_cols.get('kk', 'kk')))
            nama_col = existing_cols.get('nama', existing_cols.get('nama_lengkap', 'nama'))

            top_rows = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_sql} WHERE {num_expr} IS NOT NULL ORDER BY {num_expr} DESC LIMIT 5;').fetchall()
            bot_rows = con.execute(f'SELECT id, "{nik_col}", "{kk_col}", "{nama_col}", {num_expr} FROM {table_ref}{where_sql} WHERE {num_expr} > 0 ORDER BY {num_expr} ASC LIMIT 5;').fetchall()

            res = {
                'max': float(row[0]) if row and row[0] is not None else 0.0,
                'min': float(row[1]) if row and row[1] is not None else 0.0,
                'avg': round(float(row[2]), 2) if row and row[2] is not None else 0.0,
                'sum': float(row[3]) if row and row[3] is not None else 0.0,
                'count': int(row[4]) if row and row[4] is not None else 0,
                'top5': [{'id': r[0], 'nik': r[1], 'kk': r[2], 'nama': r[3], 'val': r[4]} for r in top_rows],
                'bot5': [{'id': r[0], 'nik': r[1], 'kk': r[2], 'nama': r[3], 'val': r[4]} for r in bot_rows]
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
