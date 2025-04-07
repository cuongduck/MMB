-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 07, 2025 at 12:43 PM
-- Server version: 10.6.21-MariaDB-cll-lve-log
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pnlekoychosting_Report`
--

-- --------------------------------------------------------

--
-- Table structure for table `production_lines`
--

CREATE TABLE `production_lines` (
  `id` int(11) NOT NULL,
  `line_name` varchar(50) NOT NULL,
  `line_code` varchar(20) NOT NULL,
  `factory` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_lines`
--

INSERT INTO `production_lines` (`id`, `line_name`, `line_code`, `factory`, `created_at`) VALUES
(1, 'Line 1', 'LINE1', 'Noodle', '2025-04-06 09:01:52'),
(2, 'Line 2', 'LINE2', 'Noodle', '2025-04-06 09:01:52'),
(3, 'Line 3', 'LINE3', 'Noodle', '2025-04-06 09:01:52'),
(4, 'Line 4', 'LINE4', 'Noodle', '2025-04-06 09:01:52'),
(5, 'Line 5', 'LINE5', 'Noodle', '2025-04-06 09:01:52'),
(6, 'Line 6', 'LINE6', 'Noodle', '2025-04-06 09:01:52'),
(7, 'Line 7', 'LINE7', 'Noodle', '2025-04-06 09:01:52'),
(8, 'Line 8', 'LINE8', 'Noodle', '2025-04-06 09:01:52'),
(9, 'Line CSD', 'CSD', 'ED', '2025-04-06 09:01:52'),
(10, 'Line FS', 'FS', 'FS', '2025-04-06 09:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `production_plans`
--

CREATE TABLE `production_plans` (
  `id` int(11) NOT NULL,
  `line_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `planned_quantity` int(11) NOT NULL,
  `actual_quantity` int(11) DEFAULT 0,
  `total_personnel` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_plans`
--

INSERT INTO `production_plans` (`id`, `line_id`, `product_id`, `start_time`, `end_time`, `planned_quantity`, `actual_quantity`, `total_personnel`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2025-04-06 09:25:00', '2025-04-06 18:25:00', 12, 0, 0, NULL, 2, '2025-04-06 09:37:10', '2025-04-06 09:40:53'),
(5, 2, 9, '2025-04-07 10:30:00', '2025-04-08 20:14:00', 3, 0, 21, '', 2, '2025-04-06 10:14:40', '2025-04-07 05:15:50'),
(7, 3, 8, '2025-04-08 03:30:00', '2025-04-12 04:48:00', 2, 0, 21, '', 2, '2025-04-07 04:48:44', '2025-04-07 05:13:44'),
(8, 5, 26, '2025-04-08 07:30:00', '2025-04-10 04:50:00', 6, 0, 21, '', 2, '2025-04-07 04:50:24', '2025-04-07 05:13:54'),
(9, 6, 1, '2025-04-08 03:30:00', '2025-04-11 04:52:00', 4, 0, 22, '', 2, '2025-04-07 04:52:30', '2025-04-07 05:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_group` varchar(50) NOT NULL,
  `color_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `product_code`, `product_group`, `color_code`, `created_at`) VALUES
(1, 'Mì KK Tôm MB 30 \r\n', '02KK00313', 'KKM65', '#FF6B6B', '2025-04-06 09:01:52'),
(2, 'Mì KK Tôm MB 30 Palm', '02KK00313-1', 'KKM65', '#FF6B6B', '2025-04-06 09:01:52'),
(3, 'Mì KK Tôm MN 100', '02KK00314', 'KKM65', '#FF6B6B', '2025-04-06 09:01:52'),
(4, 'Mì KK Tôm MN 100-Palm', '02KK00314-1', 'KKM65', '#FF6B6B', '2025-04-06 09:01:52'),
(5, 'Mì Kokomi Tôm chua cay (NA) 30gói x 65gr', '02KK00461', 'KKM65', '#FF6B6B\n', '2025-04-06 09:01:52'),
(6, 'Mì Kokomi gà nấu nấm hương 30gói x 65gr', '02KK00443', 'KKM65', '#FF6B6B\n', '2025-04-06 09:01:52'),
(7, 'Mì Omachi lẩu bắp bò riêu cua 8hộp x 300gr', '02OM00740', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(8, 'Mì Omachi Sườn 30gói x 80gr relaunch', '02OM00338\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(9, 'Mì dinh dưỡng khoai tây Omachi xốt Bò hầm 30gói x 81gr', '02OM00770\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(10, 'Mì dinh dưỡng khoai tây Omachi hương vị lẩu tôm càng 30gói x 80gr', '02OM00771\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(11, 'Mì dinh dưỡng khoai tây Omachi mì trộn xốt Spaghetti 30gói x 90gr', '02OM00618\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(17, 'Mì dinh dưỡng khoai tây Omachi hương vị lẩu bắp bò riêu cua 30gói x 80gr', '02OM00772', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(18, ' Mì dinh dưỡng khoai tây Omachi trộn xốt tôm hương vị phô mai trứng muối 30gói x 92gr \r\n', '02OM00871', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(19, ' Mì dinh dưỡng khoai tây Omachi trộn thịt xiên nướng 30gói x 82gr \r\n', '02OM00689\r\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(20, ' Mì dinh dưỡng khoai tây Omachi trộn hương vị thịt xiên nướng 30gói x 90gr', '02OM00794\r\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(21, ' Mì khoai tây Omachi Special Bò hầm xốt vang MN 30gói x 92gr', '02OM00381\r\n', 'OMC', '#FFD166', '2025-04-06 09:01:52'),
(22, 'Mì Kokomi 75 tôm sa tế 30gói x 75gr', '02KK00428', 'KKM75', '#FF7F27', '2025-04-06 09:01:52'),
(23, 'Mì Kokomi 75 bò kim chi 30gói x 75gr ', '02KK00429', 'KKM75', '#FF7F27', '2025-04-06 09:01:52'),
(24, 'Mì Kokomi 75 canh chua tôm 30gói x 75gr', '02KK00430', 'KKM75', '#FF7F27', '2025-04-06 09:01:52'),
(25, 'Mì Kokomi Đại Bò hầm rau thơm 30gói x 75gr \r\n', '02KK00320', 'KKM75', '#FF7F27', '2025-04-06 09:01:52'),
(26, 'KK đại 90 gr MB new', '02KK00361', 'KKM90', '#00A3E0', '2025-04-06 09:01:52'),
(27, 'KK đại 90 gr MB palm \r\n', '02KK00361-1', 'KKM90', '#00A3E0', '2025-04-06 09:01:52'),
(28, 'Mì dinh dưỡng khoai tây Omachi Sườn hầm ngũ quả 24hộp x 72gr\r\n\r\n', '02OM00628', 'OMC-LY', '#FFD166', '2025-04-06 09:01:52'),
(29, 'Mì dinh dưỡng khoai tây Omachi xốt Bò hầm 24hộp x 70gr\r\n\r\n', '02OM00626\r\n', 'OMC-LY', '#FFD166', '2025-04-06 09:01:52'),
(30, 'Mì dinh dưỡng khoai tây Omachi Sườn hầm ngũ quả KM 1chai tương ớt CHIN-SU 250gr - 24ly x 70gr', '02OM00877\r\n', 'OMC-LY', '#FFD166', '2025-04-06 09:01:52'),
(31, ' Mì dinh dưỡng khoai tây Omachi xốt Bò hầm KM 1chai tương ớt CHIN-SU 250gr - 24ly x 69gr', '02OM00876\r\n', 'OMC-LY', '#FFD166', '2025-04-06 09:01:52'),
(32, 'Mì Kokomi Đại hộp Tôm chua cay 24hộp x 65gr', '02KK00321', 'KKM-LY', '#FF6B6B', '2025-04-06 09:01:52'),
(33, 'Mì dinh dưỡng khoai tây Omachi Sườn hầm ngũ quả 18tô x 96gr\r\n\r\n', '02OM00774', 'OMC-TO', '#FFD166', '2025-04-06 09:01:52'),
(34, 'Mì dinh dưỡng khoai tây Omachi xốt Bò hầm 18tô x 93gr\r\n\r\n\r\n', '02OM00775\r\n', 'OMC-TO', '#FFD166', '2025-04-06 09:01:52'),
(35, 'Mì dinh dưỡng khoai tây Omachi hương vị lẩu tôm càng 18tô x 92gr\r\n', '02OM00773\r\n', 'OMC-TO', '#FFD166', '2025-04-06 09:01:52'),
(36, 'Mì Omachi trộn xốt Spaghetti 12tô x 105gr\r\n', '02OM00447\r\n', 'OMC-TO', '#FFD166', '2025-04-06 09:01:52'),
(37, 'Nước mắm Nam Ngư XK XingBao 24chai x 500ml\r\n\r\n', '03NM00710', 'NAM-NGU-500', '#0052A4', '2025-04-06 09:01:52'),
(38, 'Nước mắm Nam Ngư KM 1gói hạt nêm CHIN-SU ngọt Tôm thơm thịt MB 500gr - 3bl x 8chai x 500ml \r\n\r\n', '03NM00838\r\n', 'NAM-NGU-500', '#0052A4', '2025-04-06 09:01:52'),
(39, 'Nước mắm Nam Ngư KM 1gói hạt nêm CHIN-SU ngọt Tôm thơm thịt MN 500gr - 3bl x 8chai x 500ml\r\n', '03NM00837\r\n', 'NAM-NGU-500', '#0052A4', '2025-04-06 09:01:52'),
(40, 'Nước mắm Nam Ngư (QR) 3bl x 8chai x 500ml\r\n', '03NM00787\r\n', 'NAM-NGU-500', '#0052A4', '2025-04-06 09:01:52'),
(41, 'Nước mắm Nam Ngư KM 1gói hạt nêm CHIN-SU ngọt Tôm thơm thịt MB 500gr - 3bl x 6chai x 750ml\r\n', '03NM00840\r\n', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(42, 'Nước mắm Nam Ngư KM 1gói hạt nêm CHIN-SU ngọt Tôm thơm thịt MN 500gr - 3bl x 6chai x 750ml\r\n', '03NM00839\r\n', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(43, 'Nước mắm Nam Ngư KM 18chai TO CHIN-SU MB 100gr - 18chai x 750ml', '03NM00845', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(44, ' Nước mắm Nam Ngư KM 18chai nước chấm Nam Ngư ớt tỏi Lý Sơn MB 100ml - 18chai x 750ml \r\n', '03NM00773\r\n', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(45, 'NN750ml nhãn sau km OTLS\r\n', '88VC00018\r\n', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(46, 'Nước mắm Nam Ngư (QR) 3bl x 6chai x 750ml \r\n', '03NM00788\r\n', 'NAM-NGU-750', '#0052A4', '2025-04-06 09:01:52'),
(47, 'Nước mắm Nam Ngư (QR) 15chai x 900ml', '03NM00791', 'NAM-NGU-900', '#0052A4', '2025-04-06 09:01:52'),
(48, 'Nước chấm Nam Ngư Đệ Nhị (QR) 18chai x 800ml\r\n', '03NM00789\r\n', 'NAM-NGU-800', '#0052A4', '2025-04-06 09:01:52'),
(49, 'Nước chấm Nam Ngư Đệ Nhị KM 1chai - 18chai x 800ml\r\n', '03NM00819\r\n', 'NAM-NGU-800', '#0052A4', '2025-04-06 09:01:52'),
(50, 'Nước chấm Nam Ngư Siêu tiết kiệm 18chai x 800ml\r\n', '03NM259\r\n', 'NAM-NGU-800', '#0052A4', '2025-04-06 09:01:52'),
(51, '08TL001 : Nước tăng lực vị cà phê Wake up 247 - 4bl x 6chai x 330ml\r\n', '08TL001\r\n', 'WAKE-UP-247', '#0052A4', '2025-04-06 09:01:52'),
(52, 'Nước tăng lực vị cà phê Wake up 247 KM 1chai 4bl x 6chai x 330ml', '08TL00095\r\n', 'WAKE-UP-247', '#0052A4', '2025-04-06 09:01:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `production_lines`
--
ALTER TABLE `production_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `line_code` (`line_code`);

--
-- Indexes for table `production_plans`
--
ALTER TABLE `production_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `line_id` (`line_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `production_lines`
--
ALTER TABLE `production_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `production_plans`
--
ALTER TABLE `production_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `production_plans`
--
ALTER TABLE `production_plans`
  ADD CONSTRAINT `fk_plan_line` FOREIGN KEY (`line_id`) REFERENCES `production_lines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_plan_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_plan_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
