# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MaYoGL is a **CodeIgniter 3 PHP web application** — a port of a parent Laravel app — for managing a gold loan business. It reuses the same MySQL schema as the parent Laravel app and can point at the same database. The app has three entry points:

- `/` — public promo page
- `/admin/*` — session-authenticated admin panel for staff
- `/api/v1/*` — JSON REST API with bearer token auth (for mobile apps)

## Development Server

```bash
# 1. Ensure system/ folder is present (not tracked in repo):
git clone https://github.com/bcit-ci/CodeIgniter.git /tmp/ci3 && cp -r /tmp/ci3/system ./system

# 2. Configure environment:
cp .env.example .env   # then edit CI_BASE_URL, CI_ENCRYPTION_KEY, DB_*

# 3. Run:
php -S localhost:8080
```

There is **no build pipeline, no test suite, no linter**. Development is direct PHP execution. See `docs/SETUP.md` for database initialization and admin seeding.

> **Warning:** This port was authored without a PHP interpreter available — it has not been executed or tested. Verify by loading `/`, walking `/admin/login`, and hitting `/api/v1/auth/login` with curl before trusting it.

## Architecture

### Core Abstractions (read before writing any code)

| File | Purpose |
|---|---|
| `application/core/MY_Model.php` | Base CRUD (`all`, `find`, `first`, `paginate`, `insert`, `update`, `delete`, `count`) — never reimplement these per-model |
| `application/core/MY_Controller.php` | `Api_Controller` (base for all `api/v1/` controllers) and `Admin_Controller` |
| `application/libraries/Token_auth.php` | Bearer token issue/verify (replaces Laravel Sanctum); wired into `Api_Controller::require_auth()` |
| `application/helpers/response_helper.php` | `json_response($body, $status=200)` and `json_error($message, $status=422)` |

### Reference Implementations

- **`application/controllers/api/v1/Auth.php`** — canonical API controller: how to read JSON bodies (`$this->json_input()`), call `require_auth()` / `require_role()` / `require_device_binding()`, and shape responses.
- **`application/controllers/api/v1/Master.php`** — simpler constructor-level auth/role pattern.
- **`application/controllers/admin/Dashboard.php`** + `application/views/admin/_layout.php` — canonical admin panel pattern.

### Porting from Laravel

This CI3 port mirrors the parent Laravel app one directory up (`../app/Http/Controllers/Api/V1/*.php`, `../routes/api.php`, `../app/Http/Livewire/Admin/*`). When porting a module, **read the actual Laravel source file first** — don't guess business logic (fee/GST/eligibility math, loan account number generation, maker-checker checks, cash disbursement caps, etc.).

## Naming & File Conventions

- **API controllers:** `Api\V1\FooController` → `application/controllers/api/v1/Foo.php`, class `Foo extends Api_Controller`
- **Method names:** `camelCase` Laravel → `snake_case` CI3 (e.g. `sendOtp()` → `send_otp()`)
- **Models:** one per DB table; `jewellery_items` → `application/models/Jewellery_item_model.php`, class `Jewellery_item_model extends MY_Model` with `protected $table = 'jewellery_items';` (singular before `_model`)
- **Admin controllers:** one per Laravel Livewire component under `application/controllers/admin/`; views under `application/views/admin/`; rendered via `$this->render('view_name', $data)`

## Routes — Critical Rule

**Never edit `application/config/routes.php` directly for API routes.** Instead, create one fragment file per API module at `application/config/routes_modules/api_<module>.php`. Admin routes may be appended to the existing admin section in `routes.php`.

Fragment format:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['api/v1/<path>']['get']  = 'api/v1/<controller>/<method>';
$route['api/v1/<path>']['post'] = 'api/v1/<controller>/<method>';

// Laravel route-model-bound params become (:num)/$1:
$route['api/v1/loan/(:num)/submit-for-approval']['post'] = 'api/v1/loan_approval/submit/$1';
```

## Response Shape

Match the Laravel API exactly:

```php
json_response(array('data' => $thing));          // 200 success
json_response(array('data' => $thing), 201);     // created
json_response(array('message' => 'Done.'));       // simple message
json_error('Some message.');                      // 422 validation/business error
json_error('Forbidden.', 403);                   // other error codes
```

## Auth Pattern Per Controller Method

```php
public function approve($loan_id)
{
    $user = $this->require_auth();
    $this->require_device_binding(); // only for BRANCH_EXECUTIVE/APPRAISER/CASHIER-restricted routes
    $role = $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER'));
    // business logic ...
}
```

If all methods need the same auth/role, do it once in `__construct()` after `parent::__construct()`.

## Database Patterns

- `MY_Model::insert()` / `update()` already set `created_at` / `updated_at` — don't set them manually.
- `customers.deleted_at` is a soft-delete column — never hard-delete customers (see `Customer_model::all()` for how to exclude soft-deleted rows).
- JSON columns (`audit_logs.before_value`, `sync_queues.payload`, etc.) — use `json_encode()` before insert, `json_decode($x, true)` after read.
- No Eloquent: for relationships, either add a join/lookup method to the `_model.php` (see `Loan_model::with_relations()`) or do a second query in the controller.

## Pre-existing Models (reuse, do not redeclare)

`User_model`, `Role_model`, `Branch_model`, `Customer_model`, `Loan_model`, `Loan_product_model`, `User_otp_model`, `User_device_binding_model`, `Personal_access_token_model`

To add a new finder, add a method to the existing file — don't create a duplicate class.

## Key Reference Docs

- `docs/SCHEMA_REFERENCE.md` — full table/column definitions for the shared MySQL database (40+ tables, enum values, business rules)
- `docs/CONVENTIONS.md` — detailed porting conventions (read before writing any new module)
- `docs/SETUP.md` — environment setup and database initialization
