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
        Schema::create('ai_usage_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained();
            $blueprint->unsignedBigInteger('organization_id')->nullable();
            $blueprint->string('feature_type');
            $blueprint->string('provider');
            $blueprint->string('model_used');
            $blueprint->unsignedInteger('tokens_input');
            $blueprint->unsignedInteger('tokens_output');
            $blueprint->decimal('cost_usd', 10, 6);
            $blueprint->timestamp('used_at');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
