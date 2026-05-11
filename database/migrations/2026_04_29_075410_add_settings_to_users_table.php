<?php
// ════════════════════════════════════════════════════════════════
// database/migrations/xxxx_add_settings_to_users_table.php
// Run: php artisan make:migration add_settings_to_users_table
// ════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'settings')) {
                $table->json('settings')->nullable()->after('organisation');
            }
            if (! Schema::hasColumn('users', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('settings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['settings', 'last_synced_at']);
        });
    }
};