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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->string('code', 20)->comment('Unique branch code');
            $table->string('name')->comment('Branch name');
            $table->enum('type', ['hq', 'branch', 'mini_branch'])->default('branch');
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null')->comment('Branch manager');
            $table->json('opening_hours')->nullable()->comment('Operating hours');
            $table->boolean('is_active')->default(true);
            $table->date('opened_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->json('settings')->nullable()->comment('Branch-specific configurations');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['entity_id', 'code'], 'unique_branch_code');
            $table->index('entity_id');
            $table->index('region_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
