# Agares CMS

A modern, multi-site content management system built with Laravel 13 — with a full ecommerce module, two-factor authentication, AI-assisted SEO, REST API, and per-site role-based access control.

## Features

### Content & Sites
- **Multi-Site Management** — Manage multiple websites from a single admin dashboard
- **Hierarchical Content** — Sites → Categories → Articles with soft-delete and scheduled publishing
- **Publishing Workflow** — `draft`, `scheduled`, `published`, `private` states with public-content query scope
- **Custom Fields (Input System)** — Polymorphic templates (text, textarea, gallery, file, form, …) attachable to any content type via `InputInstance`
- **Dynamic Forms** — Build custom forms with configurable fields; submissions stored per-form
- **Media Library** — Centralised file and image management with galleries and MIME-type allowlisting
- **Menus** — Recursive menu builder with caching (`MenuTree` service)
- **Custom Code Injection** — Per-site CSS/JS injection; Monaco-editor-powered admin UI

### Ecommerce (feature-gated)
- **Products with variants, categories, tags, attributes**
- **Orders** with status history and admin status updates
- **4 payment gateways** — Stripe (Checkout Sessions), PayU (OAuth2), Przelewy24 (SHA384-signed), PayPal (Orders API v2), plus COD
- **Signature-verified webhooks** for every gateway; idempotent capture/fail transitions
- **Guest checkout** with optional account creation at checkout
- **Coupons, shipping methods, tax rules**
- **Transactional emails** — order confirmation, status change, new-order admin alert

### Security & Access Control
- **Two-Factor Authentication** — TOTP (authenticator app) + email-OTP fallback + one-time recovery codes
- **2FA enforcement** — globally, per-role (CSV), or per-user; force-setup middleware
- **OAuth + 2FA coverage** — Google and Facebook callbacks route through 2FA challenge when enrolled
- **Security audit log** — `security_audit_log` table records 2FA events (enrolment, disable, admin-reset, challenges, recovery-code use); visible on user profile
- **Role-Based Access Control** — Spatie permissions with `view X` / `manage X` convention; site-scoped overrides via `RoleSitePermission`
- **Defense-in-depth authorization** — Route middleware + per-controller `HasMiddleware` gates
- **Roles** — `owner` (full access via `Gate::before`), `admin`, `moderator`, `viewer` (read-only demo mode), `user`, `customer`
- **REST API** — scoped API key authentication (`X-API-Key` header)
- **HTML sanitisation** — `safe_html()` / `safe_label()` helpers strip event handlers and `javascript:`/`data:` URIs from admin-authored rich text

### Other
- **AI SEO** — Generate SEO metadata for articles, products, and categories via the Agares SaaS; side-by-side diff with per-field accept
- **Cookie Consent** — GDPR-ready consent management with cookie scanner integration
- **Newsletter** — Subscribers and lists with GDPR consent capture, token-based unsubscribe, campaign drafts and templates, external-API delegation for bulk send (queue-free CMS)
- **Settings-based feature flags** — `EnsureSetting` middleware gates routes by key/value settings
- **Google Analytics** — GA4 integration (summary, traffic timeline, realtime active users) with 30-min / 10-s caching
- **Social Authentication** — Login with Google or Facebook
- **QR Generator** — Admin tool for generating QR codes
- **Maintenance Mode** — Per-site maintenance guard

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL 8.0 |
| Frontend | Tailwind CSS 3, Alpine.js 3, Bootstrap 5.3, Bootstrap Icons, Sass |
| Editors | Monaco Editor (custom code), TinyMCE (rich text) |
| Build | Vite 5, Axios 1.6 |
| Auth | Laravel Breeze, Socialite, `pragmarx/google2fa`, `bacon/bacon-qr-code` |
| Permissions | Spatie Laravel Permission |
| Payments | `stripe/stripe-php`, native PayU / P24 / PayPal HTTP clients |

## Requirements

- PHP 8.3+
- MySQL 8.0+
- Node.js 20+
- Composer

## Installation

### Using Docker (Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd agares-cms

# Copy environment file
cp .env.example .env

# Start containers
docker-compose up -d

# Install PHP dependencies (inside container)
docker exec -it agares composer install

# Generate application key
docker exec -it agares php artisan key:generate

# Run migrations + seed roles/permissions + settings
docker exec -it agares php artisan migrate
docker exec -it agares php artisan db:seed

# Build frontend assets
docker exec -it agares npm install
docker exec -it agares npm run build
```

The application will be available at:
- **App**: http://localhost:8006
- **phpMyAdmin**: http://localhost:8086

### Manual Installation

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then:
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
npm run dev
```

## Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="Agares CMS"
APP_URL=http://localhost:8006

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=agares
DB_USERNAME=user
DB_PASSWORD=password

# OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

# Google Analytics (optional)
GA4_PROPERTY_ID=
GA4_CREDENTIALS_PATH=
```

Most behaviour is configured at runtime through the admin **Settings** page (key-value table), not the `.env` file — this includes feature flags (`enable_ecommerce`, `enable_api`, `enable_newsletter`, `2FA_enabled`, `2FA_required`, `2FA_method`, …), payment provider credentials, AI SEO config, and brand metadata.

## Development

### Commands

```bash
# Development server with hot reload
npm run dev

# Run tests
php artisan test

# Run specific test
php artisan test --filter=SiteTreeTest

# Code formatting
./vendor/bin/pint

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Custom Artisan Commands

```bash
# Create an API key (plaintext shown once, prefix: ak_)
php artisan api-key:create "My Key" --abilities="content:read" --abilities="preview:read"
php artisan api-key:create "Full Access" --abilities="*" --site_id=1 --expires=2026-12-31
```

### Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/                # Admin panel controllers
│   │   ├── Ecommerce/        # Products, orders, payments, coupons, …
│   │   ├── Forum/            # Forum roles, badges (partial)
│   │   ├── Newsletter/       # Subscribers, lists, campaigns, templates
│   │   └── Tools/            # QR generator
│   ├── Api/V1/               # REST API endpoints
│   ├── Auth/                 # Login, registration, 2FA, OAuth
│   └── Frontend/             # Public pages
│       └── Ecommerce/        # Checkout, payment return, webhooks
├── Models/                   # Eloquent models
│   ├── Ecommerce/
│   └── Newsletter/
├── Services/                 # Business logic
│   ├── Payment/Gateways/     # Stripe, PayU, P24, PayPal
│   ├── Payment/Webhooks/     # Signature-verified webhook handlers
│   └── Newsletter/           # Sender drivers, payload builders, HTTP client
├── Support/
│   ├── helpers.php           # safe_html, input_value, is_viewer, …
│   └── Permissions.php       # Single source of truth for permissions
└── Mail/                     # Order emails, newsletter test, 2FA challenge

resources/views/
├── pages/admin/              # Admin templates
├── pages/frontend/           # Public templates
├── pages/auth/               # 2FA setup, challenge, recovery codes
├── emails/                   # Transactional email templates
├── components/               # Blade components
└── layouts/                  # Master layouts

routes/
├── web.php                   # Web routes
├── api/v1.php                # API v1 routes
├── auth.php                  # Authentication + 2FA routes
└── newsletter.admin.php      # Newsletter admin routes
```

## API

The REST API is feature-gated by the `enable_api` setting and requires authentication via the `X-API-Key` header. API keys can be created in the admin panel or via Artisan with specific scopes:

| Scope | Access |
|-------|--------|
| `content:read` | Sites, articles, categories, menus |
| `preview:read` | Draft and unpublished content |
| `settings:read_public` | Public settings |
| `media:read` | Media files |
| `admin:read` | Users, roles, permissions |

### Example Request

```bash
curl -H "X-API-Key: your-api-key" \
     http://localhost:8006/api/v1/sites
```

### Endpoints

```
GET  /api/v1/health
GET  /api/v1/menus
GET  /api/v1/sites
GET  /api/v1/sites/{slug}
GET  /api/v1/sites/{slug}/categories
GET  /api/v1/sites/{slug}/articles
GET  /api/v1/articles/{id}
GET  /api/v1/categories/{id}/articles
GET  /api/v1/preview/sites/{slug}
GET  /api/v1/preview/articles/{id}
GET  /api/v1/settings
GET  /api/v1/media/{id}
GET  /api/v1/admin/users
GET  /api/v1/admin/roles
GET  /api/v1/admin/permissions
```

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/SiteTreeTest.php

# Run specific test method
php artisan test --filter=test_site_has_categories
```

## License

MIT License
