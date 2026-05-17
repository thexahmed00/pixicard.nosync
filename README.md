# PixiCardBagisto

An e-commerce platform built on [Bagisto](https://bagisto.com/) (Laravel-based) with custom PixiCard and LikeCard payment integrations.

## Tech Stack

- **Backend**: Laravel (PHP 8.2+)
- **Frontend**: Blade templates + Vite
- **Database**: MySQL
- **Cache/Queue**: Redis
- **Search**: Elasticsearch 7.17.0
- **Payments**: PayPal SDK, Pixi/LikeCard integration

## Project Structure

All business logic resides in `packages/`:

| Package | Description |
|---------|-------------|
| `packages/Webkul/` | Core Bagisto modules (Admin, Shop, Product, etc.) |
| `packages/Pixi/` | Custom PixiCard/LikeCard payment integration |

## Quick Start

### Prerequisites

- PHP 8.2+ with extensions: `calendar`, `curl`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`
- Composer
- Node.js & npm
- MySQL
- Redis

### Setup

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
npm run dev
```

### Docker (Sail)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
```

## Development

### Module Registration

Add new modules in `config/concord.php`:

```php
'modules' => [
    \Your\Package\Providers\ModuleServiceProvider::class,
],
```

### Testing

```bash
# All tests
./vendor/bin/pest

# Single test
./vendor/bin/pest --filter=TestName
```

### Code Style

```bash
# Check
./vendor/bin/pint --test

# Fix
./vendor/bin/pint
```

## Services

| Service | Purpose |
|---------|---------|
| Redis | Cache, Queue, Sessions |
| Elasticsearch | Product search |
| Mailpit | Email testing (Sail) |
| mPDF / DomPDF | PDF generation |
| Intervention Image | Image processing |

## License

[MIT](LICENSE)
