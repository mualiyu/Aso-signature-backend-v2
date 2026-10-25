<p align="center">
    <img src="public/themes/aso.svg" alt="Aso Signature" width="200">
</p>

<p align="center">
    Connecting Nigerian Fashion to the World
</p>

# Aso Signature

Aso Signature is an e-commerce platform that connects Nigerian fashion designers with customers worldwide. Customers buy made-to-measure garments, submit their measurements, and get their orders shipped internationally by DHL.

This repository holds the whole application:

- **Storefront** (`/`): Blade + Vue 3 + Tailwind, served by Laravel
- **Admin panel** (`/admin`): catalog, orders, designers, alterations, configuration
- **REST API**: used by API clients (see `config/l5-swagger.php` for the docs)

---

## Contents

1. [Tech stack](#tech-stack)
2. [Prerequisites](#prerequisites)
3. [Local setup](#local-setup)
4. [Front-end assets](#front-end-assets)
5. [Configuration](#configuration)
6. [Scheduler and queues](#scheduler-and-queues)
7. [Project structure](#project-structure)
8. [Testing and code style](#testing-and-code-style)
9. [Deployment](#deployment)
10. [Troubleshooting](#troubleshooting)
11. [Known issues](#known-issues)

---

## Tech stack

| Layer      | Technology                                             |
| ---------- | ------------------------------------------------------ |
| Backend    | PHP 8.2+, Laravel 10                                   |
| Database   | MySQL 8                                                |
| Front end  | Blade, Vue 3, Tailwind CSS 3, Vite 4                   |
| Payments   | Flutterwave, Stripe, PayPal                            |
| Shipping   | DHL Express (MyDHL API): rates, labels, tracking       |
| Monitoring | Laravel Pulse, Laravel Nightwatch (optional)           |
| Hosting    | Hostinger (production)                                 |

---

## Prerequisites

- **PHP 8.2 or newer** with these extensions: `calendar`, `curl`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `zip`
- **Composer 2**
- **MySQL 8**
- **Node.js 18+ and npm** (only needed when you change storefront or admin CSS/JS)
- A local web server. [Laravel Herd](https://herd.laravel.com) or [Valet](https://laravel.com/docs/10.x/valet) is recommended. `php artisan serve` also works.

Check PHP and its extensions:

```bash
php -v
php -m | grep -E 'calendar|curl|gd|intl|mbstring|openssl|pdo_mysql|tokenizer|zip'
```

---

## Local setup

### 1. Clone and install PHP dependencies

```bash
git clone https://github.com/mualiyu/Aso-signature-backend-v2.git
cd Aso-signature-backend-v2
composer install
```

### 2. Create your environment file

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set at least:

```dotenv
APP_URL=https://aso-signature.test   # or http://127.0.0.1:8000 if you use `php artisan serve`
DB_DATABASE=aso_signature_db
DB_USERNAME=root
DB_PASSWORD=
```

See [Configuration](#configuration) for the rest.

### 3. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE aso_signature_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Load the schema and data

Pick **one** option.

**Option A: fresh install (empty store)**

```bash
php artisan migrate --seed
php artisan storage:link
```

This creates every table and seeds the base data: locales, currencies, channel, attributes, CMS pages, and an admin user.

| Admin login |                      |
| ----------- | -------------------- |
| URL         | `APP_URL/admin`      |
| Email       | `admin@example.com`  |
| Password    | `admin123`           |

Change this password straight away.

> `php artisan aso:install` is an interactive alternative. It asks for your app, database and admin details, then runs everything above. It runs `migrate:fresh`, so it **wipes the database**. Only use it on an empty database.

**Option B: copy of real data (recommended for day-to-day work)**

Ask a maintainer for a sanitised database dump and a copy of `storage/app/public` (the product and designer images). Then:

```bash
mysql -u root -p aso_signature_db < aso_signature_dump.sql
php artisan migrate          # applies any migrations newer than the dump
php artisan storage:link
# copy the images you were given into storage/app/public/
```

### 5. Run the app

**Herd or Valet**: link the folder and open `https://aso-signature.test`:

```bash
valet link aso-signature && valet secure aso-signature   # Valet
# Herd: add the folder under Sites. It is served at https://aso-signature.test
```

**Built-in server:**

```bash
php artisan serve   # http://127.0.0.1:8000. Set APP_URL to match.
```

The storefront and admin work out of the box. Their compiled assets are committed (see the next section).

---

## Front-end assets

The storefront and admin themes are separate Vite projects inside their packages. Their **compiled output is committed** under `public/themes/<theme>/default/build`, so you only need Node when you change CSS or JS.

| Theme      | Source                                 | Build output                              |
| ---------- | -------------------------------------- | ----------------------------------------- |
| Storefront | `packages/Webkul/Shop/src/Resources/assets`      | `public/themes/shop/default/build`        |
| Admin      | `packages/Webkul/Admin/src/Resources/assets`     | `public/themes/admin/default/build`       |
| Installer  | `packages/Webkul/Installer/src/Resources/assets` | `public/themes/installer/default/build`   |

Working on the storefront:

```bash
cd packages/Webkul/Shop
npm install
npm run dev     # hot reload. Writes public/shop-default-vite.hot while running.
npm run build   # production build. Commit the updated public/themes/shop/default/build.
```

The admin works the same way from `packages/Webkul/Admin`.

Things to know:

- **Tailwind is JIT-purged.** A Tailwind class that is new to the theme doesn't exist in the committed CSS until you run `npm run build`. Rebuild and commit the output whenever you add classes in Blade or Vue files.
- **Stop the dev server cleanly.** If `npm run dev` is killed without cleaning up, a leftover `public/*-vite.hot` file makes pages load assets from a dev server that isn't running. Delete the `.hot` file to fix it.
- The root `package.json` and `vite.config.js` belong to the default Laravel scaffold. The themes don't use them.

---

## Configuration

Settings live in two places.

### 1. `.env` (infrastructure and API keys)

`.env.example` documents every variable. The main groups:

| Group | Variables | Notes |
| ----- | --------- | ----- |
| App | `APP_URL`, `APP_ADMIN_URL`, `APP_TIMEZONE`, `APP_CURRENCY` | `APP_ADMIN_URL` sets the admin path (default `admin`). |
| Database | `DB_*` | MySQL only. |
| Mail | `MAIL_*`, `ADMIN_MAIL_*`, `CONTACT_MAIL_*` | Locally, use [Mailtrap](https://mailtrap.io), Herd's mail catcher, or `MAIL_MAILER=log`. |
| Exchange rates | `EXCHANGE_API_DEFAULT`, `CURRENCY_API_*`, `FLUTTERWAVE_SECRET_KEY`, `FLUTTERWAVE_SANDBOX` | Production uses `flutterwave`. |
| Measurement service | `MEASUREMENT_SERVICE_*` | URL and shared secret for the separate Smart Measurement wizard. Leave empty locally unless you're running it. |
| Storefront | `STORE_FRONT_URL`, `SANCTUM_STATEFUL_DOMAINS` | |
| Social login | `FACEBOOK_*`, `GOOGLE_*`, `TWITTER_*`, `LINKEDIN_*`, `GITHUB_*` | Optional. |
| Caching | `CACHE_DRIVER`, `RESPONSE_CACHE_ENABLED` | Keep the response cache **off** locally. |
| Monitoring | `NIGHTWATCH_TOKEN`, `PULSE_*` | Optional. |

Never commit `.env`. It is git-ignored.

### 2. Admin panel (business settings, stored in the database)

Payment and shipping credentials are **not** read from `.env`. Set them in **Admin → Configure → Sales**:

- **Payment Methods**: Flutterwave, Stripe, PayPal. Use test/sandbox keys locally.
- **Shipping Methods → DHL Express**: API key, secret, account number, sandbox toggle. The MyDHL API manual and checkpoint codes are in `docs/`.
- **Shipping Settings → Origin**: the address shipments are sent from.

A database dump (setup Option B) already contains these settings. Swap any live keys for sandbox ones before testing checkout.

---

## Scheduler and queues

Add one cron entry on any server that should run scheduled jobs (production only; it isn't needed locally):

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks (`app/Console/Kernel.php`):

| Command | When | Purpose |
| ------- | ---- | ------- |
| `invoice:cron` | daily 03:00 | Invoice housekeeping |
| `indexer:index --type=price` | daily 00:01 | Rebuild the product price index |
| `product:price-rule:index` | daily 00:01 | Apply catalog price rules |
| `currency:update` | 06:00 and 18:00 | Exchange rates (see [Known issues](#known-issues)) |
| `dhl:sync-tracking` | every 4 hours | Pull DHL tracking and mark orders as delivered |

You can run any of them by hand, for example `php artisan dhl:sync-tracking`.

Queues default to `sync` (`QUEUE_DRIVER=sync`), so you don't need a worker.

---

## Project structure

```text
app/                     Laravel app layer (console commands, exception handler, providers)
config/                  Laravel and module config
database/                Root seeders and factories
docs/                    Integration manuals (DHL) and policy drafts
packages/Webkul/<Module> All domain code, one module per folder (see below)
public/themes/           Compiled theme assets (committed)
routes/                  Root route files (most routes live inside the modules)
tests/                   Shared Pest/PHPUnit bootstrap
ux-review/               UX audit notes and screenshots
```

The application is modular. Each folder in `packages/Webkul/` is a self-contained module with its own `src/` (models, repositories, controllers, routes, views, migrations, translations) and, where needed, `tests/`. Modules are autoloaded through `composer.json` and registered in `config/app.php` and `config/concord.php`.

Modules built for Aso Signature:

| Module | What it does |
| ------ | ------------ |
| `Alteration` | Post-delivery fit claims ("Report a fit issue"), the 7-day window, and vetted alteration partners |
| `Designer` | Designer profiles, portfolios and designer-product linkage |
| `Flutterwave` | Flutterwave payment method and exchange rates |
| `Stripe` | Stripe payment method |
| `Shipping` | DHL Express integration: rates, shipment creation, tracking sync (`DHLShipmentService`, `DhlTrackingService`) |
| `Shop` | The storefront theme: home, PDP with production timeline, "How it works", checkout, customer account |

The other modules (`Admin`, `Product`, `Category`, `Checkout`, `Sales`, `Customer`, `Core`, and so on) make up the e-commerce core. Change them when you need to, but keep the changes small. Most customisations go in the modules above.

**Naming conventions inherited from the core**

- PHP namespaces are `Webkul\<Module>\...`. Keep them, because composer autoloading and the module registry depend on them.
- Views fire extension hooks via `view_render_event('<prefix>.shop....')`, and theme assets load through the theme's `..._asset()` helper and `@...Vite` Blade directive. These are internal identifiers that other code depends on. Don't rename them.
- Translations live in `packages/Webkul/<Module>/src/Resources/lang/<locale>/app.php`. `en` is the source of truth. Add new keys to `en` first.

---

## Testing and code style

Tests use [Pest](https://pestphp.com). Each test runs inside a transaction against the database in your `.env`, so **that database must be migrated and seeded** (setup step 4). Use your local database only, never production.

```bash
php artisan test                                       # everything
vendor/bin/pest --testsuite="Shop Feature Test"        # one suite
vendor/bin/pest packages/Webkul/Alteration/tests       # one module
vendor/bin/pest --filter="fit issue"                   # by name
```

The suites are defined in `phpunit.xml`: Admin, Core, DataGrid, Shop, Alteration.

> A few flat-rate shipping and invoice tests fail intermittently on a clean checkout. These are known and not caused by your change. Re-run them on their own to confirm.

Code style uses [Laravel Pint](https://laravel.com/docs/10.x/pint) with `pint.json`:

```bash
vendor/bin/pint            # fix
vendor/bin/pint --test     # check only
```

---

## Deployment

Production runs on Hostinger at `https://asosignature.com`. Use `.env.production.example` as the template for the server's `.env`. It has `APP_DEBUG=false`, live payment keys, and the production URLs.

A typical release:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan responsecache:clear   # if the response cache is on
```

Front-end assets are already built and committed, so the server doesn't run npm.

---

## Troubleshooting

| Symptom | Fix |
| ------- | --- |
| Every page redirects to `/install` | The database isn't connected or has no admin user. Check `DB_*` in `.env`, then run setup step 4. |
| Unstyled pages, or assets loading from `localhost:5173` | A stale `public/*-vite.hot` file. Delete it. |
| A new Tailwind class has no effect | Rebuild the theme (`npm run build` in the package) and commit the output. |
| Product images missing | Run `php artisan storage:link` and check that `storage/app/public` has the images. |
| Changes don't show up | `php artisan optimize:clear`, and `php artisan responsecache:clear` if `RESPONSE_CACHE_ENABLED=true`. |
| `route:list` crashes on boot | Known issue in a GDPR route definition. It doesn't affect the running app. |

---

## Known issues

- `currency:update` is scheduled in `app/Console/Kernel.php`, but no command has that name, so the job fails. The custom command is `app:update-currency-rates` (`app/Console/Commands/UpdateCurrencyRates.php`). It checks for a `rates` key but loops over `quotes`, so it needs fixing before it's scheduled.
- `vendor/` is committed to git. Run `composer install` anyway, so your local `vendor/` matches `composer.lock`.

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the branch, commit and pull request workflow.

## Contact

- Website: [asosignature.com](https://asosignature.com)
- Developers: <hello@asosignature.com>
