-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 15, 2026 at 09:06 AM
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
-- Table structure for table `auction_bidders`
--

CREATE TABLE `auction_bidders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `id_proof_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auction_bidders`
--

INSERT INTO `auction_bidders` (`id`, `auction_schedule_id`, `name`, `mobile`, `id_proof_number`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bidder Name', '9000000000', NULL, '2026-08-08 14:58:44', '2026-08-08 14:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `auction_bids`
--

CREATE TABLE `auction_bids` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `bidder_id` bigint(20) UNSIGNED NOT NULL,
  `bid_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auction_bids`
--

INSERT INTO `auction_bids` (`id`, `auction_schedule_id`, `gold_packet_id`, `bidder_id`, `bid_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 20000.00, '2026-08-08 15:05:36', '2026-08-08 15:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `auction_notice_logs`
--

CREATE TABLE `auction_notice_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `auction_schedule_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `channel` varchar(20) NOT NULL,
  `sent_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auction_notice_logs`
--

INSERT INTO `auction_notice_logs` (`id`, `auction_schedule_id`, `loan_id`, `channel`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'SMS', '2026-08-08 14:57:16', '2026-08-08 14:57:16', '2026-08-08 14:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `auction_schedules`
--

CREATE TABLE `auction_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `auction_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'SCHEDULED',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auction_schedules`
--

INSERT INTO `auction_schedules` (`id`, `branch_id`, `auction_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-01', 'NOTICE_SENT', 3, '2026-08-08 14:56:09', '2026-08-08 14:57:16'),
(2, 1, '2026-09-01', 'SCHEDULED', 3, '2026-08-08 15:07:43', '2026-08-08 15:07:43');

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
-- Table structure for table `auction_winners`
--

CREATE TABLE `auction_winners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `bidder_id` bigint(20) UNSIGNED NOT NULL,
  `winning_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auction_winners`
--

INSERT INTO `auction_winners` (`id`, `gold_packet_id`, `bidder_id`, `winning_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 20000.00, '2026-08-08 15:06:28', '2026-08-08 15:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `before_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_value`)),
  `after_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_value`)),
  `actor_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `entity_type`, `entity_id`, `action`, `before_value`, `after_value`, `actor_id`, `created_at`, `updated_at`) VALUES
(1, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 10:42:46', '2026-08-08 10:42:46'),
(2, 'User', 1, 'OTP_LOGIN', NULL, '{\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 10:56:50', '2026-08-08 10:56:50'),
(3, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 11:48:32', '2026-08-08 11:48:32'),
(4, 'User', 3, 'TOKEN_REFRESH', NULL, NULL, 3, '2026-08-08 11:49:12', '2026-08-08 11:49:12'),
(5, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 11:49:41', '2026-08-08 11:49:41'),
(6, 'User', 3, 'TOKEN_REFRESH', NULL, NULL, 3, '2026-08-08 11:49:45', '2026-08-08 11:49:45'),
(7, 'User', 3, 'DEVICE_BIND', NULL, '{\"device_id\":\"test-device-001\"}', 3, '2026-08-08 11:50:12', '2026-08-08 11:50:12'),
(8, 'User', 3, 'MPIN_SET', NULL, '{\"mpin_set_at\":\"2026-08-08 11:50:26\"}', 3, '2026-08-08 11:50:26', '2026-08-08 11:50:26'),
(9, 'Customer', 3, 'CREATE', NULL, '{\"id\":\"3\",\"customer_code\":\"CUST00000001\",\"name\":\"Test Customer\",\"mobile\":\"9876543210\",\"email\":null,\"dob\":\"1990-01-01\",\"gender\":\"MALE\",\"aadhaar_last4\":null,\"aadhaar_hash\":null,\"pan_number\":null,\"branch_id\":\"1\",\"registered_by\":\"3\",\"kyc_status\":\"PENDING\",\"is_blacklisted\":\"0\",\"created_at\":\"2026-08-08 12:05:27\",\"updated_at\":\"2026-08-08 12:05:27\",\"deleted_at\":null,\"addresses\":[{\"id\":\"1\",\"customer_id\":\"3\",\"type\":\"CURRENT\",\"line1\":\"123 Main St\",\"line2\":null,\"city\":\"Chennai\",\"state\":\"Tamil Nadu\",\"pincode\":\"600001\",\"created_at\":\"2026-08-08 12:05:27\",\"updated_at\":\"2026-08-08 12:05:27\"}]}', 3, '2026-08-08 12:05:27', '2026-08-08 12:05:27'),
(10, 'Customer', 3, 'NOMINEE_ADD', NULL, '{\"id\":\"1\",\"customer_id\":\"3\",\"name\":\"Nominee Name\",\"relation\":\"Spouse\",\"mobile\":null,\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-08-08 12:16:08\",\"updated_at\":\"2026-08-08 12:16:08\"}', 3, '2026-08-08 12:16:08', '2026-08-08 12:16:08'),
(11, 'Customer', 3, 'FAMILY_MEMBER_ADD', NULL, '{\"id\":\"1\",\"customer_id\":\"3\",\"name\":\"Family Member\",\"relation\":\"Sibling\",\"mobile\":null,\"created_at\":\"2026-08-08 12:16:48\",\"updated_at\":\"2026-08-08 12:16:48\"}', 3, '2026-08-08 12:16:48', '2026-08-08 12:16:48'),
(12, 'Customer', 3, 'KYC_AADHAAR_QR_SCAN', '{\"aadhaar_last4\":\"7405\",\"aadhaar_hash\":\"87a65d54d4b55d6db192574869f89b144222c498baecd61915c6412f0dca378a\"}', '{\"aadhaar_last4\":\"7405\",\"aadhaar_hash\":\"87a65d54d4b55d6db192574869f89b144222c498baecd61915c6412f0dca378a\",\"verification\":{\"id\":\"1\",\"customer_id\":\"3\",\"method\":\"QR\",\"uidai_reference_id\":\"\",\"is_verified\":\"1\",\"verified_at\":\"2026-08-08 12:43:33\",\"created_at\":\"2026-08-08 12:43:33\",\"updated_at\":\"2026-08-08 12:43:33\"}}', 3, '2026-08-08 12:43:33', '2026-08-08 12:43:33'),
(13, 'Customer', 3, 'KYC_AADHAAR_OFFLINE_XML', NULL, '{\"id\":\"2\",\"customer_id\":\"3\",\"method\":\"OFFLINE_XML\",\"uidai_reference_id\":null,\"is_verified\":\"1\",\"verified_at\":\"2026-08-08 12:47:11\",\"created_at\":\"2026-08-08 12:47:11\",\"updated_at\":\"2026-08-08 12:47:11\"}', 3, '2026-08-08 12:47:11', '2026-08-08 12:47:11'),
(14, 'Customer', 3, 'KYC_AADHAAR_FACE_AUTH', NULL, '{\"is_matched\":true,\"confidence_score\":96.5}', 3, '2026-08-08 12:48:09', '2026-08-08 12:48:09'),
(15, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 12:50:35', '2026-08-08 12:50:35'),
(16, 'Customer', 3, 'KYC_PAN_VALIDATE', NULL, '{\"id\":\"1\",\"customer_id\":\"3\",\"is_verified\":\"1\",\"name_match\":\"1\",\"created_at\":\"2026-08-08 12:52:28\",\"updated_at\":\"2026-08-08 12:52:28\"}', 3, '2026-08-08 12:52:30', '2026-08-08 12:52:30'),
(17, 'KycDocument', 1, 'KYC_DOCUMENT_UPLOAD', NULL, '{\"id\":\"1\",\"customer_id\":\"3\",\"document_type_id\":\"1\",\"file_ref\":\"kyc-documents\\/2f715fc4f15e2651351b969c79078004.png\",\"status\":\"PENDING\",\"verified_by\":null,\"created_at\":\"2026-08-08 12:56:01\",\"updated_at\":\"2026-08-08 12:56:01\"}', 3, '2026-08-08 12:56:01', '2026-08-08 12:56:01'),
(18, 'KycDocument', 1, 'KYC_DOCUMENT_VERIFY', '{\"id\":\"1\",\"customer_id\":\"3\",\"document_type_id\":\"1\",\"file_ref\":\"kyc-documents\\/2f715fc4f15e2651351b969c79078004.png\",\"status\":\"PENDING\",\"verified_by\":null,\"created_at\":\"2026-08-08 12:56:01\",\"updated_at\":\"2026-08-08 12:56:01\"}', '{\"id\":\"1\",\"customer_id\":\"3\",\"document_type_id\":\"1\",\"file_ref\":\"kyc-documents\\/2f715fc4f15e2651351b969c79078004.png\",\"status\":\"VERIFIED\",\"verified_by\":\"3\",\"created_at\":\"2026-08-08 12:56:01\",\"updated_at\":\"2026-08-08 13:11:17\"}', 3, '2026-08-08 13:11:17', '2026-08-08 13:11:17'),
(19, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 13:15:22', '2026-08-08 13:15:22'),
(20, 'JewelleryItem', 1, 'CREATE', NULL, '{\"customer_id\":3,\"category_id\":1,\"net_weight\":24.3,\"eligible_amount\":113906.25,\"status\":\"EVALUATED\"}', 3, '2026-08-08 13:16:28', '2026-08-08 13:16:28'),
(21, 'GoldRate', 1, 'RATE_APPROVE', '{\"status\":\"APPROVED\"}', '{\"status\":\"APPROVED\",\"approved_by\":\"3\",\"approved_at\":\"2026-08-08 13:23:05\"}', 3, '2026-08-08 13:23:05', '2026-08-08 13:23:05'),
(22, 'JewelleryItem', 1, 'IMAGE_UPLOAD', NULL, '{\"jewellery_image_id\":1,\"file_ref\":\"jewellery-images\\/94e428d8951315ca75c9217f4feb7c87.png\"}', 3, '2026-08-08 13:25:25', '2026-08-08 13:25:25'),
(23, 'Loan', 2, 'REJECT', '{\"status\":\"REJECTED\"}', '{\"status\":\"REJECTED\",\"remarks\":\"Insufficient documentation\"}', 3, '2026-08-08 13:39:53', '2026-08-08 13:39:53'),
(24, 'Loan', 2, 'OVERRIDE', '{\"status\":\"REJECTED\"}', '{\"status\":\"APPROVED\",\"remarks\":\"Regional manager override\"}', 3, '2026-08-08 13:40:18', '2026-08-08 13:40:18'),
(25, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 14:18:18', '2026-08-08 14:18:18'),
(26, 'Loan', 2, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":6,\"mode\":\"3\",\"amount\":\"112277.39\"}', 3, '2026-08-08 14:25:10', '2026-08-08 14:25:10'),
(27, 'Loan', 2, 'RENEW', '{\"status\":\"ACTIVE\",\"due_date\":\"2027-08-08\"}', '{\"status\":\"RENEWED\",\"due_date\":\"2027-08-08\",\"renewal_id\":1}', 3, '2026-08-08 14:28:10', '2026-08-08 14:28:10'),
(28, 'Loan', 2, 'TOPUP_APPROVE', '{\"status\":\"RENEWED\",\"sanctioned_amount\":\"113906.25\"}', '{\"topup_id\":1,\"approved_amount\":5000}', 3, '2026-08-08 14:29:54', '2026-08-08 14:29:54'),
(29, 'Loan', 2, 'TOPUP_DISBURSE', '{\"status\":\"RENEWED\",\"sanctioned_amount\":\"113906.25\"}', '{\"sanctioned_amount\":118906.25,\"topup_id\":\"1\",\"approved_amount\":\"5000.00\"}', 3, '2026-08-08 14:30:18', '2026-08-08 14:30:18'),
(30, 'Loan', 2, 'PART_PAYMENT', '{\"status\":\"RENEWED\",\"sanctioned_amount\":\"118906.25\"}', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":116906.25,\"payment_id\":1,\"principal_amount\":2000,\"interest_amount\":200}', 3, '2026-08-08 14:34:08', '2026-08-08 14:34:08'),
(31, 'Loan', 2, 'SETTLE', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"116906.25\"}', '{\"status\":\"SETTLED\",\"closure_id\":1,\"total_amount_collected\":\"100\"}', 3, '2026-08-08 14:44:38', '2026-08-08 14:44:38'),
(32, 'AuctionSchedule', 1, 'AUCTION_SCHEDULE', NULL, '{\"branch_id\":1,\"auction_date\":\"2026-09-01\",\"status\":\"SCHEDULED\"}', 3, '2026-08-08 14:56:09', '2026-08-08 14:56:09'),
(33, 'GoldPacket', 1, 'CREATE', NULL, '{\"jewellery_item_id\":1,\"vault_id\":1,\"status\":\"IN_VAULT\"}', 3, '2026-08-08 15:04:21', '2026-08-08 15:04:21'),
(34, 'GoldPacket', 1, 'AUCTION_DECLARE_WINNER', '{\"status\":\"IN_VAULT\"}', '{\"status\":\"AUCTIONED\",\"winner_id\":1,\"bidder_id\":\"1\",\"winning_amount\":\"20000.00\"}', 3, '2026-08-08 15:06:28', '2026-08-08 15:06:28'),
(35, 'AuctionSchedule', 2, 'AUCTION_SCHEDULE', NULL, '{\"branch_id\":1,\"auction_date\":\"2026-09-01\",\"status\":\"SCHEDULED\"}', 3, '2026-08-08 15:07:43', '2026-08-08 15:07:43'),
(36, 'InventoryTransfer', 1, 'TRANSFER', '{\"vault_id\":\"1\"}', '{\"vault_id\":2,\"gold_packet_id\":\"1\"}', 3, '2026-08-08 15:10:00', '2026-08-08 15:10:00'),
(37, 'Voucher', 2, 'VOUCHER_CREATE', NULL, '{\"branch_id\":1,\"type\":\"RECEIPT\",\"voucher_date\":\"2026-08-02\",\"total_debit\":1000,\"total_credit\":1000}', 3, '2026-08-08 15:17:11', '2026-08-08 15:17:11'),
(38, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 15:21:47', '2026-08-08 15:21:47'),
(39, 'Branch', 3, 'CREATE', NULL, '{\"id\":\"3\",\"branch_code\":\"H001-BR003\",\"company_code\":\"H001\",\"name\":\"Second Branch\",\"city\":null,\"state\":null,\"latitude\":null,\"longitude\":null,\"gst_number\":null,\"is_active\":\"1\",\"created_at\":\"2026-08-08 15:28:18\",\"updated_at\":\"2026-08-08 15:28:18\"}', 3, '2026-08-08 15:28:18', '2026-08-08 15:28:18'),
(40, 'LoanProduct', 4, 'CREATE', NULL, '{\"id\":\"4\",\"code\":\"PROD002\",\"name\":\"Express Gold Loan\",\"interest_rate_pct\":\"1.20\",\"interest_type\":\"FLAT\",\"tenure_months\":\"6\",\"processing_fee_pct\":\"0.00\",\"gst_pct\":\"18.00\",\"insurance_pct\":\"0.00\",\"is_active\":\"1\",\"created_at\":\"2026-08-08 15:30:33\",\"updated_at\":\"2026-08-08 15:30:33\"}', 3, '2026-08-08 15:30:33', '2026-08-08 15:30:33'),
(41, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 15:04:16', '2026-08-08 15:04:16'),
(42, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-08 15:22:51', '2026-08-08 15:22:51'),
(43, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"31afae942e26162b\"}', NULL, '2026-08-09 15:31:03', '2026-08-09 15:31:03'),
(44, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-09 15:31:37', '2026-08-09 15:31:37'),
(45, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-10 11:51:23', '2026-08-10 11:51:23'),
(46, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-10 12:48:50', '2026-08-10 12:48:50'),
(47, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-12 16:17:40', '2026-08-12 16:17:40'),
(48, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-12 16:46:51', '2026-08-12 16:46:51'),
(49, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-12 16:48:58', '2026-08-12 16:48:58'),
(50, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"27cbd4eb-43d7-4d52-80f4-1546d41915b6\"}', NULL, '2026-08-12 16:49:16', '2026-08-12 16:49:16'),
(51, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"27cbd4eb-43d7-4d52-80f4-1546d41915b6\"}', NULL, '2026-08-12 16:52:03', '2026-08-12 16:52:03'),
(52, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"610c577b-74e9-4a85-b0ce-8bcccb0af203\"}', NULL, '2026-08-12 18:04:59', '2026-08-12 18:04:59'),
(53, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"610c577b-74e9-4a85-b0ce-8bcccb0af203\"}', NULL, '2026-08-12 18:07:21', '2026-08-12 18:07:21'),
(54, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-15 06:54:00', '2026-08-15 06:54:00'),
(55, 'User', 3, 'LOGIN', NULL, '{\"role\":\"ADMIN\",\"device_id\":\"test-device-001\"}', NULL, '2026-08-15 08:54:45', '2026-08-15 08:54:45');

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_code`, `company_code`, `name`, `city`, `state`, `latitude`, `longitude`, `gst_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'H001-BR001', 'H001', 'Swarna Gold Loan - Chennai Main', 'Chennai', 'Tamil Nadu', NULL, NULL, NULL, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(2, 'H001-BR002', 'H001', 'Swarna Gold Loan - Tambaram', 'Chennai', 'Tamil Nadu', NULL, NULL, NULL, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(3, 'H001-BR003', 'H001', 'Second Branch', NULL, NULL, NULL, NULL, NULL, 1, '2026-08-08 15:28:18', '2026-08-08 15:28:18');

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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
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

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `name`, `mobile`, `email`, `dob`, `gender`, `aadhaar_last4`, `aadhaar_hash`, `pan_number`, `branch_id`, `registered_by`, `kyc_status`, `is_blacklisted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'CUST00000001', 'Test Customer', '9876543210', NULL, '1990-01-01', 'MALE', '7405', '87a65d54d4b55d6db192574869f89b144222c498baecd61915c6412f0dca378a', NULL, 1, 3, 'PENDING', 0, '2026-08-08 12:05:27', '2026-08-08 12:43:33', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_address`
--

INSERT INTO `customer_address` (`id`, `customer_id`, `type`, `line1`, `line2`, `city`, `state`, `pincode`, `created_at`, `updated_at`) VALUES
(1, 3, 'CURRENT', '123 Main St', NULL, 'Chennai', 'Tamil Nadu', '600001', '2026-08-08 12:05:27', '2026-08-08 12:05:27');

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
-- Table structure for table `customer_family_members`
--

CREATE TABLE `customer_family_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `relation` varchar(50) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_family_members`
--

INSERT INTO `customer_family_members` (`id`, `customer_id`, `name`, `relation`, `mobile`, `created_at`, `updated_at`) VALUES
(1, 3, 'Family Member', 'Sibling', NULL, '2026-08-08 12:16:48', '2026-08-08 12:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `customer_ledgers`
--

CREATE TABLE `customer_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `particulars` varchar(255) NOT NULL,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
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
-- Table structure for table `customer_nominees`
--

CREATE TABLE `customer_nominees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `relation` varchar(50) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `id_proof_type` varchar(30) DEFAULT NULL,
  `id_proof_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_nominees`
--

INSERT INTO `customer_nominees` (`id`, `customer_id`, `name`, `relation`, `mobile`, `id_proof_type`, `id_proof_number`, `created_at`, `updated_at`) VALUES
(1, 3, 'Nominee Name', 'Spouse', NULL, NULL, NULL, '2026-08-08 12:16:08', '2026-08-08 12:16:08');

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
-- Table structure for table `gl_accounts`
--

CREATE TABLE `gl_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gl_accounts`
--

INSERT INTO `gl_accounts` (`id`, `code`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, '1000', 'Cash in Hand', 'ASSET', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(2, '1010', 'Bank Account - Current', 'ASSET', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(3, '1100', 'Loans Receivable - Gold Loan', 'ASSET', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(4, '1200', 'Gold Inventory (Pledged)', 'ASSET', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(5, '2000', 'Customer Deposits Payable', 'LIABILITY', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(6, '2100', 'GST Payable', 'LIABILITY', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(7, '2200', 'TDS Payable', 'LIABILITY', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(8, '4000', 'Interest Income', 'INCOME', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(9, '4100', 'Processing Fee Income', 'INCOME', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(10, '4200', 'Auction Surplus Income', 'INCOME', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(11, '5000', 'Staff Salary Expense', 'EXPENSE', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(12, '5100', 'Branch Rent Expense', 'EXPENSE', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(13, '5200', 'Bad Debt / NPA Write-off', 'EXPENSE', '2026-08-07 16:14:20', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `gold_packets`
--

CREATE TABLE `gold_packets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `packet_code` varchar(40) NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `vault_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'IN_VAULT',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gold_packets`
--

INSERT INTO `gold_packets` (`id`, `packet_code`, `jewellery_item_id`, `vault_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PKT00000001', 1, 2, 'AUCTIONED', '2026-08-08 15:04:21', '2026-08-08 15:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `gold_rates`
--

CREATE TABLE `gold_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rate_per_gram` decimal(10,2) NOT NULL,
  `karat` varchar(5) NOT NULL,
  `effective_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING_APPROVAL',
  `proposed_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gold_rates`
--

INSERT INTO `gold_rates` (`id`, `rate_per_gram`, `karat`, `effective_date`, `status`, `proposed_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 6250.00, '22K', '2026-08-07', 'APPROVED', 1, 3, '2026-08-08 13:23:05', '2026-08-07 16:14:20', '2026-08-08 13:23:05'),
(2, 5115.00, '18K', '2026-08-07', 'APPROVED', 1, 1, '2026-08-07 16:14:20', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(3, 6500.00, '24K', '2026-08-02', 'PENDING_APPROVAL', 3, NULL, NULL, '2026-08-08 13:22:50', '2026-08-08 13:22:50');

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
-- Table structure for table `gold_releases`
--

CREATE TABLE `gold_releases` (
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gold_releases`
--

INSERT INTO `gold_releases` (`id`, `loan_id`, `jewellery_item_id`, `id_proof_verified`, `signature_captured`, `photo_captured`, `released_by`, `released_to`, `status`, `released_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 0, 0, 3, 'manager', 'PENDING', NULL, '2026-08-08 14:48:14', '2026-08-08 14:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `interest_collections`
--

CREATE TABLE `interest_collections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_collections`
--

INSERT INTO `interest_collections` (`id`, `loan_id`, `amount`, `mode`, `receipt_number`, `collected_by`, `created_at`, `updated_at`) VALUES
(1, 2, 500.00, 'CASH', '0', 3, '2026-08-08 14:32:14', '2026-08-08 14:32:14');

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
-- Table structure for table `jewellery_images`
--

CREATE TABLE `jewellery_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `file_ref` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jewellery_images`
--

INSERT INTO `jewellery_images` (`id`, `jewellery_item_id`, `file_ref`, `created_at`, `updated_at`) VALUES
(1, 1, 'jewellery-images/94e428d8951315ca75c9217f4feb7c87.png', '2026-08-08 13:25:25', '2026-08-08 13:25:25');

-- --------------------------------------------------------

--
-- Table structure for table `jewellery_items`
--

CREATE TABLE `jewellery_items` (
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

--
-- Dumping data for table `jewellery_items`
--

INSERT INTO `jewellery_items` (`id`, `barcode`, `customer_id`, `category_id`, `hallmark_flag`, `gross_weight`, `stone_weight`, `purity_karat`, `gold_rate_id`, `applied_rate`, `eligible_percentage`, `eligible_amount`, `evaluated_by`, `status`, `loan_id`, `created_at`, `updated_at`) VALUES
(1, 'JWLUZQ5JLRTG2', 3, 1, 1, 25.500, 1.200, '22K', 1, 6250.00, 75.00, 113906.25, 3, 'RELEASED', 2, '2026-08-08 13:16:28', '2026-08-08 14:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_aadhaar_verifications`
--

CREATE TABLE `kyc_aadhaar_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(20) NOT NULL,
  `uidai_reference_id` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_aadhaar_verifications`
--

INSERT INTO `kyc_aadhaar_verifications` (`id`, `customer_id`, `method`, `uidai_reference_id`, `is_verified`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'QR', '', 1, '2026-08-08 12:43:33', '2026-08-08 12:43:33', '2026-08-08 12:43:33'),
(2, 3, 'OFFLINE_XML', NULL, 1, '2026-08-08 12:47:11', '2026-08-08 12:47:11', '2026-08-08 12:47:11');

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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_document_master`
--

INSERT INTO `kyc_document_master` (`id`, `customer_id`, `document_type_id`, `file_ref`, `status`, `verified_by`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'kyc-documents/2f715fc4f15e2651351b969c79078004.png', 'VERIFIED', 3, '2026-08-08 12:56:01', '2026-08-08 13:11:17');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_document_types`
--

CREATE TABLE `kyc_document_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_document_types`
--

INSERT INTO `kyc_document_types` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
(1, 'VOTER_ID', 'Voter ID', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(2, 'DRIVING_LICENSE', 'Driving License', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(3, 'PASSPORT', 'Passport', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(4, 'UTILITY_BILL', 'Utility Bill', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(5, 'BANK_PASSBOOK', 'Bank Passbook', '2026-08-07 16:14:20', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_face_auth_logs`
--

CREATE TABLE `kyc_face_auth_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `is_matched` tinyint(1) NOT NULL,
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_face_auth_logs`
--

INSERT INTO `kyc_face_auth_logs` (`id`, `customer_id`, `is_matched`, `confidence_score`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 96.50, '2026-08-08 12:48:09', '2026-08-08 12:48:09');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_pan_verifications`
--

CREATE TABLE `kyc_pan_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `pan_number` varchar(15) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `name_match` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_pan_verifications`
--

INSERT INTO `kyc_pan_verifications` (`id`, `customer_id`, `pan_number`, `is_verified`, `name_match`, `created_at`, `updated_at`) VALUES
(1, 3, 'ABCDE1234F', 1, 1, '2026-08-08 12:52:28', '2026-08-08 12:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
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

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `loan_account_number`, `customer_id`, `branch_id`, `loan_product_id`, `eligible_amount`, `sanctioned_amount`, `interest_rate_pct`, `processing_fee`, `gst_amount`, `insurance_amount`, `net_disbursed_amount`, `loan_date`, `due_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'LGH001000000001', 3, 1, 1, 113906.25, 116906.25, 12.00, 1139.06, 205.03, 284.77, 112277.39, '2026-08-08', '2027-08-08', 'SETTLED', 3, '2026-08-08 13:32:31', '2026-08-08 14:44:38');

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
-- Table structure for table `loan_approval_logs`
--

CREATE TABLE `loan_approval_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(30) NOT NULL,
  `action` varchar(20) NOT NULL,
  `actioned_by` bigint(20) UNSIGNED NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_approval_logs`
--

INSERT INTO `loan_approval_logs` (`id`, `loan_id`, `stage`, `action`, `actioned_by`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 2, 'APPRAISER', 'REJECT', 3, 'Insufficient documentation', '2026-08-08 13:39:53', '2026-08-08 13:39:53'),
(2, 2, 'OVERRIDE', 'OVERRIDE', 3, 'Regional manager override', '2026-08-08 13:40:18', '2026-08-08 13:40:18');

-- --------------------------------------------------------

--
-- Table structure for table `loan_approval_workflows`
--

CREATE TABLE `loan_approval_workflows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `current_stage` varchar(30) NOT NULL DEFAULT 'APPRAISER',
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_approval_workflows`
--

INSERT INTO `loan_approval_workflows` (`id`, `loan_id`, `current_stage`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'APPRAISER', 'APPROVED', '2026-08-08 13:35:42', '2026-08-08 13:40:18');

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
-- Table structure for table `loan_charges`
--

CREATE TABLE `loan_charges` (
  `id` bigint(20) NOT NULL,
  `loan_id` bigint(20) NOT NULL,
  `charge_type` enum('PROCESSING_FEE','GST','INSURANCE','LATE_FEE') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_charges`
--

INSERT INTO `loan_charges` (`id`, `loan_id`, `charge_type`, `amount`, `created_at`, `updated_at`) VALUES
(1, 2, 'PROCESSING_FEE', 1139.06, '2026-08-08 13:32:31', '2026-08-08 13:32:31'),
(2, 2, 'GST', 205.03, '2026-08-08 13:32:31', '2026-08-08 13:32:31'),
(3, 2, 'INSURANCE', 284.77, '2026-08-08 13:32:31', '2026-08-08 13:32:31');

-- --------------------------------------------------------

--
-- Table structure for table `loan_closures`
--

CREATE TABLE `loan_closures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount_collected` decimal(12,2) NOT NULL,
  `closure_date` date NOT NULL,
  `closed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_closures`
--

INSERT INTO `loan_closures` (`id`, `loan_id`, `total_amount_collected`, `closure_date`, `closed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 100.00, '2026-08-08', 3, '2026-08-08 14:44:38', '2026-08-08 14:44:38');

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
-- Table structure for table `loan_disbursements`
--

CREATE TABLE `loan_disbursements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `mode` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_number` varchar(60) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `disbursed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_disbursements`
--

INSERT INTO `loan_disbursements` (`id`, `loan_id`, `mode`, `amount`, `reference_number`, `status`, `disbursed_by`, `created_at`, `updated_at`) VALUES
(6, 2, 3, 112277.39, '1234567', 'COMPLETED', 3, '2026-08-08 14:25:10', '2026-08-08 14:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `loan_part_payments`
--

CREATE TABLE `loan_part_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `principal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `interest_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_part_payments`
--

INSERT INTO `loan_part_payments` (`id`, `loan_id`, `principal_amount`, `interest_amount`, `collected_by`, `created_at`, `updated_at`) VALUES
(1, 2, 2000.00, 200.00, 3, '2026-08-08 14:34:08', '2026-08-08 14:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `loan_products`
--

CREATE TABLE `loan_products` (
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_products`
--

INSERT INTO `loan_products` (`id`, `code`, `name`, `interest_rate_pct`, `interest_type`, `tenure_months`, `processing_fee_pct`, `gst_pct`, `insurance_pct`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'GL-STD-12', 'Standard Gold Loan - 12 Month', 12.00, 'FLAT', 12, 1.00, 18.00, 0.25, 1, '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(2, 'GL-STD-6', 'Standard Gold Loan - 6 Month', 13.50, 'FLAT', 6, 1.00, 18.00, 0.25, 1, '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(3, 'GL-PREMIUM-12', 'Premium Gold Loan - 12 Month', 10.50, 'REDUCING', 12, 0.50, 18.00, 0.25, 1, '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(4, 'PROD002', 'Express Gold Loan', 1.20, 'FLAT', 6, 0.00, 18.00, 0.00, 1, '2026-08-08 15:30:33', '2026-08-08 15:30:33');

-- --------------------------------------------------------

--
-- Table structure for table `loan_reloads`
--

CREATE TABLE `loan_reloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `excess_amount_eligible` decimal(12,2) NOT NULL,
  `reload_amount` decimal(12,2) NOT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_renewals`
--

CREATE TABLE `loan_renewals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `renewed_tenure_months` int(11) NOT NULL,
  `interest_paid` decimal(10,2) NOT NULL,
  `renewal_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `new_due_date` date NOT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_renewals`
--

INSERT INTO `loan_renewals` (`id`, `loan_id`, `renewed_tenure_months`, `interest_paid`, `renewal_charges`, `new_due_date`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 2, 12, 500.00, 0.00, '2027-08-08', 3, '2026-08-08 14:28:10', '2026-08-08 14:28:10');

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
-- Table structure for table `loan_topups`
--

CREATE TABLE `loan_topups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_topup_amount` decimal(12,2) NOT NULL,
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_topups`
--

INSERT INTO `loan_topups` (`id`, `loan_id`, `eligible_topup_amount`, `approved_amount`, `processing_fee`, `status`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 2, 5000.00, 5000.00, 0.00, 'DISBURSED', 3, '2026-08-08 14:29:54', '2026-08-08 14:30:18');

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
-- Table structure for table `packet_transfer_logs`
--

CREATE TABLE `packet_transfer_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gold_packet_id` bigint(20) UNSIGNED NOT NULL,
  `from_vault_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_vault_id` bigint(20) UNSIGNED NOT NULL,
  `transferred_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packet_transfer_logs`
--

INSERT INTO `packet_transfer_logs` (`id`, `gold_packet_id`, `from_vault_id`, `to_vault_id`, `transferred_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 3, '2026-08-08 15:10:00', '2026-08-08 15:10:00');

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
  `expires_at` datetime NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `expires_at`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'User', 3, 'mobile-app', '5721bd6df0d019e17562893abfd8aab38058d2ee2f37a0401156451067089484', NULL, '2026-08-08 11:40:58', NULL, '2026-08-08 10:40:58', '2026-08-08 10:40:58'),
(2, 'User', 3, 'mobile-app', '626a66010f2d1f6591a014ee09a70ab080fb444c6b537a38510aac6b9389d302', NULL, '2026-08-08 11:42:46', NULL, '2026-08-08 10:42:46', '2026-08-08 10:42:46'),
(3, 'User', 1, 'mobile-app', 'aa2df54b7aa878689296a36327dbc7405af0b242f9f83d94e1893db00fe6bc25', NULL, '2026-08-08 11:56:50', NULL, '2026-08-08 10:56:50', '2026-08-08 10:56:50'),
(5, 'User', 3, 'mobile-app', '56f47efd45f27c354e8a2c9778eade968713d07c79f9f7f74a1ae36eb1cefc8d', NULL, '2026-08-08 12:49:12', NULL, '2026-08-08 11:49:12', '2026-08-08 11:49:12'),
(7, 'User', 3, 'mobile-app', '07c558a1d2e8b635776c2a3683bb164827cc9d854f085d23fb73d916361fd025', NULL, '2026-08-08 12:49:45', '2026-08-08 12:49:26', '2026-08-08 11:49:45', '2026-08-08 12:49:26'),
(8, 'User', 3, 'mobile-app', '6d4c0856c92c70ff91e4d010ecb765a9eec4c5eb9f5d2fe96e64041f90a6d248', NULL, '2026-08-08 13:50:35', '2026-08-08 13:11:51', '2026-08-08 12:50:35', '2026-08-08 13:11:51'),
(9, 'User', 3, 'mobile-app', '645de376f93314a303e784457ff8f0ada640f0b25d14cd53163f6b6a3f85768c', NULL, '2026-08-08 14:15:22', '2026-08-08 14:15:05', '2026-08-08 13:15:22', '2026-08-08 14:15:05'),
(10, 'User', 3, 'mobile-app', '68b3975415852a14c3ebd2993cc9d0b2494e43c245a0e1c73ff111c34d7ec647', NULL, '2026-08-08 15:18:18', '2026-08-08 15:17:41', '2026-08-08 14:18:18', '2026-08-08 15:17:41'),
(11, 'User', 3, 'mobile-app', '07898882cb77995646fa049fc221260dbe7efa62c936312b426f7e4e65600192', NULL, '2026-08-08 16:21:47', '2026-08-08 15:31:15', '2026-08-08 15:21:47', '2026-08-08 15:31:15'),
(12, 'User', 3, 'mobile-app', 'cb13962f2feaeabb906ebe550285f213759fb9a84f2d3611981353f6fbf2e9a3', NULL, '2026-08-08 16:04:16', NULL, '2026-08-08 15:04:16', '2026-08-08 15:04:16'),
(13, 'User', 3, 'mobile-app', '52caf3a55e60807eabe8bc09a061e43a62340d47da8d63362c29f817361c755d', NULL, '2026-08-08 16:22:51', NULL, '2026-08-08 15:22:51', '2026-08-08 15:22:51'),
(14, 'User', 3, 'mobile-app', '59d1f0dca7c396b0a99ee42ff0d482525df63d3211924ea0aff4b4c36481b155', NULL, '2026-08-09 16:31:03', '2026-08-09 15:31:28', '2026-08-09 15:31:03', '2026-08-09 15:31:28'),
(15, 'User', 3, 'mobile-app', '83bcc51f6ed4c06cd5459b486f28fd4e119a83dcf53b7b8001f0c2771d6e0f0e', NULL, '2026-08-09 16:31:37', NULL, '2026-08-09 15:31:37', '2026-08-09 15:31:37'),
(16, 'User', 3, 'mobile-app', '5156b5b08584549f9556f27be62659bc2db6b6b8323c77538b29a4a661a2386e', NULL, '2026-08-10 12:51:23', NULL, '2026-08-10 11:51:23', '2026-08-10 11:51:23'),
(17, 'User', 3, 'mobile-app', '38f1dc3172f0742f628dcc396a0c35cd758a44e80d3f88e94bf7a3f6d2ea0661', NULL, '2026-08-10 13:48:50', NULL, '2026-08-10 12:48:50', '2026-08-10 12:48:50'),
(18, 'User', 3, 'mobile-app', '8c658fab3dab4a39cddd81acbbdebd54761a81265837f411c8f56d85f34c2c6f', NULL, '2026-08-12 17:17:40', NULL, '2026-08-12 16:17:40', '2026-08-12 16:17:40'),
(19, 'User', 3, 'mobile-app', 'f6aa9007e9ce9072563ad256b922186bd9a74d728add28d933524b9aefdcd4cb', NULL, '2026-08-12 17:46:51', NULL, '2026-08-12 16:46:51', '2026-08-12 16:46:51'),
(20, 'User', 3, 'mobile-app', 'dc32f5838f3ec78a5ecb5afa1435160cb9f72843972049740590a2dbcbab32f9', NULL, '2026-08-12 17:48:58', NULL, '2026-08-12 16:48:58', '2026-08-12 16:48:58'),
(21, 'User', 3, 'mobile-app', 'b925fbfff88f49e5a7a398aa1b1ce5e1f4bbe0ff05108952016d00dfade10bda', NULL, '2026-08-12 17:49:16', '2026-08-12 16:49:21', '2026-08-12 16:49:16', '2026-08-12 16:49:21'),
(22, 'User', 3, 'mobile-app', '68365f8f8436c1e4bb5fe29d7a8eb4dd1a5e0f20aaf88a88c9114f1d4dce784f', NULL, '2026-08-12 17:52:03', '2026-08-12 16:52:13', '2026-08-12 16:52:03', '2026-08-12 16:52:13'),
(23, 'User', 3, 'mobile-app', 'f8c66314ce00c1d634221b1b69a87dc3db7ee4a1c368dce934b0e596aedbb8d6', NULL, '2026-08-12 19:04:59', '2026-08-12 18:05:15', '2026-08-12 18:04:59', '2026-08-12 18:05:15'),
(24, 'User', 3, 'mobile-app', 'd557e2fa98e88027d029bb89216561b20d15bc3cfa6eb056abaa6a30eb990330', NULL, '2026-08-12 19:07:21', '2026-08-12 18:07:53', '2026-08-12 18:07:21', '2026-08-12 18:07:53'),
(25, 'User', 3, 'mobile-app', 'cad4dc5b12ce7587e3e5580211c9704c3925534d2195aeb3a9c6bb89d345f50c', NULL, '2026-08-15 07:54:00', NULL, '2026-08-15 06:54:00', '2026-08-15 06:54:00'),
(26, 'User', 3, 'mobile-app', 'fd268b68ec77976e0c4ee0dad39ede10c85e72b763ebc94884f6b7c63d0412bd', NULL, '2026-08-15 09:54:45', NULL, '2026-08-15 08:54:45', '2026-08-15 08:54:45');

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
-- Table structure for table `user_device_bindings`
--

CREATE TABLE `user_device_bindings` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `device_model` varchar(255) DEFAULT NULL,
  `push_token` varchar(512) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `bound_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_device_bindings`
--

INSERT INTO `user_device_bindings` (`id`, `user_id`, `device_id`, `device_model`, `push_token`, `is_active`, `bound_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'test-device-001', NULL, NULL, 1, '2026-08-15 08:54:45', '2026-08-08 10:39:49', '2026-08-15 08:54:45'),
(2, 1, 'test-device-001', NULL, NULL, 1, '2026-08-08 10:56:50', '2026-08-08 10:56:50', '2026-08-08 10:56:50'),
(3, 3, '31afae942e26162b', NULL, NULL, 1, '2026-08-09 15:31:03', '2026-08-09 15:31:03', '2026-08-09 15:31:03'),
(4, 3, '27cbd4eb-43d7-4d52-80f4-1546d41915b6', NULL, NULL, 1, '2026-08-12 16:52:03', '2026-08-12 16:49:16', '2026-08-12 16:52:03'),
(5, 3, '610c577b-74e9-4a85-b0ce-8bcccb0af203', NULL, NULL, 1, '2026-08-12 18:07:21', '2026-08-12 18:04:59', '2026-08-12 18:07:21');

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
(3, 'H001-EMP0002', 'System Admin', '9999999988', '1234@gmail.com', '$2y$10$eeBxh8cazPKyz6AQC7NQZOFvfdL2DI.neSCXOepI0.94GIiq83ujC', '$2y$10$iBzmO7u/Ud9f9W55owzvB.S8f4VGVRbDNtwQPa6VydnaNrIjTa.kq', 10, 1, 1, '2026-08-15 08:54:45', '2026-08-07 16:14:20', '2026-08-15 08:54:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `id` bigint(20) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` varchar(50) NOT NULL,
  `is_verified` tinyint(4) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_otps`
--

INSERT INTO `user_otps` (`id`, `mobile`, `otp_hash`, `purpose`, `is_verified`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, '9999999999', '$2y$10$.4X82IAXldMAimEwfcWYiOgURtxJrtBArkDJS69wU13FskXXp87QC', 'LOGIN', 0, '2026-08-08 10:53:30', '2026-08-08 10:48:30', '2026-08-08 10:48:30'),
(2, '9999999999', '$2y$10$Dc83MfNghpaa.OWy9x5PduoNL.dL2FNC/DBP2K6eyn.frM577pNjS', 'LOGIN', 1, '2026-08-08 11:00:33', '2026-08-08 10:55:33', '2026-08-08 10:56:50');

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
-- Table structure for table `vaults`
--

CREATE TABLE `vaults` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaults`
--

INSERT INTO `vaults` (`id`, `branch_id`, `name`, `created_at`) VALUES
(1, 1, 'Swarna Gold Loan - Chennai Main - Main Vault', '2026-08-07 16:14:20'),
(2, 2, 'Swarna Gold Loan - Tambaram - Main Vault', '2026-08-07 16:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_number` varchar(30) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL,
  `voucher_date` date NOT NULL,
  `source` varchar(40) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_number`, `branch_id`, `type`, `voucher_date`, `source`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'VCH20260808000001', 1, 'RECEIPT', '2026-08-02', NULL, 3, '2026-08-08 15:17:11', '2026-08-08 15:17:11');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_details`
--

CREATE TABLE `voucher_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED NOT NULL,
  `gl_account_id` bigint(20) UNSIGNED NOT NULL,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher_details`
--

INSERT INTO `voucher_details` (`id`, `voucher_id`, `gl_account_id`, `debit`, `credit`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1000.00, 0.00, '2026-08-08 15:17:11', '2026-08-08 15:17:11'),
(2, 2, 2, 0.00, 1000.00, '2026-08-08 15:17:11', '2026-08-08 15:17:11');

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
-- Indexes for table `auction_bidders`
--
ALTER TABLE `auction_bidders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ab_schedule` (`auction_schedule_id`);

--
-- Indexes for table `auction_bids`
--
ALTER TABLE `auction_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_abid_schedule` (`auction_schedule_id`),
  ADD KEY `fk_abid_packet` (`gold_packet_id`),
  ADD KEY `fk_abid_bidder` (`bidder_id`);

--
-- Indexes for table `auction_notice_logs`
--
ALTER TABLE `auction_notice_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_anl_schedule` (`auction_schedule_id`),
  ADD KEY `fk_anl_loan` (`loan_id`);

--
-- Indexes for table `auction_schedules`
--
ALTER TABLE `auction_schedules`
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
-- Indexes for table `auction_winners`
--
ALTER TABLE `auction_winners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aw_packet` (`gold_packet_id`),
  ADD KEY `fk_aw_bidder` (`bidder_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_aadhaar_hash` (`aadhaar_hash`),
  ADD KEY `fk_cust_branch` (`branch_id`),
  ADD KEY `fk_cust_registered_by` (`registered_by`);

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
-- Indexes for table `customer_family_members`
--
ALTER TABLE `customer_family_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cfm_customer` (`customer_id`);

--
-- Indexes for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cl_customer` (`customer_id`),
  ADD KEY `fk_cl_loan` (`loan_id`);

--
-- Indexes for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cml_primary` (`primary_customer_id`),
  ADD KEY `fk_cml_merged` (`merged_customer_id`),
  ADD KEY `fk_cml_approver` (`approved_by`);

--
-- Indexes for table `customer_nominees`
--
ALTER TABLE `customer_nominees`
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
-- Indexes for table `gl_accounts`
--
ALTER TABLE `gl_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `gold_packets`
--
ALTER TABLE `gold_packets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `packet_code` (`packet_code`),
  ADD KEY `fk_gp_item` (`jewellery_item_id`),
  ADD KEY `fk_gp_vault` (`vault_id`);

--
-- Indexes for table `gold_rates`
--
ALTER TABLE `gold_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grm_proposer` (`proposed_by`),
  ADD KEY `fk_grm_approver` (`approved_by`);

--
-- Indexes for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gral_rate` (`gold_rate_id`),
  ADD KEY `fk_gral_actor` (`actioned_by`);

--
-- Indexes for table `gold_releases`
--
ALTER TABLE `gold_releases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grl_loan` (`loan_id`),
  ADD KEY `fk_grl_item` (`jewellery_item_id`),
  ADD KEY `fk_grl_actor` (`released_by`);

--
-- Indexes for table `interest_collections`
--
ALTER TABLE `interest_collections`
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
-- Indexes for table `jewellery_images`
--
ALTER TABLE `jewellery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jim_item` (`jewellery_item_id`);

--
-- Indexes for table `jewellery_items`
--
ALTER TABLE `jewellery_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `fk_ji_customer` (`customer_id`),
  ADD KEY `fk_ji_category` (`category_id`),
  ADD KEY `fk_ji_rate` (`gold_rate_id`),
  ADD KEY `fk_ji_evaluator` (`evaluated_by`),
  ADD KEY `fk_ji_loan` (`loan_id`);

--
-- Indexes for table `kyc_aadhaar_verifications`
--
ALTER TABLE `kyc_aadhaar_verifications`
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
-- Indexes for table `kyc_document_types`
--
ALTER TABLE `kyc_document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `kyc_face_auth_logs`
--
ALTER TABLE `kyc_face_auth_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kfa_customer` (`customer_id`);

--
-- Indexes for table `kyc_pan_verifications`
--
ALTER TABLE `kyc_pan_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kpv_customer` (`customer_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_account_number` (`loan_account_number`),
  ADD KEY `idx_loan_status` (`status`),
  ADD KEY `fk_lm_customer` (`customer_id`),
  ADD KEY `fk_lm_branch` (`branch_id`),
  ADD KEY `fk_lm_product` (`loan_product_id`),
  ADD KEY `fk_lm_creator` (`created_by`);

--
-- Indexes for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lalm_role` (`role_id`);

--
-- Indexes for table `loan_approval_logs`
--
ALTER TABLE `loan_approval_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lal_loan` (`loan_id`),
  ADD KEY `fk_lal_actor` (`actioned_by`);

--
-- Indexes for table `loan_approval_workflows`
--
ALTER TABLE `loan_approval_workflows`
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
-- Indexes for table `loan_charges`
--
ALTER TABLE `loan_charges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_closures`
--
ALTER TABLE `loan_closures`
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
-- Indexes for table `loan_disbursements`
--
ALTER TABLE `loan_disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ld_loan` (`loan_id`),
  ADD KEY `fk_ld_actor` (`disbursed_by`);

--
-- Indexes for table `loan_part_payments`
--
ALTER TABLE `loan_part_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lppl_loan` (`loan_id`),
  ADD KEY `fk_lppl_actor` (`collected_by`);

--
-- Indexes for table `loan_products`
--
ALTER TABLE `loan_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `loan_reloads`
--
ALTER TABLE `loan_reloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lrel_loan` (`loan_id`),
  ADD KEY `fk_lrel_actor` (`processed_by`);

--
-- Indexes for table `loan_renewals`
--
ALTER TABLE `loan_renewals`
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
-- Indexes for table `loan_topups`
--
ALTER TABLE `loan_topups`
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
-- Indexes for table `packet_transfer_logs`
--
ALTER TABLE `packet_transfer_logs`
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
-- Indexes for table `user_device_bindings`
--
ALTER TABLE `user_device_bindings`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mobile` (`mobile`),
  ADD KEY `idx_purpose` (`purpose`);

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
-- Indexes for table `vaults`
--
ALTER TABLE `vaults`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vm_branch` (`branch_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_number` (`voucher_number`),
  ADD KEY `fk_vm2_branch` (`branch_id`),
  ADD KEY `fk_vm2_creator` (`created_by`);

--
-- Indexes for table `voucher_details`
--
ALTER TABLE `voucher_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vd_voucher` (`voucher_id`),
  ADD KEY `fk_vd_account` (`gl_account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_config_master`
--
ALTER TABLE `app_config_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `auction_bidders`
--
ALTER TABLE `auction_bidders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auction_bids`
--
ALTER TABLE `auction_bids`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auction_notice_logs`
--
ALTER TABLE `auction_notice_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auction_schedules`
--
ALTER TABLE `auction_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_winners`
--
ALTER TABLE `auction_winners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `customer_family_members`
--
ALTER TABLE `customer_family_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_nominees`
--
ALTER TABLE `customer_nominees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `gl_accounts`
--
ALTER TABLE `gl_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gold_packets`
--
ALTER TABLE `gold_packets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gold_rates`
--
ALTER TABLE `gold_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_releases`
--
ALTER TABLE `gold_releases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `interest_collections`
--
ALTER TABLE `interest_collections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `jewellery_images`
--
ALTER TABLE `jewellery_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jewellery_items`
--
ALTER TABLE `jewellery_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_verifications`
--
ALTER TABLE `kyc_aadhaar_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kyc_document_types`
--
ALTER TABLE `kyc_document_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kyc_face_auth_logs`
--
ALTER TABLE `kyc_face_auth_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kyc_pan_verifications`
--
ALTER TABLE `kyc_pan_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_approval_logs`
--
ALTER TABLE `loan_approval_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_approval_workflows`
--
ALTER TABLE `loan_approval_workflows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_charges`
--
ALTER TABLE `loan_charges`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_closures`
--
ALTER TABLE `loan_closures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_closure_charge`
--
ALTER TABLE `loan_closure_charge`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_disbursements`
--
ALTER TABLE `loan_disbursements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `loan_part_payments`
--
ALTER TABLE `loan_part_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_products`
--
ALTER TABLE `loan_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loan_reloads`
--
ALTER TABLE `loan_reloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_renewals`
--
ALTER TABLE `loan_renewals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loan_scheme_master`
--
ALTER TABLE `loan_scheme_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `loan_topups`
--
ALTER TABLE `loan_topups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `packet_transfer_logs`
--
ALTER TABLE `packet_transfer_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
-- AUTO_INCREMENT for table `user_device_bindings`
--
ALTER TABLE `user_device_bindings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `vaults`
--
ALTER TABLE `vaults`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `voucher_details`
--
ALTER TABLE `voucher_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auction_bidders`
--
ALTER TABLE `auction_bidders`
  ADD CONSTRAINT `fk_ab_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_bids`
--
ALTER TABLE `auction_bids`
  ADD CONSTRAINT `fk_abid_bidder` FOREIGN KEY (`bidder_id`) REFERENCES `auction_bidders` (`id`),
  ADD CONSTRAINT `fk_abid_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packets` (`id`),
  ADD CONSTRAINT `fk_abid_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_notice_logs`
--
ALTER TABLE `auction_notice_logs`
  ADD CONSTRAINT `fk_anl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `fk_anl_schedule` FOREIGN KEY (`auction_schedule_id`) REFERENCES `auction_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `auction_schedules`
--
ALTER TABLE `auction_schedules`
  ADD CONSTRAINT `fk_as_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_as_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  ADD CONSTRAINT `fk_ast_actor` FOREIGN KEY (`settled_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ast_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `fk_ast_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packets` (`id`);

--
-- Constraints for table `auction_winners`
--
ALTER TABLE `auction_winners`
  ADD CONSTRAINT `fk_aw_bidder` FOREIGN KEY (`bidder_id`) REFERENCES `auction_bidders` (`id`),
  ADD CONSTRAINT `fk_aw_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packets` (`id`);

--
-- Constraints for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  ADD CONSTRAINT `fk_brl_actor` FOREIGN KEY (`reconciled_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_brl_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `cash_book`
--
ALTER TABLE `cash_book`
  ADD CONSTRAINT `fk_cb_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_cust_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_cust_registered_by` FOREIGN KEY (`registered_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `fk_caddr_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_biometric_ref`
--
ALTER TABLE `customer_biometric_ref`
  ADD CONSTRAINT `fk_cbr_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_duplicate_log`
--
ALTER TABLE `customer_duplicate_log`
  ADD CONSTRAINT `fk_cdl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cdl_matched` FOREIGN KEY (`matched_customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cdl_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `customer_family_members`
--
ALTER TABLE `customer_family_members`
  ADD CONSTRAINT `fk_cfm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_ledgers`
--
ALTER TABLE `customer_ledgers`
  ADD CONSTRAINT `fk_cl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_cl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`);

--
-- Constraints for table `customer_merge_log`
--
ALTER TABLE `customer_merge_log`
  ADD CONSTRAINT `fk_cml_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_cml_merged` FOREIGN KEY (`merged_customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_cml_primary` FOREIGN KEY (`primary_customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `customer_nominees`
--
ALTER TABLE `customer_nominees`
  ADD CONSTRAINT `fk_cnom_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_visit_log`
--
ALTER TABLE `customer_visit_log`
  ADD CONSTRAINT `fk_cvl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_cvl_user` FOREIGN KEY (`user_id`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `day_book`
--
ALTER TABLE `day_book`
  ADD CONSTRAINT `fk_db_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

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
-- Constraints for table `gold_packets`
--
ALTER TABLE `gold_packets`
  ADD CONSTRAINT `fk_gp_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_items` (`id`),
  ADD CONSTRAINT `fk_gp_vault` FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`);

--
-- Constraints for table `gold_rates`
--
ALTER TABLE `gold_rates`
  ADD CONSTRAINT `fk_grm_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_grm_proposer` FOREIGN KEY (`proposed_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `gold_rate_approval_log`
--
ALTER TABLE `gold_rate_approval_log`
  ADD CONSTRAINT `fk_gral_actor` FOREIGN KEY (`actioned_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_gral_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gold_releases`
--
ALTER TABLE `gold_releases`
  ADD CONSTRAINT `fk_grl_actor` FOREIGN KEY (`released_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_grl_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_items` (`id`),
  ADD CONSTRAINT `fk_grl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interest_collections`
--
ALTER TABLE `interest_collections`
  ADD CONSTRAINT `fk_icl_actor` FOREIGN KEY (`collected_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_icl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interest_receipt`
--
ALTER TABLE `interest_receipt`
  ADD CONSTRAINT `fk_ir_collection` FOREIGN KEY (`interest_collection_log_id`) REFERENCES `interest_collections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jewellery_images`
--
ALTER TABLE `jewellery_images`
  ADD CONSTRAINT `fk_jim_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jewellery_items`
--
ALTER TABLE `jewellery_items`
  ADD CONSTRAINT `fk_ji_category` FOREIGN KEY (`category_id`) REFERENCES `jewellery_category_master` (`id`),
  ADD CONSTRAINT `fk_ji_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_ji_evaluator` FOREIGN KEY (`evaluated_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ji_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `fk_ji_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rates` (`id`);

--
-- Constraints for table `kyc_aadhaar_verifications`
--
ALTER TABLE `kyc_aadhaar_verifications`
  ADD CONSTRAINT `fk_kav_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  ADD CONSTRAINT `fk_kax_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  ADD CONSTRAINT `fk_kdm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kdm_doctype` FOREIGN KEY (`document_type_id`) REFERENCES `kyc_document_types` (`id`),
  ADD CONSTRAINT `fk_kdm_verifier` FOREIGN KEY (`verified_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `kyc_face_auth_logs`
--
ALTER TABLE `kyc_face_auth_logs`
  ADD CONSTRAINT `fk_kfa_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc_pan_verifications`
--
ALTER TABLE `kyc_pan_verifications`
  ADD CONSTRAINT `fk_kpv_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_lm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_lm_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lm_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_lm_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`);

--
-- Constraints for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  ADD CONSTRAINT `fk_lalm_role` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`id`);

--
-- Constraints for table `loan_approval_logs`
--
ALTER TABLE `loan_approval_logs`
  ADD CONSTRAINT `fk_lal_actor` FOREIGN KEY (`actioned_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lal_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_approval_workflows`
--
ALTER TABLE `loan_approval_workflows`
  ADD CONSTRAINT `fk_law_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  ADD CONSTRAINT `fk_lcl_actor` FOREIGN KEY (`calculated_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lcl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_closures`
--
ALTER TABLE `loan_closures`
  ADD CONSTRAINT `fk_lc_actor` FOREIGN KEY (`closed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lc_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_closure_charge`
--
ALTER TABLE `loan_closure_charge`
  ADD CONSTRAINT `fk_lcc_closure` FOREIGN KEY (`loan_closure_id`) REFERENCES `loan_closures` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_disbursements`
--
ALTER TABLE `loan_disbursements`
  ADD CONSTRAINT `fk_ld_actor` FOREIGN KEY (`disbursed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ld_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ld_mode` FOREIGN KEY (`mode`) REFERENCES `disbursement_mode_master` (`id`);

--
-- Constraints for table `loan_part_payments`
--
ALTER TABLE `loan_part_payments`
  ADD CONSTRAINT `fk_lppl_actor` FOREIGN KEY (`collected_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lppl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_reloads`
--
ALTER TABLE `loan_reloads`
  ADD CONSTRAINT `fk_lrel_actor` FOREIGN KEY (`processed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lrel_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_renewals`
--
ALTER TABLE `loan_renewals`
  ADD CONSTRAINT `fk_lrl_actor` FOREIGN KEY (`processed_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_lrl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_scheme_master`
--
ALTER TABLE `loan_scheme_master`
  ADD CONSTRAINT `fk_lsm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_lsm_product` FOREIGN KEY (`loan_product_id`) REFERENCES `loan_products` (`id`);

--
-- Constraints for table `loan_topups`
--
ALTER TABLE `loan_topups`
  ADD CONSTRAINT `fk_ltl_approver` FOREIGN KEY (`approved_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ltl_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_nl_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_nl_template` FOREIGN KEY (`template_id`) REFERENCES `notification_template` (`id`);

--
-- Constraints for table `packet_tracking_log`
--
ALTER TABLE `packet_tracking_log`
  ADD CONSTRAINT `fk_ptrl_actor` FOREIGN KEY (`logged_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ptrl_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packet_transfer_logs`
--
ALTER TABLE `packet_transfer_logs`
  ADD CONSTRAINT `fk_ptl_actor` FOREIGN KEY (`transferred_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_ptl_from` FOREIGN KEY (`from_vault_id`) REFERENCES `vaults` (`id`),
  ADD CONSTRAINT `fk_ptl_packet` FOREIGN KEY (`gold_packet_id`) REFERENCES `gold_packets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ptl_to` FOREIGN KEY (`to_vault_id`) REFERENCES `vaults` (`id`);

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
  ADD CONSTRAINT `fk_user_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
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
-- Constraints for table `vaults`
--
ALTER TABLE `vaults`
  ADD CONSTRAINT `fk_vm_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `fk_vm2_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_vm2_creator` FOREIGN KEY (`created_by`) REFERENCES `user_master` (`id`);

--
-- Constraints for table `voucher_details`
--
ALTER TABLE `voucher_details`
  ADD CONSTRAINT `fk_vd_account` FOREIGN KEY (`gl_account_id`) REFERENCES `gl_accounts` (`id`),
  ADD CONSTRAINT `fk_vd_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
