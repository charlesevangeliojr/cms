# Adding a page (permission module)

Pages are registry-driven. `config/cms.php` (`pages`) is the single source of
truth: sidebar, landing route, permission forms, role/user matrices, routes,
middleware labels, and DB seeds all read it. To add a page you register it
once, then build its controller and views.

## 1. Register the page in `config/cms.php`

```php
'reports' => [
    'name' => 'Reports',
    'description' => 'Usage reports and exports.',
    'route' => 'reports.index',
    'icon' => 'reports', // add the SVG path to $navIcons in
                         // resources/views/backend/partials/sidebar.blade.php,
                         // or omit it to use the generic icon
    'controller' => App\Http\Controllers\ReportController::class,
    'actions' => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
],
```

- Put the entry in sidebar/landing-priority order.
- `actions` is the resource subset the page supports. List-only pages
  (like contacts/newsletters) use `['index', 'update', 'destroy']`.
- Pages without `controller`/`actions` (like `dashboard`) are skipped by
  the route loop; define their routes explicitly.

## 2. Build the controller and views

- Controller with the standard resource methods (`index`, `create`,
  `store`, `edit`, `update`, `destroy` — only the ones in `actions`).
- Blade views under `resources/views/backend/<slug>/`.
- Gate any extra buttons with `canAccess('<slug>', '<action>')`.
- Optional: add a dashboard card in `dashboard.blade.php` (cards are
  per-module custom UI and are not generated).

## 3. Seed and verify

```
php artisan migrate:fresh --seed
php artisan route:list
php vendor/bin/phpunit
```

`migrate:fresh --seed` inserts the `pages`/`privileges` rows and grants
Super Admin full access on the new page. `route:list` should show the new
`admin/<slug>` routes with the matching `permission:<slug>,<action>`
middleware. Enforcement, the sidebar link, the landing redirect, and the
permission-matrix rows then work with no further edits.

## Notes

- Permission checks (`permission` middleware, `canAccess()`) resolve
  against the `pages`/`privileges` tables, so no code change is needed
  for enforcement — only the registry entry and seed rows.
- 403 messages and the 403 back-link resolve labels from the registry
  with plain-language fallbacks.
- Form matrix row order follows registry order.
