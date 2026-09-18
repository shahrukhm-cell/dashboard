<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('job_number');
            $table->string('status')->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('service_address')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'job_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
        });

        Schema::create('service_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('unit_type')->default('job');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_job_items');
        Schema::dropIfExists('service_jobs');
    }
};