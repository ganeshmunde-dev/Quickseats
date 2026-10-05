-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2026 at 09:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `support`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- --------------------------------------------------------

--
-- Table structure for table `bookbus_travel`
--

CREATE TABLE `bookbus_travel` (
  `id` int(11) NOT NULL,
  `passenger name` varchar(30) NOT NULL,
  `customer moblieno` varchar(10) NOT NULL,
  `route id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookmy_bus`
--

CREATE TABLE `bookmy_bus` (
  `id` int(11) NOT NULL,
  `passengername` varchar(30) NOT NULL,
  `mobileno` varchar(10) NOT NULL,
  `route id` int(11) NOT NULL,
  `routefrom` varchar(100) NOT NULL,
  `routeto` varchar(100) NOT NULL,
  `bus type` varchar(50) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `seat booked` int(50) NOT NULL,
  `total price` decimal(50,0) NOT NULL,
  `traveldate` date NOT NULL,
  `trevel time` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `booked at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `customer_mobile` varchar(255) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `route_id` int(11) NOT NULL,
  `route_from` varchar(100) NOT NULL,
  `route_to` varchar(100) NOT NULL,
  `travel_date` date NOT NULL,
  `travel_time` varchar(10) NOT NULL,
  `bus_type` varchar(50) NOT NULL,
  `seats_booked` int(11) NOT NULL,
  `price_per_seat` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `utr` varchar(50) DEFAULT NULL,
  `booked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `customer_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- --------------------------------------------------------

--
-- Table structure for table `bus_routes`
--

CREATE TABLE `bus_routes` (
  `id` int(11) NOT NULL,
  `route_from` varchar(100) NOT NULL,
  `route_to` varchar(100) NOT NULL,
  `travel_date` date NOT NULL,
  `travel_time` varchar(10) NOT NULL,
  `bus_type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `seats` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_routes`
--

INSERT INTO `bus_routes` (`id`, `route_from`, `route_to`, `travel_date`, `travel_time`, `bus_type`, `price`, `seats`) VALUES
(1, 'City A', 'City B', '2026-06-01', '09:00', 'AC', 450.00, 28),
(2, 'City C', 'City D', '2026-06-02', '13:30', 'Sleeper', 550.00, 24),
(3, 'pune', 'dubai', '2026-05-30', '21:23', 'AC', 20000.00, 41),
(4, 'dharashiv', 'pune', '2026-05-22', '22:24', 'Sleeper', 500.00, 26),
(5, 'mumbai', 'solapur', '2026-06-04', '20:20', 'Seater', 798.00, 21),
(6, 'pune', 'jaipur', '2026-06-02', '17:20', 'AC', 2000.00, 25),
(7, 'bembli', 'jaipur', '2026-06-06', '05:05', 'AC', 400.00, 22),
(8, 'solapur', 'pune', '2026-07-06', '04:44', 'AC', 452.00, 6);

-- --------------------------------------------------------

--
-- Table structure for table `customer_register`
--

CREATE TABLE `customer_register` (
  `id` int(11) NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `full_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(255) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_register`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookbus_travel`
--
ALTER TABLE `bookbus_travel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookmy_bus`
--
ALTER TABLE `bookmy_bus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`,`passengername`,`mobileno`,`route id`);

--
-- Indexes for table `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_register`
--
ALTER TABLE `customer_register`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookbus_travel`
--
ALTER TABLE `bookbus_travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookmy_bus`
--
ALTER TABLE `bookmy_bus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `bus_routes`
--
ALTER TABLE `bus_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_register`
--
ALTER TABLE `customer_register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


