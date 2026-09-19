<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status')->default('approved');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_leaves');
    }
};
