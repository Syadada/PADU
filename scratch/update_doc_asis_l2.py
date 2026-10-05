import re

path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Dokumentasi_DFD_PADU.md"
with open(path, "r", encoding="utf-8") as f:
    text = f.read()

# Text to insert for 2.3 AS-IS Level 2
asis_l2_md = """
### 2.3 AS-IS Level 2 — Dekomposisi 4 Sub-Sistem Eksisting

DFD Level 2 pada sistem AS-IS menguraikan mekanisme fungsional internal dari 4 sub-proses sistem awal (sebelum penambahan pengamanan biometrik dan masking PII):

---

#### 2.3.1 Level 2: Proses 1.0 (Scan Direktori & Ingesti Data)

Menguraikan deteksi file mentah pada folder lokal `src-dtsen/`, verifikasi berkas, penangkapan trigger HTTP POST dari operator, eksekusi script `fast_import.py`, dan penyaluran *raw stream data*.

```mermaid
flowchart LR
    E1["👤 OPERATOR DATA"]
    D1[("D1: Folder Sumber (src-dtsen/)")]

    P11(("1.1<br/>Auto-Scan Direktori<br/>Sumber src-dtsen/"))
    P12(("1.2<br/>Validasi Format &<br/>Ukuran Berkas"))
    P13(("1.3<br/>Penerimaan Trigger<br/>Impor Lokal (POST)"))
    P14(("1.4<br/>Streaming PyArrow<br/>fast_import.py"))
    P15(("1.5<br/>Penyaluran Stream<br/>Baris Mentah"))

    E1 -->|"Salin Berkas CSV/XLSX"| D1
    D1 -->|"Daftar Berkas Mentah"| P11
    P11 -->|"Metadata File Terdeteksi"| P12
    E1 -->|"Trigger Impor POST"| P13
    P12 -->|"Berkas Valid Siap Impor"| P13
    P13 -->|"Inisialisasi fast_import.py"| P14
    P14 -->|"Raw Chunk Records"| P15
    P15 -->|"Stream Baris Data Mentah ke P2.0"| P15

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E1 entity;
    class P11,P12,P13,P14,P15 process;
    class D1 store;
```

**Kamus Aliran Data Sub-Proses 1.0 (AS-IS):**
1. **E1 $\rightarrow$ D1**: Pengguna meletakkan file fisik `.csv` atau `.xlsx` ke dalam folder `src-dtsen/`.
2. **P11 $\rightarrow$ P12**: Deteksi nama file dan atribut ukuran byte.
3. **P13 $\rightarrow$ P14**: Pemanggilan sub-proses eksekusi script Python `fast_import.py`.
4. **P14 $\rightarrow$ P15**: Konversi blok biner file mentah ke dalam batch *record* PyArrow.
5. **P15**: Output aliran data mentah diteruskan ke Proses 2.0 (Normalisasi).

---

#### 2.3.2 Level 2: Proses 2.0 (Normalisasi & Evaluasi Kualitas Data)

Menguraikan standarisasi nama kolom ke 48 variabel BPS, pengujian aturan validasi dasar, penyimpanan data bersih ke `dtsen_data`, pencatatan error ke `quality_audit_logs`, dan update file status `import_progress.json`.

```mermaid
flowchart LR
    E1["👤 OPERATOR DATA"]
    D2[("D2: dtsen_data DuckDB")]
    D3[("D3: quality_audit_logs DuckDB")]

    P21(("2.1<br/>Mapping Sinonim Kolom<br/>48 Variabel BPS"))
    P22(("2.2<br/>Pemeriksaan Aturan<br/>Kualitas (Rules Engine)"))
    P23(("2.3<br/>Pemisahan Record<br/>Bersih & Error"))
    P24(("2.4<br/>Simpan Data Bersih<br/>ke DuckDB"))
    P25(("2.5<br/>Catat Log Audit Error<br/>ke DuckDB"))
    P26(("2.6<br/>Update Progress JSON<br/>& Metrik Kualitas"))

    P21 -->|"48 Kolom Terpetakan"| P22
    P22 -->|"Record Berstatus Kualitas"| P23
    P23 -->|"Batch Record Bersih (Valid)"| P24
    P23 -->|"Batch Record Anomali (Crit/Warn)"| P25
    P24 -->|"Simpan Data Bersih"| D2
    P25 -->|"Simpan Log Anomali"| D3
    P24 -->|"Status Batch Selesai"| P26
    P26 -->|"Update Progres & Ringkasan Kualitas"| E1

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E1 entity;
    class P21,P22,P23,P24,P25,P26 process;
    class D2,D3 store;
```

**Kamus Aliran Data Sub-Proses 2.0 (AS-IS):**
1. **P21 $\rightarrow$ P22**: Kolom input dinormalisasi sesuai sinonim 48 variabel BPS.
2. **P22**: Evaluasi kondisi:
   - *Critical*: NIK kosong / != 16 angka, Desil diluar 1-10.
   - *Warning*: Field pendukung kosong.
   - *Valid*: Sesuai format.
3. **P24 $\rightarrow$ D2**: Simpan baris valid langsung ke tabel kolumnar DuckDB `dtsen_data`.
4. **P25 $\rightarrow$ D3**: Simpan anomali ke tabel `quality_audit_logs`.
5. **P26 $\rightarrow$ E1**: Tulis persentase ke `import_progress.json` untuk dibaca progress bar UI operator.

---

#### 2.3.3 Level 2: Proses 3.0 (Pencarian, Filter & Agregasi KPI)

Menguraikan *parser* filter, pembentukan query SELECT DuckDB dinamis, eksekusi table scan, penghitungan count metrik KPI, dan perenderan grid **data polos apa adanya tanpa masking identitas (as-is)**.

```mermaid
flowchart LR
    E1["👤 OPERATOR DATA"]
    D2[("D2: dtsen_data DuckDB")]

    P31(("3.1<br/>Input Parser Filter<br/>& Kata Kunci"))
    P32(("3.2<br/>Dynamic SQL Query<br/>Constructor"))
    P33(("3.3<br/>DuckDB Table Scan<br/>Execution"))
    P34(("3.4<br/>KPI Statistics<br/>Calculator (Counts)"))
    P35(("3.5<br/>Render Grid Polos<br/>(Tanpa Masking PII)"))

    E1 -->|"Parameter Filter & Keyword"| P31
    P31 -->|"Parsed Filter Criteria"| P32
    P32 -->|"Query Table Scan"| D2
    D2 -->|"Raw Columnar Records"| P33
    P33 -->|"Aliran Data Numerik"| P34
    P33 -->|"Raw Tabular Records (Polos)"| P35
    P34 -->|"Metrik Ringkasan KPI"| P35
    P35 -->|"Tabel Data Polos & KPI Cards"| E1

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E1 entity;
    class P31,P32,P33,P34,P35 process;
    class D2 store;
```

**Kamus Aliran Data Sub-Proses 3.0 (AS-IS):**
1. **E1 $\rightarrow$ P31**: Parameter filter wilayah, NIK, dan desil.
2. **P32 $\rightarrow$ D2**: Query SQL DuckDB standar tanpa vektorisasi lanjutan.
3. **P33 $\rightarrow$ P35**: Data baris mentah dikirim apa adanya ke view:
   - NIK tampil 16 digit utuh (tanpa sensor).
   - Nama lengkap tampil jelas (tanpa sensor).
   - Gaji dan alamat tampil polos.
4. **P35 $\rightarrow$ E1**: Tampilan tabel HTML 50 baris per halaman dan kartu KPI.

---

#### 2.3.4 Level 2: Proses 4.0 (Ekspor Berkas CSV Mentah)

Menguraikan penerimaan permintaan ekspor, penarikan data bersih atau log error dari DuckDB, format ke teks CSV polos, dan *direct streaming download* ke browser tanpa kompresi arsip ZIP.

```mermaid
flowchart LR
    E1["👤 OPERATOR DATA"]
    D2[("D2: dtsen_data DuckDB")]
    D3[("D3: quality_audit_logs DuckDB")]

    P41(("4.1<br/>Parser Permintaan<br/>Ekspor Berkas"))
    P42(("4.2<br/>Query Dataset Bersih<br/>atau Error Log"))
    P43(("4.3<br/>Raw CSV Formatter<br/>& Text Buffer"))
    P44(("4.4<br/>Direct Browser CSV<br/>File Streamer"))

    E1 -->|"Permintaan Ekspor CSV"| P41
    P41 -->|"Target Tipe Ekspor"| P42
    D2 -->|"Ambil Data Bersih"| P42
    D3 -->|"Ambil Log Error"| P42
    P42 -->|"Raw Tabular Records"| P43
    P43 -->|"Stream Berkas CSV Polos"| P44
    P44 -->|"Stream File Unduhan CSV"| E1

    classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;
    classDef store fill:#3b0764,stroke:#c084fc,stroke-width:2px,color:#fff;
    class E1 entity;
    class P41,P42,P43,P44 process;
    class D2,D3 store;
```

**Kamus Aliran Data Sub-Proses 4.0 (AS-IS):**
1. **E1 $\rightarrow$ P41**: Tombol klik unduh CSV dari browser.
2. **P42 $\rightarrow$ P43**: Penarikan baris data dari `dtsen_data` atau `quality_audit_logs`.
3. **P43 $\rightarrow$ P44**: Penggabungan teks berkoma (.csv) tanpa metadata log atau kompresi ZIP.
4. **P44 $\rightarrow$ E1**: File `.csv` polos diunduh langsung oleh browser pengguna.
"""

# Replace old separator before Section 3 with AS-IS Level 2 section + Section 3
target_split = "## 3. DFD SISTEM TO-BE"
if target_split in text:
    parts = text.split(target_split)
    new_text = parts[0] + asis_l2_md + "\n---\n\n## 3. DFD SISTEM TO-BE" + parts[1]
else:
    new_text = text

# Update Section 5 (Tab Table) with all 14 tabs
old_table_pattern = r'\| \*\*1\*\* \| `AS-IS Level 0.*?\n.*?(?=\n\nSeluruh elemen)'
new_table = """| No Tab | Nama Tab di Draw.io | Tingkatan & Sifat | Keterangan Tata Letak |
| :---: | :--- | :--- | :--- |
| **1** | `AS-IS Level 0 (Diagram Konteks)` | AS-IS Context (Lvl 0) | Sistem lama berpusat pada 1 proses sentral dan Operator. |
| **2** | `AS-IS Level 1 (Dekomposisi Sistem)` | AS-IS Overview (Lvl 1) | 4 sub-proses sekuensial sistem eksisting dan 3 data store. |
| **3** | `AS-IS Level 2 - P1.0 (Scan & Ingesti Data)` | AS-IS Child (Lvl 2) | Dekomposisi 1.1 s/d 1.5 (Folder scan, validasi, fast_import.py). |
| **4** | `AS-IS Level 2 - P2.0 (Normalisasi & Evaluasi Kualitas)` | AS-IS Child (Lvl 2) | Dekomposisi 2.1 s/d 2.6 (Mapping kolom, quality rules, DuckDB). |
| **5** | `AS-IS Level 2 - P3.0 (Pencarian & Agregasi KPI)` | AS-IS Child (Lvl 2) | Dekomposisi 3.1 s/d 3.5 (Query scan & render data polos tanpa masking). |
| **6** | `AS-IS Level 2 - P4.0 (Ekspor CSV Mentah)` | AS-IS Child (Lvl 2) | Dekomposisi 4.1 s/d 4.4 (Query & download file CSV mentah). |
| **7** | `TO-BE Level 0 (Diagram Konteks)` | TO-BE Context (Lvl 0) | Menghubungkan Operator, Sensor Kamera, dan Windows OS API. |
| **8** | `TO-BE Level 1 (Dekomposisi Sistem)` | TO-BE Overview (Lvl 1) | **Proses 1.0 s/d 6.0 tersusun lurus dari kiri ke kanan**. |
| **9** | `TO-BE Level 2 - P1.0 (Inisialisasi Biometrik)` | TO-BE Child (Lvl 2) | Dekomposisi 1.1 s/d 1.5 (Headless OpenCV, MediaPipe, Single User). |
| **10** | `TO-BE Level 2 - P2.0 (Ingesti & Audit Kualitas)` | TO-BE Child (Lvl 2) | Dekomposisi 2.1 s/d 2.6 (PyArrow Chunking, Synonym Mapping, Dual-Table). |
| **11** | `TO-BE Level 2 - P3.0 (Analitik & PII Masking)` | TO-BE Child (Lvl 2) | Dekomposisi 3.1 s/d 3.5 (DuckDB Slicing, Dynamic Masking, Micro-Table). |
| **12** | `TO-BE Level 2 - P4.0 (Guard Loop & Anti-Surfing)` | TO-BE Child (Lvl 2) | Dekomposisi 4.1 s/d 4.5 (Loop 0.2s, Iris Tracking, LockWorkStation Win+L). |
| **13** | `TO-BE Level 2 - P5.0 (Ekspor Paket ZIP)` | TO-BE Child (Lvl 2) | Dekomposisi 5.1 s/d 5.5 (CSV Serializer, Manifest Metadata, ZIP Packaging). |
| **14** | `TO-BE Level 2 - P6.0 (Terminasi & Purge)` | TO-BE Child (Lvl 2) | Dekomposisi 6.1 s/d 6.4 (Resource Teardown, 0x00 Overwrite, File Unlink). |"""

new_text = re.sub(r'\| No Tab \| Nama Tab di Draw\.io.*?\n\| \*\*10\*\* \| `TO-BE Level 2 - P6\.0.*?\n', new_table + "\n", new_text, flags=re.DOTALL)

with open(path, "w", encoding="utf-8") as f:
    f.write(new_text)

print("Successfully updated Dokumentasi_DFD_PADU.md with AS-IS Level 2 and all 14 tabs!")
