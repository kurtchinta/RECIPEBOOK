<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Display recent recipes.
     */
    public function recent()
    {
        $recentRecipes = DB::select('SELECT * FROM recent_recipes');
        return Inertia::render('Recipes/Index', [
            'recipes' => $recentRecipes,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Display a random recipe.
     */
    public function random()
    {
        $randomRecipe = DB::select('SELECT * FROM random_recipe');
        return Inertia::render('Recipes/Index', [
            'recipes' => $randomRecipe,
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
            'ingredients' => 'required|string',
            'procedures' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        Recipe::create([
            'recipe_name' => $request->recipe_name,
            'ingredients' => $request->ingredients,
            'procedures' => $request->procedures,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('recipes.index');
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
            'ingredients' => 'required|string',
            'procedures' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $recipe->update([
            'recipe_name' => $request->recipe_name,
            'ingredients' => $request->ingredients,
            'procedures' => $request->procedures,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('recipes.show', $recipe);
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('recipes.index');
    }
}
