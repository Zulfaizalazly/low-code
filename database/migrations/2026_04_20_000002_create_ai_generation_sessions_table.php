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
        Schema::create('ai_generation_sessions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('session_key')->unique();
            $blueprint->foreignId('feature_version_id')->constrained();
            $blueprint->foreignId('user_id')->constrained();
            $blueprint->json('workflow_context');
            $blueprint->text('prompt');
            $blueprint->json('response_raw');
            $blueprint->json('generated_definition')->nullable();
            $blueprint->json('validation_results')->nullable();
            $blueprint->enum('status', ['pending', 'completed', 'failed', 'refined', 'overridden']);
            $blueprint->unsignedInteger('refinement_iteration_count')->default(0);
            $blueprint->timestamps();
        });

        Schema::create('ai_refinement_audit_trails', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('session_id')->constrained('ai_generation_sessions');
            $blueprint->text('refinement_request');
            $blueprint->json('selected_options')->nullable();
            $blueprint->json('previous_definition');
            $blueprint->json('new_definition');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_refinement_audit_trails');
        Schema::dropIfExists('ai_generation_sessions');
    }
};
