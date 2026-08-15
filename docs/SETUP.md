# Setup — CodeIgniter 3 port

This directory (`codeigniter3-app/`) is a **separate, standalone project** — a port of the
parent Laravel app's API, admin panel, and promo page to CodeIgniter 3. It reuses the same
MySQL schema (see `docs/SCHEMA_REFERENCE.md`), so it can point at the same database the
Laravel app uses, or a copy of it.

## 1. Get the CodeIgniter 3 framework core

This repo only ships `application/`, `docs/`, `index.php`, and config files — **not**
CodeIgniter 3's `system/` folder (the framework engine itself, ~300 files). That's normal:
CI3 isn't distributed as a Composer library the way Laravel's framework is. Get it one of
these ways:

```bash
# Option A — git clone and copy just the system/ folder
git clone https://github.com/bcit-ci/CodeIgniter.git /tmp/ci3
cp -r /tmp/ci3/system ./system

# Option B — download a release zip from
# https://github.com/bcit-ci/CodeIgniter/releases and extract its system/ folder here
```

Use CodeIgniter **3.1.13** (the last 3.x release) for best PHP 7/8 compatibility.

Do **not** copy the upstream `index.php` or `application/` folder — this project's own
`index.php` and `application/` already exist and are wired to this port's config/routes.

## 2. Configure the environment

```bash
cp .env.example .env
```

Edit `.env`:
- `CI_BASE_URL` — the URL you'll browse to, e.g. `http://localhost:8080/`
- `CI_ENCRYPTION_KEY` — any random 32+ byte string (used for CI's session/crypto if enabled)
- `DB_*` — point at the same MySQL database as the Laravel app (or a copy of its schema)

## 3. Database

No CI3 migration runner is included — this port assumes the schema already exists,
created by the Laravel app's migrations (`../database/migrations`). Point `DB_DATABASE`
at that same database, or export/import its schema+data into a new one:

```bash
mysqldump -u root -p gold_loan > gold_loan_schema.sql
mysql -u root -p gold_loan_ci3_copy < gold_loan_schema.sql
```

## 4. Seed a default admin login (if you don't already have one)

This port has no seeder runner either — insert directly, or reuse the Laravel app's
`AdminUserSeeder` output (same `users`/`roles` tables, so an admin created via the
Laravel app's `php artisan db:seed` already works here — same DB, same login):

```sql
-- Only needed if you're pointing at a fresh DB with no ADMIN user yet.
-- Password hash below is bcrypt for the plaintext "password" — change it after first login.
INSERT INTO users (employee_code, name, mobile, email, password, role_id, is_active, created_at, updated_at)
SELECT 'ADMIN001', 'System Administrator', '9999999999', 'admin@goldtrust.test',
       '$2y$10$examplehashreplaceme', id, 1, NOW(), NOW()
FROM roles WHERE code = 'ADMIN' LIMIT 1;
```
Generate a real bcrypt hash with `php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"`.

## 5. Run a PHP server

```bash
php -S localhost:8080
```

Entry points:
- `http://localhost:8080/` — public promo page
- `http://localhost:8080/admin/login` — admin panel (session auth)
- `http://localhost:8080/api/v1/auth/login` — JSON API (Bearer token auth)

## 6. Verify

This port was written without a PHP interpreter available in the authoring environment
(no `php`, `composer` on that machine) — **it has not been executed or tested.** Before
trusting it:
- Confirm `system/` is in place and `http://localhost:8080/` loads without a 503.
- Walk through `/admin/login` → dashboard → each module page.
- Hit a couple of `/api/v1/*` endpoints with curl/Postman using a token from `/api/v1/auth/login`.
- Check the PHP error log for undefined-index/method notices, especially around model
  method names — CI3 has no static analysis to catch typos the way Laravel/PHPStan might.

## Project layout

```
codeigniter3-app/
├── index.php                  # front controller (loads .env, boots CI3)
├── composer.json               # version marker only — see step 1 above
├── application/
│   ├── config/                 # config.php, database.php, autoload.php, routes.php
│   │   └── routes_modules/     # one route fragment file per domain module
│   ├── core/                   # MY_Controller.php (Api_Controller, Admin_Controller), MY_Model.php
│   ├── controllers/
│   │   ├── api/v1/              # one controller per API module (mirrors Api/V1/*Controller.php)
│   │   ├── admin/                # admin panel controllers (mirrors Livewire components)
│   │   └── Welcome.php            # promo page
│   ├── models/                  # one model per DB table
│   ├── views/
│   │   ├── admin/                # _layout.php + one view per admin page
│   │   └── welcome/               # promo page view
│   ├── libraries/
│   │   └── Token_auth.php         # Sanctum-equivalent bearer token issue/verify
│   └── helpers/
│       └── response_helper.php    # json_response()/json_error()
└── docs/
    ├── SCHEMA_REFERENCE.md         # full table/column reference (shared DB)
    └── SETUP.md                    # this file
```
