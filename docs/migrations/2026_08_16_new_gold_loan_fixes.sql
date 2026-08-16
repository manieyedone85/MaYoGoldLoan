-- §9 New Gold Loan fixes from docs/BRD_COVERAGE_AUDIT.md, verified against
-- the live database (docs/u163330700_mayo_gold.sql).

-- ---------------------------------------------------------------------------
-- "Unique Loan ID created after disbursement" was Partial: the loan account
-- number was generated at DRAFT creation via SELECT MAX(id)+1 (non-atomic --
-- two concurrent store() calls could compute the same number) and assigned
-- long before disbursement. Fixed by deriving the number deterministically
-- from `loans.id` (already atomic via AUTO_INCREMENT) and only assigning it
-- in Disbursement::disburse(). loan_account_number must therefore be
-- nullable for the DRAFT/PENDING_APPROVAL/APPROVED lifetime of a loan.
-- MySQL's unique index already allows multiple NULLs, so the existing
-- `UNIQUE KEY loan_account_number` constraint still holds once a number is
-- assigned.
-- ---------------------------------------------------------------------------
ALTER TABLE `loans`
  MODIFY `loan_account_number` VARCHAR(30) DEFAULT NULL;

-- ---------------------------------------------------------------------------
-- "Loan agreement & documents stored" was Missing -- no loan-document/
-- agreement model or controller existed anywhere. Same shape as the existing
-- kyc_document_master table (customer-side KYC documents).
-- ---------------------------------------------------------------------------
CREATE TABLE `loan_documents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(30) NOT NULL DEFAULT 'AGREEMENT',
  `file_ref` varchar(255) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ld_loan` (`loan_id`),
  KEY `fk_ld_uploader` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `loan_documents`
  ADD CONSTRAINT `fk_ld_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ld_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user_master` (`id`);
