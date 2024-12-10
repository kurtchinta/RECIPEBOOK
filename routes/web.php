<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChefController;
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

Route::middleware(['auth', 'setDB'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/user', [AdminController::class, 'users'])->name('user');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'display_info'])->name('admin');
        Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
        Route::put('/admin/users/{user}/update-role', [AdminController::class, 'updateUserRole'])->name('admin.updateUserRole');
        Route::post('/admin/users', [AdminController::class, 'addUser'])->name('admin.addUser');
    });

    Route::middleware('chef')->group(function () {
        Route::get('/chef', [ChefController::class, 'display_info'])->name('chef');
        Route::get('/chef/dashboard', [ChefController::class, 'dashboard'])->name('chef.dashboard');
        Route::get('/chef/dashboard/stats', [ChefController::class, 'getDashboardStats'])->name('chef.getDashboardStats');
        Route::get('/chef/recipes/create', [ChefController::class, 'createRecipe'])->name('chef.createRecipe');
        Route::post('/chef/recipes', [ChefController::class, 'storeRecipe'])->name('chef.storeRecipe');
        Route::get('/chef/recipes/{id}/edit', [ChefController::class, 'editRecipe'])->name('chef.editRecipe');
        Route::put('/chef/recipes/{id}', [ChefController::class, 'updateRecipe'])->name('chef.updateRecipe');
        Route::delete('/chef/recipes/{id}', [ChefController::class, 'deleteRecipe'])->name('chef.deleteRecipe');
        Route::post('/chef/categories', [ChefController::class, 'storeCategory'])->name('chef.storeCategory');
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';