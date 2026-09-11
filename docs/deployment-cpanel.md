# Deploying to cPanel

This covers shared hosting with cPanel, where there is no root access, no Nginx
configuration and no process supervisor. For a VPS with Nginx and systemd, see
[deployment.md](deployment.md) instead.

The deployment archive is built with `composer install --no-dev`, so it contains
production dependencies and the compiled front-end assets. **Node and Composer
are not needed on the server.**

---

## 1. What the archive contains, and what it does not

The archive is a **`.tar.gz`**, not a zip. That is deliberate: a zip stores no
Unix permission bits, and building one on Windows is what caused the unreadable
`vendor/` file behind a previous silent 500. See Section 6. cPanel's File
Manager extracts `.tar.gz` exactly as readily as `.zip`.

Included: application source, `vendor/`, `public/build/` (compiled CSS, JS and
self-hosted fonts), empty writable directories, and these helpers at the top
level:

| File | Purpose |
| --- | --- |
| `DEPLOY-CPANEL.md` | this guide |
| `production.env.template` | the environment file to copy and fill in |
| `set-permissions.sh` | applies production permissions (shell) |
| `fix-permissions.php` | the same, for accounts without shell — **delete after use** |
| `php-check.php` | diagnostic for a failed deployment — **delete after use** |

Deliberately excluded:

- **`.env`** — never leaves the developer machine. You create it on the server.
- **`backup/`** — the legacy website archive. It contains the previous hosting
  account's plaintext database credentials and must never reach a web server.
- **`bootstrap/cache/`** contents — a config or route cache built on the build
  machine records that machine's absolute paths. The server builds its own in
  Section 5.
- `tests/`, `node_modules/`, `.git/`, and development-only packages.

Rebuild it at any time with:

```bash
bash tools/build-deployment-archive.sh
```

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
3. Edit `public_html/index.php` and repoint **all three** `__DIR__.'/../'`
   references at the application directory. They are the maintenance check, the
   autoloader, and `bootstrap/app.php`:

   ```php
   if (file_exists($maintenance = '/home/<account>/gsf-website/storage/framework/maintenance.php')) {
   require '/home/<account>/gsf-website/vendor/autoload.php';
   $app = require_once '/home/<account>/gsf-website/bootstrap/app.php';
   ```

   Three, not two — missing the maintenance line will not break the site, but
   missing either of the other two produces a 500 with nothing in the log.
   `php-check.php` reads these paths back and reports whether they resolve.

Option A is less fragile: a future upload cannot accidentally overwrite the
adjusted `index.php`.

---

## 4. Create the environment file

Copy the template the archive ships — it is already filled in for production,
carries no secrets, and every key in it is verified against `config/`:

```bash
cp production.env.template .env
chmod 600 .env
php artisan key:generate
```

Then fill in the values marked `FILL IN`. The ones that matter:

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
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_FROM_ADDRESS=info@globalsupportfoundation.org
ADMIN_NOTIFICATION_EMAIL=info@globalsupportfoundation.org

CLOUDINARY_CLOUD_NAME=dt6xndtv
CLOUDINARY_API_KEY=<key>
CLOUDINARY_API_SECRET=<secret>
MEDIA_DRIVER=cloudinary

PAYSTACK_SECRET_KEY=<live key>
DONATIONS_ENABLED=true
```

Four of these are easy to get wrong:

- **`APP_DEBUG=false`.** With it on, a stack trace — including environment
  values — is shown to anyone who triggers an error.
- **`APP_URL`.** Canonical URLs, Open Graph tags, the sitemap and every signed
  link (donation receipts, newsletter confirmations) are built from it. A wrong
  value silently produces wrong canonicals and broken receipt links.
- **`MEDIA_DRIVER=cloudinary`.** Existing images already record their
  own disk and will resolve either way; this governs where *new* uploads go.
  Left as `public`, uploads land on local disk and are lost on the next deploy.
- **`MAIL_SCHEME=smtps`**, not `MAIL_ENCRYPTION`. This Laravel version ignores
  `MAIL_ENCRYPTION` entirely, so a mailbox on port 465 configured with it fails
  to connect and no acknowledgement, receipt or newsletter confirmation is ever
  delivered. For STARTTLS on port 587, use `MAIL_PORT=587` and leave
  `MAIL_SCHEME` empty.

There is no cookie-consent setting. Consent is handled entirely in the browser
and stored in the visitor's own cookie; nothing needs configuring for it, and
turning analytics on or off is simply a matter of whether `PLAUSIBLE_DOMAIN` is
set.

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

On cPanel, PHP and Apache both run as the account's own user. The owner
therefore needs write access and nobody else needs any:

| Path | Mode | Meaning |
| --- | --- | --- |
| Directories | `755` | owner writes; others traverse and read |
| Files | `644` | owner writes; others read |
| `artisan`, `vendor/bin/*` | `755` | executable |
| `.env` | `600` | owner only — nothing else may read the secrets |

`storage/` and `bootstrap/cache/` need nothing special. At `755` the owner is
already the user PHP runs as, so the application can write to them.

**Never use `777`.** It grants write access to every account on a shared server.
It is not required here, and nothing in this deployment asks for it.

### Applying them

The archive ships two tools. Use whichever your account supports.

**With shell access** (cPanel → Terminal, or SSH):

```bash
cd /home/<account>/gsf-website
bash set-permissions.sh
```

For the split layout in Option B, pass the document root as a second argument so
the served files are covered too:

```bash
bash set-permissions.sh /home/<account>/gsf-website /home/<account>/public_html
```

**Without shell access:** upload `fix-permissions.php` next to `index.php` in
the document root, open `https://<domain>/fix-permissions.php` in a browser, and
**delete it as soon as it has run**. It does the same work and only ever
tightens: it never sets `777`, and it refuses to touch anything outside the
application it finds.

Both finish by verifying the two failures that actually take the site down — a
`storage/` directory the application cannot write to, and a file under `vendor/`
it cannot read.

### Why this keeps going wrong

The releases before this one were `.zip` files built on Windows, and **a zip
carries no Unix permission bits**. Extracting one on Linux leaves every file at
whatever the extractor happens to choose, which is how a previous deployment
ended up with an unreadable file under `vendor/`: an empty 500 with nothing in
any Laravel log, because the failure happens before Laravel can register an
error handler.

The archive is now a `.tar.gz`, which stores the mode of every entry, and
`tools/build-deployment-archive.sh` normalises them as it writes — `755` on
directories, `644` on files, and it refuses to produce an archive containing
anything group- or world-writable. A correctly extracted release therefore
arrives with the right permissions and needs no repair. Run `set-permissions.sh`
anyway after any upload that went through cPanel's File Manager, which can still
reset modes on the files it replaces.

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
and a policy that omits it will blank them with no visible error. Those frames
and the contact-page map are held behind cookie consent, so they stay inert
until the visitor allows them; the policy still has to permit them for the
moment they are allowed. If analytics is configured, add its host to
`script-src` and `connect-src` as well.

Start in `Content-Security-Policy-Report-Only` mode and watch for violations
before enforcing.

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
- The cookie banner appears on the first visit. Choose **Reject non-essential**,
  then open the home page's Featured Videos section: the players must still show
  the "YouTube content is blocked" placeholder. If a video plays, the consent
  gate is not working and the site is setting third-party cookies without
  permission.
- Choose **Accept all**, reload, and confirm the banner does **not** reappear and
  the videos now load.
- `https://<domain>/cookie-policy` renders, and **Cookie settings** in the footer
  reopens the preferences dialog.
- `https://<domain>/php-check.php` and `/fix-permissions.php` both return 404.
  If either still responds, delete it now — they are deployment tools and must
  not stay online.

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
5. **A PHP version mismatch.** Two different messages, two different causes:

   - *`... require a PHP version >= 8.4.1`* — the release was built on a newer
     PHP than the server runs. Rebuild with `config.platform.php` pinned, as
     described in Section 2.
   - *`... require a PHP version >= 8.3.0`* — the server itself is below 8.3.
     **This cannot be worked around.** `laravel/framework` v13 requires
     `php ^8.3`, so there is no set of dependency versions that runs on 8.2.
     The PHP version has to be raised. See Section 11.

---

## 11. When the server is running PHP older than 8.3

`php-check.php` ships alongside the archive. Upload it to the document root,
open it in a browser, and it reports the PHP version the **web server** is
actually using, which extensions are missing, **where the application files
actually are**, and the last error from the Laravel log. **Delete it once you
are done** — it is a diagnostic, not part of the application.

If its Layout section says *"The application was not found"*, that is the cause
of the 500: `index.php` loads `vendor/autoload.php` and `bootstrap/app.php`
from one level above the document root, and they are not there. See Section 3.

If it reports a version below 8.3:

1. **cPanel → MultiPHP Manager.** Tick the domain, choose PHP 8.3 or newer from
   the dropdown, and click Apply. Reload `php-check.php`.
2. **If the version has not changed**, something is overriding MultiPHP. The
   usual culprit is a handler line in `.htaccess` — in the document root, or in
   a parent directory:

   ```apache
   AddHandler application/x-httpd-ea-php82 .php
   ```

   Remove or update that line. cPanel sometimes writes it when the PHP version
   is changed through an older interface.
3. **If 8.3 is not offered at all**, the hosting plan's EasyApache build does
   not include it and only the host can add it. Ask them to install
   `ea-php83`; it is a standard package and the request is routine.

### The command line is a separate setting

cPanel selects the web and CLI PHP versions independently. `php -v` over SSH
commonly reports an older build than the site is served with, which means
`php artisan migrate` can fail with this same message while the site itself is
fine. Call the versioned binary explicitly:

```bash
/usr/local/bin/ea-php83 artisan migrate --force
```

Use that same binary in the cron entries in Section 7.

---

## 12. Redeploying

**Never replace these.** They hold state the archive does not carry:

| Keep | Why |
| --- | --- |
| `.env` | your credentials; the archive has none |
| `storage/` | logs, sessions, cache, and any locally uploaded media |
| `public/storage` | the symlink into `storage/app/public` |
| The database | content edited in the admin panel lives only there |

Everything else in the archive replaces its counterpart. If you would rather not
merge by hand, extract the release beside the live application and swap the
directories, carrying the four rows above across first.

```bash
cd /home/<account>
tar -xzf gsf-website-<date>.tar.gz          # extracts to gsf-website/
cp  gsf-website-live/.env         gsf-website/.env
cp -r gsf-website-live/storage/app/public/. gsf-website/storage/app/public/
mv  gsf-website-live gsf-website-previous   # the rollback copy
mv  gsf-website      gsf-website-live
cd  gsf-website-live
bash set-permissions.sh
php artisan storage:link
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Always re-run the cache commands: a stale `config:cache` keeps serving the
previous environment values, which is the most common cause of a deploy that
appears to have done nothing at all.

### Rolling back

Keep `gsf-website-previous` until the new release has been checked. To go back:

```bash
cd /home/<account>
mv gsf-website-live     gsf-website-failed
mv gsf-website-previous gsf-website-live
cd gsf-website-live && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

The document root does not change, so nothing in cPanel needs touching. Note
that **migrations are not undone by this** — if the release added a migration,
roll that back first with `php artisan migrate:rollback --step=1` while the new
release is still in place, or restore the database from the backup you took
before deploying.
