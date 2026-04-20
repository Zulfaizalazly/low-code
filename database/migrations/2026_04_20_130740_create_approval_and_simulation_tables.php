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
        // 1. approval_workflows
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->enum('decision', ['approved', 'rejected'])->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['feature_version_id', 'decision']);
        });

        // 2. simulation_logs
        Schema::create('simulation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->json('test_data');
            $table->json('results');
            $table->enum('status', ['success', 'failed']);
            $table->foreignId('executed_by')->constrained('users');
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['feature_version_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulation_logs');
        Schema::dropIfExists('approval_workflows');
    }
};
