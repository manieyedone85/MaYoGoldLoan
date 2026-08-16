-- §7 Customer & KYC fixes from docs/BRD_COVERAGE_AUDIT.md, verified against
-- the live database (docs/u163330700_mayo_gold.sql).
--
-- Duplicate-detection logging (customer_duplicate_log) and the EXPIRED KYC
-- status need no schema change: `customer_duplicate_log` already exists with
-- the right columns, and `customers.kyc_status` is a plain VARCHAR(20), not
-- a real ENUM, so it already accepts any string value the application sets.
--
-- The only genuinely missing column is the KYC document rejection reason.

ALTER TABLE `kyc_document_master`
  ADD COLUMN `rejection_reason` VARCHAR(255) DEFAULT NULL AFTER `verified_by`;
