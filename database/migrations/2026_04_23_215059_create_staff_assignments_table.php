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
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->string('position')->comment('Job title');
            $table->enum('employment_type', ['permanent', 'contract', 'temporary'])->default('permanent');
            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->boolean('is_primary')->default(false)->comment('Primary assignment');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('entity_id');
            $table->index('branch_id');
            $table->index('department_id');
            $table->index('is_primary');
            $table->index(['started_at', 'ended_at']);
        });
        
        // Note: Check constraint validation will be handled at application level
        // SQLite doesn't support ALTER TABLE ADD CONSTRAINT
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
};
