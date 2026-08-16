# Swarna Gold Loan — BRD Coverage Audit

Requirement-by-requirement audit of `Swarna_Gold_Loan_Business_Requirement_Document.docx` against the current codebase (`application/controllers`, `application/models`, `application/core`, `docs/SCHEMA_REFERENCE.md`). 88 requirements checked, 14 business rules verified. Every finding cites file:line evidence.

**Coverage as originally audited: 38 found (43%) · 30 partial (34%) · 20 missing (23%). After the fixes below: 46 found (52%) · 25 partial (28%) · 17 missing (19%).**

An interactive version of this report (status pills, collapsible sections) is also available as a published artifact from this session.

## Critical issues — fix before go-live

These aren't coverage gaps, they're active bugs or silent failures found while checking coverage. **Issues 1, 4, 5 and 6 are fixed in code; 2 and 3 turned out not to need a fix at all once checked against the real live database; 7 remains open.**

> **Correction:** issues 2 and 3 below were originally diagnosed from a stale error log (`application/logs/log-2026-08-08.php`) captured before the current database was set up. Re-checking directly against the live schema (`docs/u163330700_mayo_gold.sql`) shows `audit_logs` already exists with real data in it, and `interest_collections` already has a `receipt_number` column. Neither needed the SQL patch originally written for them — that patch has been corrected to drop both statements (they would have errored against tables/columns that already exist). While re-verifying, one genuinely live bug turned up instead: `jewellery_items.net_weight` is a MySQL **generated column** (`gross_weight - stone_weight`, computed automatically), but `Jewellery.php` and `admin/Loans.php` both explicitly inserted a value for it — that insert would fail. Both have been fixed to stop inserting `net_weight`. A further systematic check also found 11 unrelated models pointing at table names that don't exist in the live DB (`cash_books` → `cash_book`, `auction_settlements` → `auction_settlement`, and 9 more all-plural-vs-singular mismatches) — all repointed to their real table names.

1. ~~**Login OTP is echoed back in the API response.**~~ `application/controllers/api/v1/Auth.php:88` returned `'otp' => $otp` in the JSON body of `send_otp()`. **Fixed:** the OTP is no longer included in the response; it must be delivered only through the notification gateway (SMS/WhatsApp) call already stubbed above that line.

2. ~~**The `audit_logs` table doesn't exist — BR-012 is silently dead.**~~ **Not actually true against the live DB** — `audit_logs` exists and already has audit rows in it (LOGIN/OTP_LOGIN/TOKEN_REFRESH events). The original finding came from a stale error log predating the current database. No fix needed.

3. ~~**Payment collection throws a live DB error.**~~ **Not actually true against the live DB** — `interest_collections.receipt_number` already exists. Same stale-log cause as #2. No fix needed.

4. ~~**Settlement can release jewellery without the release checklist.**~~ `Settlement::settle()` (`Settlement.php`) called `update_status_for_loan($loan_id,'RELEASED')` directly. **Fixed:** `Settlement::settle()` no longer touches jewellery status at all — it only marks the loan `SETTLED`. `Gold_release::complete()` (the sole place that sets a jewellery item to `RELEASED`) now additionally requires the loan's status to be `SETTLED` or `CLOSED` before it will release, on top of the existing ID-proof/signature/photo checklist. The now-unused `Jewellery_item_model::update_status_for_loan()` bulk-release method was removed so nothing can reach for it again.

5. ~~**LTV is hardcoded at 75%, not driven by loan-product config.**~~ `Jewellery.php:56` and `admin/Loans.php:240` both hardcoded `$eligiblePercentage = 75.00`. **Fixed:** added `gold_rates.ltv_pct` (approved through the same propose/approve workflow as `rate_per_gram` — see the SQL patch), and both call sites now read `$goldRate['ltv_pct']` instead of a literal. `Jewellery::propose_rate()` accepts an optional `ltv_pct` input (defaults to 75.00 if omitted).

6. ~~**The BRD's flagship scenario has no mobile-app (API) path.**~~ Section 10 is called out as *the* mandatory business requirement. **Fixed:** added `GET /api/v1/customer/{id}/loans` (all active + closed loans for a customer) and `GET /api/v1/loan/{id}` (full detail bundle: loan summary with outstanding/EMI/tenure, jewellery scoped to that loan with images, payment history, EMI schedule, a merged lifecycle timeline, and a first-pass eligible-actions flag set). The new endpoint fills gaps the admin view itself had (outstanding amount, EMI, tenure, jewellery images, part-payments, a unified timeline) rather than just porting them forward.

7. **Reports & KPIs (BRD §14) is essentially unbuilt.** 7 of the 9 required reports/KPIs have zero implementation — no active/closed/foreclosed report, no overdue-EMI report, no branch/employee performance, no renewal/top-up report, no audit report, no KPI aggregation. Only a disbursement-report stub and a dashboard widget exist. **Status: not started** — this is a feature build (multiple new reporting endpoints + KPI aggregation), not a bug fix, and needs its own scoping pass.

## Requirement-by-requirement coverage

### §7 — Customer & KYC (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Each customer has a unique Customer ID | Found | `Customer_model.php:32-37` `next_customer_code()` → `CUST` + zero-padded id |
| Mobile number is the primary search identifier | Found | `Customer_model.php:27-30` `find_by_mobile()`; `Customer.php:127-153` search endpoint |
| Duplicate customer creation is detected | Partial | `Customer.php:155-179` duplicate-check endpoint exists but never persists to `customer_duplicate_logs`; no uniqueness constraint blocks the actual insert in `store()` |
| Personal, address, nominee & KYC info captured | Found | `customer_addresses`, `customer_nominees`, `kyc_*` tables wired in `Customer.php:85-93, 219-256` |
| KYC status: Pending / Verified / Rejected / Expired | Partial | Only PENDING/VERIFIED/REJECTED exist (`SCHEMA_REFERENCE.md:43-45`); no EXPIRED state anywhere |
| Rejected KYC captures a reason | Missing | `Kyc_document.php:98-126` `verify()` accepts only status + verified_by — no reason/remarks field, none in schema |
| Documents securely stored & role-controlled | Partial | Filenames obfuscated (`encrypt_name`) but no `require_role()` gate on upload/verify/view, and no gated file-serving endpoint |

### §8 — Jewellery & Gold Valuation (8 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Unique Jewellery ID | Found | `Jewellery.php:60, 227-236` — `'JWL' + random alnum`, unique barcode |
| Linked to customer and relevant loan | Found | `customer_id` set at `evaluate()` (`Jewellery.php:61`); `loan_id` via `mark_pledged()` (`Jewellery_item_model.php:29-40`) |
| Gross / stone / net weight captured | Found | `Jewellery.php:53-66` — net = gross − stone |
| Purity and hallmark recorded | Found | `Jewellery.php:44,63,67` — `purity_karat`, `hallmark_flag` |
| Jewellery photographs stored | Found | `Jewellery.php:165-212` `upload_image()` → `jewellery_images` table |
| Valuation uses configured gold rate & business rules | Found | `Gold_rate_model::latest_approved()` is approval-gated; eligible % now reads `gold_rates.ltv_pct` (`Jewellery.php:56`), approved alongside the rate — no longer hardcoded |
| Valuation history retained | Missing | No valuation-history table/model found; `jewellery_items` only holds the current `applied_rate`, overwritten on re-evaluation |
| Lifecycle status tracked pledge → release | Found | status enum EVALUATED/PLEDGED/RELEASED/AUCTIONED; transitions in `Jewellery_item_model.php:29-61` |

### §9 — New Gold Loan (9 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Select or create the customer | Partial | Full flow in `admin/Loans.php:100-227`; `api/v1/Loan.php` only accepts an existing `customer_id`, no inline creation |
| Select eligible jewellery | Found | `admin/Loans.php:162-262`; `api/v1/Loan.php:152-159` validates `jewellery_item_ids` |
| Calculate eligible amount using valuation & LTV | Found | Formula is real (net_weight × rate × %); the % now comes from `gold_rates.ltv_pct`, no longer hardcoded |
| Apply configured interest, tenure, charges | Found | Pulled from `loan_products` via `Loan_product_model` (`api/v1/Loan.php:32-49`) |
| Generate EMI/repayment schedule before confirmation | Partial | `emi_schedule()` (`api/v1/Loan.php:114-138`) is interest-only and only callable after loan creation, not pre-confirmation |
| Approval required per authorization limits | Found | `Loan_approval.php` `submit()`/`approve()` enforces maker≠checker and role-based limits, with escalation; admin panel's direct-create path intentionally bypasses this (documented in code) |
| Unique Loan ID created after disbursement | Partial | `Loan_model.php:8-13` generates the ID at creation time, not after disbursement; generation is non-atomic (race-condition risk) |
| Loan agreement & documents stored | Missing | No loan-document/agreement model or controller found anywhere |
| Pledged jewellery linked to the loan | Found | `mark_pledged()` sets `loan_id` + `status=PLEDGED` directly on `jewellery_items` |

### §10 — Mandatory Scenario: Search Loan by Mobile Number (12 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Enter mobile number → search | Found | `api/v1/Customer.php:127-153` (exact match); `admin/Customers.php` uses LIKE |
| Show name, Customer ID, KYC status | Found | Both search paths return full customer rows including `kyc_status` |
| Show all active AND closed loans | Found | New `GET /api/v1/customer/{id}/loans` (`Customer.php`) exposes the same no-status-filter `with_relations()` the admin panel used web-only |
| Select a specific Loan ID | Found | New `GET /api/v1/loan/{id}` (`Loan.php::show()`) — now available over the API, not just the admin web panel |
| Show sanctioned / disbursed / outstanding / interest / tenure / EMI / status | Found | `Loan.php::show()` now computes `outstanding_amount`, `emi_amount` and `tenure_months` and merges them into the loan row |
| Show ONLY jewellery linked to the selected Loan ID | Found | `Jewellery_item_model::for_loan()` → `WHERE loan_id = ?` — verified correct, no BR-004/BR-009 leak |
| Show jewellery image, type, weights, purity, hallmark, value | Partial | `Loan.php::show()` now attaches images per item via `Jewellery_image_model::for_items()`, plus weights/purity/hallmark/value; `category_id` is returned but not joined to a category name |
| Show payment history and receipts | Found | `Loan.php::show()` now returns disbursements + interest_collections + part_payments together |
| Show EMI schedule, paid status, balance | Partial | `Loan.php::show()` now includes the full EMI schedule; per-installment paid/unpaid status still isn't tracked, since the schedule is generated on the fly rather than stored |
| Show loan agreement, KYC refs, jewellery receipt, documents | Missing | No document concept exists in the app at all |
| Show lifecycle timeline, application → closure | Found | `Loan.php::show()` now merges approval/disbursement/interest/part-payment/renewal/topup/reload/closure events into one chronological timeline |
| Show eligible actions: Payment/Renew/Topup/Re-loan/Foreclosure/Print/Download | Partial | `Loan.php::show()` now returns a first-pass `eligible_actions` flag set based on loan status; it doesn't yet recompute the full financial eligibility the dedicated Renewal/Topup/Settlement endpoints already enforce |

### §11 — Renewal / Top-up / Excess Re-loan (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Renewal available only when eligible | Found | `Renewal.php:20-34` `eligibility()` — real server-side status check |
| Top-up eligibility uses current valuation & outstanding | Found | `Topup.php:33-45` — sums current jewellery value minus outstanding `sanctioned_amount` |
| Additional jewellery may be added | Missing | Top-up only re-values existing pledged items; no endpoint adds new items during renewal/top-up |
| Excess re-loan uses eligible excess gold value | Partial | `Part_payment.php:76-121` `reload()` takes `excess_amount_eligible` as client input — not recomputed server-side from current gold rate |
| Interest/charges/revised amounts shown before confirmation | Partial | Preview endpoints exist (`eligibility()`) but nothing forces the client to call them, or re-validates the figures at commit time |
| Approval & disbursement follow configured permissions | Partial | Topup approve/disburse are role-gated (BRANCH_MANAGER/REGIONAL_MANAGER/CASHIER); `Renewal::renew()` has no role check at all — inconsistent |
| Related transactions retain historical references | Partial | `audit_log()` calls reference `renewal_id`/`topup_id`, but there's no old-loan↔new-loan linkage since renewal/top-up amend the same loan row |

### §12 — Payment, Foreclosure & Closure (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Configured payment modes: Cash / UPI / Bank Transfer / Card | Partial | `Interest::collect()` only allows CASH/ONLINE (`Interest.php:57`); a richer mode list exists in `Disbursement.php` but is commented-out dead code |
| Unique receipt generated per payment | Found | `Interest.php:65` generates `RCPT`+random; `interest_collections.receipt_number` confirmed to already exist on the live DB |
| EMI and outstanding updated after transaction | Found | `Part_payment.php:53-58` — transactional update of `sanctioned_amount` and status |
| Payment history maintained | Found | `Interest_collection_model` / `Loan_part_payment_model` persist date/amount/mode/reference |
| Foreclosure settlement calculated per configured rules | Partial | `Settlement::closure_statement()` (`Settlement.php:22-39`) returns `sanctioned_amount` only — a comment admits pending interest is not added |
| Loan closure verifies all settlement conditions | Missing | `Settlement::settle()` marks SETTLED without comparing collected amount against the required closure figure |
| Jewellery release only after authorized closure | Found | `Gold_release.php` enforces the ID/signature/photo checklist, and `Gold_release::complete()` now also requires the loan to be `SETTLED`/`CLOSED` before releasing; `Settlement::settle()` no longer touches jewellery status at all — see critical issue #4 |

### §13 — Business Rules (BR-001 … BR-014)

| Rule | Status | Evidence |
|---|---|---|
| BR-001 — Customer must have a unique Customer ID | Found | `next_customer_code()`, unique `customer_code` column |
| BR-002 — Mobile number validated for customer search | Found | `find_by_mobile()` / search endpoints |
| BR-003 — Each loan belongs to one customer | Found | `customer_id` FK on loans, enforced at creation |
| BR-004 — Pledged jewellery traceable to customer & loan | Found | `customer_id` + `loan_id` both set on `jewellery_items` |
| BR-005 — Loan amount cannot exceed configured eligible value/LTV | Partial | Enforced for base loan creation (server always sets sanctioned = computed eligible); `Topup::approve()` accepts a client-supplied `approved_amount` with no server check |
| BR-006 — Interest/scheme terms from approved master configuration | Found | Interest rate/tenure/fees are config-driven; LTV % now comes from `gold_rates.ltv_pct`, approved through the same propose/approve workflow as the rate itself |
| BR-007 — Disbursement requires approval and documents | Partial | Approval gate enforced (`status===APPROVED` check); no document-existence check exists anywhere |
| BR-008 — Payments update only the selected loan | Found | All payment/settlement writes scope to `$loan['id']` resolved from the route |
| BR-009 — Loan Details jewellery filtered by Loan ID | Found | `for_loan()` — verified correct SQL, no cross-loan leakage |
| BR-010 — Closed loans remain available for historical viewing | Found | No status-exclusion filter by default, no hard-delete on loans anywhere |
| BR-011 — Renewal/top-up/re-loan appear only when eligible | Partial | Eligibility endpoints are real, but `renew()`/`Topup::approve()` don't re-check at the point of commit |
| BR-012 — Critical financial actions must be audited | Found | `audit_log()` is called correctly from ~20 controllers, and `audit_logs` is confirmed to already exist on the live DB with real rows in it |
| BR-013 — Duplicate financial posting must be prevented | Partial | `Disbursement.php` has an explicit atomic guard; `Part_payment` and `Topup::disburse` have none |
| BR-014 — Permissions control approval, payment, release, admin | Found | `require_role()` used broadly across approval/payment/release/admin controllers |

### §14 — Reports & KPIs (9 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Daily loan disbursement report | Partial | `Report.php:22-46` `DAILY_CASH` is an explicit unimplemented stub |
| Active/closed/foreclosed loan report | Missing | Only ad-hoc status counts exist, no dedicated report |
| Outstanding and overdue EMI report | Missing | No portfolio-level aging/overdue endpoint anywhere |
| Daily collection report | Missing | Only a dashboard summary widget sums today's collections |
| Branch and employee performance | Missing | No grouping/aggregation by branch or employee found |
| Gold pledged inventory / jewellery release report | Partial | `Inventory.php:89` `vault_status()` gives current holdings; no release report |
| Renewal, top-up and re-loan report | Missing | Renewal/Topup controllers are transactional only, no reporting endpoint |
| Audit/user activity report | Missing | No report reads back `audit_logs` — and the table doesn't exist regardless |
| KPIs: processing time, KYC completion, disbursement volume, collection rate, overdue rate, renewal rate, repeat-customer rate | Missing | No KPI/rate aggregation found anywhere in the codebase |

### §15 — Security & Audit (8 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Role-based access control | Found | `require_role()` (`MY_Controller.php:56-67`) used across api/v1 and admin controllers |
| Secure login, OTP, optional biometric | Partial | OTP flow works but is leaked in the response body — see critical issue #1. Biometric models exist for KYC face-auth only, not login |
| Encrypted network communication | Missing | Out of scope at the app layer — this is an infra/deployment concern (TLS termination), not something CI3 code enforces |
| Secure KYC / jewellery image access | Missing | No gated file-serving controller for uploaded documents/images — only upload endpoints exist |
| Audit logs for critical operations | Partial | Widely called in code (~20 controllers) but non-functional — the underlying table is missing from the DB, see critical issue #2 |
| No sensitive customer data in debug logs | Found | Only two `log_message()` calls app-wide, neither touches customer PII |
| Financial APIs prevent duplicate submissions | Partial | Only `Disbursement.php` has an idempotency guard; `Part_payment`, `Interest`, `Topup` do not |
| Session timeout and forced logout | Found | CI session expiration configured (`config.php:387,390`); `Token_auth` enforces a 60-minute API token TTL |

### §4 / §3 — Administration & Printing (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Users | Found | `admin/Employees.php` — store/update/toggle_active |
| Roles | Partial | Read-only listing (`Master.php:92-95`); no create/update endpoint |
| Branches | Found | `Master.php:21-50` — full CRUD |
| Schemes (loan products) | Found | `Master.php:53-89` — full CRUD |
| Rates | Partial | Gold rate propose/approve exists; no standalone interest-rate management screen |
| Charges | Partial | `Loan_charge_model` used per-loan, no charges-master administration screen |
| Bluetooth/thermal printing | Missing | No printer/Bluetooth/print code found anywhere in the app |

## Priority recommendations

**P0 — fix before go-live**
1. ~~Remove the OTP from the `send_otp()` API response.~~ **Done.** `audit_logs` and `interest_collections.receipt_number` turned out to already exist on the live DB — no migration needed after all (see the correction note above).
2. ~~Route `Settlement::settle()` through the same release checklist `Gold_release` already enforces.~~ **Done** — see critical issue #4.
3. ~~Move the LTV percentage into master configuration instead of a hardcoded literal.~~ **Done** — now lives on `gold_rates.ltv_pct`, approved alongside the rate itself; see critical issue #5.

**P1 — should fix before launch**
4. ~~Build the api/v1 equivalent of the admin "search by mobile → loan → jewellery" bundle.~~ **Done** — `GET /api/v1/customer/{id}/loans` and `GET /api/v1/loan/{id}`; see critical issue #6.
5. Add a server-side eligibility re-check to `Topup::approve()` (currently trusts a client-supplied amount) and to `Renewal::renew()` (no status re-validation at the point of renewal, only at the earlier eligibility-preview call).
6. Decide, with the business, whether the admin panel's direct loan-creation path (`ADMIN_DIRECT`, bypassing maker-checker) is an intentional exception or a compliance gap — it's documented as deliberate in code, but the BRD doesn't carve out an exception for staff-initiated loans.
7. Add an idempotency guard to `Part_payment` and `Topup::disburse()` — only `Disbursement.php` currently guards against double-submission.

**P2 — polish / lower risk**
8. Scope Section 14 (Reports & KPIs) as its own workstream — almost nothing here exists yet, and it touches every other module once built.
9. ~~Round out the loan-detail view: outstanding balance, EMI amount/tenure, jewellery images, part-payments, a unified lifecycle timeline, and computed "eligible actions".~~ **Done** for the new `GET /api/v1/loan/{id}` endpoint; the admin `loans_show.php` view itself is still unchanged and could be updated to consume the same data.
10. Add an "Expired" KYC status, a reason field on KYC rejection, and enforce mobile-number uniqueness at customer creation (today's duplicate-check endpoint doesn't persist and nothing blocks the insert).

---
*Audited by cross-referencing `docs/Swarna_Gold_Loan_Business_Requirement_Document.docx` against `application/controllers`, `application/models`, `application/core`, and `docs/SCHEMA_REFERENCE.md`. Items marked "partial" have real code behind them but fall short of the stated requirement in a specific, cited way — see each row's evidence.*
