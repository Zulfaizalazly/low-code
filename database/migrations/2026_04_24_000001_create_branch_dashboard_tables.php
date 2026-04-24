<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Branch Dashboard Visibility tables:
     * - feature_access_logs: Tracks every feature access by branch staff
     * - feature_health_checks: Records feature availability/health status
     * - support_tickets: IT support request tracking for branch managers
     * - change_deployments: Tracks IT deployments for visibility tracker
     */
    public function up(): void
    {
        // 1. feature_access_logs — tracks every feature access by staff
        Schema::create('feature_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('feature_version_id')->nullable();
            $table->unsignedBigInteger('page_definition_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('access_type')->default('view'); // view, execute, submit
            $table->unsignedInteger('session_duration_seconds')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'accessed_at']);
            $table->index(['feature_id', 'accessed_at']);
            $table->index(['branch_id', 'accessed_at']);
            $table->index(['access_type']);
        });

        // 2. feature_health_checks — records feature availability status
        Schema::create('feature_health_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->string('status')->default('available'); // available, degraded, unavailable
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamps();

            $table->index(['feature_id', 'status']);
            $table->index(['checked_at']);
        });

        // 3. support_tickets — IT support request tracking
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('issue'); // bug, feature_request, issue
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->json('context_json')->nullable();
            $table->unsignedBigInteger('it_responder_id')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'priority']);
        });

        // 4. change_deployments — tracks IT deployments for visibility tracker
        Schema::create('change_deployments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('feature_version_id');
            $table->unsignedBigInteger('deployed_by')->nullable();
            $table->timestamp('deployed_at');
            $table->text('change_summary')->nullable();
            $table->boolean('is_visible_to_branches')->default(true);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['feature_id', 'deployed_at']);
            $table->index(['deployed_at']);
            $table->index(['is_visible_to_branches']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_deployments');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('feature_health_checks');
        Schema::dropIfExists('feature_access_logs');
    }
};
