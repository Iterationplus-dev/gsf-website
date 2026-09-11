# Deployment

Target: a VPS running Linux, Nginx and PHP-FPM, with MySQL or MariaDB. The same steps apply on any host that can run PHP-FPM behind a web server and keep a queue worker alive.

---

## 1. Server requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `zip`, `intl`
- Composer 2
- Node.js 20+ (for building assets; not needed at runtime)
- MySQL 8 or MariaDB 10.6+
- Nginx (or Apache) with a valid TLS certificate
- A process supervisor (`systemd` or `supervisor`) for the queue worker
- Cron, for the scheduler

Redis is optional. The application runs correctly on database-backed cache, sessions and queues; if Redis is available, set `CACHE_STORE=redis`, `SESSION_DRIVER=redis` and `QUEUE_CONNECTION=redis`.

---

## 2. Document root

**Serve `public/` only.** Everything above it — `.env`, `storage/`, `vendor/`, and the legacy `backup/` directory — must be unreachable over HTTP.

The `backup/` directory contains the previous website's PHP source and its embedded configuration. It must never be inside the document root and must never be executed. It exists as a content archive, nothing more.

---

## 3. Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name globalsupportfoundation.org www.globalsupportfoundation.org;

    root /var/www/gsf-website/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/globalsupportfoundation.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/globalsupportfoundation.org/privkey.pem;

    charset utf-8;
    client_max_body_size 24M;   # must exceed the 20 MB media upload limit

    # A Content Security Policy is applied here rather than in the application,
    # because the exact script and style set depends on the built assets.
    # Start in report-only mode, watch the reports, then enforce.
    add_header Content-Security-Policy "default-src 'self'; img-src 'self' data: https://res.cloudinary.com; font-src 'self' data:; frame-src https://www.google.com; connect-src 'self'; base-uri 'self'; form-action 'self' https://checkout.paystack.com; frame-ancestors 'self'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Built assets are content-hashed, so they can be cached indefinitely.
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.(?!well-known) { deny all; }

    error_page 404 /index.php;
}

server {
    listen 80;
    server_name globalsupportfoundation.org www.globalsupportfoundation.org;
    return 301 https://$host$request_uri;
}
```

`Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` and `Permissions-Policy` are applied by the application's `SecurityHeaders` middleware, so they need not be duplicated here.

If the site sits behind Cloudflare or a load balancer, the application already trusts proxy headers so that HTTPS detection, signed URLs and rate limiting see the real client request.

---

## 4. First deployment

```bash
cd /var/www/gsf-website

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env      # then fill it in — see the README
php artisan key:generate

php artisan migrate --force
php artisan db:seed --force        # first deployment only
php artisan storage:link

GSF_ADMIN_PASSWORD='...' php artisan gsf:create-administrator \
    --name="..." --email="..." --role=super_admin --no-interaction

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Permissions

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rw storage bootstrap/cache
chmod 640 .env && chown root:www-data .env
```

---

## 5. Subsequent deployments

```bash
php artisan down --render="errors::503"

git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan queue:restart     # workers must reload the new code
php artisan up
```

`queue:restart` is not optional. Without it, workers keep running the previous release's code and will send receipts using stale templates.

---

## 6. Queue worker

Receipts, acknowledgements and administrative notifications are all queued. **If no worker is running, donors do not get receipts.**

`/etc/systemd/system/gsf-queue.service`:

```ini
[Unit]
Description=GSF queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/gsf-website/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable --now gsf-queue
systemctl status gsf-queue
```

Run two workers on a busy site. Monitor `failed_jobs`: a receipt sitting there means a donor was charged and never acknowledged.

---

## 7. Scheduler

```cron
* * * * * cd /var/www/gsf-website && php artisan schedule:run >> /dev/null 2>&1
```

One entry, every minute. Laravel dispatches from there.

Scheduled content needs no job: the public queries compare `published_at` against the current time, so an article scheduled for Friday appears on Friday whether or not anything ran.

---

## 8. Backups

Three things need backing up, and they are not equally replaceable.

| What | Why | Frequency |
|---|---|---|
| **Database** | Donations, enquiries, subscribers and all content. Irreplaceable. | Nightly, retained 30 days, plus a monthly copy kept off-site |
| **Cloudinary media** | Recoverable from Cloudinary, but export periodically so you are not dependent on one provider | Monthly |
| **`.env`** | Secrets. Store in a password manager, **not** in the repository or the backup bucket | On change |

```bash
mysqldump --single-transaction --quick --routines gsf \
  | gzip > /var/backups/gsf-$(date +%F).sql.gz
```

**Test a restore.** A backup nobody has restored is a hypothesis.

---

## 9. Monitoring

- **Uptime** — check `/up`, which reports application health.
- **Errors** — set up Sentry, Bugsnag or Flare. Payment settlement failures are reported through `report()` and must reach a human.
- **Logs** — `storage/logs` with `LOG_CHANNEL=daily`; rotate and ship them off the box.
- **Queue** — alert on `failed_jobs` growing.
- **Certificate expiry** — Certbot renews automatically, but alert if it stops.

Watch specifically for donations stuck in `pending` for more than an hour. That means a payment started but was never confirmed by either the callback or the webhook, and it needs looking at.

---

## 10. Going live checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` set to the real domain
- [ ] `.env` not readable over HTTP and not in version control
- [ ] `backup/` outside the document root
- [ ] TLS working; HTTP redirects to HTTPS
- [ ] Cloudinary credentials set and `MEDIA_DRIVER=cloudinary`
- [ ] Paystack **live** keys set and `PAYSTACK_CURRENCIES` matching the account
- [ ] Paystack webhook registered at `/donate/webhook` and delivering
- [ ] A test donation completed end to end, with a receipt received
- [ ] Mail provider verified (SPF, DKIM, DMARC) so receipts are not filtered as spam
- [ ] Queue worker running under a supervisor
- [ ] Scheduler cron entry in place
- [ ] Database backups running **and a restore tested**
- [ ] Error monitoring reporting
- [ ] Administrator accounts created; no shared logins
- [ ] `/sitemap.xml` and `/robots.txt` returning the production domain
- [ ] Google Search Console verified and the sitemap submitted
- [ ] Legacy URLs redirecting (`/our-team`, `/partnership`, `/support-us`, `/testimonies`, `/gallery`, `/form`)

### Content sign-off — before announcing the site

- [ ] Founder's title confirmed (the organisational record says **Executive Director**)
- [ ] Award titles and awarding institutions transcribed from the certificate scans
- [ ] Board and trustee list supplied, so the governance page can be published
- [ ] Registration number and authority supplied
- [ ] Policies drafted, board-approved and dated before publication
- [ ] Impact figures supplied with their sources
- [ ] Demo projects either replaced with verified records or deleted
- [ ] Media reviewed, described with alt text, and consent confirmed for images of identifiable people
- [ ] Organisational social media accounts supplied (the legacy site linked a personal profile)
