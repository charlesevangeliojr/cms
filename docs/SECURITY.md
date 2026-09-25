# Basic security defenses

Reviewed against the working tree on 2026-09-25. See [README](../README.md) for setup and [project standards](PROJECT_STANDARDS.md) for development rules.

## Implemented

- Login POSTs: 5 requests per minute per email/IP and 30 per IP. Contact and newsletter POSTs share 5 requests per minute per IP. These count all requests, including invalid submissions.
- Browser headers deny framing, disable MIME sniffing, restrict referrers, disable unused camera/microphone/location access, block object embeds and cross-origin form submissions, and restrict base URLs.
- Admin responses use no-store and noindex headers. Noindex is a crawler hint, not access control.
- Delegated account managers cannot assign Super Admin, manage protected/Super Admin accounts, manage accounts with greater permissions, or grant permissions they lack. Super Admin retains existing management authority.
- Role deletion requires Super Admin authorization and rejects the Super Admin role or any role with assigned accounts. The UI confirms deletion before sending the CSRF-protected request.
- Permission matrices validate nested arrays and boolean values.
- Existing Laravel CSRF protection, escaped Blade output, bound database queries, password hashing, and upload validation remain in use. The CSRF meta token is intentional and is not a secret credential leak.

## Deployment requirements and limits

- The current `DatabaseSeeder` creates or resets an administrator with a fixed development password. The login form no longer prefills or displays credentials and never repopulates passwords. Replace the seeded password before public deployment; do not run this seeder against production accounts. This documentation review identifies the issue but does not change that code.
- Profile password changes currently do not require the current password. Avatar replacement/deletion is not transactional with database updates. These remain hardening gaps.
- Use HTTPS, APP_ENV=production, APP_DEBUG=false, and SESSION_SECURE_COOKIE=true on the deployed server. Keep HTTP-only session cookies enabled. Configure trusted proxies explicitly when behind a proxy.
- Set the web root to public/. Never serve the repository root or .env. Disable execution of scripts under public/uploads at the web server. Application middleware cannot protect files served directly by the web server.
- Use a persistent shared cache for rate limiting across application workers. IP limits do not stop distributed attacks.
- Run composer audit --locked on a host with Composer and patch dependencies. This audit could not run here because Composer was unavailable.
- CSP is a basic policy, not a complete script/XSS policy. Existing inline scripts and CDN assets need migration to controlled assets/nonces before enforcing script-src. Security headers follow OWASP guidance: https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html
- No live deployment, browser penetration test, or web-server configuration audit was performed. These measures reduce specific risks; they do not guarantee prevention of every attack.

## Verification

Last recorded code checks (2026-09-25; not rerun for this documentation-only update): 39 passed, 1 failed. Changed security PHP files passed Pint and Blade cache/clear passed. Earlier repository-wide Pint reported unrelated formatting issues.

SecurityDefenseTest covers headers, login/public throttling, blocked privilege escalation and permitted delegated creation. The full suite still has the existing ExampleTest failure because its homepage test does not migrate the banners table.
