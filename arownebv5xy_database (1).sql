-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th10 27, 2025 lúc 10:21 AM
-- Phiên bản máy phục vụ: 10.11.11-MariaDB-cll-lve
-- Phiên bản PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `arownebv5xy_database`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `bank_code` varchar(20) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `qr_code_url` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bank_code`, `bank_name`, `account_number`, `account_name`, `qr_code_url`, `status`, `created_at`) VALUES
(3, 'MB', 'MB Bank', '127969999', 'NGUYEN HUU THANH', 'https://i.ibb.co/67GMqNFB/image.png', 'active', '2025-10-24 07:26:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_settings`
--

CREATE TABLE `contact_settings` (
  `id` int(11) NOT NULL,
  `contact_type` varchar(50) NOT NULL,
  `contact_value` varchar(255) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `contact_settings`
--

INSERT INTO `contact_settings` (`id`, `contact_type`, `contact_value`, `display_name`, `icon`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'email', 'hotronify@gmail.com', 'Email', 'fas fa-envelope', 1, 1, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(2, 'phone', '0898 686 001', 'Hotline', 'fas fa-phone', 1, 2, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(3, 'facebook', 'https://facebook.com/nify.support', 'Facebook', 'fab fa-facebook', 1, 3, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(4, 'telegram', 'https://t.me/nifysupport', 'Telegram', 'fab fa-telegram', 1, 4, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(5, 'zalo', '0898 686 001', 'Zalo', 'fas fa-comments', 1, 5, '2025-10-27 08:53:14', '2025-10-27 08:53:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bank_code` varchar(20) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `target_page` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `target_page`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Hi', 'Hiiii', 'success', '', 1, '2025-10-27 15:55:00', '2025-10-30 15:55:00', '2025-10-27 08:55:37', '2025-10-27 08:55:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `operating_systems`
--

CREATE TABLE `operating_systems` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `min_ram_gb` int(11) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `operating_systems`
--

INSERT INTO `operating_systems` (`id`, `name`, `min_ram_gb`, `status`, `created_at`) VALUES
(1, 'CentOS-8', 1, 'active', '2025-10-24 07:26:51'),
(2, 'AlmaLinux-9', 1, 'active', '2025-10-24 07:26:51'),
(3, 'CentOS-7', 1, 'active', '2025-10-24 07:26:51'),
(4, 'Ubuntu-22.04-Jammy', 1, 'active', '2025-10-24 07:26:51'),
(5, 'Ubuntu-24.04-Noble-Numbat', 1, 'active', '2025-10-24 07:26:51'),
(6, 'Ubuntu-18.04-Bionic', 1, 'active', '2025-10-24 07:26:51'),
(7, 'Ubuntu-16.04-Xenial', 1, 'active', '2025-10-24 07:26:51'),
(8, 'Ubuntu-20.04-Focal', 1, 'active', '2025-10-24 07:26:51'),
(9, 'Debian-10', 1, 'active', '2025-10-24 07:26:51'),
(10, 'Debian-12', 1, 'active', '2025-10-24 07:26:51'),
(11, 'AlmaLinux-8', 1, 'active', '2025-10-24 07:26:51'),
(12, 'Windows-Server-2012-Emulator', 1, 'active', '2025-10-24 07:26:51'),
(13, 'Windows-Server-2016-Datacenter', 4, 'active', '2025-10-24 07:26:51'),
(14, 'Windows-Server-2012-Datacenter', 1, 'active', '2025-10-24 07:26:51'),
(15, 'Windows-Server-2022-Datacenter', 4, 'active', '2025-10-24 07:26:51'),
(16, 'Windows-10-Profestional-64Bit', 4, 'active', '2025-10-24 07:26:51'),
(17, 'Windows-7-Professional-64Bit', 4, 'active', '2025-10-24 07:26:51'),
(18, 'Windows-Server-2019-Datacenter', 4, 'active', '2025-10-24 07:26:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `renewals`
--

CREATE TABLE `renewals` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `months` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_expiry_date` date DEFAULT NULL,
  `new_expiry_date` date DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `renewals`
--

INSERT INTO `renewals` (`id`, `order_id`, `user_id`, `months`, `price`, `old_expiry_date`, `new_expiry_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 52500.00, '2025-11-24', '2025-12-24', 'completed', '2025-10-24 09:25:06', '2025-10-24 09:25:06'),
(2, 2, 2, 1, 24700.00, '2025-11-24', '2025-12-24', 'completed', '2025-10-24 09:43:45', '2025-10-24 09:43:45'),
(3, 2, 2, 1, 24700.00, '2025-12-24', '2026-01-24', 'completed', '2025-10-24 09:44:04', '2025-10-24 09:44:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `seo_settings`
--

CREATE TABLE `seo_settings` (
  `id` int(11) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `robots` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `seo_settings`
--

INSERT INTO `seo_settings` (`id`, `page_name`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `og_image`, `canonical_url`, `robots`, `created_at`, `updated_at`) VALUES
(1, 'home', 'VPS NAT Giá R? | VPS Ch?t L??ng Cao', 'Cung c?p d?ch v? VPS NAT giá r? nh?t Vi?t Nam, ch?t l??ng cao, h? tr? 24/7', 'vps nat, vps giá r?, vps ch?t l??ng cao, hosting, server', NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(2, 'packages', 'Gói VPS | VPS NAT & VPS Cheap', 'Các gói VPS ?a d?ng v?i giá c? ph?i ch?ng, phù h?p m?i nhu c?u', 'gói vps, vps nat, vps cheap, giá vps, mua vps', NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(3, 'services', 'Qu?n Lý VPS | D?ch V? VPS', 'D?ch v? qu?n lý VPS chuyên nghi?p, h? tr? k? thu?t 24/7', 'qu?n lý vps, d?ch v? vps, h? tr? vps, k? thu?t vps', NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(4, 'contact', 'Liên H? | VPS NAT', 'Thông tin liên h?, h? tr? khách hàng 24/7', 'liên h? vps, h? tr? vps, contact vps', NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(5, 'about', 'V? Chúng Tôi | VPS NAT', 'Gi?i thi?u v? VPS NAT, ??i ng? và d?ch v?', 'v? chúng tôi, gi?i thi?u vps nat, ??i ng? vps', NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:14', '2025-10-27 08:53:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','textarea','image','number') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'VPS NAT', 'text', 'Tên website', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(2, 'site_description', 'D?ch v? VPS ch?t l??ng cao v?i giá c? ph?i ch?ng', 'textarea', 'Mô t? website', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(3, 'site_logo', 'https://i.ibb.co/7mzR5qs/image.png', 'image', 'Logo website', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(4, 'site_favicon', '', 'image', 'Favicon website', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(5, 'company_name', 'VPS NAT Company', 'text', 'Tên công ty', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(6, 'company_address', 'Hà N?i, Vi?t Nam', 'textarea', '??a ch? công ty', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(7, 'company_email', 'hotronify@gmail.com', 'text', 'Email công ty', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(8, 'company_phone', '0898 686 001', 'text', 'S? ?i?n tho?i công ty', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(9, 'company_hotline', '0898 686 001', 'text', 'Hotline', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(10, 'facebook_url', '#', 'text', 'URL Facebook', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(11, 'telegram_url', '#', 'text', 'URL Telegram', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(12, 'youtube_url', '#', 'text', 'URL YouTube', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(13, 'cookie_expiry_days', '30', 'number', 'S? ngày l?u cookie', '2025-10-27 08:53:14', '2025-10-27 08:53:14'),
(14, 'maintenance_mode', 'false', 'text', 'Ch? ?? b?o trì', '2025-10-27 08:53:14', '2025-10-27 08:53:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `telegram_settings`
--

CREATE TABLE `telegram_settings` (
  `id` int(11) NOT NULL,
  `bot_token` varchar(255) NOT NULL,
  `chat_id` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `balance`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'hotronify', 'hotronify@gmail.com', '$2y$10$pD6ftHibLzQSbJkB2lUC7.6R6kwGeMGbNbzocR1SuiHnqkB2otc8K', 'NGUYEN HUU THANH', '0764780360', 388200.00, 'admin', 'active', '2025-10-24 10:04:47', '2025-10-26 04:10:08'),
(4, 'Kekee', 'thanhda4329q@gmail.com', '$2y$10$NOyi..HHGgKgGB6CVPeow./AKTwBLSC6FsUlSTlDLmcIrBaUQXVU.', 'Muốn bú theme admin thôi tại nó đẹp:)', '0986754213', 0.00, 'user', 'active', '2025-10-24 11:10:20', '2025-10-24 11:10:20'),
(5, 'nanhkiet', 'nanhkiet.me@gmail.com', '$2y$10$Fj4wItBcP.EAbfBdgpw8YefAHVMCR7TVNy.vddyTyxahqSSqa/Pei', 'Nguyễn Anh Kiệt', '0356927825', 0.00, 'user', 'active', '2025-10-24 11:17:52', '2025-10-24 11:17:52'),
(6, 'ducapivn', 'Vancongduc2703@gmail.com', '$2y$10$EQc3JpGyd/t.J0MWhQMJ1uyQ/e07qPB1xm867ZO9N7ncUa9ziXPFS', 'Văn đức', '0843251372', 0.00, 'user', 'active', '2025-10-24 13:49:20', '2025-10-24 13:49:20'),
(7, 'ntvvstwztw', 'yszlrlew@testform.xyz', '$2y$10$bRIqIAZqlScE4i9JTvLXzeXGn7dt8jyPffSw4F0nNG0eae.CR2y5S', 'hmdmyvjfud', '+1-275-842-7559', 0.00, 'user', 'active', '2025-10-25 09:58:18', '2025-10-25 09:58:18'),
(8, 'thanhhan', 'game.v8403@gmail.com', '$2y$10$jM1cbHnXRqeT/2RT5IXnW.F0phhLAoxPdVBKseFRtOMxG05nsebaC', 'Nguyen Thanh Han', 'thanhhan', 0.00, 'user', 'active', '2025-10-26 19:12:12', '2025-10-26 19:12:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vps_orders`
--

CREATE TABLE `vps_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `os_id` int(11) NOT NULL,
  `billing_cycle` enum('1','6','12','24') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','active','expired','cancelled') DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `vps_orders`
--

INSERT INTO `vps_orders` (`id`, `user_id`, `package_id`, `os_id`, `billing_cycle`, `price`, `total_price`, `status`, `ip_address`, `username`, `password`, `purchase_date`, `expiry_date`, `notes`, `created_at`, `updated_at`) VALUES
(4, 3, 278, 11, '1', 37700.00, 37700.00, 'active', '139.99.44.189', 'hotronify', 'nhthanh_dev', '2025-10-25', '2025-11-25', NULL, '2025-10-25 04:35:32', '2025-10-25 04:37:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vps_packages`
--

CREATE TABLE `vps_packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cpu` varchar(50) NOT NULL,
  `ram` varchar(50) NOT NULL,
  `storage` varchar(50) NOT NULL,
  `bandwidth` varchar(50) NOT NULL,
  `port_speed` text NOT NULL,
  `ip` text NOT NULL DEFAULT 'N/A',
  `original_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `category` enum('nat','cheap') NOT NULL DEFAULT 'nat',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `vps_packages`
--

INSERT INTO `vps_packages` (`id`, `name`, `cpu`, `ram`, `storage`, `bandwidth`, `port_speed`, `ip`, `original_price`, `selling_price`, `category`, `status`, `created_at`, `updated_at`) VALUES
(277, 'Cheap NAT 1', '1.00 vCore', '512 MB', '15 GB SSD', 'Unlimited Bandwidth', '30Mbps', '01 IP NAT', 19000.00, 24700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:11'),
(278, 'Cheap NAT 2', '1.00 vCore', '1 GB', '15 GB SSD', 'Unlimited Bandwidth', '40Mbps', '01 IP NAT', 29000.00, 37700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:14'),
(279, 'Cheap NAT 3', '2.00 vCore', '2 GB', '20 GB SSD', 'Unlimited Bandwidth', '50Mbps', '01 IP NAT', 59000.00, 76700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:17'),
(280, 'Cheap NAT 4', '2.00 vCore', '4 GB', '25 GB SSD', 'Unlimited Bandwidth', '60Mbps', '01 IP NAT', 89000.00, 115700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:20'),
(281, 'Cheap NAT 5', '3.00 vCore', '6 GB', '30 GB SSD', 'Unlimited Bandwidth', '80Mbps', '01 IP NAT', 119000.00, 154700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:33'),
(282, 'Cheap NAT 6', '4.00 vCore', '8 GB', '60 GB SSD', 'Unlimited Bandwidth', '100Mbps', '01 IP NAT', 179000.00, 232700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:31'),
(283, 'Cheap NAT 7', '6.00 vCore', '12 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 419000.00, 544700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:29'),
(284, 'Cheap NAT 8', '8.00 vCore', '16 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 519000.00, 674700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:27'),
(285, 'Cheap NAT 9', '16.00 vCore', '32 GB', '120 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 999000.00, 1298700.00, 'nat', 'active', '2025-10-24 09:40:43', '2025-10-25 04:27:24'),
(286, 'VPS Cheap 1', '1.00 vCore', '1 GB', '20 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 59000.00, 76700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(287, 'VPS Cheap 2', '1.00 vCore', '2 GB', '25 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 89000.00, 115700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(288, 'VPS Cheap 3', '2.00 vCore', '4 GB', '40 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 159000.00, 206700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(289, 'VPS Cheap 4', '4.00 vCore', '8 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', 'N/A', 319000.00, 414700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(290, 'VPS Cheap 5', '6.00 vCore', '8 GB', '80 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 399000.00, 518700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(291, 'VPS Cheap 6', '6.00 vCore', '12 GB', '100 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 499000.00, 648700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(292, 'VPS Cheap 7', '8.00 vCore', '16 GB', '150 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 599000.00, 778700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(293, 'VPS Cheap 8', '10.00 vCore', '20 GB', '180 GB SSD', 'Unlimited Bandwidth', '500Mbps', 'N/A', 799000.00, 1038700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57'),
(294, 'VPS Cheap 9', '16.00 vCore', '32 GB', '200 GB SSD', 'Unlimited Bandwidth', '500Mbps', 'N/A', 1199000.00, 1558700.00, 'cheap', 'active', '2025-10-24 09:40:43', '2025-10-25 04:26:57');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `contact_settings`
--
ALTER TABLE `contact_settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `operating_systems`
--
ALTER TABLE `operating_systems`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `renewals`
--
ALTER TABLE `renewals`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `seo_settings`
--
ALTER TABLE `seo_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_name` (`page_name`);

--
-- Chỉ mục cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Chỉ mục cho bảng `telegram_settings`
--
ALTER TABLE `telegram_settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `vps_orders`
--
ALTER TABLE `vps_orders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `vps_packages`
--
ALTER TABLE `vps_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `contact_settings`
--
ALTER TABLE `contact_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `operating_systems`
--
ALTER TABLE `operating_systems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `renewals`
--
ALTER TABLE `renewals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `seo_settings`
--
ALTER TABLE `seo_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `telegram_settings`
--
ALTER TABLE `telegram_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `vps_orders`
--
ALTER TABLE `vps_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `vps_packages`
--
ALTER TABLE `vps_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
