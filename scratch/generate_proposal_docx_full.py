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

def add_callout(doc, text, title="CATATAN KEAMANAN STRATEGIS", border_color="102C57", bg_color="F1F5F9"):
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
        run.font.size = Pt(14)
        run.font.bold = True
        run.font.color.rgb = RGBColor(16, 44, 87) # Deep Navy
        h.paragraph_format.space_before = Pt(16)
        h.paragraph_format.space_after = Pt(6)
    elif level == 2:
        run.font.size = Pt(12)
        run.font.bold = True
        run.font.color.rgb = RGBColor(30, 80, 130) # Slate Navy
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
    elif level == 3:
        run.font.size = Pt(10.5)
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
        r_hdr = p_hdr.add_run("PROPOSAL TEKNIS PENGEMBANGAN SISTEM PADU v2.0 ENTERPRISE | SANGAT RAHASIA")
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
    p_inst.paragraph_format.space_before = Pt(36)
    p_inst.paragraph_format.space_after = Pt(6)
    r_inst = p_inst.add_run("PROPOSAL TEKNIS PENGEMBANGAN & PEMBARUAN SISTEM LENGKAP (A - Z)")
    r_inst.font.name = 'Arial'
    r_inst.font.size = Pt(11)
    r_inst.font.bold = True
    r_inst.font.color.rgb = RGBColor(100, 116, 139)

    p_main_title = doc.add_paragraph()
    p_main_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_main_title.paragraph_format.space_before = Pt(10)
    p_main_title.paragraph_format.space_after = Pt(8)
    r_title = p_main_title.add_run("SISTEM INFORMASI PADU v2.0 ENTERPRISE\n(Pengolah & Analisis Data Terpadu)")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(21)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(16, 44, 87)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_before = Pt(6)
    p_sub.paragraph_format.space_after = Pt(30)
    r_sub = p_sub.add_run("Cetak Biru Komprehensif Arsitektur Keamanan Berlapis, Kedaulatan Mandiri Klien (Zero-Knowledge),\nOtentikasi Bertahap (Email-First Login), 2FA Offline Cerdas (Auto-Revoke HP Hilang),\ndan Proteksi Arsip ZIP Aplikasi (Password 20 Karakter Acak Auto-Reset)")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(10.5)
    r_sub.font.italic = True
    r_sub.font.color.rgb = RGBColor(53, 89, 143)

    # Box Metadata Cover
    tbl_meta = doc.add_table(rows=7, cols=2)
    tbl_meta.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_meta, color="E2E8F0")
    
    meta_data = [
        ("Nomor Dokumen", "PROP/PADU-SEC/IX/2026/V2.0-FINAL"),
        ("Klasifikasi Dokumen", "SANGAT RAHASIA (Strictly Confidential / Dokumen Internal Klien)"),
        ("Versi Rilis Arsitektur", "v2.0 Enterprise (Rilis Kedaulatan Mandiri Lepas Kunci)"),
        ("Tanggal Penyusunan", "24 September 2026"),
        ("Penyusun Dokumen", "Tim Pengembang Sistem Informasi PADU"),
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
        set_cell_margins(c0, top=90, bottom=90, left=130, right=130)
        set_cell_margins(c1, top=90, bottom=90, left=130, right=130)
        
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
        "Dalam rangka meningkatkan kesiapan sistem menuju tingkat Enterprise Production (v2.0) di lingkungan tertutup kantor klien, seluruh pemangku kepentingan telah menyepakati penyempurnaan mendasar terhadap aspek keamanan, kedaulatan hak cipta, dan kehandalan operasional:")

    add_paragraph_styled(doc,
        "Setelah Berita Acara Serah Terima (BAST) ditandatangani, tim pengembang 100% lepas tangan. Tidak ada pintu belakang (backdoor), tidak ada ketergantungan lisensi cloud atau serial berkala yang mengharuskan klien menghubungi pengembang di masa mendatang. Kedaulatan penuh berada di tangan Super Admin Klien.",
        bold_prefix="1. Serah Terima Lepas Kunci (Zero-Knowledge Turnkey Handover): ")

    add_paragraph_styled(doc,
        "Modul pengawasan webcam OpenCV, threading MediaPipe 0.2 detik, dan interupsi penguncian layar Windows (user32.dll) dieliminasi total. Sistem bertransformasi menjadi aplikasi web murni (clean web app) yang stabil, ringan, dan dapat diakses bersama secara simultan melalui jaringan Wi-Fi/LAN kantor tanpa membutuhkan internet.",
        bold_prefix="2. Arsitektur Web Murni Tanpa Biometrik: ")

    add_paragraph_styled(doc,
        "Sistem menerapkan urutan otentikasi bertahap: Layar pertama meminta email, layar kedua mendeteksi role pengguna dan meminta kata sandi (min. 15 karakter). Penanganan lupa kata sandi dibedakan secara tegas: Super Admin menggunakan 2FA Offline HP pribadi, sedangkan Operator diarahkan menghubungi Super Admin.",
        bold_prefix="3. Alur Otentikasi Bertahap (Email-First Login): ")

    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi (ZIP) dikunci menggunakan kata sandi sepanjang 20 karakter dengan pola acak tingkat tinggi. Setiap kali berkas ZIP diekstrak atau dibuka untuk pemulihan, sistem secara otomatis menghanguskan password lama dan mereset password 20 karakter baru seketika.",
        bold_prefix="4. Proteksi Arsip ZIP Aplikasi (Password 20 Karakter Auto-Reset): ")

    add_callout(doc,
        "Seluruh pembaruan pada v2.0 ini dirancang untuk mewujudkan sistem yang mandiri, tahan uji dari ancaman pembobolan internal maupun fisik, serta memberikan kepastian hukum dan kedaulatan mutlak bagi pihak Klien.",
        title="FILOSOFI UTAMA PEMBARUAN PADU v2.0 ENTERPRISE")

    # =========================================================
    # BAB 2: TUJUAN & SASARAN STRATEGIS
    # =========================================================
    add_styled_heading(doc, "BAB 2: TUJUAN & SASARAN STRATEGIS SISTEM", level=1)

    goals = [
        ("Kemandirian Sistem 100% (Zero-Knowledge)", "Menjamin sistem dapat beroperasi, dicadangkan, dan dipulihkan seutuhnya oleh Klien tanpa ketergantungan vendor luar."),
        ("Otentikasi Bertahap (Two-Step Email-First)", "Memisahkan tahap pengenalan email dan tahap verifikasi kata sandi sehingga role pengguna dan skenario lupa sandi dapat terisolasi dengan presisi."),
        ("Keamanan Akun Super Admin via 2FA HP Offline", "Menggunakan Google Authenticator / FreeOTP di HP Bos tanpa pulsa dan tanpa internet, didukung lembar cetak fisik kunci cadangan untuk antisipasi HP hilang."),
        ("Standar Kata Sandi Kuat (Min. 15 Karakter)", "Menegakkan kebijakan passphrase minimal 15 karakter kombinasi kompleks pada level database dan backend."),
        ("Enkripsi Database Berlapis (Layered Defense)", "Mengacak data identitas warga pada tabel fisik menggunakan AES-256-CBC bawaan Laravel Eloquent casting."),
        ("Proteksi Folder & Database Server (Anti-Oper)", "Mengikat aplikasi pada hardware PC Server kantor saat inisialisasi awal guna mencegah pemindahan folder secara tidak sah."),
        ("Pencadangan Mandiri & Auto-Reset Password ZIP", "Menyediakan One-Click Backup web, skrip server otomatis, dan proteksi arsip ZIP dengan password 20 karakter acak yang otomatis berganti setiap kali ZIP dibuka.")
    ]

    for title, desc in goals:
        add_paragraph_styled(doc, desc, bold_prefix=f"• {title}: ")

    # =========================================================
    # BAB 3: TOPOLOGI JARINGAN LAN & ANTI-OPER FOLDER
    # =========================================================
    add_styled_heading(doc, "BAB 3: TOPOLOGI JARINGAN LAN & ANTI-OPER FOLDER", level=1)

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

    # =========================================================
    # BAB 4: ALUR OTENTIKASI BERTAHAP (TWO-STEP EMAIL-FIRST LOGIN)
    # =========================================================
    add_styled_heading(doc, "BAB 4: ALUR OTENTIKASI BERTAHAP (EMAIL-FIRST LOGIN)", level=1)

    add_paragraph_styled(doc,
        "Untuk memastikan keamanan alur otentikasi dan memberikan pengalaman pengguna yang profesional, sistem menerapkan urutan login bertahap dua langkah:")

    add_styled_heading(doc, "4.1. Langkah 1: Input Alamat Email", level=2)
    add_paragraph_styled(doc,
        "Pengguna membuka alamat URL server di browser -> Mengetikkan alamat email terdaftar (misal: admin@padu.go.id atau operator@padu.go.id) -> Menekan tombol 'Lanjutkan / Next'.")

    add_styled_heading(doc, "4.2. Langkah 2: Input Password, Pembeda Role & Logika Lupa Sandi", level=2)
    add_paragraph_styled(doc,
        "Pada layar kedua, sistem membaca identitas akun dari database lokal:")
    add_paragraph_styled(doc,
        "Sistem menampilkan nama pengguna dan badge identitas resmi ('Super Admin' atau 'Operator Data') sehingga pengguna yakin tidak salah akun.",
        bold_prefix="• Identifikasi Role Otomatis: ")
    add_paragraph_styled(doc,
        "Pengguna mengetikkan kata sandi akun (kebijakan minimal 15 karakter).",
        bold_prefix="• Input Kata Sandi: ")
    add_paragraph_styled(doc,
        "Tersedia tautan 'Lupa Kata Sandi?' dengan percabangan logika otomatis sesuai role pengguna:",
        bold_prefix="• Logika Khusus Saat Klik 'Lupa Kata Sandi?': ")

    tbl_lupa = doc.add_table(rows=3, cols=3)
    tbl_lupa.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_lupa, color="CBD5E1")

    h_lupa = ["Tipe Hak Akses", "Mekanisme Saat Klik 'Lupa Sandi'", "Tindakan Sistem"]
    for i, h in enumerate(h_lupa):
        cell = tbl_lupa.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9.5); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=90, bottom=90, left=120, right=120)

    rows_lupa = [
        ("Super Admin (Pimpinan)", 
         "Verifikasi 2FA Offline HP (Google Authenticator / FreeOTP)", 
         "Masukkan 6 angka acak yang berputar di HP Super Admin -> Terverifikasi sah -> Langsung muncul pop-up membuat kata sandi baru (min 15 karakter)."),
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
            set_cell_margins(c, top=80, bottom=80, left=120, right=120)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(9)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # =========================================================
    # BAB 5: SISTEM 2FA HP CERDAS & DETEKSI HP HILANG
    # =========================================================
    add_styled_heading(doc, "BAB 5: SISTEM 2FA HP CERDAS & DETEKSI HP HILANG (AUTO-REVOKE)", level=1)

    add_paragraph_styled(doc,
        "Untuk mengamankan akun Super Admin tanpa bergantung pada koneksi internet atau pulsa SMS, sistem menerapkan arsitektur 2FA cerdas dengan pemisahan kunci ganda:")

    add_styled_heading(doc, "5.1. Pembedaan Kunci 2FA di HP vs Kunci Cadangan Cetak", level=2)
    add_paragraph_styled(doc,
        "Sistem menerbitkan dua kunci dengan sifat dan fungsi yang BERBEDA TOTAL:",
        bold_prefix="• Dua Kunci Independen: ")
    add_paragraph_styled(doc,
        "Tersimpan di dalam aplikasi Google Authenticator / FreeOTP di HP Bos melalui scan QR Code lokal saat login pertama kali. Berfungsi untuk menghasilkan token 6 digit acak yang berputar setiap 30 detik untuk kebutuhan login atau reset sandi harian.",
        bold_prefix="1. Kunci 2FA di HP (Kunci Operasional): ")
    add_paragraph_styled(doc,
        "Dicetak pada lembar fisik resmi untuk disimpan oleh Bos di brankas atau laci meja terkunci. Kunci ini adalah Emergency Revocation Key yang kodenya BERBEDA TOTAL dari kunci di HP.",
        bold_prefix="2. Kunci Cadangan Fisik (Emergency Revocation Key): ")

    add_styled_heading(doc, "5.2. Penolakan Penambahan HP Liar (Cegah Penyusup)", level=2)
    add_paragraph_styled(doc,
        "Sistem secara tegas menonaktifkan fitur penambahan HP baru dari menu profil harian. Hal ini dilakukan untuk mencegah pendaftaran HP orang lain atau pihak ketiga yang tidak berkepentingan.")

    add_styled_heading(doc, "5.3. Deteksi HP Hilang/Rusak & Auto-Revoke Kunci Lama", level=2)
    add_paragraph_styled(doc,
        "Jika HP Bos suatu hari hilang, dicuri, atau rusak, Bos membuka browser di laptop dan memasukkan Kunci Cadangan Fisik pada layar pemulihan 2FA. Begitu kunci ini dimasukkan dan diverifikasi:",
        bold_prefix="• Prosedur Pemulihan HP Hilang: ")
    add_paragraph_styled(doc,
        "Sistem seketika mengetahui bahwa HP lama Bos bermasalah. Detik itu juga, sistem LANGSUNG MENGHANGUSKAN DAN MEMUTUSKAN Kunci 2FA di HP lama tersebut. Jika HP lama ditemukan atau dicuri oknum lain, token 2FA di HP lama itu OTOMATIS MATI dan tidak dapat digunakan lagi.",
        bold_prefix="1. Auto-Revoke Kunci HP Lama: ")
    add_paragraph_styled(doc,
        "Sistem langsung men-generate QR Code 2FA BARU (dengan kunci rahasia baru yang berbeda total) untuk di-scan oleh Bos menggunakan HP BARU miliknya.",
        bold_prefix="2. Penerbitan QR Code Baru di HP Baru: ")
    add_paragraph_styled(doc,
        "Sistem mencetak ulang 1 lembar fisik Kunci Cadangan Darurat Baru untuk disimpan kembali oleh Bos di brankas kerjanya (tanpa perlu kewajiban segel amplop yang rumit).",
        bold_prefix="3. Cetak Kunci Cadangan Baru: ")

    add_callout(doc,
        "Dengan protokol Auto-Revoke, kehilangan HP tidak lagi menjadi momok keamanan. Begitu lembar cetak cadangan digunakan, HP lama langsung kehilangan seluruh hak akses secara instan.",
        title="PROTEKSI KEHILANGAN PERANGKAT (DEVICE LOSS PROTECTION)")

    # =========================================================
    # BAB 6: ENKRIPSI DATABASE BERLAPIS (LAYERED DEFENSE)
    # =========================================================
    add_styled_heading(doc, "BAB 6: ENKRIPSI DATABASE BERLAPIS (LAYERED DEFENSE)", level=1)

    add_paragraph_styled(doc,
        "Untuk mencegah kebocoran data jika harddisk atau berkas disalin secara tidak sah, sistem menerapkan 2 lapis enkripsi:")
    add_paragraph_styled(doc,
        "Seluruh kolom sensitif (NIK, Nama Lengkap, Pendapatan, Alamat RT/RW, Metadata Akun) dienkripsi secara transparan menggunakan algoritma AES-256-CBC bawaan Laravel Eloquent casting. Pada berkas fisik database.sqlite, kolom-kolom ini tersimpan sebagai teks teracak (ciphertext: 'eyJpdiI6...'). Berkas tidak dapat dibaca oleh software SQLite viewer biasa.",
        bold_prefix="• Lapis Aplikasi (Laravel Native Encrypted Cast): ")
    add_paragraph_styled(doc,
        "Folder penyimpanan analitik DuckDB dan berkas SQLite diproteksi menggunakan Windows Access Control List (ACL) yang hanya dapat dibaca oleh identitas pengguna servis lokal sistem.",
        bold_prefix="• Lapis Penyimpanan Sistem: ")

    # =========================================================
    # BAB 7: PROTEKSI ARSIP ZIP APLIKASI (PASSWORD 20 KARAKTER AUTO-RESET)
    # =========================================================
    add_styled_heading(doc, "BAB 7: PROTEKSI ARSIP ZIP APLIKASI (PASSWORD 20 KARAKTER AUTO-RESET)", level=1)

    add_paragraph_styled(doc,
        "Untuk keamanan data dan folder aplikasi pada level storage dan cadangan arsip:")
    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi (ZIP) dikunci menggunakan kata sandi sepanjang 20 karakter dengan pola acak tingkat tinggi (kombinasi huruf besar, huruf kecil, angka, dan simbol acak). Contoh: K9#mQ2$xL8!vW4&yP1*z.",
        bold_prefix="1. Password ZIP 20 Karakter Acak: ")
    add_paragraph_styled(doc,
        "Password ZIP 20 karakter ini dicetak pada lembar fisik pemulihan aplikasi untuk disimpan oleh Bos di brankas.",
        bold_prefix="2. Lembar Cetak Master ZIP: ")
    add_paragraph_styled(doc,
        "Setiap kali berkas ZIP cadangan diekstrak atau dibuka untuk pemulihan server di komputer baru, sistem mendeteksi peristiwa ekstraksi tersebut. Detik itu juga, sistem otomatis MENGHANGUSKAN password 20 karakter lama dan MERESET password baru 20 karakter acak yang berbeda, lalu mengemas ulang arsip ZIP dengan password baru tersebut.",
        bold_prefix="3. Mekanisme Auto-Reset Saat ZIP Terbuka: ")

    # =========================================================
    # BAB 8: TATA CARA PENCADANGAN APLIKASI & DATA (BACKUP SOP)
    # =========================================================
    add_styled_heading(doc, "BAB 8: TATA CARA PENCADANGAN APLIKASI & DATA (BACKUP SOP)", level=1)

    add_paragraph_styled(doc,
        "Sistem PADU v2.0 menyediakan dua prosedur pencadangan operasional mandiri:")
    add_paragraph_styled(doc,
        "Pimpinan cukup masuk ke panel Super Admin -> Menu 'Pemeliharaan' -> Klik 'Buat Cadangan Baru'. Sistem mengeksekusi perintah SQLite vacuum into dan DuckDB checkpoint tanpa mengunci tabel, menghasilkan berkas arsip berstempel waktu (PADU_BACKUP_YYYYMMDD.padubak) yang terunduh langsung ke laptop Pimpinan untuk disimpan di Flashdisk pribadi.",
        bold_prefix="• Metode 1 (One-Click Backup di Web): ")
    add_paragraph_styled(doc,
        "Disediakan skrip offline cadangkan_padu.bat di server yang dapat dijalankan secara berkala atau dijadwalkan otomatis melalui Windows Task Scheduler ke partisi penyimpanan cadangan (D:\\BACKUP_PADU\\).",
        bold_prefix="• Metode 2 (Skrip Server Otomatis): ")

    # =========================================================
    # BAB 9: MODEL DISTRIBUSI LEPAS KUNCI & BAST
    # =========================================================
    add_styled_heading(doc, "BAB 9: MODEL DISTRIBUSI LEPAS KUNCI (ZERO-KNOWLEDGE BAST)", level=1)

    add_paragraph_styled(doc,
        "Pengembang menyerahkan aplikasi dalam keadaan utuh tanpa hak retensi maupun akses terselubung:")
    add_paragraph_styled(doc,
        "Sistem tidak memerlukan aktivasi serial tahunan atau otorisasi tanda tangan developer di kemudian hari.",
        bold_prefix="• Tanpa Lisensi Berkala: ")
    add_paragraph_styled(doc,
        "Kunci enkripsi master digenerate secara lokal di PC Server saat Pimpinan login dan melakukan set password pertama kali.",
        bold_prefix="• Inisialisasi Mandiri oleh Pimpinan: ")
    add_paragraph_styled(doc,
        "Berita Acara Serah Terima (BAST) menegaskan pengalihan tanggung jawab pemeliharaan, keamanan fisik, dan kerahasiaan kata sandi secara mutlak kepada pihak Klien.",
        bold_prefix="• Klausul BAST: ")

    # =========================================================
    # BAB 10: PROSEDUR PENGEMBANG BARU & TATA KELOLA PEMBARUAN (DEVELOPER ONBOARDING & PATCH SOP)
    # =========================================================
    add_styled_heading(doc, "BAB 10: PROSEDUR PENGEMBANG BARU & TATA KELOLA PEMBARUAN (PATCH SOP)", level=1)

    add_paragraph_styled(doc,
        "Untuk mengantisipasi penunjukan programmer/developer baru di masa depan (baik staf IT internal maupun vendor baru) guna menambah fitur atau memperbaiki sistem tanpa merusak keamanan:",
        bold_prefix="• Tata Kelola Pengembang Masa Depan: ")

    add_paragraph_styled(doc,
        "Sesuai UU Perlindungan Data Pribadi (UU PDP), developer baru TIDAK BOLEH dan TIDAK PERLU memegang database asli warga kantor. Developer baru hanya menerima 'Clean Source Code' dan database data tiruan (dummy data) sebanyak 10.000 data palsu melalui perintah: php artisan db:seed.",
        bold_prefix="1. Pemisahan Kode vs Data Nyata Warga: ")

    add_paragraph_styled(doc,
        "Aplikasi dibekali isolasi lingkungan di file .env. Jika APP_ENV=local (laptop developer baru), proteksi Hardware Lock otomatis NONAKTIF sehingga developer baru bebas koding di laptop apapun. Saat dipasang di server kantor dengan APP_ENV=production, Hardware Lock dan Enkripsi otomatis AKTIF PENUH.",
        bold_prefix="2. Isolasi Lingkungan Koding (Local Sandbox): ")

    add_paragraph_styled(doc,
        "Developer baru tidak mengotak-atik server secara kasar, melainkan cukup mengemas kodingan barunya menjadi 1 berkas: 'update_vX.Y.zip'. Pimpinan mengunggah file tersebut melalui menu web Super Admin: 'Pembaruan Sistem' -> 'Unggah Patch'. Sistem mengekstrak kode baru secara aman, database asli tetap utuh, dan hardware lock tetap terkunci sempurna.",
        bold_prefix="3. Alur Pembaruan Sistem via Paket Patch ZIP: ")

    add_paragraph_styled(doc,
        "Jika developer baru harus memeriksa langsung PC Server kantor saat terjadi insiden, pekerjaan dilakukan di bawah pendampingan Pimpinan dengan mengaktifkan mode pemeliharaan (php artisan down). Seluruh aktivitas tercatat di audit trail log.",
        bold_prefix="4. Pemeliharaan Lapangan Terkendali (Supervised Access): ")

    add_callout(doc,
        "Dengan prosedur ini, Klien bebas menunjuk developer baru kapan saja tanpa khawatir data warga bocor atau kunci hardware server rusak.",
        title="JAMINAN KONTINUITAS SISTEM JANGKA PANJANG")

    # =========================================================
    # BAB 11: RENCANA KERJA & JADWAL IMPLEMENTASI v2.0
    # =========================================================
    add_styled_heading(doc, "BAB 11: RENCANA KERJA & JADWAL IMPLEMENTASI v2.0", level=1)

    tbl_plan = doc.add_table(rows=5, cols=3)
    tbl_plan.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_plan, color="CBD5E1")

    plan_headers = ["Fase", "Rincian Pekerjaan Teknis", "Target Luaran (Deliverables)"]
    for i, h in enumerate(plan_headers):
        cell = tbl_plan.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9.5); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=90, bottom=90, left=120, right=120)

    plan_rows = [
        ("Fase 1: Penyelarasan Blueprint & Diagram v2.0", 
         "Pembaruan DFD Level 0, 1, 2, ERD, dan Swimlane dengan alur Two-Step Email-First Login, 2FA HP cerdas, dan proteksi ZIP auto-reset.",
         "Berkas Draw.io v2.0 valid & teruji."),
        ("Fase 2: Backend Auth 2-Step & Enkripsi", 
         "Implementasi kontroler login bertahap (Email -> Password), casting encrypted AES-256 pada Model, dan modul 2FA TOTP RFC 6238 offline.",
         "Engine Otentikasi Bertahap & 2FA aktif."),
        ("Fase 3: Generator ZIP 20 Karakter & Cetak Fisik", 
         "Pengembangan modul auto-reset password ZIP 20 karakter, generator template cetak lembar fisik manual key 2FA & master key, dan One-Click Backup UI.",
         "Fitur Auto-Reset ZIP & Backup operasional."),
        ("Fase 4: Pengujian, Simulasi & BAST", 
         "Uji coba skenario lupa password Super Admin via 2FA HP, simulasi ganti HP via lembar cetak manual key (auto-revoke), uji ekstraksi ZIP auto-reset, dan penandatanganan BAST Lepas Kunci.",
         "Sistem PADU v2.0 operasional 100% mandiri.")
    ]

    for idx, (fase, rincian, target) in enumerate(plan_rows):
        row = tbl_plan.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([fase, rincian, target]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=80, bottom=80, left=120, right=120)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(9)
            if c_idx == 0:
                r.font.bold = True
                r.font.color.rgb = RGBColor(16, 44, 87)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # =========================================================
    # BAB 12: MATRIKS ANALISIS RISIKO & MITIGASI KEAMANAN
    # =========================================================
    add_styled_heading(doc, "BAB 12: MATRIKS ANALISIS RISIKO & MITIGASI KEAMANAN", level=1)

    tbl_risk = doc.add_table(rows=6, cols=3)
    tbl_risk.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_risk, color="CBD5E1")

    risk_headers = ["Identifikasi Potensi Risiko", "Dampak", "Rencana Mitigasi Teknis"]
    for i, h in enumerate(risk_headers):
        cell = tbl_risk.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9.5); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=90, bottom=90, left=120, right=120)

    risk_rows = [
        ("Pencurian folder server via flashdisk oleh oknum dalam", "TINGGI", "Database terenkripsi AES-256 dan terikat hardware server. Berkas tidak dapat dibuka tanpa kunci derivasi silikon asli."),
        ("Pimpinan lupa kata sandi 15 karakter", "SEDANG", "Pimpinan memulihkan hak akses secara mandiri menggunakan token 6 digit dari aplikasi Google Authenticator di HP pribadinya."),
        ("HP Pimpinan hilang / dicuri / rusak", "TINGGI", "Pimpinan memasukkan Kunci Cadangan Cetak -> Sistem otomatis MENGHANGUSKAN kunci di HP lama (auto-revoke) dan menerbitkan QR Code baru untuk HP baru."),
        ("Password arsip ZIP diintip saat ekstraksi pemulihan", "TINGGI", "Sistem otomatis MENGHANGUSKAN password lama dan MERESET password baru 20 karakter acak seketika folder ZIP terbuka."),
        ("Keterikatan vendor / Tuntutan hukum di kemudian hari", "TINGGI", "Penandatanganan BAST Lepas Kunci (Zero-Knowledge) membuktikan pengembang tidak memiliki akses dan kunci master setelah serah terima.")
    ]

    for idx, (resiko, dampak, mitigasi) in enumerate(risk_rows):
        row = tbl_risk.rows[idx + 1]
        bg = "F8FAFC" if idx % 2 == 0 else "FFFFFF"
        for c_idx, val in enumerate([resiko, dampak, mitigasi]):
            c = row.cells[c_idx]
            set_cell_background(c, bg)
            set_cell_margins(c, top=80, bottom=80, left=120, right=120)
            p = c.paragraphs[0]
            p.paragraph_format.space_before = Pt(2); p.paragraph_format.space_after = Pt(2)
            r = p.add_run(val)
            r.font.name = 'Arial'; r.font.size = Pt(9)
            if c_idx == 1:
                r.font.bold = True
                r.font.color.rgb = RGBColor(185, 28, 28) if val == "TINGGI" else RGBColor(202, 138, 4)
            else:
                r.font.color.rgb = RGBColor(51, 65, 85)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # =========================================================
    # BAB 13: KRITERIA KEBERHASILAN & LEMBAR PENGESAHAN (SIGN-OFF)
    # =========================================================
    add_styled_heading(doc, "BAB 13: KRITERIA KEBERHASILAN & LEMBAR PENGESAHAN", level=1)

    crit = [
        ("Otentikasi Bertahap (Email-First)", "Layar pertama meminta email, layar kedua mengidentifikasi role pengguna dan meminta kata sandi min 15 karakter."),
        ("Pemulihan Lupa Sandi Super Admin via 2FA HP", "Super Admin berhasil mereset kata sandi menggunakan token 6 digit dari Google Authenticator di HP pribadinya."),
        ("Auto-Revoke 2FA Saat HP Hilang", "Sistem sukses menghanguskan kunci HP lama dan menerbitkan QR Code baru setelah kunci cadangan cetak dimasukkan."),
        ("Auto-Reset Password ZIP 20 Karakter", "Sistem berhasil mereset password 20 karakter baru secara otomatis begitu arsip ZIP cadangan diekstrak."),
        ("Kemandirian Penuh (Zero-Knowledge)", "Sistem berjalan normal di jaringan LAN kantor tanpa ketergantungan koneksi internet dan tanpa campur tangan developer.")
    ]

    for title, desc in crit:
        add_paragraph_styled(doc, desc, bold_prefix=f"✓ {title}: ")

    # Tanda Tangan Formal
    doc.add_paragraph().paragraph_format.space_after = Pt(12)
    p_sign_hdr = doc.add_paragraph()
    r_sh = p_sign_hdr.add_run("LEMBAR PERSETUJUAN PROPOSAL TEKNIS PEMBARUAN v2.0 ENTERPRISE")
    r_sh.font.name = 'Arial'; r_sh.font.size = Pt(11); r_sh.font.bold = True
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
            cell.paragraphs[0].paragraph_format.space_before = Pt(18)
            cell.paragraphs[0].paragraph_format.space_after = Pt(18)

    p_n0 = tbl_sign.rows[3].cells[0].paragraphs[0]
    r_n0 = p_n0.add_run("( _____________________________ )\nLead System Architect & Developer")
    r_n0.font.name = 'Arial'; r_n0.font.size = Pt(9.5); r_n0.font.bold = True

    p_n1 = tbl_sign.rows[3].cells[1].paragraphs[0]
    r_n1 = p_n1.add_run("( _____________________________ )\nProject Sponsor / Super Admin Klien")
    r_n1.font.name = 'Arial'; r_n1.font.size = Pt(9.5); r_n1.font.bold = True

    doc.save(output_path)
    print(f"Full Proposal Word document successfully generated at: {output_path}")

if __name__ == "__main__":
    out = os.path.abspath("Proposal_Update_Sistem_PADU_v2.0_Lengkap_A_sampai_Z.docx")
    create_full_proposal_docx(out)
    
    # Juga coba perbarui Proposal_Update_Sistem_PADU_v2.0_Updated.docx
    try:
        out_updated = os.path.abspath("Proposal_Update_Sistem_PADU_v2.0_Updated.docx")
        create_full_proposal_docx(out_updated)
    except PermissionError:
        pass
