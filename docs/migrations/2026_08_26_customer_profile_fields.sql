-- New customer profile fields captured on the admin "New Loan" screen when
-- registering a customer inline (application/controllers/admin/Loans.php::store()).
-- None of these existed anywhere in the shared schema (verified against
-- docs/u163330700_mayo_gold.sql and both sibling Laravel apps' migrations).
--
-- `profession_type` is a plain VARCHAR, not a real ENUM, matching the
-- existing convention for `customers.kyc_status` / `customers.gender` --
-- the application enforces the allowed values (SALARIED, SELF_EMPLOYED,
-- BUSINESS, AGRICULTURE, RETIRED, OTHER), see Loans::PROFESSION_TYPES.
--
-- `photo_path` stores a relative path under uploads/customer-photos/,
-- same pattern as customers.aadhaar_hash-adjacent KYC file_ref columns
-- elsewhere in this schema (e.g. kyc_document_master.file_ref).

ALTER TABLE `customers`
  ADD COLUMN `father_name` VARCHAR(150) DEFAULT NULL AFTER `pan_number`,
  ADD COLUMN `profession_type` VARCHAR(30) DEFAULT NULL AFTER `father_name`,
  ADD COLUMN `profession_details` VARCHAR(255) DEFAULT NULL AFTER `profession_type`,
  ADD COLUMN `income` DECIMAL(12,2) DEFAULT NULL AFTER `profession_details`,
  ADD COLUMN `photo_path` VARCHAR(255) DEFAULT NULL AFTER `income`;
