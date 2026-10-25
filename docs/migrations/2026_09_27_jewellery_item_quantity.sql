-- Jewellery item line rows on the loan-creation form need a "how many
-- pieces make up this line" count. admin/views/loan_receipt.php already had
-- a "Qty" column in its Ornament Specifications table, but it was hardcoded
-- to the literal value 1 -- there was nowhere to store a real count.
-- Neither this schema nor the sibling Laravel apps have a quantity column
-- anywhere on jewellery_items -- this is a new feature, not a missed port.
--
-- Purely descriptive/count: it does NOT factor into eligible_amount, which
-- continues to be computed from gross_weight/stone_weight for the line as a
-- whole (i.e. gross_weight is still the combined weight of all pieces on
-- that line), matching the existing admin/Loans.php::store() formula.
--
-- DEFAULT 1 so existing insert paths that don't pass quantity (API loan
-- creation in application/controllers/api/v1/Loan.php, the standalone
-- evaluate()/re_evaluate() flow in application/controllers/admin/
-- Jewellery_items.php) keep working unchanged.

ALTER TABLE `jewellery_items`
  ADD COLUMN `quantity` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `category_id`;
