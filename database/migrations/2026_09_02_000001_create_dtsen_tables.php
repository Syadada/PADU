<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Set Data Keluarga (Rumah Tangga - 52 Variabel DTSEN)
        Schema::create('keluargas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kartu_keluarga', 16)->unique()->index();
            $table->string('kode_provinsi', 2)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_kabupaten_kota', 4)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('kode_kecamatan', 7)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kode_kelurahan_desa', 10)->nullable();
            $table->string('kelurahan_desa', 100)->nullable();
            $table->string('alamat', 500)->nullable();
            $table->integer('jumlah_anggota_keluarga')->default(1);
            $table->string('nama_kepala_keluarga', 500)->nullable();
            
            // Variabel Desil Kesejahteraan (1 - 10)
            $table->integer('desil_nasional')->nullable()->index();
            $table->integer('desil_provinsi')->nullable();
            $table->integer('desil_kabupaten_kota')->nullable();
            
            // Bantuan Iuran
            $table->string('pbi_nas', 2)->nullable();
            $table->string('pbi_pemda', 2)->nullable();
            $table->string('id_pelanggan_pln')->nullable();
            
            // Perumahan & Sanitasi
            $table->string('status_kepemilikan_rumah', 2)->nullable();
            $table->string('jenis_lantai_terluas', 2)->nullable();
            $table->integer('luas_lantai')->nullable();
            $table->string('jenis_dinding_terluas', 2)->nullable();
            $table->string('jenis_atap_terluas', 2)->nullable();
            $table->string('sumber_air_minum_utama', 2)->nullable();
            $table->string('sumber_penerangan_utama', 2)->nullable();
            $table->string('daya_terpasang', 2)->nullable();
            $table->string('bahan_bakar_utama_memasak', 2)->nullable();
            $table->string('fasilitas_bab', 2)->nullable();
            $table->string('jenis_kloset', 2)->nullable();
            $table->string('pembuangan_akhir_tinja', 2)->nullable();
            
            // Aset Bergerak & Tidak Bergerak (1 = Ya, 2 = Tidak)
            $table->integer('kepemilikan_aset')->default(2);
            $table->integer('aset_bergerak_tabung_gas')->default(2);
            $table->integer('aset_bergerak_lemari_es')->default(2);
            $table->integer('aset_bergerak_ac')->default(2);
            $table->integer('aset_bergerak_pemanas_air')->default(2);
            $table->integer('aset_bergerak_telepon_rumah')->default(2);
            $table->integer('aset_bergerak_tv_datar')->default(2);
            $table->integer('aset_bergerak_emas_perhiasan')->default(2);
            $table->integer('aset_bergerak_komputer_laptop_tablet')->default(2);
            $table->integer('aset_bergerak_sepeda_motor')->default(2);
            $table->integer('aset_bergerak_sepeda')->default(2);
            $table->integer('aset_bergerak_mobil')->default(2);
            $table->integer('aset_bergerak_perahu')->default(2);
            $table->integer('aset_bergerak_kapal_perahu_motor')->default(2);
            $table->integer('aset_bergerak_smartphone')->default(2);
            $table->integer('aset_tidak_bergerak_lahan_lainnya')->default(2);
            $table->integer('aset_tidak_bergerak_rumah_lainnya')->default(2);
            
            // Ternak
            $table->integer('jumlah_ternak_sapi')->default(0);
            $table->integer('jumlah_ternak_kerbau')->default(0);
            $table->integer('jumlah_ternak_kuda')->default(0);
            $table->integer('jumlah_ternak_babi')->default(0);
            $table->integer('jumlah_ternak_kambing_domba')->default(0);

            $table->timestamps();
        });

        // 2. Tabel Set Data Anggota Keluarga (Individu - 48 Variabel DTSEN)
        Schema::create('individus', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk_kependudukan', 16)->unique()->index();
            $table->string('nomor_kartu_keluarga', 16)->index();
            $table->foreign('nomor_kartu_keluarga')->references('nomor_kartu_keluarga')->on('keluargas')->onDelete('cascade');
            
            $table->string('nama', 500);
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('status_hubungan_keluarga', 50)->nullable();
            $table->string('pbi_nas', 2)->nullable();
            $table->string('pbi_pemda', 2)->nullable();
            $table->string('status_kawin', 50)->nullable();
            $table->string('partisipasi_sekolah', 50)->nullable();
            $table->string('jenjang_tertinggi_yang_diduduki', 50)->nullable();
            $table->string('kelas_tertinggi_yang_diduduki', 10)->nullable();
            $table->string('ijazah_tertinggi_yang_dimiliki', 50)->nullable();
            $table->string('status_bekerja', 20)->nullable();
            $table->string('lapangan_usaha_dari_pekerjaan_utama', 100)->nullable();
            $table->string('status_dalam_pekerjaan_utama', 100)->nullable();
            $table->string('kepemilikan_usaha', 20)->nullable();
            $table->integer('jumlah_usaha')->default(0);
            $table->string('lapangan_usaha_dari_usaha_utama', 100)->nullable();
            $table->integer('jumlah_pekerja_yang_dibayar_dari_usaha_utama')->default(0);
            $table->integer('jumlah_pekerja_yang_tidak_dibayar_dari_usaha_utama')->default(0);
            $table->string('omzet_usaha_utama', 50)->nullable();
            $table->decimal('gaji_bulanan', 15, 2)->nullable();
            $table->decimal('gaji', 15, 2)->nullable();
            $table->integer('usia')->nullable();
            $table->json('extra_attributes')->nullable();
            
            // Disabilitas & Kesehatan
            $table->string('kondisi_gizi', 50)->nullable();
            $table->string('penglihatan', 50)->nullable();
            $table->string('pendengaran', 50)->nullable();
            $table->string('berjalan_atau_naik_tangga', 50)->nullable();
            $table->string('menggunakan_tangan_jari', 50)->nullable();
            $table->string('belajar_kemampuan_intelektual', 50)->nullable();
            $table->string('pengendalian_perilaku', 50)->nullable();
            $table->string('berbicara_komunikasi', 50)->nullable();
            $table->string('mengurus_diri', 50)->nullable();
            $table->string('mengingat_berkonsentrasi', 50)->nullable();
            $table->string('kesedihan_depresi', 50)->nullable();
            $table->string('penyakit_kronis', 100)->nullable();
            
            // Alamat KTP
            $table->string('kode_provinsi_ktp', 2)->nullable();
            $table->string('provinsi_ktp', 100)->nullable();
            $table->string('kode_kabupaten_kota_ktp', 4)->nullable();
            $table->string('kabupaten_kota_ktp', 100)->nullable();
            $table->string('kode_kecamatan_ktp', 7)->nullable();
            $table->string('kecamatan_ktp', 100)->nullable();
            $table->string('kode_kelurahan_desa_ktp', 10)->nullable();
            $table->string('kelurahan_desa_ktp', 100)->nullable();
            $table->string('rt_ktp', 10)->nullable();
            $table->string('rw_ktp', 10)->nullable();
            $table->string('dusun_ktp', 100)->nullable();
            $table->string('alamat_ktp', 500)->nullable();
            $table->string('pekerjaan_ktp', 100)->nullable();
            $table->string('pendidikan_akhir_ktp', 100)->nullable();

            // Status Quality Check Test (Valid / Warning / Critical)
            $table->enum('quality_status', ['Valid', 'Warning', 'Critical'])->default('Valid')->index();
            $table->json('quality_issues')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individus');
        Schema::dropIfExists('keluargas');
    }
};
