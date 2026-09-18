<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_job_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamp('changed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'service_job_id', 'changed_at'], 'job_status_tenant_job_changed_idx');
            $table->index(['service_job_id', 'new_status'], 'job_status_job_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_job_status_events');
    }
};

