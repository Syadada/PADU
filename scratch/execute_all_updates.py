import xml.etree.ElementTree as ET
import xml.sax.saxutils as saxutils
import shutil
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

def build_diagram_xml(diag_id, diag_name, cells, w=2400, h=1400):
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

# Load existing Padu_Diagram.drawio
src_file = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Padu_Diagram.drawio"
tree = ET.parse(src_file)
root = tree.getroot()

existing_diags = {}
for d in root.findall("diagram"):
    existing_diags[d.get("name")] = d

# =========================================================================
# BUILD ENHANCED STANDARD TO-BE LEVEL 0
# =========================================================================
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

e4_superadmin_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR KHUSUS</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">👑 SUPER ADMIN (BAPPENAS / BPS)</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengakses Dedicated Library Management Page<br>
• Memperbarui Kamus Kode Referensi (Bappenas)<br>
• Mengatur Aturan Kualitas &amp; Variabel Baru<br>
• Mengontrol Hak Akses &amp; Manajemen Pengguna<br>
• Mengaudit Log Perubahan Master Metadata
</div>'''

def make_tobe_level_0():
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
    return build_diagram_xml("dfd_tobe_level_0", "TO-BE Level 0 (Diagram Konteks)", c, 1700, 1000)

def make_tobe_level_1():
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
    return build_diagram_xml("dfd_tobe_level_1", "TO-BE Level 1 (Dekomposisi Sistem)", c, 3200, 1300)

def make_tobe_level_2_p3():
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
    return build_diagram_xml("dfd_tobe_level_2_p3", "TO-BE Level 2 - P3.0 (Analitik & PII Masking)", c, 2100, 1100)

def make_enhanced_erd():
    # Read existing ERD diagram from Padu_Diagram.drawio
    erd_elem = existing_diags["ERD Logikal (DTSEN 2026)"]
    erd_xml = ET.tostring(erd_elem, encoding="unicode")
    
    # Add referensi_kamus_data if not present
    if "referensi_kamus_data" not in erd_xml:
        ref_kamus_val = '''&lt;div style=&quot;background-color:#0891b2;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;&quot;&gt;&#xa;  referensi_kamus_data (Kamus Transliterasi FK)&#xa;&lt;/div&gt;&#xa;&lt;div style=&quot;padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;&quot;&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[PK]&lt;/b&gt; id : &lt;i&gt;BIGINT AUTO_INCREMENT&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[IDX]&lt;/b&gt; nama_tabel : &lt;i&gt;VARCHAR(50) (&#39;keluargas&#39;, &#39;individus&#39;)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[IDX]&lt;/b&gt; nama_kolom : &lt;i&gt;VARCHAR(100)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;kode_nilai : &lt;i&gt;VARCHAR(20) (misal &#39;1&#39;, &#39;2&#39;, &#39;01&#39;)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;color:#0e7490;font-weight:bold;&quot;&gt;label_transliterasi : &lt;i&gt;VARCHAR(255) (Deskripsi)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;kategori_sumber : &lt;i&gt;VARCHAR(100) (&#39;Bappenas 2026&#39;, &#39;BPS&#39;)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;urutan : &lt;i&gt;INTEGER DEFAULT 0&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;padding:2px 0;&quot;&gt;created_at / updated_at : &lt;i&gt;TIMESTAMP&lt;/i&gt;&lt;/div&gt;&#xa;&lt;/div&gt;'''
        cell_xml = f'''        <mxCell id="tbl_ref_kamus" parent="1" style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#0891b2;strokeWidth=2;shadow=1;verticalAlign=top;" value="{ref_kamus_val}" vertex="1">
          <mxGeometry height="320" width="320" x="1560" y="1050" as="geometry" />
        </mxCell>
        <mxCell id="rel_ref_individu" edge="1" parent="1" source="tbl_ref_kamus" style="html=1;edgeStyle=orthogonalEdgeStyle;rounded=1;strokeColor=#0891b2;strokeWidth=2;dashed=1;exitX=0;exitY=0.3;entryX=1;entryY=0.7;" target="tbl_individus" value="&lt;div style=&quot;background-color:#ffffff;border:1px solid #0891b2;padding:3px 6px;border-radius:4px;font-size:10px;color:#0e7490;&quot;&gt;Transliterasi Kode FK&lt;/div&gt;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>'''
        erd_xml = erd_xml.replace("</root>", cell_xml + "\n      </root>")
    return erd_xml

# =========================================================================
# ASSEMBLE STANDARD Padu_Diagram.drawio (NO SUPERADMIN, NO BLANK PAGE-15)
# =========================================================================
standard_diagrams = []
# 1-6. AS-IS
for name in [
    "AS-IS Level 0 (Diagram Konteks)",
    "AS-IS Level 1 (Dekomposisi Sistem)",
    "AS-IS Level 2 - P1.0 (Scan & Ingesti Data)",
    "AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)",
    "AS-IS Level 2 - P3.0 (Pencarian & Agregasi KPI)",
    "AS-IS Level 2 - P4.0 (Ekspor CSV Mentah)"
]:
    standard_diagrams.append(ET.tostring(existing_diags[name], encoding="unicode"))

# 7-8. TO-BE 0 and 1
standard_diagrams.append(make_tobe_level_0())
standard_diagrams.append(make_tobe_level_1())

# 9-10. TO-BE P1, P2
standard_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P1.0 (Inisialisasi Biometrik)"], encoding="unicode"))
standard_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P2.0 (Ingesti & Audit Kualitas)"], encoding="unicode"))

# 11. TO-BE P3 (ENHANCED)
standard_diagrams.append(make_tobe_level_2_p3())

# 12-14. TO-BE P4, P5, P6
standard_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)"], encoding="unicode"))
standard_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P5.0 (Ekspor Paket ZIP)"], encoding="unicode"))
standard_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P6.0 (Terminasi & Purge)"], encoding="unicode"))

# 15. ERD Logikal (Enhanced with referensi_kamus_data)
standard_diagrams.append(make_enhanced_erd())

# 16. Arsitektur Dual-Storage
standard_diagrams.append(ET.tostring(existing_diags["Arsitektur Dual-Storage (OLTP + OLAP)"], encoding="unicode"))

std_xml = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
{"".join(standard_diagrams)}
</mxfile>'''

with open(src_file, "w", encoding="utf-8") as f:
    f.write(std_xml)
print(f"Updated standard Padu_Diagram.drawio ({len(standard_diagrams)} clean tabs, no blank Page-15).")

# Also write PADU_ERD.drawio
erd_file = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\PADU_ERD.drawio"
erd_standalone_xml = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
{make_enhanced_erd()}
{ET.tostring(existing_diags["Arsitektur Dual-Storage (OLTP + OLAP)"], encoding="unicode")}
</mxfile>'''
with open(erd_file, "w", encoding="utf-8") as f:
    f.write(erd_standalone_xml)
print("Updated PADU_ERD.drawio with referensi_kamus_data table.")

# =========================================================================
# ASSEMBLE SEPARATE SUPERADMIN DIAGRAM: Padu_Diagram_SuperAdmin.drawio
# =========================================================================
def make_superadmin_level_0():
    c = []
    c.append(make_vertex("t_sa_0", '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — TO-BE DENGAN SUPER ADMIN (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis Data Terpadu, Pengamanan Biometrik &amp; Tata Kelola Kamus Metadata (PADU v1.02)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 400, 30, 1100, 50))
    p0_sa_val = '''<div style="font-size:14px;color:#000000;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#000000;line-height:1.3;">SISTEM ANALISIS DATA DTSEN,<br>BIOMETRIC GUARD &amp; TATA KELOLA METADATA<br>(PADU v1.02 — ENTERPRISE RBAC)</div>
<hr style="border:1px solid #007ACC;margin:12px 0;">
<div style="font-size:11px;color:#000000;line-height:1.5;">
• Hybrid Dual-Engine: Laravel 12 + Python DuckDB (13M+ Baris Offline)<br>
• Role-Based Access Control: Operator Data (OPD) vs Super Admin (Bappenas/BPS)<br>
• Dedicated Library Management Page (Pembaruan Kamus Tanpa Ubah Struktur)<br>
• Dual-Module: Data Mikro (Transliterasi FK) &amp; Data Statistik (7 Metrik Wilayah)<br>
• Zero-Trace Biometric Guard &amp; user32 Lockdown
</div>'''
    c.append(make_vertex("P0_SA", p0_sa_val, f"rounded=1;arcSize=20;whiteSpace=wrap;fillColor={PROC_FILL};strokeColor={PROC_BORDER};strokeWidth=3;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;", 640, 220, 420, 520))
    c.append(make_vertex("E1_SA_0", e1_tobe_standard, ent_style(), 50, 220, 270, 520))
    c.append(make_vertex("E4_SA_0", e4_superadmin_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#fef08a;strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#000000;verticalAlign=middle;align=center;shadow=1;", 1360, 220, 300, 250))
    c.append(make_vertex("E2_SA_0", e2_tobe_val, ent_style(), 1380, 500, 260, 160))
    c.append(make_vertex("E3_SA_0", e3_tobe_val, ent_style(), 1380, 690, 260, 160))

    # Operator flows
    c.append(make_edge("e_sa_0_1", "1. Berkas Mentah DTSEN (CSV/XLSX)", "E1_SA_0", "P0_SA", blue_in + "exitX=1;exitY=0.08;entryX=0;entryY=0.08;"))
    c.append(make_edge("e_sa_0_2", "2. Kriteria Filter &amp; Pilihan Modul (Mikro/Statistik)", "E1_SA_0", "P0_SA", blue_in + "exitX=1;exitY=0.25;entryX=0;entryY=0.25;"))
    c.append(make_edge("e_sa_0_3", "3. Perintah Ekspor ZIP &amp; Navigasi", "E1_SA_0", "P0_SA", blue_in + "exitX=1;exitY=0.45;entryX=0;entryY=0.45;"))
    c.append(make_edge("e_sa_0_4", "4. Data Mikro Terpadu (Transliterasi Label FK)", "P0_SA", "E1_SA_0", green_out + "exitX=0;exitY=0.62;entryX=1;entryY=0.62;"))
    c.append(make_edge("e_sa_0_5", "5. Rekap Statistik Wilayah (7 Metrik Lengkap)", "P0_SA", "E1_SA_0", green_out + "exitX=0;exitY=0.78;entryX=1;entryY=0.78;"))
    c.append(make_edge("e_sa_0_6", "6. Paket ZIP Olahan &amp; Alert Layar", "P0_SA", "E1_SA_0", green_out + "exitX=0;exitY=0.92;entryX=1;entryY=0.92;"))

    # Super admin flows
    c.append(make_edge("e_sa_0_sa1", "1. Kredensial Super Admin &amp; RBAC Auth", "E4_SA_0", "P0_SA", "strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0;exitY=0.2;entryX=1;entryY=0.15;"))
    c.append(make_edge("e_sa_0_sa2", "2. Update Kamus Kode Referensi (Bappenas/BPS)", "E4_SA_0", "P0_SA", "strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0;exitY=0.5;entryX=1;entryY=0.3;"))
    c.append(make_edge("e_sa_0_sa3", "3. Panel Manajemen Metadata &amp; Log Audit Perubahan", "P0_SA", "E4_SA_0", "strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=1;exitY=0.42;entryX=0;entryY=0.8;"))

    # Camera & Win OS
    c.append(make_edge("e_sa_0_cam", "Stream Frame Video (Headless OpenCV)", "E2_SA_0", "P0_SA", amber_cam + "exitX=0;exitY=0.4;entryX=1;entryY=0.6;"))
    c.append(make_edge("e_sa_0_lock", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0_SA", "E3_SA_0", red_alert + "exitX=1;exitY=0.85;entryX=0;entryY=0.4;"))
    return build_diagram_xml("dfd_sa_level_0", "TO-BE Level 0 — Super Admin (Diagram Konteks)", c, 1800, 1100)

def make_superadmin_level_1():
    c = []
    c.append(make_vertex("t_sa_1", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — TO-BE DENGAN SUPER ADMIN</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 7 Sub-Proses Fungsional Berurutan (1.0 s/d 7.0), 7 Data Store, dan 4 Entitas Eksternal (Termasuk Tata Kelola Library)</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 1000, 30, 1800, 50))
    c.append(make_vertex("E1_SA_1", e1_tobe_standard, ent_style(), 50, 260, 260, 600))
    c.append(make_vertex("E4_SA_1", e4_superadmin_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#fef08a;strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#000000;verticalAlign=middle;align=center;shadow=1;", 2750, 240, 290, 250))
    c.append(make_vertex("E2_SA_1", e2_tobe_val, ent_style(), 350, 90, 260, 150))
    c.append(make_vertex("E3_SA_1", e3_tobe_val, ent_style(), 2350, 90, 260, 150))

    c.append(make_vertex("D1_SA", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 700, 120, 280, 50))
    c.append(make_vertex("D2_SA", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 950, 640, 320, 55))
    c.append(make_vertex("D3_SA", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 1450, 640, 320, 55))
    c.append(make_vertex("D4_SA", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot wajah pemilik sah sesi aktif (Zero-Trace)</span>', store_style("#f59e0b", "#451a03"), 920, 800, 340, 55))
    c.append(make_vertex("D5_SA", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#a7f3d0;">Paket ZIP kompresi Clean CSV + Error CSV + Metadata</span>', store_style("#34d399", "#064e3b"), 1970, 640, 320, 55))
    c.append(make_vertex("D6_SA", '<b style="font-size:12px;color:#ca8a04;">D6</b> | <b>Master Library Metadata (metadata_libraries)</b><br><span style="font-size:10px;color:#fef08a;">Kamus Kode Variabel Resmi (Bappenas 2026 / BPS)</span>', store_style("#ca8a04", "#713f12"), 2350, 800, 350, 55))
    c.append(make_vertex("D7_SA", '<b style="font-size:12px;color:#ec4899;">D7</b> | <b>Log Audit Library (library_audit_logs)</b><br><span style="font-size:10px;color:#fbcfe8;">Riwayat Perubahan &amp; Penambahan Kode Kamus</span>', store_style("#ec4899", "#831843"), 2740, 800, 320, 55))

    p1_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">1.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Inisialisasi &amp; Verifikasi Biometrik</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• cv2 Headless Capture (adaptasi 2s)<br>• MediaPipe Validasi Wajah Tunggal<br>• Gatekeeper Akses Sesi Aplikasi</div>'''
    c.append(make_vertex("P1_SA", p1_tb, proc_style(), 350, 400, 260, 130))

    p2_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">2.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Ingesti, Normalisasi &amp; Audit Kualitas</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• Auto-Scan &amp; PyArrow Ingestion Engine<br>• Mapping sinonim 48 variabel BPS<br>• Evaluasi Kualitas: Valid/Warn/Crit</div>'''
    c.append(make_vertex("P2_SA", p2_tb, proc_style(), 720, 400, 270, 130))

    p3_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">3.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemrosesan Analitik (Mikro &amp; Statistik)</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• Tab 1: Data Mikro (Transliterasi FK via D6)<br>• Tab 2: Statistik Wilayah (7 Metrik Dinamis)<br>• PII Data Masking &amp; DuckDB Slicing</div>'''
    c.append(make_vertex("P3_SA", p3_tb, proc_style(), 1100, 400, 310, 130))

    p4_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">4.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pemantauan Keamanan Guard Loop</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• Face Mesh &amp; Iris Eye-Tracking (0.2s)<br>• User Pergi &gt; 4s / Ngintip &gt; 1s<br>• Lock Workstation &amp; Red Blur UI</div>'''
    c.append(make_vertex("P4_SA", p4_tb, proc_style(), 1530, 400, 280, 130))

    p5_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">5.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Pengelolaan Ekspor &amp; ZIP Packaging</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• Kompresi ZIP: Clean CSV + Error + Meta<br>• Stream berkas unduhan ke Operator</div>'''
    c.append(make_vertex("P5_SA", p5_tb, proc_style(), 1930, 400, 270, 130))

    p6_tb = '''<div style="font-size:12px;color:#000000;font-weight:bold;">6.0</div><div style="font-size:14px;font-weight:bold;margin-top:2px;color:#000000;">Terminasi Sistem &amp; Zero-Trace</div><hr style="border:1px solid #007ACC;margin:6px 0;"><div style="font-size:10px;line-height:1.4;color:#000000;">• Secure Overwrite 0x00 sesi_wajah<br>• Purge cache memori saat aplikasi tutup</div>'''
    c.append(make_vertex("P6_SA", p6_tb, proc_style(), 2320, 400, 270, 130))

    # Process 7.0 (SUPER ADMIN EXCLUSIVE)
    p7_tb = '''<div style="font-size:12px;color:#713f12;font-weight:bold;">7.0 (SUPER ADMIN ONLY)</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;color:#854d0e;">Manajemen &amp; Update Library Metadata</div>
<hr style="border:1px solid #ca8a04;margin:6px 0;">
<div style="font-size:10px;line-height:1.4;color:#713f12;">
• Antarmuka Khusus Manajemen Kamus Data<br>
• Tambah/Edit Kode Variabel Baru Bappenas/BPS<br>
• Validasi Struktur Tanpa Merusak Tabel Fisik<br>
• Sinkronisasi &amp; Hot-Reload Runtime Analitik
</div>'''
    c.append(make_vertex("P7_SA", p7_tb, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#fef9c3;strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#713f12;verticalAlign=middle;align=center;shadow=1;", 2740, 560, 310, 150))

    # =========================================================================
    # COMPLETE FLOWS FOR ALL 7 PROCESSES (INCLUDING PROCESS 6.0)
    # =========================================================================
    # P1 Flows (Inisialisasi & Verifikasi Biometrik)
    c.append(make_edge("f_sa_cam_p1", "Frame Snapshot Wajah Awal", "E2_SA_1", "P1_SA", amber_cam + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_p1_d4", "Simpan Wajah Pemilik Sah (1 Wajah)", "P1_SA", "D4_SA", amber_cam + "exitX=0.5;exitY=1;entryX=0;entryY=0.5;", points=[(480, 827)]))
    c.append(make_edge("f_sa_p1_e1", "Status Sesi / Auto-Reject Silent", "P1_SA", "E1_SA_1", green_out + "exitX=0;exitY=0.3;entryX=1;entryY=0.25;"))

    # P2 Flows (Ingesti & Audit Kualitas)
    c.append(make_edge("f_sa_e1_d1", "Salin Berkas Mentah CSV/XLSX", "E1_SA_1", "D1_SA", blue_in + "exitX=1;exitY=0.1;entryX=0;entryY=0.5;", points=[(330, 280), (330, 145)]))
    c.append(make_edge("f_sa_d1_p2", "Baca Berkas Mentah", "D1_SA", "P2_SA", blue_in + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_e1_p2", "Trigger Impor (POST /import-local)", "E1_SA_1", "P2_SA", blue_in + "exitX=1;exitY=0.35;entryX=0;entryY=0.2;", points=[(330, 430), (700, 430)]))
    c.append(make_edge("f_sa_p2_e1", "Ringkasan Ingesti &amp; Skor Kualitas", "P2_SA", "E1_SA_1", green_out + "exitX=0;exitY=0.8;entryX=1;entryY=0.45;", points=[(700, 500), (340, 500)]))
    c.append(make_edge("f_sa_p2_d2", "Batch Ingest Data Bersih 48 Kolom", "P2_SA", "D2_SA", green_out + "exitX=0.5;exitY=1;entryX=0.3;entryY=0;"))
    c.append(make_edge("f_sa_p2_d3", "Rekam Anomali (Error Log)", "P2_SA", "D3_SA", red_alert + "exitX=0.8;exitY=1;entryX=0.2;entryY=0;", points=[(936, 580), (1514, 580)]))

    # P3 Flows (Analitik Dual-Modul & Transliterasi)
    c.append(make_edge("f_sa_e1_p3", "Pilihan Modul (Mikro/Statistik) &amp; Filter", "E1_SA_1", "P3_SA", purple_flow + "exitX=1;exitY=0.55;entryX=0;entryY=0.3;", points=[(340, 550), (1080, 550)]))
    c.append(make_edge("f_sa_p3_e1_m", "Data Mikro Terpadu (PII-Masked &amp; Transliterasi FK)", "P3_SA", "E1_SA_1", green_out + "exitX=0;exitY=0.7;entryX=1;entryY=0.62;", points=[(1080, 590), (340, 590)]))
    c.append(make_edge("f_sa_p3_e1_s", "Rekap Statistik Agregasi Wilayah (7 Metrik)", "P3_SA", "E1_SA_1", green_out + "exitX=0;exitY=0.9;entryX=1;entryY=0.72;", points=[(1080, 620), (340, 620)]))
    c.append(make_edge("f_sa_p3_d2", "Query Slicing Kolumnar (< 0.05s) &amp; Agregasi", "P3_SA", "D2_SA", purple_flow + "exitX=0.3;exitY=1;entryX=0.7;entryY=0;"))
    c.append(make_edge("f_sa_d2_p3", "Dataset Terfilter", "D2_SA", "P3_SA", purple_flow + "exitX=0.8;exitY=0;entryX=0.5;entryY=1;"))
    c.append(make_edge("f_sa_d6_p3", "Baca Kamus Transliterasi Terkini", "D6_SA", "P3_SA", "strokeColor=#ca8a04;strokeWidth=2;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=1;exitX=0;exitY=0.5;entryX=0.8;entryY=1;", points=[(1348, 827)]))

    # P4 Flows (Guard Loop & Anti-Surfing)
    c.append(make_edge("f_sa_cam_p4", "Continuous Video Stream (Loop 0.2s)", "E2_SA_1", "P4_SA", amber_cam + "exitX=1;exitY=0.5;entryX=0.5;entryY=0;", points=[(1670, 165)]))
    c.append(make_edge("f_sa_d4_p4", "Baca Profil Wajah Sah Referensi", "D4_SA", "P4_SA", amber_cam + "exitX=1;exitY=0.5;entryX=0.1;entryY=1;", points=[(1558, 827)]))
    c.append(make_edge("f_sa_p4_e3", "Instruksi Kunci Layar (user32.LockWorkStation)", "P4_SA", "E3_SA_1", red_alert + "exitX=0.7;exitY=0;entryX=0.2;entryY=1;", points=[(1726, 320), (2402, 320)]))
    c.append(make_edge("f_sa_p4_e1", "Red Lock Alert &amp; Blur UI Event", "P4_SA", "E1_SA_1", red_alert + "exitX=0;exitY=0.9;entryX=1;entryY=0.82;", points=[(1500, 670), (340, 670)]))

    # P5 Flows (Pengelolaan Ekspor & ZIP)
    c.append(make_edge("f_sa_e1_p5", "Permintaan Ekspor Arsip ZIP", "E1_SA_1", "P5_SA", blue_in + "exitX=1;exitY=0.9;entryX=0;entryY=0.8;", points=[(340, 760), (1900, 760)]))
    c.append(make_edge("f_sa_p5_d2", "Ambil Data Bersih Terpilih", "D2_SA", "P5_SA", green_out + "exitX=1;exitY=0.5;entryX=0.1;entryY=1;", points=[(1957, 667)]))
    c.append(make_edge("f_sa_p5_d3", "Ambil Rekam Error Audit", "D3_SA", "P5_SA", red_alert + "exitX=1;exitY=0.5;entryX=0.4;entryY=1;", points=[(2038, 667)]))
    c.append(make_edge("f_sa_p5_d5", "Simpan Paket ZIP (src-export/)", "P5_SA", "D5_SA", green_out + "exitX=0.7;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_p5_e1", "Stream Unduhan Berkas ZIP", "P5_SA", "E1_SA_1", green_out + "exitX=0.5;exitY=1;entryX=1;entryY=0.92;", points=[(2065, 790), (340, 790)]))

    # P6 Flows (Terminasi Sistem & Zero-Trace Purge)
    c.append(make_edge("f_sa_e3_p6", "Sinyal Shutdown OS / SIGTERM / atexit", "E3_SA_1", "P6_SA", indigo_sys + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_e1_p6", "Perintah Tutup Aplikasi / Selesai", "E1_SA_1", "P6_SA", red_alert + "exitX=1;exitY=0.98;entryX=0;entryY=0.85;", points=[(340, 840), (2300, 840)]))
    c.append(make_edge("f_sa_p6_d4", "Secure Overwrite 0x00 &amp; Hapus File", "P6_SA", "D4_SA", red_alert + "exitX=0.5;exitY=1;entryX=0.8;entryY=1;", points=[(2455, 890), (1192, 890)]))

    # P7 Flows (Super Admin Exclusive)
    c.append(make_edge("f_sa_auth", "Kredensial Login Super Admin", "E4_SA_1", "P7_SA", "strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_p7_d6", "Simpan Perubahan Kamus &amp; Opsi Baru", "P7_SA", "D6_SA", "strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0;exitY=0.8;entryX=1;entryY=0.5;"))
    c.append(make_edge("f_sa_p7_d7", "Catat Riwayat Audit Pembaruan", "P7_SA", "D7_SA", "strokeColor=#ec4899;strokeWidth=2;fontColor=#9d174d;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c.append(make_edge("f_sa_p7_p3", "Hot-Reload Kamus ke Engine Analitik", "P7_SA", "P3_SA", "strokeColor=#059669;strokeWidth=2;dashed=1;fontColor=#047857;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=1;exitX=0;exitY=0.2;entryX=1;entryY=0.2;", points=[(1450, 590)]))
    c.append(make_edge("f_sa_p7_e4", "Status Validasi &amp; Konfirmasi Hot-Reload", "P7_SA", "E4_SA_1", "strokeColor=#ca8a04;strokeWidth=1.5;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0.8;exitY=0;entryX=0.8;entryY=1;"))

    return build_diagram_xml("dfd_sa_level_1", "TO-BE Level 1 — Super Admin (Dekomposisi Sistem)", c, 3600, 1400)

def make_superadmin_level_2_p7():
    c = []
    c.append(make_vertex("t_sa_l2_p7", '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 2 — PROSES 7.0 (SUPER ADMIN EXCLUSIVE)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Dekomposisi Sub-Sistem Manajemen Library, Kamus Variabel Bappenas &amp; Audit Trail Perubahan</div>', "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 1200, 50))
    c.append(make_vertex("E4_L2_P7", e4_superadmin_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#fef08a;strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#000000;verticalAlign=middle;align=center;shadow=1;", 50, 240, 290, 480))
    
    c.append(make_vertex("D6_L2_P7", '<b style="font-size:12px;color:#ca8a04;">D6</b> | <b>Master Library Metadata (metadata_libraries)</b><br><span style="font-size:10px;color:#fef08a;">Tabel Penyimpan Master Kamus Variabel &amp; Aturan</span>', store_style("#ca8a04", "#713f12"), 900, 100, 380, 55))
    c.append(make_vertex("D7_L2_P7", '<b style="font-size:12px;color:#ec4899;">D7</b> | <b>Log Audit Library (library_audit_logs)</b><br><span style="font-size:10px;color:#fbcfe8;">Riwayat Jejak Timestamp &amp; User Pengubah Kamus</span>', store_style("#ec4899", "#831843"), 1350, 100, 380, 55))

    p71_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.1</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Super Admin RBAC Gatekeeper</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Verifikasi login kredensial &amp; token sesi<br>
• Cek kolom users.role == 'super_admin'<br>
• Tolak akses pengguna biasa (OPD read-only)
</div>'''
    c.append(make_vertex("P7_1", p71_val, proc_style(), 420, 240, 260, 120))

    p72_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.2</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dedicated Library Management UI</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Render Halaman Khusus Manajemen Library<br>
• Formulir Tambah/Edit Kode Variabel Baru<br>
• Transliterasi Label Kamus Bappenas/BPS
</div>'''
    c.append(make_vertex("P7_2", p72_val, proc_style(), 750, 240, 270, 120))

    p73_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.3</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Schema &amp; Compatibility Validator</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Validasi keunikan kode nilai &amp; tipe data<br>
• Jaminan: Tidak merusak struktur tabel fisik<br>
• Proteksi integritas dataset 13M+ baris
</div>'''
    c.append(make_vertex("P7_3", p73_val, proc_style(), 1090, 240, 270, 120))

    p74_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.4</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Master Metadata Synchronizer</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Simpan entri baru ke tabel metadata_libraries<br>
• Update versi rilis kamus referensi<br>
• Bangun cache transliterasi in-memory
</div>'''
    c.append(make_vertex("P7_4", p74_val, proc_style(), 1430, 240, 280, 120))

    p75_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.5</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Audit Trail &amp; Change Logger</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Catat perubahan ke library_audit_logs<br>
• Rekam User ID Super Admin, IP &amp; Timestamp<br>
• Tampilkan notifikasi 'Library Sukses Diperbarui'
</div>'''
    c.append(make_vertex("P7_5", p75_val, proc_style(), 1250, 460, 290, 120))

    p76_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">7.6</div>
<div style="font-size:13px;font-weight:bold;margin-top:2px;color:#000000;">Dynamic Hot-Reload Dispatcher</div>
<hr style="border:1px solid #007ACC;margin:5px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;color:#000000;">
• Injeksi pembaruan kamus ke modul Data Mikro<br>
• Perbarui transliterasi di modul Statistik Wilayah<br>
• Operator langsung melihat label baru seketika
</div>'''
    c.append(make_vertex("P7_6", p76_val, proc_style(), 750, 460, 310, 120))

    c.append(make_edge("e_sa_p7_req", "Akses Halaman Khusus Library", "E4_L2_P7", "P7_1", blue_in + "exitX=1;exitY=0.15;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_sa_p7_1_2", "Sesi Sah Super Admin", "P7_1", "P7_2", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_sa_p7_crud", "Input / Edit Kode Kamus Baru", "E4_L2_P7", "P7_2", "strokeColor=#ca8a04;strokeWidth=2;fontColor=#854d0e;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=1;exitY=0.35;entryX=0.2;entryY=1;", points=[(370, 408), (804, 408)]))
    c.append(make_edge("e_sa_p7_2_3", "Draf Entri Kamus Metadata", "P7_2", "P7_3", blue_in + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_sa_p7_3_4", "Validasi Lolos (Skema Aman)", "P7_3", "P7_4", green_out + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))
    c.append(make_edge("e_sa_p7_4_d6", "Insert/Update Record Kamus", "P7_4", "D6_L2_P7", green_out + "exitX=0.5;exitY=0;entryX=0.5;entryY=1;"))
    c.append(make_edge("e_sa_p7_4_5", "Payload Log Perubahan", "P7_4", "P7_5", blue_in + "exitX=0.5;exitY=1;entryX=0.8;entryY=0;", points=[(1570, 410), (1482, 410)]))
    c.append(make_edge("e_sa_p7_5_d7", "Tulis Jejak Rekam Audit", "P7_5", "D7_L2_P7", "strokeColor=#ec4899;strokeWidth=2;fontColor=#9d174d;fontSize=10;fontStyle=1;edgeStyle=orthogonalEdgeStyle;rounded=0;exitX=0.8;exitY=0;entryX=0.5;entryY=1;"))
    c.append(make_edge("e_sa_p7_5_6", "Sinyal Broadcast Kamus Terkini", "P7_5", "P7_6", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.5;"))
    c.append(make_edge("e_sa_p7_6_e4", "Status 'Library Berhasil Diperbarui'", "P7_6", "E4_L2_P7", green_out + "exitX=0;exitY=0.5;entryX=1;entryY=0.85;"))
    return build_diagram_xml("dfd_sa_level_2_p7", "TO-BE Level 2 - P7.0 (Manajemen Library Metadata)", c, 2100, 1100)

def make_superadmin_erd():
    erd_elem = existing_diags["ERD Logikal (DTSEN 2026)"]
    erd_xml = ET.tostring(erd_elem, encoding="unicode")
    
    # Replace users table to include role
    old_users = """<div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">name : <i>VARCHAR(255)</i></div>"""
    new_users = """<div style="border-bottom:1px solid #f1f5f9;padding:2px 0;">name : <i>VARCHAR(255)</i></div>
  <div style="border-bottom:1px solid #f1f5f9;padding:2px 0;color:#b45309;font-weight:bold;"><b>[RBAC]</b> role : <i>ENUM('super_admin', 'operator') DEFAULT 'operator'</i></div>"""
    erd_xml = erd_xml.replace(esc(old_users), esc(new_users))

    # Add metadata_libraries and library_audit_logs tables
    extra_sa_tables = '''        <mxCell id="tbl_metadata_libraries" parent="1" style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#ca8a04;strokeWidth=2.5;shadow=1;verticalAlign=top;" value="&lt;div style=&quot;background-color:#ca8a04;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;&quot;&gt;&#xa;  metadata_libraries (Master Kamus &amp;amp; Aturan)&#xa;&lt;/div&gt;&#xa;&lt;div style=&quot;padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;&quot;&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[PK]&lt;/b&gt; id : &lt;i&gt;BIGINT AUTO_INCREMENT&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[IDX]&lt;/b&gt; nama_tabel : &lt;i&gt;VARCHAR(50)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[IDX]&lt;/b&gt; nama_kolom : &lt;i&gt;VARCHAR(100)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;kode_nilai : &lt;i&gt;VARCHAR(20)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;font-weight:bold;color:#854d0e;&quot;&gt;label_transliterasi : &lt;i&gt;VARCHAR(255)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;kategori_sumber : &lt;i&gt;VARCHAR(100) (&#39;Bappenas&#39;, &#39;BPS&#39;)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;color:#92400e;&quot;&gt;&lt;b&gt;[FK]&lt;/b&gt; updated_by : &lt;i&gt;BIGINT -&gt; users.id (Super Admin)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;padding:2px 0;&quot;&gt;created_at / updated_at : &lt;i&gt;TIMESTAMP&lt;/i&gt;&lt;/div&gt;&#xa;&lt;/div&gt;" vertex="1">
          <mxGeometry height="340" width="340" x="1540" y="1050" as="geometry" />
        </mxCell>
        <mxCell id="tbl_library_audit_logs" parent="1" style="html=1;whiteSpace=wrap;overflow=hidden;rounded=1;arcSize=6;fillColor=#ffffff;strokeColor=#ec4899;strokeWidth=2;shadow=1;verticalAlign=top;" value="&lt;div style=&quot;background-color:#db2777;color:#ffffff;font-size:14px;font-weight:bold;padding:8px;border-radius:6px 6px 0 0;text-align:center;&quot;&gt;&#xa;  library_audit_logs (Jejak Perubahan Library)&#xa;&lt;/div&gt;&#xa;&lt;div style=&quot;padding:10px;text-align:left;font-size:11px;line-height:1.6;color:#1e293b;background-color:#ffffff;&quot;&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;&lt;b&gt;[PK]&lt;/b&gt; id : &lt;i&gt;BIGINT AUTO_INCREMENT&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;color:#9d174d;&quot;&gt;&lt;b&gt;[FK]&lt;/b&gt; user_id : &lt;i&gt;BIGINT -&gt; users.id&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;action : &lt;i&gt;ENUM(&#39;CREATE&#39;, &#39;UPDATE&#39;, &#39;DELETE&#39;)&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;target_library_id : &lt;i&gt;BIGINT&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;border-bottom:1px solid #f1f5f9;padding:2px 0;&quot;&gt;old_values : &lt;i&gt;JSON&lt;/i&gt; | new_values : &lt;i&gt;JSON&lt;/i&gt;&lt;/div&gt;&#xa;  &lt;div style=&quot;padding:2px 0;&quot;&gt;timestamp : &lt;i&gt;TIMESTAMP DEFAULT NOW()&lt;/i&gt;&lt;/div&gt;&#xa;&lt;/div&gt;" vertex="1">
          <mxGeometry height="300" width="320" x="1920" y="1280" as="geometry" />
        </mxCell>
        <mxCell id="rel_users_library" edge="1" parent="1" source="tbl_users" style="html=1;edgeStyle=orthogonalEdgeStyle;rounded=1;strokeColor=#ca8a04;strokeWidth=2;exitX=0.2;exitY=1;entryX=0.8;entryY=0;" target="tbl_metadata_libraries" value="&lt;div style=&quot;background-color:#ffffff;border:1px solid #ca8a04;padding:3px 6px;border-radius:4px;font-size:10px;color:#854d0e;&quot;&gt;1 : N (Super Admin Updates Library)&lt;/div&gt;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>
        <mxCell id="rel_users_audit" edge="1" parent="1" source="tbl_users" style="html=1;edgeStyle=orthogonalEdgeStyle;rounded=1;strokeColor=#db2777;strokeWidth=2;exitX=0.5;exitY=1;entryX=0.5;entryY=0;" target="tbl_library_audit_logs" value="&lt;div style=&quot;background-color:#ffffff;border:1px solid #db2777;padding:3px 6px;border-radius:4px;font-size:10px;color:#9d174d;&quot;&gt;1 : N (Records Audit Trail)&lt;/div&gt;">
          <mxGeometry relative="1" as="geometry" />
        </mxCell>'''
    erd_xml = erd_xml.replace("</root>", extra_sa_tables + "\n      </root>")
    return erd_xml

sa_diagrams = []
# 1-6. AS-IS (Preserved)
for name in [
    "AS-IS Level 0 (Diagram Konteks)",
    "AS-IS Level 1 (Dekomposisi Sistem)",
    "AS-IS Level 2 - P1.0 (Scan & Ingesti Data)",
    "AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)",
    "AS-IS Level 2 - P3.0 (Pencarian & Agregasi KPI)",
    "AS-IS Level 2 - P4.0 (Ekspor CSV Mentah)"
]:
    sa_diagrams.append(ET.tostring(existing_diags[name], encoding="unicode"))

# 7-8. TO-BE SuperAdmin Level 0 & 1
sa_diagrams.append(make_superadmin_level_0())
sa_diagrams.append(make_superadmin_level_1())

# 9-14. TO-BE P1 to P6
sa_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P1.0 (Inisialisasi Biometrik)"], encoding="unicode"))
sa_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P2.0 (Ingesti & Audit Kualitas)"], encoding="unicode"))
sa_diagrams.append(make_tobe_level_2_p3())
sa_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)"], encoding="unicode"))
sa_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P5.0 (Ekspor Paket ZIP)"], encoding="unicode"))
sa_diagrams.append(ET.tostring(existing_diags["TO-BE Level 2 - P6.0 (Terminasi & Purge)"], encoding="unicode"))

# 15. TO-BE P7.0 (SUPER ADMIN EXCLUSIVE)
sa_diagrams.append(make_superadmin_level_2_p7())

# 16. ERD with Super Admin & Audit
sa_diagrams.append(make_superadmin_erd())

# 17. Dual-Storage
sa_diagrams.append(ET.tostring(existing_diags["Arsitektur Dual-Storage (OLTP + OLAP)"], encoding="unicode"))

sa_xml = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
{"".join(sa_diagrams)}
</mxfile>'''

sa_file = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Padu_Diagram_SuperAdmin.drawio"
with open(sa_file, "w", encoding="utf-8") as f:
    f.write(sa_xml)
print(f"Generated separate Padu_Diagram_SuperAdmin.drawio with {len(sa_diagrams)} complete tabs!")

