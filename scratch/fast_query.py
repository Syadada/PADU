import sys
import os
import json
import sqlite3

def run_fast_query(db_path, params_json_str="{}"):
    if not os.path.exists(db_path):
        print(json.dumps({"error": f"Database '{db_path}' tidak ditemukan."}))
        sys.exit(1)

    try:
        params = json.loads(params_json_str)
    except:
        params = {}

    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()

    cursor.execute("PRAGMA journal_mode = WAL;")
    cursor.execute("PRAGMA mmap_size = 30000000000;")
    cursor.execute("PRAGMA temp_store = MEMORY;")

    # Combined single query for 15M+ records
    cursor.execute("""
        SELECT 
            COUNT(*) as total_rows,
            COUNT(DISTINCT nomor_kartu_keluarga) as total_kk,
            SUM(CASE WHEN quality_status = 'Valid' THEN 1 ELSE 0 END) as valid_count,
            SUM(CASE WHEN quality_status = 'Warning' THEN 1 ELSE 0 END) as warning_count,
            SUM(CASE WHEN quality_status = 'Critical' THEN 1 ELSE 0 END) as critical_count
        FROM individus;
    """)
    stats = cursor.fetchone()

    total_rows = stats['total_rows'] or 0
    total_kk = stats['total_kk'] or 0
    valid_count = stats['valid_count'] or 0
    warning_count = stats['warning_count'] or 0
    critical_count = stats['critical_count'] or 0

    # Salary stats
    cursor.execute("SELECT MAX(gaji_bulanan) as mx, MIN(gaji_bulanan) as mn, AVG(gaji_bulanan) as av, SUM(gaji_bulanan) as sm FROM individus WHERE gaji_bulanan > 0;")
    sal_row = cursor.fetchone()
    
    gaji_max = float(sal_row['mx'] or 0)
    gaji_min = float(sal_row['mn'] or 0)
    gaji_avg = round(float(sal_row['av'] or 0), 2)
    gaji_sum = float(sal_row['sm'] or 0)

    conn.close()

    result = {
        "success": True,
        "total_rows": total_rows,
        "total_kk": total_kk,
        "valid_count": valid_count,
        "warning_count": warning_count,
        "critical_count": critical_count,
        "gaji_max": gaji_max,
        "gaji_min": gaji_min,
        "gaji_avg": gaji_avg,
        "gaji_sum": gaji_sum
    }

    print(json.dumps(result))

if __name__ == '__main__':
    db_f = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    p_str = sys.argv[2] if len(sys.argv) > 2 else '{}'
    run_fast_query(db_f, p_str)
