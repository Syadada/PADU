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

def set_cell_margins(cell, top=120, bottom=120, left=150, right=150):
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

def add_callout(doc, text, title="CATATAN KEAMANAN PENTING", border_color="102C57", bg_color="F1F5F9"):
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
    run_title.font.size = Pt(10.5)
    run_title.font.bold = True
    run_title.font.color.rgb = RGBColor(16, 44, 87)
    
    run_text = p.add_run(text)
    run_text.font.name = 'Arial'
    run_text.font.size = Pt(10)
    run_text.font.color.rgb = RGBColor(40, 40, 40)
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_before = Pt(0)
    p_after.paragraph_format.space_after = Pt(6)

def add_styled_heading(doc, text, level):
    h = doc.add_heading(level=level)
    run = h.add_run(text)
    run.font.name = 'Arial'
    if level == 1:
        run.font.size = Pt(15)
        run.font.bold = True
        run.font.color.rgb = RGBColor(16, 44, 87) # Deep Navy
        h.paragraph_format.space_before = Pt(18)
        h.paragraph_format.space_after = Pt(8)
    elif level == 2:
        run.font.size = Pt(12.5)
        run.font.bold = True
        run.font.color.rgb = RGBColor(30, 80, 130) # Slate Navy
        h.paragraph_format.space_before = Pt(14)
        h.paragraph_format.space_after = Pt(6)
    elif level == 3:
        run.font.size = Pt(11)
        run.font.bold = True
        run.font.color.rgb = RGBColor(50, 50, 50)
        h.paragraph_format.space_before = Pt(10)
        h.paragraph_format.space_after = Pt(4)
    return h

def add_paragraph_styled(doc, text, bold_prefix="", space_after=6, italic=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        r_pre = p.add_run(bold_prefix)
        r_pre.font.name = 'Arial'
        r_pre.font.size = Pt(10.5)
        r_pre.font.bold = True
        r_pre.font.color.rgb = RGBColor(20, 20, 20)
    r_body = p.add_run(text)
    r_body.font.name = 'Arial'
    r_body.font.size = Pt(10.5)
    r_body.font.italic = italic
    r_body.font.color.rgb = RGBColor(40, 40, 40)
    return p

def create_proposal_docx(output_path):
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
        r_hdr = p_hdr.add_run("PROPOSAL TEKNIS PEMBARUAN SISTEM PADU v2.0 | SANGAT RAHASIA")
        r_hdr.font.name = 'Arial'
        r_hdr.font.size = Pt(8.5)
        r_hdr.font.color.rgb = RGBColor(140, 140, 140)
        
        footer = section.footer
        p_ftr = footer.paragraphs[0]
        p_ftr.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r_ftr = p_ftr.add_run("Sistem Informasi PADU v2.0 Enterprise — Hak Cipta & Kedaulatan Milik Klien")
        r_ftr.font.name = 'Arial'
        r_ftr.font.size = Pt(8.5)
        r_ftr.font.color.rgb = RGBColor(140, 140, 140)

    # =========================================================
    # COVER PAGE
    # =========================================================
    p_inst = doc.add_paragraph()
    p_inst.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_inst.paragraph_format.space_before = Pt(40)
    p_inst.paragraph_format.space_after = Pt(6)
    r_inst = p_inst.add_run("PROPOSAL TEKNIS PENGEMBANGAN SISTEM INFORMASI")
    r_inst.font.name = 'Arial'
    r_inst.font.size = Pt(12)
    r_inst.font.bold = True
    r_inst.font.color.rgb = RGBColor(100, 116, 139)

    p_main_title = doc.add_paragraph()
    p_main_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_main_title.paragraph_format.space_before = Pt(12)
    p_main_title.paragraph_format.space_after = Pt(8)
    r_title = p_main_title.add_run("SISTEM INFORMASI PADU v2.0\n(Pengolah & Analisis Data Terpadu)")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(22)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(16, 44, 87)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_before = Pt(8)
    p_sub.paragraph_format.space_after = Pt(36)
    r_sub = p_sub.add_run("Penyempurnaan Arsitektur Keamanan Berlapis, Kedaulatan Mandiri Klien (Zero-Knowledge),\nAlur Otentikasi Bertahap (Email-First Login), 2FA Offline HP Super Admin,\ndan Proteksi Arsip ZIP Aplikasi (Password 20 Karakter Acak Auto-Reset)")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(11)
    r_sub.font.italic = True
    r_sub.font.color.rgb = RGBColor(53, 89, 143)

    # Box Metadata Cover
    tbl_meta = doc.add_table(rows=6, cols=2)
    tbl_meta.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_meta, color="E2E8F0")
    
    meta_data = [
        ("Nomor Dokumen", "PROP/PADU-SEC/IX/2026/V2.0"),
        ("Klasifikasi", "SANGAT RAHASIA (Strictly Confidential / Klien & Internal)"),
        ("Versi Rilis Rencana", "v2.0 Enterprise (Rilis Kedaulatan & Keamanan Mandiri)"),
        ("Tanggal Penyusunan", "24 September 2026"),
        ("Penyusun", "Tim Pengembang Sistem Informasi PADU"),
        ("Sasaran Pengesahan", "Pimpinan Proyek & Super Admin (Klien)")
    ]
    
    for idx, (label, val) in enumerate(meta_data):
        row = tbl_meta.rows[idx]
        c0, c1 = row.cells[0], row.cells[1]
        c0.width = Inches(2.2)
        c1.width = Inches(4.3)
        set_cell_background(c0, "F8FAFC")
        set_cell_background(c1, "FFFFFF")
        set_cell_margins(c0, top=100, bottom=100, left=140, right=140)
        set_cell_margins(c1, top=100, bottom=100, left=140, right=140)
        
        p0 = c0.paragraphs[0]
        p0.paragraph_format.space_before = Pt(2)
        p0.paragraph_format.space_after = Pt(2)
        r0 = p0.add_run(label)
        r0.font.name = 'Arial'
        r0.font.size = Pt(10)
        r0.font.bold = True
        r0.font.color.rgb = RGBColor(51, 65, 85)
        
        p1 = c1.paragraphs[0]
        p1.paragraph_format.space_before = Pt(2)
        p1.paragraph_format.space_after = Pt(2)
        r1 = p1.add_run(val)
        r1.font.name = 'Arial'
        r1.font.size = Pt(10)
        r1.font.color.rgb = RGBColor(15, 23, 42)

    doc.add_page_break()

    # =========================================================
    # BAB 1: LATAR BELAKANG & PRINSIP KEDAULATAN SISTEM
    # =========================================================
    add_styled_heading(doc, "BAB 1: LATAR BELAKANG & PRINSIP KEDAULATAN SISTEM", level=1)
    
    add_paragraph_styled(doc, 
        "Sistem Informasi PADU (Pengolah & Analisis Data Terpadu) pada rilis v2.0 Enterprise dirancang sebagai sistem terintegrasi yang kokoh, mandiri, dan beroperasi penuh secara lokal (100% offline).")

    add_paragraph_styled(doc,
        "Guna memberikan kebebasan mutlak kepada pihak klien dan melepaskan keterikatan pengembang pasca-serah terima resmi, disepakati empat pilar arsitektur utama:")

    add_paragraph_styled(doc,
        "Setelah Berita Acara Serah Terima (BAST) ditandatangani, tim pengembang tidak menyimpan kredensial, tidak memegang master key, dan tidak menyediakan backdoor apapun. Seluruh tata kelola dan tanggung jawab keamanan data berada 100% di tangan Super Admin Klien.",
        bold_prefix="1. Serah Terima Lepas Kunci (Zero-Knowledge Handover): ")

    add_paragraph_styled(doc,
        "Mengeliminasi webcam, pustaka OpenCV, dan MediaPipe. Aplikasi bertransformasi menjadi web murni yang sangat cepat dan diakses bersama via Wi-Fi/LAN kantor tanpa membutuhkan internet.",
        bold_prefix="2. Arsitektur Web Murni Tanpa Biometrik: ")

    add_paragraph_styled(doc,
        "Mengadopsi alur otentikasi dua langkah (Two-Step Email-First Login). Sistem mengenali tipe hak akses pada tahap memasukkan kata sandi dan menyediakan penanganan lupa sandi yang terspesialisasi (2FA HP untuk Super Admin, dan Admin-Assisted untuk Operator).",
        bold_prefix="3. Alur Otentikasi Bertahap & Diferensiasi Hak Akses: ")

    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi dilindungi password 20 karakter acak tingkat tinggi yang otomatis me-reset dirinya sendiri seketika berkas ZIP dibuka, serta didukung lembar cetak fisik untuk Master Key di brankas Pimpinan.",
        bold_prefix="4. Proteksi Arsip ZIP Aplikasi (Password 20 Karakter Auto-Reset): ")

    # =========================================================
    # BAB 2: ALUR OTENTIKASI BERTAHAP (TWO-STEP EMAIL-FIRST LOGIN)
    # =========================================================
    add_styled_heading(doc, "BAB 2: ALUR OTENTIKASI BERTAHAP (EMAIL-FIRST LOGIN)", level=1)

    add_paragraph_styled(doc,
        "Untuk meningkatkan keamanan dan membedakan penanganan pengguna sejak awal, sistem menerapkan urutan login bertahap:")

    add_styled_heading(doc, "2.1. Langkah 1: Masukkan Email Akun", level=2)
    add_paragraph_styled(doc,
        "Pengguna membuka alamat URL server di browser (http://192.168.1.10:8000) -> Mengetikkan alamat email terdaftar -> Klik tombol 'Lanjutkan / Next'.")

    add_styled_heading(doc, "2.2. Langkah 2: Masukkan Kata Sandi & Pembeda Hak Akses", level=2)
    add_paragraph_styled(doc,
        "Pada layar kedua, sistem membaca identitas email yang telah diverifikasi pada database lokal:")
    add_paragraph_styled(doc,
        "Sistem menampilkan nama pengguna dan lencana role (Badge 'Super Admin' atau 'Operator Data') untuk memastikan pengguna tidak salah akun.",
        bold_prefix="• Identifikasi Role Otomatis: ")
    add_paragraph_styled(doc,
        "Pengguna mengetikkan kata sandi akun (standar minimal 15 karakter).",
        bold_prefix="• Input Kata Sandi: ")
    add_paragraph_styled(doc,
        "Tersedia tautan 'Lupa Kata Sandi?' dengan percabangan logika otomatis:",
        bold_prefix="• Penanganan Lupa Sandi Spesifik: ")

    tbl_lupa = doc.add_table(rows=3, cols=3)
    tbl_lupa.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(tbl_lupa, color="CBD5E1")

    h_lupa = ["Tipe Pengguna", "Mekanisme Saat Klik 'Lupa Sandi'", "Tindakan Sistem"]
    for i, h in enumerate(h_lupa):
        cell = tbl_lupa.rows[0].cells[i]
        set_cell_background(cell, "102C57")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'; r.font.size = Pt(9.5); r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_margins(cell, top=100, bottom=100, left=120, right=120)

    rows_lupa = [
        ("Super Admin (Pimpinan)", 
         "Verifikasi 2FA Offline HP (Google Authenticator / FreeOTP)", 
         "Masukkan 6 angka acak yang berputar di HP Super Admin -> Terverifikasi sah -> Muncul pop-up membuat kata sandi baru (min 15 karakter)."),
        ("Operator / Pegawai", 
         "Admin-Assisted Offline Reset", 
         "Muncul dialog: 'Silakan hubungi Super Admin untuk mereset kata sandi akun Anda'. Super Admin mereset via panel Pengelolaan Pengguna.")
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
    # BAB 3: SISTEM 2FA HP SUPER ADMIN & LEMBAR CETAK CADANGAN
    # =========================================================
    add_styled_heading(doc, "BAB 3: SISTEM 2FA HP SUPER ADMIN & LEMBAR CETAK CADANGAN", level=1)

    add_paragraph_styled(doc,
        "Untuk melindungi akun Super Admin tanpa ketergantungan internet/pulsa SMS:")

    add_styled_heading(doc, "3.1. Inisialisasi 2FA Saat Login Pertama Kali", level=2)
    add_paragraph_styled(doc,
        "Saat Super Admin pertama kali login dan mengganti kata sandi bawaan, sistem menampilkan QR Code lokal di layar laptop Bos. Bos membuka aplikasi Google Authenticator/FreeOTP di HP pribadinya dan melakukan pemindaian (scan QR Code) serta verifikasi 6 digit token.")

    add_styled_heading(doc, "3.2. Pembedaan Kunci 2FA di HP vs Kunci Cadangan Cetak (Berbeda Total)", level=2)
    add_paragraph_styled(doc,
        "Sistem membedakan secara tegas antara kunci operasional di HP dengan kunci cadangan darurat:",
        bold_prefix="• Dua Kunci Independen: ")
    add_paragraph_styled(doc,
        "Kunci yang tersimpan di aplikasi Google Authenticator HP Bos berfungsi untuk rotasi token 6 digit harian (login / lupa password normal).",
        bold_prefix="1. Kunci 2FA di HP: ")
    add_paragraph_styled(doc,
        "Kunci yang dicetak pada lembar fisik adalah 'Emergency 2FA Revocation Key' (Kunci Pemulihan Darurat) yang kodenya BERBEDA TOTAL dari kunci di HP.",
        bold_prefix="2. Kunci Cadangan Cetak: ")

    add_styled_heading(doc, "3.3. Deteksi HP Hilang/Rusak & Auto-Revoke Kunci Lama", level=2)
    add_paragraph_styled(doc,
        "Jika HP Bos hilang, dicuri, atau rusak, Bos memasukkan Kunci Cadangan Cetak pada layar pemulihan 2FA. Begitu kunci ini dimasukkan dan diverifikasi:",
        bold_prefix="• Prosedur Pemulihan HP Hilang: ")
    add_paragraph_styled(doc,
        "Sistem langsung MENGHANGUSKAN & MEMUTUSKAN Kunci 2FA lama di HP tersebut. Jika HP lama ditemukan atau dicuri oknum lain, token 2FA di HP lama itu OTOMATIS MATI dan tidak bisa digunakan lagi.",
        bold_prefix="1. Auto-Revoke HP Lama: ")
    add_paragraph_styled(doc,
        "Sistem seketika men-generate QR Code 2FA BARU dengan kunci rahasia baru yang berbeda total untuk di-scan oleh Bos menggunakan HP BARU miliknya.",
        bold_prefix="2. Penerbitan QR Code Baru di HP Baru: ")
    add_paragraph_styled(doc,
        "Sistem mencetak ulang 1 lembar fisik Kunci Cadangan Darurat Baru untuk disimpan kembali oleh Bos di laci/brankas kerjanya.",
        bold_prefix="3. Cetak Kunci Cadangan Baru: ")

    # =========================================================
    # BAB 4: PROTEKSI ARSIP ZIP APLIKASI (PASSWORD 20 KARAKTER AUTO-RESET)
    # =========================================================
    add_styled_heading(doc, "BAB 4: PROTEKSI ARSIP ZIP APLIKASI (PASSWORD 20 KARAKTER AUTO-RESET)", level=1)

    add_paragraph_styled(doc,
        "Untuk keamanan data dan folder aplikasi pada level storage/mesin:")

    add_paragraph_styled(doc,
        "Berkas arsip cadangan aplikasi (ZIP) dikunci menggunakan kata sandi sepanjang 20 karakter dengan pola acak tingkat tinggi (kombinasi huruf besar, huruf kecil, angka, dan simbol acak). Contoh: K9#mQ2$xL8!vW4&yP1*z.",
        bold_prefix="1. Password ZIP 20 Karakter Acak: ")

    add_paragraph_styled(doc,
        "Password ZIP 20 karakter ini dicetak pada lembar fisik pemulihan aplikasi untuk disimpan oleh Bos.",
        bold_prefix="2. Lembar Cetak Master ZIP: ")

    add_paragraph_styled(doc,
        "Setiap kali berkas ZIP cadangan diekstrak atau dibuka untuk pemulihan server di komputer baru, sistem mendeteksi peristiwa ekstraksi tersebut. Detik itu juga, sistem otomatis MENGHANGUSKAN password 20 karakter lama dan MERESET password baru 20 karakter acak yang berbeda, lalu mengemas ulang arsip ZIP dengan password baru tersebut.",
        bold_prefix="3. Mekanisme Auto-Reset Saat ZIP Terbuka: ")

    add_callout(doc,
        "Dengan fitur Auto-Reset, jika ada oknum yang sempat mengintip atau mencatat password 20 karakter saat ZIP dibuka, password tersebut langsung menjadi sampah (tidak berlaku lagi) untuk ekstraksi berikutnya.",
        title="JAMINAN KEAMANAN ONE-TIME KEY ZIP")

    # =========================================================
    # BAB 5: TATA CARA PENCADANGAN APLIKASI & DATA (BACKUP SOP)
    # =========================================================
    add_styled_heading(doc, "BAB 5: TATA CARA PENCADANGAN APLIKASI & DATA (BACKUP SOP)", level=1)

    add_paragraph_styled(doc,
        "Sistem PADU v2.0 menyediakan dua prosedur pencadangan operasional:")

    add_paragraph_styled(doc,
        "Pimpinan cukup masuk ke panel Super Admin -> Menu 'Pemeliharaan' -> Klik 'Buat Cadangan'. Sistem mengemas database SQLite dan DuckDB ke dalam arsip berstempel waktu (PADU_BACKUP_YYYYMMDD.padubak) yang terunduh langsung ke laptop Pimpinan.",
        bold_prefix="• Metode 1 (One-Click Backup di Web): ")

    add_paragraph_styled(doc,
        "Disediakan skrip cadangkan_padu.bat di server yang dapat dijalankan secara berkala atau dijadwalkan otomatis melalui Windows Task Scheduler ke partisi penyimpanan cadangan (D:\\BACKUP_PADU\\).",
        bold_prefix="• Metode 2 (Skrip Server Otomatis): ")

    # =========================================================
    # BAB 6: RENCANA KERJA & JADWAL IMPLEMENTASI v2.0
    # =========================================================
    add_styled_heading(doc, "BAB 6: RENCANA KERJA & JADWAL IMPLEMENTASI v2.0", level=1)

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
        set_cell_margins(cell, top=100, bottom=100, left=120, right=120)

    plan_rows = [
        ("Fase 1: Penyelarasan Blueprint & Diagram v2.0", 
         "Pembaruan DFD Level 0, 1, 2, ERD, dan Swimlane dengan alur Two-Step Email-First Login dan 2FA HP tanpa elemen biometrik.",
         "Berkas Draw.io v2.0 valid & teruji."),
        ("Fase 2: Backend Auth 2-Step & Enkripsi", 
         "Implementasi kontroler login bertahap (Email -> Password), casting encrypted AES-256 pada Model, dan modul 2FA TOTP RFC 6238 offline.",
         "Engine Otentikasi Bertahap & 2FA aktif."),
        ("Fase 3: Generator ZIP 20 Karakter & Cetak Fisik", 
         "Pengembangan modul auto-reset password ZIP 20 karakter, generator template cetak lembar fisik manual key 2FA & master key, dan One-Click Backup UI.",
         "Fitur Auto-Reset ZIP & Backup operasional."),
        ("Fase 4: Pengujian, Simulasi & BAST", 
         "Uji coba skenario lupa password Super Admin via 2FA HP, simulasi ganti HP via lembar cetak manual key, uji ekstraksi ZIP auto-reset, dan penandatanganan BAST Lepas Kunci.",
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
    # BAB 7: KRITERIA KEBERHASILAN & LEMBAR PENGESAHAN
    # =========================================================
    add_styled_heading(doc, "BAB 7: KRITERIA KEBERHASILAN & LEMBAR PENGESAHAN", level=1)

    crit = [
        ("Otentikasi Bertahap (Email-First)", "Layar pertama meminta email, layar kedua mengidentifikasi role pengguna dan meminta kata sandi min 15 karakter."),
        ("Pemulihan Lupa Sandi Super Admin via 2FA HP", "Super Admin berhasil mereset kata sandi menggunakan token 6 digit dari Google Authenticator di HP pribadinya."),
        ("Uji Ganti HP via Lembar Cetak Manual Key", "Kunci manual 2FA pada lembar cetak fisik berhasil di-input ke HP baru tanpa kendala."),
        ("Auto-Reset Password ZIP 20 Karakter", "Sistem berhasil mereset password 20 karakter baru secara otomatis begitu arsip ZIP cadangan diekstrak."),
        ("Kemandirian Penuh (Zero-Knowledge)", "Sistem berjalan normal di jaringan LAN kantor tanpa ketergantungan koneksi internet dan tanpa campur tangan developer.")
    ]

    for title, desc in crit:
        add_paragraph_styled(doc, desc, bold_prefix=f"✓ {title}: ")

    # Tanda Tangan Formal
    doc.add_paragraph().paragraph_format.space_after = Pt(12)
    p_sign_hdr = doc.add_paragraph()
    r_sh = p_sign_hdr.add_run("LEMBAR PERSETUJUAN PROPOSAL TEKNIS PEMBARUAN v2.0")
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
    print(f"Proposal Word v2.0 document successfully updated at: {output_path}")

if __name__ == "__main__":
    out = os.path.abspath("Proposal_Update_Sistem_PADU_v2.0.docx")
    try:
        create_proposal_docx(out)
    except PermissionError:
        out_fallback = os.path.abspath("Proposal_Update_Sistem_PADU_v2.0_Updated.docx")
        print(f"Berkas asli sedang dibuka di Microsoft Word. Menyimpan salinan ke: {out_fallback}")
        create_proposal_docx(out_fallback)
