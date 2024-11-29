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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();  // Auto-incrementing ID column
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  // Foreign key referencing the 'users' table
            $table->string('action');  // Action performed (e.g., 'Created Recipe', 'Updated Category')
            $table->string('affected_table');  // Table that was affected (e.g., 'recipes', 'categories')
            $table->bigInteger('affected_record_id');  // The ID of the affected record
            $table->timestamp('timestamp')->useCurrent();  // Timestamp when the action occurred
            $table->timestamps();  // 'created_at' and 'updated_at' columns
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
