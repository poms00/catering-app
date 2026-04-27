<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\MenuController;


Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/dashboard', 'admin/dashboard/index')->name('dashboard.index');
    Route::resource('/menu', MenuController::class);

    // API routes for combobox options
    Route::get('/api/menu-categories', [MenuController::class, 'apiCategories'])->name('api.menu-categories');
    Route::get('/api/menu-groups', [MenuController::class, 'apiGroups'])->name('api.menu-groups');
    Route::post('/api/menu-categories', [MenuController::class, 'apiCreateCategory'])->name('api.menu-categories.store');
    Route::post('/api/menu-groups', [MenuController::class, 'apiCreateGroup'])->name('api.menu-groups.store');
});

require __DIR__ . '/settings.php';
