<?php

/*
|--------------------------------------------------------------------------
| CMS admin pages and privileges
|--------------------------------------------------------------------------
|
| Single source of truth for permission pages. To add a page:
|   1. Add an entry below (slug => name, description, route, icon,
|      controller, resource actions).
|   2. Create the controller, views, and permission matrix rows.
|   3. Run `php artisan migrate:fresh --seed` so the pages,
|      privileges, and Super Admin grants are seeded.
|
| Order defines sidebar and landing-page priority.
*/

return [
    'pages' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'description' => 'Main overview and shortcuts.',
            'route' => 'dashboard',
            'icon' => 'dashboard',
        ],
        'contacts' => [
            'name' => 'Contact Us',
            'description' => 'Inbox for contact form messages.',
            'route' => 'contacts.index',
            'icon' => 'contacts',
            'controller' => App\Http\Controllers\ContactController::class,
            'actions' => ['index', 'update', 'destroy'],
        ],
        'newsletters' => [
            'name' => 'Newsletter',
            'description' => 'Newsletter subscribers and audience.',
            'route' => 'newsletters.index',
            'icon' => 'newsletters',
            'controller' => App\Http\Controllers\NewsletterController::class,
            'actions' => ['index', 'update', 'destroy'],
        ],
        'banners' => [
            'name' => 'Banner Management',
            'description' => 'Promotional banners and placements.',
            'route' => 'banners.index',
            'icon' => 'banners',
            'controller' => App\Http\Controllers\BannerController::class,
            'actions' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        ],
        'users' => [
            'name' => 'User Management',
            'description' => 'Accounts, roles, and access.',
            'route' => 'users.index',
            'icon' => 'users',
            'controller' => App\Http\Controllers\UserController::class,
            'actions' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        ],
    ],

    'privileges' => [
        'view' => 'Open and read pages.',
        'add' => 'Create new records.',
        'edit' => 'Change existing records.',
        'delete' => 'Permanently remove records.',
    ],
];
