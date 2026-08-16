-- §15 Security & Audit fixes from docs/BRD_COVERAGE_AUDIT.md.

-- ---------------------------------------------------------------------------
-- "Financial APIs prevent duplicate submissions": Disbursement/Part_payment/
-- Topup::disburse/Settlement/Renewal were already fixed under BR-013 (§13)
-- with atomic status-transition guards or atomic SQL increments/decrements --
-- but those only protect against the state-transition race, not a literal
-- duplicate submission of a plain insert (network retry, double-tap) against
-- Interest::collect() / Part_payment::part_payment(), which have no status
-- to gate on. An optional client-supplied idempotency_key lets a retried
-- request return the original record instead of creating a second one; NULL
-- stays allowed (and multiple NULLs are fine under a UNIQUE index) so
-- existing callers that don't send a key are unaffected.
-- ---------------------------------------------------------------------------
ALTER TABLE `interest_collections`
  ADD COLUMN `idempotency_key` VARCHAR(64) DEFAULT NULL AFTER `receipt_number`,
  ADD UNIQUE KEY `idx_ic_idempotency_key` (`idempotency_key`);

ALTER TABLE `loan_part_payments`
  ADD COLUMN `idempotency_key` VARCHAR(64) DEFAULT NULL AFTER `interest_amount`,
  ADD UNIQUE KEY `idx_lpp_idempotency_key` (`idempotency_key`);
