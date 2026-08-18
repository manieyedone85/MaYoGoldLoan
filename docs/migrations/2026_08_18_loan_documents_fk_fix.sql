-- Follow-up to 2026_08_16_new_gold_loan_fixes.sql: that file's
-- ADD CONSTRAINT statements for loan_documents used the name `fk_ld_loan`,
-- which already exists on loan_disbursements (MySQL requires FK constraint
-- names to be unique per-schema, not per-table). The CREATE TABLE
-- succeeded; only the two constraints below were never actually attached.
-- Same references/behavior as originally intended (ON DELETE CASCADE on
-- loan_id), just under non-colliding names.
ALTER TABLE `loan_documents`
  ADD CONSTRAINT `fk_loandoc_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loandoc_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user_master` (`id`);
