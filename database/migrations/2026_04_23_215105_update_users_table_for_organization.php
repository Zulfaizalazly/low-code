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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns after specific positions
            $table->string('employee_number', 50)->unique()->nullable()->after('id');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->date('joined_at')->nullable()->after('is_active');
            $table->date('left_at')->nullable()->after('joined_at');
            
            // Add indexes
            $table->index('employee_number');
            $table->index('entity_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['employee_number']);
            $table->dropIndex(['entity_id']);
            $table->dropIndex(['is_active']);
            
            $table->dropColumn([
                'employee_number',
                'phone',
                'avatar',
                'is_active',
                'joined_at',
                'left_at'
            ]);
        });
    }
};
