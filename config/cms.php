<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| CMS admin pages and privileges
|--------------------------------------------------------------------------
|
| Single source of truth for permission pages. To add a page:
|   1. Add an entry below (slug => name, description, route, icon,
|      controller, resource actions).
|   2. Create the controller, views, and permission matrix rows.
 |   3. Run `php artisan db:seed --class=PagesAndPrivilegesSeeder` to
 |      synchronize pages, privileges, and Super Admin grants without deleting data.
 |
 | Removing/commenting an entry disables its admin module, including direct
 | URLs and related actions. Existing database grants cannot enable a missing
 | module. Clear config and route caches after editing this registry.
 | Public website routes and own-profile access are configured separately.
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
            'controller' => ContactController::class,
            'actions' => ['index', 'update', 'destroy'],
        ],
        'newsletters' => [
            'name' => 'Newsletter',
            'description' => 'Newsletter subscribers and audience.',
            'route' => 'newsletters.index',
            'icon' => 'newsletters',
            'controller' => NewsletterController::class,
            'actions' => ['index', 'update', 'destroy'],
        ],
        'banners' => [
            'name' => 'Banner Management',
            'description' => 'Promotional banners and placements.',
            'route' => 'home-banners.index',
            'icon' => 'banners',
        ],
        'blogs' => [
            'name' => 'Blog Posts',
            'description' => 'Articles, publishing, and search engine listings.',
            'route' => 'blogs.index',
            'icon' => 'default',
            'controller' => BlogController::class,
            'actions' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
        ],
        'metadata' => [
            'name' => 'Meta Tags',
            'description' => 'Default site titles, descriptions, and social sharing metadata.',
            'route' => 'seo-metadata.index',
            'icon' => 'tags',
        ],
        'users' => [
            'name' => 'User Management',
            'description' => 'Accounts, roles, and access.',
            'route' => 'users.index',
            'icon' => 'users',
            'controller' => UserController::class,
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
