<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID column
            $table->integer('user_id'); // ID of the user who performed the action
            $table->string('table_name'); // Name of the table affected
            $table->string('action', 10); // Action performed (e.g., 'Insert', 'Update', 'Delete')
            $table->string('affected_name'); // Name of the user/recipe affected
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // Timestamp of the action
            $table->string('role_user', 20)->default(''); // Role of the user performing the action
            $table->timestamp('updated_at')->nullable(); // Nullable for updates
            $table->timestamp('deleted_at')->nullable(); // Nullable for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};