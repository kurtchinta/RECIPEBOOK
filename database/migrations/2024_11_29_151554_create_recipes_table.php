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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();  // Auto-incrementing ID column
            $table->string('title');  // Title of the recipe
            $table->text('ingredients');  // Ingredients of the recipe
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');  // Foreign key referencing the 'categories' table
            $table->string('image_url')->nullable();  // URL or path to the recipe image (nullable)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');  // Foreign key referencing the 'users' table (chef who created the recipe)
            $table->timestamps();  // 'created_at' and 'updated_at' columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
