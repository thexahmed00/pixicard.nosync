# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Assets
npm run dev          # Vite dev server
npm run build        # Build assets

# Laravel
php artisan serve    # Start dev server
php artisan migrate  # Run migrations

# Tests
./vendor/bin/pest           # All tests
php artisan test            # All tests (alias)
./vendor/bin/pest --filter=TestName  # Single test

# Code style
./vendor/bin/pint           # Fix code style (PSR-12)
./vendor/bin/pint --test    # Check without fixing

# Docker (Sail)
./vendor/bin/sail up -d     # Start all services
./vendor/bin/sail down      # Stop services
```

## Architecture

**PixiCard** is a customized [Bagisto](https://bagisto.com) e-commerce platform with LikeCard payment integration. Laravel 11 modular monolith.

### Package Structure

All business logic lives in `packages/`, not `app/`. Two namespaces:

- `packages/Webkul/` — core Bagisto modules (Admin, Shop, Product, Category, Checkout, Payment, Shipping, Marketing, Marketplace, etc.)
- `packages/Pixi/` — custom LikeCard/Pixi payment integration

Each package is self-contained: models, controllers, routes, migrations, seeders, Blade views, service providers, and event listeners.

Packages auto-register via `config/concord.php` using the Concord module system. Adding a new module = register its `ModuleServiceProvider` there.

### Request Flow

Browser → `routes/` (web/api/graphql) → Package Controllers → Package Services/Models → MySQL

Admin panel: `packages/Webkul/Admin/`
Storefront: `packages/Webkul/Shop/`
REST API: `packages/Webkul/RestApi/`
GraphQL: via Lighthouse (`packages/Webkul/GraphQLApi/`)

### Key Configs

| File | Purpose |
|------|---------|
| `config/concord.php` | Module registration (add new packages here) |
| `config/elasticsearch.php` | Product search connection |
| `config/bagisto-vite.php` | Vite asset compilation per-package |
| `phpunit.xml` | Test suites: Admin Feature, Core Unit, DataGrid Unit, Shop Feature |
| `pint.json` | Linting preset (Laravel/PSR-12) |

### Services

- **Search:** Elasticsearch 7.17.0 (product search)
- **Cache/Queue:** Redis
- **Email testing:** Mailpit (via Sail)
- **Payments:** PayPal SDK + custom Pixi/LikeCard package
- **Image processing:** Intervention Image + cache layer
- **PDF:** mPDF + DomPDF

### Frontend

Blade templates live inside each package's `Resources/views/`. Vite compiles assets per-package. No separate SPA — server-rendered with Blade + Axios for async calls.
