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
        Schema::create('email_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->unsignedInteger('received_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('after_hours_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'metric_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_metrics');
    }
};
