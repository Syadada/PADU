import xml.sax.saxutils as saxutils
import os

def esc(s):
    return saxutils.escape(s, {'"': '&quot;'})

def make_vertex(cid, val, style, x, y, w, h, parent="1"):
    # Ensure html=1 is always present in vertex style so Draw.io always renders HTML
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

def build_all_dfd():
    # Style constants
    font_family = "Helvetica, Arial, sans-serif"
    
    # Store style WITH html=1
    def store_style(border_col, fill_col):
        return f"shape=partialRectangle;right=0;left=0;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"

    # =========================================================================
    # 1. TAB 1: AS-IS LEVEL 0 (DIAGRAM KONTEKS)
    # =========================================================================
    c_asis_0 = []
    t_asis_0 = '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — AS-IS (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis &amp; Pengolah Data DTSEN Eksisting (Tanpa Pengamanan Biometrik &amp; PII Masking)</div>'
    c_asis_0.append(make_vertex("t_asis_0", t_asis_0, "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 350, 40, 850, 50))

    # Process 0.0 AS-IS
    p0_asis_val = '''<div style="font-size:14px;color:#93c5fd;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#ffffff;line-height:1.3;">SISTEM PENGOLAH &amp; ANALISIS DATA<br>DTSEN 2026 (PADU v1.0 — AS-IS)</div>
<hr style="border:1px solid #2563eb;margin:12px 0;">
<div style="font-size:11px;color:#bfdbfe;line-height:1.5;">
• Dual-Engine: Laravel 12 + Python DuckDB<br>
• Impor Lokal CSV/XLSX ke Database<br>
• Filter Multi-Kolom &amp; Table Grid Standar<br>
• Ekspor Berkas CSV Polos
</div>'''
    c_asis_0.append(make_vertex("P0_ASIS", p0_asis_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#1e3a8a;strokeColor=#3b82f6;strokeWidth=3;fontColor=#ffffff;verticalAlign=middle;align=center;shadow=1;", 640, 220, 360, 440))

    # Entity E1 AS-IS
    e1_asis_0_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#ffffff;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #334155;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#e2e8f0;padding:0 8px;">
• Menyalin file CSV/XLSX ke src-dtsen/<br>
• Memilih file &amp; trigger impor lokal<br>
• Memantau progress bar impor<br>
• Mengatur filter NIK, Wilayah, Desil<br>
• Melihat tabel data mentah (as-is)<br>
• Mengunduh file CSV hasil filter/error
</div>'''
    c_asis_0.append(make_vertex("E1_ASIS_0", e1_asis_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 80, 220, 240, 440))

    # Edges AS-IS Level 0 (8 Parallel Horizontal Lines)
    in_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=11;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
    out_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=11;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"

    # 4 Inputs (y=260, 315, 370, 425)
    c_asis_0.append(make_edge("e_asis_0_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_ASIS_0", "P0_ASIS", in_style + "exitX=1;exitY=0.09;entryX=0;entryY=0.09;"))
    c_asis_0.append(make_edge("e_asis_0_2", "2. Pilihan File &amp; Perintah Impor Lokal", "E1_ASIS_0", "P0_ASIS", in_style + "exitX=1;exitY=0.22;entryX=0;entryY=0.22;"))
    c_asis_0.append(make_edge("e_asis_0_3", "3. Kriteria Filter Multi-Kolom &amp; Keyword", "E1_ASIS_0", "P0_ASIS", in_style + "exitX=1;exitY=0.35;entryX=0;entryY=0.35;"))
    c_asis_0.append(make_edge("e_asis_0_4", "4. Permintaan Ekspor Berkas CSV", "E1_ASIS_0", "P0_ASIS", in_style + "exitX=1;exitY=0.48;entryX=0;entryY=0.48;"))

    # 4 Outputs (y=485, 540, 595, 650)
    c_asis_0.append(make_edge("e_asis_0_5", "5. Status &amp; Progress Bar Ingesti Data", "P0_ASIS", "E1_ASIS_0", out_style + "exitX=0;exitY=0.61;entryX=1;entryY=0.61;"))
    c_asis_0.append(make_edge("e_asis_0_6", "6. Ringkasan Metrik KPI Kualitas (Count)", "P0_ASIS", "E1_ASIS_0", out_style + "exitX=0;exitY=0.74;entryX=1;entryY=0.74;"))
    c_asis_0.append(make_edge("e_asis_0_7", "7. Tabel Data DTSEN Biasa (Tanpa PII Masking)", "P0_ASIS", "E1_ASIS_0", out_style + "exitX=0;exitY=0.86;entryX=1;entryY=0.86;"))
    c_asis_0.append(make_edge("e_asis_0_8", "8. Berkas Ekspor CSV (Clean / Error CSV)", "P0_ASIS", "E1_ASIS_0", out_style + "exitX=0;exitY=0.97;entryX=1;entryY=0.97;"))


    # =========================================================================
    # 2. TAB 2: AS-IS LEVEL 1 (DEKOMPOSISI SISTEM)
    # =========================================================================
    c_asis_1 = []
    t_asis_1 = '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — AS-IS (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 4 Sub-Proses dan 3 Data Store Sistem Eksisting</div>'
    c_asis_1.append(make_vertex("t_asis_1", t_asis_1, "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 30, 850, 50))

    # Entity E1 (AS-IS Level 1)
    c_asis_1.append(make_vertex("E1_ASIS_1", e1_asis_0_val, "rounded=1;arcSize=8;whiteSpace=wrap;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 60, 200, 240, 680))

    # Data Stores AS-IS (D1, D2, D3)
    c_asis_1.append(make_vertex("D1_ASIS", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 420, 100, 320, 55))
    c_asis_1.append(make_vertex("D3_ASIS", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 900, 240, 340, 55))
    c_asis_1.append(make_vertex("D2_ASIS", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 900, 480, 340, 60))

    # Processes AS-IS (1.0 - 4.0)
    p1_asis_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Scan Direktori &amp; Ingesti Data</div>
<hr style="border:1px solid #1e3a8a;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Auto-scan folder src-dtsen/<br>
• Validasi eksistensi &amp; ukuran berkas<br>
• Ingesti stream PyArrow / fast_import.py
</div>'''
    c_asis_1.append(make_vertex("P1_ASIS", p1_asis_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#172554;strokeColor=#2563eb;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 420, 200, 320, 120))

    p2_asis_val = '''<div style="font-size:12px;color:#a7f3d0;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Normalisasi &amp; Evaluasi Kualitas Data</div>
<hr style="border:1px solid #047857;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Mapping sinonim 48 variabel BPS<br>
• Evaluasi Valid, Warning, Critical<br>
• Catat progres ke import_progress.json
</div>'''
    c_asis_1.append(make_vertex("P2_ASIS", p2_asis_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 420, 380, 320, 130))

    p3_asis_val = '''<div style="font-size:12px;color:#ddd6fe;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pencarian, Filter &amp; Agregasi KPI</div>
<hr style="border:1px solid #6d28d9;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Eksekusi table scan DuckDB<br>
• Agregasi KPI (Valid/Warn/Crit Count)<br>
• Render Data Table biasa (50 rows/page polos)
</div>'''
    c_asis_1.append(make_vertex("P3_ASIS", p3_asis_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#2e1065;strokeColor=#8b5cf6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 420, 580, 320, 130))

    p4_asis_val = '''<div style="font-size:12px;color:#fed7aa;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Ekspor Berkas CSV Mentah</div>
<hr style="border:1px solid #b45309;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Query filter dataset atau error logs<br>
• Stream CSV langsung ke browser<br>
• Unduh Clean CSV / Error CSV polos
</div>'''
    c_asis_1.append(make_vertex("P4_ASIS", p4_asis_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 420, 780, 320, 120))

    # Connectors AS-IS Level 1
    c_asis_1.append(make_edge("e_asis_d1_in", "Salin File CSV/XLSX", "E1_ASIS_1", "D1_ASIS", in_style + "exitX=1;exitY=0.04;entryX=0;entryY=0.5;"))
    c_asis_1.append(make_edge("e_asis_d1_read", "Baca Berkas Mentah", "D1_ASIS", "P1_ASIS", in_style + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c_asis_1.append(make_edge("e_asis_p1_trig", "Trigger Impor (POST)", "E1_ASIS_1", "P1_ASIS", in_style + "exitX=1;exitY=0.12;entryX=0;entryY=0.5;"))
    c_asis_1.append(make_edge("e_asis_p1_pipe", "Stream Baris Data Mentah", "P1_ASIS", "P2_ASIS", in_style + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    
    c_asis_1.append(make_edge("e_asis_p2_d2", "Simpan Data Bersih", "P2_ASIS", "D2_ASIS", out_style + "exitX=1;exitY=0.7;entryX=0;entryY=0.25;"))
    c_asis_1.append(make_edge("e_asis_p2_d3", "Rekam Log Audit Error", "P2_ASIS", "D3_ASIS", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=10;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;exitX=1;exitY=0.3;entryX=0;entryY=0.5;"))
    c_asis_1.append(make_edge("e_asis_p2_stat", "Update Progres &amp; Ringkasan Kualitas", "P2_ASIS", "E1_ASIS_1", out_style + "exitX=0;exitY=0.5;entryX=1;entryY=0.35;"))

    c_asis_1.append(make_edge("e_asis_p3_filter", "Parameter Filter &amp; Keyword", "E1_ASIS_1", "P3_ASIS", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;exitX=1;exitY=0.58;entryX=0;entryY=0.3;"))
    c_asis_1.append(make_edge("e_asis_p3_query", "Query Table Scan", "P3_ASIS", "D2_ASIS", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;exitX=1;exitY=0.35;entryX=0;entryY=0.75;"))
    c_asis_1.append(make_edge("e_asis_d2_p3", "Dataset Terfilter", "D2_ASIS", "P3_ASIS", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;exitX=0;exitY=0.9;entryX=1;entryY=0.65;"))
    c_asis_1.append(make_edge("e_asis_p3_out", "Tabel Data Polos &amp; KPI Cards", "P3_ASIS", "E1_ASIS_1", "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;exitX=0;exitY=0.75;entryX=1;entryY=0.69;"))

    c_asis_1.append(make_edge("e_asis_p4_req", "Permintaan Ekspor CSV", "E1_ASIS_1", "P4_ASIS", in_style + "exitX=1;exitY=0.88;entryX=0;entryY=0.3;"))
    c_asis_1.append(make_edge("e_asis_d2_p4", "Ambil Data Bersih", "D2_ASIS", "P4_ASIS", out_style + "exitX=0.25;exitY=1;entryX=0.85;entryY=0;", points=[(985, 750), (692, 750)]))
    c_asis_1.append(make_edge("e_asis_p4_down", "Stream File Unduhan CSV", "P4_ASIS", "E1_ASIS_1", out_style + "exitX=0;exitY=0.7;entryX=1;entryY=0.94;"))


    # =========================================================================
    # 3. TAB 3: TO-BE LEVEL 0 (DIAGRAM KONTEKS)
    # =========================================================================
    c_tobe_0 = []
    t_tobe_0 = '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — TO-BE (DIAGRAM KONTEKS)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Analisis Data Terpadu &amp; Pengamanan Biometrik (PADU v1.02 — TO-BE)</div>'
    c_tobe_0.append(make_vertex("t_tobe_0", t_tobe_0, "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 450, 40, 750, 50))

    # Process 0.0 TO-BE
    p0_tobe_val = '''<div style="font-size:14px;color:#a7f3d0;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#ffffff;line-height:1.3;">SISTEM ANALISIS DATA DTSEN &amp;<br>PENGAMANAN BIOMETRIK<br>(PADU v1.02 — TO-BE)</div>
<hr style="border:1px solid #059669;margin:12px 0;">
<div style="font-size:11px;color:#d1fae5;line-height:1.5;">
• Hybrid Dual-Engine: Laravel 12 + Python DuckDB<br>
• Standalone 100% Offline Processing (13M+ Baris)<br>
• Dynamic Multi-Column Filtering &amp; PII Masking<br>
• Zero-Trace Biometric Guard &amp; user32 Lock
</div>'''
    c_tobe_0.append(make_vertex("P0_TOBE", p0_tobe_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=3;fontColor=#ffffff;verticalAlign=middle;align=center;shadow=1;", 640, 240, 360, 460))

    # Entities TO-BE
    e1_tobe_0_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#ffffff;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #334155;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#e2e8f0;padding:0 8px;">
• Mengunggah berkas mentah DTSEN<br>
• Menjalankan filter dinamis &amp; pencarian<br>
• Meninjau tabel data ter-masking PII<br>
• Menganalisis metrik kualitas data<br>
• Mengunduh paket ZIP arsip &amp; log audit<br>
• Menerima notifikasi peringatan layar
</div>'''
    c_tobe_0.append(make_vertex("E1_TOBE_0", e1_tobe_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 60, 240, 250, 460))

    e2_tobe_0_val = '''<div style="font-size:12px;color:#fde68a;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #b45309;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#fef3c7;padding:0 6px;">
• Headless OpenCV Capture (DSHOW)<br>
• Frame visual berkala (loop 0.2s)<br>
• Snapshot verifikasi biometrik
</div>'''
    c_tobe_0.append(make_vertex("E2_TOBE_0", e2_tobe_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1320, 240, 250, 180))

    e3_tobe_0_val = '''<div style="font-size:12px;color:#c7d2fe;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #4338ca;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#e0e7ff;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''
    c_tobe_0.append(make_vertex("E3_TOBE_0", e3_tobe_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1320, 520, 250, 180))

    # Flows TO-BE Level 0
    in_tobe = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=11;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
    out_tobe = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=11;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
    alert_tobe = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#b91c1c;fontSize=11;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"

    c_tobe_0.append(make_edge("f0_tb_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_TOBE_0", "P0_TOBE", in_tobe + "exitX=1;exitY=0.087;entryX=0;entryY=0.087;"))
    c_tobe_0.append(make_edge("f0_tb_2", "2. Kriteria Filter Dinamis &amp; Keyword NIK/KK", "E1_TOBE_0", "P0_TOBE", in_tobe + "exitX=1;exitY=0.239;entryX=0;entryY=0.239;"))
    c_tobe_0.append(make_edge("f0_tb_3", "3. Perintah Ekspor Data &amp; Terminasi Sesi", "E1_TOBE_0", "P0_TOBE", in_tobe + "exitX=1;exitY=0.391;entryX=0;entryY=0.391;"))
    c_tobe_0.append(make_edge("f0_tb_4", "4. Data Tabular Ter-Masking PII &amp; Metrik KPI", "P0_TOBE", "E1_TOBE_0", out_tobe + "exitX=0;exitY=0.565;entryX=1;entryY=0.565;"))
    c_tobe_0.append(make_edge("f0_tb_5", "5. Paket ZIP Olahan &amp; Log Audit Error CSV", "P0_TOBE", "E1_TOBE_0", out_tobe + "exitX=0;exitY=0.717;entryX=1;entryY=0.717;"))
    c_tobe_0.append(make_edge("f0_tb_6", "6. Alert Peringatan Layar (Red Lock &amp; Blur UI)", "P0_TOBE", "E1_TOBE_0", alert_tobe + "exitX=0;exitY=0.870;entryX=1;entryY=0.870;"))

    cam_in = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=11;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
    cam_out = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#64748b;strokeWidth=2;fontColor=#334155;fontSize=11;fontStyle=1;labelBackgroundColor=#f8fafc;labelBorderColor=#cbd5e1;"
    c_tobe_0.append(make_edge("f0_tb_7", "Stream Frame Video (Headless OpenCV)", "E2_TOBE_0", "P0_TOBE", cam_in + "exitX=0;exitY=0.278;entryX=1;entryY=0.109;"))
    c_tobe_0.append(make_edge("f0_tb_8", "Sinyal Inisialisasi &amp; Pengaturan Sensor", "P0_TOBE", "E2_TOBE_0", cam_out + "exitX=1;exitY=0.283;entryX=0;entryY=0.722;"))

    os_cmd = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=11;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
    os_sig = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=11;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"
    c_tobe_0.append(make_edge("f0_tb_9", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0_TOBE", "E3_TOBE_0", os_cmd + "exitX=1;exitY=0.717;entryX=0;entryY=0.278;"))
    c_tobe_0.append(make_edge("f0_tb_10", "Sinyal Shutdown / SIGTERM / atexit", "E3_TOBE_0", "P0_TOBE", os_sig + "exitX=0;exitY=0.722;entryX=1;entryY=0.891;"))


    # =========================================================================
    # 4. TAB 4: TO-BE LEVEL 1 (DEKOMPOSISI SISTEM - CLEAN NO OVERLAP)
    # =========================================================================
    c_tobe_1 = []
    t_tobe_1 = '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — TO-BE (DEKOMPOSISI SISTEM)</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 6 Sub-Proses Fungsional, 5 Data Store, dan 3 Entitas Eksternal Tanpa Persilangan Jalur</div>'
    c_tobe_1.append(make_vertex("t_tobe_1", t_tobe_1, "text;align=center;verticalAlign=middle;strokeColor=none;fillColor=none;", 750, 30, 950, 50))

    # Entities TO-BE Level 1
    e1_tobe_1_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#ffffff;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #334155;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#e2e8f0;padding:0 8px;">
• Menyalin file ke folder src-dtsen/<br>
• Menjalankan trigger impor lokal<br>
• Mengatur filter dinamis multi-kolom<br>
• Melihat Micro-Table &amp; metrik KPI<br>
• Meminta ekspor paket ZIP<br>
• Menutup aplikasi
</div>'''
    c_tobe_1.append(make_vertex("E1_TOBE_1", e1_tobe_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 60, 240, 240, 800))

    e2_tobe_1_val = '''<div style="font-size:12px;color:#fde68a;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #b45309;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#fef3c7;padding:0 6px;">
• Headless cv2.CAP_DSHOW<br>
• Frame snapshot verifikasi awal<br>
• Continuous visual loop (0.2s)
</div>'''
    c_tobe_1.append(make_vertex("E2_TOBE_1", e2_tobe_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 2000, 220, 240, 180))

    e3_tobe_1_val = '''<div style="font-size:12px;color:#c7d2fe;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #4338ca;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#e0e7ff;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''
    c_tobe_1.append(make_vertex("E3_TOBE_1", e3_tobe_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 2000, 780, 240, 180))

    # Data Stores TO-BE Level 1 (WITH html=1!)
    c_tobe_1.append(make_vertex("D1_TOBE", '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>', store_style("#38bdf8", "#0f172a"), 440, 100, 320, 55))
    c_tobe_1.append(make_vertex("D3_TOBE", '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam baris anomali (Critical &amp; Warning) — DuckDB</span>', store_style("#fb7185", "#4c0519"), 940, 220, 340, 55))
    c_tobe_1.append(make_vertex("D2_TOBE", '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vector Pages)</span>', store_style("#c084fc", "#3b0764"), 940, 480, 340, 60))
    c_tobe_1.append(make_vertex("D4_TOBE", '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot wajah pemilik sah sesi aktif (Zero-Trace)</span>', store_style("#f59e0b", "#451a03"), 1480, 360, 340, 55))
    c_tobe_1.append(make_vertex("D5_TOBE", '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#bbf7d0;">Paket ZIP kompresi Clean CSV + Error CSV + Metadata</span>', store_style("#34d399", "#064e3b"), 940, 880, 340, 55))

    # Processes TO-BE Level 1 (1.0 - 6.0)
    p1_tb_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Inisialisasi &amp; Verifikasi Biometrik Awal</div>
<hr style="border:1px solid #1e3a8a;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• cv2 Headless Capture (adaptasi 2s)<br>
• MediaPipe Evaluasi Jumlah Wajah<br>
• Reject jika 0 / &gt;1 wajah; simpan jika 1
</div>'''
    c_tobe_1.append(make_vertex("P1_TOBE", p1_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#172554;strokeColor=#2563eb;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 150, 340, 120))

    p2_tb_val = '''<div style="font-size:12px;color:#a7f3d0;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Ingesti, Normalisasi &amp; Audit Kualitas Data</div>
<hr style="border:1px solid #047857;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Auto-Scan &amp; PyArrow Ingestion Engine<br>
• Mapping sinonim 48 variabel resmi BPS<br>
• Quality Check Rule: Valid / Warning / Critical
</div>'''
    c_tobe_1.append(make_vertex("P2_TOBE", p2_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 220, 320, 130))

    p3_tb_val = '''<div style="font-size:12px;color:#ddd6fe;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pemrosesan Analitik, Filter &amp; PII Masking</div>
<hr style="border:1px solid #6d28d9;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• DuckDB Memory-Mapped Slicing (&lt; 0.05s)<br>
• PII Data Masking (NIK, Nama, Gaji, Alamat)<br>
• Agregasi KPI instan &amp; Micro-Table (50 rows/page)
</div>'''
    c_tobe_1.append(make_vertex("P3_TOBE", p3_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#2e1065;strokeColor=#8b5cf6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 520, 320, 140))

    p4_tb_val = '''<div style="font-size:12px;color:#fed7aa;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pemantauan Keamanan Real-Time (Guard Loop)</div>
<hr style="border:1px solid #b45309;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Face Mesh &amp; Iris Eye-Tracking (Loop 0.2s, CPU &lt; 3%)<br>
• Deteksi User Pergi &gt; 4 Detik<br>
• Deteksi Shoulder Surfing (Mata Asing &gt; 1 Detik)
</div>'''
    c_tobe_1.append(make_vertex("P4_TOBE", p4_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 520, 340, 140))

    p5_tb_val = '''<div style="font-size:12px;color:#a7f3d0;font-weight:bold;">5.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pengelolaan Ekspor &amp; Pengarsipan Data</div>
<hr style="border:1px solid #047857;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• CsvExportService Packaging Engine<br>
• Kompresi ZIP: Clean CSV + Error CSV + Metadata<br>
• Stream Berkas Unduhan ke Operator
</div>'''
    c_tobe_1.append(make_vertex("P5_TOBE", p5_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 840, 320, 130))

    p6_tb_val = '''<div style="font-size:12px;color:#fecdd3;font-weight:bold;">6.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Terminasi Sistem &amp; Zero-Trace Purge</div>
<hr style="border:1px solid #be123c;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Tangkap Sinyal atexit / SIGTERM / Close Window<br>
• Secure Wipe 0x00 sesi_wajah_aktif.jpg<br>
• Unlink berkas &amp; Flush RAM Cache (Zero-Trace)
</div>'''
    c_tobe_1.append(make_vertex("P6_TOBE", p6_tb_val, "rounded=1;arcSize=20;whiteSpace=wrap;fillColor=#4c0519;strokeColor=#f43f5e;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 840, 340, 130))

    # Edges TO-BE Level 1 with CLEAN NON-CROSSING GEOMETRY
    f_blue = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
    f_green = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
    f_purple = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;"
    f_amber = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=10;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
    f_red = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=10;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
    f_indigo = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=10;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"

    # Process 2.0 Ingesti Flows
    c_tobe_1.append(make_edge("f1_tb_d1_in", "Salin File Mentah CSV/XLSX", "E1_TOBE_1", "D1_TOBE", f_blue + "exitX=1;exitY=0.06;entryX=0;entryY=0.5;"))
    c_tobe_1.append(make_edge("f1_tb_d1_read", "Baca Berkas Mentah", "D1_TOBE", "P2_TOBE", f_blue + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c_tobe_1.append(make_edge("f1_tb_p2_trig", "Trigger Impor (POST /import-local)", "E1_TOBE_1", "P2_TOBE", f_blue + "exitX=1;exitY=0.13;entryX=0;entryY=0.45;"))
    c_tobe_1.append(make_edge("f1_tb_p2_stat", "Ringkasan Ingesti &amp; Skor Kualitas", "P2_TOBE", "E1_TOBE_1", f_green + "exitX=0;exitY=0.8;entryX=1;entryY=0.21;"))
    c_tobe_1.append(make_edge("f1_tb_p2_d3", "Rekam Anomali (Error Log)", "P2_TOBE", "D3_TOBE", f_red + "exitX=1;exitY=0.25;entryX=0;entryY=0.5;"))
    c_tobe_1.append(make_edge("f1_tb_p2_d2", "Batch Ingest Data Bersih 48 Kolom", "P2_TOBE", "D2_TOBE", f_green + "exitX=1;exitY=0.75;entryX=0;entryY=0.25;"))

    # Process 3.0 Analitik & Masking Flows
    c_tobe_1.append(make_edge("f1_tb_p3_filter", "Parameter Filter Dinamis &amp; Keyword", "E1_TOBE_1", "P3_TOBE", f_purple + "exitX=1;exitY=0.44;entryX=0;entryY=0.35;"))
    c_tobe_1.append(make_edge("f1_tb_p3_out", "Grid Data PII-Masked &amp; KPI Cards", "P3_TOBE", "E1_TOBE_1", f_purple + "exitX=0;exitY=0.75;entryX=1;entryY=0.53;"))
    c_tobe_1.append(make_edge("f1_tb_p3_query", "Query Slicing Kolumnar (&lt; 0.05s)", "P3_TOBE", "D2_TOBE", f_purple + "exitX=1;exitY=0.35;entryX=0;entryY=0.75;"))
    c_tobe_1.append(make_edge("f1_tb_d2_p3", "Dataset Terfilter", "D2_TOBE", "P3_TOBE", f_purple + "exitX=0;exitY=0.9;entryX=1;entryY=0.65;"))

    # Process 5.0 Ekspor Flows
    c_tobe_1.append(make_edge("f1_tb_p5_req", "Permintaan Ekspor Arsip ZIP", "E1_TOBE_1", "P5_TOBE", f_green + "exitX=1;exitY=0.82;entryX=0;entryY=0.35;"))
    c_tobe_1.append(make_edge("f1_tb_p5_down", "Stream Unduhan Berkas ZIP", "P5_TOBE", "E1_TOBE_1", f_green + "exitX=0;exitY=0.75;entryX=1;entryY=0.91;"))
    c_tobe_1.append(make_edge("f1_tb_d2_p5", "Ambil Data Bersih Terpilih", "D2_TOBE", "P5_TOBE", f_green + "exitX=0.2;exitY=1;entryX=0.8;entryY=0;", points=[(1008, 770), (696, 770)]))
    c_tobe_1.append(make_edge("f1_tb_d3_p5", "Ambil Rekam Error Audit", "D3_TOBE", "P5_TOBE", f_red + "exitX=0.8;exitY=1;entryX=0.95;entryY=0;", points=[(1212, 790), (744, 790)]))
    c_tobe_1.append(make_edge("f1_tb_p5_d5", "Simpan Paket ZIP (src-export/)", "P5_TOBE", "D5_TOBE", f_green + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))

    # Process 1.0 Biometrik Awal Flows
    c_tobe_1.append(make_edge("f1_tb_p1_cam", "Frame Snapshot Wajah Awal", "E2_TOBE_1", "P1_TOBE", f_amber + "exitX=0;exitY=0.25;entryX=1;entryY=0.25;", points=[(1900, 265), (1900, 180)]))
    c_tobe_1.append(make_edge("f1_tb_p1_d4", "Simpan Wajah Pemilik Sah (1 Wajah)", "P1_TOBE", "D4_TOBE", f_amber + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c_tobe_1.append(make_edge("f1_tb_p1_rej", "Status Sesi / Auto-Reject Silent", "P1_TOBE", "E1_TOBE_1", f_indigo + "exitX=0;exitY=0.2;entryX=0.9;entryY=0.01;", points=[(1400, 174), (1400, 70), (276, 70)]))

    # Process 4.0 Biometric Guard Flows
    c_tobe_1.append(make_edge("f1_tb_p4_stream", "Continuous Video Stream (Loop 0.2s)", "E2_TOBE_1", "P4_TOBE", f_amber + "exitX=0;exitY=0.75;entryX=1;entryY=0.35;", points=[(1920, 355), (1920, 569)]))
    c_tobe_1.append(make_edge("f1_tb_d4_p4", "Baca Profil Wajah Sah Referensi", "D4_TOBE", "P4_TOBE", f_amber + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    c_tobe_1.append(make_edge("f1_tb_p4_lock", "Kunci Layar (user32.LockWorkStation)", "P4_TOBE", "E3_TOBE_1", f_red + "exitX=1;exitY=0.75;entryX=0;entryY=0.35;", points=[(1920, 625), (1920, 843)]))
    # Route Red Lock Alert along dedicated corridor under D2 without overlapping
    c_tobe_1.append(make_edge("f1_tb_p4_alert", "Red Lock Alert &amp; Blur UI Event", "P4_TOBE", "E1_TOBE_1", f_red + "exitX=0;exitY=0.75;entryX=1;entryY=0.68;", points=[(1380, 625), (1380, 720), (300, 720)]))

    # Process 6.0 Terminasi & Purge Flows
    c_tobe_1.append(make_edge("f1_tb_p6_sig", "Sinyal Shutdown OS / SIGTERM / atexit", "E3_TOBE_1", "P6_TOBE", f_indigo + "exitX=0;exitY=0.75;entryX=1;entryY=0.7;", points=[(1920, 915), (1920, 931)]))
    c_tobe_1.append(make_edge("f1_tb_p6_close", "Perintah Tutup Aplikasi / Selesai", "E1_TOBE_1", "P6_TOBE", f_red + "exitX=1;exitY=0.97;entryX=0;entryY=0.85;", points=[(350, 1016), (350, 1040), (1400, 1040), (1400, 950)]))
    # Route Secure Wipe around the right perimeter of P4 directly up to D4 (No crossing P4!)
    c_tobe_1.append(make_edge("f1_tb_p6_wipe", "Secure Overwrite 0x00 &amp; Hapus File", "P6_TOBE", "D4_TOBE", f_red + "exitX=0.9;exitY=0;entryX=0.9;entryY=1;", points=[(1786, 840), (1840, 840), (1840, 480), (1786, 480), (1786, 415)]))

    # Assemble complete MXFILE with all 4 diagrams
    xml_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
  <diagram id="dfd_asis_level_0" name="AS-IS Level 0 (Diagram Konteks)">
    <mxGraphModel dx="1600" dy="1000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1600" pageHeight="1000" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(c_asis_0)}
      </root>
    </mxGraphModel>
  </diagram>
  <diagram id="dfd_asis_level_1" name="AS-IS Level 1 (Dekomposisi Sistem)">
    <mxGraphModel dx="1800" dy="1200" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1800" pageHeight="1200" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(c_asis_1)}
      </root>
    </mxGraphModel>
  </diagram>
  <diagram id="dfd_tobe_level_0" name="TO-BE Level 0 (Diagram Konteks)">
    <mxGraphModel dx="1600" dy="1000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1600" pageHeight="1000" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(c_tobe_0)}
      </root>
    </mxGraphModel>
  </diagram>
  <diagram id="dfd_tobe_level_1" name="TO-BE Level 1 (Dekomposisi Sistem)">
    <mxGraphModel dx="2400" dy="1400" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="2400" pageHeight="1400" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(c_tobe_1)}
      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

    output_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\PADU_DFD.drawio"
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(xml_content)
    print(f"Successfully generated {output_path} with 4 diagrams!")

if __name__ == "__main__":
    build_all_dfd()
