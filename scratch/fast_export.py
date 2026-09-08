import sys
import os
import json
import time
import zipfile
import duckdb
from datetime import datetime

def run_fast_export(db_path, export_dir, mode="as_is", params_json="{}"):
    start_time = time.time()
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

    if not os.path.exists(export_dir):
        os.makedirs(export_dir, exist_ok=True)

    duck_file = os.path.join(os.path.dirname(db_path), 'dataset.duckdb')
    
    if not os.path.exists(duck_file) or os.path.getsize(duck_file) == 0:
        print(f"Error: Database DuckDB '{duck_file}' tidak ditemukan atau kosong.", flush=True)
        sys.exit(1)

    con = duckdb.connect(duck_file)

    try:
        filters = json.loads(params_json) if isinstance(params_json, str) else params_json
    except Exception:
        filters = {}

    # MODE 1: EXPORT AUDIT REPORT (CSV ONLY)
    if mode == 'errors_only':
        file_name = f"laporan_audit_kualitas_data_dtsen_{timestamp}.csv"
        file_path = os.path.join(export_dir, file_name)
        file_path_escaped = file_path.replace('\\', '/')

        print(f"[*] Memulai Export Laporan Audit Error ke: {file_path}", flush=True)
        try:
            sql = f"""
            COPY (
                SELECT 
                    id AS "No. Baris",
                    quality_status AS "Tingkat Validitas",
                    CAST(nomor_induk_kependudukan AS VARCHAR) AS "NIK",
                    CAST(nomor_kartu_keluarga AS VARCHAR) AS "No. KK",
                    nama AS "Nama Lengkap",
                    desil_nasional AS "Desil Kesejahteraan",
                    provinsi || ' / ' || kabupaten_kota || ' / ' || kecamatan AS "Wilayah (Prov/Kab/Kec)",
                    CAST(quality_issues AS VARCHAR) AS "Rincian Temuan Error / Corrupt",
                    'Perbaiki NIK/KK agar 16 digit & lengkapi data subjek' AS "Rekomendasi Tindakan Perbaikan"
                FROM individus
                WHERE quality_status != 'Valid'
                ORDER BY id ASC
            ) TO '{file_path_escaped}' (HEADER);
            """
            con.execute(sql)
            total_exported = con.execute("SELECT COUNT(*) FROM individus WHERE quality_status != 'Valid';").fetchone()[0]
        except Exception as e:
            print(f"Error during export audit report: {e}", flush=True)
            con.close()
            sys.exit(1)

        con.close()
        elapsed = time.time() - start_time
        csv_size = os.path.getsize(file_path)
        csv_size_mb = f"{csv_size / 1024 / 1024:.2f} MB"
        print(f"[EXPORT_SUCCESS] {file_name} | {total_exported:,} baris | {csv_size_mb} | {elapsed:.2f} detik", flush=True)
        return

    # MODE 2 & 3: BUNDLE MULTI-FILE (.ZIP = CSV + METADATA.TXT)
    where_clauses = []
    
    search = filters.get('search', '').strip()
    if search:
        search_escaped = search.lower().replace("'", "''")
        where_clauses.append(f"(LOWER(CAST(nama AS VARCHAR)) LIKE '%{search_escaped}%' OR LOWER(CAST(nomor_induk_kependudukan AS VARCHAR)) LIKE '%{search_escaped}%' OR LOWER(CAST(nomor_kartu_keluarga AS VARCHAR)) LIKE '%{search_escaped}%')")

    quality_status = filters.get('quality_status', 'semua')
    if quality_status and quality_status != 'semua':
        qs_escaped = quality_status.replace("'", "''")
        where_clauses.append(f"quality_status = '{qs_escaped}'")

    if mode == 'cleaned_only':
        where_clauses.append("quality_status = 'Valid'")

    where_str = (" WHERE " + " AND ".join(where_clauses)) if where_clauses else ""

    file1_name = f"data_dtsen_{mode}_{timestamp}.csv"
    file1_path = os.path.join(export_dir, file1_name)
    file1_path_escaped = file1_path.replace('\\', '/')

    print(f"[*] Memulai High-Speed DuckDB Native Exporter ke: {file1_path}", flush=True)

    try:
        con.execute(f"COPY (SELECT * FROM individus{where_str}) TO '{file1_path_escaped}' (HEADER);")
        
        total_uploaded = con.execute(f"SELECT COUNT(*) FROM individus;").fetchone()[0]
        total_exported = con.execute(f"SELECT COUNT(*) FROM individus{where_str};").fetchone()[0]
        total_valid = con.execute(f"SELECT COUNT(*) FROM individus WHERE quality_status = 'Valid';").fetchone()[0]
        critical_count = con.execute(f"SELECT COUNT(*) FROM individus WHERE quality_status = 'Critical';").fetchone()[0]
        warning_count = max(0, total_uploaded - total_valid - critical_count)
        total_bermasalah = max(0, total_uploaded - total_valid)
    except Exception as e:
        print(f"Error during native export query: {e}", flush=True)
        con.close()
        sys.exit(1)

    # Generate metadata.txt audit log
    file_txt_name = "metadata.txt"
    file_txt_path = os.path.join(export_dir, file_txt_name)

    valid_rate = f"{(total_valid / max(1, total_uploaded)) * 100:.2f}"
    error_rate = f"{(total_bermasalah / max(1, total_uploaded)) * 100:.2f}"

    with open(file_txt_path, 'w', encoding='utf-8') as ft:
        ft.write("================================================================================\n")
        ft.write("           METADATA AUDIT & LOG RECORD PEMBERSIHAN DATA DTSEN 2026             \n")
        ft.write("================================================================================\n")
        ft.write(f"Waktu Ekspor           : {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
        ft.write(f"Mode Ekspor Terpilih   : {'ERROR DIBERSIHKAN (CLEANED ONLY)' if mode == 'cleaned_only' else 'AS-IS (SEMUA DATA DENGAN NOTICE)'}\n")
        ft.write("Format File Hasil      : ZIP Paket Berkas (CSV + Metadata.txt)\n\n")

        ft.write("--------------------------------------------------------------------------------\n")
        ft.write("         RINGKASAN METRIK EVALUASI KUALITAS DATASET (QUALITY STATS)             \n")
        ft.write("--------------------------------------------------------------------------------\n")
        ft.write(f"Total Baris Diolah           : {total_uploaded:,} Baris Data\n")
        ft.write(f"Total Baris Diekspor         : {total_exported:,} Baris Data\n")
        ft.write(f"Total Baris Valid            : {total_valid:,} Baris ({valid_rate}% Lolos Validasi)\n")
        ft.write(f"Total Baris Bermasalah       : {total_bermasalah:,} Baris ({error_rate}% Cacat/Error)\n")
        ft.write(f"  - Baris Status Critical    : {critical_count:,} Baris\n")
        ft.write(f"  - Baris Status Warning     : {warning_count:,} Baris\n")
        ft.write("--------------------------------------------------------------------------------\n\n")

    # Create ZIP Archive
    zip_name = f"paket_ekspor_dtsen_{mode}_{timestamp}.zip"
    zip_path = os.path.join(export_dir, zip_name)

    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED, compresslevel=1) as zipf:
        zipf.write(file1_path, arcname=file1_name)
        zipf.write(file_txt_path, arcname=file_txt_name)

    # Clean up temporary CSV/TXT files without deleting dataset.duckdb!
    try:
        os.remove(file1_path)
        os.remove(file_txt_path)
    except Exception:
        pass

    con.close()

    elapsed = time.time() - start_time
    zip_size = os.path.getsize(zip_path)
    zip_size_mb = f"{zip_size / 1024 / 1024:.2f} MB"

    print(f"[EXPORT_SUCCESS] {zip_name} | {total_exported:,} baris | {zip_size_mb} | {elapsed:.2f} detik", flush=True)

if __name__ == '__main__':
    db_f = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    exp_d = sys.argv[2] if len(sys.argv) > 2 else 'src-export'
    m_str = sys.argv[3] if len(sys.argv) > 3 else 'as_is'
    p_str = sys.argv[4] if len(sys.argv) > 4 else '{}'
    run_fast_export(db_f, exp_d, m_str, p_str)
