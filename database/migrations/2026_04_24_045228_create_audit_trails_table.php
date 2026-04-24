<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            
            // Polymorphic relationship columns (needed by domain models)
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            
            $table->string('action'); // e.g. STAFF_VIEW_TOGGLE, FEATURE_EXECUTION_AS_STAFF, created, updated, deleted
            $table->text('description')->nullable(); // Make nullable for domain audit trails
            $table->json('payload')->nullable();
            
            // Legacy columns for backward compatibility
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('performed_at')->nullable();
            
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['action', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
