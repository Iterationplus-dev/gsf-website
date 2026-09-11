# Global Support Foundation

The website of **Global Support Foundation for Grassroot Entrepreneurship (GSF)** — a Nigerian NGO, established August 2015, that helps young people build co-operative enterprises through training, business advice, consultancy and advocacy.

Built on the TALL stack: **T**ailwind CSS 4, **A**lpine.js, **L**aravel 13, **L**ivewire 4, with Filament 5 for administration, TypeScript for custom client-side code, and Cloudinary for media delivery.

---

## Requirements

| | |
|---|---|
| PHP | 8.3 or later (developed on 8.4) |
| Composer | 2.x |
| Node.js | 20 or later |
| Database | MySQL 8 / MariaDB 10.6+ in production; SQLite for local work and tests |
| Extensions | `pdo`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd` (or `imagick`) |

---

## Installation

```bash
git clone <repository> gsf-website
cd gsf-website

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan storage:link

npm run build
```

Then create your first administrator (see [Administrator accounts](#administrator-accounts)) and sign in at `/admin`.

### Development

```bash
composer run dev     # server, queue worker, log tail and Vite together
```

or individually:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

---

## Environment configuration

Everything below is set in `.env`. **Never commit `.env`, and never place a secret in `config/` or in front-end code.**

### Application

```dotenv
APP_NAME="Global Support Foundation"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://globalsupportfoundation.org
```

`APP_URL` must be correct in production: signed donation links, the sitemap, `robots.txt` and canonical URLs are all derived from it.

### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gsf
DB_USERNAME=gsf
DB_PASSWORD=
```

### Media storage — Cloudinary

Media is a **driver abstraction, not a Flysystem disk**. `MEDIA_DRIVER` selects where uploads go:

```dotenv
MEDIA_DRIVER=cloudinary
CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_FOLDER=gsf
CLOUDINARY_SIGNATURE_ALGORITHM=sha1
```

- `MEDIA_DRIVER=public` writes to `storage/app/public` and serves originals unmodified. Fine for development; not intended for production.
- `MEDIA_DRIVER=cloudinary` uploads through Cloudinary's signed REST API and delivers through their CDN with automatic AVIF/WebP negotiation and per-width variants.

Set `CLOUDINARY_SIGNATURE_ALGORITHM=sha256` **only** if your Cloudinary account is configured for SHA-256 signing. Accounts default to SHA-1.

Each media record stores the driver that uploaded it, so **changing `MEDIA_DRIVER` never orphans existing files** — files uploaded under the old driver keep resolving. The API secret is used only to sign server-side requests and never reaches the browser.

### Donations — Paystack

```dotenv
DONATIONS_ENABLED=true
PAYSTACK_SECRET_KEY=sk_live_...
PAYSTACK_CURRENCIES=NGN
```

`PAYSTACK_CURRENCIES` is a comma-separated list, and must match what your Paystack account actually accepts — a currency listed here that the account rejects will fail at checkout.

While `DONATIONS_ENABLED=false` or the secret key is empty, the Donate page shows a "not yet available" notice and the submission endpoint returns 503. Nothing half-configured can take money.

**Register the webhook** in your Paystack dashboard, pointing at:

```
https://your-domain/donate/webhook
```

The endpoint verifies the `x-paystack-signature` HMAC on every request. An unsigned or wrongly signed request is rejected with 401 and changes nothing.

### Email

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="info@globalsupportfoundation.org"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NOTIFICATION_EMAIL=office@globalsupportfoundation.org
NEWSLETTER_DOUBLE_OPT_IN=true
```

Use a reputable transactional provider (Postmark, Mailgun, SES, Resend). Donation receipts are queued, so **a queue worker must be running** or receipts will not be delivered.

### Google Maps

```dotenv
GOOGLE_MAPS_ENABLED=true
GOOGLE_MAPS_API_KEY=
```

The contact page map is built from the office address stored in **Settings**, never from hard-coded coordinates. Without a key, the page shows the address and a link out to Google Maps instead of a broken embed. Restrict the API key to the Maps Embed API and to your domain.

### Analytics (optional)

```dotenv
PLAUSIBLE_DOMAIN=globalsupportfoundation.org
```

Privacy-conscious by design: no cookies, and **no donor or enquirer details are ever sent to analytics**. Leave `PLAUSIBLE_DOMAIN` empty to load no analytics script at all.

---

## Administrator accounts

No account is ever seeded, so no installation ships with a known password.

**Interactively** (the password is prompted for, so it stays out of shell history):

```bash
php artisan gsf:create-administrator
```

**For automated provisioning**, pass the password in the environment rather than as an argument:

```bash
GSF_ADMIN_PASSWORD='...' php artisan gsf:create-administrator \
    --name="Jane Doe" --email="jane@example.org" --role=super_admin --no-interaction
```

Passwords must be at least 12 characters with letters, numbers and symbols, and are checked against known-breached password lists.

Running the command again for an existing email updates that account.

---

## Roles and permissions

| Role | Can do |
|---|---|
| **Super administrator** | Everything, including managing accounts |
| **Administrator** | All content, media, operations and settings |
| **Content manager** | Create, edit **and publish** content; manage media |
| **Editor** | Create and edit content as drafts; manage media. **Cannot publish** |
| **Donation manager** | View, filter, export donations and resend receipts. No content access |

Individual permissions can be granted on top of a role; they never subtract from it. To remove access, change the role or deactivate the account.

**Deactivating an account revokes access immediately**, whatever role it holds. Prefer deactivation over deletion so the audit trail stays attributable.

Optional per-account two-factor authentication (authenticator app, with recovery codes) is available under the account profile.

---

## Media

Upload files under **Content → Media**. Every upload arrives **unapproved and without alt text**, and unapproved media is never rendered publicly. Someone has to look at each file and describe what it shows before it can be used.

To bring the legacy website's photographs and certificate scans into the library:

```bash
php artisan gsf:import-legacy-media --dry-run   # see what would be imported
php artisan gsf:import-legacy-media
```

This reads `backup/assets/images`, skips known theme artwork, and imports the rest as unapproved records for review. It uses whichever `MEDIA_DRIVER` is configured, so run it after Cloudinary credentials are in place if you want the files there.

---

## Testing

```bash
php artisan test                              # everything
php artisan test --compact                    # condensed output
php artisan test tests/Feature/DonationTest.php
php artisan test --filter=test_a_replayed_webhook_settles_only_once
```

Tests run against an in-memory SQLite database and never contact Paystack or Cloudinary — both are faked at the HTTP client.

Coverage focuses on the things that would actually hurt: draft content leaking to the public site, payment settlement, webhook signature validation and replay, permission boundaries, form idempotency, and media storage.

---

## Code style

```bash
vendor/bin/pint --dirty --format agent   # format what you changed
npx tsc --noEmit                         # type-check the TypeScript
```

---

## Deployment

See **[docs/deployment.md](docs/deployment.md)** for server requirements, web server configuration, queue and scheduler setup, backups and monitoring.

## Managing the website

See **[docs/administration.md](docs/administration.md)** for the content management guide — pages, programs, projects, news, media, impact figures, donations, awards, policies and settings.

## Background

- **[docs/architecture.md](docs/architecture.md)** — how the platform is put together and why.
- **[docs/content-audit.md](docs/content-audit.md)** — what was migrated from the previous website, what could not be verified, and what still needs organisational sign-off.

---

## A standing rule

This website exists to be shown to donors, development partners and government agencies. Its credibility depends on nothing on it being invented.

Content that cannot be verified stays a **draft**. Demo records are flagged and can never be published while that flag is set. Impact figures require a recorded source. Awards stay unpublished until their titles and awarding institutions are transcribed and confirmed. No organisation appears as a partner without an agreement.

`docs/content-audit.md` lists what is still outstanding. Please keep it current.
