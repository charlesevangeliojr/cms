# CMS Template

Laravel 11 CMS with public Home/About pages and a permission-controlled admin area. Requires PHP 8.2 or later and Composer; see `composer.json` for dependencies.

## Documentation

- [Project standards](docs/PROJECT_STANDARDS.md): implementation and review rules.
- [Structure guide](docs/STRUCTURE.txt): files, routes, data flow, and account workflows. This is the canonical structure guide; the former root copy has moved here.
- [Security](docs/SECURITY.md): implemented defenses, deployment requirements, and known gaps.

## Current features

- Database-backed users, roles, banners, contact messages, and newsletter subscribers.
- Account contact numbers, profile editing, and avatar uploads.
- Shared in-page notification and deletion-confirmation modals, including login and public-form feedback.
- Single-page user creation and editing: account details and permissions sit side by side on wide screens and stack on smaller screens, with a shared save bar.
- Inline role creation in place of the selector, using the module permissions table, preserving unsaved account information; confirmed deletion of unused roles by Super Admin.
- Role defaults with explicit per-user permission overrides and protected administrator accounts.
- Login/public-form rate limits, browser response headers, and restrictions on delegated account management.
- Public homepage banners come from the database; other Home/About content remains template data in `PageController`.

## Local setup

1. Run `composer install`.
2. Copy `.env.example` to `.env` if it does not already exist. Configure your local database and `APP_URL`.
3. Run `php artisan key:generate` for a new installation, then `php artisan migrate`.
4. For an isolated development database only, `php artisan db:seed` creates or resets the template administrator. Review `database/seeders/DatabaseSeeder.php` first. It contains a fixed development password and must not be run against production accounts.
5. Run `php artisan serve`, then open `/` or `/admin/login`.

Current layouts load browser assets from CDNs. There is no checked-in `package.json`; an npm/Vite build is not part of the current setup. Banner and avatar files are stored directly in `public/uploads/banners` and `public/uploads/avatars` through configured disks. These disks do not require `storage:link`. Image processing uses PHP GD when available.

## Verification

```sh
php artisan test
php vendor/bin/pint --test
php artisan view:cache
php artisan view:clear
composer audit --locked
```

Last recorded code verification on 2026-09-25: 39 tests passed and one homepage `ExampleTest` failed because its test database lacks the `banners` table. Changed security PHP files passed Pint and Blade compilation passed. Repository-wide Pint previously reported issues in other files. The dependency audit could not run because Composer was unavailable in the agent environment. This documentation update does not represent a fresh full test run.

Before deployment, follow [Security](docs/SECURITY.md), including replacing development credentials, HTTPS, disabling debug output, and serving only `public/`.
