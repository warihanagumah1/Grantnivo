# Grantnivo — Figma V1

This application implements the **V1 public site and staff admin console** from the Grantnivo Figma Make document. The user-facing scope is intentionally limited to V1: grant discovery, grant detail pages, platform/about pages, early-access waitlist, and staff administration.

The older Organisation Brain, customer dashboard, proposals, applications, registration, and V2 switch have been retired. The original engineering/build documents remain as long-term product references; they are not the active release scope.

## Stack

PHP 8.3+, Laravel 13, Livewire 4, Flux, Tailwind 4, Vite, and Laravel Fortify. This local installation uses the existing MySQL database `grantnivo`; tests use isolated SQLite. No AI API, document parser, vector store, or additional database is required for V1. PostgreSQL migration should be a separate, backed-up deployment change, not a side effect of UI cleanup.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure your database in .env before continuing.
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan v1:admin your-email@example.com
php artisan serve
```

The administrator command securely prompts for a name/password for a new account. For an existing account it asks for confirmation before granting access and does not change the password. Passwords are never accepted as command-line arguments. To revoke access, run `php artisan v1:admin your-email@example.com --revoke`.

There are **no built-in administrator passwords**. Existing customer accounts are retained but are not automatically administrators. Public registration is disabled. Sign in at `/admin/login`.

For design/demo data only:

```bash
php artisan db:seed --class=GrantOpportunitySeeder
```

The six sample opportunities and destination URLs come from Figma; they are **demo content, not independently verified funding opportunities**. Replace/verify them through the editor before a public launch. Seeding preserves edited titles and existing nonempty links, fills missing demo links, and does not create demo users.

## V1 workflows

- `/opportunities`: searchable, paginated catalogue with type, status, country, sector, and sort controls.
- `/opportunities/{slug}`: published grant detail. Draft/archived/deleted grants return 404.
- Closed and expired grants remain readable; application redirects are disabled. Blank deadlines mean rolling applications.
- `/waitlist`: validated, rate-limited signup with a bot honeypot. Email addresses are normalized and unique. Repeated submissions do not reveal or overwrite existing details.
- `/admin`: visitor/session totals, published opportunities, and waitlist totals.
- `/admin/opportunities`: create, edit, search, filter, publish/draft, and archive/restore. Slugs remain stable on edit. Deletion is recoverable.
- `/admin/analytics`: opportunity views, funder/learn-more/application clicks, and click-through rate.
- `/admin/waitlist`: staff-only, searchable, paginated waitlist records.

Grant creation/editing/archiving/restoration and CLI access changes are recorded in `admin_audit_logs`. Policies protect domain actions, middleware protects pages, and Livewire rechecks staff access on every request. Staff access does not expose the archived confidential workspace data.

### Opportunity editor

Use **Save as draft** to save an unfinished opportunity (only the title is required). Drafts are private; filter the opportunity list by **Drafts**, choose **Edit & publish**, complete the required fields, then select **Publish opportunity**. Published opportunities use **Save changes** and can be moved back to drafts with confirmation. The public open/closed status remains separate from draft visibility.

Summary and additional details use Tiptap with bold, italic, underline, section headings, bulleted/numbered lists, quotes, and undo/redo. Symfony HtmlSanitizer restricts stored and rendered HTML to supported formatting and safe links. Existing plain-text records remain readable. Limits are 2,000 visible characters for summary and 20,000 for additional details.

Images can use a public HTTPS URL or an uploaded JPG, PNG, or WebP (5 MB maximum, 6,000 pixels per side). Alt text is required when publishing with an image. Uploaded public marketing images use Laravel's `public` disk by default; run `storage:link` and configure `APP_URL` correctly. `OPPORTUNITY_IMAGES_DISK` can select another configured, publicly readable disk such as S3. Storage paths and disk names are server-owned. Replaced uploads are removed only after a successful save; soft-deleted opportunities keep their image for restoration. No private organisation documents are made public.

## Analytics

Analytics are persisted server-side, never in browser localStorage. A first-party, HTTP-only pseudonymous visitor cookie distinguishes returning browsers; a hashed session identifier deduplicates sessions. No IP address or user agent is stored in analytics.

Each opportunity/action is counted once per session/day. Click-through rate is the percentage of recorded views with at least one outbound action. Staff traffic, recognized bots, Do Not Track, and Global Privacy Control requests are excluded. Counts are approximate; cookie clearing and different browsers affect uniqueness. Outbound targets are read from the database and restricted to HTTP(S); request parameters cannot supply an arbitrary redirect URL.

Analytics write failures are logged without blocking access to grant pages or application links.

## Cleanup and recovery

The V1 migration renames `newsletter_subscriptions` to `waitlist_entries` without discarding data. Ten retired V2 tables are consolidated into `legacy_v2_archive`; each row is encrypted with the application key and retains its source table/id. The current local migration preserved **11 rows**, including the user workspace reference. No other database was touched.

Keep the existing `APP_KEY` and a database backup secure. Do not regenerate the key after data is archived; recovery requires it (or a correctly configured previous key).

Historical migrations are deliberately retained. The archive migration's `down()` recreates the old schemas and restores their rows and memberships; automated tests cover the round trip. Restore the matching old application source before reverting to V2 functionality. Do not run `migrate:fresh` against existing data.

A pre-cleanup source snapshot is available locally at:

`/Users/warihanagumah/Desktop/Grantnivo-before-v1-20260830.tar.gz`

It excludes `.env`, dependencies, generated builds, and storage. Existing secrets and storage were left untouched. The snapshot is a recovery artifact outside this project, not code served by the application.

## Verification

```bash
composer test
npm run test:frontend
npm run build
php artisan view:cache
```

Tests cover staff authorization (including revoked access), CRUD/auditing, recoverable deletion, stable slugs, URL validation, optional fields, draft visibility, expired grants, waitlist deduplication/rate limits, analytics/privacy exclusions, and encrypted archive restoration. Browser QA uses a separate temporary database so test users, signups, and edits never enter the live local catalogue.

## Before production

- Set `APP_DEBUG=false`, HTTPS, secure session cookies, and correct `APP_URL`.
- Use a restricted database user and encrypted backups; restrict database/archive access.
- Configure a real mail transport for password reset. Local `MAIL_MAILER=log` does not send email.
- Provision named staff accounts using the command above; review access regularly.
- Verify the demo grants, destination URLs, dates, imagery rights, and privacy notice.
- Run the production build; ensure a stale `public/hot` file does not point production visitors to a development server.
- Configure log/error monitoring and a retention policy appropriate for your deployment.
# Grantnivo
