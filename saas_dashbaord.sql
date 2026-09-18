-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2026 at 12:58 PM
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
-- Database: `saas_dashbaord`
--

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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `address_line` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `tenant_id`, `name`, `email`, `phone`, `company`, `status`, `address_line`, `city`, `state`, `postal_code`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'customer0', 'customer0@mail0.com', '03152145854', 'company', 'active', 'st 0 brooklyn underwater', 'fish', 'ocean', '000000', 'nothing', '2026-09-17 07:05:51', '2026-09-17 07:05:51'),
(2, 1, 'elon msk', 'elonmsk@gmail.com', '03194977640', 'space x', 'active', 'space x, new york, usa', 'new york city', 'new york', '46000', 'clean my rocket', '2026-09-17 08:05:54', '2026-09-17 08:05:54'),
(3, 3, 'google ceo', 'googleceo@gmail.com', '111111111111', 'google', 'active', 'google headquater', 'google', 'google', '111111', 'google is everything', '2026-09-18 03:16:13', '2026-09-18 03:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `customer_payments`
--

CREATE TABLE `customer_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'paid',
  `method` varchar(255) NOT NULL DEFAULT 'cash',
  `amount` decimal(10,2) NOT NULL,
  `paid_at` date NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_payments`
--

INSERT INTO `customer_payments` (`id`, `tenant_id`, `service_job_id`, `customer_id`, `status`, `method`, `amount`, `paid_at`, `reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 2, 'paid', 'cash', 10000.00, '2026-09-17', 'invoice 489756', 'ho gya ha bsdk', '2026-09-17 07:27:16', '2026-09-17 08:20:50'),
(2, 1, 2, 2, 'paid', 'cash', 10000.00, '2026-09-17', 'invoice 489756', 'ho ghya', '2026-09-17 08:21:56', '2026-09-17 08:21:56'),
(3, 1, 3, 2, 'paid', 'cash', 90.00, '2026-09-17', '453275', 'ho ghya', '2026-09-17 08:26:16', '2026-09-17 08:26:16'),
(4, 1, 1, 1, 'paid', 'cash', 30.00, '2026-09-18', NULL, NULL, '2026-09-17 08:31:36', '2026-09-17 08:31:36'),
(5, 1, 1, 1, 'paid', 'cash', 20.00, '2026-09-17', NULL, NULL, '2026-09-17 08:33:18', '2026-09-17 08:33:18'),
(6, 3, 4, 3, 'paid', 'cash', 9000.00, '2026-09-23', 'aandbcarpetny', 'i am satisfied', '2026-09-18 05:09:12', '2026-09-18 05:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `vendor` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `tenant_id`, `service_job_id`, `expense_category_id`, `category_name`, `submitted_by`, `status`, `vendor`, `amount`, `expense_date`, `notes`, `receipt_path`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, 2, 'approved', 'fsdf', 20.00, '2026-09-17', 'ho ghya', NULL, NULL, NULL, NULL, '2026-09-17 07:28:19', '2026-09-17 07:29:49'),
(2, 1, 2, 1, NULL, 4, 'approved', 'raja ji', 100.00, '2026-09-28', NULL, NULL, NULL, NULL, NULL, '2026-09-17 08:16:22', '2026-09-17 08:16:22'),
(3, 1, 3, NULL, NULL, 4, 'approved', 'shell', 30.00, '2026-09-18', NULL, NULL, NULL, NULL, NULL, '2026-09-18 01:51:00', '2026-09-18 01:51:00'),
(4, 3, 4, NULL, NULL, 8, 'approved', 'shell', 40.00, '2026-09-23', 'petrol pawaya', 'expense-receipts/M72YbW5QAlEga7MVVZRnsiWp2p2zO1ed2mIsMjnk.jpg', 6, '2026-09-18 05:56:21', NULL, '2026-09-18 05:01:02', '2026-09-18 05:56:21');

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `tenant_id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'roti', 1, '2026-09-17 07:27:57', '2026-09-17 07:27:57');

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
-- Table structure for table `job_work_events`
--

CREATE TABLE `job_work_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_work_events`
--

INSERT INTO `job_work_events` (`id`, `tenant_id`, `service_job_id`, `user_id`, `event_type`, `occurred_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 'start', '2026-09-18 01:40:07', NULL, '2026-09-18 01:40:07', '2026-09-18 01:40:07'),
(2, 1, 3, 1, 'break_start', '2026-09-18 01:40:25', NULL, '2026-09-18 01:40:25', '2026-09-18 01:40:25'),
(3, 1, 3, 1, 'break_end', '2026-09-18 01:40:30', NULL, '2026-09-18 01:40:30', '2026-09-18 01:40:30'),
(4, 1, 3, 1, 'end', '2026-09-18 01:40:34', NULL, '2026-09-18 01:40:34', '2026-09-18 01:40:34'),
(5, 3, 4, 8, 'start', '2026-09-18 04:59:51', NULL, '2026-09-18 04:59:51', '2026-09-18 04:59:51'),
(6, 3, 4, 8, 'break_start', '2026-09-18 05:03:47', NULL, '2026-09-18 05:03:47', '2026-09-18 05:03:47'),
(7, 3, 4, 6, 'break_end', '2026-09-18 05:05:42', NULL, '2026-09-18 05:05:42', '2026-09-18 05:05:42'),
(8, 3, 4, 8, 'end', '2026-09-18 05:07:46', NULL, '2026-09-18 05:07:46', '2026-09-18 05:07:46');

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
(4, '2026_09_07_101610_create_tenants_table', 2),
(5, '2026_09_07_101612_create_roles_table', 3),
(6, '2026_09_07_101613_create_permissions_table', 3),
(7, '2026_09_07_101614_create_tenant_user_table', 3),
(8, '2026_09_07_101615_create_permission_role_table', 3),
(9, '2026_09_07_101616_create_role_tenant_user_table', 3),
(10, '2026_09_07_101705_add_super_admin_to_users_table', 3),
(11, '2026_09_17_000001_add_status_to_tenants_table', 4),
(12, '2026_09_17_000002_create_customers_table', 5),
(13, '2026_09_17_000003_create_service_catalog_tables', 6),
(14, '2026_09_17_000004_create_service_jobs_tables', 7),
(15, '2026_09_17_000005_create_teams_and_job_assignments', 8),
(16, '2026_09_17_000006_create_time_entries_table', 9),
(17, '2026_09_17_000007_create_expenses_tables', 10),
(18, '2026_09_17_000008_create_payments_tables', 11),
(19, '2026_09_17_000009_create_plans_and_subscriptions_tables', 12),
(20, '2026_09_18_000001_create_job_work_events_table', 13),
(21, '2026_09_18_000002_add_receipt_and_approval_to_expenses_table', 14),
(22, '2026_09_18_000003_add_category_name_to_expenses_table', 15),
(23, '2026_09_18_000004_create_service_job_status_events_table', 16);

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
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Manage tenant users', 'users.manage', '2026-09-07 05:20:17', '2026-09-07 05:20:17'),
(2, 'Update tenant settings', 'tenant.settings.update', '2026-09-07 05:20:17', '2026-09-07 05:20:17'),
(3, 'View dashboard', 'dashboard.view', '2026-09-07 05:20:17', '2026-09-17 05:32:19'),
(4, 'Manage roles and permissions', 'roles.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(5, 'View customers', 'customers.view', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(6, 'Manage customers', 'customers.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(7, 'View services', 'services.view', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(8, 'Manage services', 'services.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(9, 'View jobs', 'jobs.view', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(10, 'Manage jobs', 'jobs.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(11, 'Assign jobs', 'jobs.assign', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(12, 'Manage expenses', 'expenses.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(13, 'Manage payments', 'payments.manage', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(14, 'View reports', 'reports.view', '2026-09-17 05:32:19', '2026-09-17 05:32:19'),
(15, 'Work assigned jobs', 'jobs.work', '2026-09-18 00:52:02', '2026-09-18 00:52:02'),
(16, 'Submit expenses', 'expenses.submit', '2026-09-18 00:52:02', '2026-09-18 00:52:02'),
(17, 'Approve expenses', 'expenses.approve', '2026-09-18 00:52:02', '2026-09-18 00:52:02'),
(18, 'View own team payments', 'team-payments.view-own', '2026-09-18 00:52:02', '2026-09-18 00:52:02'),
(19, 'Update job status', 'jobs.status.update', '2026-09-18 05:40:12', '2026-09-18 05:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 13),
(1, 14),
(1, 15),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 13),
(2, 14),
(2, 15),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(4, 2),
(4, 3),
(4, 7),
(4, 8),
(4, 9),
(4, 13),
(4, 14),
(5, 2),
(5, 3),
(5, 4),
(5, 5),
(5, 7),
(5, 8),
(5, 9),
(5, 10),
(5, 11),
(5, 13),
(5, 14),
(5, 15),
(5, 16),
(6, 2),
(6, 3),
(6, 4),
(6, 7),
(6, 8),
(6, 9),
(6, 10),
(6, 13),
(6, 14),
(6, 15),
(7, 2),
(7, 3),
(7, 4),
(7, 7),
(7, 8),
(7, 9),
(7, 10),
(7, 13),
(7, 14),
(7, 15),
(8, 2),
(8, 3),
(8, 4),
(8, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 13),
(8, 14),
(8, 15),
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 6),
(9, 7),
(9, 8),
(9, 9),
(9, 10),
(9, 11),
(9, 12),
(9, 13),
(9, 14),
(9, 15),
(9, 16),
(9, 17),
(10, 2),
(10, 3),
(10, 4),
(10, 7),
(10, 8),
(10, 9),
(10, 10),
(10, 13),
(10, 14),
(10, 15),
(11, 2),
(11, 3),
(11, 4),
(11, 7),
(11, 8),
(11, 9),
(11, 10),
(11, 13),
(11, 14),
(11, 15),
(12, 2),
(12, 3),
(12, 4),
(12, 7),
(12, 8),
(12, 9),
(12, 10),
(12, 13),
(12, 14),
(12, 15),
(13, 2),
(13, 3),
(13, 4),
(13, 7),
(13, 8),
(13, 9),
(13, 10),
(13, 13),
(13, 14),
(13, 15),
(14, 2),
(14, 3),
(14, 4),
(14, 7),
(14, 8),
(14, 9),
(14, 10),
(14, 13),
(14, 14),
(14, 15),
(15, 2),
(15, 3),
(15, 4),
(15, 5),
(15, 8),
(15, 9),
(15, 10),
(15, 11),
(15, 13),
(15, 14),
(15, 15),
(15, 16),
(16, 2),
(16, 3),
(16, 4),
(16, 5),
(16, 8),
(16, 9),
(16, 10),
(16, 11),
(16, 13),
(16, 14),
(16, 15),
(16, 16),
(17, 2),
(17, 3),
(17, 4),
(17, 8),
(17, 9),
(17, 10),
(17, 13),
(17, 14),
(17, 15),
(18, 2),
(18, 3),
(18, 4),
(18, 5),
(18, 6),
(18, 8),
(18, 9),
(18, 10),
(18, 11),
(18, 12),
(18, 13),
(18, 14),
(18, 15),
(18, 16),
(18, 17),
(19, 2),
(19, 3),
(19, 4),
(19, 5),
(19, 8),
(19, 9),
(19, 10),
(19, 11),
(19, 13),
(19, 14),
(19, 15),
(19, 16);

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_users` int(10) UNSIGNED DEFAULT NULL,
  `max_jobs` int(10) UNSIGNED DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `slug`, `description`, `monthly_price`, `max_users`, `max_jobs`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 'starter', 'For small service teams getting organized.', 29.00, 5, 100, '[\"Customers\",\"Service jobs\",\"Basic reports\"]', 1, '2026-09-17 06:47:25', '2026-09-17 06:47:25'),
(2, 'Growth', 'growth', 'For growing teams that need payments and expenses.', 79.00, 20, 500, '[\"Everything in Starter\",\"Payments\",\"Expenses\",\"Team tracking\"]', 1, '2026-09-17 06:47:25', '2026-09-17 06:47:25'),
(3, 'Scale', 'scale', 'For larger operations with unlimited workflow volume.', 149.00, NULL, NULL, '[\"Everything in Growth\",\"Unlimited jobs\",\"Advanced reporting\"]', 1, '2026-09-17 06:47:25', '2026-09-17 06:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `tenant_id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tenant Admin', 'tenant-admin', '2026-09-07 05:21:31', '2026-09-07 05:21:31'),
(2, 1, 'Tenant Owner', 'tenant-owner', '2026-09-17 05:33:01', '2026-09-17 05:33:01'),
(3, 1, 'Business Admin', 'business-admin', '2026-09-17 05:33:01', '2026-09-17 05:33:01'),
(4, 1, 'Manager', 'manager', '2026-09-17 05:33:01', '2026-09-17 05:33:01'),
(5, 1, 'Team Lead', 'team-lead', '2026-09-17 05:33:01', '2026-09-17 05:33:01'),
(6, 1, 'Team Member', 'team-member', '2026-09-17 05:33:01', '2026-09-17 05:33:01'),
(7, 2, 'Tenant Admin', 'tenant-admin', '2026-09-17 07:15:29', '2026-09-17 07:15:29'),
(8, 2, 'Tenant Owner', 'tenant-owner', '2026-09-18 00:52:07', '2026-09-18 00:52:07'),
(9, 2, 'Business Admin', 'business-admin', '2026-09-18 00:52:07', '2026-09-18 00:52:07'),
(10, 2, 'Manager', 'manager', '2026-09-18 00:52:07', '2026-09-18 00:52:07'),
(11, 2, 'Team Lead', 'team-lead', '2026-09-18 00:52:07', '2026-09-18 00:52:07'),
(12, 2, 'Team Member', 'team-member', '2026-09-18 00:52:07', '2026-09-18 00:52:07'),
(13, 3, 'Tenant Owner', 'tenant-owner', '2026-09-18 02:56:22', '2026-09-18 02:56:22'),
(14, 3, 'Business Admin', 'business-admin', '2026-09-18 02:56:22', '2026-09-18 02:56:22'),
(15, 3, 'Manager', 'manager', '2026-09-18 02:56:22', '2026-09-18 02:56:22'),
(16, 3, 'Team Lead', 'team-lead', '2026-09-18 02:56:22', '2026-09-18 02:56:22'),
(17, 3, 'Team Member', 'team-member', '2026-09-18 02:56:22', '2026-09-18 02:56:22');

-- --------------------------------------------------------

--
-- Table structure for table `role_tenant_user`
--

CREATE TABLE `role_tenant_user` (
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_tenant_user`
--

INSERT INTO `role_tenant_user` (`tenant_id`, `user_id`, `role_id`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 4),
(1, 4, 5),
(1, 5, 6),
(2, 1, 7),
(3, 1, 13),
(3, 6, 13),
(3, 7, 17),
(3, 8, 16),
(3, 9, 15);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `unit_type` varchar(255) NOT NULL DEFAULT 'job',
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `tenant_id`, `service_category_id`, `name`, `unit_type`, `base_price`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'service0', 'hour', 50.00, 'we are not cheap', 1, '2026-09-17 07:09:36', '2026-09-17 07:09:36'),
(2, 1, 2, 'Big Rocket Organic', 'sqft', 5000.00, 'we are not clean but we clean every things', 1, '2026-09-17 08:08:43', '2026-09-17 08:08:43'),
(3, 3, 3, 'sofa', 'item', 100.00, 'sofa , leather sofa', 1, '2026-09-18 04:55:51', '2026-09-18 04:55:51');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `tenant_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'human hunting', 'not able to work on time', 1, '2026-09-17 07:08:44', '2026-09-17 07:08:44'),
(2, 1, 'Rocket Cleaning', 'interior and exterior cleaning with organic cleaner safe for pet and child', 1, '2026-09-17 08:07:52', '2026-09-17 08:07:52'),
(3, 3, 'upholstery', 'kuriyan, sofay', 1, '2026-09-18 04:54:39', '2026-09-18 04:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `service_jobs`
--

CREATE TABLE `service_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `job_number` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `service_address` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_jobs`
--

INSERT INTO `service_jobs` (`id`, `tenant_id`, `customer_id`, `team_id`, `assigned_user_id`, `job_number`, `status`, `scheduled_at`, `service_address`, `subtotal`, `discount`, `total`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'JOB-20260917-0001', 'completed', '2026-09-18 12:09:00', 'underwater near shark', 50.00, 0.00, 50.00, 'clean water', '2026-09-17 07:10:15', '2026-09-17 07:29:17'),
(2, 1, 2, 1, 4, 'JOB-20260917-0002', 'completed', '2026-09-22 04:30:00', 'new yourk', 25000.00, 0.00, 25000.00, 'nothing just deep clean the rocket', '2026-09-17 08:11:50', '2026-09-17 08:19:11'),
(3, 1, 2, 1, 4, 'JOB-20260917-0003', 'completed', '2026-09-17 13:24:00', 'u76u67u', 100.00, 0.00, 100.00, 'u76uj', '2026-09-17 08:18:48', '2026-09-18 01:40:34'),
(4, 3, 3, 2, 9, 'JOB-20260918-0001', 'completed', '2026-09-23 09:56:00', 'google headquater, google, google, 111111', 10000.00, 10.00, 9990.00, 'cleaning sofa is very important piece of work', '2026-09-18 04:56:58', '2026-09-18 05:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `service_job_items`
--

CREATE TABLE `service_job_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `unit_type` varchar(255) NOT NULL DEFAULT 'job',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_job_items`
--

INSERT INTO `service_job_items` (`id`, `service_job_id`, `service_id`, `name`, `unit_type`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(4, 1, 1, 'service0', 'hour', 1.00, 50.00, 50.00, '2026-09-17 07:29:17', '2026-09-17 07:29:17'),
(8, 2, 2, 'Big Rocket Organic', 'sqft', 5.00, 5000.00, 25000.00, '2026-09-17 08:19:11', '2026-09-17 08:19:11'),
(9, 3, 1, 'service0', 'hour', 2.00, 50.00, 100.00, '2026-09-17 08:24:51', '2026-09-17 08:24:51'),
(10, 4, 3, 'sofa', 'item', 100.00, 100.00, 10000.00, '2026-09-18 04:56:58', '2026-09-18 04:56:58');

-- --------------------------------------------------------

--
-- Table structure for table `service_job_status_events`
--

CREATE TABLE `service_job_status_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('OaRhARbrxXxltgDqbGsgL3m3DoOpL2d0KJuFLvTO', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRjE2Yml3ZjBJVzJuQVZrYTFFSmRrM29RWGxYZDBNbGRMczQxamJsdCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvam9icy80L2ludm9pY2UiO3M6NToicm91dGUiO3M6MTI6ImpvYnMuaW52b2ljZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjk7czoxNzoiY3VycmVudF90ZW5hbnRfaWQiO2k6Mzt9', 1789728516),
('RolVJeQTBLjEw1hvEBmcBxIaGMsu7p4EJMFg9jhY', 8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoieERFUkkwb0NLVUp1bkdlc2I4RU1uWjNPQmNzREJnZ0pLT2pEUWFwVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jdXN0b21lcnMvMyI7czo1OiJyb3V0ZSI7czoxNDoiY3VzdG9tZXJzLnNob3ciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo4O3M6MTc6ImN1cnJlbnRfdGVuYW50X2lkIjtpOjM7fQ==', 1789728740),
('vcPHpCKD9m6scqGhm3xqeLqmTwDX8m9jbnsunuWV', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidjhQT3VMQ1Z5WE9pUXVRRlBZRGZQSGxxOW9GMGdZWWw3ZzFaUkxDQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zZXJ2aWNlcyI7czo1OiJyb3V0ZSI7czoxNDoic2VydmljZXMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O3M6MTc6ImN1cnJlbnRfdGVuYW50X2lkIjtpOjM7fQ==', 1789729004);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `starts_at` date NOT NULL,
  `trial_ends_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `tenant_id`, `plan_id`, `status`, `starts_at`, `trial_ends_at`, `ends_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'active', '2026-09-17', NULL, NULL, NULL, '2026-09-17 07:15:29', '2026-09-17 07:15:29'),
(2, 3, 1, 'active', '2026-09-18', NULL, NULL, NULL, '2026-09-18 02:56:22', '2026-09-18 02:56:22');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `tenant_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'team0', 'not able to work on time', 1, '2026-09-17 07:06:44', '2026-09-17 07:06:44'),
(2, 3, 'waywe 1', 'way we cleaning + game', 1, '2026-09-18 04:50:47', '2026-09-18 04:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `team_payments`
--

CREATE TABLE `team_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `method` varchar(255) NOT NULL DEFAULT 'cash',
  `amount` decimal(10,2) NOT NULL,
  `paid_at` date DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_payments`
--

INSERT INTO `team_payments` (`id`, `tenant_id`, `service_job_id`, `team_id`, `user_id`, `status`, `method`, `amount`, `paid_at`, `reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 2, 'paid', 'cash', 20.00, '2026-09-17', NULL, NULL, '2026-09-17 07:27:33', '2026-09-17 07:27:33'),
(2, 1, 2, 1, 5, 'paid', 'cash', 20.00, '2026-09-18', 'invoice 489', NULL, '2026-09-18 01:49:24', '2026-09-18 01:49:24'),
(3, 3, 4, 2, 8, 'paid', 'cash', 100.00, '2026-09-23', NULL, NULL, '2026-09-18 05:09:35', '2026-09-18 05:09:35'),
(4, 3, 4, 2, 7, 'paid', 'cash', 100.00, '2026-09-23', NULL, NULL, '2026-09-18 05:09:53', '2026-09-18 05:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `team_user`
--

CREATE TABLE `team_user` (
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_user`
--

INSERT INTO `team_user` (`team_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 'member', '2026-09-17 07:06:44', '2026-09-17 07:06:44'),
(2, 7, 'member', '2026-09-18 04:50:47', '2026-09-18 04:50:47'),
(2, 8, 'member', '2026-09-18 04:50:47', '2026-09-18 04:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `created_by`, `name`, `slug`, `status`, `settings`, `created_at`, `updated_at`) VALUES
(1, 1, 'test', 'test-qI8a6', 'active', '{\"theme_color\":\"#5ea744\",\"brand_name\":\"test\",\"secondary_color\":\"#0091ff\"}', '2026-09-07 05:21:31', '2026-09-18 01:47:54'),
(2, 1, 'name', 'name-9bVae', 'active', '{\"theme_color\":\"#4c00ff\"}', '2026-09-17 07:15:29', '2026-09-17 07:15:29'),
(3, 1, 'Waywe', 'waywe-4gL4A', 'active', '{\"brand_name\":\"Gaming\",\"theme_color\":\"#0254ac\",\"secondary_color\":\"#7a7600\"}', '2026-09-18 02:56:22', '2026-09-18 05:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_user`
--

CREATE TABLE `tenant_user` (
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_user`
--

INSERT INTO `tenant_user` (`tenant_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-07 05:21:31', '2026-09-07 05:21:31'),
(1, 2, '2026-09-07 05:23:07', '2026-09-07 05:23:07'),
(1, 3, '2026-09-17 07:33:13', '2026-09-17 07:33:13'),
(1, 4, '2026-09-17 07:44:54', '2026-09-17 07:44:54'),
(1, 5, '2026-09-17 07:45:21', '2026-09-17 07:45:21'),
(2, 1, '2026-09-17 07:15:29', '2026-09-17 07:15:29'),
(3, 1, '2026-09-18 02:56:22', '2026-09-18 02:56:22'),
(3, 6, '2026-09-18 02:58:18', '2026-09-18 02:58:18'),
(3, 7, '2026-09-18 03:02:30', '2026-09-18 03:02:30'),
(3, 8, '2026-09-18 03:03:34', '2026-09-18 03:03:34'),
(3, 9, '2026-09-18 03:11:02', '2026-09-18 03:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

CREATE TABLE `time_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `service_job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `minutes` int(10) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `time_entries`
--

INSERT INTO `time_entries` (`id`, `tenant_id`, `service_job_id`, `user_id`, `started_at`, `ended_at`, `minutes`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-09-17 08:26:00', NULL, 120, 'ho ghya', '2026-09-17 07:26:33', '2026-09-17 07:26:33'),
(2, 1, 2, 4, '2026-09-23 08:13:00', NULL, 12000, 'rocket not clean', '2026-09-17 08:14:35', '2026-09-17 08:14:35'),
(3, 1, 3, 1, '2026-09-18 06:42:05', '2026-09-18 01:45:34', 0, 'Auto-calculated from job timer.', '2026-09-18 01:40:34', '2026-09-18 01:40:34'),
(4, 3, 4, 8, '2026-09-18 04:59:51', '2026-09-18 05:07:46', 6, 'Auto-calculated from job timer.', '2026-09-18 05:07:46', '2026-09-18 05:07:46');

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
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_super_admin`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Demo User', 'demo@nexus.test', '2026-09-07 05:20:17', '$2y$12$AfYOVVeK6yKlp9OrBVd0xefhXf8i8QCUSbAySYwCHwGnVS8CkbZLu', 1, NULL, '2026-09-07 05:09:45', '2026-09-07 05:20:17'),
(2, 'test', 'test@test.com', NULL, '$2y$12$c2XACThMPvlFqGOe4rz./.Pt6Jy2CSZiaAFrKcPeP9.EUX9rLqBe6', 0, NULL, '2026-09-07 05:23:07', '2026-09-07 05:23:07'),
(3, 'manager 0', 'manager@gmail.com', NULL, '$2y$12$UUYQjB00JqwIP.RjevXZA.rZkWyyi2BzSQq3gqSLDD8cBveLtZwsC', 0, NULL, '2026-09-17 07:33:13', '2026-09-17 07:33:13'),
(4, 'team lead', 'teamlead@gmail.com', NULL, '$2y$12$teccjwpbMhZ.1n5luw1qvu4R1AZDtNjDKHzzfh.dzLtAnbzhiKJMm', 0, NULL, '2026-09-17 07:44:54', '2026-09-17 07:44:54'),
(5, 'team member', 'teammember@gmail.com', NULL, '$2y$12$4738gunGHw2mdfDIuP2NtOqesE7CBmhvnbqjaVMulN42k0x.Ps3jS', 0, NULL, '2026-09-17 07:45:21', '2026-09-17 07:45:21'),
(6, 'Waywe Owner', 'wayweowner@gmail.com', NULL, '$2y$12$UaOQyjSYB4EtgYsexiGvKO.Qxg2fzJztYSRTRNrjJQeuQ.A7cydvW', 0, NULL, '2026-09-18 02:58:18', '2026-09-18 02:58:18'),
(7, 'waywe team one', 'wayweteamone@gmail.com', NULL, '$2y$12$TKHAolM2.jQqEcrgcDOXo.eI0ILDNnjoEUjBruC4COT8vCwMNBsOS', 0, NULL, '2026-09-18 03:02:30', '2026-09-18 03:02:30'),
(8, 'wayweteamlead', 'wayweteamlead@gmail.com', NULL, '$2y$12$tdshwwCYwwBbNyL8o6vNTOK0NmNU3LLkh/je1CkMCF1v/0SSMfNB2', 0, NULL, '2026-09-18 03:03:34', '2026-09-18 03:03:34'),
(9, 'waywemanager', 'waywemanager@gmail.com', NULL, '$2y$12$hHU4hyD32i2..Zx4qk.4O.AjV6pepH.GBucWItsvtD4Crm5HUuQci', 0, NULL, '2026-09-18 03:11:02', '2026-09-18 03:11:02');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `customers_tenant_id_name_index` (`tenant_id`,`name`);

--
-- Indexes for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_payments_service_job_id_foreign` (`service_job_id`),
  ADD KEY `customer_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_payments_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `customer_payments_tenant_id_paid_at_index` (`tenant_id`,`paid_at`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_service_job_id_foreign` (`service_job_id`),
  ADD KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  ADD KEY `expenses_submitted_by_foreign` (`submitted_by`),
  ADD KEY `expenses_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `expenses_tenant_id_expense_date_index` (`tenant_id`,`expense_date`),
  ADD KEY `expenses_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_categories_tenant_id_name_unique` (`tenant_id`,`name`);

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
-- Indexes for table `job_work_events`
--
ALTER TABLE `job_work_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_work_events_user_id_foreign` (`user_id`),
  ADD KEY `job_work_events_tenant_id_service_job_id_occurred_at_index` (`tenant_id`,`service_job_id`,`occurred_at`),
  ADD KEY `job_work_events_service_job_id_event_type_index` (`service_job_id`,`event_type`);

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
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plans_slug_unique` (`slug`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_tenant_user`
--
ALTER TABLE `role_tenant_user`
  ADD PRIMARY KEY (`tenant_id`,`user_id`,`role_id`),
  ADD KEY `role_tenant_user_user_id_foreign` (`user_id`),
  ADD KEY `role_tenant_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_tenant_id_name_unique` (`tenant_id`,`name`),
  ADD KEY `services_service_category_id_foreign` (`service_category_id`),
  ADD KEY `services_tenant_id_is_active_index` (`tenant_id`,`is_active`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_categories_tenant_id_name_unique` (`tenant_id`,`name`);

--
-- Indexes for table `service_jobs`
--
ALTER TABLE `service_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_jobs_tenant_id_job_number_unique` (`tenant_id`,`job_number`),
  ADD KEY `service_jobs_customer_id_foreign` (`customer_id`),
  ADD KEY `service_jobs_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `service_jobs_tenant_id_scheduled_at_index` (`tenant_id`,`scheduled_at`),
  ADD KEY `service_jobs_team_id_foreign` (`team_id`),
  ADD KEY `service_jobs_assigned_user_id_foreign` (`assigned_user_id`);

--
-- Indexes for table `service_job_items`
--
ALTER TABLE `service_job_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_job_items_service_job_id_foreign` (`service_job_id`),
  ADD KEY `service_job_items_service_id_foreign` (`service_id`);

--
-- Indexes for table `service_job_status_events`
--
ALTER TABLE `service_job_status_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_job_status_events_user_id_foreign` (`user_id`),
  ADD KEY `job_status_tenant_job_changed_idx` (`tenant_id`,`service_job_id`,`changed_at`),
  ADD KEY `job_status_job_status_idx` (`service_job_id`,`new_status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_tenant_id_unique` (`tenant_id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_tenant_id_name_unique` (`tenant_id`,`name`);

--
-- Indexes for table `team_payments`
--
ALTER TABLE `team_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_payments_service_job_id_foreign` (`service_job_id`),
  ADD KEY `team_payments_team_id_foreign` (`team_id`),
  ADD KEY `team_payments_user_id_foreign` (`user_id`),
  ADD KEY `team_payments_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `team_payments_tenant_id_paid_at_index` (`tenant_id`,`paid_at`);

--
-- Indexes for table `team_user`
--
ALTER TABLE `team_user`
  ADD PRIMARY KEY (`team_id`,`user_id`),
  ADD KEY `team_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_slug_unique` (`slug`),
  ADD KEY `tenants_created_by_foreign` (`created_by`);

--
-- Indexes for table `tenant_user`
--
ALTER TABLE `tenant_user`
  ADD PRIMARY KEY (`tenant_id`,`user_id`),
  ADD KEY `tenant_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_entries_user_id_foreign` (`user_id`),
  ADD KEY `time_entries_tenant_id_started_at_index` (`tenant_id`,`started_at`),
  ADD KEY `time_entries_service_job_id_user_id_index` (`service_job_id`,`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_payments`
--
ALTER TABLE `customer_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `job_work_events`
--
ALTER TABLE `job_work_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_jobs`
--
ALTER TABLE `service_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_job_items`
--
ALTER TABLE `service_job_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service_job_status_events`
--
ALTER TABLE `service_job_status_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `team_payments`
--
ALTER TABLE `team_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `time_entries`
--
ALTER TABLE `time_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_payments_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD CONSTRAINT `expense_categories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_work_events`
--
ALTER TABLE `job_work_events`
  ADD CONSTRAINT `job_work_events_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_work_events_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_work_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_tenant_user`
--
ALTER TABLE `role_tenant_user`
  ADD CONSTRAINT `role_tenant_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_tenant_user_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_tenant_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD CONSTRAINT `service_categories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_jobs`
--
ALTER TABLE `service_jobs`
  ADD CONSTRAINT `service_jobs_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_jobs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_jobs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_jobs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_job_items`
--
ALTER TABLE `service_job_items`
  ADD CONSTRAINT `service_job_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_job_items_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_job_status_events`
--
ALTER TABLE `service_job_status_events`
  ADD CONSTRAINT `service_job_status_events_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_job_status_events_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_job_status_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_payments`
--
ALTER TABLE `team_payments`
  ADD CONSTRAINT `team_payments_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_payments_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `team_user`
--
ALTER TABLE `team_user`
  ADD CONSTRAINT `team_user_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_user`
--
ALTER TABLE `tenant_user`
  ADD CONSTRAINT `tenant_user_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD CONSTRAINT `time_entries_service_job_id_foreign` FOREIGN KEY (`service_job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_entries_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `time_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
