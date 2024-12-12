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
        $categories = Category::all();
        $recipes = Recipe::where('user_id', Auth::id())->with('category')->get();
        $recentRecipes = Recipe::where('user_id', Auth::id())->latest()->take(5)->get();

        return Inertia::render('Chef/Dashboard', [
            'categories' => $categories,
            'recipes' => $recipes,
            'recentRecipes' => $recentRecipes,
            'totalRecipes' => $recipes->count(),
            'totalCategories' => $categories->count(),
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

    public function createRecipe()
    {
        $categories = Category::all();

        return Inertia::render('Chef/CreateRecipe', ['categories' => $categories]);
    }

    public function storeRecipe(Request $request)
    {
        $validatedData = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            $imagePath = $request->hasFile('image') 
                ? $request->file('image')->store('recipe_images', 'public') 
                : null;

            $recipe = Recipe::create([
                ...$validatedData,
                'url_image' => $imagePath,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Recipe added successfully.',
                'recipe' => $recipe,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to add recipe: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add recipe. Please try again.'], 500);
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

    public function updateRecipe(Request $request, $id)
    {
        $validatedData = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $recipe = Recipe::where('user_id', Auth::id())->findOrFail($id);

            if ($request->hasFile('image')) {
                if ($recipe->url_image) {
                    Storage::disk('public')->delete($recipe->url_image);
                }
                $validatedData['url_image'] = $request->file('image')->store('recipe_images', 'public');
            }

            $recipe->update($validatedData);

            return response()->json(['message' => 'Recipe updated successfully.', 'recipe' => $recipe]);
        } catch (\Exception $e) {
            \Log::error('Failed to update recipe: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update recipe. Please try again.'], 500);
        }
    }

    public function deleteRecipe($id)
    {
        try {
            $recipe = Recipe::where('user_id', Auth::id())->findOrFail($id);

            if ($recipe->url_image) {
                Storage::disk('public')->delete($recipe->url_image);
            }
            $recipe->delete();

            return redirect()->route('chef.dashboard')->with('message', 'Recipe deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete recipe: ' . $e->getMessage());
            return redirect()->back()->withErrors('Failed to delete recipe. Please try again.');
        }
    }

    public function getDashboardStats()
    {
        $totalRecipes = Recipe::where('user_id', Auth::id())->count();
        $totalCategories = Category::count();
        $recentRecipes = Recipe::where('user_id', Auth::id())->latest()->take(5)->get();
        $categories = Category::all();
        $recipes = Recipe::where('user_id', Auth::id())->with('category')->get();

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
