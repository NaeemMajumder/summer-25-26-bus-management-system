-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 12:37 PM
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
-- Database: `bus_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `seats_booked` int(11) DEFAULT NULL,
  `boarding_stop_id` int(11) DEFAULT NULL,
  `dropping_stop_id` int(11) DEFAULT NULL,
  `wheelchair` tinyint(1) DEFAULT 0,
  `window_seat` tinyint(1) NOT NULL DEFAULT 0,
  `promo_id` int(11) DEFAULT NULL,
  `total_amount` decimal(8,2) DEFAULT NULL,
  `payment_method` enum('COD','counter','bKash') NOT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT NULL,
  `booking_status` enum('confirmed','cancelled') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `payer_phone` varchar(20) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `trip_id`, `seats_booked`, `boarding_stop_id`, `dropping_stop_id`, `wheelchair`, `window_seat`, `promo_id`, `total_amount`, `payment_method`, `payment_status`, `booking_status`, `created_at`, `payer_phone`, `transaction_id`) VALUES
(26, 16, 7, 4, 4, 6, 0, 0, NULL, 3600.00, 'COD', 'pending', 'confirmed', '2026-09-10 23:25:47', NULL, NULL),
(27, 16, 8, 3, 4, 5, 1, 0, NULL, 2550.00, 'COD', 'pending', '', '2026-09-10 23:39:45', NULL, NULL),
(28, 16, 136, 2, 4, 6, 1, 0, NULL, 1700.00, 'COD', 'paid', 'confirmed', '2026-09-11 00:43:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `bus_number` varchar(30) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` enum('AC','Non-AC') DEFAULT NULL,
  `total_seats` int(11) DEFAULT NULL,
  `status` enum('active','maintenance') DEFAULT NULL,
  `service_trip_limit` int(11) DEFAULT NULL,
  `trips_since_service` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`bus_id`, `bus_number`, `name`, `type`, `total_seats`, `status`, `service_trip_limit`, `trips_since_service`) VALUES
(1, 'DHAKA-BA-1234', 'Green Line', 'AC', 40, 'active', 50, 0),
(2, 'DHAKA-BA-2001', 'Hanif Enterprise', 'AC', 40, 'active', 50, 0),
(3, 'DHAKA-BA-2002', 'Shyamoli NR Travels', 'AC', 36, 'active', 50, 0),
(4, 'DHAKA-BA-2003', 'Ena Transport', 'Non-AC', 40, 'active', 50, 0),
(5, 'DH-5678', 'Hanif Enterprise', NULL, 36, NULL, NULL, 0),
(9, 'TEST-9999', 'TEST BUS', 'AC', 40, 'active', 50, 0);

-- --------------------------------------------------------

--
-- Table structure for table `driver_availability`
--

CREATE TABLE `driver_availability` (
  `availability_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('available','off_day','on_duty') DEFAULT NULL,
  `note` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_availability`
--

INSERT INTO `driver_availability` (`availability_id`, `driver_id`, `date`, `status`, `note`) VALUES
(2, 18, '2026-09-14', 'available', 'helllo'),
(3, 18, '2026-09-13', 'on_duty', 'asdfasf');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 'hi your service is good', '2026-09-01 01:55:55'),
(2, NULL, 'shei', '2026-09-10 08:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `incident_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `type` enum('incident','damage') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','reviewing','resolved') DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incident_reports`
--

INSERT INTO `incident_reports` (`incident_id`, `driver_id`, `trip_id`, `bus_id`, `type`, `description`, `status`, `created_at`) VALUES
(1, 18, 140, 3, 'incident', 'hello', 'open', '2026-09-13 00:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `issue` text DEFAULT NULL,
  `status` enum('pending','in_progress','done','cancelled') DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`request_id`, `bus_id`, `manager_id`, `issue`, `status`, `created_at`) VALUES
(1, 1, 20, 'service issue', 'done', '2026-09-13 02:18:00'),
(2, 2, 20, 'tire issue', 'cancelled', '2026-09-13 02:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `promo_id` int(11) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `discount_type` enum('percent','flat') DEFAULT NULL,
  `discount_value` decimal(6,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`promo_id`, `code`, `discount_type`, `discount_value`, `expiry_date`, `is_active`) VALUES
(1, 'EID2026', 'percent', 10.00, '2026-09-18', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `booking_id`, `bus_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, NULL, NULL, 4, 'good', '2026-09-04 02:40:43'),
(2, 9, NULL, NULL, 5, 'joss', '2026-09-05 00:48:57'),
(3, 9, NULL, NULL, 5, 'hy', '2026-09-05 01:39:20'),
(4, 16, 28, 3, 5, 'best', '2026-09-11 01:12:51'),
(5, 16, 28, 3, 5, 'fr', '2026-09-11 01:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `distance_km` decimal(6,2) DEFAULT NULL,
  `duration` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `origin`, `destination`, `distance_km`, `duration`) VALUES
(1, 'Dhaka', 'Chattogram', 250.00, '6 Hours'),
(2, 'Dhaka', 'Sylhet', 240.00, '6 Hours'),
(3, 'Dhaka', 'Cox\'s Bazar', 390.00, '10 Hours'),
(4, 'Dhaka', 'Rajshahi', 250.00, '6 Hours'),
(5, 'Dhaka', 'Sylhet', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `route_stops`
--

CREATE TABLE `route_stops` (
  `stop_id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `stop_name` varchar(100) DEFAULT NULL,
  `stop_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_stops`
--

INSERT INTO `route_stops` (`stop_id`, `route_id`, `stop_name`, `stop_order`) VALUES
(1, 1, 'Dhaka Counter', 1),
(2, 1, 'Cumilla Counter', 2),
(3, 1, 'Chattogram Counter', 3),
(4, 2, 'Dhaka Counter', 1),
(5, 2, 'Bhairab Counter', 2),
(6, 2, 'Sylhet Counter', 3),
(7, 3, 'Dhaka Counter', 1),
(8, 3, 'Cumilla Counter', 2),
(9, 3, 'Chattogram Counter', 3),
(10, 3, 'Cox\'s Bazar Counter', 4),
(11, 4, 'Dhaka Counter', 1),
(12, 4, 'Tangail Counter', 2),
(13, 4, 'Sirajganj Counter', 3),
(14, 4, 'Rajshahi Counter', 4);

-- --------------------------------------------------------

--
-- Table structure for table `service_history`
--

CREATE TABLE `service_history` (
  `service_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `service_date` date DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `cost` decimal(8,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_history`
--

INSERT INTO `service_history` (`service_id`, `bus_id`, `manager_id`, `service_date`, `work_done`, `cost`, `created_at`) VALUES
(1, 1, 20, '2026-09-16', 'work done', 100.00, '2026-09-13 02:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `service_parts`
--

CREATE TABLE `service_parts` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `part_id` int(11) DEFAULT NULL,
  `quantity_used` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_parts`
--

INSERT INTO `service_parts` (`id`, `service_id`, `part_id`, `quantity_used`) VALUES
(1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `spare_parts`
--

CREATE TABLE `spare_parts` (
  `part_id` int(11) NOT NULL,
  `part_name` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(8,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spare_parts`
--

INSERT INTO `spare_parts` (`part_id`, `part_name`, `stock_quantity`, `unit_price`, `updated_at`) VALUES
(1, 'hello', 0, 10.00, '2026-09-13 02:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `trip_id` int(11) NOT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `trip_date` date DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `fare` decimal(8,2) DEFAULT NULL,
  `available_seats` int(11) DEFAULT NULL,
  `status` enum('scheduled','running','completed','cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`trip_id`, `bus_id`, `route_id`, `driver_id`, `trip_date`, `departure_time`, `arrival_time`, `fare`, `available_seats`, `status`) VALUES
(1, 1, 1, 2, '2026-09-05', '08:00:00', '14:00:00', 1200.00, 46, 'scheduled'),
(2, 2, 2, 2, '2026-09-06', '07:00:00', '13:00:00', 900.00, 40, 'scheduled'),
(3, 3, 3, 2, '2026-09-06', '21:00:00', '07:00:00', 1800.00, 36, 'scheduled'),
(4, 4, 4, 2, '2026-09-06', '08:00:00', '14:00:00', 750.00, 32, 'scheduled'),
(5, 1, 1, NULL, '2026-09-15', '08:00:00', '14:00:00', NULL, 40, NULL),
(6, 4, 4, NULL, '2026-09-15', '08:00:00', '14:00:00', NULL, 40, NULL),
(7, 1, 2, 2, '2026-09-15', '08:00:00', '14:00:00', 900.00, 33, 'scheduled'),
(8, 2, 2, NULL, '2026-09-10', '23:39:15', '23:49:15', 850.00, 33, 'scheduled'),
(9, 1, 1, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(10, 1, 1, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(11, 1, 1, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(12, 2, 1, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(13, 2, 1, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(14, 2, 1, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(15, 3, 1, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(16, 3, 1, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(17, 3, 1, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(18, 4, 1, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(19, 4, 1, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(20, 4, 1, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(21, 5, 1, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(22, 5, 1, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(23, 5, 1, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(24, 1, 2, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(25, 1, 2, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(26, 1, 2, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'completed'),
(27, 2, 2, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'completed'),
(28, 2, 2, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(29, 2, 2, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(30, 3, 2, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(31, 3, 2, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(32, 3, 2, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(33, 4, 2, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(34, 4, 2, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(35, 4, 2, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(36, 5, 2, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(37, 5, 2, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(38, 5, 2, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(39, 1, 3, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(40, 1, 3, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(41, 1, 3, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(42, 2, 3, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(43, 2, 3, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(44, 2, 3, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(45, 3, 3, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(46, 3, 3, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(47, 3, 3, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(48, 4, 3, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(49, 4, 3, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(50, 4, 3, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(51, 5, 3, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(52, 5, 3, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(53, 5, 3, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(54, 1, 4, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(55, 1, 4, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(56, 1, 4, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(57, 2, 4, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(58, 2, 4, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(59, 2, 4, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(60, 3, 4, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(61, 3, 4, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(62, 3, 4, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(63, 4, 4, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(64, 4, 4, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(65, 4, 4, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(66, 5, 4, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(67, 5, 4, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(68, 5, 4, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(69, 1, 5, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(70, 1, 5, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(71, 1, 5, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(72, 2, 5, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(73, 2, 5, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(74, 2, 5, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 40, 'scheduled'),
(75, 3, 5, NULL, '2026-09-13', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(76, 3, 5, NULL, '2026-09-14', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(77, 3, 5, NULL, '2026-09-15', '08:00:00', '13:30:00', 1200.00, 36, 'scheduled'),
(78, 4, 5, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(79, 4, 5, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(80, 4, 5, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 40, 'scheduled'),
(81, 5, 5, NULL, '2026-09-13', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(82, 5, 5, NULL, '2026-09-14', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(83, 5, 5, NULL, '2026-09-15', '21:00:00', '03:30:00', 850.00, 36, 'scheduled'),
(136, 3, 2, NULL, '2026-09-11', '10:00:00', '12:45:00', 850.00, 42, 'completed'),
(137, 3, 2, NULL, '2026-09-11', '10:00:00', '00:40:00', 850.00, 44, 'scheduled'),
(139, 4, 5, 18, '2026-09-13', '01:27:00', '03:28:00', 199.00, 36, 'scheduled'),
(140, 3, 4, 18, '2026-09-13', '00:31:00', '05:30:00', 123.00, 36, 'scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `trip_logs`
--

CREATE TABLE `trip_logs` (
  `log_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `status` enum('started','completed') DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('passenger','driver','admin','manager') NOT NULL,
  `nid_number` varchar(30) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `previous_company` varchar(150) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `role`, `nid_number`, `license_number`, `experience_years`, `previous_company`, `reset_token`, `remember_token`, `created_at`) VALUES
(1, 'alvirahman', 'alvirahman2052@gmail.com', '01316232886', '$2y$10$.FOz0Q9XxydCTVui.Mtey.jFIPJNKNwsgsnSEnigFb3LFhLDYShyG', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 23:01:25'),
(2, 'Test Driver', 'driver@test.com', '01700000000', 'TEMP_PASSWORD', 'driver', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-31 23:24:42'),
(3, 'Alu Bhai', 'alubhai2001@gmail.com', '1316232886', '$2y$10$Gy.2OUv7xYZAkXQqsoqhvO6shcDJasYkcmP0N/u.kvrOxFqsajDH6', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-04 01:06:10'),
(7, 'Alu_Bhai12', 'lkiy@gmail.com', '+880 1316232886', '$2y$10$EWAhdfSAK394lN2jeOuaJ.XOqMkTLj1J/fxUbkBbCgGQafE9T/kNm', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-04 01:27:05'),
(8, 'ali', 'ali@gmail.com', '0123456789', '$2y$10$e4vl5i4WhMa5oZNZri7giOMwSURlc53ZB7.hh3q4FG33N9pIIh2Wm', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-04 01:31:00'),
(9, 'alu', 'a7@gmail.com', '01316232778', '$2y$10$MSDQQey0le/GN3uMJ210c.i6K0viSHynIDlewgvTd196rENOkvure', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-04 23:59:09'),
(12, 'prity', 'prity@gmail.com', '01316232884', '$2y$10$Fhr7LoqR5gdsG7Qs9kgUzenX.lJxjZtASZ6eyc8j5OC7CVKD6yrK2', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-08 00:00:49'),
(15, 'System Admin', 'admin@bus.com', '01700000001', '$2y$10$1xJnK6y35OXDfhXFNBSOleFf2DZjay31tNJVGUDE54f79H9zirxhu', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-09 19:01:06'),
(16, 'alvi boss', 'alvi@gmail.com', '01316232887', '$2y$10$10T9bHkX03KoqqCKF91m8OgwiJYcQtD8VzCWRTBNckeYa6pBrBLw2', 'passenger', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-10 21:41:19'),
(17, 'Hello Passenger', 'hellopassenger@gmail.com', '01233233232', '$2y$10$FrO5n046quRCp9mwSSbgh.55QmQfLDC.WU0bSJKfcxN842JL3kE32', 'passenger', '92738372932', NULL, NULL, NULL, 'dc2df66261f732d95631d8b729dd7851cb788f8402f6bc1cf4dd386de2af0757', NULL, '2026-09-12 14:40:18'),
(18, 'driver', 'driver@gmail.com', '01822322323', '$2y$10$UFOnzZxlXzsiznX.0pphbOs4u5w.SE1IMRGIcnUMG47SurlV4uHn2', 'driver', NULL, '03937293237392', NULL, NULL, NULL, NULL, '2026-09-12 18:36:20'),
(19, 'passenger', 'passenger@gmail.com', '01722322323', '$2y$10$hPIDZmug2kN6DYzLl8pSI.19Udgj.gn77oH74XAYBbSn6vlc5EJKy', 'passenger', '8943434545', NULL, NULL, NULL, NULL, NULL, '2026-09-13 01:49:34'),
(20, 'manager', 'manager@gmail.com', '01677322323', '$2y$10$XUW9VBt20BAmfwJLENWeteQHp4Fba8Cvupvp3k33kZGVz4nkgbsEa', 'manager', NULL, NULL, 5, 'xyz', NULL, NULL, '2026-09-13 02:04:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `boarding_stop_id` (`boarding_stop_id`),
  ADD KEY `dropping_stop_id` (`dropping_stop_id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`),
  ADD UNIQUE KEY `bus_number` (`bus_number`);

--
-- Indexes for table `driver_availability`
--
ALTER TABLE `driver_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`);

--
-- Indexes for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `service_history`
--
ALTER TABLE `service_history`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `service_parts`
--
ALTER TABLE `service_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `part_id` (`part_id`);

--
-- Indexes for table `spare_parts`
--
ALTER TABLE `spare_parts`
  ADD PRIMARY KEY (`part_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `trip_logs`
--
ALTER TABLE `trip_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `driver_availability`
--
ALTER TABLE `driver_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `service_history`
--
ALTER TABLE `service_history`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_parts`
--
ALTER TABLE `service_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `spare_parts`
--
ALTER TABLE `spare_parts`
  MODIFY `part_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `trip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `trip_logs`
--
ALTER TABLE `trip_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`boarding_stop_id`) REFERENCES `route_stops` (`stop_id`),
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`dropping_stop_id`) REFERENCES `route_stops` (`stop_id`);

--
-- Constraints for table `driver_availability`
--
ALTER TABLE `driver_availability`
  ADD CONSTRAINT `driver_availability_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `incident_reports_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`),
  ADD CONSTRAINT `incident_reports_ibfk_3` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`);

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`),
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`);

--
-- Constraints for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD CONSTRAINT `route_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_history`
--
ALTER TABLE `service_history`
  ADD CONSTRAINT `service_history_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`),
  ADD CONSTRAINT `service_history_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `service_parts`
--
ALTER TABLE `service_parts`
  ADD CONSTRAINT `service_parts_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service_history` (`service_id`),
  ADD CONSTRAINT `service_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `spare_parts` (`part_id`);

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`),
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`),
  ADD CONSTRAINT `trips_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `trip_logs`
--
ALTER TABLE `trip_logs`
  ADD CONSTRAINT `trip_logs_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`),
  ADD CONSTRAINT `trip_logs_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
