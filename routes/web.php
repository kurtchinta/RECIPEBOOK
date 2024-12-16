<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes that require authentication and setDB middleware
Route::middleware(['auth', 'setDB'])->group(function () {
    // Dashboard route - AdminController
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/user', [AdminController::class, 'users'])->name('user');

   // Admin routes 
   Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin');
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
    Route::put('/admin/users/{user}/update-role', [AdminController::class, 'updateUserRole'])->name('admin.updateUserRole');
    Route::post('/admin', [AdminController::class, 'addUser'])->name('admin.addUser');
    Route::delete('/admin/recipes/{id}', [AdminController::class, 'deleteRecipe'])->name('admin.deleteRecipe');
    Route::post('/admin/refresh-stats', [AdminController::class, 'refreshStatistics'])->name('admin.refreshStats'); // Refresh stats
});

    // Chef routes
    Route::middleware('chef')->group(function () {
        Route::get('/chef', [ChefController::class, 'dashboard'])->name('chef');  // Chef profile info
        Route::get('/chef/dashboard', [ChefController::class, 'dashboard'])->name('chef.dashboard');
        Route::get('/chef/dashboard/stats', [ChefController::class, 'getDashboardStats'])->name('chef.getDashboardStats');
        Route::post('/chef/recipes', [ChefController::class, 'storeRecipe'])->name('chef.storeRecipe');
        Route::post('/chef/recipes/{recipe}', [ChefController::class, 'updateRecipe'])->name('chef.updateRecipe');
        Route::get('/chef/recipes/{recipe}/edit', [ChefController::class, 'editRecipe'])->name('chef.editRecipe');
        Route::delete('/chef/recipes/{recipe}', [ChefController::class, 'deleteRecipe'])->name('chef.deleteRecipe');
    });

    //User routes
    Route::get('/user', [UserController::class, 'users'])->name('user');
    
    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// Authentication routes
require __DIR__ . '/auth.php';
