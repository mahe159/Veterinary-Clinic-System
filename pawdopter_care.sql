-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 05:43 PM
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
-- Database: `pawdopter_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'admin', '', 'admin', '2025-04-15 19:02:41');

-- --------------------------------------------------------

--
-- Table structure for table `adoption_requests`
--

CREATE TABLE `adoption_requests` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_requests`
--

INSERT INTO `adoption_requests` (`id`, `pet_id`, `user_id`, `status`, `created_at`) VALUES
(1, 1, 1, 'approved', '2025-04-16 16:47:52'),
(2, 1, 1, 'approved', '2025-04-16 16:47:58'),
(3, 1, 1, 'declined', '2025-04-16 16:48:13'),
(4, 1, 1, 'declined', '2025-04-16 17:32:35'),
(5, 1, 1, 'declined', '2025-04-16 17:36:17'),
(6, 1, 1, 'approved', '2025-04-16 17:37:10'),
(7, 2, 1, 'approved', '2025-04-16 18:40:18'),
(8, 2, 1, 'approved', '2025-04-16 18:40:26'),
(9, 2, 3, 'approved', '2025-04-16 18:49:18'),
(10, 1, 3, 'approved', '2025-04-16 18:51:01'),
(11, 2, 3, 'approved', '2025-04-16 18:59:55'),
(12, 3, 3, 'approved', '2025-04-16 19:22:08'),
(13, 4, 1, 'approved', '2025-04-16 19:31:45'),
(14, 5, 1, 'approved', '2025-04-16 19:34:04'),
(15, 6, 1, 'approved', '2025-04-16 19:35:24'),
(16, 7, 3, 'approved', '2025-04-16 19:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `fees` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `doctor_name`, `available_date`, `start_time`, `end_time`, `fees`) VALUES
(1, 'Mahee', '2025-04-16', '10:10:00', '22:00:00', 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `foster_care_requests`
--

CREATE TABLE `foster_care_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `species` enum('cat','dog') NOT NULL,
  `days` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `volunteer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foster_care_requests`
--

INSERT INTO `foster_care_requests` (`id`, `user_id`, `pet_name`, `species`, `days`, `status`, `volunteer_id`, `created_at`) VALUES
(1, 1, 'cat2', 'cat', 5, 'accepted', NULL, '2025-04-15 19:29:03'),
(2, 7, 'Shiroo', 'dog', 2, 'accepted', NULL, '2025-04-15 19:46:48'),
(3, 3, 'Shiroo', 'cat', 5, 'accepted', NULL, '2025-04-15 19:59:05'),
(4, 7, 'Shiroo', 'dog', 51, 'accepted', NULL, '2025-04-15 20:09:38'),
(5, 6, 'Shiroo', 'cat', 51, 'accepted', NULL, '2025-04-15 20:17:12'),
(6, 1, 'sd', 'cat', 54, 'accepted', NULL, '2025-04-15 21:21:51'),
(7, 7, 'DOGGY', 'dog', 1, 'accepted', NULL, '2025-04-15 21:35:37'),
(8, 7, 'Catty', 'cat', 2, 'accepted', 1, '2025-04-15 21:35:45'),
(9, 7, 'Shiroo', 'dog', 5, 'accepted', 1, '2025-04-16 14:43:22'),
(11, 7, 'Shirool', 'cat', 5, 'accepted', 1, '2025-04-16 16:27:11'),
(12, 9, 'isra', 'cat', 2, 'accepted', 1, '2025-04-16 16:49:52'),
(13, 7, 'lala', 'cat', 1, 'accepted', 1, '2025-04-16 17:36:40'),
(14, 7, 'iiiiii', 'cat', 2, 'accepted', 1, '2025-04-16 17:59:26'),
(15, 1, 'iiiiii22', 'cat', 2, 'accepted', 1, '2025-04-16 18:03:10'),
(16, 3, 'miii', 'cat', 1, 'accepted', NULL, '2025-04-16 18:51:17'),
(17, 3, 'hum', 'cat', 2, 'accepted', NULL, '2025-04-16 19:01:15'),
(18, 3, 'hum2', 'cat', 2, 'accepted', NULL, '2025-04-16 19:07:29');

-- --------------------------------------------------------

--
-- Table structure for table `petcare_appointments`
--

CREATE TABLE `petcare_appointments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `breed` varchar(100) NOT NULL,
  `species` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petcare_appointments`
--

INSERT INTO `petcare_appointments` (`id`, `doctor_id`, `user_name`, `pet_name`, `breed`, `species`, `created_at`, `status`) VALUES
(1, 1, 'mahe', 'cat', 'high', 'cat', '2025-04-15 19:05:57', 'rejected'),
(2, 1, 'mahe', 'cat', 'high', 'cat', '2025-04-15 19:39:40', 'accepted'),
(3, 1, 'nazmul', 'cat', 'high', 'cat', '2025-04-15 19:40:47', 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `name`, `breed`, `age`, `description`, `available`, `photo_path`, `created_at`) VALUES
(1, 'Shiiiii', 'highbreed', 1, 'onek mota', 0, NULL, '2025-04-16 16:47:16'),
(2, 'wow', '.hm', 2, 'asd', 0, NULL, '2025-04-16 18:39:55'),
(3, 'wow2', '.hm', 2, 'ooo', 0, NULL, '2025-04-16 19:21:48'),
(4, 'TestDog', 'Mixed', 2, 'Test pet for adoption', 0, NULL, '2025-04-16 19:31:45'),
(5, 'TestPet2', 'Mixed', 3, 'Test pet for adoption', 0, NULL, '2025-04-16 19:34:04'),
(6, 'TestPet2', 'Mixed', 3, 'Test pet for adoption', 0, NULL, '2025-04-16 19:35:24'),
(7, 'SHImano', 'egu', 2, 'ninja hattori', 0, NULL, '2025-04-16 19:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('volunteer','foster care','pet care') NOT NULL DEFAULT 'volunteer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `user_type`, `created_at`) VALUES
(1, 'israfill2', 'israfil@gmail.com', '$2y$10$Ln4mah0OfFP71My1JFEr4ub7S.uSNr2jUqdvdfcYIR5vbvNXFT4dm', 'volunteer', '2025-04-15 19:05:00'),
(2, 'mahe', 'mahedulhassan147@gmail.com', '$2y$10$44uiwHaUtkVD8ALX3UyUpOYSdffJqc2dUIuDTE5milAgIFkKpXQWu', 'pet care', '2025-04-15 19:05:45'),
(3, 'israfill', 'israfil1@gmail.com', '$2y$10$Cob5BHSOxq.Af50OZ2de7.BeiyXPnwlc.aHqUSB8MgD9AwCcc6DTy', 'volunteer', '2025-04-15 19:29:40'),
(4, 'dipto', 'dip1@gmail.com', '$2y$10$yV4tc1wRX2wCvIJn0osPq.waECmwf8rKGZMWubQ4t1vo1kXFNRluS', 'pet care', '2025-04-15 19:30:25'),
(6, 'Nazmul', 'na@gmail.com', '$2y$10$sIo7/fSsklfCOTWsuO8DweQNOgipUqC51.HByfqdaV9nHQCYK0rkW', 'pet care', '2025-04-15 19:32:39'),
(7, 'Tanjib', 'Tanjib@gmail.com', '$2y$10$qPTyNCGwDz62ADBxz6nV8OD6SPPdWdqc3/DzsQ/Kg6TqEmH.C.id.', 'foster care', '2025-04-15 19:46:29'),
(8, 'vl', 'v@gmail.com', '$2y$10$CmefIlugiOjUCo0f3E/DRefj/wtqzLKtLe31Pi5DaurzFI1sRnXKS', 'volunteer', '2025-04-15 21:17:30'),
(9, 'mahe69', 'ayonalam69@gmail.com', '$2y$10$CvW2KnmbWthTd2oUz3FvmuFHAuAtl7DJcy6zMzHwJjdGKwMrl.Ufq', 'foster care', '2025-04-16 16:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_payments`
--

CREATE TABLE `volunteer_payments` (
  `id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `foster_request_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteer_payments`
--

INSERT INTO `volunteer_payments` (`id`, `volunteer_id`, `foster_request_id`, `amount`, `payment_date`, `status`) VALUES
(1, 3, 2, 100.00, '2025-04-16 01:47:18', 'paid'),
(2, 3, 2, 100.00, '2025-04-16 01:47:21', 'paid'),
(3, 3, 5, 100.00, '2025-04-16 02:20:50', 'paid'),
(4, 3, 3, 100.00, '2025-04-16 02:20:55', 'paid'),
(5, 3, 4, 100.00, '2025-04-16 02:42:35', 'paid'),
(6, 1, 6, 100.00, '2025-04-16 03:22:03', 'paid'),
(7, 1, 7, 100.00, '2025-04-16 03:41:51', 'paid'),
(8, 3, 16, 100.00, '2025-04-17 00:51:32', 'paid'),
(9, 3, 17, 100.00, '2025-04-17 01:01:31', 'paid'),
(10, 3, 18, 100.00, '2025-04-17 01:07:43', 'paid');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foster_care_requests`
--
ALTER TABLE `foster_care_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `petcare_appointments`
--
ALTER TABLE `petcare_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `volunteer_payments`
--
ALTER TABLE `volunteer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `volunteer_id` (`volunteer_id`),
  ADD KEY `foster_request_id` (`foster_request_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `foster_care_requests`
--
ALTER TABLE `foster_care_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `petcare_appointments`
--
ALTER TABLE `petcare_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `volunteer_payments`
--
ALTER TABLE `volunteer_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD CONSTRAINT `adoption_requests_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoption_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `foster_care_requests`
--
ALTER TABLE `foster_care_requests`
  ADD CONSTRAINT `foster_care_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `petcare_appointments`
--
ALTER TABLE `petcare_appointments`
  ADD CONSTRAINT `petcare_appointments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `volunteer_payments`
--
ALTER TABLE `volunteer_payments`
  ADD CONSTRAINT `volunteer_payments_ibfk_1` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `volunteer_payments_ibfk_2` FOREIGN KEY (`foster_request_id`) REFERENCES `foster_care_requests` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
