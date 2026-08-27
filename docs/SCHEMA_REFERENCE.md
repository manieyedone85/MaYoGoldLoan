# Database Schema Reference (shared, reused as-is from the Laravel app)

This CodeIgniter 3 port reuses the exact same MySQL schema as the Laravel app in the
parent directory (`../database/migrations/2025_*.php`) — do not rename tables/columns.
All tables use `id` (unsigned bigint, auto-increment PK) and `created_at`/`updated_at`
(nullable datetime, no auto Eloquent timestamps magic — set them manually in CI3 models
via `date('Y-m-d H:i:s')`). Foreign keys are plain `*_id` unsigned bigint columns (no
DB-level FK constraints needed to be re-declared in CI3 model code; the columns exist).

## roles
id, code (unique, e.g. CUSTOMER/BRANCH_EXECUTIVE/APPRAISER/CASHIER/BRANCH_MANAGER/
REGIONAL_MANAGER/OPERATIONS/FINANCE/AUDITOR/ADMIN), name, description, timestamps

## permissions / role_permission
permissions: id, code (unique), module, name, timestamps
role_permission: id, role_id, permission_id, timestamps (unique role_id+permission_id)

## branches
id, branch_code (unique), company_code (default H001), name, city, state, latitude,
longitude, gst_number, is_active (bool), timestamps

## users
id, employee_code (unique, nullable), name, mobile (unique), email (unique, nullable),
password (nullable, hashed), mpin (nullable, hashed), role_id (FK roles), branch_id
(FK branches, nullable), is_active (bool), last_login_at (nullable datetime),
remember_token, timestamps

## user_otps
id, user_id (FK users, nullable), mobile, otp_hash, purpose (default LOGIN; LOGIN,
FORGOT_PASSWORD, MPIN_RESET), is_verified (bool), expires_at, timestamps

## user_device_bindings
id, user_id (FK users), device_id, device_model (nullable), push_token (nullable),
is_active (bool), bound_at, timestamps (unique user_id+device_id)

## user_biometric_ref
id, user_id (FK users), type (FACE, FINGERPRINT), template_ref, created_at only (no
updated_at). Table name is singular in the live DB (doc previously said
`user_biometric_refs`). Was completely unused until BRD §15 "optional biometric"
login was added: `User_biometric_ref_model`, `Auth::enroll_biometric()` /
`Auth::biometric_login()` (docs/BRD_COVERAGE_AUDIT.md)

## customers
id, customer_code (unique, e.g. CUST00012345), name, mobile, email (nullable),
dob (nullable date), gender (nullable), aadhaar_last4 (nullable, 4 chars),
aadhaar_hash (nullable, SHA-256 — never store full Aadhaar), pan_number (nullable),
father_name (nullable, added by docs/migrations/2026_08_26_customer_profile_fields.sql),
profession_type (nullable varchar(30) — plain VARCHAR, not a DB enum, same convention
as kyc_status/gender; app-enforced values SALARIED/SELF_EMPLOYED/BUSINESS/AGRICULTURE/
RETIRED/OTHER, see Loans::PROFESSION_TYPES), profession_details (nullable free text),
income (nullable decimal(12,2)), photo_path (nullable, relative path under
uploads/customer-photos/), branch_id (FK branches), registered_by (FK users, nullable),
kyc_status (default PENDING; PENDING/VERIFIED/REJECTED/EXPIRED — plain VARCHAR(20), not
a DB enum, so EXPIRED needs no migration; set via `PUT /api/v1/customer/{id}/kyc-status`,
`Customer::update_kyc_status()`), is_blacklisted (bool), timestamps, deleted_at
(soft delete)

## customer_addresses
id, customer_id (FK customers), type (default CURRENT; CURRENT/PERMANENT), line1,
line2 (nullable), city, state, pincode, timestamps

## customer_family_members
id, customer_id (FK customers), name, relation, mobile (nullable), timestamps

## customer_nominees
id, customer_id (FK customers), name, relation, mobile (nullable), id_proof_type
(nullable), id_proof_number (nullable), timestamps

## customer_duplicate_logs
id, customer_id (FK customers), matched_customer_id (FK customers), match_score
(decimal 5,2), status (default PENDING_REVIEW; PENDING_REVIEW/CONFIRMED/DISMISSED),
reviewed_by (FK users, nullable), timestamps

## customer_merge_logs
id, primary_customer_id (FK customers), merged_customer_id (FK customers),
approved_by (FK users — Regional Manager), timestamps

## customer_biometrics
id, customer_id (FK customers), type (FACE/FINGERPRINT/SIGNATURE), file_ref, timestamps

## kyc_aadhaar_verifications
id, customer_id (FK customers), method (QR/OFFLINE_XML/OCR), uidai_reference_id
(nullable), is_verified (bool), verified_at (nullable), timestamps

## kyc_face_auth_logs
id, customer_id (FK customers), is_matched (bool), confidence_score (nullable decimal
5,2), timestamps

## kyc_pan_verifications
id, customer_id (FK customers), pan_number, is_verified (bool), name_match (bool),
timestamps

## kyc_document_types
id, code (unique; VOTER_ID/DRIVING_LICENSE/PASSPORT/UTILITY_BILL/BANK_PASSBOOK), name,
timestamps

## kyc_documents
id, customer_id (FK customers), document_type_id (FK kyc_document_types), file_ref,
status (default PENDING; PENDING/VERIFIED/REJECTED), verified_by (FK users, nullable),
rejection_reason (nullable — added by docs/migrations/2026_08_16_customer_kyc_fixes.sql,
required by `Kyc_document::verify()` when status=REJECTED), timestamps

## jewellery_categories
id, code (unique), name (Chain/Ring/Bangle/Necklace...), timestamps

## gold_rates
id, rate_per_gram (decimal 10,2), ltv_pct (decimal 5,2, default 75.00 — the
loan-to-value percentage approved alongside the rate; read by
Jewellery::evaluate() and admin/Loans::store() instead of a hardcoded
literal), karat (e.g. "22K", "18K"), effective_date, status (default
PENDING_APPROVAL; PENDING_APPROVAL/APPROVED), proposed_by (FK users),
approved_by (FK users, nullable), approved_at (nullable), timestamps

## jewellery_items
id, barcode (unique), customer_id (FK customers), category_id (FK
jewellery_categories), hallmark_flag (bool), gross_weight (decimal 8,3), stone_weight
(decimal 8,3, default 0), net_weight (decimal 8,3 = gross - stone), purity_karat,
gold_rate_id (FK gold_rates), applied_rate (decimal 10,2), eligible_percentage
(decimal 5,2, default 75.00), eligible_amount (decimal 12,2), evaluated_by (FK users),
status (default EVALUATED; EVALUATED/PLEDGED/RELEASED/AUCTIONED), loan_id (nullable,
set once loan created), timestamps

## jewellery_valuation_history
id, jewellery_item_id (FK jewellery_items), gold_rate_id (FK gold_rates),
gross_weight (decimal 8,3), stone_weight (decimal 8,3), applied_rate (decimal 10,2),
eligible_percentage (decimal 5,2), eligible_amount (decimal 12,2), evaluated_by (FK
users), created_at only (no updated_at) — added by
docs/migrations/2026_08_16_jewellery_valuation_history.sql. One row per valuation
event; written by `Jewellery::evaluate()`/`Jewellery::re_evaluate()` and
`admin/Loans::store()`, read via `GET /api/v1/jewellery/{id}/valuation-history`

## jewellery_images
id, jewellery_item_id (FK jewellery_items), file_ref, timestamps

## loan_products
id, code (unique), name, interest_rate_pct (decimal 5,2), interest_type (default FLAT;
FLAT/REDUCING), tenure_months, processing_fee_pct (decimal 5,2, default 0), gst_pct
(decimal 5,2, default 18.00), insurance_pct (decimal 5,2, default 0), is_active (bool),
timestamps

## loans
id, loan_account_number (unique, e.g. LGH001000123 — nullable: only assigned in
Disbursement::disburse(), derived from `id`, not set at creation; see BRD §9
"Unique Loan ID created after disbursement" in docs/BRD_COVERAGE_AUDIT.md and
docs/migrations/2026_08_16_new_gold_loan_fixes.sql), customer_id (FK customers),
branch_id (FK branches), loan_product_id (FK loan_products), eligible_amount (decimal
12,2), sanctioned_amount (decimal 12,2), interest_rate_pct (decimal 5,2),
processing_fee (decimal 10,2, default 0), gst_amount (decimal 10,2, default 0),
insurance_amount (decimal 10,2, default 0), net_disbursed_amount (nullable decimal
12,2), loan_date (date), due_date (date), status (default DRAFT — one of DRAFT,
PENDING_APPROVAL, APPROVED, REJECTED, CANCELLED, DISBURSED, ACTIVE, RENEWED, PART_PAID,
SETTLED, NPA, AUCTION_ELIGIBLE, AUCTIONED, CLOSED — CANCELLED is CI3-admin-only, not
present in the parent Laravel app: set by admin/Loans::cancel(), only reachable from
DRAFT/PENDING_APPROVAL/APPROVED i.e. before disbursement), created_by (FK users), timestamps

## loan_documents
id, loan_id (FK loans), document_type (default AGREEMENT; AGREEMENT/SANCTION_LETTER/
OTHER), file_ref, uploaded_by (FK users), timestamps — added by
docs/migrations/2026_08_16_new_gold_loan_fixes.sql for BRD §9 "Loan agreement &
documents stored"; written/read via Loan_document.php
(`POST/GET /api/v1/loan/{id}/document`, `GET /api/v1/loan/document/{id}/file`)

## loan_charges
id, loan_id (FK loans), charge_type (PROCESSING_FEE/GST/INSURANCE/LATE_FEE), amount
(decimal 10,2), timestamps

## loan_approval_limits
id, role_id (FK roles), max_amount (decimal 12,2), timestamps
(Branch Manager 200000, Regional Manager 1000000)

## loan_approval_workflows
id, loan_id (FK loans), current_stage (default APPRAISER; APPRAISER/MANAGER/
REGIONAL_MANAGER/HO), status (default PENDING; PENDING/APPROVED/REJECTED), timestamps

## loan_approval_logs
id, loan_id (FK loans), stage, action (APPROVE/REJECT/OVERRIDE), actioned_by (FK
users), remarks (nullable), timestamps
(Maker-checker rule: actioned_by must never equal loans.created_by)

## loan_disbursements
id, loan_id (FK loans), mode (FK disbursement_mode_master.id — NOT a free-text
code; Disbursement::disburse() resolves the request's mode code to this id via
Disbursement_mode_model::find_by_code() before insert), amount (decimal
12,2), reference_number (nullable), status (default PENDING; PENDING/COMPLETED/FAILED),
disbursed_by (FK users), timestamps
(Cash disbursement cap: INR 20,000 hard limit — enforce in controller)

## disbursement_mode_master
id, code (unique; CASH/IMPS/RTGS/NEFT/UPI/BANK_TRANSFER), name, timestamps — the
"configured payment modes" for disbursement (BRD §12, docs/BRD_COVERAGE_AUDIT.md)

## loan_renewals
id, loan_id (FK loans), renewed_tenure_months, interest_paid (decimal 10,2),
renewal_charges (decimal 10,2, default 0), new_due_date (date), previous_due_date
(nullable date — added by docs/migrations/2026_08_16_renewal_topup_reload_fixes.sql
for BRD §11 "Related transactions retain historical references"), processed_by (FK
users), timestamps

## loan_topups
id, loan_id (FK loans), eligible_topup_amount (decimal 12,2), approved_amount
(nullable decimal 12,2), previous_sanctioned_amount (nullable decimal 12,2 — added by
docs/migrations/2026_08_16_renewal_topup_reload_fixes.sql, set at disburse() time),
processing_fee (decimal 10,2, default 0), status (default
PENDING; PENDING/APPROVED/DISBURSED/REJECTED), approved_by (FK users, nullable),
timestamps

## interest_collections
id, loan_id (FK loans), amount (decimal 10,2), mode (free-text VARCHAR — CASH/UPI/
BANK_TRANSFER/CARD, per BRD §12 "Configured payment modes"; was CASH/ONLINE only),
receipt_number (unique), idempotency_key (nullable, unique — added by
docs/migrations/2026_08_16_security_audit_fixes.sql for BRD §15 "Financial APIs
prevent duplicate submissions"), collected_by (FK users), timestamps

## loan_part_payments
id, loan_id (FK loans), principal_amount (decimal 10,2, default 0), interest_amount
(decimal 10,2, default 0), idempotency_key (nullable, unique — added by
docs/migrations/2026_08_16_security_audit_fixes.sql, same rationale as
interest_collections above), collected_by (FK users), timestamps

## loan_reloads
id, loan_id (FK loans), excess_amount_eligible (decimal 12,2 — now recomputed
server-side in Part_payment::reload(), not client-supplied), reload_amount (decimal
12,2), previous_sanctioned_amount (nullable decimal 12,2 — added by
docs/migrations/2026_08_16_renewal_topup_reload_fixes.sql), processed_by (FK users),
timestamps

## loan_closures
id, loan_id (FK loans), total_amount_collected (decimal 12,2), closure_date (date),
closed_by (FK users), timestamps

## gold_releases
id, loan_id (FK loans), jewellery_item_id (FK jewellery_items), id_proof_verified
(bool), signature_captured (bool), photo_captured (bool), released_by (FK users),
released_to (name on ID proof at release time), status (default PENDING; PENDING/
RELEASED), released_at (nullable), timestamps
(Ready-for-release rule: all three of id_proof_verified, signature_captured,
photo_captured must be true before calling complete/release)

## vaults
id, branch_id (FK branches), name, timestamps

## gold_packets
id, packet_code (unique), jewellery_item_id (FK jewellery_items), vault_id (FK vaults),
status (default IN_VAULT; IN_VAULT/PLEDGED/RELEASED/AUCTION_ELIGIBLE/AUCTIONED),
timestamps

## packet_transfer_logs
id, gold_packet_id (FK gold_packets), from_vault_id (FK vaults, nullable), to_vault_id
(FK vaults), transferred_by (FK users), timestamps

## auction_schedules
id, branch_id (FK branches), auction_date (date), status (default SCHEDULED;
SCHEDULED/NOTICE_SENT/COMPLETED/CANCELLED), created_by (FK users), timestamps

## auction_notice_logs
id, auction_schedule_id (FK auction_schedules), loan_id (FK loans), channel (SMS/
EMAIL/POST), sent_at, timestamps

## auction_bidders
id, auction_schedule_id (FK auction_schedules), name, mobile, id_proof_number
(nullable), timestamps

## auction_bids
id, auction_schedule_id (FK auction_schedules), gold_packet_id (FK gold_packets),
bidder_id (FK auction_bidders), bid_amount (decimal 12,2), timestamps

## auction_winners
id, gold_packet_id (FK gold_packets), bidder_id (FK auction_bidders), winning_amount
(decimal 12,2), timestamps

## auction_settlements
id, loan_id (FK loans), gold_packet_id (FK gold_packets), outstanding_loan_amount
(decimal 12,2), auction_amount (decimal 12,2), remaining_balance_to_customer (decimal
12,2, default 0), settled_by (FK users), timestamps

## gl_accounts
id, code (unique), name, type (ASSET/LIABILITY/INCOME/EXPENSE), timestamps

## vouchers
id, voucher_number (unique), branch_id (FK branches), type (RECEIPT/PAYMENT/JOURNAL/
CONTRA), voucher_date (date), source (nullable, e.g. "LOAN_DISBURSEMENT#123"),
created_by (FK users), timestamps

## voucher_details
id, voucher_id (FK vouchers), gl_account_id (FK gl_accounts), debit (decimal 12,2,
default 0), credit (decimal 12,2, default 0), timestamps
(Rule: sum(debit) must equal sum(credit) per voucher)

## cash_books
id, branch_id (FK branches), book_date (date), opening_balance (decimal 12,2),
closing_balance (decimal 12,2), timestamps

## customer_ledgers
id, customer_id (FK customers), loan_id (FK loans, nullable), particulars, debit
(decimal 12,2, default 0), credit (decimal 12,2, default 0), timestamps

## bank_reconciliation_logs
id, branch_id (FK branches), statement_date (date), bank_balance (decimal 12,2),
book_balance (decimal 12,2), is_reconciled (bool), reconciled_by (FK users, nullable),
timestamps

## notification_templates
id, code (unique), channel (SMS/WHATSAPP/EMAIL/PUSH), body, timestamps

## notification_logs
id, customer_id (FK customers, nullable), template_id (FK notification_templates),
channel, status (default QUEUED; QUEUED/SENT/FAILED), retry_count (default 0),
timestamps

## audit_logs
id, entity_type (e.g. Loan/Customer/JewelleryItem), entity_id (unsigned bigint),
action (CREATE/UPDATE/DELETE/APPROVE/REJECT), before_value (nullable JSON),
after_value (nullable JSON), actor_id (FK users, nullable), timestamps

## sync_queues
id, user_id (FK users), entity_type, payload (JSON), status (default PENDING; PENDING/
SYNCED/CONFLICT), timestamps

## sync_conflict_logs
id, sync_queue_id (FK sync_queues), server_value (JSON), client_value (JSON),
resolution (nullable; SERVER_WINS/MANUAL_REVIEW), timestamps

## personal_access_tokens (reused for this port's own bearer-token auth)
id, tokenable_type, tokenable_id, name, token (unique, 64 chars — SHA-256 hex of the
plaintext token), abilities (nullable), last_used_at (nullable), expires_at
(nullable), timestamps
See `application/libraries/Token_auth.php` for how this port issues/validates tokens
(plaintext token returned to client on login, only its SHA-256 hash stored here).
