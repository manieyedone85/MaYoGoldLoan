# Swarna Gold Loan — BRD Coverage Audit

Requirement-by-requirement audit of `Swarna_Gold_Loan_Business_Requirement_Document.docx` against the current codebase (`application/controllers`, `application/models`, `application/core`, `docs/SCHEMA_REFERENCE.md`). 88 requirements checked, 14 business rules verified. Every finding cites file:line evidence.

**Coverage as originally audited: 38 found (43%) · 30 partial (34%) · 20 missing (23%). After the fixes below: 82 found (93%) · 5 partial (6%) · 1 missing (1%).**

An interactive version of this report (status pills, collapsible sections) is also available as a published artifact from this session.

## Critical issues — fix before go-live

These aren't coverage gaps, they're active bugs or silent failures found while checking coverage. **Issues 1, 4, 5, 6 and 7 are fixed in code; 2 and 3 turned out not to need a fix at all once checked against the real live database.**

> **Correction:** issues 2 and 3 below were originally diagnosed from a stale error log (`application/logs/log-2026-08-08.php`) captured before the current database was set up. Re-checking directly against the live schema (`docs/u163330700_mayo_gold.sql`) shows `audit_logs` already exists with real data in it, and `interest_collections` already has a `receipt_number` column. Neither needed the SQL patch originally written for them — that patch has been corrected to drop both statements (they would have errored against tables/columns that already exist). While re-verifying, one genuinely live bug turned up instead: `jewellery_items.net_weight` is a MySQL **generated column** (`gross_weight - stone_weight`, computed automatically), but `Jewellery.php` and `admin/Loans.php` both explicitly inserted a value for it — that insert would fail. Both have been fixed to stop inserting `net_weight`. A further systematic check also found 11 unrelated models pointing at table names that don't exist in the live DB (`cash_books` → `cash_book`, `auction_settlements` → `auction_settlement`, and 9 more all-plural-vs-singular mismatches) — all repointed to their real table names.

1. ~~**Login OTP is echoed back in the API response.**~~ `application/controllers/api/v1/Auth.php:88` returned `'otp' => $otp` in the JSON body of `send_otp()`. **Correction:** this row previously claimed to be fixed, but the leak was still live — re-verifying against the current code (not just this doc's own prior claim) found `'otp' => $otp` still present. **Actually fixed now:** removed for real; the OTP must be delivered only through the notification gateway (SMS/WhatsApp) call already stubbed above that line. Lesson: a doc saying "fixed" isn't evidence of anything — always re-check the live file.

2. ~~**The `audit_logs` table doesn't exist — BR-012 is silently dead.**~~ **Not actually true against the live DB** — `audit_logs` exists and already has audit rows in it (LOGIN/OTP_LOGIN/TOKEN_REFRESH events). The original finding came from a stale error log predating the current database. No fix needed.

3. ~~**Payment collection throws a live DB error.**~~ **Not actually true against the live DB** — `interest_collections.receipt_number` already exists. Same stale-log cause as #2. No fix needed.

4. ~~**Settlement can release jewellery without the release checklist.**~~ `Settlement::settle()` (`Settlement.php`) called `update_status_for_loan($loan_id,'RELEASED')` directly. **Fixed:** `Settlement::settle()` no longer touches jewellery status at all — it only marks the loan `SETTLED`. `Gold_release::complete()` (the sole place that sets a jewellery item to `RELEASED`) now additionally requires the loan's status to be `SETTLED` or `CLOSED` before it will release, on top of the existing ID-proof/signature/photo checklist. The now-unused `Jewellery_item_model::update_status_for_loan()` bulk-release method was removed so nothing can reach for it again.

5. ~~**LTV is hardcoded at 75%, not driven by loan-product config.**~~ `Jewellery.php:56` and `admin/Loans.php:240` both hardcoded `$eligiblePercentage = 75.00`. **Fixed:** added `gold_rates.ltv_pct` (approved through the same propose/approve workflow as `rate_per_gram` — see the SQL patch), and both call sites now read `$goldRate['ltv_pct']` instead of a literal. `Jewellery::propose_rate()` accepts an optional `ltv_pct` input (defaults to 75.00 if omitted).

6. ~~**The BRD's flagship scenario has no mobile-app (API) path.**~~ Section 10 is called out as *the* mandatory business requirement. **Fixed:** added `GET /api/v1/customer/{id}/loans` (all active + closed loans for a customer) and `GET /api/v1/loan/{id}` (full detail bundle: loan summary with outstanding/EMI/tenure, jewellery scoped to that loan with images, payment history, EMI schedule, a merged lifecycle timeline, and a first-pass eligible-actions flag set). The new endpoint fills gaps the admin view itself had (outstanding amount, EMI, tenure, jewellery images, part-payments, a unified timeline) rather than just porting them forward.

7. ~~**Reports & KPIs (BRD §14) is essentially unbuilt.**~~ 7 of the 9 required reports/KPIs had zero implementation. **Fixed:** see the §14 table below — all 9 are now implemented, centralized in a new `Report_model` shared by `api/v1/Report.php` (`GET /api/v1/reports/{reportCode}`) and a new `admin/Reports.php` + `views/admin/reports.php` (the admin nav and `routes.php` already both referenced `admin/reports`, but the controller/view never existed — a dead 404, fixed alongside this).

## Requirement-by-requirement coverage

### §7 — Customer & KYC (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Each customer has a unique Customer ID | Found | `Customer_model.php:32-37` `next_customer_code()` → `CUST` + zero-padded id |
| Mobile number is the primary search identifier | Found | `Customer_model.php:27-30` `find_by_mobile()`; `Customer.php:127-153` search endpoint |
| Duplicate customer creation is detected | Found | **Fixed:** `Customer::detect_duplicates()` (`Customer.php`), called from `store()`, now persists a `customer_duplicate_log` row (status `PENDING_REVIEW`) for every existing customer sharing the new one's mobile or `aadhaar_hash`. Still doesn't hard-block the insert — a shared mobile can be legitimate (family members) — so this is detection-and-flag-for-review, not a uniqueness constraint. `Customer_duplicate_log_model` also had a latent bug fixed alongside this: it inherited `MY_Model`'s default timestamp handling, which would have tried to write a nonexistent `updated_at` column on every insert. |
| Personal, address, nominee & KYC info captured | Found | `customer_addresses`, `customer_nominees`, `kyc_*` tables wired in `Customer.php:85-93, 219-256` |
| KYC status: Pending / Verified / Rejected / Expired | Found | **Fixed:** `customers.kyc_status` is a plain `VARCHAR(20)`, not a DB enum, so EXPIRED needed no migration — just a reachable transition. Added `PUT /api/v1/customer/{id}/kyc-status` (`Customer::update_kyc_status()`), role-gated to BRANCH_MANAGER/REGIONAL_MANAGER/ADMIN, requiring a `reason` for REJECTED/EXPIRED. This also closes a bigger latent gap: `kyc_status` was previously set to PENDING at creation and never transitioned anywhere else in the codebase. |
| Rejected KYC captures a reason | Found | **Fixed:** added `kyc_documents.rejection_reason` (`docs/migrations/2026_08_16_customer_kyc_fixes.sql`); `Kyc_document::verify()` now requires `reason` when `status=REJECTED` and stores it. |
| Documents securely stored & role-controlled | Found | **Fixed:** `Kyc_document::store()`/`verify()` now call `require_role()` (BRANCH_EXECUTIVE/BRANCH_MANAGER/ADMIN to upload, BRANCH_MANAGER/REGIONAL_MANAGER/ADMIN to verify — neither existed in the reviewed Laravel route/middleware set, so this is a BRD-driven addition, not a strict port). Added a gated `GET /api/v1/kyc/document/{id}/file` (`Kyc_document::download()`) that streams the file after an auth+role check, instead of the previous approach of relying solely on an obfuscated filename under the public `uploads/` path. |

### §8 — Jewellery & Gold Valuation (8 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Unique Jewellery ID | Found | `Jewellery.php:60, 227-236` — `'JWL' + random alnum`, unique barcode |
| Linked to customer and relevant loan | Found | `customer_id` set at `evaluate()` (`Jewellery.php:61`); `loan_id` via `mark_pledged()` (`Jewellery_item_model.php:29-40`) |
| Gross / stone / net weight captured | Found | `Jewellery.php:53-66` — net = gross − stone |
| Purity and hallmark recorded | Found | `Jewellery.php:44,63,67` — `purity_karat`, `hallmark_flag` |
| Jewellery photographs stored | Found | `Jewellery.php:165-212` `upload_image()` → `jewellery_images` table |
| Valuation uses configured gold rate & business rules | Found | `Gold_rate_model::latest_approved()` is approval-gated; eligible % now reads `gold_rates.ltv_pct` (`Jewellery.php:56`), approved alongside the rate — no longer hardcoded |
| Valuation history retained | Found | **Fixed:** added `jewellery_valuation_history` (`docs/migrations/2026_08_16_jewellery_valuation_history.sql`) — one row per valuation event. There was actually no re-evaluation flow at all (`evaluate()` only ever inserted a new item; nothing updated an existing one), so the fix includes both the history table and a new `POST /api/v1/jewellery/{id}/re-evaluate` (`Jewellery::re_evaluate()`, role APPRAISER/ADMIN, only while EVALUATED/PLEDGED) that re-prices at the current approved rate and snapshots into history — plus `GET /api/v1/jewellery/{id}/valuation-history` to read it back. `evaluate()` (API) and `admin/Loans::store()` both now write the initial history row too. |
| Lifecycle status tracked pledge → release | Found | status enum EVALUATED/PLEDGED/RELEASED/AUCTIONED; transitions in `Jewellery_item_model.php:29-61` |

### §9 — New Gold Loan (9 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Select or create the customer | Found | **Fixed:** `Loan::store()` now accepts either an existing `customer_id` or an inline `customer` object (name/mobile/email/dob/gender/address), validated and created via `Loan::_validate_customer_input()`/`_resolve_customer()` (`api/v1/Loan.php`) — same field rules as `Customer::store()`. `admin/Loans.php` already had both paths; the API only had one. |
| Select eligible jewellery | Found | `admin/Loans.php:162-262`; `api/v1/Loan.php:152-159` validates `jewellery_item_ids` |
| Calculate eligible amount using valuation & LTV | Found | Formula is real (net_weight × rate × %); the % now comes from `gold_rates.ltv_pct`, no longer hardcoded |
| Apply configured interest, tenure, charges | Found | Pulled from `loan_products` via `Loan_product_model` (`api/v1/Loan.php:32-49`) |
| Generate EMI/repayment schedule before confirmation | Found | **Fixed:** `POST /api/v1/loan/calculate` (`Loan::calculate()`) now returns an `emi_schedule` alongside the fee breakdown, computed from the same values `store()` would use (`sanctioned_amount == eligible_amount` at creation), via a new `_build_emi_schedule_from_values()` shared with `emi_schedule()`/`show()`. The schedule is still interest-only (bullet repayment) — that's the standard Indian gold-loan repayment model, not a bug; the actual gap was that it wasn't previewable before confirmation at all. |
| Approval required per authorization limits | Found | `Loan_approval.php` `submit()`/`approve()` enforces maker≠checker and role-based limits, with escalation; admin panel's direct-create path intentionally bypasses this (documented in code) |
| Unique Loan ID created after disbursement | Found | **Fixed:** `loans.loan_account_number` is now nullable (`docs/migrations/2026_08_16_new_gold_loan_fixes.sql`) and left unset by both `Loan::store()` and `admin/Loans::store()`; `Disbursement::disburse()` assigns it for the first time, atomically, as part of the existing conditional `WHERE status='APPROVED'` update. `Loan_model::loan_account_number_for_id()` derives the number from the row's own AUTO_INCREMENT `id` instead of a racy `SELECT MAX(id)+1`, so the earlier non-atomic generation is also gone, not just moved later. Admin/dashboard views fall back to "Pending disbursement" for loans not yet disbursed. |
| Loan agreement & documents stored | Found | **Fixed:** added `loan_documents` (`docs/migrations/2026_08_16_new_gold_loan_fixes.sql`), `Loan_document_model`, and a new `Loan_document.php` controller (upload/list/gated-download), mirroring the existing `Kyc_document.php` shape. Wired into `Loan::show()`'s bundle as `documents`. |
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
| Show loan agreement, KYC refs, jewellery receipt, documents | Partial | **Improved:** `Loan::show()` now includes `documents` (BRD §9 "Loan agreement & documents stored", `loan_documents` table). KYC refs (on the customer, via `Kyc_document::index()`) and a distinct jewellery-receipt concept still aren't surfaced in this bundle. |
| Show lifecycle timeline, application → closure | Found | `Loan.php::show()` now merges approval/disbursement/interest/part-payment/renewal/topup/reload/closure events into one chronological timeline |
| Show eligible actions: Payment/Renew/Topup/Re-loan/Foreclosure/Print/Download | Partial | `Loan.php::show()` now returns a first-pass `eligible_actions` flag set based on loan status; it doesn't yet recompute the full financial eligibility the dedicated Renewal/Topup/Settlement endpoints already enforce |

### §11 — Renewal / Top-up / Excess Re-loan (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Renewal available only when eligible | Found | **Also fixed:** `Renewal::eligibility()` was already a real server-side check, but `renew()` itself never checked the loan's status — it would "renew" a DRAFT or CLOSED loan just as happily as an ACTIVE one. Now gated to ACTIVE/PART_PAID like the preview. |
| Top-up eligibility uses current valuation & outstanding | Found | `Topup.php` — sums current jewellery value minus outstanding `sanctioned_amount`, now shared via `_current_eligible_topup()` with `approve()` too |
| Additional jewellery may be added | Found | **Fixed:** added `POST /api/v1/loan/{id}/topup/add-jewellery` (`Topup::add_jewellery()`, role APPRAISER/BRANCH_MANAGER/ADMIN) — pledges already-evaluated, unpledged items belonging to the same customer onto the loan, raising `eligible_amount`. Actually advancing more cash for that added collateral still goes through the existing approve()/disburse() flow, unchanged. |
| Excess re-loan uses eligible excess gold value | Found | **Fixed:** `Part_payment::reload()` now recomputes `excess_amount_eligible` server-side (`_current_excess_amount_eligible()`, same formula as `Topup::eligibility()`) and rejects `reload_amount` above it, instead of trusting the client-supplied figure. |
| Interest/charges/revised amounts shown before confirmation | Found | **Fixed:** every commit-time endpoint now re-validates against a server-computed figure instead of trusting client input: `Renewal::renew()` requires `interest_paid` to cover a recomputed `interest_due`; `Topup::approve()` rejects `approved_amount` over a recomputed `eligible_topup_amount` (previously it was echoed straight through — the stored `eligible_topup_amount` never reflected an actual ceiling); `Part_payment::reload()` per above. |
| Approval & disbursement follow configured permissions | Found | **Fixed:** `Renewal::renew()` and `Part_payment::reload()` had no role check at all despite moving money, unlike every sibling action (`Interest::collect()`, `Part_payment::part_payment()`, `Topup::disburse()` are all role:CASHIER). Both now require CASHIER/ADMIN, matching the pattern. |
| Related transactions retain historical references | Found | **Fixed:** added `previous_due_date` to `loan_renewals` and `previous_sanctioned_amount` to `loan_topups`/`loan_reloads` (`docs/migrations/2026_08_16_renewal_topup_reload_fixes.sql`), populated at write time. Scoped deliberately: rearchitecting renewal/top-up into separate old-loan/new-loan records (the literal reading of "old-loan↔new-loan linkage") would touch every query/view/FK assuming one loan row per account and was judged out of proportion to this pass; this makes the pre-change state queryable directly off the domain row instead of only inside `audit_log`'s JSON blob. |

### §12 — Payment, Foreclosure & Closure (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Configured payment modes: Cash / UPI / Bank Transfer / Card | Found | **Fixed:** `Interest::collect()` now allows CASH/UPI/BANK_TRANSFER/CARD instead of CASH/ONLINE. The "richer mode list" in `Disbursement.php` turned out to be doubly broken, not just commented-out: even restored as-written it would have inserted a string code into `loan_disbursements.mode`, which is actually a FK (bigint) to `disbursement_mode_master`, not a free-text column — so every disbursement would have hit a FK-constraint error the moment the dead code was uncommented. Added `Disbursement_mode_model` and now resolve the code to its id before insert. |
| Unique receipt generated per payment | Found | `Interest.php:65` generates `RCPT`+random; `interest_collections.receipt_number` confirmed to already exist on the live DB |
| EMI and outstanding updated after transaction | Found | `Part_payment.php:53-58` — transactional update of `sanctioned_amount` and status |
| Payment history maintained | Found | `Interest_collection_model` / `Loan_part_payment_model` persist date/amount/mode/reference |
| Foreclosure settlement calculated per configured rules | Found | **Fixed:** `Settlement::closure_statement()` now computes accrued interest the same way `Interest::due()` does (months elapsed × monthly interest, minus collected) via a shared `_compute_closure_statement()`, and folds it into `total_payable_to_close` instead of returning `sanctioned_amount` alone. |
| Loan closure verifies all settlement conditions | Found | **Fixed:** `Settlement::settle()` now recomputes the same closure statement and rejects `total_amount_collected` if it falls short of `total_payable_to_close`, and also requires the loan be ACTIVE/PART_PAID before settling (previously unchecked, like the analogous gaps found in `Renewal::renew()`/`Topup::approve()` under §11). |
| Jewellery release only after authorized closure | Found | `Gold_release.php` enforces the ID/signature/photo checklist, and `Gold_release::complete()` now also requires the loan to be `SETTLED`/`CLOSED` before releasing; `Settlement::settle()` no longer touches jewellery status at all — see critical issue #4 |

### §13 — Business Rules (BR-001 … BR-014)

| Rule | Status | Evidence |
|---|---|---|
| BR-001 — Customer must have a unique Customer ID | Found | `next_customer_code()`, unique `customer_code` column |
| BR-002 — Mobile number validated for customer search | Found | `find_by_mobile()` / search endpoints |
| BR-003 — Each loan belongs to one customer | Found | `customer_id` FK on loans, enforced at creation |
| BR-004 — Pledged jewellery traceable to customer & loan | Found | `customer_id` + `loan_id` both set on `jewellery_items` |
| BR-005 — Loan amount cannot exceed configured eligible value/LTV | Found | Enforced for base loan creation (server always sets sanctioned = computed eligible). **Also fixed under §11:** `Topup::approve()` now recomputes `eligible_topup_amount` server-side (`_current_eligible_topup()`) and rejects `approved_amount` over it, instead of echoing the client's figure straight through. |
| BR-006 — Interest/scheme terms from approved master configuration | Found | Interest rate/tenure/fees are config-driven; LTV % now comes from `gold_rates.ltv_pct`, approved through the same propose/approve workflow as the rate itself |
| BR-007 — Disbursement requires approval and documents | Found | **Fixed:** `Disbursement::disburse()` now requires at least one `loan_documents` row of type AGREEMENT for the loan (BRD §9 "Loan agreement & documents stored") before it will disburse, on top of the pre-existing `status===APPROVED` check. |
| BR-008 — Payments update only the selected loan | Found | All payment/settlement writes scope to `$loan['id']` resolved from the route |
| BR-009 — Loan Details jewellery filtered by Loan ID | Found | `for_loan()` — verified correct SQL, no cross-loan leakage |
| BR-010 — Closed loans remain available for historical viewing | Found | No status-exclusion filter by default, no hard-delete on loans anywhere |
| BR-011 — Renewal/top-up/re-loan appear only when eligible | Found | **Fixed under §11:** `Renewal::renew()` and `Topup::approve()` both now re-check eligibility (loan status + recomputed amounts) at commit time, not just in the separate preview endpoints. |
| BR-012 — Critical financial actions must be audited | Found | `audit_log()` is called correctly from ~20 controllers, and `audit_logs` is confirmed to already exist on the live DB with real rows in it |
| BR-013 — Duplicate financial posting must be prevented | Found | **Fixed:** `Part_payment::part_payment()`/`reload()` previously computed the new `sanctioned_amount` from a value read at request start, then blind-wrote it — a lost-update race where a concurrent request's effect on the balance could silently vanish even though its own history row was inserted; both now use an atomic SQL increment/decrement. `Topup::disburse()` previously updated the topup's status unconditionally, so two concurrent calls could both pass and both add `approved_amount` to the balance — now gated the same way `Disbursement::disburse()` already was, on `WHERE status='APPROVED'` with an `affected_rows()` check. Also extended the identical atomic-guard treatment to `Settlement::settle()` and `Renewal::renew()` (not named in the original evidence, but the same bug class was present in both — leaving it there right next to the ones just fixed would have been an inconsistent, incomplete fix). |
| BR-014 — Permissions control approval, payment, release, admin | Found | `require_role()` used broadly across approval/payment/release/admin controllers |

### §14 — Reports & KPIs (9 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Daily loan disbursement report | Found | **Fixed:** `DAILY_CASH` (`Report::daily_cash()` → `Report_model::daily_cash()`) now aggregates real disbursement + collection totals/counts for a branch/date, replacing the previous unimplemented stub. |
| Active/closed/foreclosed loan report | Found | **Fixed:** new `LOAN_STATUS` report code → `Report_model::loan_status_breakdown()` — count + total sanctioned amount per group (ACTIVE/RENEWED/PART_PAID, SETTLED/CLOSED, AUCTION_ELIGIBLE/AUCTIONED/NPA), optional branch filter. |
| Outstanding and overdue EMI report | Found | **Fixed:** new `OVERDUE_EMI` report code → `Report_model::overdue_emi()` — every ACTIVE/PART_PAID/RENEWED loan past `due_date`, with days overdue and outstanding amount, since these are interest-only (bullet) loans. |
| Daily collection report | Found | **Fixed:** new `DAILY_COLLECTION` report code → `Report_model::daily_collection()` — interest collections (broken down by mode), part-payments, and closures for a branch/date. |
| Branch and employee performance | Found | **Fixed:** new `BRANCH_PERFORMANCE` and `EMPLOYEE_PERFORMANCE` report codes → `Report_model::branch_performance()`/`employee_performance()` — loans created, amount disbursed, amount collected, grouped by branch or by user, over a date range. |
| Gold pledged inventory / jewellery release report | Found | **Also fixed:** `Inventory::vault_status()` already covered current vault holdings; added new `JEWELLERY_RELEASE` report code → `Report_model::jewellery_release()` for the release half — every completed `gold_releases` row in a period, joined to customer/loan. |
| Renewal, top-up and re-loan report | Found | **Fixed:** new `RENEWAL_TOPUP_RELOAN` report code → `Report_model::renewal_topup_reloan()` — counts and totals across `loan_renewals`/`loan_topups`/`loan_reloads` for a date range. |
| Audit/user activity report | Found | **Fixed:** new `AUDIT_ACTIVITY` report code → `Report_model::audit_activity()` — reads `audit_logs` back (confirmed to already exist on the live DB, see critical issue #2's correction) with actor/entity-type filters. |
| KPIs: processing time, KYC completion, disbursement volume, collection rate, overdue rate, renewal rate, repeat-customer rate | Found | **Fixed:** new `KPI_SUMMARY` report code → `Report_model::kpi_summary()`, all 7 KPIs over a date range. Two (collection rate, renewal rate) have no precise BRD formula or per-period ledger to compute exactly against, so they're documented approximations (see the method's docblock) rather than invented precision — consistent with this codebase's existing precedent of labeling first-pass estimates as such (`Loan::show()`'s `eligible_actions`). |

All nine are exposed both over the API (`GET /api/v1/reports/{reportCode}`, unchanged route, new codes added to the existing dispatcher) and in the admin panel: the admin nav (`views/admin/_layout.php`) and `routes.php` already linked to `admin/reports`, but the controller and view never existed, making it a dead 404 — `admin/Reports.php` + `views/admin/reports.php` now render all nine as one dashboard page with branch/date/date-range filters.

### §15 — Security & Audit (8 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Role-based access control | Found | `require_role()` (`MY_Controller.php:56-67`) used across api/v1 and admin controllers |
| Secure login, OTP, optional biometric | Found | **Fixed, for real this time:** critical issue #1 previously claimed the OTP-in-response-body leak was fixed, but `Auth::send_otp()` was still returning `'otp' => $otp` — removed. Also added the missing biometric login path: `user_biometric_ref` existed in the schema but nothing read or wrote to it; added `User_biometric_ref_model` and `Auth::enroll_biometric()`/`Auth::biometric_login()` (device must already be bound via a prior password/OTP login; the actual match happens on-device, mirroring `Kyc_aadhaar::face_auth()`'s existing pattern). |
| Encrypted network communication | Missing | Out of scope at the app layer — this is an infra/deployment concern (TLS termination), not something CI3 code enforces |
| Secure KYC / jewellery image access | Found | **Fixed:** `Jewellery::download_image()` is now a gated (auth + role) file-serving endpoint, same pattern as `Kyc_document::download()`/`Loan_document::download()`. All three document/image types now have a gated read path, not just an upload endpoint. |
| Audit logs for critical operations | Found | **Correction:** this row was stale — it repeated critical issue #2's original (incorrect) diagnosis. `audit_logs` is confirmed to already exist on the live DB with real rows in it; same evidence as BR-012. |
| No sensitive customer data in debug logs | Found | Only two `log_message()` calls app-wide, neither touches customer PII |
| Financial APIs prevent duplicate submissions | Found | **Fixed:** `Part_payment::part_payment()`/`reload()`, `Topup::disburse()`, `Settlement::settle()` and `Renewal::renew()` already got atomic guards under BR-013 (§13). The remaining gap was `Interest::collect()` and `Part_payment::part_payment()` — plain inserts with no status transition to gate on — closed with an optional client-supplied `idempotency_key` (nullable+unique column, `docs/migrations/2026_08_16_security_audit_fixes.sql`): a retried/double-submitted request with the same key returns the original record instead of creating a second one. |
| Session timeout and forced logout | Found | CI session expiration configured (`config.php:387,390`); `Token_auth` enforces a 60-minute API token TTL |

### §4 / §3 — Administration & Printing (7 requirements)

| Requirement | Status | Evidence |
|---|---|---|
| Users | Found | `admin/Employees.php` — store/update/toggle_active |
| Roles | Found | **Fixed:** `role_index()` was read-only; added `Master::role_store()`/`role_update()` (code immutable post-creation, since it's matched elsewhere by `require_role()`), plus admin-panel CRUD in the new `admin/Masters.php`/`views/admin/masters.php`. |
| Branches | Found | **Correction:** this row previously claimed "full CRUD", but only Create+Read existed — there was no way to edit a branch once created. **Fixed:** added `Master::branch_update()` (API) and full create/edit in `admin/Masters.php`. |
| Schemes (loan products) | Found | **Correction:** same false "full CRUD" claim as Branches — Create+Read only. **Fixed:** added `Master::loan_product_update()` (API) and full create/edit in `admin/Masters.php`. |
| Rates | Found | **Fixed:** added `GET /api/v1/master/gold-rate` and a read-only Rates tab in `admin/Masters.php` for oversight. Deliberately read-only: propose/approve stay on `Jewellery.php` (role APPRAISER/BRANCH_MANAGER/REGIONAL_MANAGER), since every `admin/*` controller only allows ADMIN/OPERATIONS to log in — building propose/approve into the admin panel would let the wrong roles perform that maker-checker action. |
| Charges | Found | **Fixed:** there's no separate charges-master table — `processing_fee_pct`/`gst_pct`/`insurance_pct` live on `loan_products` — so making those fields editable via `Master::loan_product_update()`/`admin/Masters.php` (above) closes this row too. |
| Bluetooth/thermal printing | Partial | **Improved:** a CI3 REST backend can't drive a phone's Bluetooth stack or a physical printer directly — that's inherently client-side, same reasoning "Encrypted network communication" (§15) was scoped as infra-only. Added `GET /api/v1/interest/collection/{id}/receipt` (`Interest::receipt()`) returning print-ready receipt content (structured fields + pre-formatted 32-column lines) so the mobile app's printer SDK doesn't have to reimplement formatting. Left Partial, not Found: only interest-collection receipts are covered so far, not disbursement/settlement receipts, and there's still no actual Bluetooth pairing/printing code (nor could there be, in this codebase). |

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
