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
        $recipes = Recipe::where('user_id', Auth::id())->get();  // Add this line to fetch recipes
        $stats = DB::select('SELECT * FROM chef_stat');

        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();

        return Inertia::render('Chef', [
            'stats' => $stats,
            'users' => $users,
            'roles' => $roles,
            'recipes' => $recipes,
        ]);
    }

    public function storeRecipe(Request $request)
{
    DB::statement("SET app.current_user_id = " . auth()->id());

        // Validate the request data
        $validated = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'url_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Get authenticated user ID
        $userId = Auth::id();

        // Handle image upload
        $imagePath = $request->file('url_image')
            ? $request->file('url_image')->store('images', 'public')
            : null;

        // Log the image path to check if it's being uploaded
        \Log::info('Image path: ' . $imagePath);

        // Insert the recipe into the database
        DB::table('recipes')->insert([
            'recipe_name' => $validated['recipe_name'],
            'description' => $validated['description'],
            'ingredients' => $validated['ingredients'],
            'procedure' => $validated['procedure'],
            'prep_time' => $validated['prep_time'],
            'servings' => $validated['servings'],
            'url_image' => $imagePath,
            'category_id' => $validated['category_id'],
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return back()->with('message', 'Recipe added successfully');
        // return redirect()->route('chef')->with('success', 'Recipe added successfully!');
    // } catch (\Illuminate\Validation\ValidationException $e) {
    //     return redirect()->route('chef')
    //         ->withErrors($e->errors())
    //         ->withInput();
    // } catch (\Exception $e) {
    //     \Log::error('Failed to store recipe: ' . $e->getMessage());
    //     return redirect()->route('chef')->with('error', 'Failed to add the recipe. Please try again.');
    // }
}
    
public function updateRecipe(Request $request, $id)
{
    DB::statement("SET app.current_user_id = " . auth()->id());

    // Find the recipe by ID using DB
    $recipe = DB::table('recipes')->where('id', $id)->first();

    // Check if the recipe exists
    if (!$recipe) {
        return redirect()->route('chef')->with('error', 'Recipe not found.');
    }

    // Check if the user is authorized to update the recipe
    if ($recipe->user_id !== Auth::id()) {
        return redirect()->route('chef')->with('error', 'You are not authorized to edit this recipe.');
    }

        // Validate the request data
        $validated = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'procedure' => 'required|string',
            'prep_time' => 'required|string',
            'servings' => 'required|integer|min:1',
            'url_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Handle image upload
        $imagePath = $request->file('url_image')
            ? $request->file('url_image')->store('images', 'public')
            : $recipe->url_image; // Retain the existing image if none is uploaded

        // Log the image path for debugging
        \Log::info('Updated image path: ' . $imagePath);

        // Update the recipe in the database using DB
        DB::table('recipes')->where('id', $id)->update([
            'recipe_name' => $validated['recipe_name'],
            'description' => $validated['description'],
            'ingredients' => $validated['ingredients'],
            'procedure' => $validated['procedure'],
            'prep_time' => $validated['prep_time'],
            'servings' => $validated['servings'],
            'url_image' => $imagePath,
            'category_id' => $validated['category_id'],
            'updated_at' => now(),
        ]);

        return back()->with('message', 'Recipe updated successfully!');

    //     return redirect()->route('chef')->with('success', 'Recipe updated successfully!');
    // } catch (\Illuminate\Validation\ValidationException $e) {
    //     return redirect()->route('chef')
    //         ->withErrors($e->errors())
    //         ->withInput();
    // } catch (\Exception $e) {
    //     \Log::error('Failed to update recipe: ' . $e->getMessage());
    //     return redirect()->route('chef')->with('error', 'Failed to update the recipe. Please try again.');
    // }
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

    public function deleteRecipe($id)
    {
        DB::statement("SET app.current_user_id = " . auth()->id());

        // Find the recipe by ID using DB
        $recipe = DB::table('recipes')->where('id', $id)->first();

        // Check if the recipe exists
        if (!$recipe) {
            return redirect()->route('chef')->with('error', 'Recipe not found.');
        }

        // Check if the user is authorized to delete the recipe
        if ($recipe->user_id !== Auth::id()) {
            return redirect()->route('chef')->with('error', 'You are not authorized to delete this recipe.');
        }

            // Delete the recipe using DB
            DB::table('recipes')->where('id', $id)->delete();
            
            return redirect()->back()->with('message', 'Recipe deleted successfully.');
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

