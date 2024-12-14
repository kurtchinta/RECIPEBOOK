<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChefController extends Controller
{
    public function dashboard()
    {
        $stats = DB::select('SELECT * FROM chef_stat');

        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();

        return Inertia::render('Chef', [
            'stats' => $stats,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function display_info()
    {
        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();

        return Inertia::render('Chef', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function storeRecipe(Request $request)
    {
        $validated = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        $userId = Auth::id();

        try {
            $recipe = Recipe::create([
                'recipe_name' => $validated['recipe_name'],
                'description' => $validated['description'],
                'ingredients' => $validated['ingredients'],
                'procedure' => $validated['procedure'],
                'prep_time' => $validated['prep_time'],
                'servings' => $validated['servings'],
                'category_id' => $validated['category_id'],
                'user_id' => $userId,
            ]);
            return redirect()->route('chef.dashboard')->with('success', 'Recipe added successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to save recipe: ' . $e->getMessage());
            return redirect()->route('chef.dashboard')->with('error', 'Failed to add the recipe. Please try again.');
        }
    }
      
    public function updateRecipe(Request $request, Recipe $recipe)
    {
        if ($recipe->user_id !== Auth::id()) {
            return redirect()->route('chef.dashboard')->with('error', 'You are not authorized to edit this recipe.');
        }
    
        $validated = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
        ]);
    
        try {
            $recipe->update($validated);  // Update the existing recipe in the DB
            return redirect()->route('chef.dashboard')->with('success', 'Recipe updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to update recipe: ' . $e->getMessage());
            return redirect()->route('chef.dashboard')->with('error', 'Failed to update the recipe. Please try again.');
        }
    }


    public function editRecipe($id)
    {
        $recipe = Recipe::where('user_id', Auth::id())->findOrFail($id);
        $categories = Category::all();

        return Inertia::render('Chef/EditRecipe', [
            'recipe' => $recipe,
            'categories' => $categories,
        ]);
    }

    public function deleteRecipe(Recipe $recipe)
    {
        if ($recipe->user_id !== Auth::id()) {
            return redirect()->route('chef.dashboard')->with('error', 'Failed to delete the recipe. Please try again.');
        }

        try {
            $recipe->delete();
            return redirect()->route('chef.dashboard')->with('success', 'Recipe deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Failed to delete recipe: ' . $e->getMessage());
            return redirect()->route('chef.dashboard')->with('error', 'Failed to delete the recipe. Please try again.');
        }
    }

    public function getDashboardStats()
    {
        $userId = Auth::id();
        $totalRecipes = Recipe::where('user_id', $userId)->count();
        $totalCategories = Category::count();
        $recentRecipes = Recipe::where('user_id', $userId)->latest()->take(5)->get();
        $categories = Category::all();
        $recipes = Recipe::where('user_id', $userId)->with('category')->get();

        return response()->json([
            'totalRecipes' => $totalRecipes,
            'totalRecipesCreated' => $totalRecipes,
            'totalCategories' => $totalCategories,
            'recentRecipes' => $recentRecipes,
            'categories' => $categories,
            'recipes' => $recipes,
        ]);
    }
}

