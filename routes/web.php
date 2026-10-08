<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteMetadataController;
use Illuminate\Support\Facades\Route;

// Frontend — public landing pages, NO login required.
// Data comes from the controller (mock for now).
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/blogs', [\App\Http\Controllers\PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blogs/{category:slug}', [\App\Http\Controllers\PublicBlogController::class, 'index'])->name('blog.category');
Route::get('/blogs/{category:slug}/{post}', [\App\Http\Controllers\PublicBlogController::class, 'show'])->name('blog.show');
Route::get('/sitemap.xml', [\App\Http\Controllers\PublicBlogController::class, 'sitemap'])->name('sitemap');

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
    Route::get('/dashboard/traffic', [DashboardController::class, 'traffic'])
        ->middleware(['permission:dashboard,view', 'throttle:30,1'])->name('dashboard.traffic');

    Route::get('/seo-metadata', [SiteMetadataController::class, 'index'])
        ->middleware('permission:metadata,view')->name('seo-metadata.index');
    Route::put('/seo-metadata', [SiteMetadataController::class, 'update'])
        ->middleware('permission:metadata,edit')->name('seo-metadata.update');

    Route::get('/home-banners', [\App\Http\Controllers\HomeBannerController::class, 'index'])->middleware('permission:banners,view')->name('home-banners.index');
    Route::put('/home-banners', [\App\Http\Controllers\HomeBannerController::class, 'update'])->middleware('permission:banners,edit')->name('home-banners.update');
    Route::put('/home-banners/visibility', [\App\Http\Controllers\HomeBannerController::class, 'updateVisibility'])->middleware('permission:banners,edit')->name('home-banners.visibility');
    Route::post('/home-banners/images', [\App\Http\Controllers\HomeBannerController::class, 'storeImages'])->middleware('permission:banners,edit')->name('home-banners.images.store');
    Route::put('/home-banners/images/order', [\App\Http\Controllers\HomeBannerController::class, 'reorderImages'])->middleware('permission:banners,edit')->name('home-banners.images.order');
    Route::put('/home-banners/images/{image}', [\App\Http\Controllers\HomeBannerController::class, 'updateImageStatus'])->middleware('permission:banners,edit')->name('home-banners.images.update');
    Route::delete('/home-banners/images/{image}', [\App\Http\Controllers\HomeBannerController::class, 'destroyImage'])->middleware('permission:banners,edit')->name('home-banners.images.destroy');
    Route::get('/page-banners', [\App\Http\Controllers\PageBannerController::class, 'index'])->middleware('permission:banners,view')->name('page-banners.index');
    Route::get('/page-banners/create', [\App\Http\Controllers\PageBannerController::class, 'create'])->middleware('permission:banners,add')->name('page-banners.create');
    Route::post('/page-banners', [\App\Http\Controllers\PageBannerController::class, 'store'])->middleware('permission:banners,add')->name('page-banners.store');
    Route::get('/page-banners/{pageBanner}/edit', [\App\Http\Controllers\PageBannerController::class, 'edit'])->middleware('permission:banners,edit')->name('page-banners.edit');
    Route::put('/page-banners/{pageBanner}', [\App\Http\Controllers\PageBannerController::class, 'update'])->middleware('permission:banners,edit')->name('page-banners.update');
    Route::delete('/page-banners/{pageBanner}', [\App\Http\Controllers\PageBannerController::class, 'destroy'])->middleware('permission:banners,delete')->name('page-banners.destroy');

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
            if ($resource === 'contacts' && $permission === 'view') {
                Route::get('/contacts/export', [ContactController::class, 'export'])->middleware('permission:contacts,view')->name('contacts.export');
                Route::post('/contacts/bulk', [ContactController::class, 'bulk'])->name('contacts.bulk');
            }
            if ($resource === 'newsletters' && $permission === 'view') {
                Route::get('/newsletters/export', [NewsletterController::class, 'export'])->middleware('permission:newsletters,view')->name('newsletters.export');
                Route::post('/newsletters/bulk', [NewsletterController::class, 'bulk'])->name('newsletters.bulk');
            }
            if ($resource === 'blogs' && $permission === 'view') {
                Route::get('/blogs/{blog}/pdf', [\App\Http\Controllers\BlogController::class, 'downloadPdf'])->middleware('permission:blogs,view')->name('blogs.pdf');
            }
            Route::resource($resource, $page['controller'])->only($only)->middleware("permission:{$resource},{$permission}");
        }
    }
});

// Legacy URLs → admin URLs
Route::redirect('/login', '/admin/login', 302);
Route::redirect('/dashboard', '/admin/dashboard', 302);
