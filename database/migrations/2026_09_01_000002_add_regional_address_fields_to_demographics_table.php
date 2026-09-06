<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demographics', function (Blueprint $table) {
            $table->string('provinsi')->nullable()->after('gaji_bulanan');
            $table->string('kota_kabupaten')->nullable()->after('provinsi');
            $table->string('kecamatan')->nullable()->after('kota_kabupaten');
            $table->string('kelurahan_desa')->nullable()->after('kecamatan');
            $table->string('rt_rw', 20)->nullable()->after('kelurahan_desa');
            $table->text('alamat_lengkap')->nullable()->after('rt_rw');
        });
    }

    public function down(): void
    {
        Schema::table('demographics', function (Blueprint $table) {
            $table->dropColumn([
                'provinsi',
                'kota_kabupaten',
                'kecamatan',
                'kelurahan_desa',
                'rt_rw',
                'alamat_lengkap'
            ]);
        });
    }
};
