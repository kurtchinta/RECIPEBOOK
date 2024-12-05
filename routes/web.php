<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Welcome page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});




// Authenticated routes
Route::middleware(['auth', 'setDB'])->group(function () {

    // General dashboard for all roles
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

   // Admin-specific routes
Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin');
    Route::get('/admin/displays', [AdminController::class, 'display_info'])->name('admin.display');
    // Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    // Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    // Route::get('/admin/refresh-statistics', [AdminController::class, 'refreshStatistics'])->name('admin.refreshStatistics');
});














    // Chef-specific routes
    Route::middleware('chef')->group(function () {
        Route::get('/chef', function () {
            return Inertia::render('Chef');
        })->name('chef');

        Route::resource('recipes', RecipeController::class);
    });

    // User-specific routes (without custom middleware, just role check in closure)
    Route::middleware('auth')->group(function () {
        Route::get('/user', function () {
            return Inertia :: render('User');
        })->name('user');

        // Route::get('/recipes', [RecipeController::class, 'index'])->name('user.recipes.index');
        // Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])->name('user.recipes.show');
    });

    // Profile routes for all authenticated users
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// Include auth routes
require __DIR__ . '/auth.php';
