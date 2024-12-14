<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    /*-----------------------------------
     | Dashboard and Overview Functions |
     -----------------------------------*/
    
    /**
     * Display the admin dashboard with recipe statistics and user-role data.
     */
    public function dashboard()
    {
    // Fetch data from the STATISTICS view
    $statistics = DB::select('SELECT * FROM STATISTICS');

    $recentRecipes = DB::select('
        SELECT users.id AS user_id, get_recent_recipe(users.id::bigint) AS recent_recipe 
        FROM users 
        WHERE users.role_id = 2
    ');

    \Log::info($recentRecipes); // Check the output in your logs


    // Fetch user and role information
    $users = User::with('role')->get();
    $roles = DB::table('roles')->get();

    // Pass all the combined data to the Admin view
    return Inertia::render('Admin', [
        'statistics' => $statistics,
        'users' => $users,
        'roles' => $roles,
        'recentRecipes' => $recentRecipes,
    ]);
    }

    /*------------------------
     | User Management Logic |
     ------------------------*/
    
    /**
     * Update a user's role.
     */
    
    public function updateUserRole(Request $request, $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return back()->withErrors(['message' => 'User not found.']);
        }

        if ($user->role_id == 1) {
            return back()->withErrors(['message' => 'Admin roles cannot be edited or demoted.']);
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::table('users')->where('id', $userId)->update([
            'role_id' => $validated['role_id'],
        ]);

        return back()->with('message', 'User role updated successfully!');
    }

    /**
     * Delete a user from the system.
     */

    public function deleteUser($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if ($user->role_id === 1) {
            return response()->json(['error' => 'Admin users cannot be deleted'], 403);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        DB::table('users')->where('id', $id)->delete();

        return redirect()->back()->with('message', 'User deleted successfully.');
    }
    
    /**
     * Add a new user to the system.
     */
    public function addUser(Request $request)
    {
        // Validate the incoming request data, including the dynamic role_id
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',  // Ensure role_id exists in roles table
        ]);

        // Create a new user with the provided role_id
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role_id' => $validated['role_id'],  // Set role dynamically based on the selected value
        ]);

        return back()->with('message', 'User added successfully with the selected role!');
    }

    /**
     * Get recent recipes created by a specific user.
     */

    public function getRecentUserRecipes(Request $request)
    {
        $recipes = DB::select('SELECT * FROM get_recent_user_recipes(?, ?)', [$request->user_email, $request->limit]);
        
        return response()->json(['recipes' => $recipes]);
    }
    
       /**
     * Refresh the materialized view for recipe statistics.
     */
    public function refreshStatistics()
    {
        DB::statement('REFRESH MATERIALIZED VIEW recipe_statistics');
        
        return redirect()->back()->with('message', 'Statistics refreshed successfully');
    }
}
