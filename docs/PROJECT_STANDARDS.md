# CMS Project Base Standards

This document is the reusable template standard for this CMS. Every developer working on an ongoing project from this base must follow it. `STRUCTURE.txt` describes where files live; this document describes how work must be done.

## 1. Template purpose

Use this base for content-management projects with:

- Public frontend pages.
- A permission-controlled `/admin` backend.
- Database-backed users, roles, banners, contact messages, newsletter subscribers, and future content modules.
- Responsive Blade interfaces.
- Automated feature coverage for important behavior.

Do not fork the template behavior independently without updating this standard.

## 2. Responsibilities

### 2.1 Template roles

| Role | Owns | Must review |
|---|---|---|
| Template owner | Folder structure, standards, breaking changes | New modules, middleware, auth behavior |
| Backend developer | Migrations, models, controllers, middleware, validation, APIs/jobs | Database changes and permission behavior |
| Frontend developer | Blade, Tailwind, responsive behavior, accessibility, upload UX | New pages, forms, navigation, notifications |
| QA/release owner | Tests, migration verification, deployment checklist | Definition-of-done evidence |

### 2.2 Application access roles

Application roles are data, not hard-coded developer assumptions:

- Roles live in the `roles` table.
- Only active roles appear in user-facing role selectors.
- Role defaults live in the role record’s JSON permissions.
- User-specific checkbox selections override role defaults.
- Permission matrices must store explicit `true`/`false` values.
- A missing checkbox means `false`; it must not silently restore role access.
- Protected administrative accounts cannot be deleted, but remain editable.

## 3. Request and data flow

Follow this order:

```text
routes/web.php
→ middleware: auth → active → permission
→ controller validation
→ Eloquent model/database
→ Blade view
→ public assets/uploads
```

Rules:

- Controllers validate input and coordinate models.
- Models define relationships, casts, fillable fields, and access helpers.
- Blade displays data; it does not query the database.
- Shared markup belongs in components or partials.
- Admin behavior must use real database records.
- Do not introduce new admin mock arrays.

## 4. Folder responsibilities

### `app/`

- `Http/Controllers`: one responsibility per controller action.
- `Http/Middleware`: reusable request enforcement.
- `Models`: database representation and domain helpers.
- `Providers`: application bootstrapping only.

### `bootstrap/`

- Register middleware aliases and global middleware behavior.
- Do not place business logic here.

### `routes/`

- Use descriptive, kebab-case URLs.
- Use dot-notation route names.
- Protect every admin route with authentication.
- Add module/action permission middleware to every admin content route.
- Redirect users to their first permitted module after login.

### `database/`

- Every schema change needs a migration.
- Use descriptive migration filenames.
- Seed only initial structural data needed by the template.
- Do not seed demo users, banners, or content records for production.
- Factories are for tests and local development.

### `resources/views/`

- `frontend/`: public pages.
- `backend/`: authenticated CMS pages.
- `backend/layouts/`: shared admin shell.
- `backend/partials/`: navigation, toasts, reusable page fragments.
- `components/`: shared UI primitives.
- `errors/`: full-page HTTP error screens.

### `public/`

- `frontend/`: checked-in public page assets.
- `uploads/<module>/`: runtime CMS uploads only.
- `build/`: generated assets; never edit manually.

### `tests/`

- `Feature/`: user-visible behavior.
- `Unit/`: isolated logic.
- Name tests by behavior, not implementation.
- Cover permission grants, denials, validation, uploads, and destructive actions.

## 5. Backend standards

### Controllers

- Validate every create/update request.
- Normalize checkbox matrices to complete `true`/`false` maps.
- Use route-model binding where practical.
- Return named-route redirects with transient success/error messages.
- Keep controllers thin; move reusable domain logic to models.

### Authentication and accounts

- Check active status at login.
- Recheck active status on every authenticated admin request.
- Immediately expire sessions for deactivated accounts.
- Prevent deletion of protected accounts in both UI and controller logic.
- Preserve edit access for protected accounts unless explicitly restricted.

### Permissions

- Every admin UI link must match route enforcement.
- Hiding a link is not a substitute for middleware.
- Use `dashboard:view`, `banners:<action>`, `users:<action>`, `contacts:<action>`, or `newsletters:<action>` consistently.
- Permission-denied responses must use the shared 403 experience.
- Name the denied action and module in plain language.

## 6. Frontend and UX standards

- Design mobile-first.
- Keep the admin sidebar and top header fixed.
- Avoid horizontal scrolling on mobile lists and forms.
- Use responsive cards, stacked rows, breadcrumbs, and clear action buttons.
- Associate every label with its input.
- Explain passwords, roles, permissions, uploads, and destructive actions inline.
- Use global auto-dismissing toasts for transient success/error/info messages.
- Keep form validation errors inline with the relevant form.
- Keep full-page errors, including 403 permission errors, as full pages.
- Do not use blocking modal dialogs for ordinary notifications.

## 7. File upload standards

- Create one filesystem disk per upload module.
- Store uploads under `public/uploads/<module>/`.
- Validate file type and maximum size in the controller.
- Display the same size limit in the upload form.
- Add an oversized-upload regression test.
- Delete replacement files safely when database updates fail.
- Delete associated files when records are deleted.

## 8. Naming conventions

- Models: singular PascalCase, for example `Banner`.
- Tables: plural snake_case, for example `banners`.
- Controllers: PascalCase with `Controller`, for example `BannerController`.
- Routes: kebab-case URLs and dot-notation names.
- Blade files: kebab-case.
- Blade components: kebab-case and reusable.
- Migrations: timestamped, action-oriented filenames.
- Tests: `<Area><Behavior>Test.php`.

## 9. Definition of done

A task is done only when all applicable items pass:

1. Migration created and applied successfully.
2. Database-backed behavior replaces any temporary data.
3. Routes are named and permission-protected.
4. UI is responsive on mobile and desktop.
5. Upload limits, permissions, validation, and destructive actions are tested.
6. `vendor/bin/pint` passes.
7. `php artisan test` passes.
8. `php artisan view:cache` and `view:clear` pass.
9. `STRUCTURE.txt` and this standard are updated if structure or workflow changed.
10. No unrelated files or generated runtime files are committed.

## 10. Template change process

1. Implement the feature using the existing base pattern.
2. Add or update tests before considering the work complete.
3. Update `STRUCTURE.txt` when folders, routes, or responsibilities change.
4. Update this standard when adding a reusable architectural rule.
5. Verify migrations from an empty database when schema changes are involved.
6. Keep template documentation free of project-specific credentials or data.

## 11. Admin interface conventions

- The shared admin shell owns typography, keyboard focus, form target sizes, and mobile navigation.
- Highlight the current module on its list, create, and edit pages.
- Provide a skip-to-content link and accessible names for navigation and filters.
- Show action text alongside banner icons; convert banner table rows into stacked cards on mobile.
- Only display create, edit, and delete actions when the account has the corresponding permission.
- Keep interface copy focused on the user's task.