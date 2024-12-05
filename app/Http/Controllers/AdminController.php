<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\roles; // Ensure Role model is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display the dashboard with recipe statistics.
     */
    public function dashboard()
    {
        $statistics = DB::select('SELECT * FROM recipe_statistics')[0];
        return Inertia::render('Admin', ['statistics' => $statistics]);
    }

    /**
     * Refresh the materialized view for recipe statistics.
     */
    public function refreshStatistics()
    {
        DB::select('REFRESH MATERIALIZED VIEW recipe_statistics');
        return redirect()->back()->with('message', 'Statistics refreshed successfully');
    }

    public function display_info()
    {
        $users = User::with('role')->get();

        dd($users);
        $roles = DB::table('roles')->get();

        return Inertia::render('Admin', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Render the user management interface.
     */
//     public function index()
// {
//     // Fetch all users with their roles, specifically filtering by 'admin' role (role_id = 1)
//     $admins = User::where('role_id', 1)->get();  // Adjust role_id based on your DB

//     // // Fetch all roles from the database
//     // $roles = Role::all();

//     dd($admins);

//     // Return the list of admins and roles to the Vue component using Inertia
//     return Inertia::render('Admin', [
//         'admins' => $admins, // Ensure admins is passed to Vue component
//         'roles' => $roles     // Pass roles data as well if needed for rendering
//     ]);
// }

    

    /**
     * Update a user's details and role.
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update($validated);

        return redirect()->back()->with('message', 'User updated successfully');
    }

    /**
     * Delete a user from the system.
     */
    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect()->back()->with('message', 'User deleted successfully');
    }

    /**
     * Get the count of recipes created by a specific user.
     */
    public function getUserRecipeCount(Request $request)
    {
        $count = DB::select('SELECT get_user_recipe_count(?) as count', [$request->user_email])[0]->count;
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent recipes created by a specific user.
     */
    public function getRecentUserRecipes(Request $request)
    {
        $recipes = DB::select('SELECT * FROM get_recent_user_recipes(?, ?)', [$request->user_email, $request->limit]);
        return response()->json(['recipes' => $recipes]);
    }
}
