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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->string('code', 20)->comment('Unique department code');
            $table->string('name')->comment('Department name');
            $table->text('description')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('users')->onDelete('set null')->comment('Department head');
            $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('set null')->comment('Parent department for hierarchy');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['entity_id', 'code'], 'unique_dept_code');
            $table->index('entity_id');
            $table->index('parent_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
