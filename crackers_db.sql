-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2025 at 03:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crackers_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `admin_user` varchar(100) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `admin_user`, `action`, `created_at`) VALUES
(1, 'admin', 'Added product: ', '2025-08-27 11:21:11'),
(2, 'admin', 'Added product: bilal', '2025-08-27 11:24:05'),
(3, 'admin', 'Added product: Classic Crackers Pack', '2025-08-27 11:26:07'),
(4, 'admin', 'Added product: Classic Crackers Pack', '2025-08-27 11:30:06'),
(5, 'admin', 'Added product: Classic Crackers Pack', '2025-08-27 11:30:59'),
(6, 'admin', 'Added product: Classic Crackers Pack', '2025-08-27 11:31:55'),
(7, 'admin', 'Added product: Vaishali', '2025-08-27 11:33:24'),
(8, 'admin', 'Added product: gyuhguyguy', '2025-08-27 11:35:25'),
(9, 'admin', 'Deleted product: gyuhguyguy (ID 29)', '2025-08-27 12:14:10'),
(10, 'admin', 'Updated product: Harish (ID 18)', '2025-08-27 12:14:34'),
(11, '', 'Admin logged in', '2025-08-28 04:53:29'),
(12, '', 'Admin logged in', '2025-08-28 04:54:08'),
(13, '', 'Admin logged in', '2025-08-28 04:54:24'),
(14, '', 'Admin logged in', '2025-08-28 04:54:24'),
(15, '', 'Admin logged in', '2025-08-28 04:54:24'),
(16, '', 'Admin logged in', '2025-08-28 04:55:18'),
(17, '', 'Admin logged in', '2025-08-28 04:55:20'),
(18, '', 'Admin logged in', '2025-08-28 06:25:24'),
(19, '', 'Admin logged in', '2025-08-28 06:25:25'),
(20, '', 'Admin logged in', '2025-08-28 07:22:05'),
(21, '', 'Admin logged in', '2025-08-28 07:22:10'),
(22, '', 'Admin logged in', '2025-08-28 07:31:54'),
(23, '', 'Admin logged in', '2025-08-28 09:37:33'),
(24, '', 'Admin logged in', '2025-08-28 09:37:35'),
(25, 'admin', 'Added product: Classic Crackers Pack', '2025-08-28 09:45:38'),
(26, 'admin', 'Added product: Vaishali', '2025-08-28 09:55:04'),
(27, 'admin', 'Updated product: Vaishali (ID 31)', '2025-08-28 09:56:16'),
(28, 'admin', 'Updated product: Classic Crackers Pack (ID 30)', '2025-08-28 09:57:01'),
(29, 'admin', 'Updated product: Vaishali (ID 31)', '2025-08-28 09:58:04'),
(30, 'admin', 'Added product: Chakri', '2025-08-28 09:59:23'),
(31, 'admin', 'Added product: Chakri', '2025-08-28 10:00:04'),
(32, 'admin', 'Added product: Chakri', '2025-08-28 10:00:08'),
(33, 'admin', 'Deleted product: Sparkler (ID 15)', '2025-08-28 10:03:24'),
(34, 'admin', 'Deleted product: Ground Wheel (ID 14)', '2025-08-28 10:03:26'),
(35, 'admin', 'Deleted product: Classic Crackers Pack (ID 16)', '2025-08-28 10:03:28'),
(36, 'admin', 'Deleted product: bilal (ID 17)', '2025-08-28 10:03:29'),
(37, 'admin', 'Deleted product: Harish (ID 18)', '2025-08-28 10:03:32'),
(38, 'admin', 'Deleted product: bilal (ID 19)', '2025-08-28 10:03:33'),
(39, 'admin', 'Deleted product: Classic Crackers Pack (ID 20)', '2025-08-28 10:03:35'),
(40, 'admin', 'Deleted product: Classic Crackers Pack (ID 21)', '2025-08-28 10:03:37'),
(41, 'admin', 'Deleted product: Classic Crackers Pack (ID 30)', '2025-08-28 10:03:39'),
(42, 'admin', 'Deleted product: Chakri (ID 32)', '2025-08-28 10:03:41'),
(43, 'admin', 'Deleted product: Chakri (ID 33)', '2025-08-28 10:03:44'),
(44, 'admin', 'Deleted product: Chakri (ID 34)', '2025-08-28 10:03:45'),
(45, 'admin', 'Added product: Classic Crackers Pack', '2025-08-28 10:06:14'),
(46, 'admin', 'Added product: Classic Crackers Pack', '2025-08-28 10:15:40'),
(47, 'admin', 'Added product: Rockets', '2025-08-28 11:10:03'),
(48, 'admin', 'Updated product: Rockets (ID 37)', '2025-08-28 11:10:25'),
(49, 'admin', 'Deleted product: Rockets (ID 37)', '2025-08-28 11:10:31'),
(50, 'admin', 'Added product: Classic Crackers Pack', '2025-08-28 11:11:02'),
(51, 'admin', 'Updated product: Classic Crackers Pack (ID 38)', '2025-08-28 11:11:28'),
(52, 'admin', 'Added product: Rocket', '2025-08-28 11:12:04'),
(53, 'admin', 'Deleted product: Classic Crackers Pack (ID 38)', '2025-08-28 11:12:06'),
(54, '', 'Admin logged in', '2025-08-28 12:22:42'),
(55, '', 'Admin logged in', '2025-08-28 12:22:51'),
(56, '', 'Admin logged in', '2025-08-28 12:22:54'),
(57, '', 'Admin logged in', '2025-08-28 12:22:56'),
(58, '', 'Admin logged in', '2025-08-28 12:23:02'),
(59, '', 'Admin logged in', '2025-08-28 12:23:04'),
(60, '', 'Admin logged in', '2025-08-28 12:23:05'),
(61, '', 'Admin logged in', '2025-08-29 05:50:26'),
(62, '', 'Admin logged in', '2025-08-29 05:50:45'),
(63, '', 'Admin logged in', '2025-08-29 05:50:46'),
(64, '', 'Admin logged in', '2025-08-29 06:11:59'),
(65, '', 'Admin logged in', '2025-08-29 06:12:46'),
(66, '', 'Admin logged in', '2025-08-29 06:15:06'),
(67, '', 'Admin logged in', '2025-08-29 06:15:06'),
(68, '', 'Admin logged in', '2025-08-29 06:15:06'),
(69, '', 'Admin logged in', '2025-08-29 09:25:33'),
(70, '', 'Admin logged in', '2025-08-29 09:25:34'),
(71, 'admin', 'Deleted product: Classic Crackers Pack (ID 35)', '2025-08-29 09:32:02'),
(72, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 09:35:44'),
(73, 'admin', 'Added product: Chakri', '2025-08-29 10:20:01'),
(74, 'admin', 'Added product: Chakri', '2025-08-29 10:23:29'),
(75, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:39:06'),
(76, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:43:00'),
(77, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:43:36'),
(78, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:45:33'),
(79, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:46:19'),
(80, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:53:18'),
(81, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 10:54:46'),
(82, 'admin', 'Deleted product: Classic Crackers Pack (ID 40)', '2025-08-29 10:55:17'),
(83, 'admin', 'Deleted product: Classic Crackers Pack (ID 42)', '2025-08-29 10:55:19'),
(84, 'admin', 'Deleted product: Chakri (ID 41)', '2025-08-29 10:55:21'),
(85, 'admin', 'Added product: gyuhguyguy', '2025-08-29 11:29:45'),
(86, 'admin', 'Added product: gyuhguyguy', '2025-08-29 11:30:08'),
(87, 'admin', 'Added product: gyuhguyguy', '2025-08-29 11:31:30'),
(88, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 11:33:40'),
(89, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 11:34:32'),
(90, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 11:34:36'),
(91, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 11:37:30'),
(92, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 11:38:09'),
(93, 'admin', 'Deleted product: gyuhguyguy (ID 45)', '2025-08-29 11:38:15'),
(94, 'admin', 'Added product: Classic Crackers Pack', '2025-08-29 12:11:01'),
(95, '', 'Admin logged in', '2025-08-29 12:13:35'),
(96, '', 'Admin logged in', '2025-08-29 12:13:36'),
(97, '', 'Admin logged in', '2025-08-30 06:20:04'),
(98, '', 'Admin logged in', '2025-08-30 06:20:04'),
(99, '', 'Admin logged in', '2025-08-30 06:20:17'),
(100, '', 'Admin logged in', '2025-08-30 07:28:44'),
(101, '', 'Admin logged in', '2025-08-30 07:28:59'),
(102, 'admin', 'Updated product: Classic Crackers Pack (ID 36)', '2025-08-30 07:30:14'),
(103, 'admin', 'Deleted product: Classic Crackers Pack (ID 36)', '2025-08-30 07:34:11'),
(104, 'admin', 'Deleted product: Classic Crackers Pack (ID 48)', '2025-08-30 07:34:14'),
(105, 'admin', 'Deleted product: Classic Crackers Pack (ID 47)', '2025-08-30 07:34:16'),
(106, 'admin', 'Deleted product: Classic Crackers Pack (ID 46)', '2025-08-30 07:34:18'),
(107, 'admin', 'Deleted product: Classic Crackers Pack (ID 44)', '2025-08-30 07:34:19'),
(108, 'admin', 'Deleted product: Classic Crackers Pack (ID 43)', '2025-08-30 07:34:21'),
(109, 'admin', 'Deleted product: Rocket (ID 39)', '2025-08-30 07:36:06'),
(110, 'admin', 'Added product: Flower Pots (Anaar)', '2025-08-30 07:50:28'),
(111, '', 'Admin logged in', '2025-08-30 09:35:40'),
(112, '', 'Admin logged in', '2025-08-30 09:35:43'),
(113, 'admin', 'Added product: Chakri (Ground Spinner)', '2025-08-30 09:40:02'),
(114, 'admin', 'Added product: Sky Shot (12 Shots)', '2025-08-30 09:45:38'),
(115, '', 'Admin logged in', '2025-08-30 09:50:51'),
(116, '', 'Admin logged in', '2025-08-30 09:51:04'),
(117, '', 'Admin logged in', '2025-08-30 10:32:30'),
(118, '', 'Admin logged in', '2025-08-30 10:32:31'),
(119, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 11:50:06'),
(120, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 11:50:45'),
(121, 'admin', 'Deleted product: Classic Crackers Pack (ID 52)', '2025-08-30 11:51:17'),
(122, 'admin', 'Deleted product: Classic Crackers Pack (ID 53)', '2025-08-30 11:57:29'),
(123, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 11:58:44'),
(124, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 11:59:46'),
(125, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 12:01:24'),
(126, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 12:01:32'),
(127, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 12:03:36'),
(128, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 12:04:53'),
(129, 'admin', 'Deleted product: Classic Crackers Pack (ID 54)', '2025-08-30 12:18:59'),
(130, '', 'Admin logged in', '2025-08-30 13:09:32'),
(131, '', 'Admin logged in', '2025-08-30 13:09:32'),
(132, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 13:12:52'),
(133, 'admin', 'Added product: Classic Crackers Pack', '2025-08-30 13:13:04'),
(134, 'admin', 'Updated product: Classic Crackers Pack (ID 55)', '2025-09-01 05:48:55'),
(135, 'admin', 'Deleted product: Flower Pots (Anaar) (ID 49)', '2025-09-01 05:59:41'),
(136, 'admin', 'Deleted product: Chakri (Ground Spinner) (ID 50)', '2025-09-01 05:59:44'),
(137, 'admin', 'Deleted product: Sky Shot (12 Shots) (ID 51)', '2025-09-01 05:59:47'),
(138, 'admin', 'Deleted product: Classic Crackers Pack (ID 55)', '2025-09-01 06:00:10'),
(139, 'admin', 'Deleted product: Classic Crackers Pack (ID 56)', '2025-09-01 06:00:13'),
(140, 'admin', 'Added product: Sky Shot Crackers', '2025-09-01 06:01:26'),
(141, 'admin', 'Added product: Sky Shot Crackers', '2025-09-01 06:03:46'),
(142, 'admin', 'Admin logged in', '2025-09-01 06:04:22'),
(143, 'admin', 'Admin logged in', '2025-09-01 06:04:27'),
(144, 'admin', 'Admin logged in', '2025-09-01 06:14:54'),
(145, 'admin', 'Admin logged in', '2025-09-01 06:14:58'),
(146, 'admin', 'Updated product: Sky Shot Crackers (ID 57)', '2025-09-01 06:24:35'),
(147, 'admin', 'Added product: Sparkler', '2025-09-01 06:59:30'),
(148, 'admin', 'Added product: Flower Pots (Anaar)', '2025-09-01 07:06:36'),
(149, 'admin', 'Updated product: Sparklers (Phuljhadi) (ID 58)', '2025-09-01 07:06:53'),
(150, 'admin', 'Added product: Rockets', '2025-09-01 07:12:53'),
(151, 'admin', 'Added product: Atom Bomb', '2025-09-01 07:15:09'),
(152, 'admin', 'Added product: 10000 Wali (Ladi)', '2025-09-01 07:21:42'),
(153, 'admin', 'Added product: Chakri (Ground Spinner)', '2025-09-01 07:25:41'),
(154, 'admin', 'Updated product: Chakri (Ground Spinner) (ID 63)', '2025-09-01 07:25:53'),
(155, 'admin', 'Updated product: Chakri (Ground Spinner) (ID 63)', '2025-09-01 07:25:59'),
(156, 'admin', 'Admin logged in', '2025-09-01 07:49:14'),
(157, 'admin', 'Admin logged in', '2025-09-01 07:49:18'),
(158, 'admin', 'Updated product: Chakri (Ground Spinner) (ID 63)', '2025-09-01 07:58:46'),
(159, 'admin', 'Updated product: 10000 Wali (Ladi) (ID 62)', '2025-09-01 07:58:57'),
(160, 'admin', 'Updated product: Atom Bomb (ID 61)', '2025-09-01 07:59:05'),
(161, 'admin', 'Updated product: Flower Pots (Anaar) (ID 59)', '2025-09-01 07:59:12'),
(162, 'admin', 'Updated product: 10000 Wali (Ladii) (ID 62)', '2025-09-01 08:03:46'),
(163, '', 'Admin logged in', '2025-09-02 05:31:21'),
(164, '', 'Admin logged in', '2025-09-02 05:31:22'),
(165, '', 'Admin logged in', '2025-09-02 05:31:32'),
(166, '', 'Admin logged in', '2025-09-02 05:31:32'),
(167, 'admin', 'Admin logged in', '2025-09-02 06:44:06'),
(168, 'admin', 'Admin logged in', '2025-09-02 06:44:08'),
(169, 'admin', 'Admin logged in', '2025-09-02 09:27:15'),
(170, 'admin', 'Admin logged in', '2025-09-02 09:27:17'),
(171, 'admin', 'Admin logged in', '2025-09-03 06:50:45'),
(172, 'admin', 'Admin logged in', '2025-09-03 06:50:48'),
(173, '', 'Admin logged in', '2025-09-04 05:24:52'),
(174, '', 'Admin logged in', '2025-09-04 05:25:15'),
(175, '', 'Admin logged in', '2025-09-04 05:25:17'),
(176, '', 'Admin logged in', '2025-10-01 08:52:03'),
(177, '', 'Admin logged in', '2025-10-01 08:52:22'),
(178, '', 'Admin logged in', '2025-10-01 09:14:57'),
(179, '', 'Admin logged in', '2025-10-01 09:15:13'),
(180, 'admin', 'Added product: xyz', '2025-10-01 09:22:08'),
(181, 'admin', 'Updated product: xyz (ID 64)', '2025-10-01 09:22:38'),
(182, '', 'Admin logged in', '2025-10-01 10:07:27'),
(183, '', 'Admin logged in', '2025-10-01 10:11:40'),
(184, '', 'Admin logged in', '2025-10-01 10:11:55'),
(185, '', 'Admin logged in', '2025-10-01 10:12:07'),
(186, 'admin', 'Added product: uiopuyiiouo', '2025-10-01 10:23:35'),
(187, 'admin', 'Added product: ghghghghgh', '2025-10-01 10:56:02'),
(188, 'admin', 'Added product: ghghghghgh', '2025-10-01 10:59:19'),
(189, 'admin', 'Deleted product: ghghghghgh (ID 67)', '2025-10-01 10:59:34'),
(190, 'admin', 'Deleted product: xyz (ID 64)', '2025-10-01 10:59:39'),
(191, 'admin', 'Deleted product: uiopuyiiouo (ID 65)', '2025-10-01 10:59:43'),
(192, 'admin', 'Deleted product: ghghghghgh (ID 66)', '2025-10-01 10:59:47'),
(193, 'admin', 'Added product: tytytytt', '2025-10-01 11:00:48'),
(194, 'admin', 'Added product: gghgj', '2025-10-01 11:05:28'),
(195, 'admin', 'Deleted product: tytytytt (ID 68)', '2025-10-01 11:05:47'),
(196, '', 'Admin logged in', '2025-10-01 11:17:42'),
(197, 'admin', 'Deleted product: gghgj (ID 69)', '2025-10-01 11:31:16'),
(198, '', 'Admin logged in', '2025-10-01 11:53:10'),
(199, '', 'Admin logged in', '2025-10-01 11:53:20'),
(200, '', 'Admin logged in', '2025-10-01 11:53:31'),
(201, 'admin', 'Added product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc. ', '2025-10-01 11:56:30'),
(202, 'admin', 'Updated product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc.  (ID 70)', '2025-10-01 12:08:57'),
(203, 'admin', 'Deleted product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc.  (ID 70)', '2025-10-01 12:11:07'),
(204, 'admin', 'Added product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc. ', '2025-10-01 12:13:00'),
(205, 'admin', 'Updated product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc.  (ID 71)', '2025-10-01 12:17:02'),
(206, 'admin', 'Updated product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc.  (ID 71)', '2025-10-01 12:28:24'),
(207, '', 'Admin logged in', '2025-10-02 16:14:39'),
(208, '', 'Admin logged in', '2025-10-02 16:15:15'),
(209, 'admin', 'Admin logged in', '2025-10-02 16:15:48'),
(210, 'admin', 'Admin logged in', '2025-10-02 16:16:04'),
(211, '', 'Admin logged in', '2025-10-02 16:18:57'),
(212, '', 'Admin logged in', '2025-10-02 16:19:06'),
(213, '', 'Admin logged in', '2025-10-03 04:28:15'),
(214, '', 'Admin logged in', '2025-10-03 04:37:47'),
(215, '', 'Admin logged in', '2025-10-03 04:38:04'),
(216, 'admin', 'Added product: MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc. ', '2025-10-03 05:31:02'),
(217, 'admin', 'Updated product: MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc.  (ID 72)', '2025-10-03 05:31:44'),
(218, 'admin', 'Added product: STANDARD JUMPING FROG( जम्पिंग फ्रॉग  )  EACH PACK OF 6 Pc. ', '2025-10-03 05:49:05'),
(219, 'admin', 'Updated product: STANDARD JUMPING FROG( जम्पिंग फ्रॉग  )  EACH PACK OF 6 Pc.  (ID 73)', '2025-10-03 10:27:23'),
(220, 'admin', 'Updated product: MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc.  (ID 72)', '2025-10-03 10:27:49'),
(221, 'admin', 'Updated product: MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc.  (ID 72)', '2025-10-03 10:28:09'),
(222, 'admin', 'Updated product: VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc.  (ID 71)', '2025-10-03 10:28:36'),
(223, '', 'Admin logged in', '2025-10-03 11:35:40'),
(224, '', 'Admin logged in', '2025-10-03 12:24:57'),
(225, '', 'Admin logged in', '2025-10-03 12:25:02'),
(226, '', 'Admin logged in', '2025-10-03 12:25:12'),
(227, '', 'Admin logged in', '2025-10-04 10:08:42'),
(228, '', 'Admin logged in', '2025-10-04 10:09:16'),
(229, '', 'Admin logged in', '2025-10-04 10:40:13'),
(230, '', 'Admin logged in', '2025-10-04 10:40:29'),
(231, '', 'Admin logged in', '2025-10-04 10:40:34'),
(232, '', 'Admin logged in', '2025-10-04 10:40:57'),
(233, '', 'Admin logged in', '2025-10-04 10:41:01'),
(234, '', 'Admin logged in', '2025-10-04 10:41:09'),
(235, '', 'Admin logged in', '2025-10-04 12:24:39'),
(236, '', 'Admin logged in', '2025-10-04 12:24:50');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `entity_type`, `entity_id`, `message`, `ip`, `user_agent`, `created_at`) VALUES
(1, NULL, 'login', 'admin', 0, 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:38'),
(2, NULL, 'create', 'product', 1, 'Added product: ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:38'),
(3, NULL, 'update', 'product', 0, 'Updated product: ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:38'),
(4, NULL, 'delete', 'product', 0, 'Deleted product id: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:38'),
(5, NULL, 'login', 'admin', 0, 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:53'),
(6, NULL, 'create', 'product', 5, 'Added product: ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:53'),
(7, NULL, 'update', 'product', 0, 'Updated product: ', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:53'),
(8, NULL, 'delete', 'product', 0, 'Deleted product id: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 12:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdf_file` longblob DEFAULT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending',
  `actual_deducted` tinyint(4) DEFAULT 0,
  `referred_person` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `name`, `email`, `phone`, `address`, `total`, `created_at`, `pdf_file`, `status`, `actual_deducted`, `referred_person`) VALUES
(68, 'INV-606715', 'Vaishali Narsinghani', 'gfgfg@gmail.com', '9874569874', 'House no. 16', 16000.00, '2025-09-01 07:47:42', NULL, 'Paid', 0, '7894561237'),
(80, 'INV-305872', 'Vaishali Narsinghani', NULL, '07089288544', 'Shamgarh\r\nSurvey No.210,Village Sarangpur', 3300.00, '2025-10-03 10:28:58', NULL, 'Pending', 0, '789456789'),
(81, 'INV-802857', 'ytrttryrry', NULL, '7894567897', 'Shamgarh\r\nSurvey No.210,Village Sarangpur', 1257.50, '2025-10-03 10:50:31', NULL, 'Pending', 0, '789456136'),
(82, 'INV-515722', 'Vaishali Narsinghani', NULL, '07089288544', 'Shamgarh\r\nSurvey No.210,Village Sarangpur', 212.50, '2025-10-03 11:03:27', NULL, 'Paid', 0, 'ftfyrytry');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_name`, `qty`, `price`, `subtotal`) VALUES
(32, 68, '10000 Wali (Ladi)', 8, 2000.00, 16000.00),
(62, 80, 'MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc. ', 15, 195.00, 2925.00),
(63, 80, 'VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc. ', 1, 162.50, 162.50),
(64, 80, 'STANDARD JUMPING FROG( जम्पिंग फ्रॉग  )  EACH PACK OF 6 Pc. ', 1, 212.50, 212.50),
(65, 82, 'STANDARD JUMPING FROG( जम्पिंग फ्रॉग  )  EACH PACK OF 6 Pc. ', 1, 212.50, 212.50);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `invoice_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `online_stock` int(11) NOT NULL DEFAULT 0,
  `actual_stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `description`, `image1`, `image2`, `image3`, `thumbnail`, `video`, `featured`, `online_stock`, `actual_stock`) VALUES
(71, 'VANITHA PHOTO FLASH ( फोटो फ्लैश )  EACH PACK OF 10 Pc. ', 'fancy', 162.50, 'MRP: ₹̶2̶5̶0̶0̶\r\nDISCOUNT : 94%\r\nOffer Price: ₹162.5 / UNIT', 'uploads/1759320780_1_WhatsApp Image 2025-09-08 at 3.32.09 PM (18).jpeg', '', '', 'uploads/1759487316_WhatsApp Image 2025-10-03 at 10.49.58 AM (1).jpeg', 'https://www.youtube.com/shorts/BBWOIcwL-OI', 1, 448, 449),
(72, 'MERCURY COLOUR CHANGING BUTTER FLY(रंग बदलने वाली तितली )  EACH PACK OF 10 Pc. ', 'fancy', 195.00, 'MRP:   ₹̶5̶8̶0̶  \r\nDISCOUNT : 66%\r\nOffer Price: ₹195 / UNIT', 'uploads/1759469462_1_WhatsApp Image 2025-10-03 at 10.50.02 AM (2).jpeg', 'uploads/1759469462_2_102.webp', '', 'uploads/1759487289_WhatsApp Image 2025-09-08 at 12.29.03 PM.jpeg', 'uploads/1759487269_WhatsApp Image 2025-09-08 at 12.29.03 PM.jpeg', 1, 405, 420),
(73, 'STANDARD JUMPING FROG( जम्पिंग फ्रॉग  )  EACH PACK OF 6 Pc. ', 'fancy', 212.50, ' MRP: ₹̶7̶5̶8̶  \r\nDISCOUNT : 72%\r\nOffer Price: ₹212.5/ UNIT\r\n', 'uploads/1759470545_1_WhatsApp Image 2025-10-03 at 10.49.55 AM (1).jpeg', '', '', 'uploads/1759487243_WhatsApp Image 2025-09-08 at 4.05.00 PM.jpeg', 'https://www.youtube.com/shorts/3Mso8orr77I', 1, 118, 119);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_activity_created` (`created_at`),
  ADD KEY `idx_admin_activity_admin` (`admin_id`),
  ADD KEY `idx_admin_activity_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
