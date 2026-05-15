# PixiCard Bagisto Agent Guide

## Essential Commands

### Development
- **Assets**: `npm run dev` (Vite dev server), `npm run build` (production build)
- **Laravel**: `php artisan serve` (dev server), `php artisan migrate` (run migrations)
- **Docker (Sail)**: `./vendor/bin/sail up -d` (start), `./vendor/bin/sail down` (stop)

### Testing
- **All tests**: `./vendor/bin/pest` or `php artisan test`
- **Single test**: `./vendor/bin/pest --filter=TestName`
- **Test suites**: Admin Feature, Core Unit, DataGrid Unit, Shop Feature (see phpunit.xml)

### Code Quality
- **Fix style**: `./vendor/bin/pint`
- **Check style**: `./vendor/bin/pint --test`

## Architecture

### Package Structure
All business logic resides in `packages/` (not `app/`):
- `packages/Webkul/` — Core Bagisto modules (Admin, Shop, Product, etc.)
- `packages/Pixi/` — Custom LikeCard/Pixi payment integration

Each package is self-contained with models, controllers, routes, migrations, views, and service providers.

### Module Registration
Packages auto-register via `config/concord.php`. To add a new module, register its `ModuleServiceProvider` in the `modules` array.

### Request Flow
Browser → `routes/` (web/api/graphql) → Package Controllers → Package Services/Models → MySQL

### Key Configuration
- `config/concord.php` — Module registration
- `config/bagisto-vite.php` — Vite asset compilation per-package
- `phpunit.xml` — Test suite configuration
- `pint.json` — Laravel/Pint preset (PSR-12)

## Important Notes

### Frontend
Blade templates live in each package's `Resources/views/`. Vite compiles assets per-package. No separate SPA — server-rendered with Blade + Axios for async calls.

### Services
- **Search**: Elasticsearch 7.17.0 (product search)
- **Cache/Queue**: Redis
- **Email testing**: Mailpit (via Sail)
- **Payments**: PayPal SDK + custom Pixi/LikeCard package
- **Image processing**: Intervention Image + cache layer
- **PDF**: mPDF + DomPDF

### Environment
Requires PHP 8.2+ with extensions: calendar, curl, intl, mbstring, openssl, pdo, pdo_mysql, tokenizer.