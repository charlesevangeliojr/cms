<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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
Route::middleware(['auth', 'active'])->group(function () {
    // Profile — any authenticated user, no module permission required
    Route::get('/admin/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/admin/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/admin/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard,view')->name('dashboard');
    Route::get('/admin/roles/create', [RoleController::class, 'create'])
        ->name('roles.create');
    Route::post('/admin/roles', [RoleController::class, 'store'])
        ->name('roles.store');
    Route::delete('/admin/roles/{role:name}', [RoleController::class, 'destroy'])->where('role', '.*')->name('roles.destroy');
    Route::get('/admin/banners', [BannerController::class, 'index'])
        ->middleware('permission:banners,view')->name('banners.index');
    Route::get('/admin/banners/create', [BannerController::class, 'create'])
        ->middleware('permission:banners,add')->name('banners.create');
    Route::post('/admin/banners', [BannerController::class, 'store'])
        ->middleware('permission:banners,add')->name('banners.store');
    Route::get('/admin/banners/{banner}/edit', [BannerController::class, 'edit'])
        ->middleware('permission:banners,edit')->name('banners.edit');
    Route::put('/admin/banners/{banner}', [BannerController::class, 'update'])
        ->middleware('permission:banners,edit')->name('banners.update');
    Route::delete('/admin/banners/{banner}', [BannerController::class, 'destroy'])
        ->middleware('permission:banners,delete')->name('banners.destroy');
    Route::get('/admin/contacts', [ContactController::class, 'index'])
        ->middleware('permission:contacts,view')->name('contacts.index');
    Route::patch('/admin/contacts/{contact}', [ContactController::class, 'update'])
        ->middleware('permission:contacts,edit')->name('contacts.update');
    Route::delete('/admin/contacts/{contact}', [ContactController::class, 'destroy'])
        ->middleware('permission:contacts,delete')->name('contacts.destroy');
    Route::get('/admin/newsletters', [NewsletterController::class, 'index'])
        ->middleware('permission:newsletters,view')->name('newsletters.index');
    Route::patch('/admin/newsletters/{newsletter}', [NewsletterController::class, 'update'])
        ->middleware('permission:newsletters,edit')->name('newsletters.update');
    Route::delete('/admin/newsletters/{newsletter}', [NewsletterController::class, 'destroy'])
        ->middleware('permission:newsletters,delete')->name('newsletters.destroy');
    Route::get('/admin/users', [UserController::class, 'index'])
        ->middleware('permission:users,view')->name('users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])
        ->middleware('permission:users,add')->name('users.create');
    Route::post('/admin/users', [UserController::class, 'store'])
        ->middleware('permission:users,add')->name('users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users,edit')->name('users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users,edit')->name('users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users,delete')->name('users.destroy');
});

// Legacy URLs → admin URLs
Route::redirect('/login', '/admin/login', 302);
Route::redirect('/dashboard', '/admin/dashboard', 302);
