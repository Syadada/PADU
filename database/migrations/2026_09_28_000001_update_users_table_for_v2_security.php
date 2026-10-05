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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('operator')->after('email');
            $table->boolean('first_login')->default(true)->after('role');
            $table->integer('failed_login_attempts')->default(0)->after('first_login');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('password_changed_at')->nullable()->after('locked_until');
            $table->text('two_factor_secret')->nullable()->after('password_changed_at');
            $table->text('two_factor_recovery_hash')->nullable()->after('two_factor_secret');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'first_login',
                'failed_login_attempts',
                'locked_until',
                'password_changed_at',
                'two_factor_secret',
                'two_factor_recovery_hash',
                'two_factor_enabled',
            ]);
        });
    }
};
