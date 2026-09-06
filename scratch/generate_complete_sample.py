import pandas as pd
import numpy as np
import os
import random
from datetime import datetime, timedelta

print("--- GENERATING 500-ROW COMPLETE DTSEN SAMPLE DATASET ---")

random.seed(42)
np.random.seed(42)

total_rows = 500

first_names = ["Budi", "Siti", "Teuku", "Dewi", "Wawan", "Anisa", "Rahmat", "Rina", "Hendra", "Dedi", "Cut", "Bambang", "Yuni", "Irfan", "Ahmad", "Nur", "Agus", "Tri", "Eko", "Sri"]
last_names = ["Santoso", "Rahmawati", "Umar", "Pratama", "Nyak Dien", "Sartika", "Wibowo", "Kusuma", "Setiawan", "Nurbaya", "Putri", "Hidayat", "Wijaya", "Kurniawan", "Suryani", "Lestari"]

prov_cities = [
    ("DKI Jakarta", "Jakarta Selatan", "Cilandak", "Cilandak Barat", "Jl. Mawar No."),
    ("Jawa Barat", "Kota Bogor", "Bogor Tengah", "Paledang", "Jl. Pajajaran No."),
    ("Jawa Barat", "Kota Bandung", "Coblong", "Dago", "Jl. Ganesha No."),
    ("Jawa Tengah", "Kota Semarang", "Semarang Selatan", "Randusari", "Jl. Pandanaran No."),
    ("Jawa Timur", "Kota Surabaya", "Tegalsari", "Kedungdoro", "Jl. Basuki Rahmat No."),
    ("Banten", "Kota Tangerang", "Tangerang", "Sukarasa", "Jl. Merdeka No."),
    ("Sumatera Utara", "Kota Medan", "Medan Baru", "Petisah Tengah", "Jl. Gajah Mada No."),
    ("Bali", "Kota Denpasar", "Denpasar Selatan", "Sidakarya", "Jl. Raya Puputan No.")
]

rows = []
for i in range(1, total_rows + 1):
    nik = f"320101{random.randint(10, 28):02d}{random.randint(70, 99):02d}{i:06d}"
    family_idx = (i - 1) // 3 + 1
    kk = f"320101201018{family_idx:04d}"
    
    fname = random.choice(first_names)
    lname = random.choice(last_names)
    nama = f"{fname} {lname}"
    
    birth_year = random.randint(1960, 2005)
    birth_month = random.randint(1, 12)
    birth_day = random.randint(1, 28)
    tgl_lahir = f"{birth_year}-{birth_month:02d}-{birth_day:02d}"
    usia = 2026 - birth_year
    
    jk = "Laki-laki" if fname in ["Budi", "Teuku", "Wawan", "Rahmat", "Hendra", "Dedi", "Bambang", "Irfan", "Ahmad", "Agus", "Tri", "Eko"] else "Perempuan"
    
    rel = "Kepala Keluarga" if (i - 1) % 3 == 0 else ("Istri" if (i - 1) % 3 == 1 else "Anak")
    st_kawin = "Kawin/Nikah" if usia >= 25 and rel in ["Kepala Keluarga", "Istri"] else "Belum Kawin"
    
    gaji = float(random.choice([
        random.randint(2200000, 2900000),
        random.randint(3100000, 4800000),
        random.randint(5200000, 9800000),
        random.randint(10500000, 18500000)
    ]))
    
    desil = random.randint(1, 10)
    
    prov, kab, kec, kel, almt_base = prov_cities[family_idx % len(prov_cities)]
    alamat = f"{almt_base} {i}"
    
    st_bantuan = random.choice(["PBI APBN (Kemenkes)", "PBI APBD (Pemda)", "PKH & BPNT", "PBI APBN + PKH", "Bukan Penerima Bantuan"])
    pbi_nas = "Ya" if "APBN" in st_bantuan else "Tidak"
    pbi_pem = "Ya" if "APBD" in st_bantuan else "Tidak"
    
    part_sekolah = "Tidak Sekolah Lagi" if usia >= 22 else ("Masih Sekolah" if usia >= 6 else "Belum Sekolah")
    if usia >= 24:
        ijazah = random.choice(["SMA/SMK/MA", "Diploma (D1-D4)", "Sarjana (S1)", "Magister (S2)"])
    elif usia >= 18:
        ijazah = random.choice(["SMP/MTs", "SMA/SMK/MA"])
    else:
        ijazah = "SD/MI"
        
    jenjang = ijazah
    st_kerja = "Ya" if usia >= 18 and rel != "Anak" else ("Ya" if random.random() > 0.4 else "Tidak")
    
    lap_usaha = random.choice(["Perdagangan besar dan eceran", "Pertanian, kehutanan, dan perikanan", "Jasa keuangan & asuransi", "Industri pengolahan", "Konstruksi", "Administrasi pemerintahan"])
    st_pekerjaan = random.choice(["Buruh/Karyawan/Pegawai", "Berusaha sendiri", "Pekerja bebas"])
    
    pln_id = f"5321{family_idx:08d}"
    daya_pln = random.choice(["450 VA", "900 VA", "1300 VA", "2200 VA"])
    st_rumah = random.choice(["Milik Sendiri", "Kontrak/Sewa", "Bebas Sewa"])
    lantai = random.choice(["Keramik", "Ubin/Teraso", "Semen"])
    dinding = random.choice(["Tembok", "Kayu"])
    atap = random.choice(["Genteng", "Seng", "Asbes"])
    air_minum = random.choice(["Air Kemasan/Isi Ulang", "Layanan Perpipaan (PDAM)", "Sumur Bor/Pompa"])
    penerangan = "Listrik PLN"
    bb_masak = random.choice(["LPG 3 kg", "LPG 5.5 kg / 12 kg", "Listrik"])
    fas_bab = "Sendiri"
    kloset = "Leher Angsa"
    tinja = "Tangki Septik"
    
    aset_motor = "Ya" if random.random() > 0.3 else "Tidak"
    aset_mobil = "Ya" if gaji > 8000000 else "Tidak"
    aset_tv = "Ya"
    aset_hp = "Ya"
    aset_kulkas = "Ya" if random.random() > 0.2 else "Tidak"

    row = {
        'nomor_induk_kependudukan': nik,
        'nomor_kartu_keluarga': kk,
        'nama': nama,
        'tanggal_lahir': tgl_lahir,
        'usia': usia,
        'jenis_kelamin': jk,
        'status_hubungan_keluarga': rel,
        'status_kawin': st_kawin,
        'gaji_bulanan': gaji,
        'gaji': gaji,
        'desil_nasional': desil,
        'desil': desil,
        'provinsi': prov,
        'kabupaten_kota': kab,
        'kecamatan': kec,
        'kelurahan_desa': kel,
        'alamat': alamat,
        'status_bantuan': st_bantuan,
        'pbi_nasional': pbi_nas,
        'pbi_pemda': pbi_pem,
        'partisipasi_sekolah': part_sekolah,
        'jenjang_tertinggi_yang_diduduki': jenjang,
        'ijazah_tertinggi_yang_dimiliki': ijazah,
        'status_bekerja': st_kerja,
        'lapangan_usaha_dari_pekerjaan_utama': lap_usaha,
        'status_dalam_pekerjaan_utama': st_pekerjaan,
        'id_pelanggan_pln': pln_id,
        'daya_terpasang': daya_pln,
        'status_kepemilikan_rumah': st_rumah,
        'jenis_lantai_terluas': lantai,
        'jenis_dinding_terluas': dinding,
        'jenis_atap_terluas': atap,
        'sumber_air_minum_utama': air_minum,
        'sumber_penerangan_utama': penerangan,
        'bahan_bakar_utama_memasak': bb_masak,
        'fasilitas_bab': fas_bab,
        'jenis_kloset': kloset,
        'pembuangan_akhir_tinja': tinja,
        'kepemilikan_aset': "Ya",
        'aset_bergerak_sepeda_motor': aset_motor,
        'aset_bergerak_mobil': aset_mobil,
        'aset_bergerak_tv_datar': aset_tv,
        'aset_bergerak_smartphone': aset_hp,
        'aset_bergerak_lemari_es': aset_kulkas,
        'kondisi_gizi': "Normal",
        'penglihatan': "Tidak Ada Gangguan",
        'pendengaran': "Tidak Ada Gangguan",
        'berjalan_atau_naik_tangga': "Tidak Ada Gangguan"
    }
    rows.append(row)

df = pd.DataFrame(rows)

src_dtsen = "src-dtsen"
os.makedirs(src_dtsen, exist_ok=True)

csv_path = os.path.join(src_dtsen, "dataset_dtsen_500_lengkap.csv")
xlsx_path = os.path.join(src_dtsen, "dataset_dtsen_500_lengkap.xlsx")

df.to_csv(csv_path, index=False)
df.to_excel(xlsx_path, index=False)

print(f"[SUCCESS] CSV created: {csv_path} ({len(df)} rows, {len(df.columns)} columns)")
print(f"[SUCCESS] XLSX created: {xlsx_path} ({len(df)} rows, {len(df.columns)} columns)")
