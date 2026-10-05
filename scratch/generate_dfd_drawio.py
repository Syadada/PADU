import xml.etree.ElementTree as ET
import os

def create_dfd_drawio():
    mxfile = ET.Element("mxfile", host="app.diagrams.net", agent="Antigravity", version="21.0.0", type="device")

    # =========================================================================
    # DIAGRAM 1: DFD LEVEL 0 (DIAGRAM KONTEKS)
    # =========================================================================
    diag_lvl0 = ET.SubElement(mxfile, "diagram", id="dfd_level_0", name="DFD Level 0 - Diagram Konteks")
    model_0 = ET.SubElement(diag_lvl0, "mxGraphModel", dx="1400", dy="900", grid="1", gridSize="10", guides="1", tooltips="1", connect="1", arrows="1", fold="1", page="1", pageScale="1", pageWidth="1400", pageHeight="900", math="0", shadow="0")
    root_0 = ET.SubElement(model_0, "root")
    ET.SubElement(root_0, "mxCell", id="0")
    ET.SubElement(root_0, "mxCell", id="1", parent="0")

    # Judul Diagram Level 0
    t0 = ET.SubElement(root_0, "mxCell", id="title_0", value="&lt;b style='font-size:18px;'&gt;DATA FLOW DIAGRAM (DFD) LEVEL 0 — DIAGRAM KONTEKS&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:13px;color:#64748b;'&gt;Sistem Pengolah, Analisis Data Terpadu &amp;amp; Pengamanan Biometrik (PADU v1.02)&lt;/span&gt;", style="text;html=1;align=center;verticalAlign=middle;resizable=0;points=[];autosize=1;strokeColor=none;fillColor=none;", vertex="1", parent="1")
    geo = ET.SubElement(t0, "mxGeometry", x="300", y="30", width="760", height="50")
    geo.set("as", "geometry")

    # Entitas 1: Operator Data / Analis (Kiri)
    e1_0 = ET.SubElement(root_0, "mxCell", id="E1_0", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:16px;'&gt;👤 OPERATOR DATA / ANALIS&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• Mengunggah berkas DTSEN&lt;br&gt;• Menjalankan filter &amp;amp; pencarian&lt;br&gt;• Menganalisis KPI &amp;amp; kualitas&lt;br&gt;• Mengunduh paket ekspor&lt;/div&gt;", style="rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e1_0, "mxGeometry", x="60", y="260", width="240", height="340")
    geo.set("as", "geometry")

    # Proses 0.0 (Tengah)
    p0 = ET.SubElement(root_0, "mxCell", id="P0", value="&lt;b style='font-size:15px;color:#a7f3d0;'&gt;0.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:16px;'&gt;SISTEM ANALISIS DATA DTSEN &amp;amp;&lt;br&gt;PENGAMANAN BIOMETRIK&lt;br&gt;(PADU v1.02)&lt;/b&gt;&lt;hr style='border-color:#059669;'&gt;&lt;div style='font-size:11px;color:#d1fae5;'&gt;Hybrid Dual-Engine: Laravel 12 + Python DuckDB&lt;br&gt;Zero-Trace MediaPipe Face &amp;amp; Iris Eye Guard&lt;/div&gt;", style="rounded=1;arcSize=30;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=3;fontColor=#ffffff;verticalAlign=top;spacingTop=15;shadow=1;", vertex="1", parent="1")
    geo = ET.SubElement(p0, "mxGeometry", x="510", y="290", width="380", height="280")
    geo.set("as", "geometry")

    # Entitas 2: Sensor Kamera / Headless Webcam (Kanan Atas)
    e2_0 = ET.SubElement(root_0, "mxCell", id="E2_0", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:15px;'&gt;📷 SENSOR KAMERA / WEBCAM&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• Headless cv2.CAP_DSHOW capture&lt;br&gt;• Frame visual berkala (loop 0.2s)&lt;br&gt;• Deteksi wajah &amp;amp; fiksasi mata&lt;/div&gt;", style="rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e2_0, "mxGeometry", x="1080", y="160", width="260", height="160")
    geo.set("as", "geometry")

    # Entitas 3: Sistem Operasi Windows (Kanan Bawah)
    e3_0 = ET.SubElement(root_0, "mxCell", id="E3_0", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:15px;'&gt;🪟 SISTEM OPERASI WINDOWS&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• Windows API (user32.dll)&lt;br&gt;• Layar LockWorkStation (Win+L)&lt;br&gt;• Sinyal OS (SIGTERM / atexit)&lt;/div&gt;", style="rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e3_0, "mxGeometry", x="1080", y="520", width="260", height="160")
    geo.set("as", "geometry")

    # Flow Helper
    def add_flow(root, f_id, val, src, tgt, stroke="#38bdf8", fcol="#0284c7", exitX="1", exitY="0.5", entryX="0", entryY="0.5", curved=0):
        c = ET.SubElement(root, "mxCell", id=f_id, value=val, style=f"edgeStyle=orthogonalEdgeStyle;rounded=1;curved={curved};html=1;strokeColor={stroke};strokeWidth=2;fontColor={fcol};fontSize=11;labelBackgroundColor=#ffffff;fontStyle=1;", edge="1", parent="1", source=src, target=tgt)
        geo = ET.SubElement(c, "mxGeometry", relative="1")
        geo.set("as", "geometry")
        return c

    # Flows Antara Operator (E1) -> Sistem (P0)
    add_flow(root_0, "f0_1", "Berkas Mentah DTSEN (CSV / XLSX)", "E1_0", "P0", "#0284c7", "#0369a1", exitX="1", exitY="0.2", entryX="0", entryY="0.2")
    add_flow(root_0, "f0_2", "Kriteria Filter, Pencarian &amp; Parameter", "E1_0", "P0", "#0284c7", "#0369a1", exitX="1", exitY="0.4", entryX="0", entryY="0.4")
    add_flow(root_0, "f0_3", "Perintah Ekspor &amp; Terminasi Sesi", "E1_0", "P0", "#0284c7", "#0369a1", exitX="1", exitY="0.6", entryX="0", entryY="0.6")

    # Flows Antara Sistem (P0) -> Operator (E1)
    add_flow(root_0, "f0_4", "Data Tabular Ter-Masking PII &amp; Metrik KPI", "P0", "E1_0", "#10b981", "#047857", exitX="0", exitY="0.75", entryX="1", entryY="0.75")
    add_flow(root_0, "f0_5", "Paket ZIP Olahan &amp; Log Audit Error CSV", "P0", "E1_0", "#10b981", "#047857", exitX="0", exitY="0.9", entryX="1", entryY="0.9")
    add_flow(root_0, "f0_6", "Peringatan Layar &amp; Status Guard Alert", "P0", "E1_0", "#ef4444", "#b91c1c", exitX="0", exitY="0.1", entryX="1", entryY="0.1")

    # Flows Kamera (E2) <-> Sistem (P0)
    add_flow(root_0, "f0_7", "Raw Video Frame (Headless Image Stream)", "E2_0", "P0", "#f59e0b", "#d97706", exitX="0", exitY="0.7", entryX="1", entryY="0.2")
    add_flow(root_0, "f0_8", "Sinyal Inisialisasi &amp; Kontrol Kamera", "P0", "E2_0", "#64748b", "#475569", exitX="1", exitY="0.1", entryX="0", entryY="0.3")

    # Flows OS Windows (E3) <-> Sistem (P0)
    add_flow(root_0, "f0_9", "Instruksi Kunci Layar (user32.LockWorkStation)", "P0", "E3_0", "#dc2626", "#991b1b", exitX="1", exitY="0.8", entryX="0", entryY="0.3")
    add_flow(root_0, "f0_10", "Sinyal Shutdown / SIGTERM / atexit", "E3_0", "P0", "#6366f1", "#4338ca", exitX="0", exitY="0.7", entryX="1", entryY="0.95")


    # =========================================================================
    # DIAGRAM 2: DFD LEVEL 1 (DEKOMPOSISI FUNGSIONAL)
    # =========================================================================
    diag_lvl1 = ET.SubElement(mxfile, "diagram", id="dfd_level_1", name="DFD Level 1 - Dekomposisi Sistem")
    model_1 = ET.SubElement(diag_lvl1, "mxGraphModel", dx="2200", dy="1400", grid="1", gridSize="10", guides="1", tooltips="1", connect="1", arrows="1", fold="1", page="1", pageScale="1", pageWidth="2200", pageHeight="1400", math="0", shadow="0")
    root_1 = ET.SubElement(model_1, "root")
    ET.SubElement(root_1, "mxCell", id="0")
    ET.SubElement(root_1, "mxCell", id="1", parent="0")

    # Judul Diagram Level 1
    t1 = ET.SubElement(root_1, "mxCell", id="title_1", value="&lt;b style='font-size:20px;'&gt;DATA FLOW DIAGRAM (DFD) LEVEL 1 — DEKOMPOSISI PROSES SISTEM PADU&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:13px;color:#64748b;'&gt;Pemetaan Aliran Data 6 Sub-Proses, 5 Data Store, dan 3 Entitas Luar&lt;/span&gt;", style="text;html=1;align=center;verticalAlign=middle;resizable=0;points=[];autosize=1;strokeColor=none;fillColor=none;", vertex="1", parent="1")
    geo = ET.SubElement(t1, "mxGeometry", x="650", y="25", width="900", height="50")
    geo.set("as", "geometry")

    # -----------------------------
    # ENTITAS LUAR
    # -----------------------------
    # E1: Operator Data (Kiri, Tinggi)
    e1_1 = ET.SubElement(root_1, "mxCell", id="E1_1", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:16px;'&gt;👤 OPERATOR DATA / ANALIS&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• Menyalin file ke folder src-dtsen/&lt;br&gt;• Menjalankan trigger impor lokal&lt;br&gt;• Mengatur filter dinamis multi-kolom&lt;br&gt;• Melihat Micro-Table &amp;amp; metrik KPI&lt;br&gt;• Meminta ekspor paket ZIP&lt;br&gt;• Menutup aplikasi&lt;/div&gt;", style="rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#1e293b;strokeColor=#3b82f6;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e1_1, "mxGeometry", x="40", y="180", width="240", height="860")
    geo.set("as", "geometry")

    # E2: Kamera Sensor (Kanan Atas)
    e2_1 = ET.SubElement(root_1, "mxCell", id="E2_1", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:15px;'&gt;📷 SENSOR KAMERA&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• Headless Camera Stream&lt;br&gt;• Frame Snapshot Wajah&lt;/div&gt;", style="rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#78350f;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e2_1, "mxGeometry", x="1900", y="160", width="240", height="180")
    geo.set("as", "geometry")

    # E3: OS Windows (Kanan Bawah)
    e3_1 = ET.SubElement(root_1, "mxCell", id="E3_1", value="&lt;b style='font-size:14px;'&gt;ENTITAS LUAR&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:15px;'&gt;🪟 SISTEM OPERASI WINDOWS&lt;/b&gt;&lt;hr&gt;&lt;div style='text-align:left;font-size:11px;'&gt;• user32.LockWorkStation()&lt;br&gt;• System Signals (SIGTERM / atexit)&lt;/div&gt;", style="rounded=1;arcSize=8;whiteSpace=wrap;html=1;fillColor=#312e81;strokeColor=#6366f1;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=10;", vertex="1", parent="1")
    geo = ET.SubElement(e3_1, "mxGeometry", x="1900", y="800", width="240", height="180")
    geo.set("as", "geometry")

    # -----------------------------
    # DATA STORES (D1 s/d D5)
    # -----------------------------
    # D1: src-dtsen/
    d1 = ET.SubElement(root_1, "mxCell", id="D1", value="&lt;b style='font-size:12px;'&gt;D1&lt;/b&gt; | &lt;b&gt;Folder Sumber DTSEN (src-dtsen/)&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:10px;color:#94a3b8;'&gt;File mentah CSV / XLSX (13M+ baris)&lt;/span&gt;", style="shape=partialRectangle;right=0;left=0;fillColor=#0f172a;strokeColor=#38bdf8;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;", vertex="1", parent="1")
    geo = ET.SubElement(d1, "mxGeometry", x="390", y="110", width="320", height="55")
    geo.set("as", "geometry")

    # D2: dtsen_data (DuckDB)
    d2 = ET.SubElement(root_1, "mxCell", id="D2", value="&lt;b style='font-size:12px;'&gt;D2&lt;/b&gt; | &lt;b&gt;Tabel Kolumnar (dtsen_data — DuckDB)&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:10px;color:#e9d5ff;'&gt;Dataset 48 variabel ternormalisasi (Vectorized Storage)&lt;/span&gt;", style="shape=partialRectangle;right=0;left=0;fillColor=#3b0764;strokeColor=#c084fc;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;", vertex="1", parent="1")
    geo = ET.SubElement(d2, "mxGeometry", x="870", y="360", width="360", height="60")
    geo.set("as", "geometry")

    # D3: quality_audit_logs (DuckDB)
    d3 = ET.SubElement(root_1, "mxCell", id="D3", value="&lt;b style='font-size:12px;'&gt;D3&lt;/b&gt; | &lt;b&gt;Log Audit Kualitas (quality_audit_logs)&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:10px;color:#fecdd3;'&gt;Rekam baris anomali (Critical &amp;amp; Warning Records)&lt;/span&gt;", style="shape=partialRectangle;right=0;left=0;fillColor=#4c0519;strokeColor=#fb7185;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;", vertex="1", parent="1")
    geo = ET.SubElement(d3, "mxGeometry", x="870", y="230", width="360", height="60")
    geo.set("as", "geometry")

    # D4: sesi_wajah_aktif.jpg (RAM / Temp)
    d4 = ET.SubElement(root_1, "mxCell", id="D4", value="&lt;b style='font-size:12px;'&gt;D4&lt;/b&gt; | &lt;b&gt;Kredensial Sesi (sesi_wajah_aktif.jpg / RAM)&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:10px;color:#fef08a;'&gt;Snapshot referensi wajah pemilik sesi sah (Zero-Trace)&lt;/span&gt;", style="shape=partialRectangle;right=0;left=0;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;", vertex="1", parent="1")
    geo = ET.SubElement(d4, "mxGeometry", x="1390", y="360", width="360", height="60")
    geo.set("as", "geometry")

    # D5: Repositori Paket Ekspor (src-export/)
    d5 = ET.SubElement(root_1, "mxCell", id="D5", value="&lt;b style='font-size:12px;'&gt;D5&lt;/b&gt; | &lt;b&gt;Repositori Ekspor (src-export/ &amp;amp; ZIP)&lt;/b&gt;&lt;br&gt;&lt;span style='font-size:10px;color:#bbf7d0;'&gt;Paket kompresi Clean CSV + Error CSV + Metadata&lt;/span&gt;", style="shape=partialRectangle;right=0;left=0;fillColor=#064e3b;strokeColor=#34d399;strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;", vertex="1", parent="1")
    geo = ET.SubElement(d5, "mxGeometry", x="870", y="870", width="360", height="60")
    geo.set("as", "geometry")

    # -----------------------------
    # 6 SUB-PROSES DFD LEVEL 1
    # -----------------------------
    # 1.0 Inisialisasi & Verifikasi Biometrik Awal
    p1 = ET.SubElement(root_1, "mxCell", id="P1", value="&lt;b style='font-size:13px;color:#93c5fd;'&gt;1.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Inisialisasi &amp;amp; Verifikasi&lt;br&gt;Biometrik Awal&lt;/b&gt;&lt;hr style='border-color:#1e3a8a;'&gt;&lt;div style='font-size:10px;'&gt;• cv2 Headless Capture&lt;br&gt;• MediaPipe Wajah: 0, 1, &amp;gt;1&lt;br&gt;• Simpan snapshot pemilik sah&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#172554;strokeColor=#2563eb;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p1, "mxGeometry", x="1440", y="160", width="260", height="120")
    geo.set("as", "geometry")

    # 2.0 Ingesti, Normalisasi & Evaluasi Kualitas Data
    p2 = ET.SubElement(root_1, "mxCell", id="P2", value="&lt;b style='font-size:13px;color:#a7f3d0;'&gt;2.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Ingesti, Normalisasi &amp;amp;&lt;br&gt;Audit Kualitas Data&lt;/b&gt;&lt;hr style='border-color:#047857;'&gt;&lt;div style='font-size:10px;'&gt;• Auto-Scan &amp;amp; PyArrow Ingestion&lt;br&gt;• Mapping 48 variabel resmi BPS&lt;br&gt;• Quality Check: Valid/Warning/Critical&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p2, "mxGeometry", x="420", y="230", width="280", height="130")
    geo.set("as", "geometry")

    # 3.0 Pemrosesan Analitik, Dynamic Filtering & Masking PII
    p3 = ET.SubElement(root_1, "mxCell", id="P3", value="&lt;b style='font-size:13px;color:#ddd6fe;'&gt;3.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Pemrosesan Analitik,&lt;br&gt;Filtering &amp;amp; PII Masking&lt;/b&gt;&lt;hr style='border-color:#6d28d9;'&gt;&lt;div style='font-size:10px;'&gt;• DuckDB Memory-Mapped Slicing (&amp;lt;0.05s)&lt;br&gt;• PII Masking NIK/Nama/Gaji/Alamat&lt;br&gt;• Instant KPI Cards &amp;amp; Micro-Table Grid&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#2e1065;strokeColor=#8b5cf6;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p3, "mxGeometry", x="420", y="520", width="280", height="140")
    geo.set("as", "geometry")

    # 4.0 Pemantauan Keamanan Real-Time (Biometric Guard Loop)
    p4 = ET.SubElement(root_1, "mxCell", id="P4", value="&lt;b style='font-size:13px;color:#fed7aa;'&gt;4.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Pemantauan Keamanan Real-Time&lt;br&gt;(Biometric Guard Loop 0.2s)&lt;/b&gt;&lt;hr style='border-color:#b45309;'&gt;&lt;div style='font-size:10px;'&gt;• Face Mesh &amp;amp; Iris Eye-Tracking (CPU&amp;lt;3%)&lt;br&gt;• Deteksi User Hilang &amp;gt; 4 Detik&lt;br&gt;• Deteksi Shoulder Surfing &amp;gt; 1 Detik&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#451a03;strokeColor=#f59e0b;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p4, "mxGeometry", x="1420", y="520", width="300", height="140")
    geo.set("as", "geometry")

    # 5.0 Pengelolaan Ekspor & Pengarsipan Data
    p5 = ET.SubElement(root_1, "mxCell", id="P5", value="&lt;b style='font-size:13px;color:#a7f3d0;'&gt;5.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Pengelolaan Ekspor &amp;amp;&lt;br&gt;Pengarsipan Data&lt;/b&gt;&lt;hr style='border-color:#047857;'&gt;&lt;div style='font-size:10px;'&gt;• Packaging CsvExportService&lt;br&gt;• Kompresi ZIP: Clean + Error + Metadata&lt;br&gt;• Stream Berkas Unduhan ke Operator&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#064e3b;strokeColor=#10b981;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p5, "mxGeometry", x="420", y="840", width="280", height="120")
    geo.set("as", "geometry")

    # 6.0 Terminasi Sistem & Pembersihan Jejak (Zero-Trace Purge)
    p6 = ET.SubElement(root_1, "mxCell", id="P6", value="&lt;b style='font-size:13px;color:#fecdd3;'&gt;6.0&lt;/b&gt;&lt;br&gt;&lt;b style='font-size:14px;'&gt;Terminasi Sistem &amp;amp;&lt;br&gt;Zero-Trace Purge&lt;/b&gt;&lt;hr style='border-color:#be123c;'&gt;&lt;div style='font-size:10px;'&gt;• Tangkap SIGTERM / atexit / Close App&lt;br&gt;• Secure Wipe 0x00 sesi_wajah_aktif.jpg&lt;br&gt;• Unlink &amp;amp; Flush RAM Cache&lt;/div&gt;", style="rounded=1;arcSize=25;whiteSpace=wrap;html=1;fillColor=#4c0519;strokeColor=#f43f5e;strokeWidth=2;fontColor=#ffffff;verticalAlign=top;spacingTop=8;", vertex="1", parent="1")
    geo = ET.SubElement(p6, "mxGeometry", x="1430", y="840", width="280", height="120")
    geo.set("as", "geometry")

    # -----------------------------
    # DATA FLOWS LEVEL 1
    # -----------------------------
    # Proses 1.0 (Biometrik Awal)
    add_flow(root_1, "f1_1", "Raw Video Frame", "E2_1", "P1", "#f59e0b", "#b45309", exitX="0", exitY="0.3", entryX="1", entryY="0.3")
    add_flow(root_1, "f1_2", "Simpan Sesi Wajah Sah (1 Wajah)", "P1", "D4", "#f59e0b", "#b45309", exitX="0.5", exitY="1", entryX="0.5", entryY="0")
    add_flow(root_1, "f1_3", "Status Autentikasi / Tolak", "P1", "E1_1", "#3b82f6", "#1d4ed8", exitX="0", exitY="0.8", entryX="1", entryY="0.1", curved=1)

    # Proses 2.0 (Ingesti)
    add_flow(root_1, "f2_1", "Letakkan Berkas Mentah", "E1_1", "D1", "#0284c7", "#0369a1", exitX="1", exitY="0.05", entryX="0", entryY="0.5")
    add_flow(root_1, "f2_2", "Baca Berkas CSV / XLSX", "D1", "P2", "#0284c7", "#0369a1", exitX="0.5", exitY="1", entryX="0.5", entryY="0")
    add_flow(root_1, "f2_3", "Trigger Impor (POST /import-local)", "E1_1", "P2", "#0284c7", "#0369a1", exitX="1", exitY="0.22", entryX="0", entryY="0.5")
    add_flow(root_1, "f2_4", "Batch Simpan Data Bersih", "P2", "D2", "#10b981", "#047857", exitX="1", exitY="0.75", entryX="0", entryY="0.2")
    add_flow(root_1, "f2_5", "Catat Rekam Anomali (Error Log)", "P2", "D3", "#fb7185", "#be123c", exitX="1", exitY="0.35", entryX="0", entryY="0.5")
    add_flow(root_1, "f2_6", "Status Ingesti &amp; Quality Count", "P2", "E1_1", "#10b981", "#047857", exitX="0", exitY="0.8", entryX="1", entryY="0.28")

    # Proses 3.0 (Analitik & Filter)
    add_flow(root_1, "f3_1", "Parameter Filter Dinamis &amp; Keyword", "E1_1", "P3", "#8b5cf6", "#6d28d9", exitX="1", exitY="0.45", entryX="0", entryY="0.3")
    add_flow(root_1, "f3_2", "Query Slicing Kolumnar (<0.05s)", "P3", "D2", "#8b5cf6", "#6d28d9", exitX="1", exitY="0.2", entryX="0", entryY="0.8")
    add_flow(root_1, "f3_3", "Hasil Dataset Terfilter", "D2", "P3", "#8b5cf6", "#6d28d9", exitX="0.2", exitY="1", entryX="0.8", entryY="0")
    add_flow(root_1, "f3_4", "Grid Data PII-Masked &amp; KPI Cards", "P3", "E1_1", "#8b5cf6", "#6d28d9", exitX="0", exitY="0.75", entryX="1", entryY="0.52")

    # Proses 4.0 (Biometric Guard Loop)
    add_flow(root_1, "f4_1", "Stream Frame Berkala (0.2s)", "E2_1", "P4", "#f59e0b", "#b45309", exitX="0.5", exitY="1", entryX="0.8", entryY="0")
    add_flow(root_1, "f4_2", "Baca Profil Wajah Sah Referensi", "D4", "P4", "#f59e0b", "#b45309", exitX="0.5", exitY="1", entryX="0.5", entryY="0")
    add_flow(root_1, "f4_3", "Kunci Layar (user32.LockWorkStation)", "P4", "E3_1", "#dc2626", "#991b1b", exitX="1", exitY="0.6", entryX="0", entryY="0.2")
    add_flow(root_1, "f4_4", "Red Lock Alert &amp; Blur UI Event", "P4", "E1_1", "#dc2626", "#991b1b", exitX="0", exitY="0.5", entryX="1", entryY="0.65", curved=1)

    # Proses 5.0 (Ekspor)
    add_flow(root_1, "f5_1", "Permintaan Ekspor Arsip ZIP", "E1_1", "P5", "#047857", "#065f46", exitX="1", exitY="0.78", entryX="0", entryY="0.3")
    add_flow(root_1, "f5_2", "Ambil Data Bersih Terpilih", "D2", "P5", "#047857", "#065f46", exitX="0.5", exitY="1", entryX="0.8", entryY="0")
    add_flow(root_1, "f5_3", "Ambil Data Error Audit", "D3", "P5", "#fb7185", "#9f1239", exitX="0.2", exitY="1", entryX="0.9", entryY="0")
    add_flow(root_1, "f5_4", "Simpan Paket ZIP (src-export/)", "P5", "D5", "#047857", "#065f46", exitX="1", exitY="0.6", entryX="0", entryY="0.5")
    add_flow(root_1, "f5_5", "Kirim File Unduhan ZIP ke User", "P5", "E1_1", "#047857", "#065f46", exitX="0", exitY="0.7", entryX="1", entryY="0.85")

    # Proses 6.0 (Terminasi & Purge)
    add_flow(root_1, "f6_1", "Perintah Tutup Aplikasi / Selesai", "E1_1", "P6", "#f43f5e", "#be123c", exitX="1", exitY="0.95", entryX="0", entryY="0.8", curved=1)
    add_flow(root_1, "f6_2", "Sinyal Shutdown / SIGTERM / atexit", "E3_1", "P6", "#6366f1", "#4338ca", exitX="0", exitY="0.6", entryX="1", entryY="0.5")
    add_flow(root_1, "f6_3", "Secure Overwrite (0x00) &amp; Hapus File", "P6", "D4", "#f43f5e", "#be123c", exitX="0.5", exitY="0", entryX="0.7", entryY="1")

    # Write tree to file
    tree = ET.ElementTree(mxfile)
    ET.indent(tree, space="  ", level=0)
    output_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\PADU_DFD.drawio"
    tree.write(output_path, encoding="utf-8", xml_declaration=True)
    print(f"Successfully generated {output_path}")

if __name__ == "__main__":
    create_dfd_drawio()
