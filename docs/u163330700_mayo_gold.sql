-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 05, 2026 at 02:54 AM
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
(1, 'CASH_DISBURSEMENT_LIMIT', '2000000', '2026-08-07 16:14:20', '2026-08-29 16:08:24'),
(2, 'OTP_EXPIRY_MINUTES', '5', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(3, 'JWT_TOKEN_EXPIRY_MINUTES', '600', '2026-08-07 16:14:20', '2026-08-29 16:08:41'),
(4, 'DEFAULT_ELIGIBLE_PERCENTAGE', '75.00', '2026-08-07 16:14:20', '2026-08-07 16:14:20'),
(5, 'DEFAULT_COMPANY_CODE', 'AF001', '2026-08-07 16:14:20', '2026-08-29 16:09:10'),
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
(191, 'Customer', 1, 'NOMINEE_ADD', NULL, '{\"id\":\"1\",\"customer_id\":\"1\",\"name\":\"Perumal\",\"relation\":\"Husband\",\"mobile\":\"8111075554\",\"id_proof_type\":\"Aadhar Card\",\"id_proof_number\":\"788340412928\",\"created_at\":\"2026-09-02 06:12:37\",\"updated_at\":\"2026-09-02 06:12:37\"}', 3, '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(192, 'Loan', 1, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"1\",\"loan_id\":\"1\",\"document_type\":\"JEWELLERY_PHOTO\",\"file_ref\":\"loan-documents\\/3bb1e6e043db23ce6a1b1b3468db700c.jpg\",\"uploaded_by\":\"3\",\"created_at\":\"2026-09-02 06:13:09\",\"updated_at\":\"2026-09-02 06:13:09\"}', 3, '2026-09-02 06:13:09', '2026-09-02 06:13:09'),
(193, 'Loan', 1, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"2\",\"loan_id\":\"1\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/7d3d624db05eaed1738eadc6ff42e4a3.jpg\",\"uploaded_by\":\"3\",\"created_at\":\"2026-09-02 06:13:34\",\"updated_at\":\"2026-09-02 06:13:34\"}', 3, '2026-09-02 06:13:34', '2026-09-02 06:13:34'),
(194, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:13:46', '2026-09-02 06:13:46'),
(195, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:14:22', '2026-09-02 06:14:22'),
(196, 'Customer', 1, 'CUSTOMER_PHOTO_UPDATE', '{\"photo_path\":null}', '{\"photo_path\":\"customer-photos\\/74924fd018706c7cdff12ed80d4ba242.jpg\"}', 3, '2026-09-02 06:14:43', '2026-09-02 06:14:43'),
(197, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:14:43', '2026-09-02 06:14:43'),
(198, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:15:04', '2026-09-02 06:15:04'),
(199, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:15:04', '2026-09-02 06:15:04'),
(200, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:20:16', '2026-09-02 06:20:16'),
(201, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:20:16', '2026-09-02 06:20:16'),
(202, 'Loan', 1, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"3\",\"loan_id\":\"1\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/327f08453229e84f29696c9ddf8a2518.jpg\",\"uploaded_by\":\"3\",\"created_at\":\"2026-09-02 06:30:02\",\"updated_at\":\"2026-09-02 06:30:02\"}', 3, '2026-09-02 06:30:02', '2026-09-02 06:30:02'),
(203, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:30:09', '2026-09-02 06:30:09'),
(204, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:30:09', '2026-09-02 06:30:09'),
(205, 'Loan', 1, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":1,\"mode\":\"CASH\",\"amount\":\"265686.10\"}', 3, '2026-09-02 06:30:30', '2026-09-02 06:30:30'),
(206, 'KycDocument', 2, 'KYC_DOCUMENT_UPLOAD', NULL, '{\"id\":\"2\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/c9569a458dea7c972e050e69fc54c280.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:36:00\",\"updated_at\":\"2026-09-02 06:36:00\"}', 3, '2026-09-02 06:36:00', '2026-09-02 06:36:00'),
(207, 'KycDocument', 3, 'KYC_DOCUMENT_UPLOAD', NULL, '{\"id\":\"3\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1882601719d6aaadacba88607b9cfcae.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:41:13\",\"updated_at\":\"2026-09-02 06:41:13\"}', 3, '2026-09-02 06:41:13', '2026-09-02 06:41:13'),
(208, 'KycDocument', 3, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:41:20', '2026-09-02 06:41:20'),
(209, 'KycDocument', 2, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:41:25', '2026-09-02 06:41:25'),
(210, 'KycDocument', 2, 'KYC_DOCUMENT_REJECT', '{\"id\":\"2\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/c9569a458dea7c972e050e69fc54c280.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:36:00\",\"updated_at\":\"2026-09-02 06:36:00\"}', '{\"id\":\"2\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/c9569a458dea7c972e050e69fc54c280.jpg\",\"status\":\"REJECTED\",\"verified_by\":\"3\",\"rejection_reason\":\"wrongly uploaded\",\"created_at\":\"2026-09-02 06:36:00\",\"updated_at\":\"2026-09-02 06:41:43\"}', 3, '2026-09-02 06:41:43', '2026-09-02 06:41:43'),
(211, 'KycDocument', 4, 'KYC_DOCUMENT_UPLOAD', NULL, '{\"id\":\"4\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1fd7e146c37085a24f468c11f9114857.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:43:34\",\"updated_at\":\"2026-09-02 06:43:34\"}', 3, '2026-09-02 06:43:34', '2026-09-02 06:43:34'),
(212, 'KycDocument', 4, 'KYC_DOCUMENT_VERIFY', '{\"id\":\"4\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1fd7e146c37085a24f468c11f9114857.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:43:34\",\"updated_at\":\"2026-09-02 06:43:34\"}', '{\"id\":\"4\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1fd7e146c37085a24f468c11f9114857.jpg\",\"status\":\"VERIFIED\",\"verified_by\":\"3\",\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:43:34\",\"updated_at\":\"2026-09-02 06:43:37\"}', 3, '2026-09-02 06:43:37', '2026-09-02 06:43:37'),
(213, 'KycDocument', 3, 'KYC_DOCUMENT_VERIFY', '{\"id\":\"3\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1882601719d6aaadacba88607b9cfcae.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:41:13\",\"updated_at\":\"2026-09-02 06:41:13\"}', '{\"id\":\"3\",\"customer_id\":\"1\",\"document_type_id\":\"6\",\"file_ref\":\"kyc-documents\\/1882601719d6aaadacba88607b9cfcae.jpg\",\"status\":\"VERIFIED\",\"verified_by\":\"3\",\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:41:13\",\"updated_at\":\"2026-09-02 06:43:50\"}', 3, '2026-09-02 06:43:50', '2026-09-02 06:43:50'),
(214, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:50:40', '2026-09-02 06:50:40'),
(215, 'KycDocument', 2, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:51:08', '2026-09-02 06:51:08'),
(216, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:51:28', '2026-09-02 06:51:28'),
(217, 'Customer', 1, 'KYC_AADHAAR_QR_SCAN', '{\"aadhaar_last4\":null,\"aadhaar_hash\":null}', '{\"aadhaar_last4\":\"2928\",\"aadhaar_hash\":\"adc25a5bc56b66744f7e1dd625db0cb85d7cbd18a0654a9e02886bd5ce54271f\",\"verification\":{\"id\":\"4\",\"customer_id\":\"1\",\"method\":\"QR\",\"uidai_reference_id\":null,\"is_verified\":\"1\",\"verified_at\":\"2026-09-02 06:52:07\",\"created_at\":\"2026-09-02 06:52:07\",\"updated_at\":\"2026-09-02 06:52:07\"}}', 3, '2026-09-02 06:52:07', '2026-09-02 06:52:07'),
(218, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:52:07', '2026-09-02 06:52:07'),
(219, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:52:23', '2026-09-02 06:52:23'),
(220, 'KycDocument', 2, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:52:46', '2026-09-02 06:52:46'),
(221, 'KycDocument', 3, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:52:56', '2026-09-02 06:52:56'),
(222, 'KycDocument', 4, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:53:05', '2026-09-02 06:53:05'),
(223, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:53:14', '2026-09-02 06:53:14'),
(224, 'KycDocument', 4, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:54:12', '2026-09-02 06:54:12'),
(225, 'KycDocument', 3, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:54:15', '2026-09-02 06:54:15'),
(226, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:54:21', '2026-09-02 06:54:21'),
(227, 'KycDocument', 2, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:54:44', '2026-09-02 06:54:44'),
(228, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:54:59', '2026-09-02 06:54:59'),
(229, 'KycDocument', 5, 'KYC_DOCUMENT_UPLOAD', NULL, '{\"id\":\"5\",\"customer_id\":\"1\",\"document_type_id\":\"7\",\"file_ref\":\"kyc-documents\\/f8b1c7601294ca5c80a48c9cb69a2be9.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:58:10\",\"updated_at\":\"2026-09-02 06:58:10\"}', 3, '2026-09-02 06:58:10', '2026-09-02 06:58:10'),
(230, 'KycDocument', 5, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:58:17', '2026-09-02 06:58:17'),
(231, 'KycDocument', 5, 'KYC_DOCUMENT_VERIFY', '{\"id\":\"5\",\"customer_id\":\"1\",\"document_type_id\":\"7\",\"file_ref\":\"kyc-documents\\/f8b1c7601294ca5c80a48c9cb69a2be9.jpg\",\"status\":\"PENDING\",\"verified_by\":null,\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:58:10\",\"updated_at\":\"2026-09-02 06:58:10\"}', '{\"id\":\"5\",\"customer_id\":\"1\",\"document_type_id\":\"7\",\"file_ref\":\"kyc-documents\\/f8b1c7601294ca5c80a48c9cb69a2be9.jpg\",\"status\":\"VERIFIED\",\"verified_by\":\"3\",\"rejection_reason\":null,\"created_at\":\"2026-09-02 06:58:10\",\"updated_at\":\"2026-09-02 06:58:27\"}', 3, '2026-09-02 06:58:27', '2026-09-02 06:58:27'),
(232, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:58:37', '2026-09-02 06:58:37'),
(233, 'KycDocument', 3, 'KYC_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 06:58:44', '2026-09-02 06:58:44'),
(234, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:58:53', '2026-09-02 06:58:53'),
(235, 'Customer', 1, 'KYC_PAN_VALIDATE', NULL, '{\"id\":\"2\",\"customer_id\":\"1\",\"is_verified\":\"1\",\"name_match\":\"1\",\"created_at\":\"2026-09-02 06:59:13\",\"updated_at\":\"2026-09-02 06:59:13\"}', 3, '2026-09-02 06:59:13', '2026-09-02 06:59:13'),
(236, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 06:59:13', '2026-09-02 06:59:13'),
(237, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 3, '2026-09-02 07:07:57', '2026-09-02 07:07:57'),
(238, 'Loan', 1, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 3, '2026-09-02 07:07:57', '2026-09-02 07:07:57'),
(239, 'Customer', 2, 'NOMINEE_ADD', NULL, '{\"id\":\"2\",\"customer_id\":\"2\",\"name\":\"Vijayaraman\",\"relation\":\"Husband\",\"mobile\":\"9344196536\",\"id_proof_type\":\"Aadhar\",\"id_proof_number\":\"207843879017\",\"created_at\":\"2026-09-02 07:25:27\",\"updated_at\":\"2026-09-02 07:25:27\"}', 1, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(240, 'Customer', 2, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 07:25:36', '2026-09-02 07:25:36'),
(241, 'Loan', 2, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-02 07:25:36', '2026-09-02 07:25:36'),
(242, 'Loan', 2, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"5\",\"loan_id\":\"2\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/50d198fae47ef7196a08ef9a7fce27fe.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 07:29:43\",\"updated_at\":\"2026-09-02 07:29:43\"}', 1, '2026-09-02 07:29:43', '2026-09-02 07:29:43'),
(243, 'Loan', 2, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"6\",\"loan_id\":\"2\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/4eb1941a675bc623e0bde46cb5c9bd43.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 07:30:59\",\"updated_at\":\"2026-09-02 07:30:59\"}', 1, '2026-09-02 07:30:59', '2026-09-02 07:30:59'),
(244, 'Loan', 2, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":2,\"mode\":\"CASH\",\"amount\":\"72713.98\"}', 1, '2026-09-02 07:31:22', '2026-09-02 07:31:22'),
(245, 'Customer', 2, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 07:32:41', '2026-09-02 07:32:41'),
(246, 'GoldRate', 12, 'RATE_APPROVE', '{\"status\":\"PENDING_APPROVAL\"}', '{\"status\":\"APPROVED\"}', 1, '2026-09-02 07:50:36', '2026-09-02 07:50:36'),
(247, 'Customer', 3, 'NOMINEE_ADD', NULL, '{\"id\":\"3\",\"customer_id\":\"3\",\"name\":\"Vignesh\",\"relation\":\"Son\",\"mobile\":\"9629702010\",\"id_proof_type\":\"Aadhar\",\"id_proof_number\":\"421150428689\",\"created_at\":\"2026-09-02 07:55:58\",\"updated_at\":\"2026-09-02 07:55:58\"}', 1, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(248, 'Customer', 3, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 07:56:13', '2026-09-02 07:56:13'),
(249, 'Loan', 3, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-02 07:56:13', '2026-09-02 07:56:13'),
(250, 'Loan', 3, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"8\",\"loan_id\":\"3\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/f3a1de2f1d388df6fd958f592f64ff3e.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 08:03:30\",\"updated_at\":\"2026-09-02 08:03:30\"}', 1, '2026-09-02 08:03:30', '2026-09-02 08:03:30'),
(251, 'Loan', 3, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"9\",\"loan_id\":\"3\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/f85ff454354289ced364fd12c5825766.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 08:07:56\",\"updated_at\":\"2026-09-02 08:07:56\"}', 1, '2026-09-02 08:07:56', '2026-09-02 08:07:56'),
(252, 'Loan', 3, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":3,\"mode\":\"CASH\",\"amount\":\"66920.70\"}', 1, '2026-09-02 08:08:27', '2026-09-02 08:08:27'),
(253, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 08:38:10', '2026-09-02 08:38:10'),
(254, 'Loan', 4, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-02 08:38:10', '2026-09-02 08:38:10'),
(255, 'Loan', 4, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"11\",\"loan_id\":\"4\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/cb8df1a2329a466cdcdadf98ad7d6d27.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 08:38:56\",\"updated_at\":\"2026-09-02 08:38:56\"}', 1, '2026-09-02 08:38:56', '2026-09-02 08:38:56'),
(256, 'Loan', 4, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"12\",\"loan_id\":\"4\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/1328f15199f90d43b785f4fde8b59a1a.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 08:39:15\",\"updated_at\":\"2026-09-02 08:39:15\"}', 1, '2026-09-02 08:39:15', '2026-09-02 08:39:15'),
(257, 'Loan', 4, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":4,\"mode\":\"CASH\",\"amount\":\"83900.88\"}', 1, '2026-09-02 08:39:46', '2026-09-02 08:39:46'),
(258, 'Loan', 4, 'PART_PAYMENT', '{\"status\":\"ACTIVE\",\"sanctioned_amount\":\"84000.00\"}', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"0.00\",\"payment_id\":1}', 1, '2026-09-02 08:41:25', '2026-09-02 08:41:25'),
(259, 'Loan', 4, 'PART_PAYMENT', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"0.00\"}', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"-84000.00\",\"payment_id\":2}', 1, '2026-09-02 08:42:05', '2026-09-02 08:42:05'),
(260, 'JewelleryItem', 4, 'IMAGE_UPLOAD', NULL, '{\"jewellery_image_id\":1,\"file_ref\":\"jewellery-images\\/59f36a3e7be8fa8e7ce946c62c579c3c.jpg\"}', 1, '2026-09-02 08:45:15', '2026-09-02 08:45:15'),
(261, 'Loan', 4, 'TOPUP_APPROVE', '{\"status\":\"PART_PAID\"}', '{\"topup_id\":1,\"eligible_topup_amount\":168000,\"approved_amount\":\"168000\"}', 1, '2026-09-02 08:45:44', '2026-09-02 08:45:44'),
(262, 'Loan', 4, 'TOPUP_DISBURSE', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"-84000.00\"}', '{\"sanctioned_amount\":\"84000.00\",\"topup_id\":\"1\"}', 1, '2026-09-02 08:46:01', '2026-09-02 08:46:01'),
(263, 'Loan', 4, 'SETTLE', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"84000.00\"}', '{\"status\":\"SETTLED\",\"closure_id\":1,\"total_amount_collected\":\"84000\"}', 1, '2026-09-02 08:47:11', '2026-09-02 08:47:11'),
(264, 'Loan', 4, 'INTEREST_COLLECT', NULL, '{\"collection_id\":1,\"amount\":\"765\",\"mode\":\"CASH\"}', 1, '2026-09-02 08:48:57', '2026-09-02 08:48:57'),
(265, 'Loan', 5, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-02 09:02:43', '2026-09-02 09:02:43'),
(266, 'Customer', 5, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 09:02:43', '2026-09-02 09:02:43'),
(267, 'Loan', 5, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"14\",\"loan_id\":\"5\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/a9e8881d9120adbac89b075936838583.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 09:03:48\",\"updated_at\":\"2026-09-02 09:03:48\"}', 1, '2026-09-02 09:03:48', '2026-09-02 09:03:48'),
(268, 'Loan', 5, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"15\",\"loan_id\":\"5\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/a612947102560592560f19a69c2a6a34.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 09:04:18\",\"updated_at\":\"2026-09-02 09:04:18\"}', 1, '2026-09-02 09:04:18', '2026-09-02 09:04:18'),
(269, 'Loan', 5, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":5,\"mode\":\"CASH\",\"amount\":\"49940.99\"}', 1, '2026-09-02 09:04:41', '2026-09-02 09:04:41'),
(270, 'Customer', 6, 'CUSTOMER_PHOTO_UPDATE', '{\"photo_path\":null}', '{\"photo_path\":\"customer-photos\\/2e891bde1dc15c0dc07dbc592a302889.jpg\"}', 1, '2026-09-02 11:13:29', '2026-09-02 11:13:29'),
(271, 'Customer', 6, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 11:13:29', '2026-09-02 11:13:29'),
(272, 'Customer', 6, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-02 11:14:15', '2026-09-02 11:14:15'),
(273, 'Loan', 6, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"16\",\"loan_id\":\"6\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/6a110bd6421ff37fd727c10c54a7af52.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 14:46:30\",\"updated_at\":\"2026-09-02 14:46:30\"}', 1, '2026-09-02 14:46:30', '2026-09-02 14:46:30'),
(274, 'Loan', 6, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"17\",\"loan_id\":\"6\",\"document_type\":\"JEWELLERY_PHOTO\",\"file_ref\":\"loan-documents\\/1cc627284e650bd31b86586d4d40a063.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 14:46:48\",\"updated_at\":\"2026-09-02 14:46:48\"}', 1, '2026-09-02 14:46:48', '2026-09-02 14:46:48'),
(275, 'Loan', 6, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"18\",\"loan_id\":\"6\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/41b42a643138d31ffb76dd675c2abdaa.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-02 14:47:21\",\"updated_at\":\"2026-09-02 14:47:21\"}', 1, '2026-09-02 14:47:21', '2026-09-02 14:47:21'),
(276, 'Loan', 6, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":6,\"mode\":\"CASH\",\"amount\":\"10986.23\"}', 1, '2026-09-02 14:47:51', '2026-09-02 14:47:51'),
(277, 'Customer', 7, 'NOMINEE_ADD', NULL, '{\"id\":\"4\",\"customer_id\":\"7\",\"name\":\"Kumar\",\"relation\":\"Husband\",\"mobile\":\"8825477698\",\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-09-03 07:05:27\",\"updated_at\":\"2026-09-03 07:05:27\"}', 1, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(278, 'Customer', 7, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:05:30', '2026-09-03 07:05:30'),
(279, 'Loan', 7, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:05:30', '2026-09-03 07:05:30'),
(280, 'Loan', 7, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"20\",\"loan_id\":\"7\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/b3430255878f940f06cb45a45acb2956.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 07:08:09\",\"updated_at\":\"2026-09-03 07:08:09\"}', 1, '2026-09-03 07:08:09', '2026-09-03 07:08:09'),
(281, 'Loan', 7, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"21\",\"loan_id\":\"7\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/574c5beb434468845061ec993c285cb5.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 07:08:38\",\"updated_at\":\"2026-09-03 07:08:38\"}', 1, '2026-09-03 07:08:38', '2026-09-03 07:08:38'),
(282, 'Customer', 7, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:09:17', '2026-09-03 07:09:17'),
(283, 'Loan', 7, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:09:17', '2026-09-03 07:09:17'),
(284, 'Loan', 7, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":7,\"mode\":\"CASH\",\"amount\":\"173276.27\"}', 1, '2026-09-03 07:14:05', '2026-09-03 07:14:05'),
(285, 'Customer', 7, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:18:40', '2026-09-03 07:18:40'),
(286, 'Loan', 7, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:18:40', '2026-09-03 07:18:40'),
(287, 'Customer', 6, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:18:59', '2026-09-03 07:18:59'),
(288, 'Loan', 6, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:18:59', '2026-09-03 07:18:59'),
(289, 'Customer', 5, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:19:43', '2026-09-03 07:19:43'),
(290, 'Loan', 5, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:19:43', '2026-09-03 07:19:43'),
(291, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 07:20:57', '2026-09-03 07:20:57'),
(292, 'Loan', 4, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 07:20:57', '2026-09-03 07:20:57'),
(293, 'Customer', 8, 'NOMINEE_ADD', NULL, '{\"id\":\"5\",\"customer_id\":\"8\",\"name\":\"Nagaraj\",\"relation\":\"Husband\",\"mobile\":null,\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-09-03 08:00:06\",\"updated_at\":\"2026-09-03 08:00:06\"}', 1, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(294, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:00:19', '2026-09-03 08:00:19'),
(295, 'Loan', 8, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:00:19', '2026-09-03 08:00:19'),
(296, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:08:13', '2026-09-03 08:08:13'),
(297, 'Loan', 8, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:08:13', '2026-09-03 08:08:13'),
(298, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:09:25', '2026-09-03 08:09:25'),
(299, 'Loan', 8, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:09:25', '2026-09-03 08:09:25'),
(300, 'Loan', 8, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"23\",\"loan_id\":\"8\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/58f03b6d7353fb6b01de66decfed9e29.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:11:53\",\"updated_at\":\"2026-09-03 08:11:53\"}', 1, '2026-09-03 08:11:53', '2026-09-03 08:11:53'),
(301, 'Loan', 8, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"24\",\"loan_id\":\"8\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/601551150a9890c420f61b77e2c0e66b.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:12:11\",\"updated_at\":\"2026-09-03 08:12:11\"}', 1, '2026-09-03 08:12:11', '2026-09-03 08:12:11'),
(302, 'Loan', 8, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":8,\"mode\":\"CASH\",\"amount\":\"309606.35\"}', 1, '2026-09-03 08:12:39', '2026-09-03 08:12:39'),
(303, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:14:24', '2026-09-03 08:14:24'),
(304, 'Loan', 8, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:14:24', '2026-09-03 08:14:24'),
(305, 'Customer', 1, 'NOMINEE_ADD', NULL, '{\"id\":\"6\",\"customer_id\":\"1\",\"name\":\"Renuga\",\"relation\":\"Husband\",\"mobile\":null,\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-09-03 08:21:09\",\"updated_at\":\"2026-09-03 08:21:09\"}', 1, '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(306, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:21:18', '2026-09-03 08:21:18'),
(307, 'Loan', 9, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"25\",\"loan_id\":\"9\",\"document_type\":\"JEWELLERY_PHOTO\",\"file_ref\":\"loan-documents\\/6617b0add93539352d729de3c7d993d9.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:21:52\",\"updated_at\":\"2026-09-03 08:21:52\"}', 1, '2026-09-03 08:21:52', '2026-09-03 08:21:52'),
(308, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:21:59', '2026-09-03 08:21:59'),
(309, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:21:59', '2026-09-03 08:21:59'),
(310, 'Loan', 9, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"26\",\"loan_id\":\"9\",\"document_type\":\"JEWELLERY_PHOTO\",\"file_ref\":\"loan-documents\\/d1be441b25a47794dc37c3a93348d089.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:22:36\",\"updated_at\":\"2026-09-03 08:22:36\"}', 1, '2026-09-03 08:22:36', '2026-09-03 08:22:36'),
(311, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:22:46', '2026-09-03 08:22:46'),
(312, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:23:05', '2026-09-03 08:23:05'),
(313, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:23:09', '2026-09-03 08:23:09'),
(314, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:23:13', '2026-09-03 08:23:13'),
(315, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:23:33', '2026-09-03 08:23:33'),
(316, 'Loan', 9, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:23:33', '2026-09-03 08:23:33'),
(317, 'Loan', 9, 'CANCEL', '{\"status\":\"APPROVED\"}', '{\"status\":\"CANCELLED\",\"remarks\":\"jewel Photo wrongly taken\"}', 1, '2026-09-03 08:24:12', '2026-09-03 08:24:12'),
(318, 'Customer', 1, 'NOMINEE_ADD', NULL, '{\"id\":\"7\",\"customer_id\":\"1\",\"name\":\"Renuga\",\"relation\":\"Husband\",\"mobile\":null,\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-09-03 08:28:05\",\"updated_at\":\"2026-09-03 08:28:05\"}', 1, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(319, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:28:27', '2026-09-03 08:28:27'),
(320, 'Loan', 10, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:28:28', '2026-09-03 08:28:28'),
(321, 'Loan', 10, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"28\",\"loan_id\":\"10\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/ccb5d243dd4b67447b9abbdecfe07841.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:31:46\",\"updated_at\":\"2026-09-03 08:31:46\"}', 1, '2026-09-03 08:31:46', '2026-09-03 08:31:46'),
(322, 'Loan', 10, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"29\",\"loan_id\":\"10\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/11501a36cfa9acb975cd68534952ae39.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 08:31:57\",\"updated_at\":\"2026-09-03 08:31:57\"}', 1, '2026-09-03 08:31:57', '2026-09-03 08:31:57'),
(323, 'Loan', 10, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":9,\"mode\":\"CASH\",\"amount\":\"22271.68\"}', 1, '2026-09-03 08:32:25', '2026-09-03 08:32:25'),
(324, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:33:28', '2026-09-03 08:33:28'),
(325, 'Loan', 10, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:33:28', '2026-09-03 08:33:28'),
(326, 'Customer', 9, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 08:58:54', '2026-09-03 08:58:54'),
(327, 'Loan', 11, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 08:58:54', '2026-09-03 08:58:54'),
(328, 'Loan', 11, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"31\",\"loan_id\":\"11\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/23e6f9306a1293d4a9120857f3ad6f59.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:00:10\",\"updated_at\":\"2026-09-03 09:00:10\"}', 1, '2026-09-03 09:00:10', '2026-09-03 09:00:10'),
(329, 'Loan', 11, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"32\",\"loan_id\":\"11\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/a07fc9febac18a017ac591aaf31fbf93.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:00:24\",\"updated_at\":\"2026-09-03 09:00:24\"}', 1, '2026-09-03 09:00:24', '2026-09-03 09:00:24'),
(330, 'Loan', 11, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":10,\"mode\":\"CASH\",\"amount\":\"415473.25\"}', 1, '2026-09-03 09:00:49', '2026-09-03 09:00:49'),
(331, 'Customer', 9, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:01:10', '2026-09-03 09:01:10'),
(332, 'Loan', 11, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:01:10', '2026-09-03 09:01:10'),
(333, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:10:05', '2026-09-03 09:10:05'),
(334, 'Loan', 12, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:10:05', '2026-09-03 09:10:05'),
(335, 'Loan', 12, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"34\",\"loan_id\":\"12\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/78566383585193dc44c86f1f09a05abf.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:10:51\",\"updated_at\":\"2026-09-03 09:10:51\"}', 1, '2026-09-03 09:10:51', '2026-09-03 09:10:51'),
(336, 'Loan', 12, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"35\",\"loan_id\":\"12\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/00cecc6a0ae9f50dff87816b944e0a95.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:11:10\",\"updated_at\":\"2026-09-03 09:11:10\"}', 1, '2026-09-03 09:11:10', '2026-09-03 09:11:10'),
(337, 'Loan', 12, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":11,\"mode\":\"CASH\",\"amount\":\"126148.49\"}', 1, '2026-09-03 09:12:07', '2026-09-03 09:12:07'),
(338, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:12:32', '2026-09-03 09:12:32'),
(339, 'Loan', 12, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:12:32', '2026-09-03 09:12:32'),
(340, 'Customer', 8, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:12:39', '2026-09-03 09:12:39'),
(341, 'Loan', 12, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:12:39', '2026-09-03 09:12:39'),
(342, 'Loan', 13, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:30:20', '2026-09-03 09:30:20'),
(343, 'Customer', 10, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:30:20', '2026-09-03 09:30:20'),
(344, 'Loan', 13, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"37\",\"loan_id\":\"13\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/f56e96161feb8685ee0d925b841ce148.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:31:57\",\"updated_at\":\"2026-09-03 09:31:57\"}', 1, '2026-09-03 09:31:57', '2026-09-03 09:31:57'),
(345, 'Loan', 13, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"38\",\"loan_id\":\"13\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/04c7b10f177c9b2f4dd7b0d9bd5cbda2.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 09:32:18\",\"updated_at\":\"2026-09-03 09:32:18\"}', 1, '2026-09-03 09:32:18', '2026-09-03 09:32:18'),
(346, 'Loan', 13, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":12,\"mode\":\"CASH\",\"amount\":\"42844.78\"}', 1, '2026-09-03 09:34:08', '2026-09-03 09:34:08'),
(347, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:51:55', '2026-09-03 09:51:55'),
(348, 'Loan', 14, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:51:55', '2026-09-03 09:51:55'),
(349, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:52:15', '2026-09-03 09:52:15'),
(350, 'Loan', 14, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:52:15', '2026-09-03 09:52:15'),
(351, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:56:55', '2026-09-03 09:56:55'),
(352, 'Loan', 14, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:56:55', '2026-09-03 09:56:55'),
(353, 'Customer', 4, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 09:58:59', '2026-09-03 09:58:59'),
(354, 'Loan', 14, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 09:58:59', '2026-09-03 09:58:59'),
(355, 'Loan', 14, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"40\",\"loan_id\":\"14\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/d35d8cd09e846ab06648ee988c575114.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 10:03:50\",\"updated_at\":\"2026-09-03 10:03:50\"}', 1, '2026-09-03 10:03:50', '2026-09-03 10:03:50'),
(356, 'Loan', 14, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"41\",\"loan_id\":\"14\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/51b29ac5649dc394b6aba87b2f512151.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 10:04:00\",\"updated_at\":\"2026-09-03 10:04:00\"}', 1, '2026-09-03 10:04:00', '2026-09-03 10:04:00'),
(357, 'Loan', 14, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":13,\"mode\":\"CASH\",\"amount\":\"33059.93\"}', 1, '2026-09-03 10:04:16', '2026-09-03 10:04:16'),
(358, 'Customer', 1, 'NOMINEE_ADD', NULL, '{\"id\":\"8\",\"customer_id\":\"1\",\"name\":\"Renuga\",\"relation\":\"Husband\",\"mobile\":null,\"id_proof_type\":null,\"id_proof_number\":null,\"created_at\":\"2026-09-03 10:17:43\",\"updated_at\":\"2026-09-03 10:17:43\"}', 1, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(359, 'Loan', 15, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"42\",\"loan_id\":\"15\",\"document_type\":\"JEWELLERY_PHOTO\",\"file_ref\":\"loan-documents\\/e86ecfbb18f2f01becef362910e9af36.jpg\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 10:20:53\",\"updated_at\":\"2026-09-03 10:20:53\"}', 1, '2026-09-03 10:20:53', '2026-09-03 10:20:53'),
(360, 'Loan', 15, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 10:21:05', '2026-09-03 10:21:05'),
(361, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 10:21:05', '2026-09-03 10:21:05'),
(362, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 10:24:43', '2026-09-03 10:24:43'),
(363, 'Loan', 15, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 10:24:43', '2026-09-03 10:24:43'),
(364, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 10:26:19', '2026-09-03 10:26:19'),
(365, 'Loan', 15, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 10:26:19', '2026-09-03 10:26:19'),
(366, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 10:59:42', '2026-09-03 10:59:42'),
(367, 'Loan', 15, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 10:59:42', '2026-09-03 10:59:42'),
(368, 'Loan', 15, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"43\",\"loan_id\":\"15\",\"document_type\":\"SANCTION_LETTER\",\"file_ref\":\"loan-documents\\/9cb163d5a6ee62dd1876dc68d1476469.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 11:05:47\",\"updated_at\":\"2026-09-03 11:05:47\"}', 1, '2026-09-03 11:05:47', '2026-09-03 11:05:47'),
(369, 'Loan', 15, 'LOAN_DOCUMENT_UPLOAD', NULL, '{\"id\":\"44\",\"loan_id\":\"15\",\"document_type\":\"AGREEMENT\",\"file_ref\":\"loan-documents\\/f10ebdeb469fd122a17d97137a42e7d5.pdf\",\"uploaded_by\":\"1\",\"created_at\":\"2026-09-03 11:06:33\",\"updated_at\":\"2026-09-03 11:06:33\"}', 1, '2026-09-03 11:06:33', '2026-09-03 11:06:33'),
(370, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 11:19:06', '2026-09-03 11:19:06'),
(371, 'Loan', 15, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 11:19:06', '2026-09-03 11:19:06'),
(372, 'Loan', 15, 'DISBURSE', '{\"status\":\"APPROVED\"}', '{\"status\":\"ACTIVE\",\"disbursement_id\":14,\"mode\":\"CASH\",\"amount\":\"445415.19\"}', 1, '2026-09-03 11:19:26', '2026-09-03 11:19:26'),
(373, 'Customer', 1, 'CUSTOMER_PHOTO_VIEW', NULL, NULL, 1, '2026-09-03 11:22:19', '2026-09-03 11:22:19'),
(374, 'Loan', 10, 'LOAN_DOCUMENT_VIEW', NULL, NULL, 1, '2026-09-03 11:22:19', '2026-09-03 11:22:19'),
(375, 'Loan', 11, 'INTEREST_COLLECT', NULL, '{\"collection_id\":2,\"amount\":\"840\",\"mode\":\"CASH\"}', 1, '2026-09-03 12:12:56', '2026-09-03 12:12:56'),
(376, 'Loan', 11, 'PART_PAYMENT', '{\"status\":\"ACTIVE\",\"sanctioned_amount\":\"416000.00\"}', '{\"status\":\"PART_PAID\",\"sanctioned_amount\":\"415840.00\",\"payment_id\":3}', 1, '2026-09-03 12:17:52', '2026-09-03 12:17:52');

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
(1, 'AFH001-BR001', 'H001', 'Aurum Gold Loan - Batlagundu Main', 'Batlagundu', 'Tamil Nadu', NULL, NULL, NULL, 1, '2026-08-07 16:14:20', '2026-09-02 08:50:26'),
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
(1, 'PROC_FEE_STD', 'Standard Processing Fee', 'FLAT', 100.00, '2026-08-07 16:14:20'),
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
  `father_name` varchar(150) DEFAULT NULL,
  `profession_type` varchar(30) DEFAULT NULL,
  `profession_details` varchar(255) DEFAULT NULL,
  `income` decimal(12,2) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
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

INSERT INTO `customers` (`id`, `customer_code`, `name`, `mobile`, `email`, `dob`, `gender`, `aadhaar_last4`, `aadhaar_hash`, `pan_number`, `father_name`, `profession_type`, `profession_details`, `income`, `photo_path`, `branch_id`, `registered_by`, `kyc_status`, `is_blacklisted`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CUST00000001', 'Renuga P', '8111075554', 'renuga8111@gmail.com', '1974-01-01', 'FEMALE', '2928', 'adc25a5bc56b66744f7e1dd625db0cb85d7cbd18a0654a9e02886bd5ce54271f', NULL, 'Marimuthu', 'AGRICULTURE', 'House Wife', 10000.00, 'customer-photos/74924fd018706c7cdff12ed80d4ba242.jpg', 1, 3, 'PENDING', 0, '2026-09-02 06:12:37', '2026-09-02 06:52:07', NULL),
(2, 'CUST00000002', 'Rasathi V', '9344196536', 'rasathi9344@gmail.com', '1986-05-18', 'FEMALE', NULL, NULL, NULL, 'Udhayasuriyan', 'SALARIED', 'Gorments', 20000.00, 'customer-photos/7a70b2fb932266f75e70926538572035.jpg', 1, 1, 'PENDING', 0, '2026-09-02 07:25:27', '2026-09-02 07:25:27', NULL),
(3, 'CUST00000003', 'Vijayalakshmi G', '9629702010', 'vijay1986@gmail.com', '1986-01-01', 'FEMALE', NULL, NULL, NULL, 'Gurusamy', 'SELF_EMPLOYED', 'teilor', 22000.00, 'customer-photos/50107ac668f1bf408807d753635254da.jpg', 1, 1, 'PENDING', 0, '2026-09-02 07:55:58', '2026-09-02 07:55:58', NULL),
(4, 'CUST00000004', 'Jothika S', '7806947234', 'jothikas292@gmail.com', '2001-08-21', 'FEMALE', NULL, NULL, NULL, 'Sundharavel', 'SALARIED', 'employee', 20000.00, 'customer-photos/ebc03d87ffb677cb47f090aa41a5a5a9.jpg', 1, 1, 'PENDING', 0, '2026-09-02 08:38:04', '2026-09-02 08:38:04', NULL),
(5, 'CUST00000005', 'Gurusamy G', '7639016533', 'guru1968@gmail.com', '1968-04-05', 'FEMALE', NULL, NULL, NULL, 'Gurusamy', 'AGRICULTURE', 'Agriculture', 10000.00, 'customer-photos/7fdd5e8dd72397c6ab925586f7fe0eb2.jpg', 1, 1, 'PENDING', 0, '2026-09-02 09:02:32', '2026-09-02 09:02:32', NULL),
(6, 'CUST00000006', 'Vasanth B', '8838983347', 'voiletvasanth@gmail.com', '2003-07-21', 'MALE', NULL, NULL, NULL, 'Balamurugan', 'SALARIED', 'employee', 12500.00, 'customer-photos/2e891bde1dc15c0dc07dbc592a302889.jpg', 1, 1, 'PENDING', 0, '2026-09-02 11:12:54', '2026-09-02 11:13:29', NULL),
(7, 'CUST00000007', 'Swathi K', '8825477698', 'swathi2000@gmail.com', '2000-05-03', 'FEMALE', NULL, NULL, NULL, NULL, 'AGRICULTURE', 'Agriculture', 10000.00, 'customer-photos/b61b00c76d53a5e1f13507dd37855956.jpg', 1, 1, 'PENDING', 0, '2026-09-03 07:05:27', '2026-09-03 07:05:27', NULL),
(8, 'CUST00000008', 'Bavani N', '8148266951', 'bavani1990@gmail.com', '1990-01-01', 'FEMALE', NULL, NULL, NULL, NULL, 'AGRICULTURE', 'Agriculture', 310000.00, 'customer-photos/95057d66ead6f12205bac6440c9b16c0.jpg', 1, 1, 'PENDING', 0, '2026-09-03 08:00:06', '2026-09-03 08:00:06', NULL),
(9, 'CUST00000009', 'Muniyandi M', '9786693739', 'muni1965@gmail.com', '1965-01-01', 'MALE', NULL, NULL, NULL, 'Muthaiya', 'BUSINESS', 'Fast Food Shop', 20000.00, 'customer-photos/1eed4d1d45a5148247096559c585ea89.jpg', 1, 1, 'PENDING', 0, '2026-09-03 08:58:48', '2026-09-03 08:58:48', NULL),
(10, 'CUST00000010', 'Chellappandi G', '6380092809', 'chellam1982@gmail.com', '1982-06-05', 'MALE', NULL, NULL, NULL, 'Gurusamy', 'SALARIED', 'School Bus Driver', 12000.00, 'customer-photos/a7b5980164725ec500ec7244fcba8433.jpg', 1, 1, 'PENDING', 0, '2026-09-03 09:30:15', '2026-09-03 09:30:15', NULL);

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
(1, 1, 'CURRENT', 'W/o Perumal, Sivan Kovil Street, Batlagundu.', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 2, 'CURRENT', '1-16-25 A/4, Kalidhasan street, Gandhi Nagar, Batlagundu.', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(3, 3, 'CURRENT', 'W/o Gurusamy, Vinoba Nagar, Genguvarpatty, G. Kallupatty', NULL, 'Theni', 'Tamilnadu', '625203', '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(4, 4, 'CURRENT', '9-1-17D, Ambethkar Nagar, Pattiveeranpatty', NULL, 'Dindigul', 'Tamilnadu', '624211', '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(5, 5, 'CURRENT', '120, East Street, Viralipatty.', NULL, 'dindigul', 'Tamilnadu', '624202', '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(6, 6, 'CURRENT', '6/204B, North Street, Kulathupatty, Nuthalapuram, Nilakottai', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(7, 7, 'CURRENT', 'W/O Kumar, AD colony, Nilokottai.', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(8, 8, 'CURRENT', 'W/O Nagaraj, 218,Nilakottai,', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(9, 9, 'CURRENT', 'S/O Muthaiya, Arunachalapuram, Batlagundu', NULL, 'Dindigul', 'Tamilnadu', '624202', '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(10, 10, 'CURRENT', '3/120, East Street, Viralipatti', NULL, 'dindigul', 'Tamilnadu', '624202', '2026-09-03 09:30:15', '2026-09-03 09:30:15');

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
(1, 1, 'Perumal', 'Husband', '8111075554', 'Aadhar Card', '788340412928', '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 2, 'Vijayaraman', 'Husband', '9344196536', 'Aadhar', '207843879017', '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(3, 3, 'Vignesh', 'Son', '9629702010', 'Aadhar', '421150428689', '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(4, 7, 'Kumar', 'Husband', '8825477698', NULL, NULL, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(5, 8, 'Nagaraj', 'Husband', NULL, NULL, NULL, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(6, 1, 'Renuga', 'Husband', NULL, NULL, NULL, '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(7, 1, 'Renuga', 'Husband', NULL, NULL, NULL, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(8, 1, 'Renuga', 'Husband', NULL, NULL, NULL, '2026-09-03 10:17:43', '2026-09-03 10:17:43');

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
(1, '1000', 'Cash in Hand', 'ASSET', '2026-08-07 16:14:20', '2026-09-02 08:50:49'),
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

-- --------------------------------------------------------

--
-- Table structure for table `gold_rates`
--

CREATE TABLE `gold_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rate_per_gram` decimal(10,2) NOT NULL,
  `ltv_pct` decimal(5,2) NOT NULL DEFAULT 75.00,
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

INSERT INTO `gold_rates` (`id`, `rate_per_gram`, `ltv_pct`, `karat`, `effective_date`, `status`, `proposed_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(6, 11350.00, 80.00, '22k', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 06:49:28', '2026-08-28 06:49:13', '2026-08-28 06:49:28'),
(7, 10613.00, 75.00, '21K', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 06:51:33', '2026-08-28 06:51:26', '2026-08-28 06:51:33'),
(8, 9905.00, 70.00, '20K', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 06:52:12', '2026-08-28 06:52:05', '2026-08-28 06:52:12'),
(9, 14150.00, 80.00, '22k', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 16:29:03', '2026-08-28 16:28:54', '2026-08-28 16:29:03'),
(10, 14150.00, 75.00, '21K', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 16:31:26', '2026-08-28 16:31:19', '2026-08-28 16:31:26'),
(11, 14150.00, 70.00, '20', '2026-08-28', 'APPROVED', 3, 3, '2026-08-28 16:32:47', '2026-08-28 16:32:39', '2026-08-28 16:32:47'),
(12, 15000.00, 80.00, '22k', '2026-09-02', 'APPROVED', 1, 1, '2026-09-02 07:50:36', '2026-09-02 07:47:12', '2026-09-02 07:50:36');

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
(1, 4, 4, 1, 1, 1, 1, 'Jothika S', 'PENDING', NULL, '2026-09-02 08:47:51', '2026-09-02 08:47:57');

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
  `idempotency_key` varchar(64) DEFAULT NULL,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interest_collections`
--

INSERT INTO `interest_collections` (`id`, `loan_id`, `amount`, `mode`, `receipt_number`, `idempotency_key`, `collected_by`, `created_at`, `updated_at`) VALUES
(1, 4, 765.00, 'CASH', 'RCPTABHY4AGN5Q', NULL, 1, '2026-09-02 08:48:57', '2026-09-02 08:48:57'),
(2, 11, 840.00, 'CASH', 'RCPT8VFQJ0AKP8', NULL, 1, '2026-09-03 12:12:56', '2026-09-03 12:12:56');

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
(1, 4, 'jewellery-images/59f36a3e7be8fa8e7ce946c62c579c3c.jpg', '2026-09-02 08:45:15', '2026-09-02 08:45:15');

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
(1, 'JWL0APC6B52QG', 1, 1, 1, 24.100, 0.600, '22k', 9, 14150.00, 80.00, 266020.00, 3, 'PLEDGED', 1, '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 'JWL82KTPT3QBI', 2, 5, 0, 6.450, 0.010, '22k', 9, 14150.00, 80.00, 72900.80, 1, 'PLEDGED', 2, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(3, 'JWLNLSAD6PT8H', 3, 1, 0, 5.800, 0.200, '22k', 12, 15000.00, 80.00, 67200.00, 1, 'PLEDGED', 3, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(4, 'JWLSY4BC0PRUQ', 4, 2, 0, 8.000, 1.000, '22k', 12, 15000.00, 80.00, 84000.00, 1, 'PLEDGED', 4, '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(5, 'JWLW198G2VC5S', 5, 5, 0, 8.300, 3.588, '21K', 10, 14150.00, 75.00, 50006.10, 1, 'PLEDGED', 5, '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(6, 'JWL57KX1MHGKA', 6, 5, 0, 2.200, 1.100, '21K', 10, 14150.00, 75.00, 11673.75, 1, 'PLEDGED', 6, '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(7, 'JWL8Z16PNUMJR', 7, 1, 0, 16.000, 0.200, '22k', 12, 15000.00, 80.00, 189600.00, 1, 'PLEDGED', 7, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(8, 'JWLS3QOX02ZKO', 8, 1, 0, 16.000, 0.100, '22k', 12, 15000.00, 80.00, 190800.00, 1, 'PLEDGED', 8, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(9, 'JWLMBIFAKJ0G6', 8, 1, 0, 8.000, 0.100, '22k', 12, 15000.00, 80.00, 94800.00, 1, 'PLEDGED', 8, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(10, 'JWL82CNYR38K8', 8, 5, 0, 4.100, 0.100, '22k', 12, 15000.00, 80.00, 48000.00, 1, 'PLEDGED', 8, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(11, 'JWLJ1XRP6RO0O', 1, 5, 0, 2.100, 0.100, '22k', 12, 15000.00, 80.00, 24000.00, 1, 'EVALUATED', NULL, '2026-09-03 08:21:09', '2026-09-03 08:24:12'),
(12, 'JWLN7Q7RUCSHW', 1, 5, 0, 2.100, 0.100, '22k', 12, 15000.00, 80.00, 24000.00, 1, 'PLEDGED', 10, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(13, 'JWLTB63FM5VGW', 9, 4, 0, 37.600, 0.400, '22k', 12, 15000.00, 80.00, 446400.00, 1, 'PLEDGED', 11, '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(14, 'JWL57A8U3NTKJ', 8, 2, 0, 10.800, 0.100, '22k', 12, 15000.00, 80.00, 128400.00, 1, 'PLEDGED', 12, '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(15, 'JWLRI9QTBKT1I', 10, 2, 0, 3.900, 0.000, '22k', 12, 15000.00, 80.00, 46800.00, 1, 'PLEDGED', 13, '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(16, 'JWLW58F030X77', 4, 5, 0, 3.400, 0.200, '21K', 10, 14150.00, 75.00, 33960.00, 1, 'PLEDGED', 14, '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(17, 'JWL7NN191YIB1', 1, 1, 0, 8.100, 0.100, '22k', 12, 15000.00, 80.00, 96000.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(18, 'JWLGYRJHAS8IT', 1, 1, 0, 8.000, 0.100, '22k', 12, 15000.00, 80.00, 94800.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(19, 'JWLCJUQGP4DK7', 1, 2, 0, 3.900, 0.100, '22k', 12, 15000.00, 80.00, 45600.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(20, 'JWL11UDSG6P53', 1, 2, 0, 3.900, 0.100, '22k', 12, 15000.00, 80.00, 45600.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(21, 'JWL7UWPCK0E25', 1, 5, 0, 3.000, 0.100, '22k', 12, 15000.00, 80.00, 34800.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(22, 'JWL46ANHUT9KX', 1, 8, 0, 1.800, 0.000, '22k', 12, 15000.00, 80.00, 21600.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(23, 'JWLAAFCBW4P87', 1, 7, 0, 13.100, 0.000, '22k', 12, 15000.00, 80.00, 157200.00, 1, 'PLEDGED', 15, '2026-09-03 10:17:43', '2026-09-03 10:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `jewellery_valuation_history`
--

CREATE TABLE `jewellery_valuation_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jewellery_item_id` bigint(20) UNSIGNED NOT NULL,
  `gold_rate_id` bigint(20) UNSIGNED NOT NULL,
  `gross_weight` decimal(8,3) NOT NULL,
  `stone_weight` decimal(8,3) NOT NULL DEFAULT 0.000,
  `applied_rate` decimal(10,2) NOT NULL,
  `eligible_percentage` decimal(5,2) NOT NULL,
  `eligible_amount` decimal(12,2) NOT NULL,
  `evaluated_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jewellery_valuation_history`
--

INSERT INTO `jewellery_valuation_history` (`id`, `jewellery_item_id`, `gold_rate_id`, `gross_weight`, `stone_weight`, `applied_rate`, `eligible_percentage`, `eligible_amount`, `evaluated_by`, `created_at`) VALUES
(1, 1, 9, 24.100, 0.600, 14150.00, 80.00, 266020.00, 3, '2026-09-02 06:12:37'),
(2, 2, 9, 6.450, 0.010, 14150.00, 80.00, 72900.80, 1, '2026-09-02 07:25:27'),
(3, 3, 12, 5.800, 0.200, 15000.00, 80.00, 67200.00, 1, '2026-09-02 07:55:58'),
(4, 4, 12, 8.000, 1.000, 15000.00, 80.00, 84000.00, 1, '2026-09-02 08:38:04'),
(5, 5, 10, 8.300, 3.588, 14150.00, 75.00, 50006.10, 1, '2026-09-02 09:02:32'),
(6, 6, 10, 2.200, 1.100, 14150.00, 75.00, 11673.75, 1, '2026-09-02 11:12:54'),
(7, 7, 12, 16.000, 0.200, 15000.00, 80.00, 189600.00, 1, '2026-09-03 07:05:27'),
(8, 8, 12, 16.000, 0.100, 15000.00, 80.00, 190800.00, 1, '2026-09-03 08:00:06'),
(9, 9, 12, 8.000, 0.100, 15000.00, 80.00, 94800.00, 1, '2026-09-03 08:00:06'),
(10, 10, 12, 4.100, 0.100, 15000.00, 80.00, 48000.00, 1, '2026-09-03 08:00:06'),
(11, 11, 12, 2.100, 0.100, 15000.00, 80.00, 24000.00, 1, '2026-09-03 08:21:09'),
(12, 12, 12, 2.100, 0.100, 15000.00, 80.00, 24000.00, 1, '2026-09-03 08:28:05'),
(13, 13, 12, 37.600, 0.400, 15000.00, 80.00, 446400.00, 1, '2026-09-03 08:58:48'),
(14, 14, 12, 10.800, 0.100, 15000.00, 80.00, 128400.00, 1, '2026-09-03 09:09:59'),
(15, 15, 12, 3.900, 0.000, 15000.00, 80.00, 46800.00, 1, '2026-09-03 09:30:15'),
(16, 16, 10, 3.400, 0.200, 14150.00, 75.00, 33960.00, 1, '2026-09-03 09:50:11'),
(17, 17, 12, 8.100, 0.100, 15000.00, 80.00, 96000.00, 1, '2026-09-03 10:17:43'),
(18, 18, 12, 8.000, 0.100, 15000.00, 80.00, 94800.00, 1, '2026-09-03 10:17:43'),
(19, 19, 12, 3.900, 0.100, 15000.00, 80.00, 45600.00, 1, '2026-09-03 10:17:43'),
(20, 20, 12, 3.900, 0.100, 15000.00, 80.00, 45600.00, 1, '2026-09-03 10:17:43'),
(21, 21, 12, 3.000, 0.100, 15000.00, 80.00, 34800.00, 1, '2026-09-03 10:17:43'),
(22, 22, 12, 1.800, 0.000, 15000.00, 80.00, 21600.00, 1, '2026-09-03 10:17:43'),
(23, 23, 12, 13.100, 0.000, 15000.00, 80.00, 157200.00, 1, '2026-09-03 10:17:43');

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
(4, 1, 'QR', NULL, 1, '2026-09-02 06:52:07', '2026-09-02 06:52:07', '2026-09-02 06:52:07');

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
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_document_master`
--

INSERT INTO `kyc_document_master` (`id`, `customer_id`, `document_type_id`, `file_ref`, `status`, `verified_by`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(2, 1, 6, 'kyc-documents/c9569a458dea7c972e050e69fc54c280.jpg', 'REJECTED', 3, 'wrongly uploaded', '2026-09-02 06:36:00', '2026-09-02 06:41:43'),
(3, 1, 6, 'kyc-documents/1882601719d6aaadacba88607b9cfcae.jpg', 'VERIFIED', 3, NULL, '2026-09-02 06:41:13', '2026-09-02 06:43:50'),
(4, 1, 6, 'kyc-documents/1fd7e146c37085a24f468c11f9114857.jpg', 'VERIFIED', 3, NULL, '2026-09-02 06:43:34', '2026-09-02 06:43:37'),
(5, 1, 7, 'kyc-documents/f8b1c7601294ca5c80a48c9cb69a2be9.jpg', 'VERIFIED', 3, NULL, '2026-09-02 06:58:10', '2026-09-02 06:58:27');

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
(5, 'BANK_PASSBOOK', 'Bank Passbook', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(6, 'AADHAAR', 'Aadhaar', '2026-08-07 16:14:20', '0000-00-00 00:00:00'),
(7, 'PAN', 'PAN', '2026-08-07 16:14:20', '0000-00-00 00:00:00');

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
(2, 1, 'FARPP8126E', 1, 1, '2026-09-02 06:59:13', '2026-09-02 06:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_account_number` varchar(30) DEFAULT NULL,
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
(1, 'AF001000000001', 1, 1, 3, 266020.00, 266000.00, 22.00, 266.02, 47.88, 0.00, 265686.10, '2026-09-02', '2027-03-02', 'ACTIVE', 3, '2026-09-02 06:12:37', '2026-09-02 06:30:30'),
(2, 'AF001000000002', 2, 1, 3, 72900.80, 72800.00, 22.00, 72.90, 13.12, 0.00, 72713.98, '2026-09-02', '2027-03-02', 'ACTIVE', 1, '2026-09-02 07:25:27', '2026-09-02 07:31:22'),
(3, 'AF001000000003', 3, 1, 3, 67200.00, 67000.00, 22.00, 67.20, 12.10, 0.00, 66920.70, '2026-09-02', '2027-03-02', 'ACTIVE', 1, '2026-09-02 07:55:58', '2026-09-02 08:08:27'),
(4, 'AF001000000004', 4, 1, 3, 84000.00, 84000.00, 22.00, 84.00, 15.12, 0.00, 83900.88, '2026-09-02', '2027-03-02', 'SETTLED', 1, '2026-09-02 08:38:04', '2026-09-02 08:47:11'),
(5, 'AF001000000005', 5, 1, 3, 50006.10, 50000.00, 22.00, 50.01, 9.00, 0.00, 49940.99, '2026-09-02', '2027-03-02', 'ACTIVE', 1, '2026-09-02 09:02:32', '2026-09-02 09:04:41'),
(6, 'AF001000000006', 6, 1, 3, 11673.75, 11000.00, 22.00, 11.67, 2.10, 0.00, 10986.23, '2026-09-02', '2027-03-02', 'ACTIVE', 1, '2026-09-02 11:12:54', '2026-09-02 14:47:51'),
(7, 'AF001000000007', 7, 1, 3, 189600.00, 173500.00, 22.00, 189.60, 34.13, 0.00, 173276.27, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 07:05:27', '2026-09-03 07:14:05'),
(8, 'AF001000000008', 8, 1, 1, 333600.00, 310000.00, 18.00, 333.60, 60.05, 0.00, 309606.35, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 08:00:06', '2026-09-03 08:12:39'),
(9, NULL, 1, 1, 3, 24000.00, 22300.00, 22.00, 24.00, 4.32, 0.00, 22271.68, '2026-09-03', '2027-03-03', 'CANCELLED', 1, '2026-09-03 08:21:09', '2026-09-03 08:24:12'),
(10, 'AF001000000010', 1, 1, 3, 24000.00, 22300.00, 22.00, 24.00, 4.32, 0.00, 22271.68, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 08:28:05', '2026-09-03 08:32:25'),
(11, 'AF001000000011', 9, 1, 1, 446400.00, 415840.00, 18.00, 446.40, 80.35, 0.00, 415473.25, '2026-09-03', '2027-03-03', 'PART_PAID', 1, '2026-09-03 08:58:48', '2026-09-03 12:17:52'),
(12, 'AF001000000012', 8, 1, 1, 128400.00, 126300.00, 18.00, 128.40, 23.11, 0.00, 126148.49, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 09:09:59', '2026-09-03 09:12:07'),
(13, 'AF001000000013', 10, 1, 3, 46800.00, 42900.00, 22.00, 46.80, 8.42, 0.00, 42844.78, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 09:30:15', '2026-09-03 09:34:08'),
(14, 'AF001000000014', 4, 1, 1, 33960.00, 33100.00, 18.00, 33.96, 6.11, 0.00, 33059.93, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 09:50:11', '2026-09-03 10:04:16'),
(15, 'AF001000000015', 1, 1, 1, 495600.00, 446000.00, 18.00, 495.60, 89.21, 0.00, 445415.19, '2026-09-03', '2027-03-03', 'ACTIVE', 1, '2026-09-03 10:17:43', '2026-09-03 11:19:26');

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
(1, 5, 20000000.00, '2026-08-07 16:14:20'),
(2, 6, 10000000.00, '2026-08-07 16:14:20'),
(3, 10, 10000000.00, '2026-08-28 18:42:26');

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
(1, 1, 'ADMIN_DIRECT', 'APPROVE', 3, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 2, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(3, 3, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(4, 4, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(5, 5, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(6, 6, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(7, 7, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(8, 8, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(9, 9, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(10, 9, 'ADMIN_DIRECT', 'CANCEL', 1, 'jewel Photo wrongly taken', '2026-09-03 08:24:12', '2026-09-03 08:24:12'),
(11, 10, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(12, 11, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(13, 12, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(14, 13, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(15, 14, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(16, 15, 'ADMIN_DIRECT', 'APPROVE', 1, 'Created and approved directly by admin — maker-checker workflow bypassed.', '2026-09-03 10:17:43', '2026-09-03 10:17:43');

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
(1, 1, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 2, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(3, 3, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(4, 4, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(5, 5, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(6, 6, 'ADMIN_DIRECT', 'APPROVED', '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(7, 7, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(8, 8, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(9, 9, 'ADMIN_DIRECT', 'CANCELLED', '2026-09-03 08:21:09', '2026-09-03 08:24:12'),
(10, 10, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(11, 11, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(12, 12, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(13, 13, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(14, 14, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(15, 15, 'ADMIN_DIRECT', 'APPROVED', '2026-09-03 10:17:43', '2026-09-03 10:17:43');

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
(1, 1, 'PROCESSING_FEE', 266.02, '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(2, 1, 'GST', 47.88, '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(3, 1, 'INSURANCE', 0.00, '2026-09-02 06:12:37', '2026-09-02 06:12:37'),
(4, 2, 'PROCESSING_FEE', 72.90, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(5, 2, 'GST', 13.12, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(6, 2, 'INSURANCE', 0.00, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(7, 3, 'PROCESSING_FEE', 67.20, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(8, 3, 'GST', 12.10, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(9, 3, 'INSURANCE', 0.00, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(10, 4, 'PROCESSING_FEE', 84.00, '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(11, 4, 'GST', 15.12, '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(12, 4, 'INSURANCE', 0.00, '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(13, 5, 'PROCESSING_FEE', 50.01, '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(14, 5, 'GST', 9.00, '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(15, 5, 'INSURANCE', 0.00, '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(16, 6, 'PROCESSING_FEE', 11.67, '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(17, 6, 'GST', 2.10, '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(18, 6, 'INSURANCE', 0.00, '2026-09-02 11:12:54', '2026-09-02 11:12:54'),
(19, 7, 'PROCESSING_FEE', 189.60, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(20, 7, 'GST', 34.13, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(21, 7, 'INSURANCE', 0.00, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(22, 8, 'PROCESSING_FEE', 333.60, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(23, 8, 'GST', 60.05, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(24, 8, 'INSURANCE', 0.00, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(25, 9, 'PROCESSING_FEE', 24.00, '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(26, 9, 'GST', 4.32, '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(27, 9, 'INSURANCE', 0.00, '2026-09-03 08:21:09', '2026-09-03 08:21:09'),
(28, 10, 'PROCESSING_FEE', 24.00, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(29, 10, 'GST', 4.32, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(30, 10, 'INSURANCE', 0.00, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(31, 11, 'PROCESSING_FEE', 446.40, '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(32, 11, 'GST', 80.35, '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(33, 11, 'INSURANCE', 0.00, '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(34, 12, 'PROCESSING_FEE', 128.40, '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(35, 12, 'GST', 23.11, '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(36, 12, 'INSURANCE', 0.00, '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(37, 13, 'PROCESSING_FEE', 46.80, '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(38, 13, 'GST', 8.42, '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(39, 13, 'INSURANCE', 0.00, '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(40, 14, 'PROCESSING_FEE', 33.96, '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(41, 14, 'GST', 6.11, '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(42, 14, 'INSURANCE', 0.00, '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(43, 15, 'PROCESSING_FEE', 495.60, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(44, 15, 'GST', 89.21, '2026-09-03 10:17:43', '2026-09-03 10:17:43'),
(45, 15, 'INSURANCE', 0.00, '2026-09-03 10:17:43', '2026-09-03 10:17:43');

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
(1, 4, 84000.00, '2026-09-02', 1, '2026-09-02 08:47:11', '2026-09-02 08:47:11');

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
(1, 1, 1, 265686.10, 'AF001', 'COMPLETED', 3, '2026-09-02 06:30:30', '2026-09-02 06:30:30'),
(2, 2, 1, 72713.98, '2', 'COMPLETED', 1, '2026-09-02 07:31:22', '2026-09-02 07:31:22'),
(3, 3, 1, 66920.70, 'AF003', 'COMPLETED', 1, '2026-09-02 08:08:27', '2026-09-02 08:08:27'),
(4, 4, 1, 83900.88, 'AF004', 'COMPLETED', 1, '2026-09-02 08:39:46', '2026-09-02 08:39:46'),
(5, 5, 1, 49940.99, 'AF005', 'COMPLETED', 1, '2026-09-02 09:04:41', '2026-09-02 09:04:41'),
(6, 6, 1, 10986.23, 'AF006', 'COMPLETED', 1, '2026-09-02 14:47:51', '2026-09-02 14:47:51'),
(7, 7, 1, 173276.27, 'AF007', 'COMPLETED', 1, '2026-09-03 07:14:05', '2026-09-03 07:14:05'),
(8, 8, 1, 309606.35, 'AF008', 'COMPLETED', 1, '2026-09-03 08:12:39', '2026-09-03 08:12:39'),
(9, 10, 1, 22271.68, 'AF010', 'COMPLETED', 1, '2026-09-03 08:32:25', '2026-09-03 08:32:25'),
(10, 11, 1, 415473.25, 'AF011', 'COMPLETED', 1, '2026-09-03 09:00:49', '2026-09-03 09:00:49'),
(11, 12, 1, 126148.49, 'AF012', 'COMPLETED', 1, '2026-09-03 09:12:07', '2026-09-03 09:12:07'),
(12, 13, 1, 42844.78, 'AF013', 'COMPLETED', 1, '2026-09-03 09:34:08', '2026-09-03 09:34:08'),
(13, 14, 1, 33059.93, 'AF014', 'COMPLETED', 1, '2026-09-03 10:04:16', '2026-09-03 10:04:16'),
(14, 15, 1, 445415.19, 'AF015', 'COMPLETED', 1, '2026-09-03 11:19:26', '2026-09-03 11:19:26');

-- --------------------------------------------------------

--
-- Table structure for table `loan_documents`
--

CREATE TABLE `loan_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(30) NOT NULL DEFAULT 'AGREEMENT',
  `file_ref` varchar(255) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_documents`
--

INSERT INTO `loan_documents` (`id`, `loan_id`, `document_type`, `file_ref`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'JEWELLERY_PHOTO', 'loan-documents/3bb1e6e043db23ce6a1b1b3468db700c.jpg', 3, '2026-09-02 06:13:09', '2026-09-02 06:13:09'),
(2, 1, 'SANCTION_LETTER', 'loan-documents/7d3d624db05eaed1738eadc6ff42e4a3.jpg', 3, '2026-09-02 06:13:34', '2026-09-02 06:13:34'),
(3, 1, 'AGREEMENT', 'loan-documents/327f08453229e84f29696c9ddf8a2518.jpg', 3, '2026-09-02 06:30:02', '2026-09-02 06:30:02'),
(4, 2, 'JEWELLERY_PHOTO', 'loan-documents/4db018f16db132051edb4232dec23936.jpg', 1, '2026-09-02 07:25:27', '2026-09-02 07:25:27'),
(5, 2, 'AGREEMENT', 'loan-documents/50d198fae47ef7196a08ef9a7fce27fe.jpg', 1, '2026-09-02 07:29:43', '2026-09-02 07:29:43'),
(6, 2, 'SANCTION_LETTER', 'loan-documents/4eb1941a675bc623e0bde46cb5c9bd43.jpg', 1, '2026-09-02 07:30:59', '2026-09-02 07:30:59'),
(7, 3, 'JEWELLERY_PHOTO', 'loan-documents/73e9642f24198aa2baed31503ba980fa.jpg', 1, '2026-09-02 07:55:58', '2026-09-02 07:55:58'),
(8, 3, 'AGREEMENT', 'loan-documents/f3a1de2f1d388df6fd958f592f64ff3e.jpg', 1, '2026-09-02 08:03:30', '2026-09-02 08:03:30'),
(9, 3, 'SANCTION_LETTER', 'loan-documents/f85ff454354289ced364fd12c5825766.jpg', 1, '2026-09-02 08:07:56', '2026-09-02 08:07:56'),
(10, 4, 'JEWELLERY_PHOTO', 'loan-documents/b2829fcced70266072e4849719022b38.jpg', 1, '2026-09-02 08:38:04', '2026-09-02 08:38:04'),
(11, 4, 'SANCTION_LETTER', 'loan-documents/cb8df1a2329a466cdcdadf98ad7d6d27.jpg', 1, '2026-09-02 08:38:56', '2026-09-02 08:38:56'),
(12, 4, 'AGREEMENT', 'loan-documents/1328f15199f90d43b785f4fde8b59a1a.pdf', 1, '2026-09-02 08:39:15', '2026-09-02 08:39:15'),
(13, 5, 'JEWELLERY_PHOTO', 'loan-documents/6874c7ca31a8f518438f87312d6fdc52.jpg', 1, '2026-09-02 09:02:32', '2026-09-02 09:02:32'),
(14, 5, 'AGREEMENT', 'loan-documents/a9e8881d9120adbac89b075936838583.pdf', 1, '2026-09-02 09:03:48', '2026-09-02 09:03:48'),
(15, 5, 'SANCTION_LETTER', 'loan-documents/a612947102560592560f19a69c2a6a34.jpg', 1, '2026-09-02 09:04:18', '2026-09-02 09:04:18'),
(16, 6, 'AGREEMENT', 'loan-documents/6a110bd6421ff37fd727c10c54a7af52.jpg', 1, '2026-09-02 14:46:30', '2026-09-02 14:46:30'),
(17, 6, 'JEWELLERY_PHOTO', 'loan-documents/1cc627284e650bd31b86586d4d40a063.jpg', 1, '2026-09-02 14:46:48', '2026-09-02 14:46:48'),
(18, 6, 'SANCTION_LETTER', 'loan-documents/41b42a643138d31ffb76dd675c2abdaa.pdf', 1, '2026-09-02 14:47:21', '2026-09-02 14:47:21'),
(19, 7, 'JEWELLERY_PHOTO', 'loan-documents/7e58589f66e51d3ecda92e17abb4b4da.jpg', 1, '2026-09-03 07:05:27', '2026-09-03 07:05:27'),
(20, 7, 'SANCTION_LETTER', 'loan-documents/b3430255878f940f06cb45a45acb2956.pdf', 1, '2026-09-03 07:08:09', '2026-09-03 07:08:09'),
(21, 7, 'AGREEMENT', 'loan-documents/574c5beb434468845061ec993c285cb5.pdf', 1, '2026-09-03 07:08:38', '2026-09-03 07:08:38'),
(22, 8, 'JEWELLERY_PHOTO', 'loan-documents/df4e73d342a230c24f35617a355df961.jpg', 1, '2026-09-03 08:00:06', '2026-09-03 08:00:06'),
(23, 8, 'SANCTION_LETTER', 'loan-documents/58f03b6d7353fb6b01de66decfed9e29.pdf', 1, '2026-09-03 08:11:53', '2026-09-03 08:11:53'),
(24, 8, 'AGREEMENT', 'loan-documents/601551150a9890c420f61b77e2c0e66b.pdf', 1, '2026-09-03 08:12:11', '2026-09-03 08:12:11'),
(25, 9, 'JEWELLERY_PHOTO', 'loan-documents/6617b0add93539352d729de3c7d993d9.jpg', 1, '2026-09-03 08:21:52', '2026-09-03 08:21:52'),
(26, 9, 'JEWELLERY_PHOTO', 'loan-documents/d1be441b25a47794dc37c3a93348d089.jpg', 1, '2026-09-03 08:22:36', '2026-09-03 08:22:36'),
(27, 10, 'JEWELLERY_PHOTO', 'loan-documents/e15058ddad4929d3e1240198921acad9.jpg', 1, '2026-09-03 08:28:05', '2026-09-03 08:28:05'),
(28, 10, 'AGREEMENT', 'loan-documents/ccb5d243dd4b67447b9abbdecfe07841.pdf', 1, '2026-09-03 08:31:46', '2026-09-03 08:31:46'),
(29, 10, 'SANCTION_LETTER', 'loan-documents/11501a36cfa9acb975cd68534952ae39.pdf', 1, '2026-09-03 08:31:57', '2026-09-03 08:31:57'),
(30, 11, 'JEWELLERY_PHOTO', 'loan-documents/f70197e65b9929b99f31ed47599a90be.jpg', 1, '2026-09-03 08:58:48', '2026-09-03 08:58:48'),
(31, 11, 'AGREEMENT', 'loan-documents/23e6f9306a1293d4a9120857f3ad6f59.pdf', 1, '2026-09-03 09:00:10', '2026-09-03 09:00:10'),
(32, 11, 'SANCTION_LETTER', 'loan-documents/a07fc9febac18a017ac591aaf31fbf93.pdf', 1, '2026-09-03 09:00:24', '2026-09-03 09:00:24'),
(33, 12, 'JEWELLERY_PHOTO', 'loan-documents/8eab32634d755d6d15b00704839102b2.jpg', 1, '2026-09-03 09:09:59', '2026-09-03 09:09:59'),
(34, 12, 'AGREEMENT', 'loan-documents/78566383585193dc44c86f1f09a05abf.pdf', 1, '2026-09-03 09:10:51', '2026-09-03 09:10:51'),
(35, 12, 'SANCTION_LETTER', 'loan-documents/00cecc6a0ae9f50dff87816b944e0a95.pdf', 1, '2026-09-03 09:11:10', '2026-09-03 09:11:10'),
(36, 13, 'JEWELLERY_PHOTO', 'loan-documents/8fe9ac1c03c84a7bc3d5f90c5b57b875.jpg', 1, '2026-09-03 09:30:15', '2026-09-03 09:30:15'),
(37, 13, 'AGREEMENT', 'loan-documents/f56e96161feb8685ee0d925b841ce148.pdf', 1, '2026-09-03 09:31:57', '2026-09-03 09:31:57'),
(38, 13, 'SANCTION_LETTER', 'loan-documents/04c7b10f177c9b2f4dd7b0d9bd5cbda2.pdf', 1, '2026-09-03 09:32:18', '2026-09-03 09:32:18'),
(39, 14, 'JEWELLERY_PHOTO', 'loan-documents/937508b2ebaddf508353793fee378024.jpg', 1, '2026-09-03 09:50:11', '2026-09-03 09:50:11'),
(40, 14, 'AGREEMENT', 'loan-documents/d35d8cd09e846ab06648ee988c575114.pdf', 1, '2026-09-03 10:03:50', '2026-09-03 10:03:50'),
(41, 14, 'SANCTION_LETTER', 'loan-documents/51b29ac5649dc394b6aba87b2f512151.pdf', 1, '2026-09-03 10:04:00', '2026-09-03 10:04:00'),
(42, 15, 'JEWELLERY_PHOTO', 'loan-documents/e86ecfbb18f2f01becef362910e9af36.jpg', 1, '2026-09-03 10:20:53', '2026-09-03 10:20:53'),
(43, 15, 'SANCTION_LETTER', 'loan-documents/9cb163d5a6ee62dd1876dc68d1476469.pdf', 1, '2026-09-03 11:05:47', '2026-09-03 11:05:47'),
(44, 15, 'AGREEMENT', 'loan-documents/f10ebdeb469fd122a17d97137a42e7d5.pdf', 1, '2026-09-03 11:06:33', '2026-09-03 11:06:33');

-- --------------------------------------------------------

--
-- Table structure for table `loan_part_payments`
--

CREATE TABLE `loan_part_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `principal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `interest_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `idempotency_key` varchar(64) DEFAULT NULL,
  `collected_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_part_payments`
--

INSERT INTO `loan_part_payments` (`id`, `loan_id`, `principal_amount`, `interest_amount`, `idempotency_key`, `collected_by`, `created_at`, `updated_at`) VALUES
(1, 4, 84000.00, 765.00, NULL, 1, '2026-09-02 08:41:25', '2026-09-02 08:41:25'),
(2, 4, 84000.00, 765.00, NULL, 1, '2026-09-02 08:42:05', '2026-09-02 08:42:05'),
(3, 11, 160.00, 0.00, NULL, 1, '2026-09-03 12:17:52', '2026-09-03 12:17:52');

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
  `processing_fee_type` varchar(20) NOT NULL DEFAULT 'PERCENTAGE',
  `processing_fee_flat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_pct` decimal(5,2) NOT NULL DEFAULT 18.00,
  `insurance_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_products`
--

INSERT INTO `loan_products` (`id`, `code`, `name`, `interest_rate_pct`, `interest_type`, `tenure_months`, `processing_fee_pct`, `processing_fee_type`, `processing_fee_flat`, `gst_pct`, `insurance_pct`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AF003', 'Standard Gold Loan-3M-H1.50', 18.00, 'FLAT', 6, 0.10, 'PERCENTAGE', 0.00, 18.00, 0.00, 1, '2026-08-07 16:14:20', '2026-09-03 07:35:46'),
(2, 'AF004', 'Premium Gold Loan2-6M-H1.33', 16.00, 'FLAT', 6, 0.10, 'PERCENTAGE', 0.00, 18.00, 0.00, 1, '2026-08-07 16:14:20', '2026-09-03 07:35:09'),
(3, 'AF002', 'Express Gold Loan-3M-Q1.83', 22.00, 'FLAT', 6, 0.10, 'PERCENTAGE', 0.00, 18.00, 0.00, 1, '2026-08-07 16:14:20', '2026-09-03 07:33:43'),
(4, 'AF001', 'Premium Gold Loan-6M-H1.10', 13.20, 'FLAT', 6, 0.10, 'PERCENTAGE', 0.00, 18.00, 0.00, 1, '2026-08-08 15:30:33', '2026-09-03 07:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `loan_reloads`
--

CREATE TABLE `loan_reloads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `excess_amount_eligible` decimal(12,2) NOT NULL,
  `reload_amount` decimal(12,2) NOT NULL,
  `previous_sanctioned_amount` decimal(12,2) DEFAULT NULL,
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
  `previous_due_date` date DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
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
-- Table structure for table `loan_topups`
--

CREATE TABLE `loan_topups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `eligible_topup_amount` decimal(12,2) NOT NULL,
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `previous_sanctioned_amount` decimal(12,2) DEFAULT NULL,
  `processing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_topups`
--

INSERT INTO `loan_topups` (`id`, `loan_id`, `eligible_topup_amount`, `approved_amount`, `previous_sanctioned_amount`, `processing_fee`, `status`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 4, 168000.00, 168000.00, -84000.00, 0.00, 'DISBURSED', 1, '2026-09-02 08:45:44', '2026-09-02 08:46:01');

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
(26, 'User', 3, 'mobile-app', 'fd268b68ec77976e0c4ee0dad39ede10c85e72b763ebc94884f6b7c63d0412bd', NULL, '2026-08-15 09:54:45', NULL, '2026-08-15 08:54:45', '2026-08-15 08:54:45'),
(27, 'User', 3, 'mobile-app', 'fe2396565671bee7234d676817aaeeef23a359504bb51fbbebe6674417c718ff', NULL, '2026-08-15 13:03:42', '2026-08-15 12:10:35', '2026-08-15 12:03:42', '2026-08-15 12:10:35'),
(28, 'User', 3, 'mobile-app', '3831805474806408853410f2fced3e7e9465fc1f6bc5faa1c806893cf300b9e1', NULL, '2026-08-15 18:27:43', '2026-08-15 17:27:52', '2026-08-15 17:27:43', '2026-08-15 17:27:52'),
(29, 'User', 3, 'mobile-app', 'e8607cff2240b040570ea22ac328046243a52e3876d6f8d7f4573485c2782fcf', NULL, '2026-08-19 18:50:27', '2026-08-19 17:53:55', '2026-08-19 17:50:27', '2026-08-19 17:53:55'),
(30, 'User', 3, 'mobile-app', '768af21fba3d4b46daf27c4810d8f9feaff3e824232f10030564b2a50ee5d404', NULL, '2026-08-20 06:42:36', '2026-08-20 05:52:57', '2026-08-20 05:42:36', '2026-08-20 05:52:57'),
(31, 'User', 3, 'mobile-app', 'c788be93dc0601f4f3f00aca841c88f14fb712da78121eaad4562b02e4caa583', NULL, '2026-08-20 18:43:24', '2026-08-20 17:46:16', '2026-08-20 17:43:24', '2026-08-20 17:46:16'),
(32, 'User', 3, 'mobile-app', '7bf27880326f1bead72df8fc1430da166669842996fa6c2b80fce1a3a618c93e', NULL, '2026-08-20 18:51:26', '2026-08-20 17:51:40', '2026-08-20 17:51:26', '2026-08-20 17:51:40'),
(33, 'User', 3, 'mobile-app', '3ec32e4fb24dc7abb0b34ade8bae508b60c06e9240a63c7c69aefceeea0119ca', NULL, '2026-08-20 19:04:29', '2026-08-20 18:04:31', '2026-08-20 18:04:29', '2026-08-20 18:04:31'),
(34, 'User', 3, 'mobile-app', '180cf7be3d790000833d078b8490016c502ad57894e59d36e97a703989a5f7cc', NULL, '2026-08-21 04:33:58', '2026-08-21 03:37:14', '2026-08-21 03:33:58', '2026-08-21 03:37:14');

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
  `device_id` varchar(191) DEFAULT NULL,
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
(1, 3, 'test-device-001', NULL, NULL, 1, '2026-08-21 03:33:58', '2026-08-08 10:39:49', '2026-08-21 03:33:58'),
(2, 1, 'test-device-001', NULL, NULL, 1, '2026-08-08 10:56:50', '2026-08-08 10:56:50', '2026-08-08 10:56:50'),
(3, 3, '31afae942e26162b', NULL, NULL, 1, '2026-08-09 15:31:03', '2026-08-09 15:31:03', '2026-08-09 15:31:03'),
(4, 3, '27cbd4eb-43d7-4d52-80f4-1546d41915b6', NULL, NULL, 1, '2026-08-12 16:52:03', '2026-08-12 16:49:16', '2026-08-12 16:52:03'),
(5, 3, '610c577b-74e9-4a85-b0ce-8bcccb0af203', NULL, NULL, 1, '2026-08-15 17:27:43', '2026-08-12 18:04:59', '2026-08-15 17:27:43'),
(6, 3, '3d7c0848-4f4c-449c-a621-d1fb1b81d725', NULL, NULL, 1, '2026-08-20 18:04:29', '2026-08-19 17:50:27', '2026-08-20 18:04:29');

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
(1, 'AF001-EMP0001', 'Yohamalar K', '7339468884', 'admin@afgl.com', '$2y$10$9XIMUOSEHQgucLQLwyh2PeP7mbaViHRVrX1jUjtmVlhZYT9SU2U9u', NULL, 10, 1, 1, NULL, '2026-08-07 16:14:20', '2026-09-02 06:48:29'),
(3, 'AF002-EMP0002', 'Satham Hussain H', '9042705284', 'Admin1@afgl.com', '$2y$10$STmT8ULKmobr..d9lhdGxOitEE0.O5LG9m3z.4P3IjlOkDiEGpf76', '$2y$10$iBzmO7u/Ud9f9W55owzvB.S8f4VGVRbDNtwQPa6VydnaNrIjTa.kq', 10, 1, 0, '2026-08-21 03:33:58', '2026-08-07 16:14:20', '2026-09-02 07:09:00'),
(4, 'AF003-EMP0003', 'Vinothkumar', '9940952161', 'admin2@afgl.com', '$2y$10$XA/aY6VWPi.TH5BiC4hevuKYmyxfYQMdKCCUZQA9HMgHxDaXuAepO', NULL, 10, 1, 1, NULL, '2026-09-02 06:50:01', '2026-09-02 06:50:01');

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
  ADD UNIQUE KEY `idx_ic_idempotency_key` (`idempotency_key`),
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
-- Indexes for table `jewellery_valuation_history`
--
ALTER TABLE `jewellery_valuation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jvh_item` (`jewellery_item_id`),
  ADD KEY `fk_jvh_rate` (`gold_rate_id`),
  ADD KEY `fk_jvh_evaluator` (`evaluated_by`);

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
-- Indexes for table `loan_documents`
--
ALTER TABLE `loan_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ld_loan` (`loan_id`),
  ADD KEY `fk_ld_uploader` (`uploaded_by`);

--
-- Indexes for table `loan_part_payments`
--
ALTER TABLE `loan_part_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_lpp_idempotency_key` (`idempotency_key`),
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_bids`
--
ALTER TABLE `auction_bids`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_notice_logs`
--
ALTER TABLE `auction_notice_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_schedules`
--
ALTER TABLE `auction_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_settlement`
--
ALTER TABLE `auction_settlement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auction_winners`
--
ALTER TABLE `auction_winners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=377;

--
-- AUTO_INCREMENT for table `bank_reconciliation_log`
--
ALTER TABLE `bank_reconciliation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
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
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customer_address`
--
ALTER TABLE `customer_address`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_rates`
--
ALTER TABLE `gold_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `jewellery_valuation_history`
--
ALTER TABLE `jewellery_valuation_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_verifications`
--
ALTER TABLE `kyc_aadhaar_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kyc_aadhaar_xml_log`
--
ALTER TABLE `kyc_aadhaar_xml_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_document_master`
--
ALTER TABLE `kyc_document_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kyc_document_types`
--
ALTER TABLE `kyc_document_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kyc_face_auth_logs`
--
ALTER TABLE `kyc_face_auth_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kyc_pan_verifications`
--
ALTER TABLE `kyc_pan_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `loan_approval_limit_master`
--
ALTER TABLE `loan_approval_limit_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_approval_logs`
--
ALTER TABLE `loan_approval_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `loan_approval_workflows`
--
ALTER TABLE `loan_approval_workflows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `loan_calculation_log`
--
ALTER TABLE `loan_calculation_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_charges`
--
ALTER TABLE `loan_charges`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `loan_documents`
--
ALTER TABLE `loan_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `loan_part_payments`
--
ALTER TABLE `loan_part_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_products`
--
ALTER TABLE `loan_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loan_reloads`
--
ALTER TABLE `loan_reloads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_renewals`
--
ALTER TABLE `loan_renewals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voucher_details`
--
ALTER TABLE `voucher_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `jewellery_valuation_history`
--
ALTER TABLE `jewellery_valuation_history`
  ADD CONSTRAINT `fk_jvh_evaluator` FOREIGN KEY (`evaluated_by`) REFERENCES `user_master` (`id`),
  ADD CONSTRAINT `fk_jvh_item` FOREIGN KEY (`jewellery_item_id`) REFERENCES `jewellery_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jvh_rate` FOREIGN KEY (`gold_rate_id`) REFERENCES `gold_rates` (`id`);

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
-- Constraints for table `loan_documents`
--
ALTER TABLE `loan_documents`
  ADD CONSTRAINT `fk_loandoc_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_loandoc_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `user_master` (`id`);

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
