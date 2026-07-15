-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2026 at 09:02 PM
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
-- Database: `electrical_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `as_electric`
--

CREATE TABLE `as_electric` (
  `electric_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `nama` varchar(125) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `voltage` varchar(50) DEFAULT NULL,
  `voltage_unit` varchar(10) DEFAULT NULL,
  `ampere` varchar(50) DEFAULT NULL,
  `daya` varchar(50) DEFAULT NULL,
  `daya_unit` varchar(10) DEFAULT NULL,
  `location` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `editor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `as_electric`
--

INSERT INTO `as_electric` (`electric_id`, `type_id`, `nama`, `brand`, `type`, `voltage`, `voltage_unit`, `ampere`, `daya`, `daya_unit`, `location`, `image`, `created_at`, `updated_at`, `editor`) VALUES
('ELC-LES-001', 47, 'LESER RADIATOR', 'RADIATORLASER', 'HG45GF', '31', 'V', '22', '22', 'W', 1, NULL, '2026-06-06 21:04:48', '2026-06-06 21:04:48', 'SYSTEM'),
('ELC-PRO-001', 49, 'PROXIMTY SWITCH', 'RADIATORLASER', 'G2G-F', '6', 'V', '4', '330', 'W', 1, NULL, '2026-06-06 02:58:55', '2026-06-06 02:58:55', 'SYSTEM');

-- --------------------------------------------------------

--
-- Table structure for table `as_electric_types`
--

CREATE TABLE `as_electric_types` (
  `id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `as_electric_types`
--

INSERT INTO `as_electric_types` (`id`, `type`, `image`, `created_at`, `updated_at`) VALUES
(19, 'THERMO CONTROL', 'thermo-control-1758858043.png', '2025-09-26 10:40:43', '2025-09-26 10:40:43'),
(20, 'TIMER', 'timer-1758858162.png', '2025-09-26 10:42:42', '2025-09-26 10:42:42'),
(21, 'STRIP HEATER 90', 'strip-heater-90-1758858279.png', '2025-09-26 10:44:39', '2025-09-26 10:44:39'),
(22, 'FORT SWITCHING', 'fort-switching-1758858433.png', '2025-09-26 10:47:13', '2025-09-26 10:47:13'),
(23, 'POWER SUPLAY', 'power-suplay-1758858726.jpeg', '2025-09-26 10:52:06', '2025-09-26 10:52:06'),
(25, 'RELAY', 'relay-1758871831.png', '2025-09-26 14:30:31', '2025-09-26 14:30:31'),
(26, 'PLC', 'plc-1759109219.png', '2025-09-29 08:26:59', '2025-09-29 08:26:59'),
(27, 'MCB 1 PHASE', 'mcb-1-phase-1759110827.png', '2025-09-29 08:53:47', '2025-09-29 08:53:47'),
(28, 'THERMOCOUPLPE R', 'thermocouplpe-r-1759114195.png', '2025-09-29 09:49:55', '2025-09-29 09:49:55'),
(29, 'MCB SHILINDER 2', 'mcb-shilinder-2-1759116900.png', '2025-09-29 10:35:00', '2025-09-29 10:35:00'),
(30, 'DRIVER MOTOR', 'driver-motor-1759117093.png', '2025-09-29 10:38:13', '2025-09-29 10:38:13'),
(31, 'PHOTO ELECTRIK', 'photo-electrik-1759118584.png', '2025-09-29 11:03:04', '2025-09-29 11:03:04'),
(32, 'PROXIMITY SENSOR', 'proximity-sensor-1759199934.jpeg', '2025-09-29 11:16:26', '2025-09-30 09:38:54'),
(33, 'PSU ANDON', 'psu-andon-1759121244.png', '2025-09-29 11:47:24', '2025-09-29 11:47:24'),
(34, 'PSU KECIL', 'psu-kecil-1759121686.png', '2025-09-29 11:54:46', '2025-09-29 11:54:46'),
(35, 'MOTOR STEPER', 'motor-steper-1759126399.png', '2025-09-29 13:13:19', '2025-09-29 13:13:19'),
(37, '5 PHASE STEPPING MOTOR', '5-phase-stepping-motor-1759129024.png', '2025-09-29 13:57:04', '2025-09-29 13:57:04'),
(38, 'SSR', 'ssr-1759129872.png', '2025-09-29 14:11:12', '2025-09-29 14:11:12'),
(39, 'PLC XPAN', 'plc-xpan-1759131573.png', '2025-09-29 14:39:33', '2025-09-29 14:39:33'),
(40, 'TRAVO', NULL, '2025-09-30 09:00:08', '2025-09-30 09:00:08'),
(41, 'REMOTE CONTROL SWITCH', NULL, '2025-09-30 09:06:03', '2025-09-30 09:06:03'),
(42, 'LOGIC PANEL', 'logic-panel-1759198220.jpg', '2025-09-30 09:10:20', '2025-09-30 09:10:20'),
(43, 'HMI', 'hmi-1759198457.jpg', '2025-09-30 09:14:17', '2025-09-30 09:14:17'),
(44, 'MAGNETIC SENSOR MERK', 'magnetic-sensor-merk-1759198685.jpeg', '2025-09-30 09:18:05', '2025-09-30 09:18:05'),
(45, 'PHOTO SENSOR', 'photo-sensor-1759199012.jpeg', '2025-09-30 09:23:32', '2025-09-30 09:23:32'),
(46, 'CONECTOR JUNOTION', 'conector-junotion-1759199220.jpeg', '2025-09-30 09:27:00', '2025-09-30 09:27:00'),
(47, 'LESER RADIATOR', 'leser-radiator-1759199377.png', '2025-09-30 09:29:37', '2025-09-30 09:29:37'),
(49, 'PROXIMTY SWITCH', 'proximty-switch-1759200005.jpeg', '2025-09-30 09:40:05', '2025-09-30 09:40:05'),
(50, 'SMART SWITCH', NULL, '2025-09-30 09:52:20', '2025-09-30 09:52:20');

-- --------------------------------------------------------

--
-- Table structure for table `as_history`
--

CREATE TABLE `as_history` (
  `id` int(11) NOT NULL,
  `electric_id` varchar(50) NOT NULL,
  `user_nik` varchar(20) NOT NULL,
  `type` enum('Masuk','Keluar','Audit') NOT NULL,
  `amount` int(11) NOT NULL,
  `qty_sisa` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `po_number` varchar(64) DEFAULT NULL,
  `distributor` varchar(128) DEFAULT NULL,
  `tanggal_pesan` date DEFAULT NULL,
  `tanggal_terima` date DEFAULT NULL,
  `harga_satuan` decimal(14,2) DEFAULT NULL,
  `po_id` int(11) DEFAULT NULL,
  `wo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_history`
--

INSERT INTO `as_history` (`id`, `electric_id`, `user_nik`, `type`, `amount`, `qty_sisa`, `keterangan`, `created_at`, `po_number`, `distributor`, `tanggal_pesan`, `tanggal_terima`, `harga_satuan`, `po_id`, `wo_id`) VALUES
(1, 'ELC-PRO-001', '223016012', 'Masuk', 10, 0, 'Penerimaan otomatis dari PO', '2026-06-06 14:06:17', NULL, NULL, NULL, '2026-06-06', 30000.00, 1, NULL),
(2, 'ELC-PRO-001', '223016012', 'Masuk', 10, 9, 'Penerimaan otomatis dari PO', '2026-06-06 14:06:40', NULL, NULL, NULL, '2026-06-06', 30000.00, 2, NULL),
(3, 'ELC-LES-001', '223016012', 'Masuk', 10, 0, 'Penerimaan otomatis dari PO', '2026-06-06 14:07:04', NULL, NULL, NULL, '2026-06-06', 22000.00, 3, NULL),
(4, 'ELC-LES-001', '223016012', 'Masuk', 10, 8, 'Penerimaan otomatis dari PO', '2026-06-06 14:07:40', NULL, NULL, NULL, '2026-06-06', 22000.00, 4, NULL),
(5, 'ELC-PRO-001', '223016000', 'Keluar', 10, 0, 'Pengambilan oleh Teknisi: 10 dari Batch #1', '2026-06-06 14:08:35', 'FIFO-B1 (Tgl: 06 Jun 2026)', NULL, NULL, '2026-06-06', NULL, NULL, 1),
(6, 'ELC-PRO-001', '223016000', 'Keluar', 1, 0, 'Pengambilan oleh Teknisi: 1 dari Batch #2', '2026-06-06 14:08:35', 'FIFO-B2 (Tgl: 06 Jun 2026)', NULL, NULL, '2026-06-06', NULL, NULL, 1),
(7, 'ELC-LES-001', '223016000', 'Keluar', 10, 0, 'Pengambilan oleh Teknisi: 10 dari Batch #3', '2026-06-06 14:09:18', 'FIFO-B3 (Tgl: 06 Jun 2026)', NULL, NULL, '2026-06-06', NULL, NULL, 1),
(8, 'ELC-LES-001', '223016000', 'Keluar', 2, 0, 'Pengambilan oleh Teknisi: 2 dari Batch #4', '2026-06-06 14:09:18', 'FIFO-B4 (Tgl: 06 Jun 2026)', NULL, NULL, '2026-06-06', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `as_location`
--

CREATE TABLE `as_location` (
  `id` int(11) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `as_location`
--

INSERT INTO `as_location` (`id`, `location_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Lemari Kaca', NULL, '2026-05-13 00:28:07', NULL),
(3, 'RAK I3', NULL, NULL, NULL),
(4, 'RAK DALAM OFFICE', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `as_po_details`
--

CREATE TABLE `as_po_details` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `electric_id` varchar(50) NOT NULL,
  `qty_ordered` int(11) NOT NULL,
  `price` decimal(14,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_po_details`
--

INSERT INTO `as_po_details` (`id`, `po_id`, `electric_id`, `qty_ordered`, `price`) VALUES
(1, 1, 'ELC-PRO-001', 10, 30000.00),
(2, 2, 'ELC-PRO-001', 10, 30000.00),
(3, 3, 'ELC-LES-001', 10, 22000.00),
(4, 4, 'ELC-LES-001', 10, 22000.00),
(5, 5, 'ELC-LES-001', 10, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `as_purchase_orders`
--

CREATE TABLE `as_purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(100) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_purchase_orders`
--

INSERT INTO `as_purchase_orders` (`id`, `po_number`, `supplier_id`, `order_date`, `status`, `created_at`) VALUES
(1, 'PO-20260606-001', 1, '2026-06-05', 'Completed', '2026-06-06 21:06:11'),
(2, 'PO-20260606-002', 1, '2026-06-06', 'Completed', '2026-06-06 21:06:34'),
(3, 'PO-20260606-003', 1, '2026-06-05', 'Completed', '2026-06-06 21:06:56'),
(4, 'PO-20260606-004', 1, '2026-06-06', 'Completed', '2026-06-06 21:07:36'),
(5, 'PO-20260626-001', 1, '0000-00-00', 'Pending', '2026-06-27 01:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `as_suppliers`
--

CREATE TABLE `as_suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_suppliers`
--

INSERT INTO `as_suppliers` (`id`, `supplier_name`, `contact_person`, `phone`, `address`, `created_at`) VALUES
(1, 'PT Abadi jaya ', 'Sitompul', '087815829251', 'jl kutilang V no 43 gilingan', '2026-06-06 21:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `as_user`
--

CREATE TABLE `as_user` (
  `nik` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('Staf Gudang','Manajer OE','Teknisi') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `editor` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_user`
--

INSERT INTO `as_user` (`nik`, `name`, `password`, `role`, `created_at`, `updated_at`, `editor`) VALUES
('223016000', 'Bahlil', '$2y$10$PxaeiFJNw3CMG98w.GqYnuZgl9rbmWs7jCxO7.8dD0277HBDchB9q', 'Teknisi', '2026-06-05 14:31:10', '2026-06-05 14:31:10', '223016012'),
('223016002', 'Jon', '$2y$10$lwpR3bWrYesFTIiUeVzdNu8MkL9BMbtfhnZ/.CbFa2VwX9ybugt.m', 'Manajer OE', '2026-05-13 01:21:46', '2026-05-16 11:25:01', '223016012'),
('223016012', 'Ayusiawan Ryan', '$2y$10$Y6SoezpZvsTKEwef183JC.2k2CwVwFGpLZszRAqFDRiDt0.JbadBK', 'Staf Gudang', '2026-05-13 01:21:40', '2026-06-26 09:30:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `as_work_orders`
--

CREATE TABLE `as_work_orders` (
  `id` int(11) NOT NULL,
  `wo_number` varchar(100) NOT NULL,
  `project_name` varchar(150) NOT NULL,
  `request_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `as_work_orders`
--

INSERT INTO `as_work_orders` (`id`, `wo_number`, `project_name`, `request_date`, `created_at`) VALUES
(1, 'WO-20260606-001', 'line 8 aoi 2', '2026-06-06', '2026-06-06 21:08:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `as_electric`
--
ALTER TABLE `as_electric`
  ADD PRIMARY KEY (`electric_id`),
  ADD KEY `fk_as_electric_location` (`location`),
  ADD KEY `fk_as_electric_type` (`type_id`);

--
-- Indexes for table `as_electric_types`
--
ALTER TABLE `as_electric_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_unique` (`type`);

--
-- Indexes for table `as_history`
--
ALTER TABLE `as_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_as_history_electric` (`electric_id`),
  ADD KEY `fk_hist_user` (`user_nik`),
  ADD KEY `fk_hist_po` (`po_id`),
  ADD KEY `fk_hist_wo` (`wo_id`);

--
-- Indexes for table `as_location`
--
ALTER TABLE `as_location`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_name` (`location_name`);

--
-- Indexes for table `as_po_details`
--
ALTER TABLE `as_po_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_po` (`po_id`),
  ADD KEY `fk_po_elec` (`electric_id`);

--
-- Indexes for table `as_purchase_orders`
--
ALTER TABLE `as_purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `fk_supplier` (`supplier_id`);

--
-- Indexes for table `as_suppliers`
--
ALTER TABLE `as_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `as_user`
--
ALTER TABLE `as_user`
  ADD PRIMARY KEY (`nik`);

--
-- Indexes for table `as_work_orders`
--
ALTER TABLE `as_work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wo_number` (`wo_number`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `as_electric`
--
ALTER TABLE `as_electric`
  ADD CONSTRAINT `fk_as_electric_location` FOREIGN KEY (`location`) REFERENCES `as_location` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_as_electric_type` FOREIGN KEY (`type_id`) REFERENCES `as_electric_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `as_history`
--
ALTER TABLE `as_history`
  ADD CONSTRAINT `fk_as_history_electric` FOREIGN KEY (`electric_id`) REFERENCES `as_electric` (`electric_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_po` FOREIGN KEY (`po_id`) REFERENCES `as_purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hist_user` FOREIGN KEY (`user_nik`) REFERENCES `as_user` (`nik`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hist_wo` FOREIGN KEY (`wo_id`) REFERENCES `as_work_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `as_po_details`
--
ALTER TABLE `as_po_details`
  ADD CONSTRAINT `fk_po` FOREIGN KEY (`po_id`) REFERENCES `as_purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_po_elec` FOREIGN KEY (`electric_id`) REFERENCES `as_electric` (`electric_id`) ON DELETE CASCADE;

--
-- Constraints for table `as_purchase_orders`
--
ALTER TABLE `as_purchase_orders`
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `as_suppliers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
