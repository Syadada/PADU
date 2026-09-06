import sys
import os
import csv
import json
import sqlite3
import time
import zipfile
from datetime import datetime

def run_fast_export(db_path, export_dir, mode="as_is", params_json="{}"):
    if not os.path.exists(db_path):
        print(f"Error: Database '{db_path}' tidak ditemukan.", flush=True)
        sys.exit(1)

    if not os.path.exists(export_dir):
        os.makedirs(export_dir, exist_ok=True)

    start_time = time.time()
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

    conn = sqlite3.connect(db_path, timeout=60.0)
    cursor = conn.cursor()

    try:
        cursor.execute("PRAGMA journal_mode = WAL;")
        cursor.execute("PRAGMA synchronous = OFF;")
        cursor.execute("PRAGMA temp_store = MEMORY;")
        cursor.execute("PRAGMA cache_size = -4000000;")
        cursor.execute("PRAGMA mmap_size = 30000000000;")
        cursor.execute("PRAGMA busy_timeout = 60000;")
    except Exception:
        pass

    # Parse search/filter parameters if any
    try:
        filters = json.loads(params_json) if isinstance(params_json, str) else params_json
    except:
        filters = {}

    where_clauses = []
    where_params = []

    search = filters.get('search', '').strip()
    if search:
        like_term = f"%{search.lower()}%"
        where_clauses.append("(LOWER(i.nama) LIKE ? OR LOWER(i.nomor_induk_kependudukan) LIKE ? OR LOWER(i.nomor_kartu_keluarga) LIKE ?)")
        where_params.extend([like_term, like_term, like_term])

    quality_status = filters.get('quality_status', 'semua')
    if quality_status and quality_status != 'semua':
        where_clauses.append("i.quality_status = ?")
        where_params.append(quality_status)

    where_str = (" WHERE " + " AND ".join(where_clauses)) if where_clauses else ""

    # Build SQL Query joining individu with keluarga
    sql = f"""
        SELECT 
            i.id, i.quality_status, i.nomor_induk_kependudukan, i.nomor_kartu_keluarga,
            i.nama, i.tanggal_lahir, i.jenis_kelamin, i.status_kawin, i.status_bekerja,
            i.gaji_bulanan, i.quality_issues, i.alamat_ktp,
            k.desil_nasional, k.jenis_lantai_terluas, k.jenis_atap_terluas, k.provinsi, k.kabupaten_kota
        FROM individus i
        LEFT JOIN keluargas k ON i.nomor_kartu_keluarga = k.nomor_kartu_keluarga
        {where_str}
        ORDER BY i.id ASC
    """

    file1_name = f"data_dtsen_{mode}_{timestamp}.csv"
    file1_path = os.path.join(export_dir, file1_name)

    headers_list = [
        'Original_Row_Index',
        'Status Validitas',
        'NIK (Nomor Induk Kependudukan)',
        'Nomor Kartu Keluarga (KK)',
        'Nama Lengkap',
        'Tanggal Lahir',
        'Jenis Kelamin',
        'Status Kawin',
        'Status Bekerja',
        'Gaji Bulanan (Rp)',
        'Desil Nasional',
        'Jenis Lantai Terluas',
        'Jenis Atap Terluas',
        'Provinsi',
        'Kabupaten/Kota',
        'Alamat KTP',
        'Notice / Catatan Variabel Bermasalah'
    ]

    total_uploaded = 0
    total_exported = 0
    total_bermasalah = 0
    critical_count = 0
    warning_count = 0
    single_error_count = 0
    multi_error_count = 0
    triple_error_count = 0
    eliminated_samples = []

    print(f"[*] Memulai High-Speed Python Exporter ke: {file1_path}", flush=True)

    with open(file1_path, 'w', encoding='utf-8-sig', newline='', buffering=16*1024*1024) as f1:
        writer = csv.writer(f1)
        writer.writerow(headers_list)

        cursor.execute(sql, where_params)

        while True:
            rows = cursor.fetchmany(50000)
            if not rows:
                break

            for r in rows:
                total_uploaded += 1
                r_id = r[0]
                status = r[1] or 'Valid'
                nik_val = r[2]
                kk_val = r[3]
                nama_val = r[4]
                tgl_val = r[5]
                jk_val = r[6]
                st_kawin_val = r[7]
                st_kerja_val = r[8]
                gaji_val = r[9]
                raw_issues = r[10] or ""
                alamat_val = r[11]
                desil_val = r[12]
                lantai_val = r[13]
                atap_val = r[14]
                prov_val = r[15]
                kab_val = r[16]

                is_valid = (status == 'Valid')

                issues_list = []
                if raw_issues:
                    try:
                        parsed = json.loads(raw_issues)
                        if isinstance(parsed, list):
                            issues_list = [str(x) for x in parsed]
                        elif isinstance(parsed, str):
                            issues_list = [parsed]
                    except:
                        issues_list = [raw_issues]

                err_len = len(issues_list)

                if is_valid and err_len == 0:
                    total_exported += 1
                else:
                    total_bermasalah += 1
                    if status == 'Critical':
                        critical_count += 1
                    else:
                        warning_count += 1

                    if err_len == 1:
                        single_error_count += 1
                    elif err_len == 2:
                        multi_error_count += 1
                    elif err_len >= 3:
                        triple_error_count += 1
                        multi_error_count += 1

                    if len(eliminated_samples) < 100:
                        eliminated_samples.append({
                            'id': r_id,
                            'status': status,
                            'nik': nik_val,
                            'nama': nama_val,
                            'desil': desil_val or '-',
                            'prov': prov_val or '-',
                            'kab': kab_val or '-',
                            'issues_list': issues_list
                        })

                if mode == 'cleaned_only' and not is_valid:
                    continue

                if err_len >= 3:
                    notice_text = f"🔴 [{err_len} EROR TERDETEKSI]: " + " | ".join([f"{idx+1}. {iss}" for idx, iss in enumerate(issues_list)])
                elif err_len == 2:
                    notice_text = f"🔴 [2 EROR TERDETEKSI]: 1. {issues_list[0]} | 2. {issues_list[1]}"
                elif err_len == 1:
                    notice_text = f"⚠️ [1 EROR TERDETEKSI]: {issues_list[0]}"
                else:
                    notice_text = "🟢 [VALID]: Data Valid / Tidak Ada Error"
                
                gaji_str = f"Rp {gaji_val:,.0f}".replace(',', '.') if gaji_val and gaji_val > 0 else "Rp 0"

                writer.writerow([
                    f"Baris #{r_id}",
                    f"{status} ({err_len} Eror)" if err_len > 1 else status,
                    f"'{nik_val}",
                    f"'{kk_val}",
                    nama_val,
                    tgl_val or '-',
                    jk_val or '-',
                    st_kawin_val or '-',
                    st_kerja_val or '-',
                    gaji_str,
                    f"Desil {desil_val or '-'}",
                    lantai_val or '-',
                    atap_val or '-',
                    prov_val or '-',
                    kab_val or '-',
                    alamat_val or '-',
                    notice_text
                ])

    # Metadata.txt
    file_txt_name = "metadata.txt"
    file_txt_path = os.path.join(export_dir, file_txt_name)

    valid_rate = f"{(total_exported / max(1, total_uploaded)) * 100:.2f}"
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
        ft.write(f"Total Baris Valid            : {total_exported:,} Baris ({valid_rate}% Lolos Validasi)\n")
        ft.write(f"Total Baris Bermasalah       : {total_bermasalah:,} Baris ({error_rate}% Cacat/Error)\n")
        ft.write(f"  - Baris Status Critical    : {critical_count:,} Baris\n")
        ft.write(f"  - Baris Status Warning     : {warning_count:,} Baris\n")
        ft.write(f"  - Single Error (1 Error)   : {single_error_count:,} Baris\n")
        ft.write(f"  - Multi Error (>= 2 Error) : {multi_error_count:,} Baris\n")
        ft.write(f"  - Triple Error (>= 3 Error): {triple_error_count:,} Baris (Multi-Error Tingkat Tinggi)\n")
        ft.write("--------------------------------------------------------------------------------\n\n")

        ft.write("================================================================================\n")
        ft.write("    SAMPLING DATA BARIS & VARIABEL YANG DIHAPUS / BERMASALAH (ELIMINATION LOG)   \n")
        ft.write("================================================================================\n\n")

        if not eliminated_samples:
            ft.write("[INFO] Tidak ada data yang bermasalah. Seluruh 100% baris data berstatus VALID.\n")
        else:
            for idx, es in enumerate(eliminated_samples, 1):
                ft.write(f"[DATA BERMASALAH #{idx}]\n")
                ft.write(f"  Original Row Index   : Baris #{es['id']}\n")
                ft.write(f"  Status Validitas     : {es['status']} ({len(es['issues_list'])} Eror Terdeteksi)\n")
                ft.write(f"  NIK Subjek           : {es['nik']}\n")
                ft.write(f"  Nama Lengkap         : {es['nama']}\n")
                ft.write(f"  Rincian Temuan Error :\n")
                if es['issues_list']:
                    for sub_idx, sub_iss in enumerate(es['issues_list'], 1):
                        ft.write(f"    {sub_idx}. {sub_iss}\n")
                else:
                    ft.write("    - Tidak ada rincian catatan\n")
                ft.write("--------------------------------------------------------------------------------\n")

    # Create ZIP Archive using Python zipfile
    zip_name = f"paket_ekspor_dtsen_{mode}_{timestamp}.zip"
    zip_path = os.path.join(export_dir, zip_name)

    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        zipf.write(file1_path, arcname=file1_name)
        zipf.write(file_txt_path, arcname=file_txt_name)

    # Clean up temp CSV/TXT
    try:
        os.remove(file1_path)
        os.remove(file_txt_path)
    except:
        pass

    # Clear DB tables if requested
    cursor.execute("DELETE FROM individus;")
    cursor.execute("DELETE FROM keluargas;")
    conn.commit()

    conn.close()

    elapsed = time.time() - start_time
    zip_size = os.path.getsize(zip_path)
    zip_size_mb = f"{zip_size / 1024 / 1024:.2f} MB"

    print(f"[EXPORT_SUCCESS] {zip_name} | {total_uploaded:,} baris | {zip_size_mb} | {elapsed:.2f} detik", flush=True)

if __name__ == '__main__':
    db_f = sys.argv[1] if len(sys.argv) > 1 else 'database/database.sqlite'
    exp_d = sys.argv[2] if len(sys.argv) > 2 else 'src-export'
    m_str = sys.argv[3] if len(sys.argv) > 3 else 'as_is'
    p_str = sys.argv[4] if len(sys.argv) > 4 else '{}'
    run_fast_export(db_f, exp_d, m_str, p_str)
