<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RecipeController extends Controller
{
    /**
     * Display a listing of the recipes.
     */
    public function index(Request $request)
    {
        $query = Recipe::with(['category']);

        if ($request->has('search')) {
            $query->where('recipe_name', 'ilike', '%' . $request->search . '%');
        }

        $recipes = $query->latest()->get();

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create()
    {
        $categories = Category::all();
        return Inertia::render('Recipes/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedures' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'preptime' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation for image
        ]);

        // Handle the image upload if exists
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('recipe_images', 'public');
        }

        // Create the recipe record in the database
        $recipe = Recipe::create([
            'recipe_name' => $request->recipe_name,
            'description' => $request->description,
            'ingredients' => $request->ingredients,
            'procedures' => $request->procedures,
            'category_id' => $request->category_id,
            'preptime' => $request->preptime,
            'image' => $imagePath, // Store the image path in the database
        ]);

        return response()->json(['message' => 'Recipe added successfully', 'recipe' => $recipe], 201);
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe)
    {
        $recipe->load('category');

        return Inertia::render('Recipes/Show', ['recipe' => $recipe]);
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe)
    {
        $categories = Category::all();

        return Inertia::render('Recipes/Edit', [
            'recipe' => $recipe,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified recipe in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedures' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'preptime' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle the image upload if exists
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($recipe->image && Storage::exists('public/' . $recipe->image)) {
                Storage::delete('public/' . $recipe->image);
            }
            // Store the new image
            $imagePath = $request->file('image')->store('recipe_images', 'public');
            $recipe->image = $imagePath;
        }

        // Update the recipe record
        $recipe->update([
            'recipe_name' => $request->recipe_name,
            'description' => $request->description,
            'ingredients' => $request->ingredients,
            'procedures' => $request->procedures,
            'category_id' => $request->category_id,
            'preptime' => $request->preptime,
            // 'image' is handled separately above
        ]);

        return response()->json(['message' => 'Recipe updated successfully', 'recipe' => $recipe], 200);
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        // Delete the recipe's image from storage if it exists
        if ($recipe->image && Storage::exists('public/' . $recipe->image)) {
            Storage::delete('public/' . $recipe->image);
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully'], 200);
    }
}