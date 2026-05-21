# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Agares CMS is a multi-site content management system built on Laravel 13 (PHP 8.3). It manages multiple websites from a single admin dashboard with hierarchical content organization (Sites → Categories → Articles), role-based access control via Spatie permissions, and a REST API with scoped API key authentication. An ecommerce module is feature-gated via settings.

## Common Commands

### Development
```bash
# Start Docker environment (app on :8006, phpMyAdmin on :8086)
docker-compose up -d

# Frontend assets (Vite)
npm run dev          # Development with HMR
npm run build        # Production build

# Laravel commands
php artisan serve    # Local dev server (if not using Docker)
php artisan migrate  # Run migrations
php artisan tinker   # Interactive REPL
```

### Testing
```bash
php artisan test                           # Run all tests
php artisan test --filter=SiteTreeTest     # Run specific test class
php artisan test tests/Feature/            # Run feature tests only
php artisan test tests/Unit/               # Run unit tests only
```

### Code Quality
```bash
./vendor/bin/pint    # Laravel Pint code formatting
```

### Cache Management
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Custom Artisan Commands
```bash
# Create an API key (plaintext shown once, prefix: ak_)
php artisan api-key:create "My Key" --abilities="content:read" --abilities="preview:read"
php artisan api-key:create "Full Access" --abilities="*" --site_id=1 --expires=2026-12-31
```

## Architecture

### Content Hierarchy
Sites → Categories → Articles (all support soft deletes and scheduled publishing)

Each entity has a `status` field: `draft`, `published`, `scheduled`, `private`. The `scopePublic()` query scope filters visible content on the frontend.

### Models Overview
**Core CMS**: User, Site, Category, Article, Menu, Setting
**Input System**: InputField, InputTemplate, InputTemplateItem, InputInstance
**Media**: Media, Gallery, GalleryItem
**Forms**: Form, FormField
**FAQ**: Faq, FaqItem
**Custom Code Injection**: CustomCode
**Cookie Consent**: CookieConsentSetting, CookieScan, CookieScanCookie
**Access Control**: ApiKey, RoleSitePermission, Badge, UserForumRole
**Ecommerce** (`app/Models/Ecommerce/`): Product, ProductVariant, Category, Tag, Attribute, AttributeValue, Order, OrderItem, OrderStatusHistory, Payment, PaymentProvider, ShippingMethod, TaxRule, Coupon, CouponRedemption, Setting

### Input System (Custom Fields)
Polymorphic system for attaching custom fields to any content type:
- `InputTemplate` - Reusable field definitions scoped to a site
- `InputTemplateItem` - Individual field slot within a template
- `InputInstance` - Actual field values attached to Site/Category/Article via `owner_type`/`owner_id`
- `InputField` - Field type definitions (text, textarea, gallery, file, form, etc.)

Helper functions in `app/Support/helpers.php`:
- `input_value($variable, $site, $category, $article)` — retrieves a custom field value
- `contact_form_from_instance()` — builds contact form from a form InputInstance (defined in `app/Support/forms.php`)
- `safe_html(?string $value): string` — sanitizes admin-authored rich-text HTML: strips non-allowlisted tags, event handlers (`on*`), and `javascript:`/`data:` URIs. Use with `{!! safe_html($value) !!}` wherever rich text is rendered unescaped.
- `safe_label(?string $value): string` — stricter variant for form field labels; allows only `<a>`, `<strong>`, `<em>`, `<br>` and sanitizes hrefs.

### API Authentication
REST API is feature-gated by the `enable_api` setting. Uses `X-API-Key` header with scoped abilities:
- `content:read` - Public content (sites, articles, categories, menus)
- `preview:read` - Draft/unpublished content
- `settings:read_public` - Public settings
- `media:read` - Media files
- `admin:read` - Users, roles, permissions

`routes/api.php` loads `routes/api/v1.php` (and future versions via glob). Middleware `ApiKeyAuth` validates keys and scopes.

### Permissions
Uses Spatie Laravel Permission with site-scoped permissions via `RoleSitePermission` model. Admin routes check permissions via standard Laravel `can()` gates. Custom helper: `User->canOn(string $ability, Site $site): bool` checks if the user has a permission on a specific site.

### Feature Flags (EnsureSetting Middleware)
Route middleware gates features based on `Setting` key-value pairs:
```php
// Usage in route definition:
Route::middleware('setting:enable_ecommerce,true')
Route::middleware('setting:enable_registration,true,abort403')
// Syntax: setting:<key>,<expected_value>,<mode>
// Modes: abort404 (default), abort403, json404, json403
```

### Key Directories
- `app/Http/Controllers/Admin/` - Admin panel controllers (articles, categories, sites, users, permissions, media, menus, custom code, cookie consent, galleries, input templates/instances, forms, dashboard, search, settings)
- `app/Http/Controllers/Admin/Ecommerce/` - Ecommerce admin controllers (feature-gated)
- `app/Http/Controllers/Admin/Forum/` - Forum admin controller (UserForumRole, Badges)
- `app/Http/Controllers/Admin/Tools/` - QR Generator tool
- `app/Http/Controllers/Admin/API/` - API key management UI
- `app/Http/Controllers/Api/V1/` - REST API v1 endpoints (ContentController, SettingsController, MediaController, AdminController)
- `app/Http/Controllers/Frontend/` - Public-facing pages (PageController, CookieConsentController, FormController)
- `app/Http/Controllers/Frontend/Ecommerce/` - Ecommerce frontend (CheckoutController, PaymentReturnController, PaymentWebhookController)
- `app/Services/` - Business logic:
  - `InputInstanceService.php` - Applies field templates to owner models
  - `Ga4AnalyticsService.php` - GA4 integration (summaryLast7Days, trafficTimelineLast12Months, realtimeActiveUsers; cached 30min/10s)
  - `Payment/PaymentTransitionService.php` - Shared `capture()` / `fail()` helpers used by webhook handlers and return controller
  - `Payment/Gateways/` - StripeGateway, PayUGateway, P24Gateway, PayPalGateway (each: `initiatePayment()` → redirect URL)
  - `Payment/Webhooks/` - AbstractWebhookHandler + per-driver handlers (Stripe, PayU, P24, PayPal); signature-verified, idempotent
- `app/Mail/Ecommerce/` - OrderConfirmed, OrderStatusChanged, NewOrderAlert (inline HTML, `build()` pattern)
- `app/Support/MenuTree.php` - Recursive menu tree builder with caching
- `resources/views/pages/admin/` - Admin Blade templates
- `resources/views/emails/ecommerce/` - Email templates for order events

### Route Structure
- `/admin/*` - Admin panel (auth required, permission-checked)
- `/admin/ecommerce/*` - Ecommerce admin (also requires `enable_ecommerce` setting)
- `/api/v1/*` - REST API (API key required, also requires `enable_api` setting)
- `/shop/*` - Ecommerce frontend (requires `enable_ecommerce` setting)
  - `/shop/checkout` GET/POST — checkout form + order creation
  - `/shop/order/{orderNumber}/confirmation` — post-order confirmation page
  - `/shop/payment/return/{driver}` — gateway return URL (outside maintenance middleware)
  - `/shop/payment/webhook/{driver}` — gateway webhook receiver (outside maintenance + CSRF-exempt via `bootstrap/app.php`)
- `/{site:slug}/{category?}/{articleId?}` - Frontend content routes
- `/user/{user}/settings` - User profile/settings

### Providers
- `AppServiceProvider` — registers View composers that inject global settings, custom codes (CSS/JS injection), and sets API key rate limits
- `AdminComposerServiceProvider` — injects admin sidebar menu data into all admin views

### Middleware
- `ApiKeyAuth` — validates `X-API-Key` header and checks ability scope
- `EnsureSetting` — gates routes based on Setting values (see Feature Flags above)
- `CheckMaintenanceMode` — maintenance mode guard

### Frontend Stack
- **Vite 5** (bundler)
- **Tailwind CSS 3** + forms plugin (admin UI styling)
- **Bootstrap 5.3** + Bootstrap Icons (components)
- **Alpine.js 3** (interactive UI components)
- **Axios 1.6** (HTTP client)
- **Monaco Editor 0.52** (`resources/js/monaco-editor.js`, used for custom code editing in admin)
- **Sass** (preprocessor)
- **stripe/stripe-php** (Stripe Checkout Sessions API — server-side)

### Database
MySQL 8.0 in Docker. Models use soft deletes (`deleted_at`). Key pivot tables:
- `menu_site`, `category_article` — core CMS relations
- `gallery_media`, `input_instance_media` — media attachments
- `ecommerce_product_category`, `ecommerce_product_tag`, `ecommerce_product_attribute_variant` — ecommerce relations

Settings stored in flat `settings` key-value table. Polymorphic relationships use `owner_type`/`owner_id` pattern (InputInstance, Gallery).

### OAuth
Google and Facebook OAuth supported. Users table has `google_id`, `facebook_id` columns. Credentials configured in `config/services.php`.

### Forum & FAQ (Partial)
`UserForumRole`, `Badge`, `Faq`, `FaqItem` models exist but admin UI/controllers are still in progress.

### Ecommerce Module
Full ecommerce system feature-gated by `Setting::bool('enable_ecommerce')`. Includes products with variants, categories, tags, attributes, orders with status history, payments, shipping methods, tax rules, and coupons.

#### Payment Providers
`ecommerce_payment_providers` table. `PaymentProviderController::index()` auto-seeds 5 drivers on first visit if table is empty: `stripe`, `payu`, `p24`, `paypal`, `cod`. Each stores per-driver credentials as a JSON `config` column. Admin UI at `/admin/ecommerce/payment-providers` — enable/disable toggle + per-driver configure form.

#### Order Flow
1. Customer submits checkout → `CheckoutController::store()` validates, generates order number (`{prefix}{YYYYMMDD}{seq4}`, prefix from `order_number_prefix` setting), creates `Order` + `OrderItem`s + `OrderStatusHistory` + `Payment` in one DB transaction.
2. **COD**: order status `processing`, redirect to confirmation page.
3. **Online**: order status `pending_payment`, gateway `initiatePayment()` called, redirect to external gateway URL.
4. Customer returns to `/shop/payment/return/{driver}` → `PaymentReturnController` verifies/captures, redirects to confirmation.
5. Gateway posts to `/shop/payment/webhook/{driver}` → `PaymentWebhookController` → per-driver handler → `PaymentTransitionService::capture()` / `fail()` → order status updated + history entry written.

#### Guest Checkout & Account Creation
- `guest_checkout` ecommerce setting gates the checkout page for unauthenticated users.
- `allow_register_at_checkout` setting shows "Create account" radio on the checkout form (Alpine.js toggle reveals password fields).
- When selected: User created (name/surname/email/password/role_id=customer) inside the same DB transaction as the order; `auth()->login()` called after the transaction.

#### Emails
Three Mailable classes in `App\Mail\Ecommerce\` (all use `build()` + `SerializesModels`):
- `OrderConfirmed` — sent to customer after order placement (items, totals, billing address, payment method)
- `OrderStatusChanged` — sent to customer when admin updates order status (from/to badge, optional comment)
- `NewOrderAlert` — sent to `admin_email` ecommerce setting on new order (summary bar + items + "View in Admin" CTA)

Dispatches: `CheckoutController::store()` sends OrderConfirmed + NewOrderAlert; `OrderController::updateStatus()` sends OrderStatusChanged.

#### Gateway Architecture
```
app/Services/Payment/
├── PaymentTransitionService.php       — static capture()/fail(), used everywhere
├── Gateways/
│   ├── StripeGateway.php              — Checkout Sessions API, stores payment_intent ID
│   ├── PayUGateway.php                — OAuth2 → /api/v2_1/orders, stores orderId
│   ├── P24Gateway.php                 — SHA384-signed /api/v1/transaction/register, stores token
│   └── PayPalGateway.php              — Orders API v2, captures on return
└── Webhooks/
    ├── AbstractWebhookHandler.php     — delegates to PaymentTransitionService
    ├── StripeWebhookHandler.php       — HMAC-SHA256 sig; checkout.session.completed + payment_intent.succeeded
    ├── PayUWebhookHandler.php         — MD5 sig; COMPLETED/CANCELED
    ├── P24WebhookHandler.php          — MD5 sig; single notification
    └── PayPalWebhookHandler.php       — OpenSSL cert-based sig; CAPTURE.COMPLETED/DENIED/REFUNDED
                                           Requires `webhook_id` in the PayPal provider config column (admin UI → Payment Providers → PayPal → Configure)
```

#### Webhook security
- Stripe and PayU: if `webhook_secret` / `md5_key` is absent from the provider config, the handler returns 500 and logs CRITICAL — intentional hard-fail, not a bug.
- PayPal: requires `webhook_id` in provider config in addition to `client_id`/`client_secret`. Missing `webhook_id` → hard-fail 400.
- File uploads (`MediaController::upload`, `InputInstanceController::uploadFiles`) enforce a MIME type allowlist. Blocked extensions include `.php`, `.phtml`, `.phar`, and all server-side executables. See `MediaController::ALLOWED_EXTENSIONS`.
- `InputInstanceController` mutating methods (`uploadFiles`, `updateValue`, `delete`, `move`, etc.) call `authorizeInstance()` which verifies the authenticated user has `canOn('edit', $site)` on the owning site. Super-admin role bypasses this check.

#### Ecommerce Settings (key ones)
- `order_number_prefix` — default `AG-`
- `currency` — legacy single currency (also `default_currency`, `available_currencies` for future multi-currency)
- `guest_checkout`, `allow_register_at_checkout` — checkout behaviour
- `admin_email` — new-order notification recipient
- `checkout_require_phone` — makes phone field required

#### Roles
`customer` role added to `RolesAndPermissionsSeeder` with permissions `place orders` + `view own orders`. Assigned automatically on account creation at checkout.
