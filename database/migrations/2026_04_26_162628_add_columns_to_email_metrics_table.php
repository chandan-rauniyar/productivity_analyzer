<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_metrics', function (Blueprint $table) {
            $table->float('avg_response_hours')->nullable()->after('after_hours_count');
            $table->unsignedTinyInteger('peak_hour')->nullable()->after('avg_response_hours');
            $table->json('hourly_distribution')->nullable()->after('peak_hour');
            $table->unsignedInteger('productivity_score')->default(0)->after('hourly_distribution');
        });
    }

    public function down(): void
    {
        Schema::table('email_metrics', function (Blueprint $table) {
            $table->dropColumn(['avg_response_hours', 'peak_hour', 'hourly_distribution', 'productivity_score']);
        });
    }
};