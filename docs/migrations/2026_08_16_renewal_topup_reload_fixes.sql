-- §11 Renewal / Top-up / Excess Re-loan fixes from docs/BRD_COVERAGE_AUDIT.md.
--
-- "Related transactions retain historical references" was Partial: renewal/
-- topup/reload all mutate the SAME loan row (due_date, sanctioned_amount),
-- and the only place the pre-change values were captured was inside
-- audit_log()'s JSON before/after blobs -- not queryable as part of the
-- domain data itself. Rearchitecting renewal/top-up into separate
-- old-loan/new-loan records would be a much larger, riskier change than this
-- pass warrants (it would touch every query, view and FK that assumes one
-- loan row per loan_account_number). Instead, each event table gets its own
-- "what it was before this event" snapshot column, so the history is
-- self-contained on the domain row without needing to parse audit_log.

ALTER TABLE `loan_renewals`
  ADD COLUMN `previous_due_date` DATE DEFAULT NULL AFTER `new_due_date`;

ALTER TABLE `loan_topups`
  ADD COLUMN `previous_sanctioned_amount` DECIMAL(12,2) DEFAULT NULL AFTER `approved_amount`;

ALTER TABLE `loan_reloads`
  ADD COLUMN `previous_sanctioned_amount` DECIMAL(12,2) DEFAULT NULL AFTER `reload_amount`;
