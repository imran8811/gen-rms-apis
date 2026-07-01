# genz-rms-apis — RMS Backend APIs

Backend REST APIs for the Restaurant Management System (POS). Consumed by
[`genz-rms-fe`](../genz-rms-fe) (the RMS/POS frontend).

- **Stack:** Laravel 13, PHP 8.2+, Laravel Sanctum (token auth), Tinker.
- **Frontend assets:** Vite (`vite.config.js`), `npm` for build tooling.
- **Auth:** Sanctum tokens. The frontend stores the token under `rms_token`.
- **Public API base:** `https://api.rms.genzfoods.pk/api`
- **DB:** MySQL `genz_rms_apis`. Local dev: run on a **separate port** from the
  storefront API, e.g. `php artisan serve --port=8001`.

## Menu = read-only mirror (source of truth is genz-admin)
The menu is **no longer authored here**. The source of truth is **`genz-admin-apis`**. This app
keeps a **read-only mirror** in `categories` + `menu_items` (so recipes/costing/inventory keep
referencing `menu_items` by id) that is refreshed from the admin feed:
- `php artisan menu:sync` (`App\Console\Commands\SyncMenu` + `App\Services\MenuImporter`) pulls
  `ADMIN_MENU_URL` (default `http://localhost:8002/api/public/menu`) and **upserts by slug**
  (stable ids; missing items are deactivated, never deleted — so `recipes.menu_item_id` stays valid).
- **Menu management was removed**: `MenuItemController` + the `menu-items` routes are gone, the old
  `public/menu` publisher feed was removed, and the RMS POS `/menu` page was deleted. The only menu
  endpoint left is **`GET /categories`** (`CategoryController` index/show) — a read feed the POS
  billing screen (`lib/menuStore.ts` → `useMenu`) uses to build orders. Edit the menu in `genz-admin`,
  then run `menu:sync`.
- The `Category` + `MenuItem` models and `menu_items`/`categories` tables remain (the synced mirror);
  recipes/costing/inventory still link to `menu_items` by `menu_item_id` exactly as before.

## Layout

- `routes/api.php` — API endpoints (primary surface).
- `app/` — controllers, models, services (PSR-4 `App\`).
- `database/` — migrations, factories, seeders.
- `config/`, `.env` — configuration. Copy `.env.example` → `.env` for setup.
- `tests/` — PHPUnit tests (PSR-4 `Tests\`).

## Common commands

PHP is installed at `C:\php83` (use it if `php` is not on PATH).

```bash
composer run setup       # install, copy .env, key:generate, migrate, npm build
composer run dev         # serve + queue listener + pail logs + vite (concurrently)
php artisan serve        # API server only
php artisan migrate      # run migrations
composer run test        # config:clear + artisan test (PHPUnit)
./vendor/bin/pint        # code style (Laravel Pint)
```

## Conventions

- Standard Laravel structure and conventions.
- Format with **Laravel Pint** before committing.
- Keep API routes in `routes/api.php`; protect them with Sanctum middleware.
