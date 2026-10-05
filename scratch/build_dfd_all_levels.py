import xml.sax.saxutils as saxutils
import xml.etree.ElementTree as ET
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

# STYLES AS REQUESTED:
# Entity and Process color: #66B2FF
# Text color: Black (#000000)
# Edge colors preserved!

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

# Edge styles preserved exactly
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

def generate_all():
    diagrams = []

    # =========================================================================
    # TAB 1: AS-IS Level 0
    # =========================================================================
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
    c.append(make_vertex("E1_ASIS_0", e1_asis_val, ent_style(), 80, 220, 240, 440))

    c.append(make_edge("e_asis_0_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.09;entryX=0;entryY=0.09;"))
    c.append(make_edge("e_asis_0_2", "2. Pilihan File &amp; Perintah Impor Lokal", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.22;entryX=0;entryY=0.22;"))
    c.append(make_edge("e_asis_0_3", "3. Kriteria Filter Multi-Kolom &amp; Keyword", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.35;entryX=0;entryY=0.35;"))
    c.append(make_edge("e_asis_0_4", "4. Permintaan Ekspor Berkas CSV", "E1_ASIS_0", "P0_ASIS", blue_in + "exitX=1;exitY=0.48;entryX=0;entryY=0.48;"))
    c.append(make_edge("e_asis_0_5", "5. Status &amp; Progress Bar Ingesti Data", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.61;entryX=1;entryY=0.61;"))
    c.append(make_edge("e_asis_0_6", "6. Ringkasan Metrik KPI Kualitas (Count)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.74;entryX=1;entryY=0.74;"))
    c.append(make_edge("e_asis_0_7", "7. Tabel Data DTSEN Biasa (Tanpa PII Masking)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.86;entryX=1;entryY=0.86;"))
    c.append(make_edge("e_asis_0_8", "8. Berkas Ekspor CSV (Clean / Error CSV)", "P0_ASIS", "E1_ASIS_0", green_out + "exitX=0;exitY=0.97;entryX=1;entryY=0.97;"))
    diagrams.append(build_diagram("dfd_asis_level_0", "AS-IS Level 0 (Diagram Konteks)", c, 1600, 1000))

    # =========================================================================
    # TAB 2: AS-IS Level 1
    # =========================================================================
    c = []
    c.append(make_vertex("t_asis_1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — AS-IS (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 4 Sub-Proses dan 3 Data Store Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 850, 50))
    c.append(make_vertex("E1_ASIS_1", e1_asis_val, ent_style(), 60, 200, 240, 680))
    c.append(make_vertex("D1_ASIS", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 420, 100, 320, 55))
    c.append(make_vertex("D3_ASIS", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 900, 240, 340, 55))
    c.append(make_vertex("D2_ASIS", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 900, 480, 340, 60))

    p1_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Scan Direktori &amp; Ingesti Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Auto-scan folder src-dtsen/<br>
• Validasi eksistensi &amp; ukuran berkas<br>
• Ingesti stream PyArrow / fast_import.py
</div>'''
    c.append(make_vertex("P1_ASIS", p1_asis_val, proc_style(), 420, 170, 320, 100))

    p2_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Normalisasi &amp; Evaluasi Kualitas Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Mapping sinonim 48 variabel BPS<br>
• Evaluasi Valid, Warning, Critical<br>
• Catat progres ke import_progress.json
</div>'''
    c.append(make_vertex("P2_ASIS", p2_asis_val, proc_style(), 420, 320, 320, 110))

    p3_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pencarian, Filter &amp; Agregasi KPI</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Eksekusi table scan DuckDB<br>
• Agregasi KPI (Valid/Warn/Crit Count)<br>
• Render Data Table biasa (50 rows/page polos)
</div>'''
    c.append(make_vertex("P3_ASIS", p3_asis_val, proc_style(), 420, 500, 320, 110))

    p4_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Ekspor Berkas CSV Mentah</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Query filter dataset atau error logs<br>
• Stream CSV langsung ke browser<br>
• Unduh Clean CSV / Error CSV polos
</div>'''
    c.append(make_vertex("P4_ASIS", p4_asis_val, proc_style(), 420, 635, 320, 100))

    c.append(make_edge("e_asis_d1_in", "Salin File CSV/XLSX", "E1_ASIS_1", "D1_ASIS", blue_in + "exitX=1;exitY=0.04;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_asis_d1_read", "Baca Berkas Mentah", "D1_ASIS", "P1_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_asis_p1_trig", "Trigger Import (POST)", "E1_ASIS_1", "P1_ASIS", blue_in + "exitX=1;exitY=0.12;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_asis_p1_p2", "Stream Baris Data Mentah", "P1_ASIS", "P2_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_asis_p2_d2", "Simpan Data Bersih", "P2_ASIS", "D2_ASIS", green_out + "exitX=1;exitY=0.7;entryX=0;entryY=0.25;"))
    c.append(make_edge("e_asis_p2_d3", "Rekam Log Audit Error", "P2_ASIS", "D3_ASIS", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_asis_p2_stat", "Update Progres &amp; Ringkasan Kualitas", "P2_ASIS", "E1_ASIS_1", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.35;"))
    c.append(make_edge("e_asis_p3_filter", "Parameter Filter &amp; Keyword", "E1_ASIS_1", "P3_ASIS", purple_flow + "exitX=1;exitY=0.58;entryX=0;entryY=0.3;"))
    c.append(make_edge("e_asis_p3_query", "Query Table Scan", "P3_ASIS", "D2_ASIS", purple_flow + "exitX=1;exitY=0.35;entryX=0;entryY=0.75;"))
    c.append(make_edge("e_asis_d2_p3", "Dataset Terfilter", "D2_ASIS", "P3_ASIS", purple_flow + "exitX=0;exitY=0.9;entryX=1;entryY=0.65;"))
    c.append(make_edge("e_asis_p3_out", "Tabel Data Polos &amp; KPI Cards", "P3_ASIS", "E1_ASIS_1", purple_flow + "exitX=0;exitY=0.75;entryX=1;entryY=0.69;"))
    c.append(make_edge("e_asis_p4_req", "Permintaan Ekspor CSV", "E1_ASIS_1", "P4_ASIS", blue_in + "exitX=1;exitY=0.88;entryX=0;entryY=0.3;"))
    c.append(make_edge("e_asis_d2_p4", "Ambil Data Bersih", "D2_ASIS", "P4_ASIS", green_out + "exitX=0.25;exitY=1;entryX=0.85;entryY=0;", points=[(985, 750), (692, 750)]))
    c.append(make_edge("e_asis_p4_down", "Stream File Unduhan CSV", "P4_ASIS", "E1_ASIS_1", green_out + "exitX=0;exitY=0.7;entryX=1;entryY=0.94;"))
    diagrams.append(build_diagram("dfd_asis_level_1", "AS-IS Level 1 (Dekomposisi Sistem)", c, 1800, 1200))

    # =========================================================================
    # TAB 3: AS-IS Level 2 - P1.0 (Scan Direktori & Ingesti Data)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_asis_p1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 1.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Scan Direktori &amp; Ingesti Berkas Mentah Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 950, 50))
    
    c.append(make_vertex("E1_L2_ASIS_P1", e1_asis_val, ent_style(), 60, 220, 230, 350))
    c.append(make_vertex("D1_L2_ASIS_P1", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 350, 90, 300, 55))

    p11_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Auto-Scan Direktori Sumber</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Scan folder lokal src-dtsen/<br>
• Deteksi berkas CSV / XLSX aktif<br>
• Ambil metadata file (nama &amp; size)
</div>'''
    c.append(make_vertex("P1_1_ASIS", p11_asis, proc_style(), 350, 230, 260, 110))

    p12_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Validasi Format &amp; Ukuran Berkas</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Cek validitas ekstensi (.csv / .xlsx)<br>
• Validasi berkas tidak korup/kosong<br>
• Kembalikan status siap impor
</div>'''
    c.append(make_vertex("P1_2_ASIS", p12_asis, proc_style(), 690, 230, 260, 110))

    p13_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Penerimaan Trigger Impor Lokal</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tangkap POST /import-local dari Operator<br>
• Kunci berkas agar tidak terduplikasi<br>
• Panggil script fast_import.py
</div>'''
    c.append(make_vertex("P1_3_ASIS", p13_asis, proc_style(), 1030, 230, 260, 110))

    p14_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Streaming PyArrow Ingestion</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Baca blok data CSV/XLSX ke memori<br>
• Eksekusi fast_import.py secara streaming<br>
• Konversi batch data mentah
</div>'''
    c.append(make_vertex("P1_4_ASIS", p14_asis, proc_style(), 850, 420, 270, 110))

    p15_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Penyaluran Stream Baris Mentah</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Buffer batch baris data mentah<br>
• Salurkan batch ke Proses 2.0 (Normalisasi)<br>
• Jaga kestabilan alokasi RAM
</div>'''
    c.append(make_vertex("P1_5_ASIS", p15_asis, proc_style(), 1180, 420, 260, 110))

    c.append(make_edge("e_l2_as_p1_copy", "Salin Berkas CSV/XLSX", "E1_L2_ASIS_P1", "D1_L2_ASIS_P1", blue_in + "exitX=1;exitY=0.1;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_read", "Daftar Berkas Mentah", "D1_L2_ASIS_P1", "P1_1_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p1_1_2", "Metadata File Terdeteksi", "P1_1_ASIS", "P1_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_trig", "Trigger Impor (POST)", "E1_L2_ASIS_P1", "P1_3_ASIS", blue_in + "exitX=1;exitY=0.4;entryX=0;entryY=0.2;", points=[(320, 360), (320, 200), (990, 200), (990, 252)]))
    c.append(make_edge("e_l2_as_p1_2_3", "Berkas Valid Siap Impor", "P1_2_ASIS", "P1_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p1_3_4", "Inisialisasi fast_import.py", "P1_3_ASIS", "P1_4_ASIS", blue_in + "exitX=0.5;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("e_l2_as_p1_4_5", "Raw Chunk Records", "P1_4_ASIS", "P1_5_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p1", "AS-IS Level 2 - P1.0 (Scan & Ingesti Data)", c, 1800, 1000))

    # =========================================================================
    # TAB 4: AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_asis_p2", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 2.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Normalisasi Kolom &amp; Evaluasi Kualitas Data Standar Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_ASIS_P2", e1_asis_val, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D2_L2_ASIS_P2", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih siap query</span>', store_style("#c084fc", "#3b0764"), 1050, 680, 320, 55))
    c.append(make_vertex("D3_L2_ASIS_P2", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali Critical &amp; Warning DuckDB</span>', store_style("#fb7185", "#4c0519"), 1420, 680, 320, 55))

    p21_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Mapping Sinonim Kolom BPS</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Baca kamus sinonim variabel BPS<br>
• Petakan nama kolom input ke nama standar<br>
• Validasi kelengkapan 48 variabel wajib
</div>'''
    c.append(make_vertex("P2_1_ASIS", p21_asis, proc_style(), 350, 230, 260, 110))

    p22_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Pemeriksaan Aturan Kualitas</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Cek panjang NIK (16 digit)<br>
• Cek rentang nilai Desil (1 s/d 10)<br>
• Klasifikasi: Valid, Warning, Critical
</div>'''
    c.append(make_vertex("P2_2_ASIS", p22_asis, proc_style(), 690, 230, 270, 110))

    p23_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Pemisahan Record Bersih &amp; Error</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Pisahkan baris status Valid<br>
• Pisahkan baris Critical &amp; Warning<br>
• Format struktur record untuk disimpan
</div>'''
    c.append(make_vertex("P2_3_ASIS", p23_asis, proc_style(), 1040, 230, 270, 110))

    p24_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Simpan Data Bersih ke DuckDB</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Batch insert ke tabel dtsen_data<br>
• Kompresi kolumnar halaman vektor<br>
• Indexing kolom kunci NIK &amp; Desil
</div>'''
    c.append(make_vertex("P2_4_ASIS", p24_asis, proc_style(), 1040, 440, 270, 110))

    p25_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Catat Log Audit Error ke DuckDB</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tulis rekaman anomali ke quality_audit_logs<br>
• Simpan deskripsi kesalahan &amp; nomor baris<br>
• Siapkan data untuk pelaporan audit
</div>'''
    c.append(make_vertex("P2_5_ASIS", p25_asis, proc_style(), 1380, 440, 270, 110))

    p26_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.6</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Update Progress JSON &amp; Metrik</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tulis progres ke import_progress.json<br>
• Hitung total baris &amp; rasio validitas<br>
• Kirim ringkasan kualitas ke Operator
</div>'''
    c.append(make_vertex("P2_6_ASIS", p26_asis, proc_style(), 690, 440, 270, 110))

    c.append(make_edge("e_l2_as_p2_1_2", "48 Kolom Terpetakan", "P2_1_ASIS", "P2_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_2_3", "Record Berlabel Status Kualitas", "P2_2_ASIS", "P2_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_3_4", "Batch Record Bersih (Valid)", "P2_3_ASIS", "P2_4_ASIS", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_3_5", "Batch Record Anomali (Crit/Warn)", "P2_3_ASIS", "P2_5_ASIS", red_alert + "exitX=0.8;exitY=1;entryX=0.2;entryY=0;", points=[(1256, 380), (1434, 380)]))
    c.append(make_edge("e_l2_as_p2_4_d2", "Simpan Data Bersih", "P2_4_ASIS", "D2_L2_ASIS_P2", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_5_d3", "Simpan Log Anomali", "P2_5_ASIS", "D3_L2_ASIS_P2", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p2_4_6", "Status Batch Selesai", "P2_4_ASIS", "P2_6_ASIS", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p2_6_e1", "Update Progres &amp; Ringkasan Kualitas", "P2_6_ASIS", "E1_L2_ASIS_P2", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p2", "AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)", c, 1850, 1000))

    # =========================================================================
    # TAB 5: AS-IS Level 2 - P3.0 (Pencarian, Filter & Agregasi KPI)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_asis_p3", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 3.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pencarian, Query Table Scan &amp; Render Grid Polos (Tanpa Masking PII)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1100, 50))

    c.append(make_vertex("E1_L2_ASIS_P3", e1_asis_val, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D2_L2_ASIS_P3", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 690, 90, 320, 55))

    p31_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Input Parser Filter &amp; Keyword</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Terima parameter filter NIK, Wilayah, Desil<br>
• Parse kata kunci pencarian dari Operator<br>
• Bangun kondisi filter WHERE
</div>'''
    c.append(make_vertex("P3_1_ASIS", p31_asis, proc_style(), 350, 230, 260, 110))

    p32_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic SQL Query Constructor</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Bangun query SELECT terhadap DuckDB<br>
• Terapkan limit 50 baris &amp; offset paginasi<br>
• Siapkan perintah query scan memori
</div>'''
    c.append(make_vertex("P3_2_ASIS", p32_asis, proc_style(), 690, 230, 270, 110))

    p33_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">DuckDB Table Scan Execution</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Eksekusi table scan pada tabel dtsen_data<br>
• Ambil baris data mentah terfilter<br>
• Ekstrak data numerik untuk metrik agregat
</div>'''
    c.append(make_vertex("P3_3_ASIS", p33_asis, proc_style(), 1050, 230, 270, 110))

    p34_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">KPI Statistics Calculator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Hitung total baris hasil filter (COUNT)<br>
• Agregasi jumlah baris Valid, Warn, Crit<br>
• Siapkan data angka untuk KPI cards
</div>'''
    c.append(make_vertex("P3_4_ASIS", p34_asis, proc_style(), 1050, 430, 270, 110))

    p35_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Render Grid Polos (Tanpa Masking)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Render tabel 50 baris per halaman<br>
• Data PII ditampilkan polos apa adanya<br>
• Transmisi tabel biasa &amp; KPI card ke browser
</div>'''
    c.append(make_vertex("P3_5_ASIS", p35_asis, proc_style(), 520, 430, 280, 110))

    c.append(make_edge("e_l2_as_p3_filter", "Parameter Filter &amp; Keyword", "E1_L2_ASIS_P3", "P3_1_ASIS", purple_flow + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_1_2", "Parsed Filter Criteria", "P3_1_ASIS", "P3_2_ASIS", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_2_d2", "Query Table Scan", "P3_2_ASIS", "D2_L2_ASIS_P3", purple_flow + "exitX=0.5;exitY=0;entryX=0.5;entryY=1;"))
    c.append(make_edge("e_l2_as_p3_d2_3", "Raw Columnar Records", "D2_L2_ASIS_P3", "P3_3_ASIS", purple_flow + "exitX=0.8;exitY=1;entryX=0.3;entryY=0;", points=[(946, 180), (1131, 180)]))
    c.append(make_edge("e_l2_as_p3_3_4", "Aliran Data Numerik", "P3_3_ASIS", "P3_4_ASIS", purple_flow + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_as_p3_3_5", "Raw Tabular Records (Tanpa Masking)", "P3_3_ASIS", "P3_5_ASIS", purple_flow + "exitX=0.2;exitY=1;entryX=0.8;entryY=0;", points=[(1104, 380), (744, 380)]))
    c.append(make_edge("e_l2_as_p3_4_5", "Metrik Ringkasan KPI", "P3_4_ASIS", "P3_5_ASIS", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p3_5_e1", "Tabel Data Polos &amp; KPI Cards", "P3_5_ASIS", "E1_L2_ASIS_P3", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p3", "AS-IS Level 2 - P3.0 (Pencarian & Agregasi KPI)", c, 1800, 1000))

    # =========================================================================
    # TAB 6: AS-IS Level 2 - P4.0 (Ekspor Berkas CSV Mentah)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_asis_p4", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 4.0 (AS-IS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Ekspor Berkas CSV Polos Tanpa Kompresi/Enkripsi Sistem Eksisting</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_ASIS_P4", e1_asis_val, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D2_L2_ASIS_P4", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih</span>', store_style("#c084fc", "#3b0764"), 350, 90, 280, 55))
    c.append(make_vertex("D3_L2_ASIS_P4", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam log anomali kualitas</span>', store_style("#fb7185", "#4c0519"), 710, 90, 290, 55))

    p41_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Parser Permintaan Ekspor Berkas</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tangkap request ekspor dari Operator<br>
• Cek tipe berkas (Clean CSV / Error CSV)<br>
• Teruskan instruksi query penarikan data
</div>'''
    c.append(make_vertex("P4_1_ASIS", p41_asis, proc_style(), 350, 230, 270, 110))

    p42_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Query Dataset Bersih atau Error</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Query baris bersih dari dtsen_data (D2)<br>
• Query baris anomali dari audit_logs (D3)<br>
• Tarik data dalam format tabular mentah
</div>'''
    c.append(make_vertex("P4_2_ASIS", p42_asis, proc_style(), 700, 230, 270, 110))

    p43_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Raw CSV Formatter &amp; Buffer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Format baris data ke string CSV biasa<br>
• Tulis header kolom &amp; delimiter koma<br>
• Buffer baris teks tanpa kompresi ZIP
</div>'''
    c.append(make_vertex("P4_3_ASIS", p43_asis, proc_style(), 1050, 230, 270, 110))

    p44_asis = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Direct Browser CSV File Streamer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Set HTTP Header: text/csv<br>
• Stream berkas langsung ke browser<br>
• Operator menerima Clean / Error CSV polos
</div>'''
    c.append(make_vertex("P4_4_ASIS", p44_asis, proc_style(), 650, 420, 280, 110))

    c.append(make_edge("e_l2_as_p4_req", "Permintaan Ekspor CSV", "E1_L2_ASIS_P4", "P4_1_ASIS", blue_in + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_1_2", "Target Tipe Ekspor", "P4_1_ASIS", "P4_2_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_d2_2", "Ambil Data Bersih", "D2_L2_ASIS_P4", "P4_2_ASIS", green_out + "exitX=0.5;exitY=1;entryX=0.2;entryY=0;", points=[(490, 180), (754, 180)]))
    c.append(make_edge("e_l2_as_p4_d3_2", "Ambil Log Error", "D3_L2_ASIS_P4", "P4_2_ASIS", red_alert + "exitX=0.5;exitY=1;entryX=0.7;entryY=0;", points=[(855, 180), (889, 180)]))
    c.append(make_edge("e_l2_as_p4_2_3", "Raw Tabular Records", "P4_2_ASIS", "P4_3_ASIS", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_as_p4_3_4", "Stream Berkas CSV Polos", "P4_3_ASIS", "P4_4_ASIS", green_out + "exitX=0.5;exitY=1;entryX=1;entryY=0.5;", points=[(1185, 475)]))
    c.append(make_edge("e_l2_as_p4_4_e1", "Stream File Unduhan CSV", "P4_4_ASIS", "E1_L2_ASIS_P4", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_asis_level_2_p4", "AS-IS Level 2 - P4.0 (Ekspor CSV Mentah)", c, 1800, 1000))

    # =========================================================================
    # TAB 7: TO-BE Level 0
    # =========================================================================
    c = []
    c.append(make_vertex("t_tobe_0", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — TO-BE (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis Data Terpadu &amp; Pengamanan Biometrik (PADU v1.02 — TO-BE)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 750, 50))

    p0_tobe_val = '''<div style="font-size:14px;color:#000000;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#000000;line-height:1.3;">SISTEM ANALISIS DATA DTSEN &amp;<br>PENGAMANAN BIOMETRIK<br>(PADU v1.02 — TO-BE)</div>
<hr style="border:1px solid #007ACC;margin:12px 0;">
<div style="font-size:11px;color:#000000;line-height:1.5;">
• Hybrid Dual-Engine: Laravel 12 + Python DuckDB<br>
• Standalone 100% Offline Processing (13M+ Baris)<br>
• Dynamic Multi-Column Filtering &amp; PII Masking<br>
• Zero-Trace Biometric Guard &amp; user32 Lock
</div>'''
    c.append(make_vertex("P0_TOBE", p0_tobe_val, f"rounded=1;arcSize=20;whiteSpace=wrap;fillColor={PROC_FILL};strokeColor={PROC_BORDER};strokeWidth=3;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;", 640, 240, 360, 460))

    e1_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengunggah berkas mentah DTSEN<br>
• Menjalankan filter dinamis &amp; pencarian<br>
• Meninjau tabel data ter-masking PII<br>
• Menganalisis metrik kualitas data<br>
• Mengunduh paket ZIP arsip &amp; log audit<br>
• Menerima notifikasi peringatan layar
</div>'''
    c.append(make_vertex("E1_TOBE_0", e1_tobe_val, ent_style(), 60, 240, 250, 460))

    e2_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Headless OpenCV Capture (DSHOW)<br>
• Frame visual berkala (loop 0.2s)<br>
• Snapshot verifikasi biometrik
</div>'''
    c.append(make_vertex("E2_TOBE_0", e2_tobe_val, ent_style(), 1320, 240, 250, 180))

    e3_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''
    c.append(make_vertex("E3_TOBE_0", e3_tobe_val, ent_style(), 1320, 520, 250, 180))

    c.append(make_edge("f0_tb_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.087;entryX=0;entryY=0.087;"))
    c.append(make_edge("f0_tb_2", "2. Kriteria Filter Dinamis &amp; Keyword NIK/KK", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.239;entryX=0;entryY=0.239;"))
    c.append(make_edge("f0_tb_3", "3. Perintah Ekspor Data &amp; Terminasi Sesi", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.391;entryX=0;entryY=0.391;"))
    c.append(make_edge("f0_tb_4", "4. Data Tabular Ter-Masking PII &amp; Metrik KPI", "P0_TOBE", "E1_TOBE_0", green_out + "exitX=0;exitY=0.565;entryX=1;entryY=0.565;"))
    c.append(make_edge("f0_tb_5", "5. Paket ZIP Olahan &amp; Log Audit Error CSV", "P0_TOBE", "E1_TOBE_0", green_out + "exitX=0;exitY=0.717;entryX=1;entryY=0.717;"))
    c.append(make_edge("f0_tb_6", "6. Alert Peringatan Layar (Red Lock &amp; Blur UI)", "P0_TOBE", "E1_TOBE_0", red_alert + "exitX=0;exitY=0.870;entryX=1;entryY=0.870;"))

    c.append(make_edge("f0_tb_7", "Stream Frame Video (Headless OpenCV)", "E2_TOBE_0", "P0_TOBE", amber_cam + "exitX=0;exitY=0.278;entryX=1;entryY=0.109;"))
    c.append(make_edge("f0_tb_8", "Sinyal Inisialisasi &amp; Pengaturan Sensor", "P0_TOBE", "E2_TOBE_0", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#64748b;strokeWidth=2;fontColor=#334155;fontSize=10;fontStyle=1;labelBackgroundColor=#f8fafc;labelBorderColor=#cbd5e1;exitX=1;exitY=0.283;entryX=0;entryY=0.722;"))
    c.append(make_edge("f0_tb_9", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0_TOBE", "E3_TOBE_0", red_alert + "exitX=1;exitY=0.717;entryX=0;entryY=0.278;"))
    c.append(make_edge("f0_tb_10", "Sinyal Shutdown / SIGTERM / atexit", "E3_TOBE_0", "P0_TOBE", indigo_sys + "exitX=0;exitY=0.722;entryX=1;entryY=0.891;"))
    diagrams.append(build_diagram("dfd_tobe_level_0", "TO-BE Level 0 (Diagram Konteks)", c, 1600, 1000))

    # =========================================================================
    # TAB 8: TO-BE Level 1 (RE-ORDERED: 1.0 ON LEFT -> 6.0 ON RIGHT)
    # =========================================================================
    c = []
    c.append(make_vertex("t_tobe_1", '<div style="font-size:22px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — TO-BE (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 6 Sub-Proses Fungsional Berurutan Secara Sekuensial (1.0 di Kiri sd 6.0 di Kanan), 5 Data Store, dan 3 Entitas Eksternal</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 950, 30, 1100, 50))

    c.append(make_vertex("E1_TOBE_1", e1_tobe_val, ent_style(), 50, 280, 240, 760))
    c.append(make_vertex("E2_TOBE_1", e2_tobe_val, ent_style(), 360, 130, 310, 150))
    c.append(make_vertex("E3_TOBE_1", e3_tobe_val, ent_style(), 2460, 130, 320, 150))

    c.append(make_vertex("D1_TOBE", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 740, 180, 310, 55))
    c.append(make_vertex("D2_TOBE", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 940, 780, 340, 60))
    c.append(make_vertex("D3_TOBE", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 1480, 780, 340, 55))
    c.append(make_vertex("D4_TOBE", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot wajah pemilik sah sesi aktif (Zero-Trace)</span>', store_style("#f59e0b", "#451a03"), 940, 980, 340, 55))
    c.append(make_vertex("D5_TOBE", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#bbf7d0;">Paket ZIP kompresi Clean CSV + Error CSV + Metadata</span>', store_style("#34d399", "#064e3b"), 2020, 780, 320, 55))

    p1_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Inisialisasi &amp; Verifikasi Biometrik Awal</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• cv2 Headless Capture (adaptasi 2s)<br>
• MediaPipe Evaluasi Jumlah Wajah<br>
• Reject jika 0 / &gt;1 wajah; simpan jika 1
</div>'''
    c.append(make_vertex("P1_TOBE", p1_tb1_val, proc_style(), 360, 480, 310, 140))

    p2_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Ingesti, Normalisasi &amp; Audit Kualitas Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Auto-Scan &amp; PyArrow Ingestion Engine<br>
• Mapping sinonim 48 variabel resmi BPS<br>
• Quality Check Rule: Valid / Warning / Critical
</div>'''
    c.append(make_vertex("P2_TOBE", p2_tb1_val, proc_style(), 740, 480, 310, 140))

    p3_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemrosesan Analitik, Filter &amp; PII Masking</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• DuckDB Memory-Mapped Slicing (&lt; 0.05s)<br>
• PII Data Masking (NIK, Nama, Gaji, Alamat)<br>
• Agregasi KPI instan &amp; Micro-Table (50 rows/page)
</div>'''
    c.append(make_vertex("P3_TOBE", p3_tb1_val, proc_style(), 1140, 480, 320, 140))

    p4_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemantauan Keamanan Real-Time (Guard Loop)</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Face Mesh &amp; Iris Eye-Tracking (Loop 0.2s, CPU &lt; 3%)<br>
• Deteksi User Pergi &gt; 4 Detik<br>
• Deteksi Shoulder Surfing (Mata Asing &gt; 1 Detik)
</div>'''
    c.append(make_vertex("P4_TOBE", p4_tb1_val, proc_style(), 1580, 480, 320, 140))

    p5_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pengelolaan Ekspor &amp; Pengarsipan Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• CsvExportService Packaging Engine<br>
• Kompresi ZIP: Clean CSV + Error CSV + Metadata<br>
• Stream Berkas Unduhan ke Operator
</div>'''
    c.append(make_vertex("P5_TOBE", p5_tb1_val, proc_style(), 2020, 480, 320, 140))

    p6_tb1_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Terminasi Sistem &amp; Zero-Trace Purge</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tangkap Sinyal atexit / SIGTERM / Close Window<br>
• Secure Wipe 0x00 sesi_wajah_aktif.jpg<br>
• Unlink berkas &amp; Flush RAM Cache (Zero-Trace)
</div>'''
    c.append(make_vertex("P6_TOBE", p6_tb1_val, proc_style(), 2460, 480, 320, 140))

    c.append(make_edge("f1_cam_p1", "Frame Snapshot Wajah Awal", "E2_TOBE_1", "P1_TOBE", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_p1_d4", "Simpan Wajah Pemilik Sah (1 Wajah)", "P1_TOBE", "D4_TOBE", amber_cam + "exitX=0.5;exitY=1;entryX=0;entryY=0.5;", points=[(515, 1007)]))
    c.append(make_edge("f1_p1_e1", "Status Sesi / Auto-Reject Silent", "P1_TOBE", "E1_TOBE_1", indigo_sys + "exitX=0;exitY=0.3;entryX=1;entryY=0.15;"))

    c.append(make_edge("f1_e1_d1", "Salin File Mentah CSV/XLSX", "E1_TOBE_1", "D1_TOBE", blue_in + "exitX=1;exitY=0.06;entryX=0;entryY=0.5;", points=[(320, 325), (320, 207)]))
    c.append(make_edge("f1_d1_p2", "Baca Berkas Mentah", "D1_TOBE", "P2_TOBE", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_e1_p2", "Trigger Impor (POST /import-local)", "E1_TOBE_1", "P2_TOBE", blue_in + "exitX=1;exitY=0.25;entryX=0;entryY=0.2;", points=[(710, 470), (710, 508)]))
    c.append(make_edge("f1_p2_e1", "Ringkasan Ingesti &amp; Skor Kualitas", "P2_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.7;entryX=1;entryY=0.33;", points=[(710, 578), (710, 530)]))
    c.append(make_edge("f1_p2_d2", "Batch Ingest Data Bersih 48 Kolom", "P2_TOBE", "D2_TOBE", green_out + "exitX=0.6;exitY=1;entryX=0.2;entryY=0;"))
    c.append(make_edge("f1_p2_d3", "Rekam Anomali (Error Log)", "P2_TOBE", "D3_TOBE", red_alert + "exitX=0.9;exitY=1;entryX=0.1;entryY=0;", points=[(1019, 680), (1514, 680)]))

    c.append(make_edge("f1_e1_p3", "Parameter Filter Dinamis &amp; Keyword", "E1_TOBE_1", "P3_TOBE", purple_flow + "exitX=1;exitY=0.45;entryX=0;entryY=0.2;", points=[(1100, 622), (1100, 508)]))
    c.append(make_edge("f1_p3_e1", "Grid Data PII-Masked &amp; KPI Cards", "P3_TOBE", "E1_TOBE_1", purple_flow + "exitX=0;exitY=0.7;entryX=1;entryY=0.55;", points=[(1100, 578), (1100, 698)]))
    c.append(make_edge("f1_p3_d2", "Query Slicing Kolumnar (&lt; 0.05s)", "P3_TOBE", "D2_TOBE", purple_flow + "exitX=0.3;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("f1_d2_p3", "Dataset Terfilter", "D2_TOBE", "P3_TOBE", purple_flow + "exitX=0.85;exitY=0;entryX=0.6;entryY=1;"))

    c.append(make_edge("f1_cam_p4", "Continuous Video Stream (Loop 0.2s)", "E2_TOBE_1", "P4_TOBE", amber_cam + "exitX=1;exitY=0.5;entryX=0.5;entryY=0;", points=[(1740, 205)]))
    c.append(make_edge("f1_d4_p4", "Baca Profil Wajah Sah Referensi", "D4_TOBE", "P4_TOBE", amber_cam + "exitX=1;exitY=0.5;entryX=0.5;entryY=1;", points=[(1740, 1007)]))
    c.append(make_edge("f1_p4_e3", "Instruksi Kunci Layar (user32.LockWorkStation)", "P4_TOBE", "E3_TOBE_1", red_alert + "exitX=0.7;exitY=0;entryX=0;entryY=0.5;", points=[(1804, 380), (2420, 380), (2420, 205)]))
    c.append(make_edge("f1_p4_e1", "Red Lock Alert &amp; Blur UI Event", "P4_TOBE", "E1_TOBE_1", red_alert + "exitX=0;exitY=0.85;entryX=1;entryY=0.70;", points=[(1540, 599), (1540, 740), (320, 740), (320, 812)]))

    c.append(make_edge("f1_e1_p5", "Permintaan Ekspor Arsip ZIP", "E1_TOBE_1", "P5_TOBE", green_out + "exitX=1;exitY=0.82;entryX=0;entryY=0.3;", points=[(340, 903), (340, 930), (1980, 930), (1980, 522)]))
    c.append(make_edge("f1_p5_e1", "Stream Unduhan Berkas ZIP", "P5_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.75;entryX=1;entryY=0.90;", points=[(1960, 585), (1960, 950), (320, 950), (320, 964)]))
    c.append(make_edge("f1_d2_p5", "Ambil Data Bersih Terpilih", "D2_TOBE", "P5_TOBE", green_out + "exitX=0.95;exitY=0.5;entryX=0.3;entryY=1;", points=[(1320, 810), (1320, 890), (2116, 890)]))
    c.append(make_edge("f1_d3_p5", "Ambil Rekam Error Audit", "D3_TOBE", "P5_TOBE", red_alert + "exitX=1;exitY=0.5;entryX=0.6;entryY=1;", points=[(1850, 807), (1850, 870), (2212, 870)]))
    c.append(make_edge("f1_p5_d5", "Simpan Paket ZIP (src-export/)", "P5_TOBE", "D5_TOBE", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))

    c.append(make_edge("f1_e3_p6", "Sinyal Shutdown OS / SIGTERM / atexit", "E3_TOBE_1", "P6_TOBE", indigo_sys + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_e1_p6", "Perintah Tutup Aplikasi / Selesai", "E1_TOBE_1", "P6_TOBE", red_alert + "exitX=1;exitY=0.98;entryX=0;entryY=0.85;", points=[(340, 1025), (340, 1070), (2400, 1070), (2400, 599)]))
    c.append(make_edge("f1_p6_d4", "Secure Overwrite 0x00 &amp; Hapus File", "P6_TOBE", "D4_TOBE", red_alert + "exitX=0.5;exitY=1;entryX=0.8;entryY=1;", points=[(2620, 1090), (1212, 1090)]))
    diagrams.append(build_diagram("dfd_tobe_level_1", "TO-BE Level 1 (Dekomposisi Sistem)", c, 3200, 1300))

    # =========================================================================
    # TAB 9: TO-BE Level 2 - P1.0 (Inisialisasi & Verifikasi Biometrik)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 1.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Inisialisasi &amp; Verifikasi Biometrik Awal (Headless OpenCV + MediaPipe)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 950, 50))
    
    c.append(make_vertex("E2_L2_P1", e2_tobe_val, ent_style(), 60, 220, 230, 180))
    c.append(make_vertex("E1_L2_P1", e1_tobe_val, ent_style(), 1480, 220, 230, 420))
    c.append(make_vertex("D4_L2_P1", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot matriks wajah tunggal pemilik sah</span>', store_style("#f59e0b", "#451a03"), 720, 580, 360, 60))

    p11_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Inisialisasi Device &amp; Warm-Up Kamera</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• cv2.VideoCapture(0, DSHOW)<br>
• Warm-up adaptasi auto-exposure (2s)<br>
• Stream frame awal beresolusi 640x480
</div>'''
    c.append(make_vertex("P1_1", p11_val, proc_style(), 350, 220, 250, 120))

    p12_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Deteksi &amp; Ekstraksi Face Mesh</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• MediaPipe FaceMesh Engine<br>
• Ekstraksi 468 titik koordinat 3D<br>
• Identifikasi bounding box &amp; centroid
</div>'''
    c.append(make_vertex("P1_2", p12_val, proc_style(), 680, 220, 250, 120))

    p13_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Validasi Kebijakan Wajah Tunggal</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Hitung total face detected<br>
• Rule: Reject if count == 0 or count &gt; 1<br>
• Valid if count == 1 (Single User)
</div>'''
    c.append(make_vertex("P1_3", p13_val, proc_style(), 1010, 220, 260, 120))

    p14_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Registrasi Kredensial Pemilik Sah</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Crop &amp; encode frame acuan (baseline)<br>
• Tulis ke sesi_wajah_aktif.jpg di RAM<br>
• Catat token identifikasi sesi aktif
</div>'''
    c.append(make_vertex("P1_4", p14_val, proc_style(), 840, 420, 260, 110))

    p15_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Handshake Sesi &amp; Dispatcher UI</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Teruskan token aktif ke Laravel Web UI<br>
• Unlock tampilan login/dasbor aplikasi<br>
• Kirim notifikasi 'Autentikasi Sukses'
</div>'''
    c.append(make_vertex("P1_5", p15_val, proc_style(), 1180, 420, 240, 110))

    c.append(make_edge("e_l2_p1_cam", "Frame Video Mentah", "E2_L2_P1", "P1_1", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p1_1_2", "Frame Visual Siap Injeksi", "P1_1", "P1_2", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p1_2_3", "Vektor 468 Landmark &amp; Count", "P1_2", "P1_3", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p1_3_4", "Snapshot Wajah Sah (Count = 1)", "P1_3", "P1_4", amber_cam + "exitX=0.3;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("e_l2_p1_4_d4", "Simpan Buffer Wajah Acuan", "P1_4", "D4_L2_P1", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p1_4_5", "Sinyal Otorisasi Sesi Sah", "P1_4", "P1_5", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p1_5_e1", "Buka Dashboard Analitik", "P1_5", "E1_L2_P1", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.65;"))
    c.append(make_edge("e_l2_p1_3_rej", "Auto-Reject (Count 0 atau &gt;1)", "P1_3", "E1_L2_P1", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.15;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p1", "TO-BE Level 2 - P1.0 (Inisialisasi Biometrik)", c, 1800, 1000))

    # =========================================================================
    # TAB 10: TO-BE Level 2 - P2.0 (Ingesti, Normalisasi & Audit Kualitas Data)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p2", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 2.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Ingesti Vektor, Pemetaan Sinonim BPS &amp; Evaluasi Kualitas Data DuckDB</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_P2", e1_tobe_val, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D1_L2_P2", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 340, 90, 280, 55))
    c.append(make_vertex("D2_L2_P2", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih siap query</span>', store_style("#c084fc", "#3b0764"), 1050, 680, 320, 55))
    c.append(make_vertex("D3_L2_P2", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali Critical &amp; Warning DuckDB</span>', store_style("#fb7185", "#4c0519"), 1420, 680, 320, 55))

    p21_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Scan Direktori &amp; Validasi Berkas</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Scan folder lokal src-dtsen/<br>
• Cek eksistensi, ekstensi, &amp; ukuran berkas<br>
• Validasi permission file read-only
</div>'''
    c.append(make_vertex("P2_1", p21_val, proc_style(), 340, 240, 260, 110))

    p22_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Streaming PyArrow Chunking Reader</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Fast zero-copy PyArrow CSV/XLSX reader<br>
• Streaming batch chunk (500k-1M baris/batch)<br>
• Kecepatan &gt; 1.5 Juta baris/detik
</div>'''
    c.append(make_vertex("P2_2", p22_val, proc_style(), 670, 240, 270, 110))

    p23_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Synonym Header Mapping BPS</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Kamus sinonim 48 variabel individu BPS<br>
• Kamus 52 variabel keluarga (KK)<br>
• Standarisasi nama atribut kolom seragam
</div>'''
    c.append(make_vertex("P2_3", p23_val, proc_style(), 1010, 240, 270, 110))

    p24_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Rule Engine Evaluasi Kualitas Data</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Cek NIK == 16 digit &amp; Nama regex valid<br>
• Cek Desil (1-10) &amp; Usia (0-120)<br>
• Klasifikasi: Valid, Warning, Critical
</div>'''
    c.append(make_vertex("P2_4", p24_val, proc_style(), 1350, 240, 270, 110))

    p25_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dual-Table Batch Ingestion</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Batch insert clean data ke dtsen_data<br>
• Batch log anomali ke quality_audit_logs<br>
• Kompresi kolumnar vektor DuckDB
</div>'''
    c.append(make_vertex("P2_5", p25_val, proc_style(), 1200, 460, 280, 110))

    p26_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.6</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">KPI Calculator &amp; Progress Notifier</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Agregasi baris Valid, Warning, Critical<br>
• Estimasi kardinalitas KK unik via HyperLogLog<br>
• Update import_progress.json &amp; UI bar
</div>'''
    c.append(make_vertex("P2_6", p26_val, proc_style(), 670, 460, 270, 110))

    c.append(make_edge("e_l2_p2_scan", "Perintah Trigger Impor", "E1_L2_P2", "P2_1", blue_in + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p2_d1", "File Mentah CSV/XLSX", "D1_L2_P2", "P2_1", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p2_1_2", "Valid File Pointer", "P2_1", "P2_2", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p2_2_3", "Raw PyArrow Table Batch", "P2_2", "P2_3", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p2_3_4", "Standardized 48-Column Chunks", "P2_3", "P2_4", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p2_4_5", "Audit Flagged Batches", "P2_4", "P2_5", green_out + "exitX=0.5;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("e_l2_p2_5_d2", "Clean Vector Pages", "P2_5", "D2_L2_P2", green_out + "exitX=0.3;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p2_5_d3", "Audit Error Rows", "P2_5", "D3_L2_P2", red_alert + "exitX=0.8;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p2_5_6", "Metrik Batch Selesai", "P2_5", "P2_6", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_p2_6_e1", "Progress &amp; KPI Summary", "P2_6", "E1_L2_P2", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p2", "TO-BE Level 2 - P2.0 (Ingesti & Audit Kualitas)", c, 1850, 1000))

    # =========================================================================
    # TAB 11: TO-BE Level 2 - P3.0 (Pemrosesan Analitik, Filter & PII Masking)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p3", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 3.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pemrosesan Analitik, Slicing DuckDB &amp; Dynamic PII Masking Engine</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_P3", e1_tobe_val, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D2_L2_P3", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 680, 90, 320, 55))

    p31_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Request Query Parser &amp; Sanitizer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Parse filter: NIK, Provinsi, Kab/Kota<br>
• Parse kriteria: Desil, Status Validitas<br>
• Sanitasi input cegah SQL/Code Injection
</div>'''
    c.append(make_vertex("P3_1", p31_val, proc_style(), 350, 240, 260, 110))

    p32_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Memory-Mapped Vector Slicing Engine</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Eksekusi query kolumnar DuckDB<br>
• Scan teroptimasi zero-copy (&lt; 0.05 detik)<br>
• Hasilkan dataset terfilter di memori
</div>'''
    c.append(make_vertex("P3_2", p32_val, proc_style(), 700, 240, 280, 110))

    p33_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic PII Masking Transformer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• NIK: 3201************ (4 digit awal)<br>
• Nama: B*** S****** (inisial per kata)<br>
• Gaji: Rp 8.xxx.xxx | Alamat: sensor parsial
</div>'''
    c.append(make_vertex("P3_3", p33_val, proc_style(), 1060, 240, 280, 110))

    p34_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">HyperLogLog &amp; Instant KPI Aggregator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Agregasi Total Jiwa, KK Unik, Desil Ratio<br>
• Statistik: Min, Max, Average, Sum Nilai<br>
• Ringkasan status Valid, Warning, Critical
</div>'''
    c.append(make_vertex("P3_4", p34_val, proc_style(), 1060, 440, 280, 110))

    p35_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Micro-Table &amp; KPI Cards View Generator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Render 50 baris data per halaman (paginasi)<br>
• Format badge status &amp; visual KPI cards<br>
• Transmisi tampilan aman ke browser
</div>'''
    c.append(make_vertex("P3_5", p35_val, proc_style(), 520, 440, 280, 110))

    c.append(make_edge("e_l2_p3_filter", "Kriteria Filter &amp; Keyword", "E1_L2_P3", "P3_1", purple_flow + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_1_2", "Sanitized Query AST", "P3_1", "P3_2", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_2_d2", "Direct Table Scan", "P3_2", "D2_L2_P3", purple_flow + "exitX=0.3;exitY=0;entryX=0.3;entryY=1;"))
    c.append(make_edge("e_l2_p3_d2_2", "Slices Data Mentah Terpilih", "D2_L2_P3", "P3_2", purple_flow + "exitX=0.7;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("e_l2_p3_2_3", "Filtered Record Chunks", "P3_2", "P3_3", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_2_4", "Metric Aggregation Stream", "P3_2", "P3_4", purple_flow + "exitX=0.9;exitY=1;entryX=0.1;entryY=0;", points=[(952, 380), (1088, 380)]))
    c.append(make_edge("e_l2_p3_3_5", "Masked Tabular Records", "P3_3", "P3_5", purple_flow + "exitX=0.5;exitY=1;entryX=0.9;entryY=0;", points=[(1200, 390), (772, 390)]))
    c.append(make_edge("e_l2_p3_4_5", "Statistik Ringkasan KPI", "P3_4", "P3_5", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_5_e1", "Grid PII-Masked &amp; KPI Cards", "P3_5", "E1_L2_P3", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p3", "TO-BE Level 2 - P3.0 (Analitik & PII Masking)", c, 1800, 1000))

    # =========================================================================
    # TAB 12: TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p4", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 4.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pemantauan Keamanan Real-Time (Biometric Guard Loop &amp; Anti-Shoulder Surfing)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1100, 50))

    c.append(make_vertex("E2_L2_P4", e2_tobe_val, ent_style(), 50, 220, 230, 180))
    c.append(make_vertex("D4_L2_P4", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Profil geometri wajah pemilik sah sesi aktif</span>', store_style("#f59e0b", "#451a03"), 720, 90, 340, 55))
    c.append(make_vertex("E3_L2_P4", e3_tobe_val, ent_style(), 1450, 180, 230, 180))
    c.append(make_vertex("E1_L2_P4", e1_tobe_val, ent_style(), 1450, 420, 230, 240))

    p41_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Polling Frame Loop 0.2s (OpenCV)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Polling periodik non-blocking setiap 200ms<br>
• Beban CPU sangat ringan (&lt; 3%)<br>
• Stream frame RGB ke pipeline evaluasi
</div>'''
    c.append(make_vertex("P4_1", p41_val, proc_style(), 340, 240, 260, 110))

    p42_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Iris Tracking &amp; Face Matcher</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Deteksi orientasi pandangan mata (gaze)<br>
• Komparasi kemiripan terhadap D4 baseline<br>
• Klasifikasi status visual frame aktif
</div>'''
    c.append(make_vertex("P4_2", p42_val, proc_style(), 720, 240, 280, 110))

    p43_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Absence Detector (User Pergi &gt; 4s)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Deteksi 0 wajah pada frame berkala<br>
• Stopwatch delta waktu ketidakhadiran<br>
• Trigger lockout jika durasi &gt; 4.0 detik
</div>'''
    c.append(make_vertex("P4_3", p43_val, proc_style(), 1080, 180, 270, 110))

    p44_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Shoulder Surfing Detector (Asing &gt; 1s)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Deteksi kemunculan wajah kedua / asing<br>
• Evaluasi tatapan mata asing mengintip ke layar<br>
• Trigger lockout instan jika durasi &gt; 1.0 detik
</div>'''
    c.append(make_vertex("P4_4", p44_val, proc_style(), 1080, 320, 270, 110))

    p45_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Security Actuator &amp; Lockdown Controller</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Eksekusi API user32.LockWorkStation (Win+L)<br>
• Tampilkan overlay Red Lock &amp; Blur UI<br>
• Freeze seluruh interaksi data secara instan
</div>'''
    c.append(make_vertex("P4_5", p45_val, proc_style(), 1080, 480, 270, 110))

    c.append(make_edge("e_l2_p4_stream", "Continuous Video Frames", "E2_L2_P4", "P4_1", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_1_2", "Synchronized Frame Buffers", "P4_1", "P4_2", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_d4_2", "Baca Profil Geometri Acuan", "D4_L2_P4", "P4_2", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p4_2_3", "Status: Wajah Hilang (Count 0)", "P4_2", "P4_3", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_2_4", "Status: Wajah Asing (Count &gt; 1)", "P4_2", "P4_4", red_alert + "exitX=1;exitY=0.7;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_3_5", "Timeout Terpenuhi (&gt; 4s)", "P4_3", "P4_5", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;", points=[(1215, 305), (1215, 470)]))
    c.append(make_edge("e_l2_p4_4_5", "Surfer Terdeteksi (&gt; 1s)", "P4_4", "P4_5", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p4_5_e3", "Instruksi Win+L (user32.dll)", "P4_5", "E3_L2_P4", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.6;", points=[(1390, 513), (1390, 288)]))
    c.append(make_edge("e_l2_p4_5_e1", "Red Alert Banner &amp; Blur UI", "P4_5", "E1_L2_P4", red_alert + "exitX=1;exitY=0.7;entryX=0;entryY=0.5;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p4", "TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)", c, 1850, 1000))

    # =========================================================================
    # TAB 13: TO-BE Level 2 - P5.0 (Ekspor Paket ZIP & Pengarsipan)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p5", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 5.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pengelolaan Ekspor, Packaging ZIP &amp; Pengarsipan Terpadu</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_P5", e1_tobe_val, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D2_L2_P5", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset bersih terfilter</span>', store_style("#c084fc", "#3b0764"), 350, 90, 280, 55))
    c.append(make_vertex("D3_L2_P5", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam log anomali kualitas</span>', store_style("#fb7185", "#4c0519"), 710, 90, 290, 55))
    c.append(make_vertex("D5_L2_P5", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#bbf7d0;">Paket ZIP terkompresi permanen</span>', store_style("#34d399", "#064e3b"), 1400, 460, 300, 55))

    p51_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Query Fetcher Data Bersih &amp; Anomali</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Fetch dataset bersih dari dtsen_data (D2)<br>
• Fetch rekaman error dari quality_audit_logs (D3)<br>
• Filter sesuai parameter aktif sesi
</div>'''
    c.append(make_vertex("P5_1", p51_val, proc_style(), 350, 240, 280, 110))

    p52_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">CSV Serializer &amp; Data Sanitizer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tulis berkas data_dtsen.csv (Clean CSV)<br>
• Tulis berkas audit_error.csv (Anomali Log)<br>
• Zero-copy memory buffer streaming
</div>'''
    c.append(make_vertex("P5_2", p52_val, proc_style(), 710, 240, 280, 110))

    p53_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Audit Metadata Manifest Generator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Buat metadata.txt berisi log sesi integritas<br>
• Catat waktu ekspor, operator, filter used<br>
• Generate checksum hash integritas berkas
</div>'''
    c.append(make_vertex("P5_3", p53_val, proc_style(), 1060, 240, 280, 110))

    p54_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">ZIP Packaging &amp; Compression Engine</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Packaging 3 berkas (Clean, Error, Metadata)<br>
• Kompresi algoritma DEFLATE tingkat tinggi<br>
• Simpan arsip resmi ke folder src-export/
</div>'''
    c.append(make_vertex("P5_4", p54_val, proc_style(), 1060, 430, 280, 110))

    p55_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Streaming Download Dispatcher</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Set HTTP Header: application/zip<br>
• Stream berkas langsung ke browser operator<br>
• Log status pengunduhan selesai
</div>'''
    c.append(make_vertex("P5_5", p55_val, proc_style(), 540, 430, 280, 110))

    c.append(make_edge("e_l2_p5_req", "Permintaan Ekspor Paket ZIP", "E1_L2_P5", "P5_1", green_out + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p5_d2_1", "Stream Data Bersih", "D2_L2_P5", "P5_1", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p5_d3_1", "Stream Log Anomali", "D3_L2_P5", "P5_1", red_alert + "exitX=0.5;exitY=1;entryX=0.8;entryY=0;", points=[(855, 190), (574, 190)]))
    c.append(make_edge("e_l2_p5_1_2", "Raw Data Stream Collections", "P5_1", "P5_2", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p5_2_3", "Formatted CSV Objects", "P5_2", "P5_3", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p5_3_4", "Trio Berkas (CSV + Log + Meta)", "P5_3", "P5_4", green_out + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p5_4_d5", "Arsip Paket ZIP Tersimpan", "P5_4", "D5_L2_P5", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p5_4_5", "Stream File Unduhan ZIP", "P5_4", "P5_5", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_p5_5_e1", "Kirim File Unduhan ke Browser", "P5_5", "E1_L2_P5", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p5", "TO-BE Level 2 - P5.0 (Ekspor Paket ZIP)", c, 1800, 1000))

    # =========================================================================
    # TAB 14: TO-BE Level 2 - P6.0 (Terminasi Sistem & Zero-Trace Purge)
    # =========================================================================
    c = []
    c.append(make_vertex("t_l2_p6", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 6.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Terminasi Sesi &amp; Pembersihan Jejak Biometrik (Zero-Trace Purge)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))

    c.append(make_vertex("E1_L2_P6", e1_tobe_val, ent_style(), 50, 160, 230, 200))
    c.append(make_vertex("E3_L2_P6", e3_tobe_val, ent_style(), 50, 420, 230, 200))
    c.append(make_vertex("D4_L2_P6", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot file di RAM/disk yang harus dimusnahkan</span>', store_style("#f59e0b", "#451a03"), 1200, 290, 340, 60))

    p61_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Termination Signal Interceptor</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Tangkap event tutup browser (window.onbeforeunload)<br>
• Tangkap handler python atexit / SIGTERM<br>
• Cegah proses terminasi sebelum cleanup selesai
</div>'''
    c.append(make_vertex("P6_1", p61_val, proc_style(), 360, 290, 270, 110))

    p62_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Resource &amp; Hardware Teardown</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• cv2.VideoCapture.release() (Lepas kamera)<br>
• Tutup koneksi &amp; cursor DuckDB database<br>
• Matikan thread background guard loop
</div>'''
    c.append(make_vertex("P6_2", p62_val, proc_style(), 700, 290, 270, 110))

    p63_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Cryptographic Byte Overwrite (0x00)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Buka sesi_wajah_aktif.jpg mode r+b<br>
• Tuliskan byte 0x00 sebanyak ukuran file penuh<br>
• Flush buffer disk I/O seketika
</div>'''
    c.append(make_vertex("P6_3", p63_val, proc_style(), 1040, 170, 270, 110))

    p64_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">File Unlink &amp; RAM Cache Purge</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• os.unlink(sesi_wajah_aktif.jpg) (Hapus permanen)<br>
• Garbage collection paksa untuk clear RAM memory<br>
• Jaminan Zero-Trace (Tanpa jejak biometrik tersisa)
</div>'''
    c.append(make_vertex("P6_4", p64_val, proc_style(), 1040, 390, 270, 110))

    c.append(make_edge("e_l2_p6_e1_trig", "Perintah Tutup Aplikasi", "E1_L2_P6", "P6_1", red_alert + "exitX=1;exitY=0.5;entryX=0;entryY=0.3;"))
    c.append(make_edge("e_l2_p6_e3_sig", "Sinyal OS SIGTERM / Shutdown", "E3_L2_P6", "P6_1", indigo_sys + "exitX=1;exitY=0.5;entryX=0;entryY=0.7;"))
    c.append(make_edge("e_l2_p6_1_2", "Sinyal Shutdown Terverifikasi", "P6_1", "P6_2", red_alert + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p6_2_3", "Perintah Secure Wipe Berkas", "P6_2", "P6_3", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p6_3_d4", "Timpa Byte 0x00 ke Berkas", "P6_3", "D4_L2_P6", red_alert + "exitX=1;exitY=0.5;entryX=0.3;entryY=0;"))
    c.append(make_edge("e_l2_p6_3_4", "Konfirmasi Byte Terhapus", "P6_3", "P6_4", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p6_4_d4", "Unlink Berkas dari Disk &amp; RAM", "P6_4", "D4_L2_P6", red_alert + "exitX=1;exitY=0.5;entryX=0.3;entryY=1;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p6", "TO-BE Level 2 - P6.0 (Terminasi & Purge)", c, 1700, 900))

    # Wrap up full XML
    full_xml = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
{"".join(diagrams)}
</mxfile>'''
    
    out_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\PADU_DFD.drawio"
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(full_xml)
    print(f"Successfully generated {len(diagrams)} diagrams (including AS-IS Level 2 and TO-BE Level 2) to {out_path}!")

if __name__ == "__main__":
    generate_all()
