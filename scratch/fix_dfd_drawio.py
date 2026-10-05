import xml.sax.saxutils as saxutils
import os

def escape_xml(s):
    # Standard XML escaping for attributes: & -> &amp;, < -> &lt;, > -> &gt;, " -> &quot;
    return saxutils.escape(s, {'"': '&quot;'})

def generate_dfd():
    # Helper to generate XML for mxCell vertex
    def make_vertex(cid, val, style, x, y, w, h, parent="1"):
        return f'''        <mxCell id="{cid}" value="{escape_xml(val)}" style="{style}" vertex="1" parent="{parent}">
          <mxGeometry x="{x}" y="{y}" width="{w}" height="{h}" as="geometry" />
        </mxCell>'''

    # Helper to generate XML for mxCell edge
    def make_edge(cid, val, src, tgt, style, points=None, parent="1"):
        pts_xml = ""
        if points:
            pts_items = "".join([f'<mxPoint x="{px}" y="{py}" />' for px, py in points])
            pts_xml = f'''\n            <Array as="points">{pts_items}</Array>'''

        return f'''        <mxCell id="{cid}" value="{escape_xml(val)}" style="{style}" edge="1" parent="{parent}" source="{src}" target="{tgt}">
          <mxGeometry relative="1" as="geometry">{pts_xml}
          </mxGeometry>
        </mxCell>'''

    # =========================================================================
    # 1. PAGE 1: DFD LEVEL 0 (DIAGRAM KONTEKS)
    # =========================================================================
    lvl0_cells = []

    # Title Level 0
    t0_val = '<div style="font-size:18px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 0 — DIAGRAM KONTEKS</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Sistem Pengolah, Analisis Data Terpadu &amp; Pengamanan Biometrik (PADU v1.02)</div>'
    lvl0_cells.append(make_vertex("title_0", t0_val, "text;html=1;align=center;verticalAlign=middle;resizable=0;points=[];autosize=1;strokeColor=none;fillColor=none;", 450, 40, 750, 50))

    # Process 0.0 (Center)
    p0_val = '''<div style="font-size:14px;color:#a7f3d0;font-weight:bold;">0.0</div>
<div style="font-size:16px;font-weight:bold;margin-top:4px;color:#ffffff;line-height:1.3;">SISTEM ANALISIS DATA DTSEN &amp;<br>PENGAMANAN BIOMETRIK<br>(PADU v1.02)</div>
<hr style="border:1px solid #059669;margin:12px 0;">
<div style="font-size:11px;color:#d1fae5;line-height:1.5;">
• Hybrid Dual-Engine: Laravel 12 + Python DuckDB<br>
• Standalone 100% Offline Processing (13M+ Baris)<br>
• Zero-Trace Biometric Guard &amp; LockWorkStation
</div>'''
    lvl0_cells.append(make_vertex("P0", p0_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=3;fontColor=#ffffff;verticalAlign=middle;align=center;shadow=1;", 640, 240, 360, 460))

    # Entity E1 (Left)
    e1_0_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">ENTITAS LUAR</div>
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
    lvl0_cells.append(make_vertex("E1_0", e1_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 60, 240, 250, 460))

    # Entity E2 (Top Right)
    e2_0_val = '''<div style="font-size:12px;color:#fde68a;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #b45309;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#fef3c7;padding:0 6px;">
• Headless OpenCV Capture (DSHOW)<br>
• Frame visual berkala (loop 0.2s)<br>
• Snapshot verifikasi biometrik
</div>'''
    lvl0_cells.append(make_vertex("E2_0", e2_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1320, 240, 250, 180))

    # Entity E3 (Bottom Right)
    e3_0_val = '''<div style="font-size:12px;color:#c7d2fe;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #4338ca;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#e0e7ff;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''
    lvl0_cells.append(make_vertex("E3_0", e3_0_val, "rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1320, 520, 250, 180))

    # Edge style templates for Level 0
    # Inbound to P0 from E1 (Blue)
    in_e1_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=11;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
    # Outbound from P0 to E1 (Green)
    out_e1_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=11;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
    # Alert Outbound from P0 to E1 (Red)
    alert_e1_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#dc2626;strokeWidth=2;fontColor=#b91c1c;fontSize=11;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"

    # Connectors between E1 and P0: 6 distinct horizontal parallel tracks
    # Track 1: y=280 (Input)
    lvl0_cells.append(make_edge("f0_1", "1. Berkas Mentah DTSEN (CSV / XLSX)", "E1_0", "P0", in_e1_style + "exitX=1;exitY=0.087;entryX=0;entryY=0.087;"))
    # Track 2: y=350 (Input)
    lvl0_cells.append(make_edge("f0_2", "2. Kriteria Filter Dinamis &amp; Keyword NIK/KK", "E1_0", "P0", in_e1_style + "exitX=1;exitY=0.239;entryX=0;entryY=0.239;"))
    # Track 3: y=420 (Input)
    lvl0_cells.append(make_edge("f0_3", "3. Perintah Ekspor Data &amp; Terminasi Sesi", "E1_0", "P0", in_e1_style + "exitX=1;exitY=0.391;entryX=0;entryY=0.391;"))
    # Track 4: y=500 (Output)
    lvl0_cells.append(make_edge("f0_4", "4. Data Tabular Ter-Masking PII &amp; Metrik KPI", "P0", "E1_0", out_e1_style + "exitX=0;exitY=0.565;entryX=1;entryY=0.565;"))
    # Track 5: y=570 (Output)
    lvl0_cells.append(make_edge("f0_5", "5. Paket ZIP Olahan &amp; Log Audit Error CSV", "P0", "E1_0", out_e1_style + "exitX=0;exitY=0.717;entryX=1;entryY=0.717;"))
    # Track 6: y=640 (Output - Alert)
    lvl0_cells.append(make_edge("f0_6", "6. Alert Peringatan Layar (Red Lock &amp; Blur UI)", "P0", "E1_0", alert_e1_style + "exitX=0;exitY=0.870;entryX=1;entryY=0.870;"))

    # Connectors between P0 and E2 (Kamera)
    cam_in_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=11;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
    cam_out_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#64748b;strokeWidth=2;fontColor=#334155;fontSize=11;fontStyle=1;labelBackgroundColor=#f8fafc;labelBorderColor=#cbd5e1;"
    # Track A: y=290 (Camera Stream to P0)
    lvl0_cells.append(make_edge("f0_7", "Stream Frame Video (Headless OpenCV)", "E2_0", "P0", cam_in_style + "exitX=0;exitY=0.278;entryX=1;entryY=0.109;"))
    # Track B: y=370 (Control from P0 to Camera)
    lvl0_cells.append(make_edge("f0_8", "Sinyal Inisialisasi &amp; Pengaturan Sensor", "P0", "E2_0", cam_out_style + "exitX=1;exitY=0.283;entryX=0;entryY=0.722;"))

    # Connectors between P0 and E3 (OS Windows)
    os_cmd_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=11;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
    os_sig_style = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=11;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"
    # Track C: y=570 (Command to OS)
    lvl0_cells.append(make_edge("f0_9", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0", "E3_0", os_cmd_style + "exitX=1;exitY=0.717;entryX=0;entryY=0.278;"))
    # Track D: y=650 (Signal from OS)
    lvl0_cells.append(make_edge("f0_10", "Sinyal Shutdown / SIGTERM / atexit", "E3_0", "P0", os_sig_style + "exitX=0;exitY=0.722;entryX=1;entryY=0.891;"))


    # =========================================================================
    # 2. PAGE 2: DFD LEVEL 1 (DEKOMPOSISI SISTEM)
    # =========================================================================
    lvl1_cells = []

    # Title Level 1
    t1_val = '<div style="font-size:20px;font-weight:bold;color:#0f172a;">DATA FLOW DIAGRAM (DFD) LEVEL 1 — DEKOMPOSISI PROSES SISTEM PADU</div><div style="font-size:13px;color:#64748b;margin-top:4px;">Pemetaan 6 Sub-Proses Fungsional, 5 Data Store, dan 3 Entitas Eksternal</div>'
    lvl1_cells.append(make_vertex("title_1", t1_val, "text;html=1;align=center;verticalAlign=middle;resizable=0;points=[];autosize=1;strokeColor=none;fillColor=none;", 800, 30, 850, 50))

    # --- ENTITAS EKSTERNAL ---
    e1_1_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">ENTITAS LUAR</div>
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
    lvl1_cells.append(make_vertex("E1_1", e1_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 60, 240, 240, 780))

    e2_1_val = '''<div style="font-size:12px;color:#fde68a;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #b45309;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#fef3c7;padding:0 6px;">
• Headless cv2.CAP_DSHOW<br>
• Frame snapshot verifikasi awal<br>
• Continuous visual loop (0.2s)
</div>'''
    lvl1_cells.append(make_vertex("E2_1", e2_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 2000, 240, 240, 180))

    e3_1_val = '''<div style="font-size:12px;color:#c7d2fe;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#ffffff;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #4338ca;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#e0e7ff;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''
    lvl1_cells.append(make_vertex("E3_1", e3_1_val, "rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 2000, 760, 240, 180))

    # --- DATA STORES (D1 - D5) ---
    store_style_d1 = "shape=partialRectangle;right=0;left=0;fillColor=#0f172a;strokeColor=#38bdf8;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"
    d1_val = '<b style="font-size:12px;color:#38bdf8;">D1</b> | <b>Folder Sumber DTSEN (src-dtsen/)</b><br><span style="font-size:10px;color:#94a3b8;">File mentah CSV / XLSX (13M+ baris)</span>'
    lvl1_cells.append(make_vertex("D1", d1_val, store_style_d1, 440, 110, 320, 55))

    store_style_d3 = "shape=partialRectangle;right=0;left=0;fillColor=#4c0519;strokeColor=#fb7185;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"
    d3_val = '<b style="font-size:12px;color:#fb7185;">D3</b> | <b>Log Audit Kualitas (quality_audit_logs)</b><br><span style="font-size:10px;color:#fecdd3;">Rekam anomali baris (Critical &amp; Warning) — DuckDB</span>'
    lvl1_cells.append(make_vertex("D3", d3_val, store_style_d3, 940, 220, 340, 55))

    store_style_d2 = "shape=partialRectangle;right=0;left=0;fillColor=#3b0764;strokeColor=#c084fc;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"
    d2_val = '<b style="font-size:12px;color:#c084fc;">D2</b> | <b>Tabel Kolumnar (dtsen_data — DuckDB)</b><br><span style="font-size:10px;color:#e9d5ff;">Dataset 48 variabel ternormalisasi (Vectorized Pages)</span>'
    lvl1_cells.append(make_vertex("D2", d2_val, store_style_d2, 940, 480, 340, 60))

    store_style_d4 = "shape=partialRectangle;right=0;left=0;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"
    d4_val = '<b style="font-size:12px;color:#f59e0b;">D4</b> | <b>Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)</b><br><span style="font-size:10px;color:#fef08a;">Snapshot wajah pemilik sah sesi aktif (Zero-Trace)</span>'
    lvl1_cells.append(make_vertex("D4", d4_val, store_style_d4, 1480, 360, 340, 55))

    store_style_d5 = "shape=partialRectangle;right=0;left=0;fillColor=#064e3b;strokeColor=#34d399;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"
    d5_val = '<b style="font-size:12px;color:#34d399;">D5</b> | <b>Repositori Ekspor (src-export/ &amp; ZIP)</b><br><span style="font-size:10px;color:#bbf7d0;">Paket ZIP kompresi Clean CSV + Error CSV + Metadata</span>'
    lvl1_cells.append(make_vertex("D5", d5_val, store_style_d5, 940, 880, 340, 55))

    # --- SUB-PROSES DFD LEVEL 1 (1.0 s/d 6.0) ---
    p1_val = '''<div style="font-size:12px;color:#93c5fd;font-weight:bold;">1.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Inisialisasi &amp; Verifikasi Biometrik Awal</div>
<hr style="border:1px solid #1e3a8a;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• cv2 Headless Capture (adaptasi 2s)<br>
• MediaPipe Evaluasi Jumlah Wajah<br>
• Reject jika 0 / &gt;1 wajah; simpan jika 1
</div>'''
    lvl1_cells.append(make_vertex("P1", p1_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#172554;strokeColor=#2563eb;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 150, 340, 120))

    p2_val = '''<div style="font-size:12px;color:#a7f3d0;font-weight:bold;">2.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Ingesti, Normalisasi &amp; Audit Kualitas Data</div>
<hr style="border:1px solid #047857;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Auto-Scan &amp; PyArrow Ingestion Engine<br>
• Mapping sinonim 48 variabel resmi BPS<br>
• Quality Check Rule: Valid / Warning / Critical
</div>'''
    lvl1_cells.append(make_vertex("P2", p2_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 220, 320, 130))

    p3_val = '''<div style="font-size:12px;color:#ddd6fe;font-weight:bold;">3.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pemrosesan Analitik, Filter &amp; PII Masking</div>
<hr style="border:1px solid #6d28d9;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• DuckDB Memory-Mapped Slicing (&lt; 0.05s)<br>
• PII Data Masking (NIK, Nama, Gaji, Alamat)<br>
• Agregasi KPI instan &amp; Micro-Table (50 rows/page)
</div>'''
    lvl1_cells.append(make_vertex("P3", p3_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#2e1065;strokeColor=#8b5cf6;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 520, 320, 140))

    p4_val = '''<div style="font-size:12px;color:#fed7aa;font-weight:bold;">4.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pemantauan Keamanan Real-Time (Guard Loop)</div>
<hr style="border:1px solid #b45309;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Face Mesh &amp; Iris Eye-Tracking (Loop 0.2s, CPU &lt; 3%)<br>
• Deteksi User Pergi &gt; 4 Detik<br>
• Deteksi Shoulder Surfing (Mata Asing &gt; 1 Detik)
</div>'''
    lvl1_cells.append(make_vertex("P4", p4_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 520, 340, 140))

    p5_val = '''<div style="font-size:12px;color:#a7f3d0;font-weight:bold;">5.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Pengelolaan Ekspor &amp; Pengarsipan Data</div>
<hr style="border:1px solid #047857;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• CsvExportService Packaging Engine<br>
• Kompresi ZIP: Clean CSV + Error CSV + Metadata<br>
• Stream Berkas Unduhan ke Operator
</div>'''
    lvl1_cells.append(make_vertex("P5", p5_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 440, 840, 320, 130))

    p6_val = '''<div style="font-size:12px;color:#fecdd3;font-weight:bold;">6.0</div>
<div style="font-size:14px;font-weight:bold;margin-top:2px;">Terminasi Sistem &amp; Zero-Trace Purge</div>
<hr style="border:1px solid #be123c;margin:6px 0;">
<div style="font-size:10px;text-align:left;line-height:1.4;">
• Tangkap Sinyal atexit / SIGTERM / Close Window<br>
• Secure Wipe 0x00 sesi_wajah_aktif.jpg<br>
• Unlink berkas &amp; Flush RAM Cache (Zero-Trace)
</div>'''
    lvl1_cells.append(make_vertex("P6", p6_val, "rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor=#4c0519;strokeColor=#f43f5e;strokeWidth=2;fontColor=#ffffff;verticalAlign=middle;align=center;", 1480, 840, 340, 130))

    # --- EDGES / DATA FLOWS LEVEL 1 ---
    # Style shortcuts
    f_blue = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
    f_green = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
    f_purple = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;"
    f_amber = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=10;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
    f_red = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=10;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
    f_indigo = "edgeStyle=orthogonalEdgeStyle;rounded=0;html=1;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=10;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"

    # 1. Flow around Ingesti (Process 2.0)
    lvl1_cells.append(make_edge("f1_d1_in", "Salin File Mentah CSV/XLSX", "E1_1", "D1", f_blue + "exitX=1;exitY=0.06;entryX=0;entryY=0.5;"))
    lvl1_cells.append(make_edge("f1_d1_read", "Baca Berkas Mentah", "D1", "P2", f_blue + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    lvl1_cells.append(make_edge("f1_p2_trig", "Trigger Impor (POST /import-local)", "E1_1", "P2", f_blue + "exitX=1;exitY=0.13;entryX=0;entryY=0.45;"))
    lvl1_cells.append(make_edge("f1_p2_stat", "Ringkasan Ingesti &amp; Skor Kualitas", "P2", "E1_1", f_green + "exitX=0;exitY=0.8;entryX=1;entryY=0.21;"))
    lvl1_cells.append(make_edge("f1_p2_d3", "Rekam Anomali (Error Log)", "P2", "D3", f_red + "exitX=1;exitY=0.25;entryX=0;entryY=0.5;"))
    lvl1_cells.append(make_edge("f1_p2_d2", "Batch Ingest Data Bersih 48 Kolom", "P2", "D2", f_green + "exitX=1;exitY=0.75;entryX=0;entryY=0.25;"))

    # 2. Flow around Analytics & Masking (Process 3.0)
    lvl1_cells.append(make_edge("f1_p3_filter", "Parameter Filter Dinamis &amp; Keyword", "E1_1", "P3", f_purple + "exitX=1;exitY=0.44;entryX=0;entryY=0.35;"))
    lvl1_cells.append(make_edge("f1_p3_out", "Grid Data PII-Masked &amp; KPI Cards", "P3", "E1_1", f_purple + "exitX=0;exitY=0.75;entryX=1;entryY=0.53;"))
    lvl1_cells.append(make_edge("f1_p3_query", "Query Slicing Kolumnar (&lt; 0.05s)", "P3", "D2", f_purple + "exitX=1;exitY=0.35;entryX=0;entryY=0.75;"))
    lvl1_cells.append(make_edge("f1_d2_p3", "Dataset Terfilter", "D2", "P3", f_purple + "exitX=0;exitY=0.9;entryX=1;entryY=0.65;"))

    # 3. Flow around Export (Process 5.0)
    lvl1_cells.append(make_edge("f1_p5_req", "Permintaan Ekspor Arsip ZIP", "E1_1", "P5", f_green + "exitX=1;exitY=0.82;entryX=0;entryY=0.35;"))
    lvl1_cells.append(make_edge("f1_p5_down", "Stream Unduhan Berkas ZIP", "P5", "E1_1", f_green + "exitX=0;exitY=0.75;entryX=1;entryY=0.91;"))
    lvl1_cells.append(make_edge("f1_d2_p5", "Ambil Data Bersih Terpilih", "D2", "P5", f_green + "exitX=0.25;exitY=1;entryX=0.8;entryY=0;", points=[(1025, 780), (696, 780)]))
    lvl1_cells.append(make_edge("f1_d3_p5", "Ambil Rekam Error Audit", "D3", "P5", f_red + "exitX=0.8;exitY=1;entryX=0.95;entryY=0;", points=[(1212, 800), (744, 800)]))
    lvl1_cells.append(make_edge("f1_p5_d5", "Simpan Paket ZIP (src-export/)", "P5", "D5", f_green + "exitX=1;exitY=0.5;entryX=0;entryY=0.5;"))

    # 4. Flow around Biometric Startup (Process 1.0)
    lvl1_cells.append(make_edge("f1_p1_cam", "Frame Snapshot Wajah Awal", "E2_1", "P1", f_amber + "exitX=0;exitY=0.25;entryX=1;entryY=0.25;", points=[(1900, 285), (1900, 180)]))
    lvl1_cells.append(make_edge("f1_p1_d4", "Simpan Wajah Pemilik Sah (1 Wajah)", "P1", "D4", f_amber + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    lvl1_cells.append(make_edge("f1_p1_rej", "Status Sesi / Auto-Reject Silent", "P1", "E1_1", f_indigo + "exitX=0;exitY=0.2;entryX=0.9;entryY=0.01;", points=[(1400, 174), (1400, 70), (276, 70)]))

    # 5. Flow around Biometric Guard Loop (Process 4.0)
    lvl1_cells.append(make_edge("f1_p4_stream", "Continuous Video Stream (Loop 0.2s)", "E2_1", "P4", f_amber + "exitX=0;exitY=0.75;entryX=1;entryY=0.35;", points=[(1920, 375), (1920, 569)]))
    lvl1_cells.append(make_edge("f1_d4_p4", "Baca Profil Wajah Sah Referensi", "D4", "P4", f_amber + "exitX=0.5;exitY=1;entryX=0.5;entryY=0;"))
    lvl1_cells.append(make_edge("f1_p4_lock", "Kunci Layar (user32.LockWorkStation)", "P4", "E3_1", f_red + "exitX=1;exitY=0.75;entryX=0;entryY=0.35;", points=[(1920, 625), (1920, 823)]))
    lvl1_cells.append(make_edge("f1_p4_alert", "Red Lock Alert &amp; Blur UI Event", "P4", "E1_1", f_red + "exitX=0;exitY=0.75;entryX=1;entryY=0.68;", points=[(1380, 625), (1380, 770), (300, 770)]))

    # 6. Flow around Termination & Purge (Process 6.0)
    lvl1_cells.append(make_edge("f1_p6_sig", "Sinyal Shutdown OS / SIGTERM / atexit", "E3_1", "P6", f_indigo + "exitX=0;exitY=0.75;entryX=1;entryY=0.7;", points=[(1920, 895), (1920, 931)]))
    lvl1_cells.append(make_edge("f1_p6_close", "Perintah Tutup Aplikasi / Selesai", "E1_1", "P6", f_red + "exitX=1;exitY=0.97;entryX=0;entryY=0.85;", points=[(350, 997), (350, 1020), (1400, 1020), (1400, 950)]))
    lvl1_cells.append(make_edge("f1_p6_wipe", "Secure Overwrite (0x00) &amp; Hapus File", "P6", "D4", f_red + "exitX=0.2;exitY=0;entryX=0.2;entryY=1;", points=[(1548, 760), (1548, 415)]))

    # Assemble complete MXFILE
    xml_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
  <diagram id="dfd_level_0" name="DFD Level 0 - Diagram Konteks">
    <mxGraphModel dx="1600" dy="1000" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1600" pageHeight="1000" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(lvl0_cells)}
      </root>
    </mxGraphModel>
  </diagram>
  <diagram id="dfd_level_1" name="DFD Level 1 - Dekomposisi Sistem">
    <mxGraphModel dx="2400" dy="1400" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="2400" pageHeight="1400" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{chr(10).join(lvl1_cells)}
      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

    output_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\PADU_DFD.drawio"
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(xml_content)
    print(f"Successfully generated {output_path}")

if __name__ == "__main__":
    generate_dfd()
