# Adding a page (permission module)

Pages are registry-driven. `config/cms.php` (`pages`) is the single source of
truth: sidebar, landing route, permission forms, role/user matrices, routes,
middleware labels, and DB seeds all read it. To add a page you register it
once, then build its controller and views.

To temporarily hide the Meta Tags item from the admin sidebar, open
`config/cms.php`, select the entire `'metadata' => [...]` entry, and press
**Ctrl+/** to comment or uncomment the selection in editors that support that
shortcut. This removes it from the registry-driven navigation and permission
matrix. It does not remove the explicit `/admin/seo-metadata` routes; remove or
comment those route definitions in `routes/web.php` as well to disable direct URL access.

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
php artisan db:seed --class=PagesAndPrivilegesSeeder
php artisan route:list
php artisan test
```

The idempotent seeder inserts missing `pages`/`privileges` rows and grants
Super Admin full access on the new page. `route:list` should show the new
`admin/<slug>` routes with the matching `permission:<slug>,<action>`
middleware. Enforcement, the sidebar link, the landing redirect, and the
permission-matrix rows then work with no further edits.

Use `php artisan migrate:fresh --seed` only with a new/disposable database: it
drops all tables. This project has individual current-schema migrations for fresh
installs; historical upgrades are archived. To preserve an existing database, keep its
connection unchanged and use its existing data, or point `.env` to the new
database before running migrations/seeds.

## Notes

- Permission checks (`permission` middleware, `canAccess()`) resolve
  against the `pages`/`privileges` tables, so no code change is needed
  for enforcement — only the registry entry and seed rows.
- 403 messages and the 403 back-link resolve labels from the registry
  with plain-language fallbacks.
- Form matrix row order follows registry order.
