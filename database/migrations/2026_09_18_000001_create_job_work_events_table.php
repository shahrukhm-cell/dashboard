<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_work_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'service_job_id', 'occurred_at']);
            $table->index(['service_job_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_work_events');
    }
};
