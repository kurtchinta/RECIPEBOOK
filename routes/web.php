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
        // Admin Dashboard route
        Route::get('/admin', [AdminController::class, 'display_info'])->name('admin');

        // User management routes
        Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
        Route::put('/admin/update-user-role/{user}', [AdminController::class, 'updateUserRole'])->name('admin.updateUserRole');
        Route::post('/admin/addUser', [AdminController::class, 'addUser'])->name('admin.addUser');
    });

    // Chef-specific routes
    Route::middleware('chef')->group(function () {
        Route::get('/chef', function () {
            return Inertia::render('Chef');
        })->name('chef');

        Route::resource('recipes', RecipeController::class)->except(['index', 'show']);
        Route::post('/chef/store', [AdminController::class, 'store'])->name('chef.store');
    });

    // User-specific routes
    Route::middleware('auth')->group(function () {
        Route::get('/user', function () {
            return Inertia::render('User');
        })->name('user');
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
