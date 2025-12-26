# Bendahara

![Tests](https://img.shields.io/badge/tests-60%20passing-brightgreen)
![Assertions](https://img.shields.io/badge/assertions-141-blue)
![Coverage](https://img.shields.io/badge/coverage-comprehensive-success)

Multi-division accounting application built with Laravel 12, powered by **WuriN7i/Balance** double-entry bookkeeping engine.

## About

Bendahara adalah aplikasi akuntansi berbasis divisi yang dirancang untuk organisasi dengan multiple unit/divisi yang memerlukan pembukuan terpisah. Aplikasi ini dibangun di atas modul **Balance** — sebuah pure double-entry accounting engine yang portable dan dapat digunakan secara independen.

## Features

### ✅ Balance Module (Core Accounting Engine)
- **Double-entry bookkeeping** - Debit = Credit validation
- **Account behaviors** - FLEXIBLE, TRANSIT_ONLY, CREDIT_ONLY, NON_LIQUID
- **Transaction workflow** - DRAFT → PENDING → APPROVED/REJECTED/VOID
- **Approval system** - Multi-level approval dengan audit trail
- **Balance calculator** - Real-time balance calculation per account
- **Voucher generation** - Auto-generated voucher numbers
- **Extensible architecture** - Interface-based untuk customization

### 🚧 Bendahara Features (Coming Soon)
- Multi-division management
- Division-scoped transactions
- Division-specific chart of accounts ("saku")
- Division-aware balance calculation
- User authorization per division
- Reporting & analytics

## Tech Stack

- **PHP** 8.2+
- **Laravel** 12.x
- **Database** SQLite (dev) / MySQL/PostgreSQL (prod)
- **Architecture** Modular monorepo with path repository

## Installation

```bash
# Clone repository
git clone https://github.com/wurin7i/bendahara.git
cd bendahara

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate --seed

# Start development server
php artisan serve
```

## Testing

Comprehensive test suite with 60 tests and 141 assertions:

```bash
# Run Bendahara tests (Pest PHP)
vendor/bin/pest

# Run Balance module tests (PHPUnit)
cd modules/balance && ../../vendor/bin/phpunit
```

**Test Coverage:**
- ✅ Bendahara Application: 22 tests (Pest PHP)
- ✅ Balance Module: 38 tests (PHPUnit with Orchestra Testbench)
- 📊 100% passing rate

See [`docs/TESTING.md`](docs/TESTING.md) for complete testing guide.

## Development Status

- [x] **Phase 1: Balance Module** (Complete)
- [x] **Phase 1.5: Test Suite** (Complete)
- [ ] **Phase 2: Bendahara Application** (Next)

## Contributing

Saat ini project masih dalam tahap development awal. Contribution guidelines akan ditambahkan setelah API stable.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

Built with ❤️ using Laravel 12

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
