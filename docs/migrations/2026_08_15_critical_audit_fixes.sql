-- Critical-issue fixes from docs/BRD_COVERAGE_AUDIT.md, verified against the
-- actual live database (docs/u163330700_mayo_gold.sql).
--
-- NOTE: an earlier version of this file also created `audit_logs` and added
-- `interest_collections.receipt_number`. Both were based on a stale error
-- log (application/logs/log-2026-08-08.php) from before this database was
-- set up -- re-checking the current dump directly shows `audit_logs`
-- already exists (with data in it) and `interest_collections` already has
-- `receipt_number`. Neither statement is needed and both have been removed
-- from this file (CREATE TABLE / ADD COLUMN would simply error against a
-- table/column that already exists).
--
-- The only schema change still genuinely required is the LTV column below.

-- ---------------------------------------------------------------------------
-- Critical issue #5 (BR-006): jewellery LTV is hardcoded at 75% in PHP
-- (application/controllers/api/v1/Jewellery.php and admin/Loans.php), instead
-- of coming from approved master configuration. Confirmed against the live
-- `gold_rates` table: it has no LTV-related column at all. gold_rates is
-- already approval-gated (BRANCH_MANAGER/REGIONAL_MANAGER) exactly like the
-- rate itself, so LTV now travels with the same approved record.
-- ---------------------------------------------------------------------------
ALTER TABLE `gold_rates`
  ADD COLUMN `ltv_pct` DECIMAL(5,2) NOT NULL DEFAULT 75.00 AFTER `rate_per_gram`;
