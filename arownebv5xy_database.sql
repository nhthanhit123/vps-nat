-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th10 25, 2025 lúc 04:26 AM
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
(12, 'Windows-Server-2012-Emulator', 4, 'active', '2025-10-24 07:26:51'),
(13, 'Windows-Server-2016-Datacenter', 4, 'active', '2025-10-24 07:26:51'),
(14, 'Windows-Server-2012-Datacenter', 4, 'active', '2025-10-24 07:26:51'),
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
(3, 'hotronify', 'hotronify@gmail.com', '$2y$10$pD6ftHibLzQSbJkB2lUC7.6R6kwGeMGbNbzocR1SuiHnqkB2otc8K', 'NGUYEN HUU THANH', '0764780360', 475300.00, 'admin', 'active', '2025-10-24 10:04:47', '2025-10-24 23:14:18'),
(4, 'Kekee', 'thanhda4329q@gmail.com', '$2y$10$NOyi..HHGgKgGB6CVPeow./AKTwBLSC6FsUlSTlDLmcIrBaUQXVU.', 'Muốn bú theme admin thôi tại nó đẹp:)', '0986754213', 0.00, 'user', 'active', '2025-10-24 11:10:20', '2025-10-24 11:10:20'),
(5, 'nanhkiet', 'nanhkiet.me@gmail.com', '$2y$10$Fj4wItBcP.EAbfBdgpw8YefAHVMCR7TVNy.vddyTyxahqSSqa/Pei', 'Nguyễn Anh Kiệt', '0356927825', 0.00, 'user', 'active', '2025-10-24 11:17:52', '2025-10-24 11:17:52'),
(6, 'ducapivn', 'Vancongduc2703@gmail.com', '$2y$10$EQc3JpGyd/t.J0MWhQMJ1uyQ/e07qPB1xm867ZO9N7ncUa9ziXPFS', 'Văn đức', '0843251372', 0.00, 'user', 'active', '2025-10-24 13:49:20', '2025-10-24 13:49:20');

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
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Đang đổ dữ liệu cho bảng `vps_packages`
--

INSERT INTO `vps_packages` (`id`, `name`, `cpu`, `ram`, `storage`, `bandwidth`, `port_speed`, `ip`, `original_price`, `selling_price`, `status`, `created_at`, `updated_at`) VALUES
(277, 'Cheap NAT 1', '1.00 vCore', '512 MB', '15 GB SSD', 'Unlimited Bandwidth', '30Mbps', '01 IP NAT', 19000.00, 24700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(278, 'Cheap NAT 2', '1.00 vCore', '1 GB', '15 GB SSD', 'Unlimited Bandwidth', '40Mbps', '01 IP NAT', 29000.00, 37700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(279, 'Cheap NAT 3', '2.00 vCore', '2 GB', '20 GB SSD', 'Unlimited Bandwidth', '50Mbps', '01 IP NAT', 59000.00, 76700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(280, 'Cheap NAT 4', '2.00 vCore', '4 GB', '25 GB SSD', 'Unlimited Bandwidth', '60Mbps', '01 IP NAT', 89000.00, 115700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(281, 'Cheap NAT 5', '3.00 vCore', '6 GB', '30 GB SSD', 'Unlimited Bandwidth', '80Mbps', '01 IP NAT', 119000.00, 154700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(282, 'Cheap NAT 6', '4.00 vCore', '8 GB', '60 GB SSD', 'Unlimited Bandwidth', '100Mbps', '01 IP NAT', 179000.00, 232700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(283, 'Cheap NAT 7', '6.00 vCore', '12 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 419000.00, 544700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(284, 'Cheap NAT 8', '8.00 vCore', '16 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 519000.00, 674700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(285, 'Cheap NAT 9', '16.00 vCore', '32 GB', '120 GB SSD', 'Unlimited Bandwidth', '200Mbps', '01 IP NAT', 999000.00, 1298700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(286, 'VPS Cheap 1', '1.00 vCore', '1 GB', '20 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 59000.00, 76700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(287, 'VPS Cheap 2', '1.00 vCore', '2 GB', '25 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 89000.00, 115700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(288, 'VPS Cheap 3', '2.00 vCore', '4 GB', '40 GB SSD', 'Unlimited Bandwidth', '100Mbps', 'N/A', 159000.00, 206700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(289, 'VPS Cheap 4', '4.00 vCore', '8 GB', '80 GB SSD', 'Unlimited Bandwidth', '200Mbps', 'N/A', 319000.00, 414700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(290, 'VPS Cheap 5', '6.00 vCore', '8 GB', '80 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 399000.00, 518700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(291, 'VPS Cheap 6', '6.00 vCore', '12 GB', '100 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 499000.00, 648700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(292, 'VPS Cheap 7', '8.00 vCore', '16 GB', '150 GB SSD', 'Unlimited Bandwidth', '300Mbps', 'N/A', 599000.00, 778700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(293, 'VPS Cheap 8', '10.00 vCore', '20 GB', '180 GB SSD', 'Unlimited Bandwidth', '500Mbps', 'N/A', 799000.00, 1038700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43'),
(294, 'VPS Cheap 9', '16.00 vCore', '32 GB', '200 GB SSD', 'Unlimited Bandwidth', '500Mbps', 'N/A', 1199000.00, 1558700.00, 'active', '2025-10-24 09:40:43', '2025-10-24 09:40:43');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `deposits`
--
ALTER TABLE `deposits`
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
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `vps_orders`
--
ALTER TABLE `vps_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `vps_packages`
--
ALTER TABLE `vps_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=295;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
