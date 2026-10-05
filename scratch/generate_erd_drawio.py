import xml.etree.ElementTree as ET
import os

def create_erd_drawio():
    mxfile = ET.Element("mxfile", host="app.diagrams.net", agent="Antigravity", version="21.0.0", type="device")

    # =========================================================================
    # DIAGRAM 1: ERD LOGIKAL & RELASIONAL (DTSEN 2026)
    # =========================================================================
    diag1 = ET.SubElement(mxfile, "diagram", id="erd_logikal_dtsen", name="ERD Logikal (DTSEN 2026)")
    model1 = ET.SubElement(diag1, "mxGraphModel", dx="2200", dy="1400", grid="1", gridSize="10", guides="1", tooltips="1", connect="1", arrows="1", fold="1", page="1", pageScale="1", pageWidth="2400", pageHeight="1600", math="0", shadow="0")
    root1 = ET.SubElement(model1, "root")
    ET.SubElement(root1, "mxCell", id="0")
    ET.SubElement(root1, "mxCell", id="1", parent="0")

    # Header Title
    title_cell = ET.SubElement(root1, "mxCell", id="title_erd_1", 
        value="""<div style="font-size:22px;font-weight:bold;color:#0f172a;letter-spacing:-0.5px;">ENTITY RELATIONSHIP DIAGRAM (ERD) — PADU v1.02</div>
<div style="font-size:13px;color:#475569;margin-top:5px;">Sistem Pengolah &amp; Analisis Data Terpadu (DTSEN 2026 Engine) — Skema Relasi Database Relasional (OLTP)</div>""",
        style="html=1;text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;",
        vertex="1", parent="1")
    geo = ET.SubElement(title_cell, "mxGeometry", x="600", y="30", width="1200", height="60")
    geo.set("as", "geometry")

    # Legend / Info Box
    legend_cell = ET.SubElement(root1, "mxCell", id="legend_box",
        value="""<div style="font-size:12px;font-weight:bold;color:#1e293b;border-bottom:1px solid #cbd5e1;padding-bottom:4px;margin-bottom:6px;">KETERANGAN NOTASI &amp; SIMBOL ERD</div>
<div style="font-size:11px;line-height:1.6;color:#334155;text-align:left;">
<span style="background-color:#fee2e2;color:#991b1b;font-weight:bold;padding:1px 5px;border-radius:3px;">PK</span> = Primary Key (Kunci Utama)<br>
<span style="background-color:#fef3c7;color:#92400e;font-weight:bold;padding:1px 5px;border-radius:3px;">FK</span> = Foreign Key (Kunci Tamu)<br>
<span style="background-color:#e0e7ff;color:#3730a3;font-weight:bold;padding:1px 5px;border-radius:3px;">UK</span> = Unique Key (Indeks Unik)<br>
<span style="background-color:#dcfce7;color:#166534;font-weight:bold;padding:1px 5px;border-radius:3px;">IDX</span> = Indexed Column (Optimasi Query)<br>
<b>Relasi</b>: 1 : N (One-to-Many) dengan <i>ON DELETE CASCADE</i>
</div>""",
        style="html=1;rounded=1;arcSize=10;fillColor=#f8fafc;strokeColor=#94a3b8;strokeWidth=1.5;shadow=1;align=center;verticalAlign=top;spacingTop=8;spacingLeft=10;spacingRight=10;",
        vertex="1", parent="1")
    geo = ET.SubElement(legend_cell, "mxGeometry", x="60", y="110", width="280", height="160")
    geo.set("as", "geometry")

    # Table: USERS
    users_val = """<div style="background-color:#3b82f6;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  users
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.7;color:#1e293b;background-color:#ffffff;">
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[PK]</b> id : <i>BIGINT AUTO_INCREMENT</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">name : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[UK]</b> email : <i>VARCHAR(255) UNIQUE</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">email_verified_at : <i>TIMESTAMP NULL</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">password : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">remember_token : <i>VARCHAR(100) NULL</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">created_at : <i>TIMESTAMP</i></div>
  <div style="padding:2px 0;">updated_at : <i>TIMESTAMP</i></div>
</div>"""
    u_cell = ET.SubElement(root1, "mxCell", id="tbl_users", value=users_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#2563eb;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(u_cell, "mxGeometry", x="60", y="300", width="280", height="240")
    geo.set("as", "geometry")

    # Table: DEMOGRAPHICS
    demographics_val = """<div style="background-color:#0284c7;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  demographics (Stand-alone Analytics)
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[PK]</b> id : <i>BIGINT AUTO_INCREMENT</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[UK]</b> nik : <i>VARCHAR(16) UNIQUE</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">nama_lengkap : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">jenis_kelamin : <i>ENUM('L','P')</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">pendidikan_terakhir : <i>ENUM(...)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">tanggal_lahir : <i>DATE</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">gaji_bulanan : <i>DECIMAL(12,2)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">kota_domisili : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">email : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">status_pernikahan : <i>ENUM(...)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">status_kehidupan : <i>VARCHAR(50)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">created_at / updated_at : <i>TIMESTAMP</i></div>
  <div style="padding:2px 0;color:#e11d48;">deleted_at : <i>TIMESTAMP NULL (Soft Delete)</i></div>
</div>"""
    d_cell = ET.SubElement(root1, "mxCell", id="tbl_demographics", value=demographics_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0284c7;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(d_cell, "mxGeometry", x="60", y="570", width="280", height="340")
    geo.set("as", "geometry")

    # Table: KELUARGAS (52 Variabel DTSEN)
    keluargas_val = """<div style="background-color:#047857;color:#ffffff;font-size:15px;font-weight:bold;padding:10px;border-radius:6px 6px 0 0;text-align:center;">
  keluargas (Set Data Rumah Tangga - 52 Variabel DTSEN)
</div>
<div style="padding:10px 12px;text-align:left;font-size:11px;line-height:1.55;color:#1e293b;background-color:#ffffff;">
  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:3px 0;border-radius:3px;">IDENTITAS &amp; KUNCI UTAMA</div>
  <div><b>[PK]</b> id : <i>BIGINT AUTO_INCREMENT</i></div>
  <div><b>[UK, IDX]</b> nomor_kartu_keluarga : <i>VARCHAR(16) UNIQUE</i></div>
  <div>nama_kepala_keluarga : <i>VARCHAR(500)</i></div>
  <div>jumlah_anggota_keluarga : <i>INTEGER DEFAULT 1</i></div>
  <div>alamat : <i>VARCHAR(500)</i></div>
  
  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">WILAYAH ADMINISTRASI</div>
  <div>kode_provinsi : <i>VARCHAR(2)</i> | provinsi : <i>VARCHAR(100)</i></div>
  <div>kode_kabupaten_kota : <i>VARCHAR(4)</i> | kabupaten_kota : <i>VARCHAR(100)</i></div>
  <div>kode_kecamatan : <i>VARCHAR(7)</i> | kecamatan : <i>VARCHAR(100)</i></div>
  <div>kode_kelurahan_desa : <i>VARCHAR(10)</i> | kelurahan_desa : <i>VARCHAR(100)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">DESIL KESEJAHTERAAN &amp; BANTUAN</div>
  <div><b>[IDX]</b> desil_nasional : <i>INTEGER (1 - 10)</i></div>
  <div>desil_provinsi : <i>INTEGER</i> | desil_kabupaten_kota : <i>INTEGER</i></div>
  <div>pbi_nas : <i>VARCHAR(2)</i> | pbi_pemda : <i>VARCHAR(2)</i></div>
  <div>id_pelanggan_pln : <i>VARCHAR(255)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">PERUMAHAN &amp; SANITASI</div>
  <div>status_kepemilikan_rumah : <i>VARCHAR(2)</i></div>
  <div>jenis_lantai_terluas : <i>VARCHAR(2)</i> | luas_lantai : <i>INTEGER</i></div>
  <div>jenis_dinding_terluas : <i>VARCHAR(2)</i> | jenis_atap_terluas : <i>VARCHAR(2)</i></div>
  <div>sumber_air_minum_utama : <i>VARCHAR(2)</i></div>
  <div>sumber_penerangan_utama : <i>VARCHAR(2)</i> | daya_terpasang : <i>VARCHAR(2)</i></div>
  <div>bahan_bakar_utama_memasak : <i>VARCHAR(2)</i></div>
  <div>fasilitas_bab : <i>VARCHAR(2)</i> | jenis_kloset : <i>VARCHAR(2)</i></div>
  <div>pembuangan_akhir_tinja : <i>VARCHAR(2)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">KEPEMILIKAN ASET (1=Ya, 2=Tidak)</div>
  <div>kepemilikan_aset : <i>INT</i> | aset_bergerak_tabung_gas : <i>INT</i></div>
  <div>aset_bergerak_lemari_es : <i>INT</i> | aset_bergerak_ac : <i>INT</i></div>
  <div>aset_bergerak_tv_datar : <i>INT</i> | aset_bergerak_pemanas_air : <i>INT</i></div>
  <div>aset_bergerak_sepeda_motor : <i>INT</i> | aset_bergerak_mobil : <i>INT</i></div>
  <div>aset_bergerak_komputer_laptop_tablet : <i>INT</i> | aset_bergerak_smartphone : <i>INT</i></div>
  <div>aset_bergerak_emas_perhiasan : <i>INT</i> | aset_bergerak_perahu / motor : <i>INT</i></div>
  <div>aset_tidak_bergerak_lahan_lainnya / rumah_lainnya : <i>INT</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">KEPEMILIKAN TERNAK &amp; AUDIT TIMESTAMPS</div>
  <div>ternak_sapi, kerbau, kuda, babi, kambing_domba : <i>INTEGER DEFAULT 0</i></div>
  <div>created_at : <i>TIMESTAMP</i> | updated_at : <i>TIMESTAMP</i></div>
</div>"""
    k_cell = ET.SubElement(root1, "mxCell", id="tbl_keluargas", value=keluargas_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=4;fillColor=#ffffff;strokeColor=#059669;strokeWidth=2.5;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(k_cell, "mxGeometry", x="400", y="110", width="460", height="900")
    geo.set("as", "geometry")

    # Table: INDIVIDUS (48 Variabel DTSEN)
    individus_val = """<div style="background-color:#4338ca;color:#ffffff;font-size:15px;font-weight:bold;padding:10px;border-radius:6px 6px 0 0;text-align:center;">
  individus (Set Data Anggota Keluarga - 48 Variabel DTSEN)
</div>
<div style="padding:10px 12px;text-align:left;font-size:11px;line-height:1.55;color:#1e293b;background-color:#ffffff;">
  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:3px 0;border-radius:3px;">IDENTITAS POKOK &amp; KUNCI RELASI</div>
  <div><b>[PK]</b> id : <i>BIGINT AUTO_INCREMENT</i></div>
  <div><b>[UK, IDX]</b> nomor_induk_kependudukan : <i>VARCHAR(16) UNIQUE (NIK)</i></div>
  <div style="color:#b45309;font-weight:bold;"><b>[FK, IDX]</b> nomor_kartu_keluarga : <i>VARCHAR(16) -> keluargas.nomor_kartu_keluarga</i></div>
  <div>nama : <i>VARCHAR(500)</i> (Dukung Masking PII di Tampilan)</div>
  <div>tanggal_lahir : <i>DATE</i> | usia : <i>INTEGER</i> | jenis_kelamin : <i>VARCHAR(20)</i></div>
  <div>status_hubungan_keluarga : <i>VARCHAR(50)</i> | status_kawin : <i>VARCHAR(50)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">PENDIDIKAN &amp; KETENAGAKERJAAN</div>
  <div>partisipasi_sekolah : <i>VARCHAR(50)</i> | jenjang_tertinggi_yang_diduduki : <i>VARCHAR(50)</i></div>
  <div>kelas_tertinggi_yang_diduduki : <i>VARCHAR(10)</i> | ijazah_tertinggi : <i>VARCHAR(50)</i></div>
  <div>status_bekerja : <i>VARCHAR(20)</i> | lapangan_usaha_pekerjaan_utama : <i>VARCHAR(100)</i></div>
  <div>status_dalam_pekerjaan_utama : <i>VARCHAR(100)</i> | kepemilikan_usaha : <i>VARCHAR(20)</i></div>
  <div>jumlah_usaha : <i>INT</i> | lapangan_usaha_utama : <i>VARCHAR(100)</i></div>
  <div>pekerja_dibayar_usaha / pekerja_tidak_dibayar : <i>INTEGER</i></div>
  <div>omzet_usaha_utama : <i>VARCHAR(50)</i></div>
  <div>gaji_bulanan : <i>DECIMAL(15,2)</i> | gaji : <i>DECIMAL(15,2)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">KESEHATAN, BANSOS &amp; FUNGSIONALITAS DISABILITAS</div>
  <div>pbi_nas : <i>VARCHAR(2)</i> | pbi_pemda : <i>VARCHAR(2)</i></div>
  <div>kondisi_gizi : <i>VARCHAR(50)</i> | penyakit_kronis : <i>VARCHAR(100)</i></div>
  <div>penglihatan, pendengaran, berjalan_atau_naik_tangga : <i>VARCHAR(50)</i></div>
  <div>menggunakan_tangan_jari, belajar_intelektual, pengendalian_perilaku : <i>VARCHAR(50)</i></div>
  <div>berbicara_komunikasi, mengurus_diri, mengingat_berkonsentrasi : <i>VARCHAR(50)</i></div>
  <div>kesedihan_depresi : <i>VARCHAR(50)</i></div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">ALAMAT KTP LENGKAP</div>
  <div>kode_provinsi_ktp, provinsi_ktp | kode_kabupaten_kota_ktp, kabupaten_kota_ktp</div>
  <div>kode_kecamatan_ktp, kecamatan_ktp | kode_kelurahan_desa_ktp, kelurahan_desa_ktp</div>
  <div>rt_ktp : <i>VARCHAR(10)</i> | rw_ktp : <i>VARCHAR(10)</i> | dusun_ktp : <i>VARCHAR(100)</i></div>
  <div>alamat_ktp : <i>VARCHAR(500)</i> | pekerjaan_ktp / pendidikan_akhir_ktp</div>

  <div style="background-color:#f1f5f9;font-weight:bold;color:#475569;padding:3px 6px;margin:6px 0 3px 0;border-radius:3px;">QUALITY AUDIT ENGINE &amp; METADATA</div>
  <div style="color:#b91c1c;font-weight:bold;"><b>[IDX]</b> quality_status : <i>ENUM('Valid', 'Warning', 'Critical') DEFAULT 'Valid'</i></div>
  <div>quality_issues : <i>JSON (Log Detail Rule Gagal)</i></div>
  <div>extra_attributes : <i>JSON (Atribut Tambahan Fleksibel)</i></div>
  <div>created_at : <i>TIMESTAMP</i> | updated_at : <i>TIMESTAMP</i></div>
</div>"""
    i_cell = ET.SubElement(root1, "mxCell", id="tbl_individus", value=individus_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=4;fillColor=#ffffff;strokeColor=#4338ca;strokeWidth=2.5;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(i_cell, "mxGeometry", x="960", y="110", width="520", height="900")
    geo.set("as", "geometry")

    # Connector: keluargas (1) -> individus (N)
    rel_edge = ET.SubElement(root1, "mxCell", id="rel_keluarga_individu",
        value="""<div style="background-color:#ffffff;border:1px solid #4338ca;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:bold;color:#1e1b4b;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
  1 : N (One-to-Many)<br>
  <span style="font-size:10px;font-weight:normal;color:#6b21a8;">FK: nomor_kartu_keluarga</span><br>
  <span style="font-size:9px;color:#dc2626;">ON DELETE CASCADE</span>
</div>""",
        style="html=1;edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;strokeColor=#4338ca;strokeWidth=3;startArrow=ERone;startFill=0;endArrow=ERmany;endFill=0;labelBackgroundColor=none;",
        edge="1", parent="1", source="tbl_keluargas", target="tbl_individus")
    geo = ET.SubElement(rel_edge, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # Relationship Note Box
    rel_note = ET.SubElement(root1, "mxCell", id="rel_desc_box",
        value="""<div style="font-size:12px;font-weight:bold;color:#0f766e;margin-bottom:4px;">INTEGRITAS REFERENSIAL &amp; KARDINALITAS</div>
<div style="font-size:11px;line-height:1.5;color:#134e4a;text-align:left;">
• Setiap 1 Kartu Keluarga (<b>keluargas</b>) menjadi induk dari 1 hingga N Anggota Keluarga (<b>individus</b>).<br>
• Relasi diikat via <code>nomor_kartu_keluarga</code> (16 digit angka unik di tabel <i>keluargas</i>).<br>
• Jika berkas/kartu keluarga dihapus, seluruh anggota keluarga terkait dihapus otomatis (<i>Cascade</i>).<br>
• Model Eloquent: <code>Keluarga->hasMany(Individu)</code> &amp; <code>Individu->belongsTo(Keluarga)</code>.
</div>""",
        style="html=1;rounded=1;arcSize=10;fillColor=#f0fdfa;strokeColor=#0d9488;strokeWidth=1.5;shadow=1;align=center;verticalAlign=top;spacingTop=6;spacingLeft=10;spacingRight=10;",
        vertex="1", parent="1")
    geo = ET.SubElement(rel_note, "mxGeometry", x="400", y="1030", width="1080", height="90")
    geo.set("as", "geometry")


    # =========================================================================
    # DIAGRAM 2: ARSITEKTUR DUAL-STORAGE (OLTP RELASIONAL + OLAP DUCKDB VECTOR)
    # =========================================================================
    diag2 = ET.SubElement(mxfile, "diagram", id="erd_fisikal_dual_storage", name="Arsitektur Dual-Storage (OLTP + OLAP)")
    model2 = ET.SubElement(diag2, "mxGraphModel", dx="2000", dy="1200", grid="1", gridSize="10", guides="1", tooltips="1", connect="1", arrows="1", fold="1", page="1", pageScale="1", pageWidth="2000", pageHeight="1200", math="0", shadow="0")
    root2 = ET.SubElement(model2, "root")
    ET.SubElement(root2, "mxCell", id="0")
    ET.SubElement(root2, "mxCell", id="1", parent="0")

    # Header Title
    title_cell2 = ET.SubElement(root2, "mxCell", id="title_erd_2",
        value="""<div style="font-size:20px;font-weight:bold;color:#0f172a;">ARSITEKTUR PENYIMPANAN DATA DUAL-ENGINE (PADU v1.02)</div>
<div style="font-size:13px;color:#475569;margin-top:4px;">Kolaborasi Database Relasional Transaksional (SQLite / MySQL) dan Engine Analitik Tervektorisasi (DuckDB)</div>""",
        style="html=1;text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;",
        vertex="1", parent="1")
    geo = ET.SubElement(title_cell2, "mxGeometry", x="400", y="30", width="1200", height="50")
    geo.set("as", "geometry")

    # Ingestion Box (Source Files)
    src_val = """<div style="background-color:#ea580c;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  DATASET SUMBER (INPUT)
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Berkas Masukan:</b><br>
  • CSV / XLSX Dataset DTSEN<br>
  • Skala: 500 s.d 13+ Juta Baris<br>
  • Lokasi: <code>src-dtsen/</code><br>
  • Kecepatan Baca: &gt; 1.5M baris/detik
</div>"""
    src_cell = ET.SubElement(root2, "mxCell", id="src_box", value=src_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#ea580c;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(src_cell, "mxGeometry", x="60", y="260", width="240", height="160")
    geo.set("as", "geometry")

    # Engine Ingestion / Dispatcher
    disp_val = """<div style="background-color:#4f46e5;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  INGESTION &amp; AUDIT PIPELINE
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Komponen Pemroses:</b><br>
  • <code>fast_import.py</code> (Vectorized Loader)<br>
  • Quality Rules Evaluation (Critical/Warning/Valid)<br>
  • Synonym Header Mapper (48/52 Variabel)<br>
  • PII Masking Transformer
</div>"""
    disp_cell = ET.SubElement(root2, "mxCell", id="disp_box", value=disp_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#4f46e5;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(disp_cell, "mxGeometry", x="380", y="260", width="280", height="160")
    geo.set("as", "geometry")

    # Storage 1: Relational SQLite / MySQL
    oltp_val = """<div style="background-color:#0284c7;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  OLTP RELASIONAL (SQLite / MySQL)
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Tabel Relasional:</b><br>
  1. <code>keluargas</code> (52 Variabel DTSEN)<br>
  2. <code>individus</code> (48 Variabel DTSEN)<br>
  3. <code>users</code> (Autentikasi)<br>
  4. <code>demographics</code> (Legacy)<br>
  <br>
  <b>Tujuan:</b><br>
  • Integritas referensial (Foreign Keys &amp; Cascades)<br>
  • CRUD transaksional &amp; Session Store<br>
  • Laravel Eloquent ORM Model Binding
</div>"""
    oltp_cell = ET.SubElement(root2, "mxCell", id="oltp_box", value=oltp_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0284c7;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(oltp_cell, "mxGeometry", x="780", y="140", width="340", height="220")
    geo.set("as", "geometry")

    # Storage 2: DuckDB Vectorized Analytics
    olap_val = """<div style="background-color:#059669;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  OLAP ENGINE (dataset.duckdb)
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Tabel Teroptimasi:</b><br>
  1. <code>individus</code> (Flattened Denormalized Columns)<br>
  2. Index Kolom: NIK, KK, Desil, Gaji, Usia, Wilayah<br>
  <br>
  <b>Tujuan:</b><br>
  • Eksekusi Vektor SIMD 13+ Juta Baris Data<br>
  • Agregasi Statistik Real-Time (&lt; 0.05 detik)<br>
  • Multi-Column Filtering &amp; Pencarian Instan<br>
  • Ekspor Cepat ZIP/CSV via <code>fast_export.py</code>
</div>"""
    olap_cell = ET.SubElement(root2, "mxCell", id="olap_box", value=olap_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#059669;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(olap_cell, "mxGeometry", x="780", y="400", width="340", height="220")
    geo.set("as", "geometry")

    # Web Dashboard / UI Consumer
    ui_val = """<div style="background-color:#0f172a;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  LARAVEL WEB UI / DASHBOARD
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Fitur Antarmuka:</b><br>
  • Dashboard Metrik &amp; Visualisasi Desil<br>
  • Dynamic Multi-Filter Checkbox<br>
  • Grid Data dengan PII Masking Aktif<br>
  • Export Manager (ZIP Package &amp; CSV Log)
</div>"""
    ui_cell = ET.SubElement(root2, "mxCell", id="ui_box", value=ui_val,
        style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor:#0f172a;strokeWidth=2;shadow=1;verticalAlign=top;",
        vertex="1", parent="1")
    geo = ET.SubElement(ui_cell, "mxGeometry", x="1240", y="270", width="300", height="180")
    geo.set("as", "geometry")

    # Edges between components in Diagram 2
    # 1. Source to Ingestion
    e1 = ET.SubElement(root2, "mxCell", id="e_src_disp", value="Baca File CSV/XLSX",
        style="html=1;strokeColor=#ea580c;strokeWidth=2;fontColor=#c2410c;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;",
        edge="1", parent="1", source="src_box", target="disp_box")
    geo = ET.SubElement(e1, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # 2. Ingestion to OLTP
    e2 = ET.SubElement(root2, "mxCell", id="e_disp_oltp", value="Relational Insert / Migration",
        style="html=1;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;",
        edge="1", parent="1", source="disp_box", target="oltp_box")
    geo = ET.SubElement(e2, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # 3. Ingestion to OLAP
    e3 = ET.SubElement(root2, "mxCell", id="e_disp_olap", value="Vectorized Ingestion (500k/s)",
        style="html=1;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;",
        edge="1", parent="1", source="disp_box", target="olap_box")
    geo = ET.SubElement(e3, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # 4. OLTP to UI
    e4 = ET.SubElement(root2, "mxCell", id="e_oltp_ui", value="Auth & Relasional Detail",
        style="html=1;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;",
        edge="1", parent="1", source="oltp_box", target="ui_box")
    geo = ET.SubElement(e4, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # 5. OLAP to UI
    e5 = ET.SubElement(root2, "mxCell", id="e_olap_ui", value="Instant Analytics Query (<0.05s)",
        style="html=1;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;",
        edge="1", parent="1", source="olap_box", target="ui_box")
    geo = ET.SubElement(e5, "mxGeometry", relative="1")
    geo.set("as", "geometry")

    # Write to PADU_ERD.drawio
    tree = ET.ElementTree(mxfile)
    ET.indent(tree, space="  ", level=0)
    out_path = "PADU_ERD.drawio"
    tree.write(out_path, encoding="utf-8", xml_declaration=True)
    print(f"ERD drawio successfully generated at {out_path}")

if __name__ == "__main__":
    create_erd_drawio()
