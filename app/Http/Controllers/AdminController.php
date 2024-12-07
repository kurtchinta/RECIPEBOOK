<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog; // Import the ActivityLog model
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
        DB::statement('REFRESH MATERIALIZED VIEW recipe_statistics');
        return redirect()->back()->with('message', 'Statistics refreshed successfully');
    }

    /**
     * Display user and role information.
     */
    public function display_info()
    {
        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();

        return Inertia::render('Admin', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(Request $request, $userId)
    {
        \Log::info('Updating user role', ['request' => $request->all(), 'userId' => $userId]);

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

        if ($validated['role_id'] != 1 && $user->role_id == 1) {
            return back()->withErrors(['message' => 'Admins cannot be demoted.']);
        }

        DB::table('users')->where('id', $userId)->update([
            'role_id' => $validated['role_id'],
        ]);

        return back()->with('message', 'User role updated successfully!');
    }

    /**
     * Delete a user from the system.
     */
    public function deleteUser(User $user)
    {
        if ($user->role_id === 1) {
            return redirect()->back()->with('error', 'Admin users cannot be deleted.');
        }

        $user->delete();

        return redirect()->back()->with('message', 'User deleted successfully.');
    }

    /**
     * Add a new user to the system.
     */
    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role_id' => 3,
        ]);

        return back()->with('message', 'User added successfully with a default role of User!');
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

    /**
     * Get activity logs.
     */
   /**
 * Get activity logs without needing to refresh anything.
 */

}
