<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table) {
            // bcrypt hash = 60 chars, sha256 hash = 64 chars
            $table->string('otp', 255)->change();
            $table->string('token', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->string('otp', 6)->change();
            $table->string('token', 64)->nullable()->change();
        });
    }
};