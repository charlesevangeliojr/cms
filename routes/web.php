<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Frontend — public landing pages, NO login required.
// Data comes from the controller (mock for now).
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

// Legacy URL → canonical URL
Route::redirect('/home', '/', 301);

// Admin auth (login lives at /admin/login)
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Admin (sidebar layout, login required + module permissions)
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard,view')->name('dashboard');
    Route::get('/admin/banners', [BannerController::class, 'index'])
        ->middleware('permission:banners,view')->name('banners.index');
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
