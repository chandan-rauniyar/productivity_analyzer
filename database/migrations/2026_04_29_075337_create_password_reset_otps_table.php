<?php
// ════════════════════════════════════════════════════════════════
// database/migrations/xxxx_create_password_reset_otps_table.php
// Run: php artisan make:migration create_password_reset_otps_table
// Then replace the up() method with this content.
// ════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 6);                 // 6-digit OTP
            $table->string('token')->nullable();       // magic link token
            $table->boolean('otp_used')->default(false);
            $table->boolean('token_used')->default(false);
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0); // brute-force guard
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};