<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('minutes');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'started_at']);
            $table->index(['service_job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};