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
        Schema::create('scoped_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_version_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type');       // branch, product, entity, region
            $table->string('scope_id');         // e.g., branch_id or product_code
            $table->string('target_table');     // which registry table
            $table->string('target_key');       // which record key
            $table->json('override_value');     // the override
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->index(['feature_version_id', 'scope_type']);
            $table->index(['scope_type', 'scope_id']);
            $table->index(['effective_from', 'effective_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoped_overrides');
    }
};
