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
    public function dashboard(Request $request)
    {

        // Fetch filter parameters
    $action = $request->input('action');       // e.g., INSERT, UPDATE, DELETE
    $tableName = $request->input('table_name'); // e.g., users, recipes, categories

    // Base query for logs
    $logsQuery = DB::table('activity_logs_summary');

    // Apply filters dynamically
    if ($action) {
        $logsQuery->where('action', $action);
    }
    if ($tableName) {
        $logsQuery->where('table_name', $tableName);
    }

    // Fetch the filtered logs
    $logs = $logsQuery->orderBy('created_at', 'desc')->get();
    // Fetch data from the STATISTICS view
    $statistics = DB::select('SELECT * FROM STATISTICS');
    // $logs = DB::select('SELECT * FROM activity_logs_summary');
    $recipes = DB::select('SELECT * FROM get_recipes()');
    $recentRecipes = DB::select('
        SELECT users.id AS user_id, get_recent_recipe(users.id::bigint) AS recent_recipe 
        FROM users 
        WHERE users.role_id = 2
    ');

    // Fetch user and role information
    $users = User::with('role')->get();
    $roles = DB::table('roles')->get();
    

    // Pass all the combined data to the Admin view
    return Inertia::render('Admin', [
        'statistics' => $statistics,
        'users' => $users,
        'roles' => $roles,
        'recipes' => $recipes,
        'recentRecipes' => $recentRecipes,
        'logs' => $logs
    ]);
    }


    public function deleteRecipe($id)
    {
        DB::statement("SET app.current_user_id = " . auth()->id());

        \Log::info(message: "Deleting recipe with ID: $id"); // Log the ID
        
        $recipe = DB::table('recipes')->where('id', $id)->first();
        
        if ($recipe) {
            DB::table('recipes')->where('id', $id)->delete();
            return redirect()->route('admin')->with('success', 'Recipe deleted successfully.');
        }
        
        return redirect()->route('admin')->with('error', 'Recipe not found.');
    }
    
    

    public function users()
    {
        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();

        return Inertia::render('Admin', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /*------------------------
     | User Management Logic |
     ------------------------*/
    
    /**
     * Update a user's role.
     */
    
     public function updateRole(Request $request, $userId)
    {
        DB::statement("SET app.current_user_id = " . auth()->id());

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
         // Find the user by ID using DB facade
         DB::statement("SET app.current_user_id = " . auth()->id());

         $user = DB::table('users')->where('id', $id)->first();
     
         // Check if user exists
         if (!$user) {
             return redirect()->back()->with('error', 'User not found.');
         }
     
         // Prevent deleting admin users
         if ($user->role_id === 1) {
             return redirect()->back()->with('error', 'Admin users cannot be deleted.');
         }
     
         // Delete the user from the database
         DB::table('users')->where('id', $id)->delete();
     
         return redirect()->back()->with('message', 'User deleted successfully.');
     }
    
    /**
     * Add a new user to the system.
     */
    public function addUser(Request $request)
{
    DB::statement("SET app.current_user_id = " . auth()->id());

    // Validate the incoming request data, including the dynamic role_id
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'role_id' => 'required|exists:roles,id',  // Ensure role_id exists in roles table
    ]);

    // Insert the new user directly into the users table
    DB::table('users')->insert([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role_id' => $validated['role_id'],  // Set role dynamically based on the selected value
        'created_at' => now(),  // Set the created_at timestamp
        'updated_at' => now(),  // Set the updated_at timestamp
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
        DB::statement('REFRESH MATERIALIZED VIEW activity_logs_summary');
        
        return redirect()->back()->with('message', 'Statistics refreshed successfully');
    }
}