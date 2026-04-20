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
        // 1. release_batches
        Schema::create('release_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            // draft, in_review, approved, published, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status']);
        });

        // 2. release_items
        Schema::create('release_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_version_id')->constrained();
            $table->string('change_type'); // new_feature, update, hotfix, rollback
            $table->text('change_summary')->nullable();
            $table->timestamps();
            $table->index(['release_batch_id']);
        });

        // 3. publish_validations
        Schema::create('publish_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('check_type');
            $table->string('check_key');
            $table->string('status'); // passed, failed, warning, skipped
            $table->text('message')->nullable();
            $table->timestamp('validated_at');
            $table->timestamps();
            $table->index(['feature_version_id', 'status']);
        });

        // 4. impact_analysis_reports
        Schema::create('impact_analysis_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->json('affected_branches')->nullable();
            $table->json('affected_roles')->nullable();
            $table->json('affected_documents')->nullable();
            $table->json('affected_reports')->nullable();
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->text('summary')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->index(['feature_version_id']);
        });

        // 5. rollback_logs
        Schema::create('rollback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained();
            $table->unsignedBigInteger('rolled_back_from_version');
            $table->text('reason');
            $table->unsignedBigInteger('rolled_back_by');
            $table->timestamp('rolled_back_at');
            $table->timestamps();
            $table->index(['feature_version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollback_logs');
        Schema::dropIfExists('impact_analysis_reports');
        Schema::dropIfExists('publish_validations');
        Schema::dropIfExists('release_items');
        Schema::dropIfExists('release_batches');
    }
};
