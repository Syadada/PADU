import sys
import os
import duckdb

def run_fast_delete(db_path):
    print(f"[*] Memulai Fast Delete & Reset Total Database: {db_path}")

    base_db_dir = os.path.dirname(os.path.abspath(db_path))

    # 1. Bersihkan tabel DuckDB
    duck_file = os.path.join(base_db_dir, 'dataset.duckdb')
    if os.path.exists(duck_file):
        try:
            con = duckdb.connect(duck_file)
            tables = [t[0] for t in con.execute("SHOW TABLES").fetchall()]
            for t in tables:
                con.execute(f'DROP TABLE IF EXISTS "{t}";')
            con.execute("CHECKPOINT;")
            con.close()
        except Exception as e:
            print(f"[WARN] Gagal drop table DuckDB: {e}")

        try:
            os.remove(duck_file)
        except Exception as e:
            print(f"[WARN] Gagal remove {duck_file}: {e}")

    # 2. Bersihkan HANYA tabel dataset analitik di database.sqlite (JANGAN PERNAH MENGHAPUS file database.sqlite atau tabel users, migrations, sessions!)
    try:
        if os.path.exists(db_path) and os.path.getsize(db_path) > 0:
            import sqlite3
            con = sqlite3.connect(db_path)
            cur = con.cursor()
            existing_tables = [r[0] for r in cur.execute("SELECT name FROM sqlite_master WHERE type='table';").fetchall()]
            for tbl in ['individus', 'keluargas', 'demographics']:
                if tbl in existing_tables:
                    cur.execute(f'DELETE FROM "{tbl}";')
            con.commit()
            try:
                cur.execute("VACUUM;")
            except Exception:
                pass
            con.close()
            print("[INFO] Tabel dataset analitik (individus, keluargas, demographics) berhasil dikosongkan. Akun pengguna & sistem tetap aman.")
    except Exception as e:
        print(f"[WARN] Gagal membersihkan tabel dataset SQLite {db_path}: {e}")

    # 3. Hapus file metadata aktif di database/
    for json_name in ['active_dataset.json', 'dataset_hashes.json']:
        json_path = os.path.join(base_db_dir, json_name)
        if os.path.exists(json_path):
            try:
                os.remove(json_path)
            except Exception as e:
                print(f"[WARN] Gagal hapus {json_path}: {e}")

    print("[SUCCESS] Data berhasil dikosongkan total secara instan!")

if __name__ == '__main__':
    db_f = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    run_fast_delete(db_f)
