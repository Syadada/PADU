import xml.sax.saxutils as saxutils
import os

def esc(s):
    return saxutils.escape(s, {'"': '&quot;'})

def make_vertex(cid, val, style, x, y, w, h, parent="1"):
    if "html=1" not in style:
        style = "html=1;" + style
    return f'''        <mxCell id="{cid}" value="{esc(val)}" style="{style}" vertex="1" parent="{parent}">
          <mxGeometry x="{x}" y="{y}" width="{w}" height="{h}" as="geometry" />
        </mxCell>'''

def make_edge(cid, val, src, tgt, style, points=None, parent="1"):
    if "html=1" not in style:
        style = "html=1;" + style
    pts_xml = ""
    if points:
        pts_items = "".join([f'<mxPoint x="{px}" y="{py}" />' for px, py in points])
        pts_xml = f'''\n            <Array as="points">{pts_items}</Array>'''

    return f'''        <mxCell id="{cid}" value="{esc(val)}" style="{style}" edge="1" parent="{parent}" source="{src}" target="{tgt}">
          <mxGeometry relative="1" as="geometry">{pts_xml}
          </mxGeometry>
        </mxCell>'''

PROC_FILL = "#66B2FF"
PROC_BORDER = "#007ACC"
ENT_FILL = "#66B2FF"
ENT_BORDER = "#007ACC"
TEXT_COLOR = "#000000"

def proc_style(border_col=PROC_BORDER, fill_col=PROC_FILL):
    return f"rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;"

def ent_style(border_col=ENT_BORDER, fill_col=ENT_FILL):
    return f"rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;"

def store_style(border_col, fill_col):
    return f"shape=partialRectangle;right=0;left=0;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"

blue_in = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
green_out = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
purple_flow = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;"
amber_cam = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=10;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
red_alert = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=10;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
indigo_sys = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=10;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"

def build_diagram(diag_id, diag_name, cells, w=2400, h=1400):
    body = "\n".join(cells)
    return f'''  <diagram id="{diag_id}" name="{esc(diag_name)}">
    <mxGraphModel dx="{w}" dy="{h}" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="{w}" pageHeight="{h}" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{body}
      </root>
    </mxGraphModel>
  </diagram>'''

# =========================================================================
# COMMON ENTITIES
# =========================================================================
e1_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Menyalin file CSV/XLSX ke src-dtsen/<br>
• Memilih file &amp; trigger impor lokal<br>
• Memantau progress bar impor<br>
• Mengatur filter NIK, Wilayah, Desil<br>
• Melihat tabel data mentah (as-is)<br>
• Mengunduh file CSV hasil filter/error
</div>'''

e1_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengunggah berkas mentah DTSEN<br>
• Memilih Modul: Data Mikro vs Statistik<br>
• Menjalankan filter dinamis multi-kolom<br>
• Meninjau data mikro terpadu (Transliterasi FK)<br>
• Menganalisis metrik agregasi wilayah (7 Rumus)<br>
• Mengunduh paket ZIP arsip &amp; log audit<br>
• Menerima notifikasi peringatan layar
</div>'''

e2_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Headless OpenCV Capture (DSHOW)<br>
• Frame visual berkala (loop 0.2s)<br>
• Snapshot verifikasi biometrik
</div>'''

e3_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''

# =========================================================================
# BUILD AS-IS DIAGRAMS (TABS 1 - 6) - IDENTICAL & FULLY PRESERVED
# =========================================================================
def get_asis_diagrams():
    diagrams = []
    
    # 1. AS-IS Level 0
    c = []
    c.append(make_vertex("t_asis_0", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — AS-IS (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis &amp; Pengolah Data DTSEN Eksisting (Tanpa Pengamanan Biometrik &amp; PII Masking)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 350, 40, 850, 50))
    p0_asis_val = '''<div style="font-size:14px;color:#000000;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#000000;line-height:1.3;">SISTEM PENGOLAH &amp; ANALISIS DATA<br>DTSEN 2026 (PADU v1.0 — AS-IS)</div>
<hr style="border:1px solid #007ACC;margin:12px 0;">
<div style="font-size:11px;color:#000000;line-height:1.5;">
• Dual-Engine: Laravel 12 + Python DuckDB<br>
• Impor Lokal CSV/XLSX ke Database<br>
• Filter Multi-Kolom &amp; Table Grid Standar<br>
• Ekspor Berkas CSV Polos
</div>'''
    c.append(make_vertex("P0_ASIS", p0_asis_val, f"rounded=1;arcSize=20;whiteSpace=wrap;fillColor={PROC_FILL};strokeColor={PROC_BORDER};strokeWidth=3;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;", 640, 220, 360, 440))
    c.append(make_vertex("E1_ASIS_0", e1_asis_val, ent_style(), 80, 220, 240, 440))
    c.append(make_edge("e_asis_0_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.09;entryX=0;entryY=0.09;"))
    c.append(make_edge("e_asis_0_2", "2. Pilihan File &amp; Perintah Impor Lokal", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.22;entryX=0;entryY=0.22;"))
    c.append(make_edge("e_asis_0_3", "3. Kriteria Filter Multi-Kolom &amp; Keyword", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.35;entryX=0;entryY=0.35;"))
    c.append(make_edge("e_asis_0_4", "4. Permintaan Ekspor Berkas CSV", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.48;entryX=0;entryY=0.48;"))
    c.append(make_edge("e_asis_0_5", "5. Status &amp; Progress Bar Ingesti Data", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.61;entryX=1;entryY=0.61;"))
    c.append(make_edge("e_asis_0_6", "6. Ringkasan Metrik KPI Kualitas (Count)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.74;entryX=1;entryY=0.74;"))
    c.append(make_edge("e_asis_0_7", "7. Tabel Data DTSEN Biasa (Tanpa PII Masking)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.86;entryX=1;entryY=0.86;"))
    c.append(make_edge("e_asis_0_8", "8. Berkas Ekspor CSV (Clean / Error CSV)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.96;entryX=1;entryY=0.96;"))
    diagrams.append(build_diagram("dfd_asis_level_0", "AS-IS Level 0 (Diagram Konteks)", c, 1600, 1000))

    # 2. AS-IS Level 1
    c = []
    c.append(make_vertex("t_asis_1", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — AS-IS (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 4 Sub-Proses dan 3 Data Store Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 750, 50))
    c.append(make_vertex("E1_ASIS_1", e1_asis_val, ent_style(), 60, 220, 240, 480))
    c.append(make_vertex("D1_ASIS", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 400, 90, 280, 55))
    c.append(make_vertex("D2_ASIS", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 860, 400, 300, 55))
    c.append(make_vertex("D3_ASIS", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 860, 210, 300, 55))

    p1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Scan Direktori &amp; Ingesti Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Auto-scan folder src-dtsen/<br>
• Validasi eksistensi &amp; ukuran berkas<br>
• Ingesti stream PyArrow / fast_import.py
</div>'''
    c.append(make_vertex("P1_ASIS", p1_val, proc_style(), 410, 180, 260, 95))

    p2_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Normalisasi &amp; Evaluasi Kualitas Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Mapping sinonim 48 variabel BPS<br>
• Evaluasi Valid, Warning, Critical<br>
• Catat progres ke import_progress.json
</div>'''
    c.append(make_vertex("P2_ASIS", p2_val, proc_style(), 410, 310, 260, 95))

    p3_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pencarian, Filter &amp; Agregasi KPI</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Eksekusi table scan DuckDB<br>
• Agregasi KPI (Valid/Warn/Crit Count)<br>
• Render Data Table biasa (50 rows/page polos)
</div>'''
    c.append(make_vertex("P3_ASIS", p3_val, proc_style(), 410, 440, 260, 95))

    p4_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Ekspor Berkas CSV Mentah</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Query filter dataset atau error logs<br>
• Stream CSV langsung ke browser<br>
• Unduh Clean CSV / Error CSV polos
</div>'''
    c.append(make_vertex("P4_ASIS", p4_val, proc_style(), 410, 570, 260, 95))

    c.append(make_edge("e_as_salin", "Salin File CSV/XLSX", "E1_ASIS_1", "D1_ASIS", blue_in + "exitX=1;exitY=0.08;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_baca", "Baca Berkas Mentah", "D1_ASIS", "P1_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_as_trig", "Trigger Import (POST)", "E1_ASIS_1", "P1_ASIS", blue_in + "exitX=1;exitY=0.18;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_p1_p2", "Stream Baris Data Mentah", "P1_ASIS", "P2_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_as_p2_d2", "Simpan Data Bersih", "P2_ASIS", "D2_ASIS", green_out + "exitX=1;exitY=0.7;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_p2_d3", "Rekam Log Audit Error", "P2_ASIS", "D3_ASIS", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_p2_e1", "Update Progres &amp; Ringkasan Kualitas", "P2_ASIS", "E1_ASIS_1", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.38;"))
    c.append(make_edge("e_as_e1_p3", "Parameter Filter &amp; Keyword", "E1_ASIS_1", "P3_ASIS", purple_flow + "exitX=1;exitY=0.55;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_p3_d2", "Query Table Scan", "P3_ASIS", "D2_ASIS", purple_flow + "exitX=0.9;exitY=0.2;entryX=0;entryY=0.8;"))
    c.append(make_edge("e_as_d2_p3", "Dataset Terfilter", "D2_ASIS", "P3_ASIS", purple_flow + "exitX=0;exitY=0.3;entryX=0.9;entryY=0.8;"))
    c.append(make_edge("e_as_p3_e1", "Tabel Data Polos &amp; KPI Cards", "P3_ASIS", "E1_ASIS_1", green_out + "exitX=0;exitY=0.8;entryX=1;entryY=0.68;"))
    c.append(make_edge("e_as_e1_p4", "Permintaan Ekspor CSV", "E1_ASIS_1", "P4_ASIS", blue_in + "exitX=1;exitY=0.82;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_as_d2_p4", "Ambil Data Bersih", "D2_ASIS", "P4_ASIS", green_out + "exitX=0.5;exitY=1;entryX=1;entryY=0.5;", points=[(1010, 618)]))
    c.append(make_edge("e_as_p4_e1", "Stream File Unduhan CSV", "P4_ASIS", "E1_ASIS_1", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.95;"))
    diagrams.append(build_diagram("dfd_asis_level_1", "AS-IS Level 1 (Dekomposisi Sistem)", c, 1800, 1200))

    # 3. AS-IS Level 2 P1.0
    c = []
    c.append(make_vertex("t_l2_as_p1", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 1.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Scan Direktori &amp; Ingesti Berkas Mentah Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 750, 50))
    c.append(make_vertex("E1_L2_ASIS_P1", e1_asis_val, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D1_L2_ASIS_P1", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 350, 90, 280, 55))
    p11_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.1</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Auto-Scan Direktori Sumber</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Scan folder lokal src-dtsen/<br>• Deteksi berkas CSV / XLSX aktif<br>• Ambil metadata file (nama &amp; size)</div>'''
    c.append(make_vertex("P1_1_ASIS", p11_asis, proc_style(), 350, 230, 240, 110))
    p12_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.2</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Validasi Format &amp; Ukuran Berkas</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Cek validitas ekstensi (.csv / .xlsx)<br>• Validasi berkas tidak korup/kosong<br>• Kembalikan status siap impor</div>'''
    c.append(make_vertex("P1_2_ASIS", p12_asis, proc_style(), 650, 230, 240, 110))
    p13_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.3</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Penerimaan Trigger Impor Lokal</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Tangkap POST /import-local dari Operator<br>• Kunci berkas agar tidak terduplikasi<br>• Panggil script fast_import.py</div>'''
    c.append(make_vertex("P1_3_ASIS", p13_asis, proc_style(), 950, 230, 240, 110))
    p14_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.4</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Streaming PyArrow Ingestion</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Baca blok data CSV/XLSX ke memori<br>• Eksekusi fast_import.py secara streaming<br>• Konversi batch data mentah</div>'''
    c.append(make_vertex("P1_4_ASIS", p14_asis, proc_style(), 800, 420, 250, 110))
    p15_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.5</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Penyaluran Stream Baris Mentah</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Buffer batch baris data mentah<br>• Salurkan batch ke Proses 2.0 (Normalisasi)<br>• Jaga kestabilan alokasi RAM</div>'''
    c.append(make_vertex("P1_5_ASIS", p15_asis, proc_style(), 1100, 420, 250, 110))
    c.append(make_edge("e_l2_as_p1_salin", "Salin Berkas CSV/XLSX", "E1_L2_ASIS_P1", "D1_L2_ASIS_P1", blue_in + "exitX=1;exitY=0.08;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_baca", "Daftar Berkas Mentah", "D1_L2_ASIS_P1", "P1_1_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p1_1_2", "Metadata File Terdeteksi", "P1_1_ASIS", "P1_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_trig", "Trigger Impor (POST)", "E1_L2_ASIS_P1", "P1_3_ASIS", blue_in + "exitX=1;exitY=0.25;entryX=0.5;entryY=0;", points=[(1070, 180)]))
    c.append(make_edge("e_l2_as_p1_2_3", "Berkas Valid Siap Impor", "P1_2_ASIS", "P1_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_3_4", "Inisialisasi fast_import.py", "P1_3_ASIS", "P1_4_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p1_4_5", "Raw Chunk Records", "P1_4_ASIS", "P1_5_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p1", "AS-IS Level 2 - P1.0 (Scan & Ingesti Data)", c, 1800, 1000))

    # 4. AS-IS Level 2 P2.0
    c = []
    c.append(make_vertex("t_l2_as_p2", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 2.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Normalisasi Kolom &amp; Evaluasi Kualitas Data Standar Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 850, 50))
    c.append(make_vertex("E1_L2_ASIS_P2", e1_asis_val, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D2_L2_ASIS_P2", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih siap query</span>', store_style("#c084fc", "#3b0764"), 950, 640, 290, 55))
    c.append(make_vertex("D3_L2_ASIS_P2", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali Critical &amp; Warning DuckDB</span>', store_style("#fb7185", "#4c0519"), 1280, 640, 290, 55))
    p21_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.1</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Mapping Sinonim Kolom BPS</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Baca kamus sinonim variabel BPS<br>• Petakan nama kolom input ke nama standar<br>• Validasi kelengkapan 48 variabel wajib</div>'''
    c.append(make_vertex("P2_1_ASIS", p21_asis, proc_style(), 340, 230, 240, 110))
    p22_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.2</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Pemeriksaan Aturan Kualitas</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Cek panjang NIK (16 digit)<br>• Cek rentang nilai Desil (1 s/d 10)<br>• Klasifikasi: Valid, Warning, Critical</div>'''
    c.append(make_vertex("P2_2_ASIS", p22_asis, proc_style(), 650, 230, 240, 110))
    p23_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.3</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Pemisahan Record Bersih &amp; Error</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Pisahkan baris status Valid<br>• Pisahkan baris Critical &amp; Warning<br>• Format struktur record untuk disimpan</div>'''
    c.append(make_vertex("P2_3_ASIS", p23_asis, proc_style(), 960, 230, 250, 110))
    p24_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.4</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Simpan Data Bersih ke DuckDB</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Batch insert ke tabel dtsen_data<br>• Kompresi kolumnar halaman vektor<br>• Indexing kolom kunci NIK &amp; Desil</div>'''
    c.append(make_vertex("P2_4_ASIS", p24_asis, proc_style(), 960, 430, 250, 110))
    p25_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.5</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Catat Log Audit Error ke DuckDB</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Tulis rekaman anomali ke quality_audit_logs<br>• Simpan deskripsi kesalahan &amp; nomor baris<br>• Siapkan data untuk pelaporan audit</div>'''
    c.append(make_vertex("P2_5_ASIS", p25_asis, proc_style(), 1260, 430, 250, 110))
    p26_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.6</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Update Progress JSON &amp; Metrik</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Tulis progres ke import_progress.json<br>• Hitung total baris &amp; rasio validitas<br>• Kirim ringkasan kualitas ke Operator</div>'''
    c.append(make_vertex("P2_6_ASIS", p26_asis, proc_style(), 650, 430, 250, 110))
    c.append(make_edge("e_l2_as_p2_1_2", "48 Kolom Terpetakan", "P2_1_ASIS", "P2_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_2_3", "Record Berlabel Status Kualitas", "P2_2_ASIS", "P2_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_3_4", "Batch Record Bersih (Valid)", "P2_3_ASIS", "P2_4_ASIS", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_3_5", "Batch Record Anomali (Crit/Warn)", "P2_3_ASIS", "P2_5_ASIS", red_alert + "exitX=0.7;exitY=1;entryX=0.5;entryY=0;", points=[(1135, 380), (1385, 380)]))
    c.append(make_edge("e_l2_as_p2_4_d2", "Simpan Data Bersih", "P2_4_ASIS", "D2_L2_ASIS_P2", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_5_d3", "Simpan Log Anomali", "P2_5_ASIS", "D3_L2_ASIS_P2", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_4_6", "Status Batch Selesai", "P2_4_ASIS", "P2_6_ASIS", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_6_e1", "Update Progres &amp; Ringkasan Kualitas", "P2_6_ASIS", "E1_L2_ASIS_P2", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p2", "AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)", c, 1850, 1000))

    # 5. AS-IS Level 2 P3.0
    c = []
    c.append(make_vertex("t_l2_as_p3", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 3.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pencarian, Query Table Scan &amp; Render Grid Polos (Tanpa Masking PII)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 850, 50))
    c.append(make_vertex("E1_L2_ASIS_P3", e1_asis_val, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D2_L2_ASIS_P3", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 650, 90, 280, 55))
    p31_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.1</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Input Parser Filter &amp; Keyword</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Terima parameter filter NIK, Wilayah, Desil<br>• Parse kata kunci pencarian dari Operator<br>• Bangun kondisi filter WHERE</div>'''
    c.append(make_vertex("P3_1_ASIS", p31_asis, proc_style(), 350, 230, 250, 110))
    p32_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.2</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic SQL Query Constructor</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Bangun query SELECT terhadap DuckDB<br>• Terapkan limit 50 baris &amp; offset paginasi<br>• Siapkan perintah query scan memori</div>'''
    c.append(make_vertex("P3_2_ASIS", p32_asis, proc_style(), 680, 230, 250, 110))
    p33_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.3</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">DuckDB Table Scan Execution</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Eksekusi table scan pada tabel dtsen_data<br>• Ambil baris data mentah terfilter<br>• Ekstrak data numerik untuk metrik agregat</div>'''
    c.append(make_vertex("P3_3_ASIS", p33_asis, proc_style(), 1020, 230, 260, 110))
    p34_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.4</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">KPI Statistics Calculator</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Hitung total baris hasil filter (COUNT)<br>• Agregasi jumlah baris Valid, Warn, Crit<br>• Siapkan data angka untuk KPI cards</div>'''
    c.append(make_vertex("P3_4_ASIS", p34_asis, proc_style(), 1020, 420, 260, 110))
    p35_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.5</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Render Grid Polos (Tanpa Masking)</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Render tabel 50 baris per halaman<br>• Data PII ditampilkan polos apa adanya<br>• Transmisi tabel biasa &amp; KPI card ke browser</div>'''
    c.append(make_vertex("P3_5_ASIS", p35_asis, proc_style(), 520, 420, 270, 110))
    c.append(make_edge("e_l2_as_p3_filt", "Parameter Filter &amp; Keyword", "E1_L2_ASIS_P3", "P3_1_ASIS", purple_flow + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_1_2", "Parsed Filter Criteria", "P3_1_ASIS", "P3_2_ASIS", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_2_d2", "Query Table Scan", "P3_2_ASIS", "D2_L2_ASIS_P3", purple_flow + "exitX=0.5;exitY=0;entryX=0.5;entryY=1;"))
    c.append(make_edge("e_l2_as_p3_d2_3", "Raw Columnar Records", "D2_L2_ASIS_P3", "P3_3_ASIS", purple_flow + "exitX=1;exitY=0.5;entryX=0.5;entryY=0;", points=[(1150, 118)]))
    c.append(make_edge("e_l2_as_p3_3_4", "Aliran Data Numerik", "P3_3_ASIS", "P3_4_ASIS", purple_flow + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p3_3_5", "Raw Tabular Records (Tanpa Masking)", "P3_3_ASIS", "P3_5_ASIS", purple_flow + "exitX=0.2;exitY=1;entryX=0.9;entryY=0;", points=[(1072, 380), (763, 380)]))
    c.append(make_edge("e_l2_as_p3_4_5", "Metrik Ringkasan KPI", "P3_4_ASIS", "P3_5_ASIS", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_5_e1", "Tabel Data Polos &amp; KPI Cards", "P3_5_ASIS", "E1_L2_ASIS_P3", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p3", "AS-IS Level 2 - P3.0 (Pencarian & Agregasi KPI)", c, 1800, 1000))

    # 6. AS-IS Level 2 P4.0
    c = []
    c.append(make_vertex("t_l2_as_p4", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 4.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Ekspor Berkas CSV Polos Tanpa Kompresi/Enkripsi Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 850, 50))
    c.append(make_vertex("E1_L2_ASIS_P4", e1_asis_val, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D2_L2_ASIS_P4", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih</span>', store_style("#c084fc", "#3b0764"), 350, 90, 280, 55))
    c.append(make_vertex("D3_L2_ASIS_P4", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam log anomali kualitas</span>', store_style("#fb7185", "#4c0519"), 710, 90, 290, 55))
    p41_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.1</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Parser Permintaan Ekspor Berkas</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Tangkap request ekspor dari Operator<br>• Cek tipe berkas (Clean CSV / Error CSV)<br>• Teruskan instruksi query penarikan data</div>'''
    c.append(make_vertex("P4_1_ASIS", p41_asis, proc_style(), 350, 230, 270, 110))
    p42_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.2</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Query Dataset Bersih atau Error</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Query baris bersih dari dtsen_data (D2)<br>• Query baris anomali dari audit_logs (D3)<br>• Tarik data dalam format tabular mentah</div>'''
    c.append(make_vertex("P4_2_ASIS", p42_asis, proc_style(), 700, 230, 270, 110))
    p43_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.3</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Raw CSV Formatter &amp; Buffer</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Format baris data ke string CSV biasa<br>• Tulis header kolom &amp; delimiter koma<br>• Buffer baris teks tanpa kompresi ZIP</div>'''
    c.append(make_vertex("P4_3_ASIS", p43_asis, proc_style(), 1050, 230, 270, 110))
    p44_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.4</div><div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Direct Browser CSV File Streamer</div><hr style="border:1px solid #007ACC;margin:5px 0;"><div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">• Set HTTP Header: text/csv<br>• Stream berkas langsung ke browser<br>• Operator menerima Clean / Error CSV polos</div>'''
    c.append(make_vertex("P4_4_ASIS", p44_asis, proc_style(), 650, 420, 280, 110))
    c.append(make_edge("e_l2_as_p4_req", "Permintaan Ekspor CSV", "E1_L2_ASIS_P4", "P4_1_ASIS", blue_in + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_1_2", "Target Tipe Ekspor", "P4_1_ASIS", "P4_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_d2_2", "Ambil Data Bersih", "D2_L2_ASIS_P4", "P4_2_ASIS", green_out + "exitX=0.5;exitY=1;entryX=0.2;entryY=0;", points=[(490, 180), (754, 180)]))
    c.append(make_edge("e_l2_as_p4_d3_2", "Ambil Log Error", "D3_L2_ASIS_P4", "P4_2_ASIS", red_alert + "exitX=0.5;exitY=1;entryX=0.7;entryY=0;", points=[(855, 180), (889, 180)]))
    c.append(make_edge("e_l2_as_p4_2_3", "Raw Tabular Records", "P4_2_ASIS", "P4_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_3_4", "Stream Berkas CSV Polos", "P4_3_ASIS", "P4_4_ASIS", green_out + "exitX=0.5;exitY=1;entryX=1;entryY=0.5;", points=[(1185, 475)]))
    c.append(make_edge("e_l2_as_p4_4_e1", "Stream File Unduhan CSV", "P4_4_ASIS", "E1_L2_ASIS_P4", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p4", "AS-IS Level 2 - P4.0 (Ekspor CSV Mentah)", c, 1800, 1000))

    return diagrams

print("AS-IS diagrams ready.")
