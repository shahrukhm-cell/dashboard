<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();

            $table->primary(['team_id', 'user_id']);
        });

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};