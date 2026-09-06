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
        Schema::table('individus', function (Blueprint $table) {
            $table->index('nomor_induk_kependudukan', 'idx_ind_nik');
            $table->index('nomor_kartu_keluarga', 'idx_ind_kk');
            $table->index('quality_status', 'idx_ind_quality');
            $table->index('usia', 'idx_ind_usia');
            $table->index('jenis_kelamin', 'idx_ind_jk');
            $table->index('status_bekerja', 'idx_ind_kerja');
        });

        Schema::table('keluargas', function (Blueprint $table) {
            $table->index('nomor_kartu_keluarga', 'idx_kel_kk');
            $table->index('desil_nasional', 'idx_kel_desil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('individus', function (Blueprint $table) {
            $table->dropIndex('idx_ind_nik');
            $table->dropIndex('idx_ind_kk');
            $table->dropIndex('idx_ind_quality');
            $table->dropIndex('idx_ind_usia');
            $table->dropIndex('idx_ind_jk');
            $table->dropIndex('idx_ind_kerja');
        });

        Schema::table('keluargas', function (Blueprint $table) {
            $table->dropIndex('idx_kel_kk');
            $table->dropIndex('idx_kel_desil');
        });
    }
};
