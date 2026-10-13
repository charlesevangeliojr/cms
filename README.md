# CMS Template

Laravel 11 CMS with public Home/About pages and a permission-controlled admin area. Requires PHP 8.2 or later and Composer; see `composer.json` for dependencies.

## Documentation

- [Project standards](docs/PROJECT_STANDARDS.md): implementation and review rules.
- [Structure guide](docs/STRUCTURE.md): files, routes, data flow, and account workflows. This is the canonical structure guide; the former root copy has moved here.
- [Security](docs/SECURITY.md): implemented defenses, deployment requirements, and known gaps.
- [Google Analytics 4](docs/GOOGLE_ANALYTICS.md): GA4 tracking, read-only Data API credentials, dashboard metrics, and troubleshooting.

## Current features

- Database-backed users, roles, banners, contact messages, and newsletter subscribers.
- Account contact numbers, profile editing, and avatar uploads.
- Shared in-page notification and deletion-confirmation modals, including login and public-form feedback.
- Single-page user creation and editing: account details and permissions sit side by side on wide screens and stack on smaller screens, with a shared save bar.
- Inline role creation in place of the selector, using the module permissions table, preserving unsaved account information; confirmed deletion of unused roles by Super Admin.
- Role defaults with explicit per-user permission overrides and protected administrator accounts.
- Login/public-form rate limits, browser response headers, and restrictions on delegated account management.
- Public homepage banners come from the database; other Home/About content remains template data in `PageController`.
- Google Analytics 4 public tracking and a read-only Realtime/month-to-date traffic panel on the admin dashboard.

## Local setup

1. Run `composer install`.
2. Copy `.env.example` to `.env` if it does not already exist. Configure your local database and `APP_URL`.
3. Run `php artisan key:generate` for a new installation, then `php artisan migrate`.
4. For an isolated development database only, `php artisan db:seed` creates or resets the template administrator. Review `database/seeders/DatabaseSeeder.php` first. It contains a fixed development password and must not be run against production accounts.
5. Run `php artisan serve`, then open `/` or `/admin/login`. No Node.js, npm, or asset build step is required.

Public-site CSS and JavaScript live in `public/frontend/app.css` and `public/frontend/app.js`. Admin assets live in `public/backend/`, with module subfolders for banners and metadata. Blade loads these files directly with `asset()`; edit them in place and refresh the browser. No Vite or build folder is used. External libraries still load from CDNs. Banner and avatar files are stored directly in configured `public/uploads/` directories. These disks do not require `storage:link`. Image processing uses PHP GD when available.

Uploaded images under `public/uploads/` are not included in Git. When restoring a database on a new machine, copy those files from the original installation or re-upload them through the CMS. Missing assigned home banners, page banners, and blog images use bundled fallback artwork until their original files are restored; database paths are not changed.

## Verification

### Apache deployment

Prefer setting Apache's `DocumentRoot` to `public/`. A root `.htaccess` beside `.env` adds protection for sensitive files, private directories, directory listings, and executable uploads, and forwards requests into `public/` when the project root is used as the web root. Keep `public/.htaccess` for Laravel routing. Apache 2.4, `mod_rewrite`, and appropriate `AllowOverride` permissions are required. Neither `.htaccess` file is used by `php artisan serve` or Nginx; configure equivalent restrictions in Nginx directly. These rules do not replace secure application settings or patched dependencies.

### Code checks

```sh
php artisan test
php vendor/bin/pint --test
php artisan view:cache
php artisan view:clear
composer audit --locked
```

Last recorded code verification on 2026-09-25: 39 tests passed and one homepage `ExampleTest` failed because its test database lacks the `banners` table. Changed security PHP files passed Pint and Blade compilation passed. Repository-wide Pint previously reported issues in other files. The dependency audit could not run because Composer was unavailable in the agent environment. This documentation update does not represent a fresh full test run.

Before deployment, follow [Security](docs/SECURITY.md), including replacing development credentials, HTTPS, disabling debug output, and serving only `public/`.
