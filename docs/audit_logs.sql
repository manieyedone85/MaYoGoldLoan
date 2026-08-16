-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 15, 2026 at 08:59 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
