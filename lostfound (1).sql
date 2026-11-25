-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 05:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lostfound`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `message`, `is_read`, `created_at`) VALUES
(2, 'User Meryl Rapista reported a Lost item: \'bag\'', 1, '2025-10-25 10:23:24'),
(7, 'User Admin User submitted a claim for \'n c ivd m\'', 1, '2025-10-25 17:35:30'),
(8, 'User Meryl Rapista submitted a claim for \'n c ivd m\'', 1, '2025-10-25 17:36:22'),
(9, 'User Meryl Rapista submitted a claim for \'VIVO Phone\' (Pending verification).', 1, '2025-10-28 16:38:17'),
(10, 'Admin Admin User Approved the claim for \'VIVO Phone\'.', 1, '2025-10-28 16:58:02'),
(11, 'User jisdsc submitted a claim for \'njshudc\' (Pending verification).', 1, '2025-10-28 17:01:42'),
(12, 'Claim ID #9 has been Approved by Admin User.', 1, '2025-10-28 17:02:19'),
(13, 'User Meryl Rapista submitted a claim for \'GH\' (Pending verification).', 1, '2025-11-11 14:50:05'),
(14, 'User jisdsc submitted a claim for \'Red wallet\' (Pending verification).', 1, '2025-11-22 14:59:28'),
(15, 'Claim ID #11 has been Approved by Admin User.', 1, '2025-11-22 15:01:03'),
(16, 'User Faith Carisma submitted a claim for \'Birth Certificate\' (Pending verification).', 1, '2025-11-23 14:16:58'),
(17, 'Claim ID #12 has been Rejected by Admin User.', 1, '2025-11-23 14:18:14'),
(18, 'Claim ID #10 has been Approved by Admin User.', 1, '2025-11-23 15:07:59'),
(19, 'User Faith Carisma submitted a claim for \'cat\' (Pending verification).', 1, '2025-11-27 01:00:03'),
(20, 'Claim ID #13 has been Rejected by Admin User.', 1, '2025-11-27 01:01:47'),
(21, 'User Dharyl Rapista submitted a claim for \'cat\' (Pending verification).', 1, '2025-11-27 01:14:38'),
(22, 'Claim ID #14 has been Approved by Admin User.', 1, '2025-11-27 01:14:58'),
(23, 'User Meryl Rapista submitted a claim for \'Phone\' (Pending verification).', 1, '2025-11-27 07:09:37'),
(24, 'Claim ID #15 has been Approved by Admin User.', 1, '2025-11-27 07:10:40'),
(25, 'User Meryl Rapista submitted a claim for \'Apple watch\' (Pending verification).', 0, '2025-12-01 14:28:40'),
(26, 'Claim ID #16 has been Rejected by Admin User.', 0, '2025-12-02 14:39:06'),
(27, 'User erra roncal submitted a claim for \'chdc\' (Pending verification).', 0, '2025-12-03 00:33:51'),
(28, 'Claim ID #17 has been Approved by Admin User.', 0, '2025-12-03 00:36:51'),
(29, 'User Faith Carisma submitted a claim for \'basta\' (Pending verification).', 0, '2025-12-04 04:32:03'),
(30, 'Claim ID #18 has been Approved by Admin User.', 0, '2025-12-04 08:45:39');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `claimant_user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `verification_q1` text DEFAULT NULL,
  `verification_q2` text DEFAULT NULL,
  `verification_q3` text DEFAULT NULL,
  `additional_description` text DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `item_id`, `claimant_user_id`, `message`, `status`, `created_at`, `resolved_at`, `verification_q1`, `verification_q2`, `verification_q3`, `additional_description`, `proof_image`, `is_seen`) VALUES
(5, 13, 4, 'Claim request submitted', 'Approved', '2025-10-25 17:08:51', '2025-10-25 17:19:49', NULL, NULL, NULL, NULL, NULL, 1),
(13, 36, 11, 'Claim request submitted', 'Rejected', '2025-11-27 01:00:03', '2025-11-27 01:01:47', 'canteen', 'calico', 'mama ko witness', '', 'uploads/claims/1764205203_ahh.jpg', 1),
(14, 36, 13, 'Claim request submitted', 'Approved', '2025-11-27 01:14:38', '2025-11-27 01:14:58', 'canteen', 'calico', 'photo', '', 'uploads/claims/1764206078_asskaybilly.jpg', 1),
(15, 37, 1, 'Claim request submitted', 'Approved', '2025-11-27 07:09:37', '2025-11-27 07:10:40', 'cjcj', 'kcihidh', 'jdcsdh', '', 'uploads/claims/1764227377_AGATGACHRIST.jpg', 1),
(16, 45, 1, 'Claim request submitted', 'Rejected', '2025-12-01 14:28:40', '2025-12-02 14:39:06', 'basta', 'basta', 'basta', '', 'uploads/claims/1764599320_download (20).jfif', 1),
(17, 49, 16, 'Claim request submitted', 'Approved', '2025-12-03 00:33:51', '2025-12-03 00:36:51', 'hbhb', 'kcihidh', 'photo', '', 'uploads/claims/1764722031_download (16).jfif', 1),
(18, 55, 11, 'Claim request submitted', 'Approved', '2025-12-04 04:32:03', '2025-12-04 08:45:39', 'basta', 'basta', 'photo', '', 'uploads/claims/1764822723_download (22).jfif', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `target` varchar(20) DEFAULT 'user',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `user_id`, `message`, `created_at`, `target`, `is_read`) VALUES
(2, 1, '???? You successfully reported a FOUND item: \'Phone\'.', '2025-10-19 23:20:45', 'user', 1),
(3, 4, '???? You have successfully submitted a claim request for an item.', '2025-10-19 23:32:56', 'user', 1),
(4, 9, '???? You successfully reported a LOST item: \'bag\'.', '2025-10-25 18:20:40', 'user', 0),
(5, 1, '???? You successfully reported a LOST item: \'bag\'.', '2025-10-25 18:23:24', 'user', 1),
(6, 9, '???? You successfully reported a FOUND item: \'Id\'.', '2025-10-25 20:05:49', 'user', 0),
(7, 1, '???? You successfully reported a FOUND item: \'Id\'.', '2025-10-25 20:08:29', 'user', 1),
(8, 9, '???? You have successfully submitted a claim request for an item.', '2025-10-26 00:37:47', 'user', 0),
(9, 9, '???? You have successfully submitted a claim request for an item.', '2025-10-26 00:45:50', 'user', 0),
(10, 4, '???? You have successfully submitted a claim request for an item.', '2025-10-26 01:08:51', 'user', 1),
(11, 4, 'Your claim for \'Id\' has been \'Approved\' by admin.', '2025-10-26 01:19:49', 'user', 1),
(12, 4, 'Your report for \'n c ivd m\' has been \'Open\' by the admin.', '2025-10-26 01:35:07', 'user', 1),
(13, 9, '???? You have successfully submitted a claim request for an item.', '2025-10-26 01:35:30', 'user', 0),
(14, 1, '???? You have successfully submitted a claim request for an item.', '2025-10-26 01:36:22', 'user', 1),
(15, 1, 'Your claim for \'n c ivd m\' has been Approved by the admin.', '2025-10-26 01:36:39', 'user', 1),
(16, 1, '???? You have successfully submitted a claim request with verification details.', '2025-10-29 00:38:17', 'user', 1),
(17, 1, '✅ Your claim for \'VIVO Phone\' has been approved by the admin. You may coordinate for retrieval.', '2025-10-29 00:58:02', 'user', 1),
(18, 4, '???? You have successfully submitted a claim request with verification details.', '2025-10-29 01:01:42', 'user', 1),
(19, 4, '???? Your claim for \'njshudc\' has been Approved by the admin.', '2025-10-29 01:02:19', 'user', 1),
(20, 4, 'Your report for \'VIVO Phone\' has been \'Open\' by the admin.', '2025-10-29 01:13:40', 'user', 1),
(21, 1, 'Your report for \'njshudc\' has been \'Open\' by the admin.', '2025-10-29 01:13:43', 'user', 1),
(22, 1, 'Your report for \'cnsdsd\' has been \'Missing\' by the admin.', '2025-10-29 01:14:58', 'user', 1),
(23, 4, 'Your report for \'cjbbd\' has been \'Missing\' by the admin.', '2025-10-29 01:26:56', 'user', 1),
(24, 1, 'Your report for \'jzcu\' has been \'Missing\' by the admin.', '2025-10-29 01:35:15', 'user', 1),
(25, 4, 'Your report for \'cbj\' has been \'Missing\' by the admin.', '2025-10-29 01:58:37', 'user', 1),
(26, 1, 'Your report for \'cnsdsd\' has been \'Rejected\' by the admin.', '2025-10-29 01:58:40', 'user', 1),
(27, 4, 'Your report for \'cbj\' has been \'Rejected\' by the admin.', '2025-10-29 01:59:05', 'user', 1),
(28, 1, 'Your report for \'jc c\' has been \'Rejected\' by the admin.', '2025-10-29 02:10:11', 'user', 1),
(29, 4, 'Your report for \'c\' has been \'Missing\' by the admin.', '2025-10-29 02:14:05', 'user', 1),
(30, 1, 'Your report for \'c jdf\' has been set to\'Missing\' by the admin.', '2025-11-11 21:42:33', 'user', 1),
(31, 4, 'Your report for \'GH\' has been set to\'Open\' by the admin.', '2025-11-11 22:34:50', 'user', 1),
(32, 1, '???? You have successfully submitted a claim request with verification details.', '2025-11-11 22:50:05', 'user', 1),
(33, 10, 'Your report for \'Red wallet\' has been set to \'Open\' by the admin.', '2025-11-22 22:56:01', 'user', 1),
(34, 4, '???? You have successfully submitted a claim request with verification details.', '2025-11-22 22:59:28', 'user', 1),
(35, 4, '???? Your claim for \'Red wallet\' has been set to Approved by the admin.', '2025-11-22 23:01:03', 'user', 1),
(36, 1, 'Your report for \'selpon\' has been set to \'Claimed\' by the admin.', '2025-11-23 22:00:42', 'user', 1),
(37, 1, 'Your report for \'Meow\' has been set to \'Claimed\' by the admin.', '2025-11-23 22:01:26', 'user', 1),
(38, 11, '???? You have successfully submitted a claim request with verification details.', '2025-11-23 22:16:58', 'user', 1),
(39, 11, '???? Your claim for \'Birth Certificate\' has been set to Rejected by the admin.', '2025-11-23 22:18:14', 'user', 1),
(40, 1, 'Your report for \'bag\' has been set to \'Claimed\' by the admin.', '2025-11-23 22:25:36', 'user', 1),
(41, 4, 'Your report for \'Headphone\' has been set to \'Rejected\' by the admin.', '2025-11-23 22:25:57', 'user', 1),
(42, 1, 'Your report for \'COR\' has been set to \'Rejected\' by the admin.', '2025-11-23 22:26:03', 'user', 1),
(43, 4, 'Your report for \'Headphone\' has been set to \'Missing\' by the admin.', '2025-11-23 22:26:06', 'user', 1),
(44, 11, 'Your report for \'kxcns\' has been set to \'Rejected\' by the admin.', '2025-11-23 22:31:50', 'user', 1),
(45, 4, 'Your report for \'Headphone\' has been set to \'Rejected\' by the admin.', '2025-11-23 22:35:57', 'user', 1),
(46, 11, 'Your report for \'v\' has been set to \'Rejected\' by the admin.', '2025-11-23 22:37:04', 'user', 1),
(47, 1, '???? Your claim for \'Birth Certificate\' has been set to Approved by the admin.', '2025-11-23 23:07:59', 'user', 1),
(48, 11, 'Your report for \'kxcns\' has been set to \'Claimed\' by the admin.', '2025-11-23 23:10:39', 'user', 1),
(49, 4, 'Your report for \'Headphone\' has been set to \'Claimed\' by the admin.', '2025-11-23 23:10:44', 'user', 1),
(50, 1, 'Your report for \'COR\' has been set to \'Claimed\' by the admin.', '2025-11-23 23:10:46', 'user', 1),
(51, 4, 'Your report for \'VIVO Phone\' has been set to \'Rejected\' by the admin.', '2025-11-23 23:11:08', 'user', 1),
(52, 4, 'Your report for \'Birth Certificate\' has been set to \'Rejected\' by the admin.', '2025-11-23 23:11:09', 'user', 1),
(53, 10, 'Your report for \'Red wallet\' has been set to \'Rejected\' by the admin.', '2025-11-23 23:11:10', 'user', 0),
(54, 4, 'Your report for \'necklace\' has been set to \'Claimed\' by the admin.', '2025-11-23 23:11:14', 'user', 1),
(55, 12, 'Your report for \'fgrb\' has been set to \'Rejected\' by the admin.', '2025-11-23 23:14:11', 'user', 1),
(56, 12, 'Your report for \'j\' has been set to \'Open\' by the admin.', '2025-11-23 23:15:07', 'user', 1),
(57, 12, 'Your report for \'j\' has been set to \'Claimed\' by the admin.', '2025-11-23 23:15:17', 'user', 1),
(58, 12, 'Your report for \'Anak ko\' has been set to \'Open\' by the admin.', '2025-11-23 23:21:32', 'user', 0),
(59, 11, 'Your report for \'cat\' has been set to \'Missing\' by the admin.', '2025-11-27 08:51:53', 'user', 1),
(60, 4, 'Your report for \'cat\' has been set to \'Open\' by the admin.', '2025-11-27 08:57:52', 'user', 1),
(61, 11, '???? You have successfully submitted a claim request with verification details.', '2025-11-27 09:00:03', 'user', 1),
(62, 11, '???? Your claim for \'cat\' has been set to Rejected by the admin.', '2025-11-27 09:01:47', 'user', 1),
(63, 12, 'Your report for \'Anak ko\' has been set to \'Rejected\' by the admin.', '2025-11-27 09:09:25', 'user', 0),
(64, 13, '???? You have successfully submitted a claim request with verification details.', '2025-11-27 09:14:38', 'user', 1),
(65, 13, '???? Your claim for \'cat\' has been set to Approved by the admin.', '2025-11-27 09:14:58', 'user', 1),
(66, 11, 'Your report for \'cat\' has been set to \'Claimed\' by the admin.', '2025-11-27 09:16:20', 'user', 1),
(67, 11, 'Your report for \'Phone\' has been set to \'Open\' by the admin.', '2025-11-27 15:08:14', 'user', 1),
(68, 1, '???? You have successfully submitted a claim request with verification details.', '2025-11-27 15:09:37', 'user', 1),
(69, 1, '???? Your claim for \'Phone\' has been set to Approved by the admin.', '2025-11-27 15:10:40', 'user', 1),
(70, 4, 'Your report for \'cat\' has been set to \'Claimed\' by the admin.', '2025-11-27 15:11:25', 'user', 1),
(71, 14, 'Your report for \'wallet\' has been set to \'Missing\' by the admin.', '2025-11-27 15:14:36', 'user', 1),
(72, 15, 'Your report for \'wallet\' has been set to \'Missing\' by the admin.', '2025-11-27 15:30:23', 'user', 0),
(73, 1, 'Your report for \'Umbrella\' has been set to \'Open\' by the admin.', '2025-12-01 21:44:56', 'user', 1),
(74, 14, 'Your report for \'wallet\' has been set to \'Claimed\' by the admin.', '2025-12-01 21:56:30', 'user', 0),
(75, 11, 'Your report for \'rt\' has been set to \'Missing\' by the admin.', '2025-12-01 22:06:31', 'user', 1),
(76, 11, 'Your report for \'Phone\' has been set to \'Rejected\' by the admin.', '2025-12-01 22:06:33', 'user', 1),
(77, 15, 'Your report for \'wallet\' has been set to \'Claimed\' by the admin.', '2025-12-01 22:06:40', 'user', 0),
(78, 1, 'Your report for \'Umbrella\' has been set to \'Rejected\' by the admin.', '2025-12-01 22:06:43', 'user', 1),
(79, 11, 'Your report for \'rt\' has been set to \'Claimed\' by the admin.', '2025-12-01 22:06:53', 'user', 1),
(80, 11, 'Your report for \'umbrella\' has been set to \'Open\' by the admin.', '2025-12-01 22:12:52', 'user', 1),
(81, 1, 'Your report for \'Necklace\' has been set to \'Missing\' by the admin.', '2025-12-01 22:19:12', 'user', 1),
(82, 11, 'Your report for \'Apple watch\' has been set to \'Open\' by the admin.', '2025-12-01 22:25:23', 'user', 1),
(83, 11, 'Your report for \'Umbrella ngani!\' has been set to \'Missing\' by the admin.', '2025-12-01 22:27:18', 'user', 1),
(84, 1, '???? You have successfully submitted a claim request with verification details.', '2025-12-01 22:28:40', 'user', 1),
(85, 1, 'Your report for \'relo\' has been set to \'Missing\' by the admin.', '2025-12-02 10:34:27', 'user', 1),
(86, 1, 'Your report for \'Necklace\' has been set to \'Open\' by the admin.', '2025-12-02 10:48:39', 'user', 1),
(87, 1, 'Your report for \'chdc\' has been set to \'Open\' by the admin.', '2025-12-02 10:51:38', 'user', 1),
(88, 1, 'Your report for \'nbjb\' has been set to \'Rejected\' by the admin.', '2025-12-02 10:52:31', 'user', 1),
(89, 1, '???? Your claim for \'Apple watch\' has been set to Rejected by the admin.', '2025-12-02 22:39:06', 'user', 1),
(90, 1, 'Your report for \'relo\' has been set to \'Missing\' by the admin.', '2025-12-02 22:50:11', 'user', 1),
(91, 1, 'Your report for \'relo\' has been set to \'Missing\' by the admin.', '2025-12-02 22:50:20', 'user', 1),
(92, 1, 'Your report for \'relo\' has been set to \'Missing\' by the admin.', '2025-12-02 23:00:30', 'user', 1),
(93, 1, 'Your report for \'Black Wallet\' has been set to \'Missing\' by the admin.', '2025-12-02 23:01:28', 'user', 1),
(94, 1, 'Your report for \'relo\' has been set to \'Approved\' by the admin.', '2025-12-02 23:04:30', 'user', 1),
(95, 1, 'Your report for \'Black Wallet\' has been set to \'Approved\' by the admin.', '2025-12-02 23:04:36', 'user', 1),
(96, 1, 'Your report for \'Black Wallet\' has been set to \'Approved\' by the admin.', '2025-12-02 23:04:50', 'user', 1),
(97, 1, 'Your report for \'relo\' has been set to \'Approved\' by the admin.', '2025-12-02 23:04:51', 'user', 1),
(98, 1, 'Your report for \'relo\' has been set to \'Missing\' by the admin.', '2025-12-02 23:13:18', 'user', 1),
(99, 1, 'Your report for \'Black Wallet\' has been set to \'Rejected\' by the admin.', '2025-12-02 23:13:22', 'user', 1),
(100, 1, 'Your report for \'Payong sang rolls royce\' has been set to \'Missing\' by the admin.', '2025-12-02 23:14:37', 'user', 1),
(101, 16, 'Your report for \'tumbler\' has been set to \'Missing\' by the admin.', '2025-12-03 08:20:46', 'user', 1),
(102, 16, 'Your report for \'tumbler\' has been set to \'Claimed\' by the admin.', '2025-12-03 08:20:53', 'user', 1),
(103, 4, 'Your report for \'wallet\' has been set to \'Missing\' by the admin.', '2025-12-03 08:31:19', 'user', 0),
(104, 16, '???? You have successfully submitted a claim request with verification details.', '2025-12-03 08:33:51', 'user', 1),
(105, 16, '???? Your claim for \'chdc\' has been set to Approved by the admin.', '2025-12-03 08:36:51', 'user', 0),
(106, 1, 'Your report for \'basta\' has been set to \'Open\' by the admin.', '2025-12-04 12:31:34', 'user', 1),
(107, 11, '???? You have successfully submitted a claim request with verification details.', '2025-12-04 12:32:03', 'user', 1),
(108, 11, 'Your report for \'wallet\' has been set to \'Missing\' by the admin.', '2025-12-04 16:42:09', 'user', 0),
(109, 11, '???? Your claim for \'basta\' has been set to Approved by the admin.', '2025-12-04 16:45:39', 'user', 0),
(110, 11, 'Your report for \'wallet\' has been set to \'Claimed\' by the admin.', '2025-12-04 16:46:19', 'user', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_reported` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Missing','Open','Rejected','Claimed') NOT NULL DEFAULT 'Pending',
  `image` varchar(255) DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `user_id`, `item_name`, `description`, `category`, `type`, `location`, `date_reported`, `status`, `image`, `is_seen`) VALUES
(13, 1, 'Id', 'without id lace', 'Others', 'Found', 'Before Gym', '2025-10-25 00:00:00', 'Claimed', '1761394109_IMG_20241021_122219.jpg', 1),
(34, 12, 'Anak ko', 'basta nike', 'Electronics', 'Found', 'school shed', '2025-11-23 00:00:00', 'Rejected', '1763911258_Messenger_creation_FD3B1A21-5EFE-42C2-AC3C-6F0DA18C9BA7.jpeg', 1),
(35, 11, 'cat', 'calico, maingay, magulo, mausok, puno ng pangarap', 'Others', 'Lost', 'from ict building to canteen', '2025-11-27 00:00:00', 'Claimed', '1764204657_ahh.jpg', 1),
(36, 4, 'cat', 'calico', 'Others', 'Found', 'canteen', '2025-11-27 00:00:00', 'Claimed', '1764205046_ahh.jpg', 1),
(37, 11, 'Phone', 'cjxchxuvhdu', 'Electronics', 'Found', 'ICT BUILDING', '2025-11-27 00:00:00', 'Open', '1764227207_AGATGACHRIST.jpg', 1),
(38, 14, 'wallet', 'jschdc', 'Others', 'Lost', 'ICT BUILDING', '2025-11-24 00:00:00', 'Claimed', '1764227648_ahh.jpg', 1),
(39, 15, 'wallet', 'black', 'Others', 'Lost', 'guwa', '2025-11-26 00:00:00', 'Claimed', '1764228516_AGATGACHRIST.jpg', 0),
(40, 1, 'Umbrella', 'green na with iron crust, whew!', 'Others', 'Found', 'Amphitheater', '2025-12-01 00:00:00', 'Rejected', '1764596643_download (10).jfif', 1),
(41, 11, 'Phone', 'fghj', 'Electronics', 'Lost', 'fgd', '2025-12-01 00:00:00', 'Rejected', '1764597955_download (4).jfif', 1),
(42, 11, 'rt', 'fbdf', 'Clothing', 'Lost', 'Shed', '2025-12-01 00:00:00', 'Claimed', '1764597980_download (13).jfif', 1),
(43, 11, 'umbrella', 'Basta may design na hindi o maintindian , anteh! May dinausor pala ante, rarwwrrr!', 'Others', 'Found', 'Amphitheater', '2025-12-01 00:00:00', 'Open', '1764598358_download (20).jfif', 1),
(44, 1, 'Necklace', 'Gold heart Pendant.', 'Accessories', 'Lost', 'Gym.', '2025-12-01 00:00:00', 'Missing', '1764598686_download (28).jfif', 1),
(45, 11, 'Apple watch', 'Apple Watch', 'Accessories', 'Found', 'Amphi', '2026-01-02 00:00:00', 'Open', '1764599112_download (24).jfif', 1),
(46, 11, 'Umbrella ngani!', 'Rainbow!!!', 'Others', 'Lost', 'ICT Building!', '2025-12-01 00:00:00', 'Missing', '1764599221_download (18).jfif', 1),
(47, 1, 'relo', 'black', 'Accessories', 'Lost', 'canteen', '2025-12-02 00:00:00', 'Missing', '1764642829_ahh.jpg', 1),
(48, 1, 'Necklace', 'Gold', 'Accessories', 'Found', 'Gym', '2025-12-02 00:00:00', 'Open', '1764643698_download (26).jfif', 1),
(49, 1, 'chdc', 'csdfv', 'Clothing', 'Found', 'dcjsdv', '2025-12-02 00:00:00', 'Open', '1764643881_download (16).jfif', 1),
(50, 1, 'nbjb', 'cgcg', 'Electronics', 'Lost', 'n j', '2025-12-02 00:00:00', 'Rejected', '1764643942_download (1).jfif', 1),
(51, 1, 'Black Wallet', 'bgfb', 'Electronics', 'Lost', 'babaw sang bugnay tree', '2025-12-19 00:00:00', 'Rejected', '1764687668_download (21).jfif', 1),
(52, 1, 'Payong sang rolls royce', 'fbd', 'Others', 'Lost', 'fdbd', '2025-12-02 00:00:00', 'Missing', '1764688462_download (22).jfif', 1),
(53, 16, 'tumbler', 'pink', 'Others', 'Lost', 'ict building', '2025-12-03 00:00:00', 'Claimed', '1764721133_calli.jpg', 1),
(54, 4, 'wallet', 'orange', 'Accessories', 'Lost', 'guwa', '2025-12-03 00:00:00', 'Missing', '1764721829_black2.jpg', 0),
(55, 1, 'basta', 'csdvfv', 'Electronics', 'Found', 'fdvdf', '2025-12-04 00:00:00', 'Open', '1764822620_download (22).jfif', 1),
(56, 11, 'wallet', 'black', 'Others', 'Lost', 'ict buildng', '2025-12-04 00:00:00', 'Claimed', '1764837580_calli.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `course` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `user_role` enum('student','faculty_staff','visitor','admin') DEFAULT 'student',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `created_at`, `course`, `year_level`, `section`, `user_role`, `is_active`, `profile_pic`) VALUES
(1, 'Meryl Rapista', 'merylrapista9@gmail.com', '$2y$10$75We4DaQ7H2ozm7o/M/iF.kGUrDW8uJCHqD.DhcoyULAWE48wAcpu', '', '2025-10-18 15:18:21', 'BSICT', '1st Year', 'A', '', 1, '1_mewo.jpg'),
(4, 'Kenardo Sumanib', 'merylrapista229@gmail.com', '$2y$10$sZnKvwxhAp.Fvl0lAbiRQOjU.sZRUSI2BmH2wUhRc2IqXoKcOmG5u', '', '2025-10-19 12:10:14', 'SOCSTUD', '2nd Year', 'A', 'student', 1, '4_IMG_20241021_122219.jpg'),
(9, 'Admin User', 'admin@gmail.com', '$2y$10$OpOuiLxSDf1/U/h78ic5pur0rvHokj5JQ.DkDd5kmZ5Qjg.im5NzG', 'admin', '2025-10-25 08:22:13', NULL, NULL, NULL, NULL, 1, NULL),
(10, 'Mariel Prias Rapista', 'elroyalheirolexis@gmail.com', '$2y$10$9nlDp6lBcV5ELmAL/Ba3n.dBM5Wq4quW5W1dfagGxlqdc4N04bIH2', '', '2025-11-22 14:33:08', '', '2nd Year', 'A', 'student', 1, '10_f4bbb3b8-694f-412a-ad65-5d8f03055509.jfif'),
(11, 'Faith Carisma', 'carisma@gmail.com', '$2y$10$sd9w75GPQXZ/e7w77GPTU.kC/HFKSUbbMXGnkizjcEEOJT22/PhJW', '', '2025-11-23 14:15:40', 'BSICT', '2nd Year', 'A', 'student', 1, '11_WIN_20251011_10_16_17_Pro.jpg'),
(12, 'Katelin', 'cathlene@gmail.com', '$2y$10$kv3uUvoJmiGnf5A/lylZnuw9CL/xgh37uZN00bg4Gh3zn/bNd6fXq', 'user', '2025-11-23 15:03:55', 'BSED', '2nd Year', 'A', 'student', 1, NULL),
(13, 'Dharyl Rapista', 'dharyl@gmail.com', '$2y$10$YwDm5ZmwLHKXg.H.3bNybuT0.J/2tYntthbJLwToix837WLpcfIE2', 'user', '2025-11-26 12:25:42', 'CCJE', '4th Year', 'A', 'student', 1, NULL),
(14, 'julieme', 'julie@gmail.com', '$2y$10$FQ/qJkTCbgy9nu.ebek/8.dQkauNT6aMYrk8cRjh50c284J8vatQy', 'user', '2025-11-27 07:12:48', '', '', 'a', 'student', 1, NULL),
(15, 'gina jane lego', 'gina@gmail.com', '$2y$10$gKstdYvyzRp1SDGElFJ84umBdGJYEQvjARmyguo3BtZPqomTIo1Ey', 'user', '2025-11-27 07:27:04', '', '', 'a', 'student', 1, NULL),
(16, 'erra roncal', 'erra@gmail.com', '$2y$10$m7xhjieeSXWcohd9jlVfC.vBgIMPrA3C/87Cj7gwgRX2OShqTWRSu', 'user', '2025-12-03 00:17:08', 'BSICT', '3rd Year', 'a', 'student', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `claimant_user_id` (`claimant_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
