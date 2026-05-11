<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->unsignedInteger('total_meetings')->default(0);
            $table->unsignedInteger('meeting_minutes')->default(0);
            $table->unsignedInteger('focus_time_minutes')->default(0);
            $table->unsignedInteger('back_to_back_count')->default(0);
            $table->unsignedInteger('after_hours_meetings')->default(0);
            $table->json('meeting_slots')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_metrics');
    }
};