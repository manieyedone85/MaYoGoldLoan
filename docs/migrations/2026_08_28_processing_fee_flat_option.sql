-- Processing fee can now be a flat amount instead of always a percentage
-- of the eligible amount. Previously loan_products only had
-- `processing_fee_pct`, applied unconditionally in
-- application/controllers/admin/Loans.php::store() and both methods in
-- application/controllers/api/v1/Loan.php. Neither this schema nor the
-- sibling Laravel apps had a flat-fee option anywhere -- this is a new
-- feature, not a missed port.
--
-- `processing_fee_type` is a plain VARCHAR, not a real ENUM, mirroring the
-- existing `loan_products.interest_type` (FLAT/REDUCING) convention on the
-- same table -- the application enforces the allowed values (PERCENTAGE,
-- FLAT).

ALTER TABLE `loan_products`
  ADD COLUMN `processing_fee_type` VARCHAR(20) NOT NULL DEFAULT 'PERCENTAGE' AFTER `processing_fee_pct`,
  ADD COLUMN `processing_fee_flat` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `processing_fee_type`;
