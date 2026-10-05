import xml.sax.saxutils as saxutils

def create_superadmin_swimlane():
    out_path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\swimlane_tobe_superadmin.drawio"
    
    # Perfectly compact & proportioned dimensions:
    # 6 Lanes with widths: 270, 270, 280, 270, 280, 290 -> Total width = 1660px (+80px margin = 1740px)
    # Total height = 1430px (+80px margin = 1510px)
    # This guarantees that the entire diagram fits 100% inside 1 SINGLE PAGE canvas in Draw.io without crossing any grid/page boundaries!
    
    xml_content = '''<mxfile host="app.diagrams.net" agent="Antigravity" version="21.0.0" type="device">
  <diagram id="diagram_tobe_superadmin" name="TO-BE Enterprise: RBAC Super Admin + Biometric Guard + Dedicated Library">
    <mxGraphModel dx="1600" dy="1100" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1740" pageHeight="1510" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

        <!-- 6 LANES (TOTAL WIDTH 1660px, HEIGHT 1430px, FITS PERFECTLY INSIDE 1 PAGE) -->
        <!-- Lane 1: Operator Data -->
        <mxCell id="lane_1" value="👤 OPERATOR DATA (OPD)" style="swimlane;html=1;startSize=40;fillColor=#1e293b;strokeColor=#334155;fontColor=#ffffff;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="40" y="40" width="270" height="1430" as="geometry" />
        </mxCell>
        <!-- Lane 2: Super Admin -->
        <mxCell id="lane_2" value="👑 SUPER ADMIN (BAPPENAS/BPS)" style="swimlane;html=1;startSize=40;fillColor=#713f12;strokeColor=#ca8a04;fontColor=#fef08a;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="310" y="40" width="270" height="1430" as="geometry" />
        </mxCell>
        <!-- Lane 3: Frontend UI -->
        <mxCell id="lane_3" value="💻 FRONTEND UI (MIKRO &amp; STATISTIK)" style="swimlane;html=1;startSize=40;fillColor=#0f172a;strokeColor=#1e3a8a;fontColor=#93c5fd;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="580" y="40" width="280" height="1430" as="geometry" />
        </mxCell>
        <!-- Lane 4: Backend Gateway & RBAC -->
        <mxCell id="lane_4" value="⚙️ BACKEND SERVICES &amp; RBAC" style="swimlane;html=1;startSize=40;fillColor=#064e3b;strokeColor=#047857;fontColor=#a7f3d0;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="860" y="40" width="270" height="1430" as="geometry" />
        </mxCell>
        <!-- Lane 5: Python AI & Ingest -->
        <mxCell id="lane_5" value="🧠 PYTHON AI &amp; INGEST ENGINE" style="swimlane;html=1;startSize=40;fillColor=#78350f;strokeColor=#b45309;fontColor=#fde68a;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="1130" y="40" width="280" height="1430" as="geometry" />
        </mxCell>
        <!-- Lane 6: Storage Layer -->
        <mxCell id="lane_6" value="🗄️ STORAGE LAYER (DUCKDB &amp; ZERO-TRACE)" style="swimlane;html=1;startSize=40;fillColor=#3b0764;strokeColor=#6b21a8;fontColor=#f5d0fe;fontSize=13;fontStyle=1;align=center;" vertex="1" parent="1">
          <mxGeometry x="1410" y="40" width="290" height="1430" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 1: STARTUP & VALIDASI BIOMETRIK     -->
        <!-- ========================================== -->
        <mxCell id="SA_A1" value="&lt;b&gt;Start: Jalankan PADU (.pyw)&lt;/b&gt;&lt;br&gt;Inisialisasi sistem 100% offline" style="rounded=1;arcSize=50;whiteSpace=wrap;html=1;fillColor=#2563eb;strokeColor=#1d4ed8;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="65" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_D1" value="&lt;b&gt;Silent Headless Camera Capture&lt;/b&gt;&lt;br&gt;OpenCV capture 1 frame di RAM" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#b45309;strokeColor=#f59e0b;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="60" width="240" height="60" as="geometry" />
        </mxCell>
        <mxCell id="SA_D2" value="&lt;b&gt;Validasi Wajah Tunggal&lt;/b&gt;&lt;br&gt;MediaPipe: Tepat 1 wajah sah?" style="rhombus;whiteSpace=wrap;html=1;fillColor=#d97706;strokeColor=#b45309;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="145" width="240" height="75" as="geometry" />
        </mxCell>
        <mxCell id="SA_E1" value="&lt;b&gt;Simpan sesi_wajah_aktif.jpg&lt;/b&gt;&lt;br&gt;Kunci sesi pemilik sah di RAM" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#581c87;strokeColor=#7e22ce;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_6">
          <mxGeometry x="20" y="157" width="250" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 2: RBAC GATEWAY & AUTHENTICATION    -->
        <!-- ========================================== -->
        <mxCell id="SA_B1" value="&lt;b&gt;Render Login Dialog / Auth Form&lt;/b&gt;&lt;br&gt;Input kredensial pengguna" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#1e3a8a;strokeColor=#2563eb;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_3">
          <mxGeometry x="20" y="245" width="240" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_C1" value="&lt;b&gt;RBAC Middleware &amp; Role Check&lt;/b&gt;&lt;br&gt;Cek role: 'super_admin' vs 'operator'?" style="rhombus;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_4">
          <mxGeometry x="20" y="315" width="230" height="75" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 3: DEDICATED SUPER ADMIN LIBRARY    -->
        <!-- ========================================== -->
        <mxCell id="SA_A2_ADMIN" value="&lt;b&gt;Super Admin Login Berhasil&lt;/b&gt;&lt;br&gt;Masuk ke panel tata kelola metadata" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#ca8a04;strokeColor=#a16207;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_2">
          <mxGeometry x="20" y="415" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_B2_ADMIN" value="&lt;b&gt;Dedicated Library Management&lt;/b&gt;&lt;br&gt;CRUD Kamus Kode &amp; Aturan&lt;br&gt;&lt;i&gt;(Hanya Akses Super Admin)&lt;/i&gt;" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#854d0e;strokeColor=#ca8a04;fontColor=#fef08a;fontSize=11;" vertex="1" parent="lane_3">
          <mxGeometry x="20" y="410" width="240" height="60" as="geometry" />
        </mxCell>
        <mxCell id="SA_A3_ADMIN" value="&lt;b&gt;Input Pembaruan Kamus Metadata&lt;/b&gt;&lt;br&gt;Opsi baru kategori air/lantai/bansos" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#ca8a04;strokeColor=#a16207;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_2">
          <mxGeometry x="20" y="495" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_C2_ADMIN" value="&lt;b&gt;Compatibility &amp; Schema Validator&lt;/b&gt;&lt;br&gt;Update referensi tanpa ubah tabel fisik" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_4">
          <mxGeometry x="20" y="495" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_E2_ADMIN" value="&lt;b&gt;Update metadata_libraries &amp; Log&lt;/b&gt;&lt;br&gt;Simpan kode baru &amp; catat audit trail" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#581c87;strokeColor=#7e22ce;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_6">
          <mxGeometry x="20" y="495" width="250" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_D3_ADMIN" value="&lt;b&gt;Dynamic Hot-Reload Transliterasi&lt;/b&gt;&lt;br&gt;Injeksi kamus seketika ke memory" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="570" width="240" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 4: OPERATOR INGESTION & DATA FLOW   -->
        <!-- ========================================== -->
        <mxCell id="SA_A2_OPD" value="&lt;b&gt;Operator OPD Login Berhasil&lt;/b&gt;&lt;br&gt;Masuk ke Dashboard Analisis Data" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#334155;strokeColor=#475569;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="645" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_A3_OPD" value="&lt;b&gt;Pilih File CSV &amp; Trigger Ingesti&lt;/b&gt;&lt;br&gt;Dataset 13M+ baris diproses lokal" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#334155;strokeColor=#475569;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="720" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_D4_OPD" value="&lt;b&gt;Fast Ingest PyArrow &amp; Quality Check&lt;/b&gt;&lt;br&gt;Evaluasi Valid, Warning, Critical" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="720" width="240" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_E3_OPD" value="&lt;b&gt;Simpan dtsen_data &amp; Audit Logs&lt;/b&gt;&lt;br&gt;Data kolumnar DuckDB siap query" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#581c87;strokeColor=#7e22ce;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_6">
          <mxGeometry x="20" y="720" width="250" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 5: DUAL-MODULE (MIKRO & STATISTIK)  -->
        <!-- ========================================== -->
        <mxCell id="SA_B3" value="&lt;b&gt;Render Dual-Module Navigation UI&lt;/b&gt;&lt;br&gt;Pilihan Tab: Data Mikro vs Statistik" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#1e3a8a;strokeColor=#2563eb;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_3">
          <mxGeometry x="20" y="795" width="240" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_A4" value="&lt;b&gt;Input Filter Dinamis &amp; Pilih 7 Metrik&lt;/b&gt;&lt;br&gt;Filter NIK, Desil &amp; Agregasi Wilayah" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#334155;strokeColor=#475569;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="870" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_C3" value="&lt;b&gt;Transliterasi FK &amp; PII Masking&lt;/b&gt;&lt;br&gt;Decode kode ke label &amp; sensor NIK/Nama" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_4">
          <mxGeometry x="20" y="870" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_D5" value="&lt;b&gt;DuckDB Vector Slicing &amp; 7 Metrik&lt;/b&gt;&lt;br&gt;COUNT, SUM, AVG, MIN, MAX, MED, MOD" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#581c87;strokeColor=#7e22ce;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_6">
          <mxGeometry x="20" y="870" width="250" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_B4" value="&lt;b&gt;Sajian Data Mikro &amp; Rekap Wilayah&lt;/b&gt;&lt;br&gt;Tampilan rapi sesuai aturan kamus" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#1e3a8a;strokeColor=#2563eb;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_3">
          <mxGeometry x="20" y="945" width="240" height="50" as="geometry" />
        </mxCell>

        <!-- Ekspor ZIP -->
        <mxCell id="SA_A5" value="&lt;b&gt;Ekspor Paket Arsip ZIP Terkompresi&lt;/b&gt;&lt;br&gt;Unduh Clean CSV + Error CSV + Meta" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#334155;strokeColor=#475569;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="1025" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_C4" value="&lt;b&gt;Packaging &amp; Compression Engine&lt;/b&gt;&lt;br&gt;Stream arsip ZIP langsung ke browser" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#065f46;strokeColor=#059669;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_4">
          <mxGeometry x="20" y="1025" width="230" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 6: BIOMETRIC GUARD & PC LOCKOUT     -->
        <!-- ========================================== -->
        <mxCell id="SA_D6" value="&lt;b&gt;Guard Loop 0.2s: Anti-Surfing&lt;/b&gt;&lt;br&gt;User pergi &gt; 4s / diintip &gt; 1s?" style="rhombus;whiteSpace=wrap;html=1;fillColor=#d97706;strokeColor=#b45309;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="1105" width="240" height="75" as="geometry" />
        </mxCell>
        <mxCell id="SA_C5" value="&lt;b&gt;user32.LockWorkStation()&lt;/b&gt;&lt;br&gt;Kunci Layar Windows (Win+L)" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#991b1b;strokeColor=#dc2626;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_4">
          <mxGeometry x="20" y="1117" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_B5" value="&lt;b&gt;Red Lock Alert &amp; Blur Screen UI&lt;/b&gt;&lt;br&gt;Data PII aman dari pihak luar" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#991b1b;strokeColor=#dc2626;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_3">
          <mxGeometry x="20" y="1117" width="240" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- BAGIAN 7: SHUTDOWN & ZERO-TRACE PURGE      -->
        <!-- ========================================== -->
        <mxCell id="SA_A6" value="&lt;b&gt;Tutup Aplikasi / Selesai Sesi&lt;/b&gt;&lt;br&gt;Trigger penutupan aman OS" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#334155;strokeColor=#475569;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_1">
          <mxGeometry x="20" y="1215" width="230" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_D7" value="&lt;b&gt;Signal atexit / SIGTERM&lt;/b&gt;&lt;br&gt;Tangkap event shutdown sistem" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#991b1b;strokeColor=#dc2626;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_5">
          <mxGeometry x="20" y="1215" width="240" height="50" as="geometry" />
        </mxCell>
        <mxCell id="SA_E4" value="&lt;b&gt;Zero-Trace Secure Wipe (0x00)&lt;/b&gt;&lt;br&gt;Timpa 0x00 &amp; hapus permanen foto wajah" style="rounded=1;whiteSpace=wrap;html=1;fillColor=#581c87;strokeColor=#7e22ce;fontColor=#ffffff;fontSize=11;" vertex="1" parent="lane_6">
          <mxGeometry x="20" y="1215" width="250" height="50" as="geometry" />
        </mxCell>

        <!-- ========================================== -->
        <!-- CONNECTORS (ORTHOGONAL & ROUNDED)          -->
        <!-- ========================================== -->
        <!-- Start & Bio -->
        <mxCell id="c_1" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A1" target="SA_D1"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_2" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#fbbf24;strokeWidth=2;" edge="1" parent="1" source="SA_D1" target="SA_D2"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_3" value="1 Wajah Sah" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#34d399;strokeWidth=2;fontColor=#34d399;" edge="1" parent="1" source="SA_D2" target="SA_E1"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_4" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_E1" target="SA_B1"><mxGeometry relative="1" as="geometry" /></mxCell>

        <!-- Login to RBAC -->
        <mxCell id="c_5" value="Kredensial" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#34d399;strokeWidth=2;" edge="1" parent="1" source="SA_B1" target="SA_C1"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_6_admin" value="Role: super_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ca8a04;strokeWidth=2.5;fontColor=#ca8a04;" edge="1" parent="1" source="SA_C1" target="SA_A2_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_6_opd" value="Role: operator" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;fontColor=#38bdf8;" edge="1" parent="1" source="SA_C1" target="SA_A2_OPD"><mxGeometry relative="1" as="geometry" /></mxCell>

        <!-- Admin Path -->
        <mxCell id="c_7_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ca8a04;strokeWidth=2;" edge="1" parent="1" source="SA_A2_ADMIN" target="SA_B2_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_8_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ca8a04;strokeWidth=2;" edge="1" parent="1" source="SA_B2_ADMIN" target="SA_A3_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_9_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ca8a04;strokeWidth=2;" edge="1" parent="1" source="SA_A3_ADMIN" target="SA_C2_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_10_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ca8a04;strokeWidth=2;" edge="1" parent="1" source="SA_C2_ADMIN" target="SA_E2_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_11_admin" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#059669;strokeWidth=2;dashed=1;" edge="1" parent="1" source="SA_E2_ADMIN" target="SA_D3_ADMIN"><mxGeometry relative="1" as="geometry" /></mxCell>

        <!-- OPD Ingest & Analytics -->
        <mxCell id="c_12_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A2_OPD" target="SA_A3_OPD"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_13_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A3_OPD" target="SA_D4_OPD"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_14_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#c084fc;strokeWidth=2;" edge="1" parent="1" source="SA_D4_OPD" target="SA_E3_OPD"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_15_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_E3_OPD" target="SA_B3"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_16_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_B3" target="SA_A4"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_17_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A4" target="SA_C3"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_18_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#c084fc;strokeWidth=2;" edge="1" parent="1" source="SA_C3" target="SA_D5"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_19_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_D5" target="SA_B4"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_20_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_B4" target="SA_A5"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_21_opd" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#34d399;strokeWidth=2;" edge="1" parent="1" source="SA_A5" target="SA_C4"><mxGeometry relative="1" as="geometry" /></mxCell>

        <!-- Guard loop -->
        <mxCell id="c_22" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#fbbf24;strokeWidth=2;" edge="1" parent="1" source="SA_B4" target="SA_D6"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_23" value="Ancaman" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ef4444;strokeWidth=2;fontColor=#f87171;" edge="1" parent="1" source="SA_D6" target="SA_C5"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_24" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#ef4444;strokeWidth=2;" edge="1" parent="1" source="SA_C5" target="SA_B5"><mxGeometry relative="1" as="geometry" /></mxCell>

        <!-- Shutdown -->
        <mxCell id="c_25" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A5" target="SA_A6"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_26" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#38bdf8;strokeWidth=2;" edge="1" parent="1" source="SA_A6" target="SA_D7"><mxGeometry relative="1" as="geometry" /></mxCell>
        <mxCell id="c_27" style="edgeStyle=orthogonalEdgeStyle;rounded=1;html=1;strokeColor=#c084fc;strokeWidth=2;" edge="1" parent="1" source="SA_D7" target="SA_E4"><mxGeometry relative="1" as="geometry" /></mxCell>

      </root>
    </mxGraphModel>
  </diagram>
</mxfile>'''

    with open(out_path, "w", encoding="utf-8") as f:
        f.write(xml_content)
    print(f"Generated separate swimlane_tobe_superadmin.drawio at {out_path}!")

if __name__ == "__main__":
    create_superadmin_swimlane()
