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

# Import AS-IS
from generate_both_variants import get_asis_diagrams, e1_asis_val, e2_tobe_val, e3_tobe_val

# Updated Operator Entity with 2 modules & 7 metrics
e1_tobe_standard = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengunggah berkas mentah DTSEN<br>
• Memilih Modul: Data Mikro vs Statistik Wilayah<br>
• Menjalankan filter dinamis &amp; pencarian NIK/KK<br>
• Meninjau data mikro terpadu (Transliterasi Label FK)<br>
• Menganalisis agregasi wilayah (7 Metrik Statistik)<br>
• Mengunduh paket ZIP arsip olahan &amp; log audit<br>
• Menerima notifikasi peringatan layar
</div>'''

def get_standard_tobe_diagrams():
    diagrams = []

    # 1. TO-BE Level 0 (Standard - No SuperAdmin)
    c = []
    c.append(make_vertex("t_tobe_0", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — TO-BE (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis Data Terpadu &amp; Pengamanan Biometrik (PADU v1.02 — TO-BE)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 850, 50))
    
    p0_tobe_val = '''<div style="font-size:14px;color:#000000;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#000000;line-height:1.3;">SISTEM ANALISIS DATA DTSEN &amp;<br>PENGAMANAN BIOMETRIK<br>(PADU v1.02 — TO-BE)</div>
<hr style="border:1px solid #007ACC;margin:12px 0;">
<div style="font-size:11px;color:#000000;line-height:1.5;">
• Hybrid Dual-Engine: Laravel 12 + Python DuckDB<br>
• Standalone 100% Offline Processing (13M+ Baris)<br>
• Dual-Module UI: Data Mikro (Transliterasi FK) &amp; Statistik (7 Metrik)<br>
• Dynamic Multi-Column Filtering &amp; PII Masking<br>
• Zero-Trace Biometric Guard &amp; user32 Lock
</div>'''
    c.append(make_vertex("P0_TOBE", p0_tobe_val, f"rounded=1;arcSize=20;whiteSpace=wrap;fillColor={PROC_FILL};strokeColor={PROC_BORDER};strokeWidth=3;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;", 640, 240, 380, 480))
    c.append(make_vertex("E1_TOBE_0", e1_tobe_standard, ent_style(), 50, 240, 270, 480))
    c.append(make_vertex("E2_TOBE_0", e2_tobe_val, ent_style(), 1330, 240, 250, 180))
    c.append(make_vertex("E3_TOBE_0", e3_tobe_val, ent_style(), 1330, 530, 250, 190))

    c.append(make_edge("e_tb_0_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.08;entryX=0;entryY=0.08;"))
    c.append(make_edge("e_tb_0_2", "2. Pilihan Modul (Mikro / Statistik) &amp; Filter", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.22;entryX=0;entryY=0.22;"))
    c.append(make_edge("e_tb_0_2b", "3. Konfigurasi Variabel &amp; 7 Metrik Agregasi Wilayah", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.36;entryX=0;entryY=0.36;"))
    c.append(make_edge("e_tb_0_3", "4. Perintah Ekspor Paket ZIP &amp; Terminasi Sesi", "E1_TOBE_0", "P0_TOBE", blue_in + "exitX=1;exitY=0.50;entryX=0;entryY=0.50;"))
    c.append(make_edge("e_tb_0_4", "5. Data Mikro Terpadu (PII Masking &amp; Transliterasi FK)", "P0_TOBE", "E1_TOBE_0", green_out + "exitX=0;exitY=0.64;entryX=1;entryY=0.64;"))
    c.append(make_edge("e_tb_0_4b", "6. Rekap Statistik Wilayah (SUM, AVG, COUNT, MIN, MAX, MED, MOD)", "P0_TOBE", "E1_TOBE_0", green_out + "exitX=0;exitY=0.76;entryX=1;entryY=0.76;"))
    c.append(make_edge("e_tb_0_5", "7. Paket ZIP Olahan &amp; Log Audit Error CSV", "P0_TOBE", "E1_TOBE_0", green_out + "exitX=0;exitY=0.88;entryX=1;entryY=0.88;"))
    c.append(make_edge("e_tb_0_6", "8. Alert Peringatan Layar (Red Lock &amp; Blur UI)", "P0_TOBE", "E1_TOBE_0", red_alert + "exitX=0;exitY=0.97;entryX=1;entryY=0.97;"))

    c.append(make_edge("e_tb_0_cam", "Stream Frame Video (Headless OpenCV)", "E2_TOBE_0", "P0_TOBE", amber_cam + "exitX=0;exitY=0.3;entryX=1;entryY=0.15;"))
    c.append(make_edge("e_tb_0_cam_sig", "Sinyal Inisialisasi &amp; Pengaturan Sensor", "P0_TOBE", "E2_TOBE_0", amber_cam + "exitX=1;exitY=0.25;entryX=0;entryY=0.7;strokeColor=#d97706;dashed=1;"))
    c.append(make_edge("e_tb_0_win_lock", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0_TOBE", "E3_TOBE_0", red_alert + "exitX=1;exitY=0.75;entryX=0;entryY=0.3;"))
    c.append(make_edge("e_tb_0_win_sig", "Sinyal Shutdown / SIGTERM / atexit", "E3_TOBE_0", "P0_TOBE", indigo_sys + "exitX=0;exitY=0.7;entryX=1;entryY=0.85;"))
    diagrams.append(build_diagram("dfd_tobe_level_0", "TO-BE Level 0 (Diagram Konteks)", c, 1700, 1000))

    # 2. TO-BE Level 1 (Standard - No SuperAdmin)
    c = []
    c.append(make_vertex("t_tobe_1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — TO-BE (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 6 Sub-Proses Fungsional Berurutan Secara Sekuensial (1.0 di Kiri sd 6.0 di Kanan), 5 Data Store, dan 3 Entitas Eksternal</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 800, 30, 1600, 50))
    c.append(make_vertex("E1_TOBE_1", e1_tobe_standard, ent_style(), 50, 220, 260, 600))
    c.append(make_vertex("E2_TOBE_1", e2_tobe_val, ent_style(), 350, 90, 260, 150))
    c.append(make_vertex("E3_TOBE_1", e3_tobe_val, ent_style(), 2420, 90, 260, 150))

    c.append(make_vertex("D1_TOBE", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 700, 120, 280, 50))
    c.append(make_vertex("D2_TOBE", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 950, 640, 320, 55))
    c.append(make_vertex("D3_TOBE", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 1450, 640, 320, 55))
    c.append(make_vertex("D4_TOBE", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot wajah pemilik sah sesi aktif (Zero-Trace)</span>', store_style("#f59e0b", "#451a03"), 920, 800, 340, 55))
    c.append(make_vertex("D5_TOBE", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#a7f3d0;">Paket ZIP kompresi Clean CSV + Error CSV + Metadata</span>', store_style("#34d399", "#064e3b"), 1970, 640, 320, 55))

    p1_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Inisialisasi &amp; Verifikasi Biometrik Awal</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• cv2 Headless Capture (adaptasi 2s)<br>
• MediaPipe Evaluasi Jumlah Wajah<br>
• Reject jika 0 / &gt;1 wajah; simpan jika 1
</div>'''
    c.append(make_vertex("P1_TOBE", p1_tb, proc_style(), 350, 400, 260, 130))

    p2_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Ingesti, Normalisasi &amp; Audit Kualitas Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Auto-Scan &amp; PyArrow Ingestion Engine<br>
• Mapping sinonim 48 variabel resmi BPS<br>
• Quality Check Rule: Valid / Warning / Critical
</div>'''
    c.append(make_vertex("P2_TOBE", p2_tb, proc_style(), 720, 400, 270, 130))

    p3_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemrosesan Analitik (Data Mikro &amp; Statistik)</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• DuckDB Vector Slicing (&lt; 0.05s) &amp; PII Masking<br>
• Tab 1: Data Mikro Terpadu (Transliterasi Label FK)<br>
• Tab 2: Agregasi Statistik Wilayah (7 Metrik)
</div>'''
    c.append(make_vertex("P3_TOBE", p3_tb, proc_style(), 1100, 400, 310, 130))

    p4_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemantauan Keamanan Real-Time (Guard Loop)</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Face Mesh &amp; Iris Eye-Tracking (Loop 0.2s, CPU &lt; 3%)<br>
• Deteksi User Pergi &gt; 4 Detik<br>
• Deteksi Shoulder Surfing (Mata Asing &gt; 1 Detik)
</div>'''
    c.append(make_vertex("P4_TOBE", p4_tb, proc_style(), 1530, 400, 280, 130))

    p5_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pengelolaan Ekspor &amp; Pengarsipan Data</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• CsvExportService Packaging Engine<br>
• Kompresi ZIP: Clean CSV + Error CSV + Metadata<br>
• Stream Berkas Unduhan ke Operator
</div>'''
    c.append(make_vertex("P5_TOBE", p5_tb, proc_style(), 1930, 400, 270, 130))

    p6_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Terminasi Sistem &amp; Zero-Trace Purge</div>
<hr style="border:1px solid #007ACC;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#000000;">
• Tangkap Sinyal atexit / SIGTERM / Close Window<br>
• Secure Wipe 0x00 sesi_wajah_aktif.jpg<br>
• Unlink berkas &amp; Flush RAM Cache (Zero-Trace)
</div>'''
    c.append(make_vertex("P6_TOBE", p6_tb, proc_style(), 2320, 400, 270, 130))

    c.append(make_edge("f1_cam_p1", "Frame Snapshot Wajah Awal", "E2_TOBE_1", "P1_TOBE", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_p1_d4", "Simpan Wajah Pemilik Sah (1 Wajah)", "P1_TOBE", "D4_TOBE", amber_cam + "exitX=0.5;exitY=1;entryX=0;entryY=0.5;", points=[(480, 827)]))
    c.append(make_edge("f1_p1_e1", "Status Sesi / Auto-Reject Silent", "P1_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.3;entryX=1;entryY=0.25;"))
    c.append(make_edge("f1_e1_d1", "Salin File Mentah CSV/XLSX", "E1_TOBE_1", "D1_TOBE", blue_in + "exitX=1;exitY=0.1;entryX=0;entryY=0.5;", points=[(330, 280), (330, 145)]))
    c.append(make_edge("f1_d1_p2", "Baca Berkas Mentah", "D1_TOBE", "P2_TOBE", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_e1_p2", "Trigger Impor (POST /import-local)", "E1_TOBE_1", "P2_TOBE", blue_in + "exitX=1;exitY=0.35;entryX=0;entryY=0.2;", points=[(330, 430), (700, 430)]))
    c.append(make_edge("f1_p2_e1", "Ringkasan Ingesti &amp; Skor Kualitas", "P2_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.8;entryX=1;entryY=0.45;", points=[(700, 500), (340, 500)]))
    c.append(make_edge("f1_p2_d2", "Batch Ingest Data Bersih 48 Kolom", "P2_TOBE", "D2_TOBE", green_out + "exitX=0.5;exitY=1;entryX=0.3;entryY=0;"))
    c.append(make_edge("f1_p2_d3", "Rekam Anomali (Error Log)", "P2_TOBE", "D3_TOBE", red_alert + "exitX=0.8;exitY=1;entryX=0.2;entryY=0;", points=[(936, 580), (1514, 580)]))

    c.append(make_edge("f1_e1_p3", "Pilihan Modul (Mikro/Statistik) &amp; Filter", "E1_TOBE_1", "P3_TOBE", purple_flow + "exitX=1;exitY=0.55;entryX=0;entryY=0.3;", points=[(340, 550), (1080, 550)]))
    c.append(make_edge("f1_p3_e1_mikro", "Data Mikro Terpadu (PII-Masked &amp; Transliterasi FK)", "P3_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.7;entryX=1;entryY=0.62;", points=[(1080, 590), (340, 590)]))
    c.append(make_edge("f1_p3_e1_stat", "Rekap Statistik Agregasi Wilayah (7 Metrik)", "P3_TOBE", "E1_TOBE_1", green_out + "exitX=0;exitY=0.9;entryX=1;entryY=0.72;", points=[(1080, 620), (340, 620)]))
    c.append(make_edge("f1_p3_d2", "Query Slicing Kolumnar (< 0.05s) &amp; Agregasi", "P3_TOBE", "D2_TOBE", purple_flow + "exitX=0.3;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("f1_d2_p3", "Dataset Terfilter", "D2_TOBE", "P3_TOBE", purple_flow + "exitX=0.8;exitY=0;entryX=0.5;entryY=1;"))

    c.append(make_edge("f1_cam_p4", "Continuous Video Stream (Loop 0.2s)", "E2_TOBE_1", "P4_TOBE", amber_cam + "exitX=1;exitY=0.5;entryX=0.5;entryY=0;", points=[(1670, 165)]))
    c.append(make_edge("f1_d4_p4", "Baca Profil Wajah Sah Referensi", "D4_TOBE", "P4_TOBE", amber_cam + "exitX=1;exitY=0.5;entryX=0.1;entryY=1;", points=[(1558, 827)]))
    c.append(make_edge("f1_p4_e3", "Instruksi Kunci Layar (user32.LockWorkStation)", "P4_TOBE", "E3_TOBE_1", red_alert + "exitX=0.7;exitY=0;entryX=0.2;entryY=1;", points=[(1726, 320), (2472, 320)]))
    c.append(make_edge("f1_p4_e1", "Red Lock Alert &amp; Blur UI Event", "P4_TOBE", "E1_TOBE_1", red_alert + "exitX=0;exitY=0.9;entryX=1;entryY=0.82;", points=[(1500, 670), (340, 670)]))

    c.append(make_edge("f1_e1_p5", "Permintaan Ekspor Arsip ZIP", "E1_TOBE_1", "P5_TOBE", blue_in + "exitX=1;exitY=0.9;entryX=0;entryY=0.8;", points=[(340, 760), (1900, 760)]))
    c.append(make_edge("f1_p5_d2", "Ambil Data Bersih Terpilih", "D2_TOBE", "P5_TOBE", green_out + "exitX=1;exitY=0.5;entryX=0.1;entryY=1;", points=[(1957, 667)]))
    c.append(make_edge("f1_p5_d3", "Ambil Rekam Error Audit", "D3_TOBE", "P5_TOBE", red_alert + "exitX=1;exitY=0.5;entryX=0.4;entryY=1;", points=[(2038, 667)]))
    c.append(make_edge("f1_p5_d5", "Simpan Paket ZIP (src-export/)", "P5_TOBE", "D5_TOBE", green_out + "exitX=0.7;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_p5_e1", "Stream Unduhan Berkas ZIP", "P5_TOBE", "E1_TOBE_1", green_out + "exitX=0.5;exitY=1;entryX=1;entryY=0.92;", points=[(2065, 790), (340, 790)]))

    c.append(make_edge("f1_e3_p6", "Sinyal Shutdown OS / SIGTERM / atexit", "E3_TOBE_1", "P6_TOBE", indigo_sys + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f1_e1_p6", "Perintah Tutup Aplikasi / Selesai", "E1_TOBE_1", "P6_TOBE", red_alert + "exitX=1;exitY=0.98;entryX=0;entryY=0.85;", points=[(340, 840), (2300, 840)]))
    c.append(make_edge("f1_p6_d4", "Secure Overwrite 0x00 &amp; Hapus File", "P6_TOBE", "D4_TOBE", red_alert + "exitX=0.5;exitY=1;entryX=0.8;entryY=1;", points=[(2455, 870), (1192, 870)]))
    diagrams.append(build_diagram("dfd_tobe_level_1", "TO-BE Level 1 (Dekomposisi Sistem)", c, 3200, 1300))

    # 3. TO-BE Level 2 - P1.0 (Preserved)
    from build_dfd_all_levels import p11_val, p12_val, p13_val, p14_val, p15_val
    c = []
    c.append(make_vertex("t_l2_p1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 1.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Inisialisasi &amp; Verifikasi Biometrik Awal (Headless OpenCV + MediaPipe)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 950, 50))
    c.append(make_vertex("E2_L2_P1", e2_tobe_val, ent_style(), 60, 220, 230, 180))
    c.append(make_vertex("E1_L2_P1", e1_tobe_standard, ent_style(), 1480, 220, 230, 420))
    c.append(make_vertex("D4_L2_P1", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot matriks wajah tunggal pemilik sah</span>', store_style("#f59e0b", "#451a03"), 720, 580, 360, 60))
    c.append(make_vertex("P1_1", p11_val, proc_style(), 350, 220, 250, 120))
    c.append(make_vertex("P1_2", p12_val, proc_style(), 680, 220, 250, 120))
    c.append(make_vertex("P1_3", p13_val, proc_style(), 1010, 220, 260, 120))
    c.append(make_vertex("P1_4", p14_val, proc_style(), 840, 420, 260, 110))
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

    # 4. TO-BE Level 2 - P2.0 (Preserved)
    from build_dfd_all_levels import p21_val, p22_val, p23_val, p24_val, p25_val, p26_val
    c = []
    c.append(make_vertex("t_l2_p2", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 2.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Ingesti Vektor, Pemetaan Sinonim BPS &amp; Evaluasi Kualitas Data DuckDB</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))
    c.append(make_vertex("E1_L2_P2", e1_tobe_standard, ent_style(), 50, 220, 230, 480))
    c.append(make_vertex("D1_L2_P2", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 340, 90, 280, 55))
    c.append(make_vertex("D2_L2_P2", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 kolom bersih siap query</span>', store_style("#c084fc", "#3b0764"), 1050, 680, 320, 55))
    c.append(make_vertex("D3_L2_P2", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali Critical &amp; Warning DuckDB</span>', store_style("#fb7185", "#4c0519"), 1420, 680, 320, 55))
    c.append(make_vertex("P2_1", p21_val, proc_style(), 340, 240, 260, 110))
    c.append(make_vertex("P2_2", p22_val, proc_style(), 670, 240, 270, 110))
    c.append(make_vertex("P2_3", p23_val, proc_style(), 1010, 240, 270, 110))
    c.append(make_vertex("P2_4", p24_val, proc_style(), 1350, 240, 270, 110))
    c.append(make_vertex("P2_5", p25_val, proc_style(), 1200, 460, 280, 110))
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

    # 5. TO-BE Level 2 - P3.0 (ENHANCED WITH DATA MIKRO, TRANSLITERASI FK & AGREGASI WILAYAH 7 METRIK)
    c = []
    c.append(make_vertex("t_l2_p3_enh", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 3.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pemrosesan Analitik: Data Mikro (Transliterasi FK), PII Masking &amp; Agregasi Wilayah (7 Metrik)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 500, 30, 1200, 50))
    c.append(make_vertex("E1_L2_P3", e1_tobe_standard, ent_style(), 50, 240, 250, 650))
    c.append(make_vertex("D2_L2_P3", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 750, 90, 340, 55))
    c.append(make_vertex("D_REF_KAMUS", '<b style="font-size:12px;color:#38bdf8;">REF</b> | <b>Kamus Referensi Kode (referensi_kamus_data)</b><br><span style="font-size:10px;color:#bae6fd;">Pemetaan Kode Numerik ke Label Deskriptif</span>', store_style("#38bdf8", "#0369a1"), 1200, 90, 340, 55))

    p31_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Request Query Parser &amp; Modul Dispatcher</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Parse pilihan modul: Data Mikro / Statistik Wilayah<br>
• Parse filter: NIK, KK, Desil, Rentang Usia/Gaji<br>
• Sanitasi parameter cegah SQL Injection
</div>'''
    c.append(make_vertex("P3_1", p31_val, proc_style(), 360, 240, 280, 120))

    p32_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Memory-Mapped Vector Slicing Engine</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Eksekusi query teroptimasi DuckDB zero-copy<br>
• Kecepatan respons &lt; 0.05 detik pada 13M+ baris<br>
• Pisahkan aliran: Record Individual vs Aggregation
</div>'''
    c.append(make_vertex("P3_2", p32_val, proc_style(), 750, 240, 300, 120))

    p33_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic PII Masking Transformer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• NIK: 3201************ (4 digit awal)<br>
• Nama: B*** S****** (huruf pertama tiap kata)<br>
• Gaji: Rp 8.xxx.xxx | Alamat: sensor parsial
</div>'''
    c.append(make_vertex("P3_3", p33_val, proc_style(), 1160, 240, 280, 120))

    p34_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Transliterasi FK &amp; Metadata Label Mapper</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Ubah kode numerik menjadi label deskriptif:<br>
  - Air Minum: '1' &rarr; 'Kemasan Bermerk', '2' &rarr; 'Sumur'<br>
  - Lantai: '01' &rarr; 'Marmer', '02' &rarr; 'Keramik'<br>
  - Gender: '1' &rarr; 'Laki-Laki', '2' &rarr; 'Perempuan'<br>
• Hasilkan Master Record Mikro Terpadu
</div>'''
    c.append(make_vertex("P3_4", p34_val, proc_style(), 1540, 240, 310, 135))

    p35_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic Regional Aggregator (7 Metrik)</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Agregasi multi-level: Kab/Kota &rarr; Kec &rarr; Desa/Kel<br>
• 7 Rumus Metrik Statistik Terpilih:<br>
  - COUNT (Jiwa/KK), SUM (Finansial), AVG (Rata-rata)<br>
  - MIN, MAX, MEDIAN, MODUS Distribusi<br>
• Hasilkan Rangkuman Tabel Statistik Wilayah
</div>'''
    c.append(make_vertex("P3_5", p35_val, proc_style(), 1160, 480, 320, 135))

    p36_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.6</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dual-Module View &amp; Navigation Generator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Render Tab 1: Grid Data Mikro Terpadu (Transliterasi FK)<br>
• Render Tab 2: Tabel Agregasi Statistik Wilayah Dinamis<br>
• Transmisi tampilan aman ke browser Operator
</div>'''
    c.append(make_vertex("P3_6", p36_val, proc_style(), 650, 480, 330, 120))

    c.append(make_edge("e_l2_p3_filter", "Kriteria Filter, Modul &amp; Metrik", "E1_L2_P3", "P3_1", purple_flow + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_1_2", "Sanitized Query AST &amp; Target Modul", "P3_1", "P3_2", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_2_d2", "Direct Table Scan", "P3_2", "D2_L2_P3", purple_flow + "exitX=0.3;exitY=0;entryX=0.3;entryY=1;"))
    c.append(make_edge("e_l2_p3_d2_2", "Slices Data Mentah Terpilih", "D2_L2_P3", "P3_2", purple_flow + "exitX=0.7;exitY=1;entryX=0.7;entryY=0;"))

    c.append(make_edge("e_l2_p3_2_3", "Filtered Micro Records", "P3_2", "P3_3", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_3_4", "Masked Individual Records", "P3_3", "P3_4", purple_flow + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_dref_4", "Tabel Referensi Kode &amp; Label", "D_REF_KAMUS", "P3_4", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))

    c.append(make_edge("e_l2_p3_2_5", "Raw Numeric &amp; Region Vector Stream", "P3_2", "P3_5", purple_flow + "exitX=0.9;exitY=1;entryX=0.1;entryY=0;", points=[(1020, 420), (1192, 420)]))
    c.append(make_edge("e_l2_p3_4_6", "Master View Data Mikro (Transliterasi FK)", "P3_4", "P3_6", purple_flow + "exitX=0.5;exitY=1;entryX=0.9;entryY=0;", points=[(1695, 440), (947, 440)]))
    c.append(make_edge("e_l2_p3_5_6", "Tabel Rekap Agregasi Wilayah (7 Metrik)", "P3_5", "P3_6", purple_flow + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_l2_p3_6_e1", "Render Tab Data Mikro &amp; Statistik Wilayah", "P3_6", "E1_L2_P3", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.65;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p3", "TO-BE Level 2 - P3.0 (Analitik & PII Masking)", c, 2100, 1100))

    # 6. TO-BE Level 2 - P4.0 (Preserved)
    from build_dfd_all_levels import p41_val, p42_val, p43_val, p44_val, p45_val
    c = []
    c.append(make_vertex("t_l2_p4", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 4.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pemantauan Keamanan Real-Time (Biometric Guard Loop &amp; Anti-Shoulder Surfing)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1100, 50))
    c.append(make_vertex("E2_L2_P4", e2_tobe_val, ent_style(), 50, 220, 230, 180))
    c.append(make_vertex("D4_L2_P4", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Profil geometri wajah pemilik sah sesi aktif</span>', store_style("#f59e0b", "#451a03"), 720, 90, 340, 55))
    c.append(make_vertex("E3_L2_P4", e3_tobe_val, ent_style(), 1450, 180, 230, 180))
    c.append(make_vertex("E1_L2_P4", e1_tobe_standard, ent_style(), 1450, 420, 230, 240))
    c.append(make_vertex("P4_1", p41_val, proc_style(), 340, 240, 270, 110))
    c.append(make_vertex("P4_2", p42_val, proc_style(), 680, 240, 270, 110))
    c.append(make_vertex("P4_3", p43_val, proc_style(), 1050, 180, 270, 110))
    c.append(make_vertex("P4_4", p44_val, proc_style(), 1050, 340, 270, 110))
    c.append(make_vertex("P4_5", p45_val, proc_style(), 1050, 500, 270, 110))
    c.append(make_edge("e_l2_p4_cam", "Continuous Video Frames", "E2_L2_P4", "P4_1", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_1_2", "Synchronized Frame Buffers", "P4_1", "P4_2", amber_cam + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_d4_2", "Baca Profil Geometri Acuan", "D4_L2_P4", "P4_2", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p4_2_3", "Status: Wajah Hilang (Count 0)", "P4_2", "P4_3", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_2_4", "Status: Wajah Asing (Count &gt; 1)", "P4_2", "P4_4", red_alert + "exitX=1;exitY=0.7;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p4_3_5", "Timeout Terpenuhi (&gt; 4s)", "P4_3", "P4_5", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p4_4_5", "Surfer Terdeteksi (&gt; 1s)", "P4_4", "P4_5", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p4_5_e3", "Instruksi Win+L (user32.dll)", "P4_5", "E3_L2_P4", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;", points=[(1380, 533), (1380, 270)]))
    c.append(make_edge("e_l2_p4_5_e1", "Red Alert Banner &amp; Blur UI", "P4_5", "E1_L2_P4", red_alert + "exitX=1;exitY=0.7;entryX=0;entryY=0.5;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p4", "TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)", c, 1800, 1000))

    # 7. TO-BE Level 2 - P5.0 (Preserved)
    from build_dfd_all_levels import p51_val, p52_val, p53_val, p54_val, p55_val
    c = []
    c.append(make_vertex("t_l2_p5", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 5.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Pengelolaan Ekspor, Packaging ZIP &amp; Pengarsipan Terpadu</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))
    c.append(make_vertex("E1_L2_P5", e1_tobe_standard, ent_style(), 50, 220, 230, 420))
    c.append(make_vertex("D2_L2_P5", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset bersih terfilter</span>', store_style("#c084fc", "#3b0764"), 350, 90, 280, 55))
    c.append(make_vertex("D3_L2_P5", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam log anomali kualitas</span>', store_style("#fb7185", "#4c0519"), 710, 90, 290, 55))
    c.append(make_vertex("D5_L2_P5", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#a7f3d0;">Paket ZIP terkompresi permanen</span>', store_style("#34d399", "#064e3b"), 1400, 520, 290, 55))
    c.append(make_vertex("P5_1", p51_val, proc_style(), 350, 240, 270, 110))
    c.append(make_vertex("P5_2", p52_val, proc_style(), 700, 240, 270, 110))
    c.append(make_vertex("P5_3", p53_val, proc_style(), 1050, 240, 270, 110))
    c.append(make_vertex("P5_4", p54_val, proc_style(), 1050, 440, 270, 110))
    c.append(make_vertex("P5_5", p55_val, proc_style(), 550, 440, 270, 110))
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

    # 8. TO-BE Level 2 - P6.0 (Preserved)
    from build_dfd_all_levels import p61_val, p62_val, p63_val, p64_val
    c = []
    c.append(make_vertex("t_l2_p6", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 6.0 (TO-BE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Terminasi Sesi &amp; Pembersihan Jejak Biometrik (Zero-Trace Purge)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1050, 50))
    c.append(make_vertex("E1_L2_P6", e1_tobe_standard, ent_style(), 50, 160, 230, 200))
    c.append(make_vertex("E3_L2_P6", e3_tobe_val, ent_style(), 50, 420, 230, 200))
    c.append(make_vertex("D4_L2_P6", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot file di RAM/disk yang harus dimusnahkan</span>', store_style("#f59e0b", "#451a03"), 1200, 290, 340, 60))
    c.append(make_vertex("P6_1", p61_val, proc_style(), 360, 290, 270, 110))
    c.append(make_vertex("P6_2", p62_val, proc_style(), 700, 290, 270, 110))
    c.append(make_vertex("P6_3", p63_val, proc_style(), 1040, 170, 270, 110))
    c.append(make_vertex("P6_4", p64_val, proc_style(), 1040, 390, 270, 110))
    c.append(make_edge("e_l2_p6_e1_trig", "Perintah Tutup Aplikasi", "E1_L2_P6", "P6_1", red_alert + "exitX=1;exitY=0.5;entryX=0;entryY=0.3;"))
    c.append(make_edge("e_l2_p6_e3_sig", "Sinyal OS SIGTERM / Shutdown", "E3_L2_P6", "P6_1", indigo_sys + "exitX=1;exitY=0.5;entryX=0;entryY=0.7;"))
    c.append(make_edge("e_l2_p6_1_2", "Sinyal Shutdown Terverifikasi", "P6_1", "P6_2", red_alert + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p6_2_3", "Perintah Secure Wipe Berkas", "P6_2", "P6_3", red_alert + "exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_l2_p6_3_d4", "Timpa Byte 0x00 ke Berkas", "P6_3", "D4_L2_P6", red_alert + "exitX=1;exitY=0.5;entryX=0.3;entryY=0;"))
    c.append(make_edge("e_l2_p6_3_4", "Konfirmasi Byte Terhapus", "P6_3", "P6_4", red_alert + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("e_l2_p6_4_d4", "Unlink Berkas dari Disk &amp; RAM", "P6_4", "D4_L2_P6", red_alert + "exitX=1;exitY=0.5;entryX=0.3;entryY=1;"))
    diagrams.append(build_diagram("dfd_tobe_level_2_p6", "TO-BE Level 2 - P6.0 (Terminasi & Purge)", c, 1700, 900))

    # 9. ERD LOGIKAL (DENGAN TABEL REFERENSI KAMUS DATA UNTUK TRANSLITERASI FK)
    c = []
    c.append(make_vertex("title_erd_1", '<div style="font-size:22px;font-weight:bold;color:#0f172a;letter-spacing:-0.5px;">ENTITY RELATIONSHIP DIAGRAM (ERD) — PADU v1.02</div><div style="font-size:13px;color:#475569;margin-top:5px;">Sistem Pengolah &amp; Analisis Data Terpadu (DTSEN 2026 Engine) — Skema Relasi Database Relasional (OLTP)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 600, 30, 1200, 60))
    c.append(make_vertex("legend_box", '<div style="font-size:12px;font-weight:bold;color:#1e293b;border-bottom:1px solid #cbd5e1;padding-bottom:4px;margin-bottom:6px;">KETERANGAN NOTASI &amp; SIMBOL ERD</div><div style="font-size:11px;line-height:1.6;color:#334155;text-align:left;"><span style="background-color:#fee2e2;color:#991b1b;font-weight:bold;padding:1px 5px;border-radius:3px;">PK</span> = Primary Key (Kunci Utama)<br><span style="background-color:#fef3c7;color:#92400e;font-weight:bold;padding:1px 5px;border-radius:3px;">FK</span> = Foreign Key (Kunci Tamu)<br><span style="background-color:#e0e7ff;color:#3730a3;font-weight:bold;padding:1px 5px;border-radius:3px;">UK</span> = Unique Key (Indeks Unik)<br><span style="background-color:#dcfce7;color:#166534;font-weight:bold;padding:1px 5px;border-radius:3px;">IDX</span> = Indexed Column (Optimasi Query)<br><b>Relasi</b>: 1 : N (One-to-Many) dengan <i>ON DELETE CASCADE</i></div>', "rounded=1;arcSize=10;fillColor=#f8fafc;strokeColor=#94a3b8;strokeWidth=1.5;shadow=1;align=center;verticalAlign=top;spacingTop=8;spacingLeft=10;spacingRight=10;", 1910, 440, 320, 240))
    
    # Table users
    from generate_erd_drawio import users_val, demographics_val, keluargas_val, individus_val
    c.append(make_vertex("tbl_users", users_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#2563eb;strokeWidth=2;shadow=1;verticalAlign=top;", 1930, 780, 300, 460))
    c.append(make_vertex("tbl_demographics", demographics_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0284c7;strokeWidth=2;shadow=1;verticalAlign=top;", 1580, 380, 270, 630))
    c.append(make_vertex("tbl_keluargas", keluargas_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=4;fillColor=#ffffff;strokeColor=#059669;strokeWidth=2.5;shadow=1;verticalAlign=top;", 40, 40, 460, 1520))
    c.append(make_vertex("tbl_individus", individus_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=4;fillColor=#ffffff;strokeColor=#4338ca;strokeWidth=2.5;shadow=1;verticalAlign=top;", 960, 110, 560, 1420))
    
    # NEW: Table referensi_kamus_data (Untuk Transliterasi FK)
    ref_kamus_val = '''<div style="background-color:#0891b2;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  referensi_kamus_data (Kamus Transliterasi FK)
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[PK]</b> id : <i>BIGINT AUTO_INCREMENT</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[IDX]</b> nama_tabel : <i>VARCHAR(50) ('keluargas', 'individus')</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;"><b>[IDX]</b> nama_kolom : <i>VARCHAR(100) (nama variabel)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">kode_nilai : <i>VARCHAR(20) (misal '1', '2', '01')</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;color:#0e7490;font-weight:bold;">label_transliterasi : <i>VARCHAR(255) (Deskripsi)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">kategori_sumber : <i>VARCHAR(100) ('Bappenas 2026', 'BPS')</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">urutan : <i>INTEGER DEFAULT 0</i></div>
  <div style="padding:2px 0;">created_at / updated_at : <i>TIMESTAMP</i></div>
</div>'''
    c.append(make_vertex("tbl_ref_kamus", ref_kamus_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0891b2;strokeWidth=2;shadow=1;verticalAlign=top;", 1560, 1050, 320, 320))

    c.append(make_edge("rel_keluarga_individu", '<div style="background-color:#ffffff;border:1px solid #4338ca;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:bold;color:#1e1b4b;box-shadow:0 1px 3px rgba(0,0,0,0.1);">1 : N (One-to-Many)<br><span style="font-size:10px;font-weight:normal;color:#6b21a8;">FK: nomor_kartu_keluarga</span><br><span style="font-size:9px;color:#dc2626;">ON DELETE CASCADE</span></div>', "tbl_keluargas", "tbl_individus", "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;strokeColor=#4338ca;strokeWidth=3;startArrow=ERone;startFill=0;endArrow=ERmany;endFill=0;labelBackgroundColor=none;"))
    
    # Edge referensi to individu/keluarga
    c.append(make_edge("rel_ref_individu", '<div style="background-color:#ffffff;border:1px solid #0891b2;padding:3px 6px;border-radius:4px;font-size:10px;color:#0e7490;">Transliterasi Kode FK</div>', "tbl_ref_kamus", "tbl_individus", "edgeStyle=orthogonalEdgeStyle;rounded=1;strokeColor=#0891b2;strokeWidth=2;dashed=1;exitX=0;exitY=0.3;entryX=1;entryY=0.7;"))

    rel_desc_val = '''<div style="font-size:12px;font-weight:bold;color:#0f766e;margin-bottom:4px;">INTEGRITAS REFERENSIAL &amp; KAMUS TRANSLITERASI</div>
<div style="font-size:11px;line-height:1.5;color:#134e4a;text-align:left;">
• Setiap 1 Kartu Keluarga (<b>keluargas</b>) menjadi induk dari 1 hingga N Anggota Keluarga (<b>individus</b>).<br>
• Relasi diikat via <code>nomor_kartu_keluarga</code> (16 digit angka unik di tabel <i>keluargas</i>).<br>
• Jika berkas/kartu keluarga dihapus, seluruh anggota keluarga terkait dihapus otomatis (<i>Cascade</i>).<br>
• Tabel <b>referensi_kamus_data</b> memetakan seluruh kode numerik (jenis lantai, sanitasi, air minum, disabilitas, dsb.) ke label teks resmi (Transliterasi Foreign Key).
</div>'''
    c.append(make_vertex("rel_desc_box", rel_desc_val, "rounded=1;arcSize=10;fillColor=#f0fdfa;strokeColor=#0d9488;strokeWidth=1.5;shadow=1;align=center;verticalAlign=top;spacingTop=6;spacingLeft=10;spacingRight=10;", 1540, 130, 820, 210))
    diagrams.append(build_diagram("erd_logikal_dtsen", "ERD Logikal (DTSEN 2026)", c, 3300, 1600))

    # 10. Arsitektur Dual-Storage (OLTP + OLAP)
    from generate_erd_drawio import title_cell2, src_val, disp_val, oltp_val, olap_val, ui_val
    c = []
    c.append(make_vertex("title_erd_2", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">ARSITEKTUR PENYIMPANAN DATA DUAL-ENGINE (PADU v1.02)</div><div style="font-size:13px;color:#475569;margin-top:4px;">Kolaborasi Database Relasional Transaksional (SQLite / MySQL) dan Engine Analitik Tervektorisasi (DuckDB)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 400, 30, 1200, 50))
    c.append(make_vertex("src_box", src_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#ea580c;strokeWidth=2;shadow=1;verticalAlign=top;", 60, 260, 240, 160))
    c.append(make_vertex("disp_box", disp_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#4f46e5;strokeWidth=2;shadow=1;verticalAlign=top;", 380, 260, 280, 160))
    c.append(make_vertex("oltp_box", oltp_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0284c7;strokeWidth=2;shadow=1;verticalAlign=top;", 780, 140, 340, 220))
    c.append(make_vertex("olap_box", olap_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#059669;strokeWidth=2;shadow=1;verticalAlign=top;", 780, 400, 340, 220))
    
    ui_enhanced_val = '''<div style="background-color:#0f172a;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;">
  LARAVEL WEB UI / DASHBOARD
</div>
<div style="padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;">
  <b>Fitur Antarmuka Terpadu:</b><br>
  • <b>Tab 1: Data Mikro</b> (Grid Individu + Transliterasi Label FK &amp; PII Masking)<br>
  • <b>Tab 2: Data Statistik</b> (Agregasi Wilayah: SUM, AVG, COUNT, MIN, MAX, MED, MOD)<br>
  • Dynamic Multi-Filter Checkbox<br>
  • Export Manager (ZIP Package &amp; CSV Log)
</div>'''
    c.append(make_vertex("ui_box", ui_enhanced_val, "whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0f172a;strokeWidth=2;shadow=1;verticalAlign=top;", 1240, 260, 340, 200))
    c.append(make_edge("e1_ds", "Baca File CSV/XLSX", "src_box", "disp_box", "strokeColor=#ea580c;strokeWidth=2;fontColor=#c2410c;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;"))
    c.append(make_edge("e2_ds", "Relational Insert / Migration", "disp_box", "oltp_box", "strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;"))
    c.append(make_edge("e3_ds", "Vectorized Ingestion (500k/s)", "disp_box", "olap_box", "strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;"))
    c.append(make_edge("e4_ds", "Auth &amp; Transliterasi Kamus", "oltp_box", "ui_box", "strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;"))
    c.append(make_edge("e5_ds", "Instant Analytics &amp; Agregasi (<0.05s)", "olap_box", "ui_box", "strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;"))
    diagrams.append(build_diagram("erd_fisikal_dual_storage", "Arsitektur Dual-Storage (OLTP + OLAP)", c, 2000, 1200))

    return diagrams

def generate_standard_drawio():
    all_diags = []
    all_diags.extend(get_asis_diagrams())
    all_diags.extend(get_standard_tobe_diagrams())
    
    full_xml = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
{"".join(all_diags)}
</mxfile>'''
    out_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Padu_Diagram.drawio"
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(full_xml)
    print(f"Standard Padu_Diagram.drawio generated with {len(all_diags)} tabs.")

if __name__ == "__main__":
    generate_standard_drawio()
