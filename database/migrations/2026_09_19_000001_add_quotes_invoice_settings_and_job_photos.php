<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->string('quote_status')->default('draft')->after('status');
            $table->timestamp('quote_sent_at')->nullable()->after('quote_status');
            $table->timestamp('quote_approved_at')->nullable()->after('quote_sent_at');
            $table->index(['tenant_id', 'quote_status']);
        });

        Schema::create('job_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'service_job_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_photos');

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'quote_status']);
            $table->dropColumn(['quote_status', 'quote_sent_at', 'quote_approved_at']);
        });
    }
};
