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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();  // Auto-incrementing ID column
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');  // Foreign key referencing the 'recipes' table
            $table->string('ingredient');  // Ingredient name
            $table->string('quantity');  // Quantity of the ingredient (e.g., "2 cups", "1 tsp")
            $table->timestamps();  // 'created_at' and 'updated_at' columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
