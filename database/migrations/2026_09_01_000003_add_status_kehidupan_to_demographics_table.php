<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demographics', function (Blueprint $table) {
            $table->string('status_kehidupan', 30)->default('Masih Hidup')->after('status_pernikahan');
        });
    }

    public function down(): void
    {
        Schema::table('demographics', function (Blueprint $table) {
            $table->dropColumn('status_kehidupan');
        });
    }
};
