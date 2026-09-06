<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Demographic;
use Carbon\Carbon;

class DemographicSeeder extends Seeder
{
    public function run(): void
    {
        $depanL = ['Budi', 'Ahmad', 'Rizky', 'Dedi', 'Eko', 'Fajar', 'Hendra', 'Indra', 'Joko', 'Muhammad', 'Reza', 'Surya', 'Taufik', 'Wahyu', 'Yudi', 'Agus', 'Bayu', 'Bambang', 'Heri', 'Rudi'];
        $depanP = ['Siti', 'Dewi', 'Anisa', 'Lestari', 'Maya', 'Nia', 'Putri', 'Rina', 'Sri', 'Titi', 'Wulan', 'Yulia', 'Dian', 'Ratna', 'Fitri', 'Nurul', 'Eka', 'Kartika', 'Melati', 'Indah'];
        $belakang = ['Santoso', 'Pratama', 'Hidayat', 'Kusuma', 'Saputra', 'Wibowo', 'Nugroho', 'Setiawan', 'Utomo', 'Firmansyah', 'Suryani', 'Rahayu', 'Handayani', 'Puspita', 'Wahyuni', 'Lestari', 'Kurniawan', 'Hadi', 'Gunawan', 'Subagyo'];

        $pendidikanList = ['SD', 'SMP', 'SMA/K', 'D3', 'S1', 'S2', 'S3'];
        $statusPernikahanList = ['Menikah', 'Belum Menikah', 'Cerai'];

        $wilayahData = [
            [
                'provinsi' => 'DKI Jakarta',
                'kota' => 'Jakarta Selatan',
                'kecamatan' => ['Kebayoran Baru', 'Cilandak', 'Tebet', 'Pancoran', 'Pasar Minggu'],
                'kelurahan' => ['Senayan', 'Gandaria Selatan', 'Menteng Dalam', 'Kalibata', 'Pejaten Barat'],
            ],
            [
                'provinsi' => 'Jawa Barat',
                'kota' => 'Kota Bandung',
                'kecamatan' => ['Coblong', 'Sumur Bandung', 'Cicendo', 'Lengkong', 'Bandung Wetan'],
                'kelurahan' => ['Dago', 'Braga', 'Pasirkaliki', 'Malabar', 'Cihapit'],
            ],
            [
                'provinsi' => 'Jawa Timur',
                'kota' => 'Kota Surabaya',
                'kecamatan' => ['Gubeng', 'Tegalsari', 'Wonokromo', 'Sukolilo', 'Genteng'],
                'kelurahan' => ['Airlangga', 'Dr. Soetomo', 'Darmo', 'Keputih', 'Embong Kaliasin'],
            ],
            [
                'provinsi' => 'Sumatera Utara',
                'kota' => 'Kota Medan',
                'kecamatan' => ['Medan Kota', 'Medan Barat', 'Medan Baru', 'Medan Helvetia'],
                'kelurahan' => ['Passar Baru', 'Kesawan', 'Padang Bulan', 'Helvetia Tengah'],
            ],
            [
                'provinsi' => 'Sulawesi Selatan',
                'kota' => 'Kota Makassar',
                'kecamatan' => ['Ujung Pandang', 'Panakkukang', 'Tamalate', 'Rappocini'],
                'kelurahan' => ['Losari', 'Masale', 'Tanjung Merdeka', 'Buakana'],
            ],
            [
                'provinsi' => 'D.I. Yogyakarta',
                'kota' => 'Kota Yogyakarta',
                'kecamatan' => ['Gondomanan', 'Danurejan', 'Umbulharjo', 'Mantrijeron'],
                'kelurahan' => ['Prawirodirjan', 'Suryatmajan', 'Giwangan', 'Suryodiningratan'],
            ],
        ];

        $jalanList = ['Jl. Jenderal Sudirman', 'Jl. Gajah Mada', 'Jl. Ahmad Yani', 'Jl. Diponegoro', 'Jl. Merdeka', 'Jl. Pahlawan', 'Jl. Gatot Subroto', 'Jl. Hayam Wuruk'];

        for ($i = 1; $i <= 120; $i++) {
            $isMale = (rand(0, 100) > 48);
            $jk = $isMale ? 'Laki-laki' : 'Perempuan';
            
            $namaDepan = $isMale ? $depanL[array_rand($depanL)] : $depanP[array_rand($depanP)];
            $namaBelakang = $belakang[array_rand($belakang)];
            $namaLengkap = $namaDepan . ' ' . $namaBelakang;

            $provKab = '32' . str_pad(rand(1, 75), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 30), 2, '0', STR_PAD_LEFT);
            $tglRand = Carbon::now()->subYears(rand(18, 65))->subMonths(rand(0, 11))->subDays(rand(0, 28));
            $tglNik = $tglRand->format('d');
            if (!$isMale) {
                $tglNik = (int)$tglNik + 40;
            }
            $nikDateStr = str_pad($tglNik, 2, '0', STR_PAD_LEFT) . $tglRand->format('my');
            $nikUrut = str_pad($i, 4, '0', STR_PAD_LEFT);
            $nik = $provKab . $nikDateStr . $nikUrut;

            $pendidikan = $pendidikanList[array_rand($pendidikanList)];

            $baseSalary = match($pendidikan) {
                'SD' => rand(3000000, 4500000),
                'SMP' => rand(3500000, 5500000),
                'SMA/K' => rand(4500000, 7500000),
                'D3' => rand(5500000, 9500000),
                'S1' => rand(7000000, 16000000),
                'S2' => rand(12000000, 25000000),
                'S3' => rand(18000000, 40000000),
            };

            $wil = $wilayahData[array_rand($wilayahData)];
            $kec = $wil['kecamatan'][array_rand($wil['kecamatan'])];
            $kel = $wil['kelurahan'][array_rand($wil['kelurahan'])];
            $rtRw = 'RT ' . str_pad(rand(1, 15), 3, '0', STR_PAD_LEFT) . ' / RW ' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT);
            $alamat = $jalanList[array_rand($jalanList)] . ' No. ' . rand(1, 150);

            $cleanName = strtolower(str_replace(' ', '.', $namaLengkap));
            $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'perusahaan.co.id'];
            $email = $cleanName . rand(10, 99) . '@' . $domains[array_rand($domains)];

            // 90% Masih Hidup, 10% Meninggal Dunia
            $statusKehidupan = (rand(1, 100) <= 90) ? 'Masih Hidup' : 'Meninggal Dunia';

            Demographic::create([
                'nik' => $nik,
                'nama_lengkap' => $namaLengkap,
                'jenis_kelamin' => $jk,
                'pendidikan_terakhir' => $pendidikan,
                'tanggal_lahir' => $tglRand->format('Y-m-d'),
                'gaji_bulanan' => $baseSalary,
                'provinsi' => $wil['provinsi'],
                'kota_kabupaten' => $wil['kota'],
                'kota_domisili' => $wil['kota'],
                'kecamatan' => $kec,
                'kelurahan_desa' => $kel,
                'rt_rw' => $rtRw,
                'alamat_lengkap' => $alamat,
                'email' => $email,
                'status_pernikahan' => $statusPernikahanList[array_rand($statusPernikahanList)],
                'status_kehidupan' => $statusKehidupan,
            ]);
        }
    }
}
