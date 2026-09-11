# Platform architecture

## Application

Laravel 13 / PHP 8.4+, Livewire 4 (bundled Alpine), Filament 5, Tailwind 4 and TypeScript. MySQL or MariaDB in production; SQLite for isolated tests and local preview. Laravel database queues work without Redis; production may select Redis using environment configuration. Public rendering is server-side Blade with Livewire for interactive search and forms.

## Content and publication

Programs, projects, articles, pages, stories, awards, people, partners, publications and policies use an editorial record with shared publishing, provenance, SEO and media fields. Project-specific data is stored separately with explicit lifecycle, location, dates, outcomes, SDGs and relationships. Categories and tags provide taxonomy; media stores original file metadata and variants. Settings and menus are editable records. Impact metrics require a source, reporting year and approval before publication. Draft and scheduled content is excluded from public queries, search and sitemap.

## Financial boundary

Donations and payment events have separate tables from editorial content. Amounts use integer minor units. The application creates an unpredictable unique reference, initializes Paystack server-side and validates every confirmation against the stored amount, currency, reference and provider response. Signed webhooks and callback verification use one transactional settlement service with row locking. A unique event identity and terminal successful state prevent duplicate settlement. Receipts are queued after commit and accessible only through expiring signed URLs. Secret configuration never reaches browser assets.

## Access boundary

Super administrator manages users and permissions. Administrator manages editorial and operational records. Content manager publishes content; editor prepares drafts. Finance manages donations and reporting. Every CMS resource enforces policies independently of navigation visibility. Public forms use CSRF, rate limits, honeypots, explicit validation and duplicate handling. Uploaded files use approved MIME types and storage-generated names; published documents require editorial approval.

## Public information architecture

Home; About; History; Mission, Vision & Values; Founder; Leadership; Governance; Programs and program detail; Projects and project detail; Impact; Stories and story detail; Awards and award detail; Partners; News and article detail; Resources and publication detail; Policies; Transparency; Get Involved; Volunteer; Partnership; Donate; Contact; Search.

## Visual system

Logo-derived forest green primary, deeper green secondary, red accent, warm ivory background, white surfaces and charcoal text. Semantic success, warning and error tokens remain distinct. Large editorial headings, generous readable body text, restrained corners, strong image-led layouts and consistent spacing. Navigation collapses accessibly on mobile, focus is visible, reduced-motion is respected and images reserve dimensions.

## Integrations and operations

Paystack keys, allowed account currencies, mail transport, media storage, map configuration and optional analytics use environment configuration. Media storage is a driver abstraction rather than a Flysystem disk: `MEDIA_DRIVER=public` writes to a local disk for development, and `MEDIA_DRIVER=cloudinary` uploads through Cloudinary's signed REST API for CDN delivery, responsive width variants and automatic AVIF/WebP negotiation. Each media record stores the driver it was uploaded with, so changing the default never orphans existing files, and the Cloudinary API secret is only used to sign server-side requests. Queue workers process mail; scheduler supports publication and housekeeping. Deployment serves only `public/`, forces HTTPS, disables debug output and protects database backups and legacy source outside the public tree. Organization content approval and live payment credentials remain release requirements.
