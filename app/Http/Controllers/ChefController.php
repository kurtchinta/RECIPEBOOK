<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChefController extends Controller
{
    /**
     * Display the chef dashboard with category and recipe statistics.
     */
    public function dashboard()
    {
        $statistics = DB::select('SELECT COUNT(*) AS total_categories, 
                                         (SELECT COUNT(*) FROM recipes WHERE category_id IN (SELECT id FROM categories WHERE user_id = ?)) AS total_recipes
                                  FROM categories WHERE user_id = ?', [Auth::id(), Auth::id()])[0];
        return Inertia::render('Chef/Dashboard', ['statistics' => $statistics]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function createCategory()
    {
        return Inertia::render('Chef/CreateCategory');
    }

    /**
     * Store a newly created category in storage.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        Category::create([
            'category_name' => $request->category_name,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('chef.dashboard')->with('message', 'Category created successfully');
    }

    /**
     * Show the form for adding a new recipe.
     */
    public function createRecipe()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        return Inertia::render('Chef/CreateRecipe', ['categories' => $categories]);
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function storeRecipe(Request $request)
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

        return redirect()->route('chef.dashboard')->with('message', 'Recipe added successfully');
    }
}
