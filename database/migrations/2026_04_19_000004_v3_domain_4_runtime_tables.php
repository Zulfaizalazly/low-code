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
        // 1. event_logs
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');           // e.g., 'facility.created'
            $table->json('event_payload');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('emitted_at');
            $table->timestamps();
            $table->index(['event_type']);
            $table->index(['source_type', 'source_id']);
            $table->index(['emitted_at']);
        });

        // 2. automation_execution_logs
        Schema::create('automation_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_definition_id')->constrained();
            $table->unsignedBigInteger('feature_version_id');
            $table->string('trigger_type');
            $table->string('trigger_source')->nullable();
            $table->string('status')->default('running');
            // running, completed, failed, timed_out, cancelled
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['flow_definition_id', 'status']);
            $table->index(['feature_version_id']);
            $table->index(['started_at']);
        });

        // 3. automation_node_logs
        Schema::create('automation_node_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_log_id')
                  ->constrained('automation_execution_logs')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('flow_node_id');
            $table->string('node_key');
            $table->string('node_type');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->string('status'); // pending, running, completed, failed, skipped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['execution_log_id']);
            $table->index(['flow_node_id']);
            $table->index(['status']);
        });

        // 4. command_logs
        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command_type');         // e.g., 'CreateFacilityApplication'
            $table->json('command_payload');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status');               // accepted, processed, failed, rejected
            $table->timestamp('executed_at');
            $table->timestamps();
            $table->index(['command_type', 'status']);
            $table->index(['user_id']);
            $table->index(['executed_at']);
        });

        // 5. ui_submission_logs
        Schema::create('ui_submission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_definition_id');
            $table->integer('page_version');
            $table->json('form_data');
            $table->unsignedBigInteger('submitted_by');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index(['page_definition_id']);
            $table->index(['submitted_by']);
            $table->index(['submitted_at']);
        });

        // 6. audit_trails - REMOVED: Now created in 2026_04_24_045228_create_audit_trails_table.php
        // This prevents duplicate table creation conflicts

        // 7. dead_letter_jobs
        Schema::create('dead_letter_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at');
            $table->timestamps();
            $table->index(['queue']);
            $table->index(['failed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dead_letter_jobs');
        // audit_trails now dropped in 2026_04_24_045228_create_audit_trails_table.php
        Schema::dropIfExists('ui_submission_logs');
        Schema::dropIfExists('command_logs');
        Schema::dropIfExists('automation_node_logs');
        Schema::dropIfExists('automation_execution_logs');
        Schema::dropIfExists('event_logs');
    }
};
