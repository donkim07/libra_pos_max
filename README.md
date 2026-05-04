# LibraPOS

**LibraPOS** is a multi-tenant SaaS-style point of sale and operations platform for Tanzanian manufacturers and trading businesses. It brings **production**, **inventory**, **purchasing**, **sales**, and **reporting** into one Filament-powered admin experience, with a **public marketing site** for pricing and lead capture.

## What it does

- **Manufacturing** — Production batches, bulk manufacturing, and assembly / BOM-style workflows.
- **Inventory** — Items with SKU, categories, units, stock by store, movements, and adjustments.
- **Sales** — POS-oriented sales, sale orders, invoicing, and print routes.
- **Purchasing & partners** — Purchases, suppliers, customers.
- **Finance & insight** — Accounts, expenses, transfers, and reporting-oriented pages (e.g. profit/loss views in the panel).
- **Access control** — Filament Shield / Spatie Permission for roles and policies inside the panel.

Each business is intended to run as an **isolated tenant** (workspace); subscription rules and enforcement are described for implementers in [`docs/PRICING_PLANS_SPEC.md`](docs/PRICING_PLANS_SPEC.md).

## Tech stack

| Layer | Choice |
|--------|--------|
| Runtime | PHP **8.2+** |
| Framework | **Laravel 12** |
| Admin UI | **Filament 4.5** (Livewire) |
| Frontend assets | **Vite**, **Tailwind CSS v4** |
| Permissions | **Filament Shield**, **spatie/laravel-permission** |
| Settings | **spatie/laravel-settings** |

## Requirements

- PHP 8.2 or newer with common extensions (mbstring, openssl, pdo, etc.)
- Composer
- Node.js 20+ (or compatible) and npm, for Vite

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate
```

Install and build frontend assets:

```bash
npm install
npm run dev
```

Run the application (choose one):

```bash
php artisan serve
```

## URLs

| URL | Purpose |
|-----|---------|
| `/` | Marketing home |
| `/features`, `/pricing`, `/about`, `/contact` | Marketing pages |
| `POST /contact` | Contact form (validated; extend with mail as needed) |
| `/admin` | Filament panel (dashboard) |
| `/admin/login` | Panel login |

Authenticated users see **Dashboard** in the marketing layout instead of **Login** / **Start Free Trial**.

## Project layout (high level)

- `app/Filament/` — Resources, pages, widgets for the admin panel.
- `resources/views/landing/` — Public marketing Blade views.
- `resources/views/layouts/landing.blade.php` — Shared marketing layout (nav, footer, Alpine, AOS).
- `routes/web.php` — Marketing routes and contact handler.
- `docs/PRICING_PLANS_SPEC.md` — Plan limits and feature flags for future billing and tenancy enforcement.

## Testing & quality

```bash
php artisan test
./vendor/bin/pint
```

## License

This project inherits the **MIT** license from the Laravel application skeleton unless otherwise specified in the repository.

## Contact

- **Dar es Salaam, Tanzania**
- **WhatsApp:** +255748224536  
- **Email:** librapos@rejoda.co.tz
