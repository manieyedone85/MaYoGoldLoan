-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 07, 2026 at 05:10 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u163330700_mayo_gold`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_config_master`
--

CREATE TABLE `app_config_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_config_master`
--

INSERT INTO `app_config_master` (`id`, `config_key`, `config_value`, `created_at`, `updated_at`) VALUES
(1, 'CASH_DISBURSEMENT_LIMIT', '20000', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(2, 'OTP_EXPIRY_MINUTES', '5', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(3, 'JWT_TOKEN_EXPIRY_MINUTES', '60', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(4, 'DEFAULT_ELIGIBLE_PERCENTAGE', '75.00', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(5, 'DEFAULT_COMPANY_CODE', 'H001', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(6, 'SINGLE_DEVICE_ENFORCED_ROLES', 'BRANCH_EXECUTIVE,APPRAISER,CASHIER', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(7, 'NPA_DAYS_THRESHOLD', '90', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(8, 'APP_MIN_SUPPORTED_VERSION', '1.0.0', '2026-08-07 16:14:20', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `auction_bid`
--

CREATE TABLE `auction_bid` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `bidder_id` bigint(20) UNSIGNED NOT NULL,
  `bid_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auction_bidder`
--

CREATE TABLE `auction_bidder` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `id_proof_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auction_notice_log`
--

CREATE TABLE `auction_notice_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `channel` varchar(20) NOT NULL,
  `sent_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auction_schedule`
--

CREATE TABLE `auction_schedule` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `auction_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'SCHEDULED',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auction_settlement`
--

CREATE TABLE `auction_settlement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `outstanding_loan_amount` decimal(12,2) NOT NULL,
  `auction_amount` decimal(12,2) NOT NULL,
  `remaining_balance_to_customer` decimal(12,2) NOT NULL DEFAULT 0.00,
  `settled_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auction_winner`
--

CREATE TABLE `auction_winner` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `bidder_id` bigint(20) UNSIGNED NOT NULL,
  `winning_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(30) NOT NULL,
  `before_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_value`)),
  `after_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_value`)),
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_reconciliation_log`
--

CREATE TABLE `bank_reconciliation_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `statement_date` date NOT NULL,
  `bank_balance` decimal(12,2) NOT NULL,
  `book_balance` decimal(12,2) NOT NULL,
  `is_reconciled` tinyint(1) NOT NULL DEFAULT 0,
  `reconciled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `company_code` varchar(10) NOT NULL DEFAULT 'H001',
  `name` varchar(150) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_master`
--

CREATE TABLE `branch_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `company_code` varchar(10) NOT NULL DEFAULT 'H001',
  `name` varchar(150) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_master`
--

INSERT INTO `branch_master` (`id`, `branch_code`, `company_code`, `name`, `city`, `state`, `latitude`, `longitude`, `gst_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'H001-BR001', 'H001', 'Swarna Gold Loan - Chennai Main', 'Chennai', 'Tamil Nadu', NULL, NULL, NULL, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(2, 'H001-BR002', 'H001', 'Swarna Gold Loan - Tambaram', 'Chennai', 'Tamil Nadu', NULL, NULL, NULL, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `cash_book`
--

CREATE TABLE `cash_book` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `book_date` date NOT NULL,
  `opening_balance` decimal(12,2) NOT NULL,
  `closing_balance` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `charge_master`
--

CREATE TABLE `charge_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charge_master`
--

INSERT INTO `charge_master` (`id`, `code`, `name`, `type`, `value`, `created_at`) VALUES
(1, 'PROC_FEE_STD', 'Standard Processing Fee', 'PERCENTAGE', 1.00, '2026-08-07 16:14:20'),
(2, 'GST_STD', 'GST on Processing Fee', 'PERCENTAGE', 18.00, '2026-08-07 16:14:20'),
(3, 'INSURANCE_STD', 'Gold Insurance', 'PERCENTAGE', 0.25, '2026-08-07 16:14:20'),
(4, 'LATE_FEE_FLAT', 'Late Payment Fee', 'FLAT', 100.00, '2026-08-07 16:14:20'),
(5, 'DUPLICATE_RECEIPT_FEE', 'Duplicate Receipt Fee', 'FLAT', 50.00, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `customer_address`
--

CREATE TABLE `customer_address` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'CURRENT',
  `line1` varchar(255) NOT NULL,
  `line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_biometric_ref`
--

CREATE TABLE `customer_biometric_ref` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `file_ref` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_duplicate_log`
--

CREATE TABLE `customer_duplicate_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `matched_customer_id` bigint(20) UNSIGNED NOT NULL,
  `match_score` decimal(5,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING_REVIEW',
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_family_member`
--

CREATE TABLE `customer_family_member` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `relation` varchar(50) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_ledger`
--

CREATE TABLE `customer_ledger` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `particulars` varchar(255) NOT NULL,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_master`
--

CREATE TABLE `customer_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `aadhaar_last4` varchar(4) DEFAULT NULL,
  `aadhaar_hash` varchar(128) DEFAULT NULL,
  `pan_number` varchar(15) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `registered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `kyc_status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_merge_log`
--

CREATE TABLE `customer_merge_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `primary_customer_id` bigint(20) UNSIGNED NOT NULL,
  `merged_customer_id` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_nominee`
--

CREATE TABLE `customer_nominee` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `relation` varchar(50) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `id_proof_type` varchar(30) DEFAULT NULL,
  `id_proof_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_visit_log`
--

CREATE TABLE `customer_visit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `visited_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `day_book`
--

CREATE TABLE `day_book` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `book_date` date NOT NULL,
  `total_receipts` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_payments` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_integrity_log`
--

CREATE TABLE `device_integrity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` varchar(191) NOT NULL,
  `is_rooted` tinyint(1) NOT NULL DEFAULT 0,
  `is_screen_capture_blocked` tinyint(1) NOT NULL DEFAULT 1,
  `checked_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disbursement_mode_master`
--

CREATE TABLE `disbursement_mode_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disbursement_mode_master`
--

INSERT INTO `disbursement_mode_master` (`id`, `code`, `name`, `created_at`) VALUES
(1, 'CASH', 'Cash', '2026-08-07 16:14:20'),
(2, 'IMPS', 'IMPS', '2026-08-07 16:14:20'),
(3, 'RTGS', 'RTGS', '2026-08-07 16:14:20'),
(4, 'NEFT', 'NEFT', '2026-08-07 16:14:20'),
(5, 'UPI', 'UPI', '2026-08-07 16:14:20'),
(6, 'BANK_TRANSFER', 'Bank Transfer', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `employee_master`
--

CREATE TABLE `employee_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `reporting_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gl_account_master`
--

CREATE TABLE `gl_account_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gl_account_master`
--

INSERT INTO `gl_account_master` (`id`, `code`, `name`, `type`, `created_at`) VALUES
(1, '1000', 'Cash in Hand', 'ASSET', '2026-08-07 16:14:20'),
(2, '1010', 'Bank Account - Current', 'ASSET', '2026-08-07 16:14:20'),
(3, '1100', 'Loans Receivable - Gold Loan', 'ASSET', '2026-08-07 16:14:20'),
(4, '1200', 'Gold Inventory (Pledged)', 'ASSET', '2026-08-07 16:14:20'),
(5, '2000', 'Customer Deposits Payable', 'LIABILITY', '2026-08-07 16:14:20'),
(6, '2100', 'GST Payable', 'LIABILITY', '2026-08-07 16:14:20'),
(7, '2200', 'TDS Payable', 'LIABILITY', '2026-08-07 16:14:20'),
(8, '4000', 'Interest Income', 'INCOME', '2026-08-07 16:14:20'),
(9, '4100', 'Processing Fee Income', 'INCOME', '2026-08-07 16:14:20'),
(10, '4200', 'Auction Surplus Income', 'INCOME', '2026-08-07 16:14:20'),
(11, '5000', 'Staff Salary Expense', 'EXPENSE', '2026-08-07 16:14:20'),
(12, '5100', 'Branch Rent Expense', 'EXPENSE', '2026-08-07 16:14:20'),
(13, '5200', 'Bad Debt / NPA Write-off', 'EXPENSE', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `gold_packet`
--

CREATE TABLE `gold_packet` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `packet_code` varchar(40) NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `vault_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'IN_VAULT',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gold_rate_approval_log`
--

CREATE TABLE `gold_rate_approval_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_rate_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(20) NOT NULL,
  `actioned_by` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gold_rate_master`
--

CREATE TABLE `gold_rate_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rate_per_gram` decimal(10,2) NOT NULL,
  `karat` varchar(5) NOT NULL,
  `effective_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING_APPROVAL',
  `proposed_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gold_rate_master`
--

INSERT INTO `gold_rate_master` (`id`, `rate_per_gram`, `karat`, `effective_date`, `status`, `proposed_by`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 6250.00, '22K', '2026-08-07', 'APPROVED', 1, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(2, 5115.00, '18K', '2026-08-07', 'APPROVED', 1, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `gold_release_log`
--

CREATE TABLE `gold_release_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `id_proof_verified` tinyint(1) NOT NULL DEFAULT 0,
  `signature_captured` tinyint(1) NOT NULL DEFAULT 0,
  `photo_captured` tinyint(1) NOT NULL DEFAULT 0,
  `released_by` bigint(20) UNSIGNED NOT NULL,
  `released_to` varchar(150) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `released_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interest_collection_log`
--

CREATE TABLE `interest_collection_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interest_receipt`
--

CREATE TABLE `interest_receipt` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `interest_collection_log_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(30) NOT NULL,
  `printed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interest_slab_master`
--

CREATE TABLE `interest_slab_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `min_amount` decimal(12,2) NOT NULL,
  `max_amount` decimal(12,2) NOT NULL,
  `interest_rate_pct` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_slab_master`
--

INSERT INTO `interest_slab_master` (`id`, `min_amount`, `max_amount`, `interest_rate_pct`, `created_at`) VALUES
(1, 0.00, 50000.00, 13.50, '2026-08-07 16:14:20'),
(2, 50000.01, 200000.00, 12.00, '2026-08-07 16:14:20'),
(3, 200000.01, 1000000.00, 10.50, '2026-08-07 16:14:20'),
(4, 1000000.01, 99999999.00, 9.00, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `jewellery_category_master`
--

CREATE TABLE `jewellery_category_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jewellery_category_master`
--

INSERT INTO `jewellery_category_master` (`id`, `code`, `name`, `created_at`) VALUES
(1, 'CHAIN', 'Chain', '2026-08-07 16:14:20'),
(2, 'RING', 'Ring', '2026-08-07 16:14:20'),
(3, 'BANGLE', 'Bangle', '2026-08-07 16:14:20'),
(4, 'NECKLACE', 'Necklace', '2026-08-07 16:14:20'),
(5, 'EARRING', 'Earring', '2026-08-07 16:14:20'),
(6, 'COIN', 'Gold Coin', '2026-08-07 16:14:20'),
(7, 'BRACELET', 'Bracelet', '2026-08-07 16:14:20'),
(8, 'ANKLET', 'Anklet', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `jewellery_image`
--

CREATE TABLE `jewellery_image` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `file_ref` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jewellery_item`
--

CREATE TABLE `jewellery_item` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `barcode` varchar(40) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `hallmark_flag` tinyint(1) NOT NULL DEFAULT 0,
  `gross_weight` decimal(8,3) NOT NULL,
  `stone_weight` decimal(8,3) NOT NULL DEFAULT 0.000,
  `net_weight` decimal(8,3) GENERATED ALWAYS AS (`gross_weight` - `stone_weight`) STORED,
  `purity_karat` varchar(5) NOT NULL,
  `gold_rate_id` bigint(20) UNSIGNED NOT NULL,
  `applied_rate` decimal(10,2) NOT NULL,
  `eligible_percentage` decimal(5,2) NOT NULL DEFAULT 75.00,
  `eligible_amount` decimal(12,2) NOT NULL,
  `evaluated_by` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'EVALUATED',
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_aadhaar_verification`
--

CREATE TABLE `kyc_aadhaar_verification` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(20) NOT NULL,
  `uidai_reference_id` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_aadhaar_xml_log`
--

CREATE TABLE `kyc_aadhaar_xml_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `share_code` varchar(10) DEFAULT NULL,
  `xml_file_ref` varchar(255) NOT NULL,
  `is_valid_signature` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_document_master`
--

CREATE TABLE `kyc_document_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `document_type_id` bigint(20) UNSIGNED NOT NULL,
  `file_ref` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_document_type`
--

CREATE TABLE `kyc_document_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_document_type`
--

INSERT INTO `kyc_document_type` (`id`, `code`, `name`, `created_at`) VALUES
(1, 'VOTER_ID', 'Voter ID', '2026-08-07 16:14:20'),
(2, 'DRIVING_LICENSE', 'Driving License', '2026-08-07 16:14:20'),
(3, 'PASSPORT', 'Passport', '2026-08-07 16:14:20'),
(4, 'UTILITY_BILL', 'Utility Bill', '2026-08-07 16:14:20'),
(5, 'BANK_PASSBOOK', 'Bank Passbook', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_face_auth_log`
--

CREATE TABLE `kyc_face_auth_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `is_matched` tinyint(1) NOT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_pan_verification`
--

CREATE TABLE `kyc_pan_verification` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `pan_number` varchar(15) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `name_match` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_limit_master`
--

CREATE TABLE `loan_approval_limit_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `max_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_approval_limit_master`
--

INSERT INTO `loan_approval_limit_master` (`id`, `role_id`, `max_amount`, `created_at`) VALUES
(1, 5, 200000.00, '2026-08-07 16:14:20'),
(2, 6, 1000000.00, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_log`
--

CREATE TABLE `loan_approval_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(30) NOT NULL,
  `action` varchar(20) NOT NULL,
  `actioned_by` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_workflow`
--

CREATE TABLE `loan_approval_workflow` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `current_stage` varchar(30) NOT NULL DEFAULT 'APPRAISER',
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_calculation_log`
--

CREATE TABLE `loan_calculation_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_amount` decimal(12,2) NOT NULL,
  `interest_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insurance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL,
  `calculated_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_closure`
--

CREATE TABLE `loan_closure` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount_collected` decimal(12,2) NOT NULL,
  `closure_date` date NOT NULL,
  `closed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_closure_charge`
--

CREATE TABLE `loan_closure_charge` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_closure_id` bigint(20) UNSIGNED NOT NULL,
  `charge_type` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_disbursement`
--

CREATE TABLE `loan_disbursement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `mode_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_number` varchar(60) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `disbursed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_master`
--

CREATE TABLE `loan_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_account_number` varchar(30) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `loan_product_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_amount` decimal(12,2) NOT NULL,
  `sanctioned_amount` decimal(12,2) NOT NULL,
  `interest_rate_pct` decimal(5,2) NOT NULL,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insurance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_disbursed_amount` decimal(12,2) DEFAULT NULL,
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'DRAFT',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_part_payment_log`
--

CREATE TABLE `loan_part_payment_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `principal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `interest_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_product_master`
--

CREATE TABLE `loan_product_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `interest_rate_pct` decimal(5,2) NOT NULL,
  `interest_type` varchar(20) NOT NULL DEFAULT 'FLAT',
  `tenure_months` int(11) NOT NULL,
  `processing_fee_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `gst_pct` decimal(5,2) NOT NULL DEFAULT 18.00,
  `insurance_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_product_master`
--

INSERT INTO `loan_product_master` (`id`, `code`, `name`, `interest_rate_pct`, `interest_type`, `tenure_months`, `processing_fee_pct`, `gst_pct`, `insurance_pct`, `is_active`, `created_at`) VALUES
(1, 'GL-STD-12', 'Standard Gold Loan - 12 Month', 12.00, 'FLAT', 12, 1.00, 18.00, 0.25, 1, '2026-08-07 16:14:20'),
(2, 'GL-STD-6', 'Standard Gold Loan - 6 Month', 13.50, 'FLAT', 6, 1.00, 18.00, 0.25, 1, '2026-08-07 16:14:20'),
(3, 'GL-PREMIUM-12', 'Premium Gold Loan - 12 Month', 10.50, 'REDUCING', 12, 0.50, 18.00, 0.25, 1, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `loan_reload_log`
--

CREATE TABLE `loan_reload_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `excess_amount_eligible` decimal(12,2) NOT NULL,
  `reload_amount` decimal(12,2) NOT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_renewal_log`
--

CREATE TABLE `loan_renewal_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `renewed_tenure_months` int(11) NOT NULL,
  `interest_paid` decimal(10,2) NOT NULL,
  `renewal_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `new_due_date` date NOT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_scheme_master`
--

CREATE TABLE `loan_scheme_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `loan_product_id` bigint(20) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_scheme_master`
--

INSERT INTO `loan_scheme_master` (`id`, `branch_id`, `loan_product_id`, `is_enabled`, `created_at`) VALUES
(1, 1, 3, 1, '2026-08-07 16:14:20'),
(2, 2, 3, 1, '2026-08-07 16:14:20'),
(3, 1, 1, 1, '2026-08-07 16:14:20'),
(4, 2, 1, 1, '2026-08-07 16:14:20'),
(5, 1, 2, 1, '2026-08-07 16:14:20'),
(6, 2, 2, 1, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `loan_topup_log`
--

CREATE TABLE `loan_topup_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_topup_amount` decimal(12,2) NOT NULL,
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `location_log`
--

CREATE TABLE `location_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `logged_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_master`
--

CREATE TABLE `menu_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_master`
--

INSERT INTO `menu_master` (`id`, `code`, `name`, `parent_id`, `role_id`, `is_active`, `created_at`) VALUES
(1, 'CUSTOMER_REG', 'Customer Registration', NULL, 2, 1, '2026-08-07 16:14:20'),
(2, 'JEWELLERY_EVAL', 'Jewellery Evaluation', NULL, 3, 1, '2026-08-07 16:14:20'),
(3, 'CASH_COLLECTION', 'Cash Collection', NULL, 4, 1, '2026-08-07 16:14:20'),
(4, 'LOAN_APPROVAL', 'Loan Approval', NULL, 5, 1, '2026-08-07 16:14:20'),
(5, 'AUCTION_MGMT', 'Auction Management', NULL, 6, 1, '2026-08-07 16:14:20'),
(6, 'ADMIN_MASTERS', 'Masters', NULL, 10, 1, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_01_01_000000_create_roles_table', 1),
(6, '2025_01_02_000000_create_permissions_tables', 1),
(7, '2025_01_03_000000_create_branches_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification_log`
--

CREATE TABLE `notification_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `template_id` bigint(20) UNSIGNED NOT NULL,
  `channel` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'QUEUED',
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_template`
--

CREATE TABLE `notification_template` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_template`
--

INSERT INTO `notification_template` (`id`, `code`, `channel`, `body`, `created_at`) VALUES
(1, 'LOAN_APPROVED', 'SMS', 'Dear {customer_name}, your gold loan {loan_account_number} for Rs.{sanctioned_amount} has been approved. - Swarna Gold Loan', '2026-08-07 16:14:20'),
(2, 'LOAN_DISBURSED', 'SMS', 'Dear {customer_name}, Rs.{net_disbursed_amount} has been disbursed to your account against loan {loan_account_number}.', '2026-08-07 16:14:20'),
(3, 'INTEREST_DUE_REMINDER', 'SMS', 'Dear {customer_name}, interest of Rs.{interest_due} is due on loan {loan_account_number} by {due_date}.', '2026-08-07 16:14:20'),
(4, 'AUCTION_NOTICE', 'SMS', 'Dear {customer_name}, your pledged gold under loan {loan_account_number} is scheduled for auction on {auction_date} due to non-payment. Please contact your branch immediately.', '2026-08-07 16:14:20'),
(5, 'OTP_LOGIN', 'SMS', 'Your Swarna Gold Loan OTP is {otp}. Valid for 5 minutes. Do not share this with anyone.', '2026-08-07 16:14:20'),
(6, 'LOAN_RENEWED', 'WHATSAPP', 'Your loan {loan_account_number} has been renewed successfully. New due date: {new_due_date}.', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `packet_tracking_log`
--

CREATE TABLE `packet_tracking_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `logged_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packet_transfer_log`
--

CREATE TABLE `packet_transfer_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `from_vault_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_vault_id` bigint(20) UNSIGNED NOT NULL,
  `transferred_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(100) NOT NULL,
  `module` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_master`
--

CREATE TABLE `permission_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(100) NOT NULL,
  `module` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permission_master`
--

INSERT INTO `permission_master` (`id`, `code`, `module`, `name`, `created_at`) VALUES
(1, 'customer.create', 'CUSTOMER', 'Create customer', '2026-08-07 16:14:20'),
(2, 'customer.merge', 'CUSTOMER', 'Merge duplicate customers', '2026-08-07 16:14:20'),
(3, 'jewellery.evaluate', 'JEWELLERY', 'Evaluate jewellery and set eligible amount', '2026-08-07 16:14:20'),
(4, 'gold_rate.propose', 'JEWELLERY', 'Propose a new gold rate', '2026-08-07 16:14:20'),
(5, 'gold_rate.approve', 'JEWELLERY', 'Approve a proposed gold rate', '2026-08-07 16:14:20'),
(6, 'loan.create', 'LOAN', 'Create a loan', '2026-08-07 16:14:20'),
(7, 'loan.approve', 'LOAN', 'Approve a loan', '2026-08-07 16:14:20'),
(8, 'loan.override', 'LOAN', 'Override loan approval', '2026-08-07 16:14:20'),
(9, 'loan.disburse', 'LOAN', 'Disburse a loan', '2026-08-07 16:14:20'),
(10, 'interest.collect', 'LOAN', 'Collect interest payment', '2026-08-07 16:14:20'),
(11, 'loan.settle', 'LOAN', 'Settle / close a loan', '2026-08-07 16:14:20'),
(12, 'gold_release.complete', 'LOAN', 'Complete gold release', '2026-08-07 16:14:20'),
(13, 'auction.manage', 'AUCTION', 'Schedule and manage auctions', '2026-08-07 16:14:20'),
(14, 'accounting.voucher.create', 'ACCOUNTING', 'Create accounting voucher', '2026-08-07 16:14:20'),
(15, 'master.manage', 'ADMIN', 'Manage master data', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_template_master`
--

CREATE TABLE `print_template_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` varchar(30) NOT NULL,
  `template_body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `print_template_master`
--

INSERT INTO `print_template_master` (`id`, `code`, `type`, `template_body`, `created_at`) VALUES
(1, 'RECEIPT_STD', 'RECEIPT', 'Receipt No: {receipt_number}\nCustomer: {customer_name}\nAmount: Rs.{amount}\nDate: {date}', '2026-08-07 16:14:20'),
(2, 'LOAN_AGREEMENT_STD', 'LOAN_AGREEMENT', 'Loan Agreement - {loan_account_number}\nCustomer: {customer_name}\nSanctioned: Rs.{sanctioned_amount}\nTerms apply as per schedule.', '2026-08-07 16:14:20'),
(3, 'GOLD_PACKET_LABEL_STD', 'GOLD_PACKET_LABEL', 'Packet: {packet_code}\nCustomer: {customer_name}\nLoan: {loan_account_number}\nNet Wt: {net_weight}g', '2026-08-07 16:14:20'),
(4, 'BARCODE_STD', 'BARCODE', '{barcode}', '2026-08-07 16:14:20'),
(5, 'QR_STD', 'QR', '{qr_payload}', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_master`
--

CREATE TABLE `role_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_master`
--

INSERT INTO `role_master` (`id`, `code`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CUSTOMER', 'Customer', 'End borrower — app access to loan status, payments, EMI', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(2, 'BRANCH_EXECUTIVE', 'Branch Executive', 'Customer registration, gold entry, loan creation', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(3, 'APPRAISER', 'Gold Appraiser', 'Jewellery verification: weight, purity, hallmark, eligible amount', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(4, 'CASHIER', 'Cashier', 'Cash collection, disbursement, receipts', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(5, 'BRANCH_MANAGER', 'Branch Manager', 'Loan approval, override, cash verification, reports', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(6, 'REGIONAL_MANAGER', 'Regional Manager', 'Branch monitoring, high-value loan approval, audit', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(7, 'OPERATIONS', 'Operations Team', 'Loan monitoring, branch monitoring, exception handling', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(8, 'FINANCE', 'Finance Team', 'Accounting, GL posting, GST, TDS', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(9, 'AUDITOR', 'Auditor', 'Audit trail, user activity, deleted records, loan changes', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(10, 'ADMIN', 'Admin', 'Masters, branches, employees, roles, configuration', '2026-08-07 16:14:20', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permission_map`
--

CREATE TABLE `role_permission_map` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permission_map`
--

INSERT INTO `role_permission_map` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 3, 4, '2026-08-07 16:14:20'),
(2, 3, 3, '2026-08-07 16:14:20'),
(4, 5, 13, '2026-08-07 16:14:20'),
(5, 5, 5, '2026-08-07 16:14:20'),
(6, 5, 12, '2026-08-07 16:14:20'),
(7, 5, 7, '2026-08-07 16:14:20'),
(8, 5, 11, '2026-08-07 16:14:20'),
(11, 6, 13, '2026-08-07 16:14:20'),
(12, 6, 2, '2026-08-07 16:14:20'),
(13, 6, 5, '2026-08-07 16:14:20'),
(14, 6, 7, '2026-08-07 16:14:20'),
(15, 6, 8, '2026-08-07 16:14:20'),
(18, 4, 10, '2026-08-07 16:14:20'),
(19, 4, 9, '2026-08-07 16:14:20'),
(20, 4, 11, '2026-08-07 16:14:20'),
(21, 2, 1, '2026-08-07 16:14:20'),
(22, 2, 6, '2026-08-07 16:14:20'),
(24, 8, 14, '2026-08-07 16:14:20'),
(25, 10, 15, '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `security_audit_log`
--

CREATE TABLE `security_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_conflict_log`
--

CREATE TABLE `sync_conflict_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sync_queue_id` bigint(20) UNSIGNED NOT NULL,
  `server_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`server_value`)),
  `client_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`client_value`)),
  `resolution` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_queue`
--

CREATE TABLE `sync_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `role_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'mani', '1234@gmail.com', '2026-08-07 21:50:30', '$2y$10$eeBxh8cazPKyz6AQC7NQZOFvfdL2DI.neSCXOepI0.94GIiq83ujC', NULL, 10, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_biometric_ref`
--

CREATE TABLE `user_biometric_ref` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `template_ref` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_device_binding`
--

CREATE TABLE `user_device_binding` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` varchar(191) NOT NULL,
  `device_model` varchar(100) DEFAULT NULL,
  `push_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `bound_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_master`
--

CREATE TABLE `user_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_code` varchar(30) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mpin_hash` varchar(255) DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_master`
--

INSERT INTO `user_master` (`id`, `employee_code`, `name`, `mobile`, `email`, `password`, `mpin_hash`, `role_id`, `branch_id`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'H001-EMP0001', 'System Admin', '9999999999', 'admin@swarnagoldloan.example', '$2y$10$REPLACE_WITH_REAL_HASH', NULL, 10, 1, 1, NULL, '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(3, 'H001-EMP0002', 'System Admin', '9999999988', '1234@gmail.com', '$2y$10$eeBxh8cazPKyz6AQC7NQZOFvfdL2DI.neSCXOepI0.94GIiq83ujC', NULL, 10, 1, 1, NULL, '2026-08-07 16:14:20', '2026-08-07 16:52:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_otp_log`
--

CREATE TABLE `user_otp_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` varchar(30) NOT NULL DEFAULT 'LOGIN',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_role_map`
--

CREATE TABLE `user_role_map` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_session_log`
--

CREATE TABLE `user_session_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` varchar(191) DEFAULT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vault_master`
--

CREATE TABLE `vault_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vault_master`
--

INSERT INTO `vault_master` (`id`, `branch_id`, `name`, `created_at`) VALUES
(1, 1, 'Swarna Gold Loan - Chennai Main - Main Vault', '2026-08-07 16:14:20'),
(2, 2, 'Swarna Gold Loan - Tambaram - Main Vault', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_detail`
--

CREATE TABLE `voucher_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED NOT NULL,
  `gl_account_id` bigint(20) UNSIGNED NOT NULL,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voucher_master`
--

CREATE TABLE `voucher_master` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_number` varchar(30) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `voucher_date` date NOT NULL,
  `source` varchar(40) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_config_master`
--
ALTER TABLE `app_config_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `auction_bid`
--
ALTER TABLE `auction_bid`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_abid_schedule` (`auction_schedule_id`),
  ADD KEY `fk_abid_packet` (`gold_packet_id`),
  ADD KEY `fk_abid_bidder` (`bidder_id`);

--
-- Indexes for table `auction_bidder`
--
ALTER TABLE `auction_bidder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ab_schedule` (`auction_schedule_id`);

--
-- Indexes for table `auction_notice_log`
--
ALTER TABLE `auction_notice_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_anl_schedule` (`auction_schedule_id`),
  ADD KEY `fk_anl_loan` (`loan_id`);

--
-- Indexes for table `auction_schedule`
--
ALTER TABLE `auction_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_as_branch` (`branch_id`),
  ADD KEY `fk_as_creator` (`created_by`);

--
-- Indexes for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ast_loan` (`loan_id`),
  ADD KEY `fk_ast_packet` (`gold_packet_id`),
  ADD KEY `fk_ast_actor` (`settled_by`);

--
-- Indexes for table `auction_winner`
--
ALTER TABLE `auction_winner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aw_packet` (`gold_packet_id`),
  ADD KEY `fk_aw_bidder` (`bidder_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `fk_al_actor` (`actor_id`);

--
-- Indexes for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_brl_branch` (`branch_id`),
  ADD KEY `fk_brl_actor` (`reconciled_by`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_branch_code_unique` (`branch_code`);

--
-- Indexes for table `branch_master`
--
ALTER TABLE `branch_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`);

--
-- Indexes for table `cash_book`
--
ALTER TABLE `cash_book`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_branch_date` (`branch_id`,`book_date`);

--
-- Indexes for table `charge_master`
--
ALTER TABLE `charge_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caddr_customer` (`customer_id`);

--
-- Indexes for table `customer_biometric_ref`
--
ALTER TABLE `customer_biometric_ref`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cbr_customer` (`customer_id`);

--
-- Indexes for table `customer_duplicate_log`
--
ALTER TABLE `customer_duplicate_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cdl_customer` (`customer_id`),
  ADD KEY `fk_cdl_matched` (`matched_customer_id`),
  ADD KEY `fk_cdl_reviewer` (`reviewed_by`);

--
-- Indexes for table `customer_family_member`
--
ALTER TABLE `customer_family_member`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cfm_customer` (`customer_id`);

--
-- Indexes for table `customer_ledger`
--
ALTER TABLE `customer_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cl_customer` (`customer_id`),
  ADD KEY `fk_cl_loan` (`loan_id`);

--
-- Indexes for table `customer_master`
--
ALTER TABLE `customer_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_aadhaar_hash` (`aadhaar_hash`),
  ADD KEY `fk_cust_branch` (`branch_id`),
  ADD KEY `fk_cust_registered_by` (`registered_by`);

--
-- Indexes for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cml_primary` (`primary_customer_id`),
  ADD KEY `fk_cml_merged` (`merged_customer_id`),
  ADD KEY `fk_cml_approver` (`approved_by`);

--
-- Indexes for table `customer_nominee`
--
ALTER TABLE `customer_nominee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cnom_customer` (`customer_id`);

--
-- Indexes for table `customer_visit_log`
--
ALTER TABLE `customer_visit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cvl_user` (`user_id`),
  ADD KEY `fk_cvl_customer` (`customer_id`);

--
-- Indexes for table `day_book`
--
ALTER TABLE `day_book`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_db_branch_date` (`branch_id`,`book_date`);

--
-- Indexes for table `device_integrity_log`
--
ALTER TABLE `device_integrity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dil_user` (`user_id`);

--
-- Indexes for table `disbursement_mode_master`
--
ALTER TABLE `disbursement_mode_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `employee_master`
--
ALTER TABLE `employee_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_em_manager` (`reporting_to`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gl_account_master`
--
ALTER TABLE `gl_account_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `gold_packet`
--
ALTER TABLE `gold_packet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packet_code` (`packet_code`),
  ADD KEY `fk_gp_item` (`jewellery_item_id`),
  ADD KEY `fk_gp_vault` (`vault_id`);

--
-- Indexes for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gral_rate` (`gold_rate_id`),
  ADD KEY `fk_gral_actor` (`actioned_by`);

--
-- Indexes for table `gold_rate_master`
--
ALTER TABLE `gold_rate_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grm_proposer` (`proposed_by`),
  ADD KEY `fk_grm_approver` (`approved_by`);

--
-- Indexes for table `gold_release_log`
--
ALTER TABLE `gold_release_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grl_loan` (`loan_id`),
  ADD KEY `fk_grl_item` (`jewellery_item_id`),
  ADD KEY `fk_grl_actor` (`released_by`);

--
-- Indexes for table `interest_collection_log`
--
ALTER TABLE `interest_collection_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_icl_loan` (`loan_id`),
  ADD KEY `fk_icl_actor` (`collected_by`);

--
-- Indexes for table `interest_receipt`
--
ALTER TABLE `interest_receipt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `fk_ir_collection` (`interest_collection_log_id`);

--
-- Indexes for table `interest_slab_master`
--
ALTER TABLE `interest_slab_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jewellery_category_master`
--
ALTER TABLE `jewellery_category_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `jewellery_image`
--
ALTER TABLE `jewellery_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jim_item` (`jewellery_item_id`);

--
-- Indexes for table `jewellery_item`
--
ALTER TABLE `jewellery_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `fk_ji_customer` (`customer_id`),
  ADD KEY `fk_ji_category` (`category_id`),
  ADD KEY `fk_ji_rate` (`gold_rate_id`),
  ADD KEY `fk_ji_evaluator` (`evaluated_by`),
  ADD KEY `fk_ji_loan` (`loan_id`);

--
-- Indexes for table `kyc_aadhaar_verification`
--
ALTER TABLE `kyc_aadhaar_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kav_customer` (`customer_id`);

--
-- Indexes for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kax_customer` (`customer_id`);

--
-- Indexes for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kdm_customer` (`customer_id`),
  ADD KEY `fk_kdm_doctype` (`document_type_id`),
  ADD KEY `fk_kdm_verifier` (`verified_by`);

--
-- Indexes for table `kyc_document_type`
--
ALTER TABLE `kyc_document_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `kyc_face_auth_log`
--
ALTER TABLE `kyc_face_auth_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kfa_customer` (`customer_id`);

--
-- Indexes for table `kyc_pan_verification`
--
ALTER TABLE `kyc_pan_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kpv_customer` (`customer_id`);

--
-- Indexes for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lalm_role` (`role_id`);

--
-- Indexes for table `loan_approval_log`
--
ALTER TABLE `loan_approval_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lal_loan` (`loan_id`),
  ADD KEY `fk_lal_actor` (`actioned_by`);

--
-- Indexes for table `loan_approval_workflow`
--
ALTER TABLE `loan_approval_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_law_loan` (`loan_id`);

--
-- Indexes for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lcl_loan` (`loan_id`),
  ADD KEY `fk_lcl_actor` (`calculated_by`);

--
-- Indexes for table `loan_closure`
--
ALTER TABLE `loan_closure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lc_loan` (`loan_id`),
  ADD KEY `fk_lc_actor` (`closed_by`);

--
-- Indexes for table `loan_closure_charge`
--
ALTER TABLE `loan_closure_charge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lcc_closure` (`loan_closure_id`);

--
-- Indexes for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ld_loan` (`loan_id`),
  ADD KEY `fk_ld_mode` (`mode_id`),
  ADD KEY `fk_ld_actor` (`disbursed_by`);

--
-- Indexes for table `loan_master`
--
ALTER TABLE `loan_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_account_number` (`loan_account_number`),
  ADD KEY `idx_loan_status` (`status`),
  ADD KEY `fk_lm_customer` (`customer_id`),
  ADD KEY `fk_lm_branch` (`branch_id`),
  ADD KEY `fk_lm_product` (`loan_product_id`),
  ADD KEY `fk_lm_creator` (`created_by`);

--
-- Indexes for table `loan_part_payment_log`
--
ALTER TABLE `loan_part_payment_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lppl_loan` (`loan_id`),
  ADD KEY `fk_lppl_actor` (`collected_by`);

--
-- Indexes for table `loan_product_master`
--
ALTER TABLE `loan_product_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `loan_reload_log`
--
ALTER TABLE `loan_reload_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lrel_loan` (`loan_id`),
  ADD KEY `fk_lrel_actor` (`processed_by`);

--
-- Indexes for table `loan_renewal_log`
--
ALTER TABLE `loan_renewal_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lrl_loan` (`loan_id`),
  ADD KEY `fk_lrl_actor` (`processed_by`);

--
-- Indexes for table `loan_scheme_master`
--
ALTER TABLE `loan_scheme_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_branch_product` (`branch_id`,`loan_product_id`),
  ADD KEY `fk_lsm_product` (`loan_product_id`);

--
-- Indexes for table `loan_topup_log`
--
ALTER TABLE `loan_topup_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ltl_loan` (`loan_id`),
  ADD KEY `fk_ltl_approver` (`approved_by`);

--
-- Indexes for table `location_log`
--
ALTER TABLE `location_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ll_user` (`user_id`);

--
-- Indexes for table `menu_master`
--
ALTER TABLE `menu_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_mm_parent` (`parent_id`),
  ADD KEY `fk_mm_role` (`role_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nl_customer` (`customer_id`),
  ADD KEY `fk_nl_template` (`template_id`);

--
-- Indexes for table `notification_template`
--
ALTER TABLE `notification_template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `packet_tracking_log`
--
ALTER TABLE `packet_tracking_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ptrl_packet` (`gold_packet_id`),
  ADD KEY `fk_ptrl_actor` (`logged_by`);

--
-- Indexes for table `packet_transfer_log`
--
ALTER TABLE `packet_transfer_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ptl_packet` (`gold_packet_id`),
  ADD KEY `fk_ptl_from` (`from_vault_id`),
  ADD KEY `fk_ptl_to` (`to_vault_id`),
  ADD KEY `fk_ptl_actor` (`transferred_by`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_code_unique` (`code`);

--
-- Indexes for table `permission_master`
--
ALTER TABLE `permission_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `print_template_master`
--
ALTER TABLE `print_template_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_code_unique` (`code`);

--
-- Indexes for table `role_master`
--
ALTER TABLE `role_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `role_permission_map`
--
ALTER TABLE `role_permission_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_rpm_permission` (`permission_id`);

--
-- Indexes for table `security_audit_log`
--
ALTER TABLE `security_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sal_user` (`user_id`);

--
-- Indexes for table `sync_conflict_log`
--
ALTER TABLE `sync_conflict_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scl_queue` (`sync_queue_id`);

--
-- Indexes for table `sync_queue`
--
ALTER TABLE `sync_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sq_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_biometric_ref`
--
ALTER TABLE `user_biometric_ref`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ubr_user` (`user_id`);

--
-- Indexes for table `user_device_binding`
--
ALTER TABLE `user_device_binding`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_device` (`user_id`,`device_id`);

--
-- Indexes for table `user_master`
--
ALTER TABLE `user_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_role` (`role_id`),
  ADD KEY `fk_user_branch` (`branch_id`);

--
-- Indexes for table `user_otp_log`
--
ALTER TABLE `user_otp_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mobile_purpose` (`mobile`,`purpose`),
  ADD KEY `fk_uol_user` (`user_id`);

--
-- Indexes for table `user_role_map`
--
ALTER TABLE `user_role_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_role` (`user_id`,`role_id`),
  ADD KEY `fk_urm_role` (`role_id`);

--
-- Indexes for table `user_session_log`
--
ALTER TABLE `user_session_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usl_user` (`user_id`);

--
-- Indexes for table `vault_master`
--
ALTER TABLE `vault_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vm_branch` (`branch_id`);

--
-- Indexes for table `voucher_detail`
--
ALTER TABLE `voucher_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vd_voucher` (`voucher_id`),
  ADD KEY `fk_vd_account` (`gl_account_id`);

--
-- Indexes for table `voucher_master`
--
ALTER TABLE `voucher_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_number` (`voucher_number`),
  ADD KEY `fk_vm2_branch` (`branch_id`),
  ADD KEY `fk_vm2_creator` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_config_master`
--
ALTER TABLE `app_config_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `auction_bid`
--
ALTER TABLE `auction_bid`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_bidder`
--
ALTER TABLE `auction_bidder`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_notice_log`
--
ALTER TABLE `auction_notice_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_schedule`
--
ALTER TABLE `auction_schedule`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_winner`
--
ALTER TABLE `auction_winner`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_master`
--
ALTER TABLE `branch_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cash_book`
--
ALTER TABLE `cash_book`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charge_master`
--
ALTER TABLE `charge_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_biometric_ref`
--
ALTER TABLE `customer_biometric_ref`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_duplicate_log`
--
ALTER TABLE `customer_duplicate_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_family_member`
--
ALTER TABLE `customer_family_member`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_ledger`
--
ALTER TABLE `customer_ledger`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_master`
--
ALTER TABLE `customer_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_nominee`
--
ALTER TABLE `customer_nominee`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_visit_log`
--
ALTER TABLE `customer_visit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `day_book`
--
ALTER TABLE `day_book`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_integrity_log`
--
ALTER TABLE `device_integrity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disbursement_mode_master`
--
ALTER TABLE `disbursement_mode_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employee_master`
--
ALTER TABLE `employee_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gl_account_master`
--
ALTER TABLE `gl_account_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gold_packet`
--
ALTER TABLE `gold_packet`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_rate_master`
--
ALTER TABLE `gold_rate_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gold_release_log`
--
ALTER TABLE `gold_release_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interest_collection_log`
--
ALTER TABLE `interest_collection_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interest_receipt`
--
ALTER TABLE `interest_receipt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interest_slab_master`
--
ALTER TABLE `interest_slab_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jewellery_category_master`
--
ALTER TABLE `jewellery_category_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jewellery_image`
--
ALTER TABLE `jewellery_image`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jewellery_item`
--
ALTER TABLE `jewellery_item`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_verification`
--
ALTER TABLE `kyc_aadhaar_verification`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_document_type`
--
ALTER TABLE `kyc_document_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kyc_face_auth_log`
--
ALTER TABLE `kyc_face_auth_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_pan_verification`
--
ALTER TABLE `kyc_pan_verification`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_approval_log`
--
ALTER TABLE `loan_approval_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_approval_workflow`
--
ALTER TABLE `loan_approval_workflow`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_closure`
--
ALTER TABLE `loan_closure`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_closure_charge`
--
ALTER TABLE `loan_closure_charge`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_master`
--
ALTER TABLE `loan_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_part_payment_log`
--
ALTER TABLE `loan_part_payment_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_product_master`
--
ALTER TABLE `loan_product_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_reload_log`
--
ALTER TABLE `loan_reload_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_renewal_log`
--
ALTER TABLE `loan_renewal_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_scheme_master`
--
ALTER TABLE `loan_scheme_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `loan_topup_log`
--
ALTER TABLE `loan_topup_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location_log`
--
ALTER TABLE `location_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_master`
--
ALTER TABLE `menu_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification_log`
--
ALTER TABLE `notification_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_template`
--
ALTER TABLE `notification_template`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `packet_tracking_log`
--
ALTER TABLE `packet_tracking_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packet_transfer_log`
--
ALTER TABLE `packet_transfer_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_master`
--
ALTER TABLE `permission_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_template_master`
--
ALTER TABLE `print_template_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_master`
--
ALTER TABLE `role_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `role_permission`
--
ALTER TABLE `role_permission`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permission_map`
--
ALTER TABLE `role_permission_map`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `security_audit_log`
--
ALTER TABLE `security_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_conflict_log`
--
ALTER TABLE `sync_conflict_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_queue`
--
ALTER TABLE `sync_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_biometric_ref`
--
ALTER TABLE `user_biometric_ref`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_device_binding`
--
ALTER TABLE `user_device_binding`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_otp_log`
--
ALTER TABLE `user_otp_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_role_map`
--
ALTER TABLE `user_role_map`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_session_log`
--
ALTER TABLE `user_session_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vault_master`
--
ALTER TABLE `vault_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `voucher_detail`
--
ALTER TABLE `voucher_detail`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_master`
--
ALTER TABLE `voucher_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auction_bid`
--
ALTER TABLE `auction_bid`
  ADD CONSTRAINT `fk_abid_bidder` FOREIGN KEY (`bidder_id`) REFERENCES `auction_bidder` (`id`),
  ADD CONSTRAINT `fk_abid_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packet` (`id`),
  ADD CONSTRAINT `fk_abid_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedule` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_bidder`
--
ALTER TABLE `auction_bidder`
  ADD CONSTRAINT `fk_ab_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedule` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_notice_log`
--
ALTER TABLE `auction_notice_log`
  ADD CONSTRAINT `fk_anl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`),
  ADD CONSTRAINT `fk_anl_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedule` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_schedule`
--
ALTER TABLE `auction_schedule`
  ADD CONSTRAINT `fk_as_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_as_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  ADD CONSTRAINT `fk_ast_actor` FOREIGN KEY (`settled_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ast_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`),
  ADD CONSTRAINT `fk_ast_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packet` (`id`);

--
-- Constraints for table `auction_winner`
--
ALTER TABLE `auction_winner`
  ADD CONSTRAINT `fk_aw_bidder` FOREIGN KEY (`bidder_id`) REFERENCES `auction_bidder` (`id`),
  ADD CONSTRAINT `fk_aw_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packet` (`id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_al_actor` FOREIGN KEY (`actor_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  ADD CONSTRAINT `fk_brl_actor` FOREIGN KEY (`reconciled_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_brl_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`);

--
-- Constraints for table `cash_book`
--
ALTER TABLE `cash_book`
  ADD CONSTRAINT `fk_cb_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`);

--
-- Constraints for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `fk_caddr_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_biometric_ref`
--
ALTER TABLE `customer_biometric_ref`
  ADD CONSTRAINT `fk_cbr_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_duplicate_log`
--
ALTER TABLE `customer_duplicate_log`
  ADD CONSTRAINT `fk_cdl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cdl_matched` FOREIGN KEY (`matched_customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cdl_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `customer_family_member`
--
ALTER TABLE `customer_family_member`
  ADD CONSTRAINT `fk_cfm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_ledger`
--
ALTER TABLE `customer_ledger`
  ADD CONSTRAINT `fk_cl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_cl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`);

--
-- Constraints for table `customer_master`
--
ALTER TABLE `customer_master`
  ADD CONSTRAINT `fk_cust_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_cust_registered_by` FOREIGN KEY (`registered_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  ADD CONSTRAINT `fk_cml_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_cml_merged` FOREIGN KEY (`merged_customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_cml_primary` FOREIGN KEY (`primary_customer_id`) REFERENCES `customer_master` (`id`);

--
-- Constraints for table `customer_nominee`
--
ALTER TABLE `customer_nominee`
  ADD CONSTRAINT `fk_cnom_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_visit_log`
--
ALTER TABLE `customer_visit_log`
  ADD CONSTRAINT `fk_cvl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_cvl_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `day_book`
--
ALTER TABLE `day_book`
  ADD CONSTRAINT `fk_db_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`);

--
-- Constraints for table `device_integrity_log`
--
ALTER TABLE `device_integrity_log`
  ADD CONSTRAINT `fk_dil_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `employee_master`
--
ALTER TABLE `employee_master`
  ADD CONSTRAINT `fk_em_manager` FOREIGN KEY (`reporting_to`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_em_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gold_packet`
--
ALTER TABLE `gold_packet`
  ADD CONSTRAINT `fk_gp_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_item` (`id`),
  ADD CONSTRAINT `fk_gp_vault` FOREIGN KEY (`vault_id`) REFERENCES `vault_master` (`id`);

--
-- Constraints for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  ADD CONSTRAINT `fk_gral_actor` FOREIGN KEY (`actioned_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_gral_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rate_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gold_rate_master`
--
ALTER TABLE `gold_rate_master`
  ADD CONSTRAINT `fk_grm_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_grm_proposer` FOREIGN KEY (`proposed_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `gold_release_log`
--
ALTER TABLE `gold_release_log`
  ADD CONSTRAINT `fk_grl_actor` FOREIGN KEY (`released_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_grl_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_item` (`id`),
  ADD CONSTRAINT `fk_grl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interest_collection_log`
--
ALTER TABLE `interest_collection_log`
  ADD CONSTRAINT `fk_icl_actor` FOREIGN KEY (`collected_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_icl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interest_receipt`
--
ALTER TABLE `interest_receipt`
  ADD CONSTRAINT `fk_ir_collection` FOREIGN KEY (`interest_collection_log_id`) REFERENCES `interest_collection_log` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jewellery_image`
--
ALTER TABLE `jewellery_image`
  ADD CONSTRAINT `fk_jim_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_item` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jewellery_item`
--
ALTER TABLE `jewellery_item`
  ADD CONSTRAINT `fk_ji_category` FOREIGN KEY (`category_id`) REFERENCES `jewellery_category_master` (`id`),
  ADD CONSTRAINT `fk_ji_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_ji_evaluator` FOREIGN KEY (`evaluated_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ji_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`),
  ADD CONSTRAINT `fk_ji_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rate_master` (`id`);

--
-- Constraints for table `kyc_aadhaar_verification`
--
ALTER TABLE `kyc_aadhaar_verification`
  ADD CONSTRAINT `fk_kav_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  ADD CONSTRAINT `fk_kax_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  ADD CONSTRAINT `fk_kdm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kdm_doctype` FOREIGN KEY (`document_type_id`) REFERENCES `kyc_document_type` (`id`),
  ADD CONSTRAINT `fk_kdm_verifier` FOREIGN KEY (`verified_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `kyc_face_auth_log`
--
ALTER TABLE `kyc_face_auth_log`
  ADD CONSTRAINT `fk_kfa_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_pan_verification`
--
ALTER TABLE `kyc_pan_verification`
  ADD CONSTRAINT `fk_kpv_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  ADD CONSTRAINT `fk_lalm_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`);

--
-- Constraints for table `loan_approval_log`
--
ALTER TABLE `loan_approval_log`
  ADD CONSTRAINT `fk_lal_actor` FOREIGN KEY (`actioned_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lal_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_approval_workflow`
--
ALTER TABLE `loan_approval_workflow`
  ADD CONSTRAINT `fk_law_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  ADD CONSTRAINT `fk_lcl_actor` FOREIGN KEY (`calculated_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lcl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_closure`
--
ALTER TABLE `loan_closure`
  ADD CONSTRAINT `fk_lc_actor` FOREIGN KEY (`closed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lc_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_closure_charge`
--
ALTER TABLE `loan_closure_charge`
  ADD CONSTRAINT `fk_lcc_closure` FOREIGN KEY (`loan_closure_id`) REFERENCES `loan_closure` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  ADD CONSTRAINT `fk_ld_actor` FOREIGN KEY (`disbursed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ld_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ld_mode` FOREIGN KEY (`mode_id`) REFERENCES `disbursement_mode_master` (`id`);

--
-- Constraints for table `loan_master`
--
ALTER TABLE `loan_master`
  ADD CONSTRAINT `fk_lm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_lm_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_lm_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_product_master` (`id`);

--
-- Constraints for table `loan_part_payment_log`
--
ALTER TABLE `loan_part_payment_log`
  ADD CONSTRAINT `fk_lppl_actor` FOREIGN KEY (`collected_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lppl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_reload_log`
--
ALTER TABLE `loan_reload_log`
  ADD CONSTRAINT `fk_lrel_actor` FOREIGN KEY (`processed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lrel_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_renewal_log`
--
ALTER TABLE `loan_renewal_log`
  ADD CONSTRAINT `fk_lrl_actor` FOREIGN KEY (`processed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lrl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_scheme_master`
--
ALTER TABLE `loan_scheme_master`
  ADD CONSTRAINT `fk_lsm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_lsm_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_product_master` (`id`);

--
-- Constraints for table `loan_topup_log`
--
ALTER TABLE `loan_topup_log`
  ADD CONSTRAINT `fk_ltl_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ltl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loan_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `location_log`
--
ALTER TABLE `location_log`
  ADD CONSTRAINT `fk_ll_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `menu_master`
--
ALTER TABLE `menu_master`
  ADD CONSTRAINT `fk_mm_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu_master` (`id`),
  ADD CONSTRAINT `fk_mm_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`);

--
-- Constraints for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD CONSTRAINT `fk_nl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer_master` (`id`),
  ADD CONSTRAINT `fk_nl_template` FOREIGN KEY (`template_id`) REFERENCES `notification_template` (`id`);

--
-- Constraints for table `packet_tracking_log`
--
ALTER TABLE `packet_tracking_log`
  ADD CONSTRAINT `fk_ptrl_actor` FOREIGN KEY (`logged_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ptrl_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packet` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packet_transfer_log`
--
ALTER TABLE `packet_transfer_log`
  ADD CONSTRAINT `fk_ptl_actor` FOREIGN KEY (`transferred_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ptl_from` FOREIGN KEY (`from_vault_id`) REFERENCES `vault_master` (`id`),
  ADD CONSTRAINT `fk_ptl_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packet` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptl_to` FOREIGN KEY (`to_vault_id`) REFERENCES `vault_master` (`id`);

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permission_map`
--
ALTER TABLE `role_permission_map`
  ADD CONSTRAINT `fk_rpm_permission` FOREIGN KEY (`permission_id`) REFERENCES `permission_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpm_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_audit_log`
--
ALTER TABLE `security_audit_log`
  ADD CONSTRAINT `fk_sal_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `sync_conflict_log`
--
ALTER TABLE `sync_conflict_log`
  ADD CONSTRAINT `fk_scl_queue` FOREIGN KEY (`sync_queue_id`) REFERENCES `sync_queue` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sync_queue`
--
ALTER TABLE `sync_queue`
  ADD CONSTRAINT `fk_sq_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `user_biometric_ref`
--
ALTER TABLE `user_biometric_ref`
  ADD CONSTRAINT `fk_ubr_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_device_binding`
--
ALTER TABLE `user_device_binding`
  ADD CONSTRAINT `fk_udb_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_master`
--
ALTER TABLE `user_master`
  ADD CONSTRAINT `fk_user_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`);

--
-- Constraints for table `user_otp_log`
--
ALTER TABLE `user_otp_log`
  ADD CONSTRAINT `fk_uol_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_role_map`
--
ALTER TABLE `user_role_map`
  ADD CONSTRAINT `fk_urm_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_urm_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_session_log`
--
ALTER TABLE `user_session_log`
  ADD CONSTRAINT `fk_usl_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vault_master`
--
ALTER TABLE `vault_master`
  ADD CONSTRAINT `fk_vm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`);

--
-- Constraints for table `voucher_detail`
--
ALTER TABLE `voucher_detail`
  ADD CONSTRAINT `fk_vd_account` FOREIGN KEY (`gl_account_id`) REFERENCES `gl_account_master` (`id`),
  ADD CONSTRAINT `fk_vd_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `voucher_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voucher_master`
--
ALTER TABLE `voucher_master`
  ADD CONSTRAINT `fk_vm2_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`id`),
  ADD CONSTRAINT `fk_vm2_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
