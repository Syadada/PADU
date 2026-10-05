import os
import sys
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def set_cell_background(cell, fill_hex):
    tcPr = cell._element.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
    tcPr = cell._element.get_or_add_tcPr()
    tcMar = parse_xml(f'''
        <w:tcMar {nsdecls("w")}>
            <w:top w:w="{top}" w:type="dxa"/>
            <w:bottom w:w="{bottom}" w:type="dxa"/>
            <w:left w:w="{left}" w:type="dxa"/>
            <w:right w:w="{right}" w:type="dxa"/>
        </w:tcMar>
    ''')
    tcPr.append(tcMar)

def set_table_borders(table, color="CBD5E1", sz="4", val="single"):
    tblPr = table._element.xpath('w:tblPr')
    if tblPr:
        borders = parse_xml(f'''
            <w:tblBorders {nsdecls("w")}>
                <w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>
                <w:insideV w:val="none"/>
                <w:left w:val="none"/>
                <w:right w:val="none"/>
            </w:tblBorders>
        ''')
        tblPr[0].append(borders)

def add_callout(doc, text, title="CATATAN KEAMANAN & REGULASI BSSN", border_color="102C57", bg_color="F1F5F9"):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, bg_color)
    set_cell_margins(cell, top=140, bottom=140, left=200, right=180)
    
    tcPr = cell._element.get_or_add_tcPr()
    borders = parse_xml(f'''
        <w:tcBorders {nsdecls("w")}>
            <w:left w:val="single" w:sz="36" w:space="0" w:color="{border_color}"/>
            <w:top w:val="none"/>
            <w:right w:val="none"/>
            <w:bottom w:val="none"/>
        </w:tcBorders>
    ''')
    tcPr.append(borders)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(4)
    run_title = p.add_run(f"📌 {title}\n")
    run_title.font.name = 'Arial'
    run_title.font.size = Pt(10)
    run_title.font.bold = True
    run_title.font.color.rgb = RGBColor(16, 44, 87)
    
    run_text = p.add_run(text)
    run_text.font.name = 'Arial'
    run_text.font.size = Pt(9.5)
    run_text.font.color.rgb = RGBColor(40, 40, 40)
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_before = Pt(0)
    p_after.paragraph_format.space_after = Pt(6)

def add_styled_heading(doc, text, level):
    h = doc.add_heading(level=level)
    run = h.add_run(text)
    run.font.name = 'Arial'
    if level == 1:
        run.font.size = Pt(13.5)
        run.font.bold = True
        run.font.color.rgb = RGBColor(16, 44, 87) # Deep Navy
        h.paragraph_format.space_before = Pt(16)
        h.paragraph_format.space_after = Pt(6)
    elif level == 2:
        run.font.size = Pt(11.5)
        run.font.bold = True
        run.font.color.rgb = RGBColor(30, 80, 130) # Slate Navy
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
    elif level == 3:
        run.font.size = Pt(10)
        run.font.bold = True
        run.font.color.rgb = RGBColor(50, 50, 50)
        h.paragraph_format.space_before = Pt(8)
        h.paragraph_format.space_after = Pt(3)
    return h

def add_paragraph_styled(doc, text, bold_prefix="", space_after=5, italic=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        r_pre = p.add_run(bold_prefix)
        r_pre.font.name = 'Arial'
        r_pre.font.size = Pt(10)
        r_pre.font.bold = True
        r_pre.font.color.rgb = RGBColor(20, 20, 20)
    r_body = p.add_run(text)
    r_body.font.name = 'Arial'
    r_body.font.size = Pt(10)
    r_body.font.italic = italic
    r_body.font.color.rgb = RGBColor(40, 40, 40)
    return p

def create_full_proposal_docx(output_path):
    doc = Document()
    
    # Setup Page Margins: 1 inch (2.54 cm)
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)
        section.different_first_page_header_footer = True
        
        # Header / Footer
        header = section.header
        p_hdr = header.paragraphs[0]
        p_hdr.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        r_hdr = p_hdr.add_run("PROPOSAL TEKNIS PEMBARUAN PADU v2.0 ENTERPRISE | STANDAR BSSN & UU PDP | SANGAT RAHASIA")
        r_hdr.font.name = 'Arial'
        r_hdr.font.size = Pt(8)
        r_hdr.font.color.rgb = RGBColor(140, 140, 140)
        
        footer = section.footer
        p_ftr = footer.paragraphs[0]
        p_ftr.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r_ftr = p_ftr.add_run("Sistem Informasi PADU v2.0 Enterprise — Dokumen Usulan Resmi Lepas Kunci (Zero-Knowledge)")
        r_ftr.font.name = 'Arial'
        r_ftr.font.size = Pt(8)
        r_ftr.font.color.rgb = RGBColor(140, 140, 140)

    # =========================================================
    # COVER PAGE
    # =========================================================
    p_inst = doc.add_paragraph()
    p_inst.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_inst.paragraph_format.space_before = Pt(30)
    p_inst.paragraph_format.space_after = Pt(6)
    r_inst = p_inst.add_run("PROPOSAL TEKNIS PENGEMBANGAN & PEMBARUAN SISTEM LENGKAP (A - Z)")
    r_inst.font.name = 'Arial'
    r_inst.font.size = Pt(11)
    r_inst.font.bold = True
    r_inst.font.color.rgb = RGBColor(100, 116, 139)

    p_main_title = doc.add_paragraph()
    p_main_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_main_title.paragraph_format.space_before = Pt(8)
    p_main_title.paragraph_format.space_after = Pt(8)
    r_title = p_main_title.add_run("SISTEM INFORMASI PADU v2.0 ENTERPRISE\n(Pengolah & Analisis Data Terpadu)")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(21)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(16, 44, 87)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_before = Pt(6)
    p_sub.paragraph_format.space_after = Pt(24)
    r_sub = p_sub.add_run("Cetak Biru Komprehensif Arsitektur Keamanan Berlapis, Kedaulatan Mandiri Klien (Zero-Knowledge),\nDiselaraskan Penuh dengan Regulasi Badan Siber dan Sandi Negara (BSSN) & UU No. 27/2022 (UU PDP),\nOtentikasi Bertahap, 2FA Offline Cerdas, Immutable Audit Trail, dan Proteksi Arsip ZIP AES-256")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(10)
    r_sub.font.italic = True
    r_sub.font.color.rgb = RGBColor(53, 89, 143)

    # Box Metadata Cover
    tbl_meta = doc.add_table(rows=8, cols=2)
    tbl_meta.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_meta, color="E2E8F0")
    
    meta_data = [
        ("Nomor Dokumen", "PROP/PADU-SEC/IX/2026/V2.0-FINAL-BSSN"),
        ("Klasifikasi Dokumen", "SANGAT RAHASIA (Strictly Confidential / Dokumen Internal Klien)"),
        ("Versi Rilis Arsitektur", "v2.0 Enterprise (Rilis Kedaulatan Mandiri Lepas Kunci)"),
        ("Standar Kepatuhan", "Per BSSN No. 4/2021, No. 11/2024, No. 10/2020, No. 8/2020 & UU PDP"),
        ("Tanggal Pembaruan", "27 September 2026"),
        ("Penyusun Dokumen", "Lead System Architect & Developer Sistem PADU"),
        ("Sasaran Pengesahan", "Pimpinan Proyek & Super Admin (Klien)"),
        ("Status Pelaksanaan", "Usulan Rencana Kerja Resmi Siap Eksekusi (Action-Ready)")
    ]
    
    for idx, (label, val) in enumerate(meta_data):
        row = tbl_meta.rows[idx]
        c0, c1 = row.cells[0], row.cells[1]
        c0.width = Inches(2.3)
        c1.width = Inches(4.2)
        set_cell_background(c0, "F8FAFC")
        set_cell_background(c1, "FFFFFF")
        set_cell_margins(c0, top=80, bottom=80, left=130, right=130)
        set_cell_margins(c1, top=80, bottom=80, left=130, right=130)
        
        p0 = c0.paragraphs[0]
        p0.paragraph_format.space_before = Pt(2); p0.paragraph_format.space_after = Pt(2)
        r0 = p0.add_run(label)
        r0.font.name = 'Arial'; r0.font.size = Pt(9.5); r0.font.bold = True
        r0.font.color.rgb = RGBColor(51, 65, 85)
        
        p1 = c1.paragraphs[0]
        p1.paragraph_format.space_before = Pt(2); p1.paragraph_format.space_after = Pt(2)
        r1 = p1.add_run(val)
        r1.font.name = 'Arial'; r1.font.size = Pt(9.5)
        r1.font.color.rgb = RGBColor(15, 23, 42)

    doc.add_page_break()

    # =========================================================
    # BAB 1: LATAR BELAKANG, URGENSI & KEDAULATAN SISTEM
    # =========================================================
    add_styled_heading(doc, "BAB 1: LATAR BELAKANG, URGENSI & KEDAULATAN SISTEM v2.0", level=1)
    
    add_paragraph_styled(doc, 
        "Sistem Informasi PADU (Pengolah & Analisis Data Terpadu) pada implementasi operasional awal telah sukses mendemonstrasikan keandalan mesin analitik OLAP DuckDB yang dipadukan dengan framework web Laravel 12. Sistem terbukti mampu melakukan streaming ingestion lebih dari 1,5 juta baris data per detik serta kalkulasi 7 metrik statistik regional secara instan.")

    add_paragraph_styled(doc,
        "Dalam rangka membawa sistem ke tingkat Enterprise Production (v2.0) di lingkungan tertutup kantor klien, seluruh pemangku kepentingan telah menyepakati penyempurnaan mendasar terhadap aspek keamanan, privasi data kependudukan, dan tata kelola kepemilikan yang diselaraskan secara ketat dengan regulasi Badan Siber dan Sandi Negara (BSSN):")

    add_paragraph_styled(doc,
        "Setelah Berita Acara Serah Terima (BAST) ditandatangani, tim pengembang 100% lepas tangan. Tidak ada pintu belakang (backdoor), tidak ada ketergantungan lisensi cloud atau serial berkala yang mengharuskan klien menghubungi pengembang di masa mendatang. Kedaulatan penuh berada di tangan Super Admin Klien.",
        bold_prefix="1. Serah Terima Lepas Kunci (Zero-Knowledge Turnkey Handover): ")

    add_paragraph_styled(doc,
        "Modul pengawasan webcam OpenCV, loop komputasi wajah 0.2 detik, dan pemaksaan lock screen Windows OS dieliminasi total. Sistem bertransformasi menjadi aplikasi web murni (clean web app) yang sangat stabil, efisien, dan dapat diakses bersama secara simultan melalui jaringan Wi-Fi/LAN kantor tanpa membutuhkan internet.",
        bold_prefix="2. Arsitektur Web Murni Tanpa Biometrik: ")

    add_paragraph_styled(doc,
        "Seluruh rancangan pengamanan data, manajemen kata sandi, otentikasi multi-faktor, pencatatan log audit forensik, dan manajemen sesi dikonstruksi memenuhi ambang batas ketentuan teknis pemerintah Indonesia yang ditetapkan BSSN dan UU PDP.",
        bold_prefix="3. Kepatuhan Penuh Regulasi Siber BSSN & UU PDP: ")

    add_paragraph_styled(doc,
        "Sistem menerapkan urutan otentikasi bertahap: Layar pertama meminta email, layar kedua mendeteksi role pengguna dan meminta kata sandi (min. 15 karakter). Penanganan lupa kata sandi dibedakan secara tegas: Super Admin menggunakan 2FA Offline HP pribadi, sedangkan Operator diarahkan menghubungi Super Admin.",
        bold_prefix="4. Alur Otentikasi Bertahap (Email-First Login): ")

    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi (ZIP) dikunci menggunakan standar enkripsi AES-256 dengan password 20 karakter acak tingkat tinggi. Setiap kali berkas ZIP diekstrak atau dibuka untuk pemulihan, sistem secara otomatis menghanguskan password lama dan mereset password 20 karakter baru seketika, didukung Master Key fisik di brankas Pimpinan.",
        bold_prefix="5. Proteksi Arsip ZIP Aplikasi (AES-256 Password 20 Karakter Auto-Reset): ")

    add_callout(doc,
        "Pembaruan PADU v2.0 Enterprise mengintegrasikan seluruh prinsip Kedaulatan Mandiri, Standar Teknis BSSN (Per BSSN No. 4/2021 & No. 11/2024), serta Perlindungan Data Pribadi (UU PDP No. 27/2022) sehingga sistem berstatus 100% Audit-Ready bagi instansi pemerintah/klien.",
        title="FILOSOFI KEDAULATAN & KEPATUHAN SIBER NASIONAL")

    # =========================================================
    # BAB 2: TUJUAN, SASARAN STRATEGIS & KEPATUHAN BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 2: TUJUAN, SASARAN STRATEGIS & KEPATUHAN REGULASI BSSN", level=1)

    add_styled_heading(doc, "2.1. Landasan Hukum & Regulasi Keamanan Informasi", level=2)
    add_paragraph_styled(doc, "Sistem PADU v2.0 mengacu pada instrumen hukum dan standar teknis siber berikut:")

    tbl_reg = doc.add_table(rows=6, cols=3)
    tbl_reg.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_reg, color="CBD5E1")
    
    reg_headers = ["Regulasi / Standar", "Ruang Lingkup Acuan", "Implementasi Teknis di PADU v2.0"]
    for i, h in enumerate(reg_headers):
        cell = tbl_reg.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)

    reg_data = [
        ("Peraturan BSSN No. 4 Tahun 2021", "Standar Teknis Keamanan SPBE & Aplikasi Web", "Sandi min 15 char, Account Lockout (5x salah), Inactivity Timeout 15 menit, dan Password aging 180 hari."),
        ("Peraturan BSSN No. 11 Tahun 2024", "Penyelenggaraan Kriptografi & Penilaian Modul", "Enkripsi kolom AES-256-CBC dipadu HMAC-SHA256 (Encrypt-then-MAC) & Enkripsi ZIP AES-256."),
        ("Peraturan BSSN No. 10 Tahun 2020", "Pengamanan Pengadaan & Pengembangan Perangkat Lunak (SSDLC)", "Pemisahan total kode vs data riil warga; developer hanya menerima 10.000 data sintetis dummy."),
        ("Peraturan BSSN No. 8 Tahun 2020", "Sistem Pengamanan Penyelenggara Sistem Elektronik", "5 Pilar: Confidentiality (AES-256), Integrity (SHA-256), Availability (DRP SOP), Non-Repudiation (Audit Log)."),
        ("UU No. 27 Tahun 2022 (UU PDP)", "Perlindungan Data Pribadi Spesifik Kependudukan", "Enkripsi data kependudukan (NIK, Nama, Alamat, Finansial), RBAC strict, dan pemusnahan kunci darurat.")
    ]

    for idx, (reg, scope, impl) in enumerate(reg_data):
        row = tbl_reg.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([reg, scope, impl]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=70, bottom=70, left=100, right=100)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(8.5)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    add_styled_heading(doc, "2.2. Sasaran Strategis Sistem", level=2)
    goals = [
        ("Kemandirian Sistem 100% (Zero-Knowledge)", "Menjamin sistem dapat beroperasi, dicadangkan, dan dipulihkan seutuhnya oleh Klien tanpa ketergantungan pihak ketiga."),
        ("Otentikasi Berlapis Sesuai BSSN", "Menegakkan kebijakan kata sandi minimal 15 karakter (melampaui syarat 12 karakter BSSN), pembatasan 5 kali percobaan gagal (account lockout), dan sesi kedaluwarsa otomatis (inactivity timeout 15 menit)."),
        ("Keamanan Akun Super Admin via 2FA HP Offline", "Menggunakan Google Authenticator / FreeOTP di HP Bos tanpa pulsa dan tanpa internet (TOTP RFC 6238), didukung lembar cetak fisik kunci cadangan untuk antisipasi HP hilang."),
        ("Enkripsi Data Sensitif (Data at Rest)", "Mengacak data identitas warga pada tabel fisik menggunakan AES-256-CBC dipadu HMAC-SHA256 bawaan Laravel Eloquent casting serta proteksi partisi disk server BitLocker AES-XTS 256."),
        ("Audit Trail Forensik Anti-Manipulasi (Immutable Audit Log)", "Menyimpan rekaman riwayat akses, login, ekspor, dan perubahan konfigurasi secara permanen (append-only) dengan retensi minimal 12 bulan."),
        ("Proteksi Folder Server & SOP Pemulihan Bencana", "Mengikat aplikasi pada hardware PC Server kantor saat inisialisasi awal, disertai prosedur darurat re-binding resmi berotorisasi jika server fisik mengalami kerusakan.")
    ]

    for title, desc in goals:
        add_paragraph_styled(doc, desc, bold_prefix=f"• {title}: ")

    # =========================================================
    # BAB 3: TOPOLOGI LAN, ANTI-OPER & DRP SOP
    # =========================================================
    add_styled_heading(doc, "BAB 3: TOPOLOGI JARINGAN LAN OFFLINE, ANTI-OPER FOLDER & SOP PEMULIHAN BENCANA HARDWARE (DRP)", level=1)

    add_paragraph_styled(doc,
        "Sistem PADU v2.0 mengadopsi topologi On-Premise Single-Host Server terpusat di lingkungan jaringan lokal kantor:")

    add_paragraph_styled(doc,
        "Aplikasi, mesin komputasi DuckDB, database SQLite, dan seluruh berkas project hanya diletakkan pada 1 unit komputer desktop server di kantor. Server menjalankan skrip 'jalankan_padu.bat' pada IP lokal (misal: 192.168.1.10:8000).",
        bold_prefix="1. Komputer Server Mandiri (PC Host): ")

    add_paragraph_styled(doc,
        "Laptop Pimpinan (Super Admin) dan laptop Pegawai (Operator) hanya membuka Google Chrome dari meja kerja masing-masing melalui kabel LAN atau Wi-Fi kantor tanpa kuota internet. Laptop klien sama sekali tidak memiliki folder aplikasi, source code, ataupun berkas database, sehingga mustahil dicuri dari laptop staf.",
        bold_prefix="2. Akses Klien Murni Browser: ")

    add_paragraph_styled(doc,
        "Untuk mencegah oknum kantor meng-copy folder aplikasi dari komputer server kantor ke flashdisk lalu menjalankannya di laptop pribadi di rumah, sistem mengikat diri ke nomor seri Motherboard dan CPU Processor ID server kantor saat setup pertama. Jika dijalankan pada komputer yang berbeda, sistem mendeteksi ketidaksesuaian mesin dan langsung mengunci akses.",
        bold_prefix="3. Proteksi Hardware Self-Binding (Anti-Oper Folder): ")

    add_paragraph_styled(doc,
        "Mengacu pada Peraturan BSSN No. 8 Tahun 2020 (Pilar Availability), jika server fisik mengalami kerusakan fatal (motherboard/CPU terbakar) dan harus diganti mendadak, Pimpinan dapat merestorasi sistem ke PC baru menggunakan Master Recovery Key fisik dari brankas melalui skrip 'restore_hardware_bind.bat' untuk melakukan re-binding hardware resmi tanpa perlu memanggil tim pengembang eksternal.",
        bold_prefix="4. Prosedur Pemulihan Bencana Hardware (DRP Re-Binding SOP): ")

    # =========================================================
    # BAB 4: ALUR OTENTIKASI BERTAHAP & MANAJEMEN SESI BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 4: ALUR OTENTIKASI BERTAHAP, SANDI MIN. 15 KARAKTER, ACCOUNT LOCKOUT & MANAJEMEN SESI", level=1)

    add_paragraph_styled(doc,
        "Mengacu pada Peraturan BSSN No. 4 Tahun 2021 (Standar Teknis Autentikasi & Manajemen Sesi Aplikasi Web), sistem menerapkan kontrol komprehensif:")

    add_styled_heading(doc, "4.1. Alur Otentikasi Bertahap (Email-First)", level=2)
    add_paragraph_styled(doc,
        "Pengguna membuka alamat URL server di browser -> Mengetikkan alamat email terdaftar -> Menekan tombol 'Lanjutkan / Next'. Sistem memeriksa keberadaan email secara aman tanpa mengekspos detail infrastruktur.",
        bold_prefix="• Langkah 1 (Email Input): ")
    add_paragraph_styled(doc,
        "Sistem menampilkan nama pengguna dan badge identitas resmi ('Super Admin' atau 'Operator Data') lalu meminta kata sandi akun (kebijakan minimal 15 karakter).",
        bold_prefix="• Langkah 2 (Password & Role Badge): ")

    add_styled_heading(doc, "4.2. Standar Kekuatan Kata Sandi (Minimal 15 Karakter)", level=2)
    add_paragraph_styled(doc,
        "Kebijakan kata sandi wajib memenuhi standar: Password::min(15)->mixedCase()->numbers()->symbols()->uncompromised(). Ketentuan ini melampaui batas minimal 12 karakter yang disyaratkan BSSN No. 4/2021.")

    add_styled_heading(doc, "4.3. Pembatasan Percobaan Gagal (Account Lockout & Rate Limiting)", level=2)
    add_paragraph_styled(doc,
        "Sesuai mandat BSSN untuk mencegah serangan Brute-Force / Credential Stuffing, sistem membatasi kesalahan input kata sandi maksimal 5 kali berturut-turut. Jika batas terlampaui, akun otomatis terkunci sementara selama 15 menit (RateLimiter 5 attempts / 900 detik) dan insiden dicatat seketika dalam Audit Trail Forensik.")

    add_styled_heading(doc, "4.4. Masa Berlaku Kata Sandi (Password Expiry) & Riwayat Sandi", level=2)
    add_paragraph_styled(doc,
        "Kata sandi wajib dirotasi setiap 180 hari (6 bulan) sesuai standar BSSN. Sistem melarang penggunaan kembali salah satu dari 3 kata sandi terakhir (password history constraint) yang divalidasi melalui penyimpanan nilai hash.")

    add_styled_heading(doc, "4.5. Manajemen Sesi (Inactivity Session Timeout)", level=2)
    add_paragraph_styled(doc,
        "Sesuai Peraturan BSSN No. 4/2021 untuk mencegah pembajakan sesi (session hijacking) dan intipan fisik di kantor, sistem menerapkan batas waktu tidak aktif (inactivity timeout) maksimal 15 menit (SESSION_LIFETIME = 15). Jika tidak ada interaksi selama 15 menit, sesi login hangus otomatis dan layar dialihkan ke halaman login awal.")

    add_styled_heading(doc, "4.6. Logika Khusus Saat Klik 'Lupa Kata Sandi?'", level=2)
    
    tbl_lupa = doc.add_table(rows=3, cols=3)
    tbl_lupa.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_lupa, color="CBD5E1")

    h_lupa = ["Tipe Hak Akses", "Mekanisme Saat Klik 'Lupa Sandi'", "Tindakan Sistem"]
    for i, h in enumerate(h_lupa):
        cell = tbl_lupa.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)

    rows_lupa = [
        ("Super Admin (Pimpinan)", 
         "Verifikasi 2FA Offline HP (Google Authenticator / FreeOTP)", 
         "Masukkan 6 angka token TOTP di HP -> Terverifikasi sah -> Langsung muncul dialog membuat kata sandi baru (min 15 karakter)."),
        ("Operator (Pegawai)", 
         "Admin-Assisted Offline Reset", 
         "Muncul dialog: 'Akun Operator tidak dapat reset mandiri demi kepatuhan audit. Silakan hubungi Super Admin di kantor untuk mereset kata sandi Anda'.")
    ]

    for idx, (role, meka, aksi) in enumerate(rows_lupa):
        row = tbl_lupa.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([role, meka, aksi]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=70, bottom=70, left=100, right=100)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(8.5)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # =========================================================
    # BAB 5: SISTEM 2FA HP CERDAS & HASHING KUNCI DARURAT
    # =========================================================
    add_styled_heading(doc, "BAB 5: SISTEM KEAMANAN 2FA HP CERDAS, HASHING KUNCI DARURAT & PROTOKOL AUTO-REVOKE", level=1)

    add_paragraph_styled(doc,
        "Untuk mengamankan akun Super Admin tanpa bergantung pada koneksi internet atau pulsa SMS:")

    add_styled_heading(doc, "5.1. Pembedaan Kunci 2FA di HP vs Kunci Cadangan Cetak", level=2)
    add_paragraph_styled(doc,
        "Tersimpan di dalam aplikasi Google Authenticator / FreeOTP di HP Bos melalui scan QR Code lokal saat setup pertama kali. Berfungsi untuk menghasilkan token 6 digit acak berbasis waktu (TOTP RFC 6238) yang berputar setiap 30 detik untuk kebutuhan login harian.",
        bold_prefix="1. Kunci 2FA di HP (Kunci Operasional): ")
    add_paragraph_styled(doc,
        "Dicetak pada lembar fisik resmi untuk disimpan oleh Bos di brankas atau laci meja terkunci. Kunci ini adalah Emergency Revocation Key yang kodenya BERBEDA TOTAL dari kunci di HP.",
        bold_prefix="2. Kunci Cadangan Fisik (Emergency Revocation Key): ")

    add_styled_heading(doc, "5.2. Penyimpanan Kunci Darurat Berbasis Hash (Kepatuhan Kriptografi BSSN)", level=2)
    add_paragraph_styled(doc,
        "Sesuai standar BSSN, teks kunci cadangan fisik TIDAK DISIMPAN DALAM BENTUK TEKS ASLI (PLAINTEXT) pada database server. Kunci darurat disimpan dalam bentuk nilai hash satu arah berbobot tinggi menggunakan algoritma Argon2id / Bcrypt dengan salt acak unik. Jika database server dicuri, pelaku tidak dapat membaca maupun membalikkan nilai kunci fisik darurat tersebut.")

    add_styled_heading(doc, "5.3. Penolakan Penambahan HP Liar (Cegah Akun Liar)", level=2)
    add_paragraph_styled(doc,
        "Sistem secara tegas menonaktifkan fitur penambahan HP baru dari menu profil harian. Hal ini menutup celah keamanan agar akun Super Admin tidak dapat ditautkan ke HP pihak ketiga atau oknum internal lain yang tidak berkepentingan.")

    add_styled_heading(doc, "5.4. Deteksi HP Hilang/Rusak & Auto-Revoke Kunci Lama", level=2)
    add_paragraph_styled(doc,
        "Jika HP Bos suatu hari hilang, dicuri, atau rusak, Bos memasukkan Kunci Cadangan Fisik pada layar pemulihan 2FA. Begitu kunci ini diverifikasi cocok dengan hash di server:")
    add_paragraph_styled(doc,
        "Sistem seketika mengetahui bahwa HP lama Bos bermasalah. Detik itu juga, sistem LANGSUNG MENGHANGUSKAN DAN MEMUTUSKAN Kunci 2FA di HP lama tersebut. Jika HP lama ditemukan atau dicuri orang lain, token 2FA di HP lama itu OTOMATIS MATI dan tidak dapat digunakan lagi.",
        bold_prefix="1. Auto-Revoke Kunci HP Lama: ")
    add_paragraph_styled(doc,
        "Sistem langsung men-generate QR Code 2FA BARU (dengan kunci rahasia baru yang berbeda total) untuk di-scan oleh Bos menggunakan HP BARU miliknya.",
        bold_prefix="2. Penerbitan QR Code Baru di HP Baru: ")
    add_paragraph_styled(doc,
        "Sistem mencetak ulang 1 lembar fisik Kunci Cadangan Darurat Baru untuk disimpan kembali oleh Bos di brankas kerjanya (tanpa perlu kewajiban segel amplop yang rumit).",
        bold_prefix="3. Cetak Ulang Kunci Cadangan Baru: ")

    # =========================================================
    # BAB 6: ENKRIPSI DATABASE BERLAPIS & KRIPTOGRAFI BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 6: ENKRIPSI DATABASE BERLAPIS (LAYERED DATABASE DEFENSE) & STANDAR KRIPTOGRAFI BSSN", level=1)

    add_paragraph_styled(doc,
        "Mengacu pada Peraturan BSSN No. 11 Tahun 2024 dan UU No. 27 Tahun 2022 (UU PDP Pasal 35), sistem menerapkan pertahanan berlapis:")

    add_paragraph_styled(doc,
        "Seluruh atribut data pribadi spesifik warga (NIK, Nama Lengkap, Pendapatan, Alamat RT/RW, Metadata Akun) dienkripsi secara transparan menggunakan algoritma AES-256-CBC bawaan Laravel Eloquent casting yang dipadu dengan otentikasi pesan kriptografi HMAC-SHA256 (Encrypt-then-MAC). Pada berkas fisik database.sqlite, kolom-kolom tersimpan sebagai teks teracak (ciphertext: 'eyJpdiI6...'). Berkas tidak dapat dibaca oleh software SQLite viewer biasa.",
        bold_prefix="• Enkripsi Lapis Aplikasi (Laravel Native Encrypted Cast): ")

    add_paragraph_styled(doc,
        "Direktori analitik DuckDB (dataset.duckdb) dan berkas SQLite diproteksi menggunakan Windows Access Control List (ACL) yang hanya dapat dibaca oleh identitas pengguna servis lokal sistem.",
        bold_prefix="• Lapis Akses Sistem File (OS ACL): ")

    add_paragraph_styled(doc,
        "Drive partisi server tempat instalasi sistem PADU diwajibkan mengaktifkan proteksi Windows BitLocker Drive Encryption dengan enkripsi AES-XTS 256-bit berbasis chip hardware TPM (Trusted Platform Module). Jika harddisk server dicopot secara fisik, seluruh berkas tidak dapat diakses tanpa PIN otorisasi BIOS server.",
        bold_prefix="• Enkripsi Lapis Media Penyimpanan (Full Disk Encryption / BitLocker): ")

    # =========================================================
    # BAB 7: STANDAR AUDIT TRAIL FORENSIK DIGITAL BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 7: STANDAR AUDIT TRAIL, FORENSIK DIGITAL & PENCATATAN LOG KEBAL MANIPULASI (IMMUTABLE LOG)", level=1)

    add_paragraph_styled(doc,
        "Mengacu pada Peraturan BSSN No. 4 Tahun 2021 (Pasal Standar Teknis Log & Forensik Digital) serta Peraturan BSSN No. 8 Tahun 2020 (Pilar Non-Repudiation):")

    add_styled_heading(doc, "7.1. Struktur & Parameter Log Audit Forensik", level=2)
    add_paragraph_styled(doc,
        "Setiap peristiwa penting dicatat dalam tabel audit terdedikasi (system_audit_logs) pada SQLite dengan format rekaman terstruktur:")

    tbl_log = doc.add_table(rows=7, cols=3)
    tbl_log.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_log, color="CBD5E1")

    log_headers = ["Parameter Log", "Tipe Data / Format", "Deskripsi & Nilai Sampel"]
    for i, h in enumerate(log_headers):
        cell = tbl_log.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)

    log_rows = [
        ("Timestamp", "ISO-8601 (WIB)", "Stempel waktu presisi detik: '2026-09-27 21:30:15 WIB'."),
        ("User Identity", "Varchar / Integer", "Email, User ID, dan Badge Role resmi (Super Admin / Operator)."),
        ("Network Context", "Varchar", "IP Address lokal ('192.168.1.15'), Hostname, dan Header User-Agent browser."),
        ("Action Category", "Varchar (Enum)", "AUTH_LOGIN_SUCCESS, AUTH_LOGIN_FAILED, AUTH_LOCKOUT, DATA_EXPORT, BACKUP, dll."),
        ("Event Payload", "JSON Structured", "Ringkasan kriteria filter data, nama berkas ekspor, atau versi patch."),
        ("Execution Status", "Enum", "SUCCESS, DENIED, atau FAILED.")
    ]

    for idx, (param, tipe, desc) in enumerate(log_rows):
        row = tbl_log.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([param, tipe, desc]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=70, bottom=70, left=100, right=100)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(8.5)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    add_styled_heading(doc, "7.2. Kebal Manipulasi (Immutable & Append-Only)", level=2)
    add_paragraph_styled(doc,
        "Tabel audit log dirancang bersifat Hanya Tambah (Append-Only). Antarmuka web Super Admin hanya menyediakan fitur Read & Filter Log; tidak ada tombol Edit maupun Delete Log. Riwayat log audit dipertahankan dengan masa retensi minimal 12 bulan (1 tahun) sesuai standar audit kepatuhan keamanan informasi pemerintah.")

    # =========================================================
    # BAB 8: PROTEKSI ARSIP ZIP AES-256 AUTO-RESET
    # =========================================================
    add_styled_heading(doc, "BAB 8: PROTEKSI BERKAS ARSIP ZIP APLIKASI (AES-256 PASSWORD 20 KARAKTER ACAK AUTO-RESET)", level=1)

    add_paragraph_styled(doc,
        "Untuk keamanan data dan folder aplikasi pada level storage dan cadangan arsip:")
    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi (ZIP) dikunci menggunakan standar WinZip/7-Zip AES-256 dengan PBKDF2 Key Derivation (menggantikan ZipCrypto jadul yang rentan). Sandi sepanjang 20 karakter acak tingkat tinggi (kombinasi huruf besar, kecil, angka, dan simbol). Contoh: K9#mQ2$xL8!vW4&yP1*z.",
        bold_prefix="1. Standar Enkripsi Arsip AES-256: ")
    add_paragraph_styled(doc,
        "Password ZIP 20 karakter ini dicetak pada lembar fisik pemulihan aplikasi untuk disimpan oleh Bos di brankas.",
        bold_prefix="2. Lembar Cetak Master ZIP: ")
    add_paragraph_styled(doc,
        "Setiap kali berkas ZIP cadangan diekstrak atau dibuka untuk pemulihan server di komputer baru, sistem mendeteksi peristiwa ekstraksi tersebut. Detik itu juga, sistem otomatis MENGHANGUSKAN password 20 karakter lama dan MERESET password baru 20 karakter acak yang berbeda, lalu mengemas ulang arsip ZIP dengan password baru tersebut.",
        bold_prefix="3. Mekanisme Auto-Reset Saat ZIP Terbuka: ")

    # =========================================================
    # BAB 9: TATA CARA PENCADANGAN & INTEGRITAS HASH SHA-256
    # =========================================================
    add_styled_heading(doc, "BAB 9: TATA CARA PENCADANGAN APLIKASI & INTEGRITAS HASH SHA-256 (BACKUP SOP 3-2-1)", level=1)

    add_paragraph_styled(doc,
        "Sesuai panduan ketahanan siber BSSN dan mitigasi ancaman ransomware, sistem menerapkan aturan pencadangan 3-2-1:")
    add_paragraph_styled(doc,
        "Salinan data aktif pada basis data PC Server.",
        bold_prefix="• Salinan 1 (Data Operasional): ")
    add_paragraph_styled(doc,
        "Skrip offline cadangkan_padu.bat di server yang dijalankan berkala atau dijadwalkan otomatis melalui Windows Task Scheduler ke partisi lokal sekunder (D:\\BACKUP_PADU\\).",
        bold_prefix="• Salinan 2 (Cadangan Lokal Terjadwal): ")
    add_paragraph_styled(doc,
        "Pimpinan masuk ke panel Super Admin -> Menu 'Pemeliharaan' -> Klik 'Buat Cadangan Baru'. Sistem mengeksekusi perintah SQLite vacuum into dan DuckDB checkpoint tanpa mengunci tabel, menghasilkan berkas arsip berstempel waktu (PADU_BACKUP_YYYYMMDD.padubak) yang terunduh langsung ke laptop pribadi Pimpinan untuk disimpan di Flashdisk fisik terpisah.",
        bold_prefix="• Salinan 3 (Cadangan Terisolasi / Off-Host): ")
    add_paragraph_styled(doc,
        "Setiap kali berkas cadangan dibuat, sistem secara otomatis membangkitkan berkas stempel integritas: PADU_BACKUP_YYYYMMDD.padubak.sha256. Sebelum proses restore dijalankan, sistem menghitung ulang nilai hash untuk memvalidasi bahwa berkas 100% utuh dan tidak pernah dimanipulasi.",
        bold_prefix="• Verifikasi Integritas Melalui Hash SHA-256: ")

    # =========================================================
    # BAB 10: MODEL DISTRIBUSI LEPAS KUNCI & BAST
    # =========================================================
    add_styled_heading(doc, "BAB 10: MODEL DISTRIBUSI LEPAS KUNCI (ZERO-KNOWLEDGE TURNKEY HANDOVER) & BAST", level=1)

    add_paragraph_styled(doc,
        "Pengembang menyerahkan aplikasi dalam keadaan utuh tanpa hak retensi maupun akses terselubung:")
    add_paragraph_styled(doc,
        "Sistem tidak memerlukan aktivasi serial tahunan atau otorisasi tanda tangan developer di kemudian hari.",
        bold_prefix="• Tanpa Lisensi Berkala: ")
    add_paragraph_styled(doc,
        "Kunci enkripsi master digenerate secara lokal di PC Server saat Pimpinan login dan melakukan set password pertama kali.",
        bold_prefix="• Inisialisasi Mandiri oleh Pimpinan: ")
    add_paragraph_styled(doc,
        "Menegaskan pengalihan tanggung jawab pemeliharaan, keamanan fisik, dan kerahasiaan kata sandi secara mutlak kepada pihak Klien sesuai kaidah hukum perikatan perdata.",
        bold_prefix="• Klausul BAST (Berita Acara Serah Terima): ")

    # =========================================================
    # BAB 11: PROSEDUR DEVELOPER BARU & SSDLC BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 11: PROSEDUR PENGEMBANG BARU, ISOLASI DATA SINTETIS (SSDLC) & VERIFIKASI PATCH", level=1)

    add_paragraph_styled(doc,
        "Mengacu pada Peraturan BSSN No. 10 Tahun 2020 tentang Pengamanan Pengadaan dan Pengembangan Perangkat Lunak (SSDLC):")
    add_paragraph_styled(doc,
        "Sesuai UU PDP dan standar BSSN, developer baru DILARANG KERAS memegang database asli warga. Developer baru hanya menerima Clean Source Code dan database data tiruan sintetis (dummy data) sebanyak 10.000 data palsu melalui perintah: 'php artisan db:seed --class=DummyCitizenSeeder'.",
        bold_prefix="1. Pemisahan Total Kode vs Data Nyata Warga: ")
    add_paragraph_styled(doc,
        "Aplikasi dibekali isolasi lingkungan di file .env. Jika APP_ENV=local, proteksi Hardware Lock nonaktif agar developer leluasa menguji kode. Saat dipasang di server kantor dengan APP_ENV=production, Hardware Lock, Enkripsi AES-256, dan Rate Limiter otomatis aktif penuh.",
        bold_prefix="2. Isolasi Lingkungan Koding (Local Sandbox): ")
    add_paragraph_styled(doc,
        "Developer baru mengemas kodingan barunya menjadi 1 berkas: update_vX.Y.zip disertai nilai hash SHA-256 resminya. Pimpinan mengunggah berkas tersebut melalui menu Super Admin -> Pembaruan Sistem. Sistem secara otomatis mencocokkan hash SHA-256 paket sebelum mengekstrak kode baru secara aman.",
        bold_prefix="3. Alur Pembaruan Sistem via Patch ZIP & Verifikasi Hash SHA-256: ")
    add_paragraph_styled(doc,
        "Jika diperlukan perbaikan langsung di PC Server kantor, pekerjaan wajib dilakukan di bawah pendampingan fisik Pimpinan dengan mengaktifkan mode pemeliharaan (php artisan down). Seluruh aktivitas tercatat lengkap pada audit trail log.",
        bold_prefix="4. Pemeliharaan Lapangan Terkendali (Supervised Access): ")

    # =========================================================
    # BAB 12: RENCANA KERJA 4 FASE (ACTION PLAN)
    # =========================================================
    add_styled_heading(doc, "BAB 12: RENCANA KERJA & JADWAL IMPLEMENTASI SISTEMATIS (ACTION PLAN 4 FASE)", level=1)

    tbl_plan = doc.add_table(rows=5, cols=3)
    tbl_plan.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_plan, color="CBD5E1")

    plan_headers = ["Fase Kerja", "Rincian Pekerjaan Teknis", "Target Luaran (Deliverables)"]
    for i, h in enumerate(plan_headers):
        cell = tbl_plan.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=80, bottom=80, left=100, right=100)

    plan_rows = [
        ("Fase 1: Penyelarasan Blueprint & Spesifikasi BSSN", 
         "Pembaruan DFD Level 0, 1, 2, ERD, dan Swimlane dengan alur Two-Step Login, 2FA TOTP RFC 6238, tabel audit forensik, dan rate limiter.",
         "Berkas Draw.io v2.0 valid & tersertifikasi desain BSSN."),
        ("Fase 2: Backend Auth, Enkripsi AES-256 & Audit Logger", 
         "Implementasi Auth Bertahap, RateLimiter (5x lockout), Session Timeout (15 mnt), Password Expiry (180 hari), Laravel encrypted casting, dan modul Immutable Audit Log.",
         "Modul Otentikasi BSSN & Audit Logger aktif operasional."),
        ("Fase 3: Generator ZIP AES-256, Backup Hash & Lembar Cetak", 
         "Pengembangan modul auto-reset ZIP AES-256, generator verifikasi hash SHA-256 backup, template cetak kunci fisik (Argon2id hash di DB), dan UI One-Click Backup.",
         "Fitur Enkripsi Arsip & Integritas Backup selesai 100%."),
        ("Fase 4: Simulasi Forensik, Uji Penetrasi & BAST", 
         "Uji coba serangan brute-force (validasi lockout 5x), simulasi timeout 15 menit, simulasi auto-revoke HP via kunci cetak, uji DRP hardware re-bind, dan penandatanganan BAST Lepas Kunci.",
         "Berita Acara Uji Kepatuhan & BAST Lepas Kunci ditandatangani.")
    ]

    for idx, (fase, rincian, target) in enumerate(plan_rows):
        row = tbl_plan.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([fase, rincian, target]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=70, bottom=70, left=100, right=100)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(8.5)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # =========================================================
    # BAB 13: MATRIKS ANALISIS RISIKO & KEPATUHAN BSSN
    # =========================================================
    add_styled_heading(doc, "BAB 13: MATRIKS ANALISIS RISIKO, MITIGASI SIBER & KESELARASAN REGULASI BSSN", level=1)

    tbl_risk = doc.add_table(rows=11, cols=4)
    tbl_risk.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_risk, color="CBD5E1")

    risk_headers = ["Identifikasi Potensi Risiko", "Dampak", "Rencana Mitigasi Teknis PADU v2.0", "Keselarasan Regulasi"]
    for i, h in enumerate(risk_headers):
        cell = tbl_risk.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(8.5); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=80, bottom=80, left=90, right=90)

    risk_rows = [
        ("Pencurian folder server via flashdisk oleh oknum internal", "TINGGI", "Database terenkripsi AES-256-CBC, drive server terproteksi BitLocker, dan aplikasi terikat hardware UUID server.", "Per BSSN No. 11/2024 & UU PDP"),
        ("Serangan Brute-Force tebak password akun", "TINGGI", "Wajib sandi minimal 15 karakter kompleksitas tinggi dan sistem Account Lockout otomatis setelah 5 kali gagal (kunci 15 menit).", "Per BSSN No. 4/2021"),
        ("Laptop ditinggal staf dalam keadaan login (Intipan Fisik)", "SEDANG", "Inactivity Session Timeout memutus sesi secara otomatis setelah 15 menit tanpa aktivitas pengguna.", "Per BSSN No. 4/2021"),
        ("Pimpinan lupa kata sandi 15 karakter", "SEDANG", "Pimpinan memulihkan hak akses secara mandiri menggunakan token 6 digit dari aplikasi Google Authenticator di HP pribadinya.", "Per BSSN No. 4/2021"),
        ("HP Pimpinan hilang / dicuri / rusak", "TINGGI", "Memasukkan Kunci Cadangan Cetak -> Sistem otomatis MENGHANGUSKAN kunci lama (auto-revoke) dan menerbitkan QR Code baru di HP baru. Kunci darurat di-hash Argon2id.", "Per BSSN No. 4/2021 & No. 8/2020"),
        ("Password arsip ZIP diintip saat ekstraksi pemulihan", "TINGGI", "Sistem mengunci ZIP dengan AES-256 dan seketika MENGHANGUSKAN password lama serta MERESET password baru 20 karakter acak begitu ZIP dibuka.", "Per BSSN No. 11/2024"),
        ("Motherboard PC Server kantor terbakar / rusak fisik", "TINGGI", "Disediakan SOP Pemulihan Bencana (Hardware Re-Binding) menggunakan otorisasi Master Recovery Key fisik dari brankas.", "Per BSSN No. 8/2020 (Availability)"),
        ("Penyusupan kode berbahaya pada paket pembaruan", "TINGGI", "Verifikasi nilai hash integritas SHA-256 sebelum berkas patch diekstrak oleh sistem.", "Per BSSN No. 10/2020 (SSDLC)"),
        ("Penyangkalan aksi pengguna (Repudiation)", "SEDANG", "Pencatatan seluruh transaksi penting ke dalam Immutable Audit Log permanen dengan retensi minimal 12 bulan.", "Per BSSN No. 8/2020 (Non-Repudiation)"),
        ("Keterikatan vendor / Tuntutan hukum di kemudian hari", "TINGGI", "Penandatanganan BAST Lepas Kunci (Zero-Knowledge) membuktikan pengembang tidak memiliki akses dan kunci master setelah serah terima.", "UU ITE & Hukum Perdata")
    ]

    for idx, (resiko, dampak, mitigasi, regulasi) in enumerate(risk_rows):
        row = tbl_risk.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([resiko, dampak, mitigasi, regulasi]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=60, bottom=60, left=80, right=80)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(8)
            if c_idx == 1:
                r.font.bold = True
                r.font.color.rgb = RGBColor(185, 28, 28) if val == "TINGGI" else RGBColor(202, 138, 4)
            elif c_idx == 3:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # =========================================================
    # BAB 14: KRITERIA KEBERHASILAN & LEMBAR PENGESAHAN
    # =========================================================
    add_styled_heading(doc, "BAB 14: KRITERIA KEBERHASILAN, AUDIT KEPATUHAN & LEMBAR PENGESAHAN (SIGN-OFF)", level=1)

    crit = [
        ("Otentikasi Bertahap & Password Min. 15 Karakter", "Layar pertama meminta email, layar kedua mengidentifikasi role pengguna dan meminta kata sandi min 15 karakter."),
        ("Uji Brute-Force & Account Lockout", "Sistem sukses mengunci akun selama 15 menit setelah 5 kali gagal memasukkan kata sandi, serta mencatatnya di log audit."),
        ("Uji Inactivity Session Timeout", "Sesi web otomatis terputus setelah 15 menit pengguna tidak melakukan interaksi pada layar peramban."),
        ("Pemulihan Lupa Sandi Super Admin via 2FA HP", "Super Admin berhasil mereset kata sandi menggunakan token 6 digit dari Google Authenticator di HP pribadinya."),
        ("Auto-Revoke 2FA Saat HP Hilang", "Sistem sukses menghanguskan kunci HP lama dan menerbitkan QR Code baru setelah kunci cadangan cetak dimasukkan (hash Argon2id terverifikasi)."),
        ("Uji Enkripsi Database & Arsip ZIP AES-256", "Kolom sensitif di SQLite terbukti teracak, arsip ZIP terkunci enkripsi AES-256, dan password 20 karakter sukses ter-reset otomatis saat ZIP diekstrak."),
        ("Validasi Integritas Hash SHA-256", "Sistem sukses memverifikasi berkas cadangan .padubak.sha256 dan paket pembaruan patch."),
        ("Uji Forensik Log Kebal Manipulasi", "Seluruh riwayat transaksi terekam di tabel audit tanpa tersedianya tombol edit/hapus pada antarmuka."),
        ("Kemandirian Penuh (Zero-Knowledge Handover)", "Sistem berjalan normal di jaringan LAN kantor tanpa ketergantungan koneksi internet dan tanpa campur tangan developer.")
    ]

    for title, desc in crit:
        add_paragraph_styled(doc, desc, bold_prefix=f"✓ {title}: ")

    # Tanda Tangan Formal
    doc.add_paragraph().paragraph_format.space_after = Pt(10)
    p_sign_hdr = doc.add_paragraph()
    r_sh = p_sign_hdr.add_run("LEMBAR PERSETUJUAN PROPOSAL TEKNIS PEMBARUAN v2.0 ENTERPRISE\n(Penyelarasan Standar Keamanan BSSN & Tata Kelola Lepas Kunci)")
    r_sh.font.name = 'Arial'; r_sh.font.size = Pt(10.5); r_sh.font.bold = True
    r_sh.font.color.rgb = RGBColor(16, 44, 87)

    tbl_sign = doc.add_table(rows=4, cols=2)
    tbl_sign.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_sign, color="CBD5E1")

    for c in tbl_sign.rows[0].cells:
        set_cell_background(c, "F1F5F9")
    
    p_s0 = tbl_sign.rows[0].cells[0].paragraphs[0]
    r_s0 = p_s0.add_run("Diajukan Oleh (Pengembang):")
    r_s0.font.name = 'Arial'; r_s0.font.size = Pt(9.5); r_s0.font.bold = True
    
    p_s1 = tbl_sign.rows[0].cells[1].paragraphs[0]
    r_s1 = p_s1.add_run("Disetujui Oleh (Klien / Pimpinan Proyek):")
    r_s1.font.name = 'Arial'; r_s1.font.size = Pt(9.5); r_s1.font.bold = True

    for r_i in range(1, 3):
        for cell in tbl_sign.rows[r_i].cells:
            cell.paragraphs[0].paragraph_format.space_before = Pt(16)
            cell.paragraphs[0].paragraph_format.space_after = Pt(16)

    p_n0 = tbl_sign.rows[3].cells[0].paragraphs[0]
    r_n0 = p_n0.add_run("( _____________________________ )\nLead System Architect & Developer")
    r_n0.font.name = 'Arial'; r_n0.font.size = Pt(9.5); r_n0.font.bold = True

    p_n1 = tbl_sign.rows[3].cells[1].paragraphs[0]
    r_n1 = p_n1.add_run("( _____________________________ )\nProject Sponsor / Super Admin Klien")
    r_n1.font.name = 'Arial'; r_n1.font.size = Pt(9.5); r_n1.font.bold = True

    doc.save(output_path)
    print(f"Full Proposal Word document successfully generated at: {output_path}")

if __name__ == "__main__":
    targets = [
        "Proposal_Update_Sistem_PADU_v2.0_Updated.docx",
        "Proposal_Update_Sistem_PADU_v2.0 (2).docx"
    ]
    for t in targets:
        p = os.path.abspath(t)
        try:
            create_full_proposal_docx(p)
        except Exception as e:
            print(f"Error generating {t}: {e}")
