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

class UserController extends Controller
{
    public function users()
    {
        $users = User::with('role')->get();
        $roles = DB::table('roles')->get();
        $recipes = DB::table('recipes')->get();
        $categories = DB::table('categories')->get();

        return Inertia::render('User', [
            'users' => $users,
            'roles' => $roles,
            'recipes' => $recipes,
            'categories' => $categories,
        ]);
    }

}