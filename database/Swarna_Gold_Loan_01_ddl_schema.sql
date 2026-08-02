-- ============================================================================
-- SWARNA GOLD LOAN MANAGEMENT SYSTEM
-- Full DDL — matches every table named in Swarna_Gold_Loan_Technical_Module_Spec.md
-- Convention: InnoDB, utf8mb4 / utf8mb4_general_ci, company_code = 'H001'
-- NOTE: `loan_master` was referenced by loan_id FKs throughout the spec doc
--       but never itself defined under any module's Tables: line — added here
--       under Module 8/9 since that's where the loan record is first created.
-- ============================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- MODULE 1 — Authentication & Device Security
-- ============================================================================

CREATE TABLE role_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE branch_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_code VARCHAR(20) NOT NULL UNIQUE,
    company_code VARCHAR(10) NOT NULL DEFAULT 'H001',
    name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    gst_number VARCHAR(20) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(30) NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(150) NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    mpin_hash VARCHAR(255) NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES role_master(id),
    CONSTRAINT fk_user_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Secondary role assignments (a user's role_id above is their primary role;
-- this table covers cases where staff hold an additional role, e.g. an
-- Appraiser temporarily covering Cashier duties).
CREATE TABLE user_role_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_role (user_id, role_id),
    CONSTRAINT fk_urm_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_urm_role FOREIGN KEY (role_id) REFERENCES role_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_device_binding (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(191) NOT NULL,
    device_model VARCHAR(100) NULL,
    push_token VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    bound_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_device (user_id, device_id),
    CONSTRAINT fk_udb_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_otp_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    mobile VARCHAR(15) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    purpose VARCHAR(30) NOT NULL DEFAULT 'LOGIN',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_mobile_purpose (mobile, purpose),
    CONSTRAINT fk_uol_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_session_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(191) NULL,
    login_at DATETIME NOT NULL,
    logout_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usl_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE user_biometric_ref (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL,
    template_ref VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ubr_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 2 — Customer Management
-- ============================================================================

CREATE TABLE customer_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(150) NULL,
    dob DATE NULL,
    gender VARCHAR(10) NULL,
    aadhaar_last4 VARCHAR(4) NULL,
    aadhaar_hash VARCHAR(128) NULL,
    pan_number VARCHAR(15) NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    registered_by BIGINT UNSIGNED NULL,
    kyc_status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    is_blacklisted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_aadhaar_hash (aadhaar_hash),
    CONSTRAINT fk_cust_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_cust_registered_by FOREIGN KEY (registered_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_address (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'CURRENT',
    line1 VARCHAR(255) NOT NULL,
    line2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_caddr_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_family_member (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    relation VARCHAR(50) NOT NULL,
    mobile VARCHAR(15) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cfm_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_nominee (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    relation VARCHAR(50) NOT NULL,
    mobile VARCHAR(15) NULL,
    id_proof_type VARCHAR(30) NULL,
    id_proof_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cnom_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_duplicate_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    matched_customer_id BIGINT UNSIGNED NOT NULL,
    match_score DECIMAL(5,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING_REVIEW',
    reviewed_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cdl_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_cdl_matched FOREIGN KEY (matched_customer_id) REFERENCES customer_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_cdl_reviewer FOREIGN KEY (reviewed_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_merge_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    primary_customer_id BIGINT UNSIGNED NOT NULL,
    merged_customer_id BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cml_primary FOREIGN KEY (primary_customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_cml_merged FOREIGN KEY (merged_customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_cml_approver FOREIGN KEY (approved_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 3 — Aadhaar Verification
-- ============================================================================

CREATE TABLE kyc_aadhaar_verification (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    method VARCHAR(20) NOT NULL,
    uidai_reference_id VARCHAR(100) NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kav_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE kyc_aadhaar_xml_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    share_code VARCHAR(10) NULL,
    xml_file_ref VARCHAR(255) NOT NULL,
    is_valid_signature TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kax_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE kyc_face_auth_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    is_matched TINYINT(1) NOT NULL,
    confidence_score DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kfa_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 4 — PAN Verification
-- ============================================================================

CREATE TABLE kyc_pan_verification (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    pan_number VARCHAR(15) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    name_match TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kpv_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 5 — KYC Documents
-- ============================================================================

CREATE TABLE kyc_document_type (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE kyc_document_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    document_type_id BIGINT UNSIGNED NOT NULL,
    file_ref VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    verified_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kdm_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_kdm_doctype FOREIGN KEY (document_type_id) REFERENCES kyc_document_type(id),
    CONSTRAINT fk_kdm_verifier FOREIGN KEY (verified_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 6 — Customer Biometrics
-- ============================================================================

CREATE TABLE customer_biometric_ref (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL,
    file_ref VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cbr_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 7 — Jewellery Evaluation
-- ============================================================================

CREATE TABLE jewellery_category_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE gold_rate_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate_per_gram DECIMAL(10,2) NOT NULL,
    karat VARCHAR(5) NOT NULL,
    effective_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING_APPROVAL',
    proposed_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_grm_proposer FOREIGN KEY (proposed_by) REFERENCES user_master(id),
    CONSTRAINT fk_grm_approver FOREIGN KEY (approved_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE gold_rate_approval_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gold_rate_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(20) NOT NULL,
    actioned_by BIGINT UNSIGNED NOT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gral_rate FOREIGN KEY (gold_rate_id) REFERENCES gold_rate_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_gral_actor FOREIGN KEY (actioned_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE jewellery_item (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(40) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    hallmark_flag TINYINT(1) NOT NULL DEFAULT 0,
    gross_weight DECIMAL(8,3) NOT NULL,
    stone_weight DECIMAL(8,3) NOT NULL DEFAULT 0,
    net_weight DECIMAL(8,3) GENERATED ALWAYS AS (gross_weight - stone_weight) STORED,
    purity_karat VARCHAR(5) NOT NULL,
    gold_rate_id BIGINT UNSIGNED NOT NULL,
    applied_rate DECIMAL(10,2) NOT NULL,
    eligible_percentage DECIMAL(5,2) NOT NULL DEFAULT 75.00,
    eligible_amount DECIMAL(12,2) NOT NULL,
    evaluated_by BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'EVALUATED',
    loan_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ji_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_ji_category FOREIGN KEY (category_id) REFERENCES jewellery_category_master(id),
    CONSTRAINT fk_ji_rate FOREIGN KEY (gold_rate_id) REFERENCES gold_rate_master(id),
    CONSTRAINT fk_ji_evaluator FOREIGN KEY (evaluated_by) REFERENCES user_master(id)
    -- fk_ji_loan added after loan_master is created below
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Generated column above enforces net_weight = gross - stone at the DB level
-- (defense-in-depth alongside the same rule in the application service layer).

CREATE TABLE jewellery_image (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jewellery_item_id BIGINT UNSIGNED NOT NULL,
    file_ref VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_jim_item FOREIGN KEY (jewellery_item_id) REFERENCES jewellery_item(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 8 — Loan Calculation (loan_master added here — see header note)
-- ============================================================================

CREATE TABLE loan_product_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    interest_rate_pct DECIMAL(5,2) NOT NULL,
    interest_type VARCHAR(20) NOT NULL DEFAULT 'FLAT',
    tenure_months INT NOT NULL,
    processing_fee_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    gst_pct DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    insurance_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_account_number VARCHAR(30) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    loan_product_id BIGINT UNSIGNED NOT NULL,
    eligible_amount DECIMAL(12,2) NOT NULL,
    sanctioned_amount DECIMAL(12,2) NOT NULL,
    interest_rate_pct DECIMAL(5,2) NOT NULL,
    processing_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    gst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    insurance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    net_disbursed_amount DECIMAL(12,2) NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
    -- DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, DISBURSED, ACTIVE, RENEWED,
    -- PART_PAID, SETTLED, NPA, AUCTION_ELIGIBLE, AUCTIONED, CLOSED
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_loan_status (status),
    CONSTRAINT fk_lm_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_lm_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_lm_product FOREIGN KEY (loan_product_id) REFERENCES loan_product_master(id),
    CONSTRAINT fk_lm_creator FOREIGN KEY (created_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE jewellery_item
    ADD CONSTRAINT fk_ji_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id);

CREATE TABLE loan_calculation_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    eligible_amount DECIMAL(12,2) NOT NULL,
    interest_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    processing_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    gst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    insurance_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(12,2) NOT NULL,
    calculated_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lcl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lcl_actor FOREIGN KEY (calculated_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 9 — Loan Approval (Maker-Checker)
-- ============================================================================

CREATE TABLE loan_approval_limit_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    max_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lalm_role FOREIGN KEY (role_id) REFERENCES role_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_approval_workflow (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    current_stage VARCHAR(30) NOT NULL DEFAULT 'APPRAISER',
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_law_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_approval_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    stage VARCHAR(30) NOT NULL,
    action VARCHAR(20) NOT NULL,
    actioned_by BIGINT UNSIGNED NOT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Maker-Checker enforced at service layer: actioned_by must never equal loan_master.created_by
    CONSTRAINT fk_lal_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lal_actor FOREIGN KEY (actioned_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 10 — Loan Disbursement
-- ============================================================================

CREATE TABLE disbursement_mode_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_disbursement (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    mode_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reference_number VARCHAR(60) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    disbursed_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ld_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_ld_mode FOREIGN KEY (mode_id) REFERENCES disbursement_mode_master(id),
    CONSTRAINT fk_ld_actor FOREIGN KEY (disbursed_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 11 — Loan Renewal
-- ============================================================================

CREATE TABLE loan_renewal_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    renewed_tenure_months INT NOT NULL,
    interest_paid DECIMAL(10,2) NOT NULL,
    renewal_charges DECIMAL(10,2) NOT NULL DEFAULT 0,
    new_due_date DATE NOT NULL,
    processed_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lrl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lrl_actor FOREIGN KEY (processed_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 12 — Top-Up Loan
-- ============================================================================

CREATE TABLE loan_topup_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    eligible_topup_amount DECIMAL(12,2) NOT NULL,
    approved_amount DECIMAL(12,2) NULL,
    processing_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ltl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_ltl_approver FOREIGN KEY (approved_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 13 — Interest Collection
-- ============================================================================

CREATE TABLE interest_collection_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    mode VARCHAR(20) NOT NULL,
    collected_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_icl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_icl_actor FOREIGN KEY (collected_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE interest_receipt (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interest_collection_log_id BIGINT UNSIGNED NOT NULL,
    receipt_number VARCHAR(30) NOT NULL UNIQUE,
    printed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ir_collection FOREIGN KEY (interest_collection_log_id) REFERENCES interest_collection_log(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 14 — Part Payment / Excess & Reload
-- ============================================================================

CREATE TABLE loan_part_payment_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    principal_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    interest_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    collected_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lppl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lppl_actor FOREIGN KEY (collected_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_reload_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    excess_amount_eligible DECIMAL(12,2) NOT NULL,
    reload_amount DECIMAL(12,2) NOT NULL,
    processed_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lrel_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lrel_actor FOREIGN KEY (processed_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 15 — Settlement / Full Closure
-- ============================================================================

CREATE TABLE loan_closure (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    total_amount_collected DECIMAL(12,2) NOT NULL,
    closure_date DATE NOT NULL,
    closed_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lc_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_lc_actor FOREIGN KEY (closed_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE loan_closure_charge (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_closure_id BIGINT UNSIGNED NOT NULL,
    charge_type VARCHAR(30) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lcc_closure FOREIGN KEY (loan_closure_id) REFERENCES loan_closure(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 16 — Gold Release
-- ============================================================================

CREATE TABLE gold_release_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    jewellery_item_id BIGINT UNSIGNED NOT NULL,
    id_proof_verified TINYINT(1) NOT NULL DEFAULT 0,
    signature_captured TINYINT(1) NOT NULL DEFAULT 0,
    photo_captured TINYINT(1) NOT NULL DEFAULT 0,
    released_by BIGINT UNSIGNED NOT NULL,
    released_to VARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    released_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- All three gates above must be true before status can move to RELEASED
    -- (enforced in the application service layer).
    CONSTRAINT fk_grl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_grl_item FOREIGN KEY (jewellery_item_id) REFERENCES jewellery_item(id),
    CONSTRAINT fk_grl_actor FOREIGN KEY (released_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 23 — Inventory (created before Module 17 Auction, per its own FK need)
-- ============================================================================

CREATE TABLE vault_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vm_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE gold_packet (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    packet_code VARCHAR(40) NOT NULL UNIQUE,
    jewellery_item_id BIGINT UNSIGNED NOT NULL,
    vault_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'IN_VAULT',
    -- IN_VAULT, PLEDGED, RELEASED, AUCTION_ELIGIBLE, AUCTIONED
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gp_item FOREIGN KEY (jewellery_item_id) REFERENCES jewellery_item(id),
    CONSTRAINT fk_gp_vault FOREIGN KEY (vault_id) REFERENCES vault_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE packet_transfer_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gold_packet_id BIGINT UNSIGNED NOT NULL,
    from_vault_id BIGINT UNSIGNED NULL,
    to_vault_id BIGINT UNSIGNED NOT NULL,
    transferred_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ptl_packet FOREIGN KEY (gold_packet_id) REFERENCES gold_packet(id) ON DELETE CASCADE,
    CONSTRAINT fk_ptl_from FOREIGN KEY (from_vault_id) REFERENCES vault_master(id),
    CONSTRAINT fk_ptl_to FOREIGN KEY (to_vault_id) REFERENCES vault_master(id),
    CONSTRAINT fk_ptl_actor FOREIGN KEY (transferred_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE packet_tracking_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gold_packet_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL,
    remarks VARCHAR(255) NULL,
    logged_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ptrl_packet FOREIGN KEY (gold_packet_id) REFERENCES gold_packet(id) ON DELETE CASCADE,
    CONSTRAINT fk_ptrl_actor FOREIGN KEY (logged_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 17 — Auction
-- ============================================================================

CREATE TABLE auction_schedule (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    auction_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'SCHEDULED',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_as_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_as_creator FOREIGN KEY (created_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE auction_notice_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auction_schedule_id BIGINT UNSIGNED NOT NULL,
    loan_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(20) NOT NULL,
    sent_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_anl_schedule FOREIGN KEY (auction_schedule_id) REFERENCES auction_schedule(id) ON DELETE CASCADE,
    CONSTRAINT fk_anl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE auction_bidder (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auction_schedule_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    id_proof_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ab_schedule FOREIGN KEY (auction_schedule_id) REFERENCES auction_schedule(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE auction_bid (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auction_schedule_id BIGINT UNSIGNED NOT NULL,
    gold_packet_id BIGINT UNSIGNED NOT NULL,
    bidder_id BIGINT UNSIGNED NOT NULL,
    bid_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_abid_schedule FOREIGN KEY (auction_schedule_id) REFERENCES auction_schedule(id) ON DELETE CASCADE,
    CONSTRAINT fk_abid_packet FOREIGN KEY (gold_packet_id) REFERENCES gold_packet(id),
    CONSTRAINT fk_abid_bidder FOREIGN KEY (bidder_id) REFERENCES auction_bidder(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE auction_winner (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gold_packet_id BIGINT UNSIGNED NOT NULL,
    bidder_id BIGINT UNSIGNED NOT NULL,
    winning_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aw_packet FOREIGN KEY (gold_packet_id) REFERENCES gold_packet(id),
    CONSTRAINT fk_aw_bidder FOREIGN KEY (bidder_id) REFERENCES auction_bidder(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE auction_settlement (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    gold_packet_id BIGINT UNSIGNED NOT NULL,
    outstanding_loan_amount DECIMAL(12,2) NOT NULL,
    auction_amount DECIMAL(12,2) NOT NULL,
    remaining_balance_to_customer DECIMAL(12,2) NOT NULL DEFAULT 0,
    settled_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ast_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id),
    CONSTRAINT fk_ast_packet FOREIGN KEY (gold_packet_id) REFERENCES gold_packet(id),
    CONSTRAINT fk_ast_actor FOREIGN KEY (settled_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 18 — Notifications
-- ============================================================================

CREATE TABLE notification_template (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    channel VARCHAR(20) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notification_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'QUEUED',
    retry_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nl_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_nl_template FOREIGN KEY (template_id) REFERENCES notification_template(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 19 — Bluetooth Printing
-- ============================================================================

CREATE TABLE print_template_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type VARCHAR(30) NOT NULL, -- RECEIPT, LOAN_AGREEMENT, GOLD_PACKET_LABEL, BARCODE, QR
    template_body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 22 — Accounting
-- ============================================================================

CREATE TABLE gl_account_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(20) NOT NULL, -- ASSET, LIABILITY, INCOME, EXPENSE
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE voucher_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_number VARCHAR(30) NOT NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL, -- RECEIPT, PAYMENT, JOURNAL, CONTRA
    voucher_date DATE NOT NULL,
    source VARCHAR(40) NULL, -- e.g. LOAN_DISBURSEMENT#123 -- auto-posted, not manual
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vm2_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_vm2_creator FOREIGN KEY (created_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE voucher_detail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id BIGINT UNSIGNED NOT NULL,
    gl_account_id BIGINT UNSIGNED NOT NULL,
    debit DECIMAL(12,2) NOT NULL DEFAULT 0,
    credit DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vd_voucher FOREIGN KEY (voucher_id) REFERENCES voucher_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_vd_account FOREIGN KEY (gl_account_id) REFERENCES gl_account_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE cash_book (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    book_date DATE NOT NULL,
    opening_balance DECIMAL(12,2) NOT NULL,
    closing_balance DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_branch_date (branch_id, book_date),
    CONSTRAINT fk_cb_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE day_book (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    book_date DATE NOT NULL,
    total_receipts DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_payments DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_db_branch_date (branch_id, book_date),
    CONSTRAINT fk_db_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_ledger (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    loan_id BIGINT UNSIGNED NULL,
    particulars VARCHAR(255) NOT NULL,
    debit DECIMAL(12,2) NOT NULL DEFAULT 0,
    credit DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cl_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id),
    CONSTRAINT fk_cl_loan FOREIGN KEY (loan_id) REFERENCES loan_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE bank_reconciliation_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    statement_date DATE NOT NULL,
    bank_balance DECIMAL(12,2) NOT NULL,
    book_balance DECIMAL(12,2) NOT NULL,
    is_reconciled TINYINT(1) NOT NULL DEFAULT 0,
    reconciled_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_brl_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_brl_actor FOREIGN KEY (reconciled_by) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 24 — GPS / Location Tracking
-- ============================================================================

CREATE TABLE location_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    logged_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ll_user FOREIGN KEY (user_id) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE customer_visit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    visited_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cvl_user FOREIGN KEY (user_id) REFERENCES user_master(id),
    CONSTRAINT fk_cvl_customer FOREIGN KEY (customer_id) REFERENCES customer_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 25 — Offline Mode
-- ============================================================================

CREATE TABLE sync_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    payload JSON NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sq_user FOREIGN KEY (user_id) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE sync_conflict_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sync_queue_id BIGINT UNSIGNED NOT NULL,
    server_value JSON NOT NULL,
    client_value JSON NOT NULL,
    resolution VARCHAR(20) NULL, -- SERVER_WINS, MANUAL_REVIEW
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_scl_queue FOREIGN KEY (sync_queue_id) REFERENCES sync_queue(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 26 — Admin / Masters
-- (branch_master, role_master already created in Module 1 above)
-- ============================================================================

CREATE TABLE employee_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE, -- 1:1 with user_master; adds HR fields
    designation VARCHAR(100) NULL,
    date_of_joining DATE NULL,
    reporting_to BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_em_user FOREIGN KEY (user_id) REFERENCES user_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_em_manager FOREIGN KEY (reporting_to) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Same concept as loan_product_master (Module 8) at branch-assignment level --
-- kept separate per the spec doc's own Module 26 listing; consider collapsing
-- these two tables into one if your team confirms they don't need to differ.
CREATE TABLE loan_scheme_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    loan_product_id BIGINT UNSIGNED NOT NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_branch_product (branch_id, loan_product_id),
    CONSTRAINT fk_lsm_branch FOREIGN KEY (branch_id) REFERENCES branch_master(id),
    CONSTRAINT fk_lsm_product FOREIGN KEY (loan_product_id) REFERENCES loan_product_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE interest_slab_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    min_amount DECIMAL(12,2) NOT NULL,
    max_amount DECIMAL(12,2) NOT NULL,
    interest_rate_pct DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE charge_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL, -- PERCENTAGE, FLAT
    value DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE permission_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(60) NOT NULL,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE role_permission_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_role_permission (role_id, permission_id),
    CONSTRAINT fk_rpm_role FOREIGN KEY (role_id) REFERENCES role_master(id) ON DELETE CASCADE,
    CONSTRAINT fk_rpm_permission FOREIGN KEY (permission_id) REFERENCES permission_master(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE menu_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    role_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mm_parent FOREIGN KEY (parent_id) REFERENCES menu_master(id),
    CONSTRAINT fk_mm_role FOREIGN KEY (role_id) REFERENCES role_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 27 — Security
-- ============================================================================

CREATE TABLE security_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(50) NOT NULL, -- LOGIN_FAILED, ROOT_DETECTED, TOKEN_REVOKED, etc.
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sal_user FOREIGN KEY (user_id) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE device_integrity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(191) NOT NULL,
    is_rooted TINYINT(1) NOT NULL DEFAULT 0,
    is_screen_capture_blocked TINYINT(1) NOT NULL DEFAULT 1,
    checked_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dil_user FOREIGN KEY (user_id) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- Cross-cutting audit_log (referenced in the doc's "Compliance Modules Folded
-- In" section: "every module above writes to a shared audit_log table")
-- ============================================================================

CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(60) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(30) NOT NULL, -- CREATE, UPDATE, DELETE, APPROVE, REJECT
    before_value JSON NULL,
    after_value JSON NULL,
    actor_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_entity (entity_type, entity_id),
    CONSTRAINT fk_al_actor FOREIGN KEY (actor_id) REFERENCES user_master(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- MODULE 30 — Mobile Settings
-- ============================================================================

CREATE TABLE app_config_master (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
