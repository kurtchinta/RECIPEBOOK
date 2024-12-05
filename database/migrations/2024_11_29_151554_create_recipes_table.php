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
            $table->string('recipe_name');  // Title of the recipe
            $table->text('url_image');
            $table->text('prep_time');
            $table->text('description');  
            $table->text('ingredients'); 
            $table->text('procedures');  
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
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
