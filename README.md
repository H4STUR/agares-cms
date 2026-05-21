# Agares CMS

A modern, multi-site content management system built with Laravel 13.

## Features

- **Multi-Site Management** — Manage multiple websites from a single admin dashboard
- **Hierarchical Content** — Organize content with Sites → Categories → Articles structure
- **Publishing Workflow** — Draft, scheduled, published, and private content states
- **Custom Fields** — Flexible input system to add custom fields to any content type
- **Role-Based Access Control** — Granular permissions with site-scoped roles
- **REST API** — Full API access with scoped API key authentication
- **Media Library** — Centralized file and image management with galleries
- **Dynamic Forms** — Build custom forms with configurable fields
- **Cookie Compliance** — GDPR-ready cookie consent management
- **Custom Code Injection** — Add custom CSS/JS per site
- **Google Analytics** — GA4 integration for dashboard analytics
- **Social Authentication** — Login with Google or Facebook

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL 8.0 |
| Frontend | Tailwind CSS, Alpine.js, Bootstrap 5 |
| Build | Vite |
| Auth | Laravel Breeze, Socialite |
| Permissions | Spatie Laravel Permission |

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

# Run migrations
docker exec -it agares php artisan migrate

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

### Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/          # Admin panel controllers
│   ├── Api/V1/         # REST API endpoints
│   └── Frontend/       # Public pages
├── Models/             # Eloquent models
└── Services/           # Business logic

resources/views/
├── pages/admin/        # Admin templates
├── pages/frontend/     # Public templates
├── components/         # Blade components
└── layouts/            # Master layouts

routes/
├── web.php             # Web routes
├── api/v1.php          # API v1 routes
└── auth.php            # Authentication routes
```

## API

The REST API requires authentication via `X-API-Key` header. API keys can be created in the admin panel with specific scopes:

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
