-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 04:46 PM
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
-- Database: `welcome_guide`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_type` varchar(255) NOT NULL DEFAULT 'admin',
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(255) DEFAULT NULL,
  `actor_email` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `severity` varchar(255) NOT NULL DEFAULT 'info',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `booking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `actor_type`, `actor_id`, `actor_name`, `actor_email`, `action`, `description`, `icon`, `module`, `severity`, `ip_address`, `user_agent`, `metadata`, `old_values`, `new_values`, `property_id`, `booking_id`, `subject_type`, `subject_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'admin', NULL, NULL, NULL, 'demo_created', 'Demo portal seeded for Lumina Hotel & Residences.', 'sparkles', NULL, 'info', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 05:36:26', '2026-05-14 05:36:26'),
(2, 'admin', NULL, NULL, NULL, 'property_ready', 'Lumina property content and guide categories are ready for review.', 'properties', NULL, 'info', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'App\\Models\\Property', 1, NULL, '2026-05-14 05:36:26', '2026-05-14 05:36:26'),
(3, 'admin', NULL, NULL, NULL, 'guest_url_ready', 'Demo guest secure check-in URL is ready to copy.', 'copy', NULL, 'info', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 05:36:26', '2026-05-14 05:36:26'),
(4, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 06:07:47', '2026-05-14 06:07:47'),
(5, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 06:07:51', '2026-05-14 06:07:51'),
(6, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 06:07:55', '2026-05-14 06:07:55'),
(7, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 06:07:59', '2026-05-14 06:07:59'),
(8, 'system', NULL, NULL, NULL, 'test_log', 'Enterprise upgrade test log entry.', 'auth', 'auth', 'info', '127.0.0.1', 'Symfony', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 06:08:30', '2026-05-14 06:08:30'),
(9, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 06:14:11', '2026-05-14 06:14:11'),
(10, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:14:15', '2026-05-14 06:14:15'),
(11, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:16:26', '2026-05-14 06:16:26'),
(12, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:16:42', '2026-05-14 06:16:42'),
(13, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:16:46', '2026-05-14 06:16:46'),
(14, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:19:45', '2026-05-14 06:19:45'),
(15, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:23:47', '2026-05-14 06:23:47'),
(16, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:24:30', '2026-05-14 06:24:30'),
(17, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:25:19', '2026-05-14 06:25:19'),
(18, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:25:33', '2026-05-14 06:25:33'),
(19, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:29:15', '2026-05-14 06:29:15'),
(20, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:29:34', '2026-05-14 06:29:34'),
(21, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:32:16', '2026-05-14 06:32:16'),
(22, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:33:21', '2026-05-14 06:33:21'),
(23, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:38:25', '2026-05-14 06:38:25'),
(24, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:38:38', '2026-05-14 06:38:38'),
(25, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:40:06', '2026-05-14 06:40:06'),
(26, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:40:11', '2026-05-14 06:40:11'),
(27, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:40:16', '2026-05-14 06:40:16'),
(28, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:40:24', '2026-05-14 06:40:24'),
(29, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:40:28', '2026-05-14 06:40:28'),
(30, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:42:57', '2026-05-14 06:42:57'),
(31, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:44:58', '2026-05-14 06:44:58'),
(32, 'admin', 1, 'Site Admin', 'admin@example.com', 'tour_restarted', 'Site Admin restarted the admin tour.', 'users', 'users', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 06:45:09', '2026-05-14 06:45:09'),
(33, 'admin', 1, 'Site Admin', 'admin@example.com', 'tour_completed', 'Site Admin completed the admin onboarding tour.', 'users', 'users', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 06:45:34', '2026-05-14 06:45:34'),
(34, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:53:01', '2026-05-14 06:53:01'),
(35, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:54:50', '2026-05-14 06:54:50'),
(36, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:55:19', '2026-05-14 06:55:19'),
(37, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 06:55:34', '2026-05-14 06:55:34'),
(38, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:02:19', '2026-05-14 07:02:19'),
(39, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 07:03:59', '2026-05-14 07:03:59'),
(40, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:04:30', '2026-05-14 07:04:30'),
(41, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:12:12', '2026-05-14 07:12:12'),
(42, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:23:04', '2026-05-14 07:23:04'),
(43, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:30:51', '2026-05-14 07:30:51'),
(44, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:31:40', '2026-05-14 07:31:40'),
(45, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:32:56', '2026-05-14 07:32:56'),
(46, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:37:04', '2026-05-14 07:37:04'),
(47, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:37:13', '2026-05-14 07:37:13'),
(48, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:37:43', '2026-05-14 07:37:43'),
(49, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:39:03', '2026-05-14 07:39:03'),
(50, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:39:11', '2026-05-14 07:39:11'),
(51, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:41:45', '2026-05-14 07:41:45'),
(52, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:41:52', '2026-05-14 07:41:52'),
(53, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:41:58', '2026-05-14 07:41:58'),
(54, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:42:04', '2026-05-14 07:42:04'),
(55, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:42:09', '2026-05-14 07:42:09'),
(56, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:42:15', '2026-05-14 07:42:15'),
(57, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:42:20', '2026-05-14 07:42:20'),
(58, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:48:25', '2026-05-14 07:48:25'),
(59, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:48:31', '2026-05-14 07:48:31'),
(60, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:48:42', '2026-05-14 07:48:42'),
(61, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:48:48', '2026-05-14 07:48:48'),
(62, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:49:00', '2026-05-14 07:49:00'),
(63, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:49:07', '2026-05-14 07:49:07'),
(64, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:49:40', '2026-05-14 07:49:40'),
(65, 'guest', NULL, 'Jordan Taylor', NULL, 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 07:49:47', '2026-05-14 07:49:47'),
(66, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 07:49:51', '2026-05-14 07:49:51'),
(67, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:49:56', '2026-05-14 07:49:56'),
(68, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'photo_id_uploaded', 'Guest Jordan Taylor submitted photo ID and pre-arrival details.', 'photo_id', 'photo_id', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"email\":\"jinuilyas63@gmail.com\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:50:30', '2026-05-14 07:50:30'),
(69, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: arrival).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"arrival\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:50:30', '2026-05-14 07:50:30'),
(70, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: arrival).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"arrival\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:58:44', '2026-05-14 07:58:44'),
(71, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: arrival).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"arrival\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:58:58', '2026-05-14 07:58:58'),
(72, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: arrival).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"arrival\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 07:59:17', '2026-05-14 07:59:17'),
(73, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:02:17', '2026-05-14 08:02:17'),
(74, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:02:36', '2026-05-14 08:02:36'),
(75, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 08:02:49', '2026-05-14 08:02:49'),
(76, 'admin', 1, 'Site Admin', 'admin@example.com', 'tour_restarted', 'Site Admin restarted the admin tour.', 'users', 'users', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 08:02:52', '2026-05-14 08:02:52'),
(77, 'admin', 1, 'Site Admin', 'admin@example.com', 'tour_completed', 'Site Admin completed the admin onboarding tour.', 'users', 'users', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 08:02:57', '2026-05-14 08:02:57'),
(78, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: identity) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"identity\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:19', '2026-05-14 08:04:19'),
(79, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: identity) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"identity\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:22', '2026-05-14 08:04:22'),
(80, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: waiting) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"waiting\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:25', '2026-05-14 08:04:25'),
(81, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: arrival) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"arrival\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:27', '2026-05-14 08:04:27'),
(82, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: arrival) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"arrival\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:30', '2026-05-14 08:04:30'),
(83, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: arrival) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"arrival\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:33', '2026-05-14 08:04:33'),
(84, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:35', '2026-05-14 08:04:35'),
(85, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:38', '2026-05-14 08:04:38'),
(86, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: checkout) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"preview_state\":\"checkout\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:41', '2026-05-14 08:04:41'),
(87, 'admin', 1, 'Site Admin', 'admin@example.com', 'manual_checkin_override', 'Site Admin manually checked in Jordan Taylor (LUMINA-DEMO).', 'auth', 'auth', 'warning', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"guest_name\":\"Jordan Taylor\",\"override_by\":\"Site Admin\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:04:46', '2026-05-14 08:04:46'),
(88, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:47', '2026-05-14 08:04:47'),
(89, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:49', '2026-05-14 08:04:49'),
(90, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Restaurants.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Restaurants\",\"category_id\":5}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:52', '2026-05-14 08:04:52'),
(91, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Bars.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Bars\",\"category_id\":6}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:54', '2026-05-14 08:04:54'),
(92, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Parking.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Parking\",\"category_id\":7}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:57', '2026-05-14 08:04:57'),
(93, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Checkout Instructions.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Checkout Instructions\",\"category_id\":8}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:04:59', '2026-05-14 08:04:59'),
(94, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Contact / Guest Services.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"category\":\"Contact \\/ Guest Services\",\"category_id\":9}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:05:02', '2026-05-14 08:05:02'),
(95, 'guest', 1, 'Site Admin', 'admin@example.com', 'invalid_token_access', 'Invalid guest token access attempt for booking: INVALID.', 'auth', 'auth', 'warning', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36', '{\"booking_id\":\"INVALID\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 08:05:04', '2026-05-14 08:05:04'),
(96, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: identity) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', '{\"preview_state\":\"identity\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:05:07', '2026-05-14 08:05:07'),
(97, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-14 08:05:09', '2026-05-14 08:05:09'),
(98, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:05:12', '2026-05-14 08:05:12'),
(99, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:06:13', '2026-05-14 08:06:13'),
(100, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 08:06:17', '2026-05-14 08:06:17'),
(101, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"ip\":\"::1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-14 12:24:31', '2026-05-14 12:24:31'),
(102, 'guest', 1, 'Jordan Taylor', 'admin@example.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: identity).', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"identity\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:24:49', '2026-05-14 12:24:49'),
(103, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'photo_id_uploaded', 'Guest Jordan Taylor submitted photo ID and pre-arrival details.', 'photo_id', 'photo_id', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"email\":\"jinuilyas63@gmail.com\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:07', '2026-05-14 12:25:07'),
(104, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:08', '2026-05-14 12:25:08'),
(105, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:22', '2026-05-14 12:25:22'),
(106, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:25', '2026-05-14 12:25:25'),
(107, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Fitness Center.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Fitness Center\",\"category_id\":3}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:27', '2026-05-14 12:25:27'),
(108, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:25:32', '2026-05-14 12:25:32'),
(109, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-14 12:26:22', '2026-05-14 12:26:22'),
(110, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:30:00', '2026-05-14 12:30:00'),
(111, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:30:02', '2026-05-14 12:30:02'),
(112, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Restaurants.', 'guest_portal', 'guest_portal', 'info', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Restaurants\",\"category_id\":5}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-14 12:30:06', '2026-05-14 12:30:06'),
(113, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 06:37:27', '2026-05-16 06:37:27'),
(114, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 06:37:57', '2026-05-16 06:37:57'),
(115, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 06:42:45', '2026-05-16 06:42:45'),
(116, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 06:44:18', '2026-05-16 06:44:18'),
(117, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:44:44', '2026-05-16 06:44:44'),
(118, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:44:48', '2026-05-16 06:44:48');
INSERT INTO `activity_logs` (`id`, `actor_type`, `actor_id`, `actor_name`, `actor_email`, `action`, `description`, `icon`, `module`, `severity`, `ip_address`, `user_agent`, `metadata`, `old_values`, `new_values`, `property_id`, `booking_id`, `subject_type`, `subject_id`, `user_id`, `created_at`, `updated_at`) VALUES
(119, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:44:51', '2026-05-16 06:44:51'),
(120, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 06:47:12', '2026-05-16 06:47:12'),
(121, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:09', '2026-05-16 06:48:09'),
(122, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:11', '2026-05-16 06:48:11'),
(123, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:14', '2026-05-16 06:48:14'),
(124, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:21', '2026-05-16 06:48:21'),
(125, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:27', '2026-05-16 06:48:27'),
(126, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Pool.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Pool\",\"category_id\":4}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:29', '2026-05-16 06:48:29'),
(127, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:35', '2026-05-16 06:48:35'),
(128, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:43', '2026-05-16 06:48:43'),
(129, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:45', '2026-05-16 06:48:45'),
(130, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Parking.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Parking\",\"category_id\":7}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:53', '2026-05-16 06:48:53'),
(131, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:48:59', '2026-05-16 06:48:59'),
(132, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Pool.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Pool\",\"category_id\":4}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:49:04', '2026-05-16 06:49:04'),
(133, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 06:49:09', '2026-05-16 06:49:09'),
(134, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 07:05:49', '2026-05-16 07:05:49'),
(135, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:10', '2026-05-16 07:06:10'),
(136, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:12', '2026-05-16 07:06:12'),
(137, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:17', '2026-05-16 07:06:17'),
(138, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Pool.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Pool\",\"category_id\":4}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:20', '2026-05-16 07:06:20'),
(139, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:22', '2026-05-16 07:06:22'),
(140, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Parking.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Parking\",\"category_id\":7}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:28', '2026-05-16 07:06:28'),
(141, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:30', '2026-05-16 07:06:30'),
(142, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Bars.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Bars\",\"category_id\":6}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:32', '2026-05-16 07:06:32'),
(143, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:06:36', '2026-05-16 07:06:36'),
(144, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 07:12:06', '2026-05-16 07:12:06'),
(145, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 07:12:07', '2026-05-16 07:12:07'),
(146, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:13:46', '2026-05-16 07:13:46'),
(147, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Bars.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Bars\",\"category_id\":6}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:13:50', '2026-05-16 07:13:50'),
(148, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:13:53', '2026-05-16 07:13:53'),
(149, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 07:17:23', '2026-05-16 07:17:23'),
(150, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:04', '2026-05-16 07:18:04'),
(151, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Fitness Center.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Fitness Center\",\"category_id\":3}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:06', '2026-05-16 07:18:06'),
(152, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:09', '2026-05-16 07:18:09'),
(153, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Bars.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Bars\",\"category_id\":6}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:11', '2026-05-16 07:18:11'),
(154, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:38', '2026-05-16 07:18:38'),
(155, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Checkout Instructions.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Checkout Instructions\",\"category_id\":8}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:18:44', '2026-05-16 07:18:44'),
(156, 'guest', NULL, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, NULL, '2026-05-16 07:25:10', '2026-05-16 07:25:10'),
(157, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 07:25:11', '2026-05-16 07:25:11'),
(158, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-16 07:25:11', '2026-05-16 07:25:11'),
(159, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Checkout Instructions.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Checkout Instructions\",\"category_id\":8}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:27:26', '2026-05-16 07:27:26'),
(160, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:27:28', '2026-05-16 07:27:28'),
(161, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Checkout Instructions.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Checkout Instructions\",\"category_id\":8}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:27:36', '2026-05-16 07:27:36'),
(162, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:27:38', '2026-05-16 07:27:38'),
(163, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 07:30:20', '2026-05-16 07:30:20'),
(164, 'admin', 1, 'Site Admin', 'admin@example.com', 'login', 'Site Admin logged in successfully.', 'auth', 'auth', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"ip\":\"127.0.0.1\"}', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-05-16 07:30:20', '2026-05-16 07:30:20'),
(165, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-16 07:30:21', '2026-05-16 07:30:21'),
(166, 'admin', 1, 'Site Admin', 'admin@example.com', 'booking_previewed', 'Site Admin previewed guest page (state: guide) for Jordan Taylor.', 'guests', 'guests', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"preview_state\":\"guide\"}', NULL, NULL, 1, 1, 'App\\Models\\Booking', 1, 1, '2026-05-16 07:30:22', '2026-05-16 07:30:22'),
(167, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Amenities.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', '{\"category\":\"Amenities\",\"category_id\":2}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:30:22', '2026-05-16 07:30:22'),
(168, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:23', '2026-05-16 07:32:23'),
(169, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: WiFi.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"WiFi\",\"category_id\":1}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:26', '2026-05-16 07:32:26'),
(170, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:31', '2026-05-16 07:32:31'),
(171, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Fitness Center.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"category\":\"Fitness Center\",\"category_id\":3}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:32', '2026-05-16 07:32:32'),
(172, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'portal_viewed', 'Guest Jordan Taylor viewed the portal (state: guide).', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '{\"state\":\"guide\",\"booking_ref\":\"LUMINA-DEMO\"}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:35', '2026-05-16 07:32:35'),
(173, 'guest', 1, 'Jordan Taylor', 'jinuilyas63@gmail.com', 'category_viewed', 'Guest Jordan Taylor viewed category: Restaurants.', 'guest_portal', 'guest_portal', 'info', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '{\"category\":\"Restaurants\",\"category_id\":5}', NULL, NULL, 1, 1, NULL, NULL, 1, '2026-05-16 07:32:39', '2026-05-16 07:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `property_id`, `title`, `icon`, `details`, `images`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Fitness Center', 'Fit', 'Open daily from 6:00 AM to 10:00 PM. Use your room code at the amenity door.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(2, 1, 'Pool', 'Pool', 'Open 8:00 AM to 9:00 PM. Please use the pool towels from the amenity cabinet.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(3, 1, 'Laundry', 'Wash', 'Laundry room is on Level 3 near the east elevator. Machines accept card payment.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(4, 1, 'Parking', 'Park', 'Visitor parking is available on garage Level B1 in marked spaces only.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(5, 1, 'Smart Lock', 'Lock', 'Use your assigned access code followed by #. The lock relocks automatically.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(6, 1, 'Building Access', 'Door', 'Lobby and elevator access use the same code as your suite.', NULL, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` varchar(255) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `photo_id_path` varchar(255) DEFAULT NULL,
  `parking_needed` tinyint(1) DEFAULT NULL,
  `gps_verified` tinyint(1) NOT NULL DEFAULT 0,
  `manually_checked_in` tinyint(1) NOT NULL DEFAULT 0,
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `guest_name`, `phone`, `email`, `check_in_date`, `check_out_date`, `property_id`, `token`, `photo_id_path`, `parking_needed`, `gps_verified`, `manually_checked_in`, `checked_in_at`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'LUMINA-DEMO', 'Jordan Taylor', '+1 555 555 0199', 'jinuilyas63@gmail.com', '2026-05-14', '2026-05-17', 1, 'lumina-demo-secure-token', 'photo-ids/0od2qf2bYhZzEc8BPD2zvvvOFIvpVIFOwCHBbGmK.png', 1, 0, 1, '2026-05-14 08:04:46', 'id_uploaded', 'Demo booking for client walkthrough.', '2026-05-14 02:20:25', '2026-05-14 12:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `guest_icon` varchar(255) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `is_global` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`, `slug`, `icon`, `guest_icon`, `header_image`, `description`, `sort_order`, `active`, `is_global`, `created_at`, `updated_at`) VALUES
(1, 'WiFi', 'wifi', 'WiFi', NULL, NULL, 'Network name, password, and connection tips.', 20, 1, 1, '2026-05-14 02:20:24', '2026-05-14 12:30:29'),
(2, 'Amenities', 'amenities', 'Ameni', NULL, NULL, 'Pool, laundry, building access, and services.', 10, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(3, 'Fitness Center', 'fitness-center', 'Fit', NULL, NULL, 'Hours, access, and equipment notes.', 30, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(4, 'Pool', 'pool', 'Pool', NULL, NULL, 'Pool access, rules, and towel details.', 40, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(5, 'Restaurants', 'restaurants', 'Food', NULL, NULL, 'Favorite nearby dining options.', 50, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(6, 'Bars', 'bars', 'Bars', NULL, NULL, 'Relaxed local bars and lounges.', 60, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(7, 'Parking', 'parking', 'Park', NULL, NULL, 'Garage access and parking rules.', 70, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(8, 'Checkout Instructions', 'checkout-instructions', 'Out', NULL, NULL, 'Departure steps and reminders.', 80, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29'),
(9, 'Contact / Guest Services', 'contact-guest-services', 'Help', NULL, NULL, 'How to reach the team.', 90, 1, 1, '2026-05-14 02:20:25', '2026-05-14 12:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `category_pages`
--

CREATE TABLE `category_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_pages`
--

INSERT INTO `category_pages` (`id`, `property_id`, `category_id`, `title`, `content`, `image_1`, `image_2`, `image_3`, `sort_order`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'WiFi', 'Network: Lumina Guest\nPassword: StayBright123\n\nFor the fastest connection, choose the 5G network when available.', NULL, NULL, NULL, 1, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(2, 1, 2, 'Amenities', 'This section includes practical details for your stay at Lumina Hotel & Residences. Admins can edit this content, upload images, and tailor it for each property.', NULL, NULL, NULL, 2, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(3, 1, 3, 'Fitness Center', 'This section includes practical details for your stay at Lumina Hotel & Residences. Admins can edit this content, upload images, and tailor it for each property.', NULL, NULL, NULL, 3, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(4, 1, 4, 'Pool', 'This section includes practical details for your stay at Lumina Hotel & Residences. Admins can edit this content, upload images, and tailor it for each property.', NULL, NULL, NULL, 4, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(5, 1, 5, 'Restaurants', 'Juniper & Ivy is excellent for a polished dinner. The Fish Market is a reliable waterfront choice, and Morning Glory is a popular brunch stop.', NULL, NULL, NULL, 5, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(6, 1, 6, 'Bars', 'Try The Nolen for rooftop views, False Idol for a memorable cocktail room, and Neighborhood for an easy downtown evening.', NULL, NULL, NULL, 6, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(7, 1, 7, 'Parking', 'Use the B1 visitor garage and keep your vehicle in marked visitor spaces. Oversized vehicles should contact Guest Services before arrival.', NULL, NULL, NULL, 7, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(8, 1, 8, 'Checkout Instructions', 'Checkout is at 11:00 AM. Please gather belongings, place used towels in the bathroom, and close the suite door firmly.', NULL, NULL, NULL, 8, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(9, 1, 9, 'Contact / Guest Services', 'Guest Services\nPhone: +1 555 123 4567\nEmail: guestservices@example.com\n\nFor urgent issues, call rather than email.', NULL, NULL, NULL, 9, 1, '2026-05-14 02:20:25', '2026-05-14 02:20:25');

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_14_000001_create_welcome_guide_tables', 1),
(5, '2026_05_14_000002_add_experience_upgrade_tables', 2),
(6, '2026_05_14_000003_enterprise_upgrade', 3),
(7, '2026_05_14_000004_add_category_images', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `map_embed_url` text DEFAULT NULL,
  `map_directions_url` text DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `welcome_intro` longtext DEFAULT NULL,
  `checkin_instructions` longtext DEFAULT NULL,
  `parking_instructions` longtext DEFAULT NULL,
  `checkout_instructions` longtext DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `name`, `slug`, `address`, `city`, `state`, `zip`, `latitude`, `longitude`, `map_embed_url`, `map_directions_url`, `contact_phone`, `contact_email`, `welcome_intro`, `checkin_instructions`, `parking_instructions`, `checkout_instructions`, `header_image`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Lumina Hotel & Residences', 'lumina-hotel-residences', '123 Aura Way', 'San Diego', 'CA', '92101', 32.7157360, -117.1610870, 'https://www.google.com/maps?q=San%20Diego%20CA&output=embed', 'https://www.google.com/maps/search/?api=1&query=123+Aura+Way+San+Diego+CA+92101', '+1 555 123 4567', 'guestservices@example.com', 'Welcome to Lumina Hotel & Residences. We are delighted to host you in downtown San Diego with a calm arrival experience and a curated local guide.', 'Your smart lock code is 4826#. Enter through the main lobby doors, take the elevator to your floor, and use the code at your suite door.\n\nQuiet hours begin at 10:00 PM. Guest Services is available by phone if you need arrival support.', 'Parking is available in the resident garage on Level B1. Use code 4826# at the garage keypad and park only in spaces marked Visitor.', 'Checkout is at 11:00 AM. Please place used towels in the bathroom, load dishes into the dishwasher, turn off lights, and close the door firmly behind you.', NULL, 1, '2026-05-14 02:20:24', '2026-05-14 02:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `property_category`
--

CREATE TABLE `property_category` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `custom_title` varchar(255) DEFAULT NULL,
  `custom_description` text DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_category`
--

INSERT INTO `property_category` (`id`, `property_id`, `category_id`, `custom_title`, `custom_description`, `header_image`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(2, 1, 2, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(3, 1, 3, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(4, 1, 4, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(5, 1, 5, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(6, 1, 6, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(7, 1, 7, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(8, 1, 8, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42'),
(9, 1, 9, NULL, NULL, NULL, 1, '2026-05-14 02:20:25', '2026-05-14 08:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1smQI4NwOUw7EAw9pAOv2Zs4h0FElTMujL90SO2M', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZE5ZQ0J4clVFQVVmOEg4S09OQW81OHd2RFlwTXpHQUJIeVJYb21GZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778933843),
('1tTYFTdyN3RJXi1CQLZplVJ5SvoBf0TxxqWGvyym', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiR0lKYXg3aUNicFJDRk9qcFJiQXlwSlNwNG94a1BIQVhCd2NaY3FibCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jb250ZW50IjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5jb250ZW50LmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1778933527),
('byZzwmjuRpUqLFhAOOu0p3vsFnqn4eujgpBtJAll', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibVd6YThNdEdKUVJZNWpIQWdQS2RKRTJaUFFvczZSdWUzeUVQSVBWYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Jvb2tpbmdzLzEvcHJldmlldy9hcnJpdmFsIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9ib29raW5ncy8xL3ByZXZpZXcvYXJyaXZhbCI7czo1OiJyb3V0ZSI7czoyMjoiYWRtaW4uYm9va2luZ3MucHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778933844),
('D786oZVMCmDgwzQrhMiKeC0lo49ruHZuZweerhOb', 1, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiblhpWTJJMGJkaWFadTBaRjNudDJxanVJS3VuYkp5YXZjWlNjZ2NZZiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjgyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZ3Vlc3QvTFVNSU5BLURFTU8vbHVtaW5hLWRlbW8tc2VjdXJlLXRva2VuL2d1aWRlL3Jlc3RhdXJhbnRzIjtzOjU6InJvdXRlIjtzOjE0OiJndWVzdC5jYXRlZ29yeSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1778934759),
('DTqsBWvZLXS78hKai7En8F399GpFHkyhZU5wxhE5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib2YzUlNSeDdzM2xKMTZJVjdTalEwUDlLMUp4WnhCckNjMHJTa010bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1779182290),
('dyOvDaFQzzue3ck2YSXzKcfGgVgv2ce9QbABRS5F', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRVdHS1dRU1BYMkpqdkF6SDZ2UkJPSnVXSUJRczJDWU54Yk1SSUE0cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778931765),
('EbvFXarExkzTVP4NYewGLX1OIYnV27d227jnHi6m', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZklCb0hWQmtsVnlWUU9HdkQwdkdYT0ZjSmVCM2NlZU1LRUpER08wYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMy9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779182308),
('fvccLmdsC74MHKxrjtSqjkBcDeEnVeR2GgCYBVrF', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2gwenZXSWQwaFcyYTJGOHF6a1JIdWRJaUdwZEJUMno0cWRaQ2tuTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778931753),
('gEMeP2vnnjq1NoJTnOb9nBpUB6FcPkTbZcAuU9rT', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicUZTMEJKelBtNjhiRXJESldVQnNWNDhGWlVWbTRTWE96V0V6NkdHeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMi9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779182290),
('GlkDxKRU8QxDS1x7MFGWlZOcTVdTqcKG1dds0izZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTU9tTnRvQXdPWmloV1BIVjFVSXhuZm1GNnVJbm9NVlBHMndSVkhMVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778938683),
('kGymOihZF1m5G29Fj43ly0bcj4PrN16fvfoZDm2V', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibHNGc0djemFVNVFONHozRkt4WnVZRjd3ZFhZYkZJbmh1dmNNSG1wSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jb250ZW50IjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5jb250ZW50LmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1778931478),
('M7MHJbRp2wQva48huepbV0hs9bJZh2c5ZTv93tAN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWdGcDU0TklCak50dFNjTlpOeFdCZjlMV0hUSnQ1bG9uOE5GY2pzYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778933149),
('MA9H56kU3PT23KUIIsCup1eSfq0OllCrcWdaNl4S', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidnFsN1BTelByZERZSVhLQW9ldkVjaERzY0hqWWJONzNkWGI4QkVFOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4vZ3VpZGUvYW1lbml0aWVzIjtzOjU6InJvdXRlIjtzOjE0OiJndWVzdC5jYXRlZ29yeSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1778934622),
('pRmTZINpFaCX8H9wQYjWdMIRKe6r1DfGQelscATy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic0VHOHI4c290RU5Ec3VFa1RFSTdGMkE3MlBuMjVENU5PSjBva1RBYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778931378),
('qkkNuc3jZem1XhOhSxE9VRQLzT5T4jV2rjl7NILh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicFhJNlRzaHZBVWUzaGVOUHVad0p0bjFLQXlpZU5xNDljeE00S3N3SyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778932032),
('u1sul1Kmhd5HSJVE269Cx0sTzM26Zg5bGUPs2sBC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoielBOb0NEaVh0R2hveFNIeUJlZzZPaEViZ3ppbDR5cWlFak0zajZCdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778933526),
('U5ttLuZDQXsYDIdsS3AcclXo62ClEYQg5YqiY3Mj', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSEJHQTBqOHlZN0FLRjVmcG95QnFwdnBGUWJIR0NubTZ4NGZrR2s1cSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9ib29raW5ncy8xL3ByZXZpZXcvZ3VpZGUiO3M6NToicm91dGUiO3M6MjI6ImFkbWluLmJvb2tpbmdzLnByZXZpZXciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1778934621),
('UYHTBYqX48L4F6iNJzo726wwQV5zsnNbCINzqgtk', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHdra3JGQ08wR1VpdEdudnp4ZGVNUDBqODNHSzJZNG5lMFNOcWRHRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778931727),
('WMAVrC38I0iWBBaaPMSGyuS09GNOdPsKNozJm3au', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN08yV2FzSXVjWjNGSm5jWnJyMkNXaTgxa0JMWkFpM1g1aE1iQkRacCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1778931447),
('XDBxqZge5Eus2cbT49UDdEgsLosdu398nci1fWWV', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUkRrNVBqSzF6c1NnNmFnbmhINWRsdll4RzlkWHNNaVBCNGVTbmJRWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9ib29raW5ncy8xL3ByZXZpZXcvZ3VpZGUiO3M6NToicm91dGUiO3M6MjI6ImFkbWluLmJvb2tpbmdzLnByZXZpZXciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1778934311),
('XQYZHurZgRLU861gSHVv3rywKlN7ux0hpkq4fZ4z', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVRpSGVwYjY1UGEyOGUyUkpSVTJlMmljYXhNdVRITTk3YWhIZG5ZdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778934310),
('YusCpzfozFiQh2FUmzOV8xEZb7JBwJp5eHfs3msH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8115', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMndzVmFOdUFqYTkzY2ZJYWdLVGlVOGpOeEVwYnQyb09aUkU0Y0V5eCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ndWVzdC9MVU1JTkEtREVNTy9sdW1pbmEtZGVtby1zZWN1cmUtdG9rZW4iO3M6NToicm91dGUiO3M6MTA6Imd1ZXN0LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1778931447);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'gps_radius_meters', '150', '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(2, 'brand_color', '#0f766e', '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(3, 'contact_phone', '+1 555 123 4567', '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(4, 'contact_email', 'guestservices@example.com', '2026-05-14 02:20:25', '2026-05-14 02:20:25'),
(5, 'default_intro', 'Welcome. Your arrival details and local guide are ready when you are.', '2026-05-14 02:20:25', '2026-05-14 02:20:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'owner',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `admin_tour_completed_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `dashboard_tour_completed_at` timestamp NULL DEFAULT NULL,
  `full_system_tour_completed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `role`, `status`, `email`, `phone`, `avatar`, `email_verified_at`, `password`, `remember_token`, `admin_tour_completed_at`, `last_login_at`, `last_login_ip`, `dashboard_tour_completed_at`, `full_system_tour_completed_at`, `created_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Site Admin', 'owner', 'active', 'admin@example.com', NULL, NULL, NULL, '$2y$12$oT92eb42IZ3gnbBUY7NDE.U1/ZZPgrDl32CgJKcQqd/FD9KAOFJt2', NULL, '2026-05-14 08:02:57', '2026-05-16 07:30:20', '127.0.0.1', NULL, NULL, NULL, NULL, '2026-05-14 02:20:24', '2026-05-16 07:30:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`),
  ADD KEY `al_actor_idx` (`actor_type`,`actor_id`),
  ADD KEY `al_module_idx` (`module`),
  ADD KEY `al_severity_idx` (`severity`),
  ADD KEY `al_property_idx` (`property_id`),
  ADD KEY `al_booking_idx` (`booking_id`),
  ADD KEY `al_created_idx` (`created_at`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `amenities_property_id_foreign` (`property_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bookings_booking_id_unique` (`booking_id`),
  ADD UNIQUE KEY `bookings_token_unique` (`token`),
  ADD KEY `bookings_property_id_foreign` (`property_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `category_pages`
--
ALTER TABLE `category_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_pages_property_id_foreign` (`property_id`),
  ADD KEY `category_pages_category_id_foreign` (`category_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `properties_slug_unique` (`slug`);

--
-- Indexes for table `property_category`
--
ALTER TABLE `property_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_category_property_id_category_id_unique` (`property_id`,`category_id`),
  ADD KEY `property_category_category_id_foreign` (`category_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `category_pages`
--
ALTER TABLE `category_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `property_category`
--
ALTER TABLE `property_category`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `amenities`
--
ALTER TABLE `amenities`
  ADD CONSTRAINT `amenities_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_pages`
--
ALTER TABLE `category_pages`
  ADD CONSTRAINT `category_pages_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_pages_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_category`
--
ALTER TABLE `property_category`
  ADD CONSTRAINT `property_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_category_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
