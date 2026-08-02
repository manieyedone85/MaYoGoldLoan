-- ============================================================================
-- SWARNA GOLD LOAN MANAGEMENT SYSTEM
-- DML — seed data for every master/lookup table in 01_ddl_schema.sql
-- Run this after 01_ddl_schema.sql. Transactional tables (customer_master,
-- loan_master, jewellery_item, etc.) are intentionally left empty here --
-- they fill up from live application usage, not seed data.
-- ============================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- role_master + loan_approval_limit_master (Module 1 / Module 9)
-- ----------------------------------------------------------------------------
INSERT INTO role_master (code, name, description) VALUES
('CUSTOMER', 'Customer', 'End borrower — app access to loan status, payments, EMI'),
('BRANCH_EXECUTIVE', 'Branch Executive', 'Customer registration, gold entry, loan creation'),
('APPRAISER', 'Gold Appraiser', 'Jewellery verification: weight, purity, hallmark, eligible amount'),
('CASHIER', 'Cashier', 'Cash collection, disbursement, receipts'),
('BRANCH_MANAGER', 'Branch Manager', 'Loan approval, override, cash verification, reports'),
('REGIONAL_MANAGER', 'Regional Manager', 'Branch monitoring, high-value loan approval, audit'),
('OPERATIONS', 'Operations Team', 'Loan monitoring, branch monitoring, exception handling'),
('FINANCE', 'Finance Team', 'Accounting, GL posting, GST, TDS'),
('AUDITOR', 'Auditor', 'Audit trail, user activity, deleted records, loan changes'),
('ADMIN', 'Admin', 'Masters, branches, employees, roles, configuration');

-- Per-role value thresholds -- amounts above these escalate to the next stage
INSERT INTO loan_approval_limit_master (role_id, max_amount)
SELECT id, 200000 FROM role_master WHERE code = 'BRANCH_MANAGER';
INSERT INTO loan_approval_limit_master (role_id, max_amount)
SELECT id, 1000000 FROM role_master WHERE code = 'REGIONAL_MANAGER';

-- ----------------------------------------------------------------------------
-- permission_master + role_permission_map (Module 26) -- representative set
-- ----------------------------------------------------------------------------
INSERT INTO permission_master (code, module, name) VALUES
('customer.create', 'CUSTOMER', 'Create customer'),
('customer.merge', 'CUSTOMER', 'Merge duplicate customers'),
('jewellery.evaluate', 'JEWELLERY', 'Evaluate jewellery and set eligible amount'),
('gold_rate.propose', 'JEWELLERY', 'Propose a new gold rate'),
('gold_rate.approve', 'JEWELLERY', 'Approve a proposed gold rate'),
('loan.create', 'LOAN', 'Create a loan'),
('loan.approve', 'LOAN', 'Approve a loan'),
('loan.override', 'LOAN', 'Override loan approval'),
('loan.disburse', 'LOAN', 'Disburse a loan'),
('interest.collect', 'LOAN', 'Collect interest payment'),
('loan.settle', 'LOAN', 'Settle / close a loan'),
('gold_release.complete', 'LOAN', 'Complete gold release'),
('auction.manage', 'AUCTION', 'Schedule and manage auctions'),
('accounting.voucher.create', 'ACCOUNTING', 'Create accounting voucher'),
('master.manage', 'ADMIN', 'Manage master data');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'APPRAISER' AND p.code IN ('jewellery.evaluate', 'gold_rate.propose');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'BRANCH_MANAGER' AND p.code IN ('gold_rate.approve', 'loan.approve', 'loan.settle', 'gold_release.complete', 'auction.manage');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'REGIONAL_MANAGER' AND p.code IN ('gold_rate.approve', 'loan.approve', 'loan.override', 'customer.merge', 'auction.manage');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'CASHIER' AND p.code IN ('loan.disburse', 'interest.collect', 'loan.settle');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'BRANCH_EXECUTIVE' AND p.code IN ('customer.create', 'loan.create');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'FINANCE' AND p.code IN ('accounting.voucher.create');

INSERT INTO role_permission_map (role_id, permission_id)
SELECT r.id, p.id FROM role_master r, permission_master p
WHERE r.code = 'ADMIN' AND p.code = 'master.manage';

-- ----------------------------------------------------------------------------
-- branch_master (Module 26) -- sample branch, replace with real rollout list
-- ----------------------------------------------------------------------------
INSERT INTO branch_master (branch_code, company_code, name, city, state, gst_number, is_active) VALUES
('H001-BR001', 'H001', 'Swarna Gold Loan - Chennai Main', 'Chennai', 'Tamil Nadu', NULL, 1),
('H001-BR002', 'H001', 'Swarna Gold Loan - Tambaram', 'Chennai', 'Tamil Nadu', NULL, 1);

-- ----------------------------------------------------------------------------
-- kyc_document_type (Module 5)
-- ----------------------------------------------------------------------------
INSERT INTO kyc_document_type (code, name) VALUES
('VOTER_ID', 'Voter ID'),
('DRIVING_LICENSE', 'Driving License'),
('PASSPORT', 'Passport'),
('UTILITY_BILL', 'Utility Bill'),
('BANK_PASSBOOK', 'Bank Passbook');

-- ----------------------------------------------------------------------------
-- jewellery_category_master (Module 7)
-- ----------------------------------------------------------------------------
INSERT INTO jewellery_category_master (code, name) VALUES
('CHAIN', 'Chain'),
('RING', 'Ring'),
('BANGLE', 'Bangle'),
('NECKLACE', 'Necklace'),
('EARRING', 'Earring'),
('COIN', 'Gold Coin'),
('BRACELET', 'Bracelet'),
('ANKLET', 'Anklet');

-- ----------------------------------------------------------------------------
-- gold_rate_master (Module 7) -- seed with a system user as proposer/approver;
-- replace user_id 1 with your actual Admin user_master.id after seeding users.
-- ----------------------------------------------------------------------------
-- NOTE: requires at least one user_master row to exist first (see below).

-- ----------------------------------------------------------------------------
-- loan_product_master (Module 8)
-- ----------------------------------------------------------------------------
INSERT INTO loan_product_master (code, name, interest_rate_pct, interest_type, tenure_months, processing_fee_pct, gst_pct, insurance_pct, is_active) VALUES
('GL-STD-12', 'Standard Gold Loan - 12 Month', 12.00, 'FLAT', 12, 1.00, 18.00, 0.25, 1),
('GL-STD-6', 'Standard Gold Loan - 6 Month', 13.50, 'FLAT', 6, 1.00, 18.00, 0.25, 1),
('GL-PREMIUM-12', 'Premium Gold Loan - 12 Month', 10.50, 'REDUCING', 12, 0.50, 18.00, 0.25, 1);

-- ----------------------------------------------------------------------------
-- loan_scheme_master (Module 26) -- enable both branches for all products
-- ----------------------------------------------------------------------------
INSERT INTO loan_scheme_master (branch_id, loan_product_id, is_enabled)
SELECT b.id, p.id, 1 FROM branch_master b, loan_product_master p;

-- ----------------------------------------------------------------------------
-- interest_slab_master (Module 26)
-- ----------------------------------------------------------------------------
INSERT INTO interest_slab_master (min_amount, max_amount, interest_rate_pct) VALUES
(0, 50000, 13.50),
(50000.01, 200000, 12.00),
(200000.01, 1000000, 10.50),
(1000000.01, 99999999, 9.00);

-- ----------------------------------------------------------------------------
-- charge_master (Module 26)
-- ----------------------------------------------------------------------------
INSERT INTO charge_master (code, name, type, value) VALUES
('PROC_FEE_STD', 'Standard Processing Fee', 'PERCENTAGE', 1.00),
('GST_STD', 'GST on Processing Fee', 'PERCENTAGE', 18.00),
('INSURANCE_STD', 'Gold Insurance', 'PERCENTAGE', 0.25),
('LATE_FEE_FLAT', 'Late Payment Fee', 'FLAT', 100.00),
('DUPLICATE_RECEIPT_FEE', 'Duplicate Receipt Fee', 'FLAT', 50.00);

-- ----------------------------------------------------------------------------
-- disbursement_mode_master (Module 10)
-- ----------------------------------------------------------------------------
INSERT INTO disbursement_mode_master (code, name) VALUES
('CASH', 'Cash'),
('IMPS', 'IMPS'),
('RTGS', 'RTGS'),
('NEFT', 'NEFT'),
('UPI', 'UPI'),
('BANK_TRANSFER', 'Bank Transfer');

-- ----------------------------------------------------------------------------
-- gl_account_master (Module 22) -- minimal chart of accounts to start
-- ----------------------------------------------------------------------------
INSERT INTO gl_account_master (code, name, type) VALUES
('1000', 'Cash in Hand', 'ASSET'),
('1010', 'Bank Account - Current', 'ASSET'),
('1100', 'Loans Receivable - Gold Loan', 'ASSET'),
('1200', 'Gold Inventory (Pledged)', 'ASSET'),
('2000', 'Customer Deposits Payable', 'LIABILITY'),
('2100', 'GST Payable', 'LIABILITY'),
('2200', 'TDS Payable', 'LIABILITY'),
('4000', 'Interest Income', 'INCOME'),
('4100', 'Processing Fee Income', 'INCOME'),
('4200', 'Auction Surplus Income', 'INCOME'),
('5000', 'Staff Salary Expense', 'EXPENSE'),
('5100', 'Branch Rent Expense', 'EXPENSE'),
('5200', 'Bad Debt / NPA Write-off', 'EXPENSE');

-- ----------------------------------------------------------------------------
-- notification_template (Module 18)
-- ----------------------------------------------------------------------------
INSERT INTO notification_template (code, channel, body) VALUES
('LOAN_APPROVED', 'SMS', 'Dear {customer_name}, your gold loan {loan_account_number} for Rs.{sanctioned_amount} has been approved. - Swarna Gold Loan'),
('LOAN_DISBURSED', 'SMS', 'Dear {customer_name}, Rs.{net_disbursed_amount} has been disbursed to your account against loan {loan_account_number}.'),
('INTEREST_DUE_REMINDER', 'SMS', 'Dear {customer_name}, interest of Rs.{interest_due} is due on loan {loan_account_number} by {due_date}.'),
('AUCTION_NOTICE', 'SMS', 'Dear {customer_name}, your pledged gold under loan {loan_account_number} is scheduled for auction on {auction_date} due to non-payment. Please contact your branch immediately.'),
('OTP_LOGIN', 'SMS', 'Your Swarna Gold Loan OTP is {otp}. Valid for 5 minutes. Do not share this with anyone.'),
('LOAN_RENEWED', 'WHATSAPP', 'Your loan {loan_account_number} has been renewed successfully. New due date: {new_due_date}.');

-- ----------------------------------------------------------------------------
-- print_template_master (Module 19)
-- ----------------------------------------------------------------------------
INSERT INTO print_template_master (code, type, template_body) VALUES
('RECEIPT_STD', 'RECEIPT', 'Receipt No: {receipt_number}\nCustomer: {customer_name}\nAmount: Rs.{amount}\nDate: {date}'),
('LOAN_AGREEMENT_STD', 'LOAN_AGREEMENT', 'Loan Agreement - {loan_account_number}\nCustomer: {customer_name}\nSanctioned: Rs.{sanctioned_amount}\nTerms apply as per schedule.'),
('GOLD_PACKET_LABEL_STD', 'GOLD_PACKET_LABEL', 'Packet: {packet_code}\nCustomer: {customer_name}\nLoan: {loan_account_number}\nNet Wt: {net_weight}g'),
('BARCODE_STD', 'BARCODE', '{barcode}'),
('QR_STD', 'QR', '{qr_payload}');

-- ----------------------------------------------------------------------------
-- menu_master (Module 26) -- top-level app menu, role-gated
-- ----------------------------------------------------------------------------
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'CUSTOMER_REG', 'Customer Registration', NULL, r.id, 1 FROM role_master r WHERE r.code = 'BRANCH_EXECUTIVE';
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'JEWELLERY_EVAL', 'Jewellery Evaluation', NULL, r.id, 1 FROM role_master r WHERE r.code = 'APPRAISER';
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'CASH_COLLECTION', 'Cash Collection', NULL, r.id, 1 FROM role_master r WHERE r.code = 'CASHIER';
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'LOAN_APPROVAL', 'Loan Approval', NULL, r.id, 1 FROM role_master r WHERE r.code = 'BRANCH_MANAGER';
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'AUCTION_MGMT', 'Auction Management', NULL, r.id, 1 FROM role_master r WHERE r.code = 'REGIONAL_MANAGER';
INSERT INTO menu_master (code, name, parent_id, role_id, is_active)
SELECT 'ADMIN_MASTERS', 'Masters', NULL, r.id, 1 FROM role_master r WHERE r.code = 'ADMIN';

-- ----------------------------------------------------------------------------
-- app_config_master (Module 30)
-- ----------------------------------------------------------------------------
INSERT INTO app_config_master (config_key, config_value) VALUES
('CASH_DISBURSEMENT_LIMIT', '20000'),
('OTP_EXPIRY_MINUTES', '5'),
('JWT_TOKEN_EXPIRY_MINUTES', '60'),
('DEFAULT_ELIGIBLE_PERCENTAGE', '75.00'),
('DEFAULT_COMPANY_CODE', 'H001'),
('SINGLE_DEVICE_ENFORCED_ROLES', 'BRANCH_EXECUTIVE,APPRAISER,CASHIER'),
('NPA_DAYS_THRESHOLD', '90'),
('APP_MIN_SUPPORTED_VERSION', '1.0.0');

-- ----------------------------------------------------------------------------
-- Sample admin user + branch-linked gold rates (Module 1, Module 7)
-- Password/MPIN hashes below are placeholders -- replace via your app's
-- registration flow (bcrypt/argon2), never insert real hashes via raw SQL
-- in production.
-- ----------------------------------------------------------------------------
INSERT INTO user_master (employee_code, name, mobile, email, password_hash, role_id, branch_id, is_active)
SELECT 'H001-EMP0001', 'System Admin', '9999999999', 'admin@swarnagoldloan.example', '$2y$10$REPLACE_WITH_REAL_HASH',
       r.id, b.id, 1
FROM role_master r, branch_master b
WHERE r.code = 'ADMIN' AND b.branch_code = 'H001-BR001'
LIMIT 1;

INSERT INTO gold_rate_master (rate_per_gram, karat, effective_date, status, proposed_by, approved_by, approved_at)
SELECT 6250.00, '22K', CURDATE(), 'APPROVED', u.id, u.id, NOW()
FROM user_master u WHERE u.employee_code = 'H001-EMP0001';

INSERT INTO gold_rate_master (rate_per_gram, karat, effective_date, status, proposed_by, approved_by, approved_at)
SELECT 5115.00, '18K', CURDATE(), 'APPROVED', u.id, u.id, NOW()
FROM user_master u WHERE u.employee_code = 'H001-EMP0001';

INSERT INTO vault_master (branch_id, name)
SELECT id, CONCAT(name, ' - Main Vault') FROM branch_master;
