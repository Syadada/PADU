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
        Schema::create('demographics', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->enum('pendidikan_terakhir', ['SD', 'SMP', 'SMA/K', 'D3', 'S1', 'S2', 'S3']);
            $table->date('tanggal_lahir');
            $table->decimal('gaji_bulanan', 12, 2);
            $table->string('kota_domisili');
            $table->string('email');
            $table->enum('status_pernikahan', ['Menikah', 'Belum Menikah', 'Cerai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demographics');
    }
};
