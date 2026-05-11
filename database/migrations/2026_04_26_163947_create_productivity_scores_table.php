<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Weekly productivity scores ──────────────────────────────────────
        Schema::create('productivity_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');                          // Monday of the week
            $table->unsignedTinyInteger('score')->default(0);   // 0-100 overall
            $table->unsignedTinyInteger('email_score')->default(0);
            $table->unsignedTinyInteger('calendar_score')->default(0);
            $table->unsignedTinyInteger('balance_score')->default(0);
            $table->json('insights')->nullable();                // array of insight strings
            $table->string('best_day')->nullable();             // e.g. "Tuesday"
            $table->string('worst_day')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });

        // ── Burnout flags ───────────────────────────────────────────────────
        Schema::create('burnout_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('flagged_date');
            $table->string('reason');                           // e.g. "3+ heavy meeting days"
            $table->unsignedTinyInteger('severity');            // 1=mild, 2=moderate, 3=severe
            $table->boolean('acknowledged')->default(false);
            $table->timestamps();
        });

        // ── Add extra columns to email_metrics if not present ───────────────
        Schema::table('email_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('email_metrics', 'avg_response_hours')) {
                $table->float('avg_response_hours')->nullable()->after('after_hours_count');
            }
            if (! Schema::hasColumn('email_metrics', 'peak_hour')) {
                $table->unsignedTinyInteger('peak_hour')->nullable()->after('avg_response_hours');
            }
            if (! Schema::hasColumn('email_metrics', 'hourly_distribution')) {
                $table->json('hourly_distribution')->nullable()->after('peak_hour');
            }
            if (! Schema::hasColumn('email_metrics', 'productivity_score')) {
                $table->unsignedTinyInteger('productivity_score')->default(0)->after('hourly_distribution');
            }
        });

        // ── Add extra columns to calendar_metrics if not present ────────────
        Schema::table('calendar_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('calendar_metrics', 'back_to_back_count')) {
                $table->unsignedInteger('back_to_back_count')->default(0);
            }
            if (! Schema::hasColumn('calendar_metrics', 'after_hours_meetings')) {
                $table->unsignedInteger('after_hours_meetings')->default(0);
            }
            if (! Schema::hasColumn('calendar_metrics', 'meeting_slots')) {
                $table->json('meeting_slots')->nullable();
            }
        });

        // ── Add last_synced_at to users if not present ──────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('burnout_flags');
        Schema::dropIfExists('productivity_scores');
    }
};