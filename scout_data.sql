-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 09:16 AM
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
-- Database: `scout_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$1lBRU18Wr5O9ux42Lo87EuD7uFtvNlSfiPgyNFD7ZO89RmoPzRT5.', '2025-07-14 13:56:38');

-- --------------------------------------------------------

--
-- Table structure for table `scouts`
--

CREATE TABLE `scouts` (
  `id` int(11) NOT NULL,
  `scout_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `payment_status` varchar(10) DEFAULT 'Unpaid',
  `season_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scouts`
--

INSERT INTO `scouts` (`id`, `scout_id`, `name`, `unit`, `district`, `father_name`, `mother_name`, `mobile_number`, `image_url`, `created_at`, `payment_status`, `season_id`) VALUES
(43, 'ZE6360', 'মোছা: জারিন তাসনিম', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মো: ফরিদুল ইসলাম খান', 'মোসা: আসমা পারভীন', '০১৬১১৯২৫৯৪৯', 'http://service.scouts.gov.bd/profile_img/849213.png', '2025-07-15 01:35:33', 'Unpaid', 26),
(44, 'ZE6361', 'মোঃ নাফিসা আনজুম', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মোঃ বশির আহমেদ বকুল', 'মোছাঃ নিলুফা ইয়াসমিন নিলা', '০১৮২১০২৪৮৮২', 'http://service.scouts.gov.bd/profile_img/849217.png', '2025-07-15 01:42:19', 'Unpaid', 26),
(45, 'ZE6359', 'মো: সাগর হোসেন শান্ত', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মো: শাহীন আলম', 'মোসা: নাদিরা বেগম', '০১৬১৮১৯৭৭৮৩', 'http://service.scouts.gov.bd/profile_img/849095.png', '2025-07-15 01:45:12', 'Unpaid', 23),
(46, 'ZE6350', 'মোঃ জাহিদ হাসান', 'উত্তর বাংলা কলেজ রোভার স্কাউট গ্রুপ', 'বাংলাদেশ স্কাউটস, লালমনিরহাট জেলা রোভার', 'মোঃ বাবুর আলী', 'মোছাঃ জোহরা বেগম', '০১৬১২০৩২৭৯৬', 'http://service.scouts.gov.bd/profile_img/847270.png', '2025-07-15 01:59:10', 'Unpaid', 23),
(47, 'CD9573', 'সানজিদা খাতুন', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'শামীম আহম্মেদ', 'সুরাইয়া বেগম', '০১৭৯০৯৬৭৯৫০', 'http://service.scouts.gov.bd/profile_img/729377.png', '2025-07-15 01:59:58', 'Unpaid', 26),
(49, 'AB8777', 'মোঃ সালাহ উদ্দিন শিশির', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মোঃ আবু বক্কার সিদ্দিক', 'মোছাঃ সেলিনা খাতুন', '০১৭৫৮৩৩০০৭৯', 'http://service.scouts.gov.bd/profile_img/6582.png', '2025-07-15 02:07:09', 'Unpaid', 23),
(50, 'ZE6361', 'মোঃ নাফিসা আনজুম', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মোঃ বশির আহমেদ বকুল', 'মোছাঃ নিলুফা ইয়াসমিন নিলা', '০১৮২১০২৪৮৮২', 'http://service.scouts.gov.bd/profile_img/849217.png', '2025-07-15 02:08:10', 'Unpaid', 23),
(51, 'AB8777', 'মোঃ সালাহ উদ্দিন শিশির', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মোঃ আবু বক্কার সিদ্দিক', 'মোছাঃ সেলিনা খাতুন', '০১৭৫৮৩৩০০৭৯', 'http://service.scouts.gov.bd/profile_img/6582.png', '2025-07-15 02:17:24', 'Unpaid', 26),
(52, 'CD8431', 'আসমা তুন নেসা', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'শহিদুল ইসলাম', 'রোকসানা পারভীন', '০১৩২০৫৭৪৭৫৭', 'http://service.scouts.gov.bd/profile_img/727789.png', '2025-07-15 02:22:27', 'Unpaid', 24),
(53, 'AB8743', 'সাধন কুমার সরকার', 'সেবা মুক্ত স্কাউট দল, সিরাজগঞ্জ', 'বাংলাদেশ স্কাউটস, সিরাজগঞ্জ জেলা', 'সুদেব চন্দ্র সরকার', 'সোনালী রানী', '০১৭৬০৮৪৮১১৬', 'http://service.scouts.gov.bd/profile_img/1551349968-20190224_133711.jpg', '2025-07-15 02:22:55', 'Unpaid', 24),
(54, 'CA1038', 'ইসরাত জাহান সুমাইয়া', 'উত্তর পূর্ব চর আফজাল সপ্রাবি কাবদল', 'বাংলাদেশ স্কাউটস, লক্ষীপুর জেলা', 'মো জুয়েল উদ্দিন', 'জরিনা বেগম', '০১৮৮৯৭১৬৮৮৬', 'http://service.scouts.gov.bd/profile_img/680225.png', '2025-07-15 03:17:48', 'Unpaid', 23),
(55, 'AB8777', 'মোঃ সালাহ উদ্দিন শিশির', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মোঃ আবু বক্কার সিদ্দিক', 'মোছাঃ সেলিনা খাতুন', '০১৭৫৮৩৩০০৭৯', 'http://service.scouts.gov.bd/profile_img/6582.png', '2025-07-15 03:25:53', 'Unpaid', 24),
(56, 'CD8424', 'মো মুস্তাফিজুর রহমান', 'নিউ গভঃ ডিগ্রী কলেজ রোভার স্কাউট গ্রুপ, রাজশাহী', 'বাংলাদেশ স্কাউটস, রাজশাহী জেলা রোভার', 'মো জাহিদুর রহমান', 'মোসা পারুল বেগম', '০১৭৫৯৮৮৮৫৫৬', 'http://service.scouts.gov.bd/profile_img/719360.png', '2025-07-15 03:39:20', 'Unpaid', 23);

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` int(11) NOT NULL,
  `season_name` varchar(255) NOT NULL,
  `registrations_open` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seasons`
--

INSERT INTO `seasons` (`id`, `season_name`, `registrations_open`, `created_at`) VALUES
(23, '১৫তম কোর্স ফর রোভার মেট', 1, '2025-07-14 15:51:13'),
(24, 'সিনিয়র রোভার মেট তথ্য সংগ্রহ', 1, '2025-07-14 15:51:20'),
(26, 'জুলাই গণঅভ্যুত্থান', 1, '2025-07-14 19:31:34');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_name` varchar(255) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_name` varchar(255) NOT NULL,
  `quota` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `scouts`
--
ALTER TABLE `scouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `created_at_2` (`created_at`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_name`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `scouts`
--
ALTER TABLE `scouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
