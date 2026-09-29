<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Frontend — public landing pages, NO login required.
// Data comes from the controller (mock for now).
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

// Public test inputs for Contact Us & Newsletter (visible on landing page)
Route::post('/contact', [ContactController::class, 'storePublic'])->middleware('throttle:public-submissions')->name('contact.store');
Route::post('/newsletter', [NewsletterController::class, 'storePublic'])->middleware('throttle:public-submissions')->name('newsletter.store');

// Legacy URL → canonical URL
Route::redirect('/home', '/', 301);

// Admin auth (login lives at /admin/login)
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Admin (sidebar layout, login required + module permissions)
Route::middleware(['auth', 'active', 'timeout'])->prefix('admin')->group(function () {
    // Profile — any authenticated user, no module permission required
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard,view')->name('dashboard');

    // Roles — Super Admin checks live in RoleController
    Route::resource('roles', RoleController::class)->only(['store', 'destroy']);

    // Admin CRUD from the cms page registry — resource name doubles
    // as the permission module. Each action maps to its permission:
    // view/add/edit/delete.
    $actionPermission = [
        'index' => 'view', 'create' => 'add', 'store' => 'add',
        'edit' => 'edit', 'update' => 'edit', 'destroy' => 'delete',
    ];

    foreach (config('cms.pages', []) as $resource => $page) {
        if (empty($page['controller']) || empty($page['actions'])) {
            continue;
        }
        $grouped = [];
        foreach ($page['actions'] as $action) {
            $grouped[$actionPermission[$action]][] = $action;
        }
        foreach ($grouped as $permission => $only) {
            Route::resource($resource, $page['controller'])->only($only)->middleware("permission:{$resource},{$permission}");
        }
    }
});

// Legacy URLs → admin URLs
Route::redirect('/login', '/admin/login', 302);
Route::redirect('/dashboard', '/admin/dashboard', 302);
