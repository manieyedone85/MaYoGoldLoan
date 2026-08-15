# Conventions for porting a module to this CodeIgniter 3 app

Read this fully before writing any code. This project mirrors the Laravel app one
directory up (`../app/Http/Controllers/Api/V1/*.php`, `../routes/api.php`,
`../app/Http/Livewire/Admin/*` for the admin panel) into CodeIgniter 3. Your job is to
port ONE group of modules — port behavior faithfully, translated to CI3 idioms below.
Do not touch files outside your assigned module group (see the "Ownership" note in your
task prompt) — other agents are porting other modules in parallel against these same
conventions, and stepping on a shared file will cause conflicts.

## Already built (read these before writing anything new)

- `application/core/MY_Model.php` — base CRUD every model extends (`all`, `find`,
  `first`, `paginate`, `insert`, `update`, `delete`, `count`). Read it; don't redefine
  these methods per-model.
- `application/core/MY_Controller.php` — `Api_Controller` (base for every
  `controllers/api/v1/*.php`) and `Admin_Controller` (base for every
  `controllers/admin/*.php`).
- `application/libraries/Token_auth.php` — bearer token issue/verify (this port's
  Sanctum replacement). Already wired into `Api_Controller::require_auth()`.
- `application/helpers/response_helper.php` — `json_response($body, $status=200)` and
  `json_error($message, $status=422)`.
- `application/controllers/api/v1/Auth.php` — full reference implementation: how to
  read JSON bodies (`$this->json_input()`), call `require_auth()`/`require_role()`/
  `require_device_binding()`, and shape responses.
- `application/controllers/api/v1/Master.php` — reference for a simpler,
  role-gated-in-constructor controller.
- `application/models/User_model.php`, `Role_model.php`, `Branch_model.php`,
  `Customer_model.php`, `Loan_model.php`, `Loan_product_model.php`,
  `User_otp_model.php`, `User_device_binding_model.php`,
  `Personal_access_token_model.php` — already exist. **Reuse these, do not redeclare
  them.** If you need a new finder method on one of them (e.g. `Loan_model::foo()`),
  add the method to the existing file rather than creating a duplicate model class.
- `application/controllers/admin/Dashboard.php` + `application/views/admin/dashboard.php`
  + `application/views/admin/_layout.php` + `application/views/admin/login.php` — the
  reference pattern for admin panel pages (only relevant if your task is the admin panel).
- `docs/SCHEMA_REFERENCE.md` — every table/column in the shared MySQL schema.

## Directory / naming conventions

- One API controller per Laravel controller: `Api\V1\FooController` →
  `application/controllers/api/v1/Foo.php`, class `Foo extends Api_Controller`.
- Controller methods: `camelCase` Laravel method names become `snake_case` CI3 method
  names (e.g. `sendOtp()` → `send_otp()`).
- One model per DB table: `jewellery_items` → `application/models/Jewellery_item_model.php`,
  class `Jewellery_item_model extends MY_Model` with `protected $table = 'jewellery_items';`.
  Use the **singular** of the table name before `_model` (matches the existing files).
- Admin panel: one controller per Laravel Livewire component under
  `application/controllers/admin/`, one plain-PHP view per page under
  `application/views/admin/`, rendered via `$this->render('view_name', $data)` (see
  `Dashboard.php`/`dashboard.php` for the pattern). No Livewire/AJAX reactivity needed —
  plain forms posting to a controller action, then redirect, is fine and matches CI3
  norms.

## Routes — CRITICAL: only add your own fragment file

Do **not** edit `application/config/routes.php` directly. Instead create ONE new file
per module at `application/config/routes_modules/api_<module>.php` (API) or it's fine to
add admin routes directly since those already exist in `routes.php` under the "Admin
panel routes" section — if your task is a NEW admin page not already routed there, add
your routes only into `routes.php`'s existing admin section, appending below the last
`$route['admin/...']` line (don't touch the API `$route[...]` block or other admin
lines).

Route fragment format (see `routes_modules/api_auth.php` and `api_master.php` for
real examples):
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['api/v1/<path>']['get']  = 'api/v1/<controller>/<method>';
$route['api/v1/<path>']['post'] = 'api/v1/<controller>/<method>';
```
Route path segments with a Laravel route-model-bound param like `{loan}` become a CI3
numeric segment placeholder `(:num)` plus a `/$1` in the target, e.g.:
```php
$route['api/v1/loan/(:num)/submit-for-approval']['post'] = 'api/v1/loan_approval/submit/$1';
```

## Response shape

Match the Laravel API's JSON shape exactly so this is a drop-in replacement:
- Success: `json_response(array('data' => $thing))` or `json_response(array('data' => $thing), 201)` for creates.
- Simple message: `json_response(array('message' => 'Loan approved.'))`.
- Validation/business error: `json_error('Some message.')` (422) or
  `json_error('Forbidden message.', 403)` / `json_error('Not found.', 404)` etc.

## Auth / authorization pattern per controller method

Look at how `Auth.php` and `Master.php` do it. Typical pattern inside a method:
```php
public function approve($loan_id)
{
    $user = $this->require_auth();
    $this->require_device_binding(); // only for BRANCH_EXECUTIVE/APPRAISER/CASHIER-restricted routes — check the Laravel route's middleware list
    $role = $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN')); // only if the Laravel route has role: middleware
    // ... business logic using $this->json_input() / $this->input->get(...) / models
}
```
If EVERY method in a controller needs the same auth/role, do it once in `__construct()`
after `parent::__construct()` (see `Master.php`).

## No Eloquent — translate relationships/business logic manually

CI3's Query Builder has no relationships, model events, or casts. Where the Laravel
controller relies on an Eloquent relation (`$loan->customer`, `$item->images`, etc.),
either:
- add a small join/lookup method to the relevant `_model.php` (see
  `Loan_model::with_relations()` for the pattern), or
- do a second query in the controller method (fine for read-only detail pages; CI3 apps
  commonly do this).

Where the Laravel controller does business logic inline (fee/GST/eligibility math,
loan account number generation, maker-checker checks, cash disbursement caps, etc.),
port that logic verbatim into the CI3 controller method — read the actual Laravel
source file for the exact formula/rule, don't guess. Your primary source of truth for
each module's behavior is the real file at
`../app/Http/Controllers/Api/V1/<Name>Controller.php` and the matching block in
`../routes/api.php`. Read those before writing the port.

## Timestamps / soft deletes / JSON columns

- Every table has `created_at`/`updated_at` — `MY_Model::insert()`/`update()` already
  set these for you; don't set them yourself.
- `customers.deleted_at` is a soft-delete column — see `Customer_model::all()` for how
  to exclude soft-deleted rows; don't hard-delete customers.
- Columns typed `json` in the Laravel migrations (`audit_logs.before_value`/
  `after_value`, `sync_queues.payload`, `sync_conflict_logs.server_value`/
  `client_value`) — encode with `json_encode()` before insert, `json_decode($x, true)`
  after read.

## What NOT to do

- Don't reimplement `MY_Model`'s CRUD methods per-model.
- Don't redeclare `User_model`, `Role_model`, `Branch_model`, `Customer_model`,
  `Loan_model`, `Loan_product_model` — extend/use what exists.
- Don't touch `application/config/routes.php`'s existing lines — only add new lines/
  files as described above.
- Don't invent new response shapes — match the `data`/`message` convention above.
- Don't add a `system/` folder or vendor CI3 framework files — that's out of scope,
  see `docs/SETUP.md`.
- Don't run `php` — it isn't installed in this environment. Write carefully; there is
  no linter/test run to catch mistakes before a human tries it.
