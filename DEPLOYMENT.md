# Deploy Grantnivo V1

Deploy from GitHub into a new release directory, using a reviewed commit. Keep production secrets in the host environment and user data in persistent storage; neither belongs in the repository. No deployment ZIP is required.

## Requirements

- PHP 8.4.1+ with cURL, DOM/XML, Fileinfo, Mbstring, OpenSSL, PDO and the database driver. Enable GD in development/CI for image tests. Enable OPcache on the server.
- Composer 2 and a supported MySQL database. Use a dedicated application database user.
- HTTPS and a web server with its document root set to `public/`, never the project root.
- Writable `storage/` and `bootstrap/cache/`, with permissions limited to the deployment and web-server users. Do not use `chmod 777`.
- Cloudflare R2 for new opportunity images; follow [R2_SETUP.md](R2_SETUP.md). Preserve local public storage for any images whose database records still use the `public` disk. Back up media, the database and the existing application key.
- Outbound DNS and HTTPS access for downloading images and checking production passwords. Private-network image URLs are deliberately rejected.
- PHP `upload_max_filesize` of at least `5M`, `post_max_size` greater than that (for example `8M`), and matching web-server request limits. Large CSV imports may need longer request timeouts: up to 250 records can each download an image.

Build assets in the hosting build step or CI using Node 22.13+ and pnpm 11.19.0. Deploy the generated `public/build` directory with the PHP source; a Git checkout alone does not include compiled assets.

## Check the release before deploying

```bash
composer install --prefer-dist --no-interaction
pnpm install --frozen-lockfile
composer test
pnpm run test:frontend
pnpm run build
```

Have your hosting provider check out the chosen GitHub commit into a new release directory. Build with `pnpm install --frozen-lockfile` and `pnpm run build`, or supply assets built in CI. Do not overlay a running release or overwrite persistent storage.

## Configure the server

For a new installation, copy `.env.production.example` to `.env` and replace every `CHANGE_ME` value with your real domain, database and mail settings. Adjust SMTP scheme/port to your provider (the example uses implicit TLS on port 465).

For an existing installation, preserve its `.env`, `APP_KEY`, database and storage. Set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, and `SESSION_SECURE_COOKIE=true`. Do not run `key:generate` against an existing database: encrypted archive data and cookies depend on the key. Generate a key only for a new, empty installation.

Do not copy the development `.env` or `public/hot` to the server. Do not run `composer setup` on the server; it generates a key and is intended for initial local setup.

Before activating a release, connect it to persistent storage and the production environment. Then run from the release directory:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
# Only for a brand-new installation with an empty database:
# php artisan key:generate --force

# Back up the existing database, APP_KEY and image storage before this step.
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Create a named staff account interactively if needed:

```bash
php artisan v1:admin your-email@example.com
```

No sample data or administrator accounts are added by the release process. Do not run the demo seeder on production. Never use `migrate:fresh`, `db:wipe` or blind migration rollback on existing data. Historical migrations must remain: they preserve and encrypt retired workspace records.

For a single-directory deployment, put the app in maintenance mode before replacing code and migrating, then run `php artisan up` only after the checks below succeed. For atomic releases, activate the prepared directory through your deployment system. Restart PHP-FPM/OPcache after activation as appropriate. Restart any configured queue workers (`php artisan queue:restart`); V1 currently has no scheduled console tasks.

Behind a load balancer or reverse proxy, configure Laravel to trust only that infrastructure's addresses before enabling forwarded HTTPS/IP headers. Never trust arbitrary forwarded headers from public clients. The exact configuration depends on your host.

## Verify before opening traffic

- `/up` responds successfully; remember this is an application boot check, not a complete database/mail test.
- Home, Opportunities, About, and Waitlist load without a development server; browser requests use `/build/assets/`.
- `/admin/login` works over HTTPS, and staff can create a private draft, download/upload its image, preview it, publish it, and restore an archived opportunity.
- Guests cannot access admin or draft preview URLs; a draft's public URL returns 404.
- A waitlist signup is saved once; repeating the email shows the inline duplicate message.
- Password reset mail reaches a designated test staff account. `MAIL_MAILER=log` does not send email.
- New images load from the R2 custom domain across releases. Existing local `/storage/opportunities/...` images remain available if retained. Private storage is not publicly accessible.
- Review opportunity content, deadlines, outbound links, image rights and the privacy notice before launch.

If activation fails, keep traffic on the previous release. Restore a database backup only through a planned recovery procedure; switching code back does not reverse migrations. Retain the original application key and compatible source for encrypted archive recovery.

## Policy launch checks

Before accepting public signups, complete [POLICY_REVIEW.md](POLICY_REVIEW.md). Configure `LEGAL_OPERATOR`, `LEGAL_COUNTRY`, `LEGAL_ADDRESS`, and a monitored `PRIVACY_CONTACT_EMAIL`. Set `LEGAL_POLICIES_REVIEWED=true` only after an appropriate review of the final wording and actual data practices; this flag is an operator acknowledgement, not a compliance certification. Rebuild the config cache after changing these values.

The new nullable consent columns are applied by the normal migration command. Existing records remain unchanged; do not backfill consent that was never obtained. Test an unchecked checkbox (no request), a valid new signup (versioned acceptance), and a duplicate email (no overwritten details). Optional analytics stays off until enabled on `/cookies`; confirm declining removes the visitor cookie and prevents further events. Metrics therefore describe consenting visitors only and are not directly comparable with earlier traffic totals.
