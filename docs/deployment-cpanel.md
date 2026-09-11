# Deploying to cPanel

This covers shared hosting with cPanel, where there is no root access, no Nginx
configuration and no process supervisor. For a VPS with Nginx and systemd, see
[deployment.md](deployment.md) instead.

The deployment archive is built with `composer install --no-dev`, so it contains
production dependencies and the compiled front-end assets. **Node and Composer
are not needed on the server.**

---

## 1. What the archive contains, and what it does not

Included: application source, `vendor/`, `public/build/` (compiled CSS, JS and
self-hosted fonts), and empty writable directories.

Deliberately excluded:

- **`.env`** — never leaves the developer machine. You create it on the server.
- **`backup/`** — the legacy website archive. It contains the previous hosting
  account's plaintext database credentials and must never reach a web server.
- `tests/`, `node_modules/`, `.git/`, and development-only packages.

---

## 2. Server requirements

- PHP **8.3 or newer** with `pdo_mysql`, `mbstring`, `openssl`, `curl`,
  `fileinfo`, `gd`, `zip` and `intl`

  `composer.json` pins `config.platform.php` to `8.3.0`, so dependencies always
  resolve to versions that run on 8.3 regardless of the PHP version on the
  machine that builds the release. Without that pin, building on 8.4 silently
  locks Symfony 8, which requires PHP >= 8.4.1 and fails on an 8.3 host with
  *"Your Composer dependencies require a PHP version >= 8.4.1"*. If you later
  move to a host with 8.4, raise the pin deliberately rather than removing it.
- MySQL 8 or MariaDB 10.6+
- The ability to run cron jobs (cPanel → Cron Jobs)

Set the PHP version in cPanel → **MultiPHP Manager**, and enable the extensions
in **MultiPHP INI Editor** or **Select PHP Version → Extensions**.

---

## 3. Upload and placement

**The `public/` directory is the only part that may be web-accessible.**
Everything else — `.env`, `storage/`, `vendor/` — must sit above the document
root. There are two ways to arrange that.

### Option A — change the document root (preferred)

Best for an addon or subdomain, where cPanel lets you set the document root.

1. Upload the archive to `/home/<account>/` and extract it, giving
   `/home/<account>/gsf-website`.
2. In cPanel → **Domains**, set the document root to
   `/home/<account>/gsf-website/public`.

Nothing else needs editing.

### Option B — split into `public_html` (when the document root is fixed)

Typical for a primary domain whose document root cannot be moved.

1. Upload and extract to `/home/<account>/gsf-website`.
2. Move the *contents* of `gsf-website/public/` into `public_html/`.
3. Edit `public_html/index.php` and repoint the two `require` paths one level
   further up — replace `__DIR__.'/../'` with `__DIR__.'/../gsf-website/'` in
   both places.

Option A is less fragile: a future upload cannot accidentally overwrite the
adjusted `index.php`.

---

## 4. Create the environment file

Copy `.env.example` to `.env` and fill it in. The values that matter:

```dotenv
APP_NAME="Global Support Foundation"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://globalsupportfoundation.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=<cpanel_db>
DB_USERNAME=<cpanel_db_user>
DB_PASSWORD=<password>

MAIL_MAILER=smtp
MAIL_HOST=<smtp host>
MAIL_USERNAME=<mailbox>
MAIL_PASSWORD=<password>
MAIL_FROM_ADDRESS=info@globalsupportfoundation.org

CLOUDINARY_CLOUD_NAME=dt6xndtv
CLOUDINARY_API_KEY=<key>
CLOUDINARY_API_SECRET=<secret>
FOUNDATION_MEDIA_DRIVER=cloudinary

PAYSTACK_SECRET_KEY=<live key>
DONATIONS_ENABLED=true
```

Three of these are easy to get wrong:

- **`APP_DEBUG=false`.** With it on, a stack trace — including environment
  values — is shown to anyone who triggers an error.
- **`APP_URL`.** Canonical URLs, Open Graph tags, the sitemap and every signed
  link (donation receipts, newsletter confirmations) are built from it. A wrong
  value silently produces wrong canonicals and broken receipt links.
- **`FOUNDATION_MEDIA_DRIVER=cloudinary`.** Existing images already record their
  own disk and will resolve either way; this governs where *new* uploads go.
  Left as `public`, uploads land on local disk and are lost on the next deploy.

---

## 5. First-run commands

Run these from `/home/<account>/gsf-website` in cPanel → **Terminal**, or as
one-off cron jobs if Terminal is unavailable. Use the same PHP binary cPanel
serves the site with (often `/usr/local/bin/ea-php84`).

```bash
php artisan key:generate            # only once, on first deploy
php artisan migrate --force
php artisan db:seed --force         # first deploy only — seeds site content
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan gsf:create-administrator --email=you@example.org
```

`db:seed` is for the initial deploy only. Re-running it is safe — every seeder
uses `firstOrCreate` — but it will not overwrite content edited in the admin
panel, which is the intended behaviour.

The administrator password is prompted for, must be at least 12 characters with
letters, numbers and symbols, and is checked against known breach corpora, so
the server needs outbound HTTPS for that command.

---

## 6. Permissions

```bash
find storage bootstrap/cache -type d -exec chmod 755 {} \;
find storage bootstrap/cache -type f -exec chmod 644 {} \;
```

Do not use `777`. On shared hosting the web server runs as your own user, so
`755` is sufficient and `777` is a genuine risk.

---

## 7. Cron

cPanel has no process supervisor, so the queue is drained on a schedule rather
than held open by a long-running worker.

| Frequency | Command |
| --- | --- |
| Every minute | `cd /home/<account>/gsf-website && /usr/local/bin/ea-php84 artisan schedule:run >> /dev/null 2>&1` |
| Every 5 minutes | `cd /home/<account>/gsf-website && /usr/local/bin/ea-php84 artisan queue:work --stop-when-empty --tries=3 --max-time=280 >> /dev/null 2>&1` |

`--stop-when-empty` lets the process exit once the queue drains, and
`--max-time=280` keeps it inside the next run's window so two workers never
overlap. Donation receipts, enquiry acknowledgements and newsletter
confirmations are all queued, so without this cron **no email is ever sent**.

---

## 8. Content Security Policy

The application deliberately sets no CSP: the correct policy depends on the
built asset hashes and on which embeds are in use. If you add one in
`.htaccess`, it must allow the third parties this site actually uses:

```apache
Header always set Content-Security-Policy "default-src 'self'; img-src 'self' data: https://res.cloudinary.com; font-src 'self' data:; frame-src https://www.youtube.com https://www.google.com; connect-src 'self'; base-uri 'self'; form-action 'self' https://checkout.paystack.com; frame-ancestors 'self'"
```

Note `frame-src` includes **youtube.com** — the home page embeds four videos,
and a policy that omits it will blank them with no visible error. Start in
`Content-Security-Policy-Report-Only` mode and watch for violations before
enforcing.

---

## 9. Post-deploy checks

- `https://<domain>/` renders, and the header menus open — if they do not,
  `public/build` did not upload.
- `https://<domain>/sitemap.xml` lists your real domain, not `localhost`.
- `https://<domain>/robots.txt` points at that sitemap.
- View source on any page: `<link rel="canonical">` shows the live domain.
- Submit the contact form and confirm the acknowledgement arrives — this proves
  mail **and** the queue cron are both working.
- `https://<domain>/.env` returns 404, not the file. If it downloads, the
  document root is wrong: stop and fix Section 3 before going further.

---

## 10. Troubleshooting a 500

A 500 is deliberately opaque in production, so the first job is always to read
the real error rather than guess at it.

```bash
tail -n 50 storage/logs/laravel.log
```

If that file is empty or missing, the failure happened before Laravel could log
anything — look at **cPanel → Metrics → Errors**, or the `error_log` file in the
document root. As a last resort set `APP_DEBUG=true` in `.env`, reload the page
once, read the trace, then **set it straight back to `false`**: with it on, the
error page exposes environment values including database and API credentials.

The two causes below account for almost every 500 on a first deploy. Both were
reproduced against this codebase, so the symptoms are exact.

### `No application encryption key has been specified`

`APP_KEY` is empty. The archive ships no `.env`, so a freshly copied
`.env.example` has a blank key.

```bash
php artisan key:generate
php artisan config:clear
```

### `SQLSTATE[42S02]: Base table or view not found: ... sessions`

The database is reachable but the migrations have not run. This application
stores sessions, cache and the queue in the database, so the **`sessions` table
is touched on the very first request** — before any page content. Until the
schema exists, every URL returns 500, including the home page.

```bash
php artisan migrate --force
php artisan db:seed --force     # first deploy only
```

If the message names a different table — `settings`, `redirects`, `contents` —
the cause is the same: migrations are incomplete. Run `php artisan migrate:status`
to see what has and has not been applied.

### Other causes, in the order worth checking

1. **Permissions.** `storage/` and `bootstrap/cache/` must be writable by the
   account. Section 6 has the commands. A read-only `storage/` produces a 500
   with nothing in the Laravel log, because it cannot write the log either.
2. **A stale configuration cache.** If `php artisan config:cache` ran before
   `.env` was complete, the old values are still being served and editing
   `.env` changes nothing. Run `php artisan config:clear`, then re-cache.
3. **A missing PHP extension.** `php artisan about` fails loudly and names it.
   `intl` and `gd` are the two most often absent on shared hosting.
4. **The wrong document root.** If `https://<domain>/.env` downloads a file
   rather than returning 404, the document root points at the application
   directory instead of `public/`. Fix Section 3 before anything else — the
   environment file is being served to the public.
5. **A PHP version mismatch.** `Your Composer dependencies require a PHP
   version >= 8.4.1` means the release was built on a newer PHP than the server
   runs. See the note in Section 2.

---

## 11. Redeploying

For subsequent releases, upload over the application directory but leave
`.env`, `storage/` and `public/storage` alone, then:

```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Always re-run the cache commands: a stale `config:cache` keeps serving the
previous environment values, which is the most common cause of a deploy that
appears to have done nothing at all.
