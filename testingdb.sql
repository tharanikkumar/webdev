-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 15, 2024 at 07:30 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `testingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `delete_status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `delete_status`, `created_at`) VALUES
(75, 'John Doe', 'johndoe@example.com', '$2y$10$tYjikVysT3UrPKehKyEGee4sQhr9u1qxAVEdnBPh3/BqZXfWseiJu', 0, '2024-11-12 13:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `evaluator`
--

CREATE TABLE `evaluator` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `alternate_email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(15) NOT NULL,
  `alternate_phone_number` varchar(15) DEFAULT NULL,
  `college_name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `total_experience` int(11) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `knowledge_domain` varchar(255) DEFAULT NULL,
  `theme_preference_1` varchar(255) DEFAULT NULL,
  `theme_preference_2` varchar(255) DEFAULT NULL,
  `theme_preference_3` varchar(255) DEFAULT NULL,
  `expertise_in_startup_value_chain` varchar(255) DEFAULT NULL,
  `role_interested` varchar(255) DEFAULT NULL,
  `evaluator_status` varchar(50) DEFAULT '0',
  `delete_status` varchar(50) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluator`
--

INSERT INTO `evaluator` (`id`, `first_name`, `last_name`, `gender`, `email`, `alternate_email`, `phone_number`, `alternate_phone_number`, `college_name`, `designation`, `total_experience`, `city`, `state`, `knowledge_domain`, `theme_preference_1`, `theme_preference_2`, `theme_preference_3`, `expertise_in_startup_value_chain`, `role_interested`, `evaluator_status`, `delete_status`, `created_at`, `password`) VALUES
(11, 'John', 'Doe', 'Male', 'tharanikkumar6@gmail.com', 'doe1@example.com', '1234567890', '0987654321', 'Example College', '0', 5, 'CityName', 'StateName', 'Software Development', '1', '2', '3', 'Fundraising', 'Mentor', '3', '0', '2024-11-14 17:08:31', '$2y$10$AWxtnFUB0MIovc5cnDWzJ.Ld8689kXhesDP3RikpAtoSUJAD3INC.'),
(12, 'John', 'Doe', 'Male', 'tharanikkumar63@gmail.com', 'doe1@example.com', '1234567890', '0987654321', 'Example College', '0', 5, 'CityName', 'StateName', 'Software Development', '1', '2', '3', 'Fundraising', 'Mentor', '1', '0', '2024-11-14 17:08:35', '$2y$10$KSscvpUg2FToj4r2Tc5zTe5giutXHTaL0pXi/A.eCf9XXIIs27HCO'),
(13, 'John', 'Doe', 'Male', 'tharanikkumar3@gmail.com', 'doe1@example.com', '1234567890', '0987654321', 'Example College', '0', 5, 'CityName', 'StateName', 'Software Development', '1', '2', '3', 'Fundraising', 'Mentor', '1', '0', '2024-11-14 17:14:42', '$2y$10$1sQuniS96uzU9zNa34VUqux2yjF301XxQsIMejwa9N97p6WryizAO'),
(14, 'John', 'Doe', 'Male', 'tharanikkumar30@gmail.com', 'doe1@example.com', '1234567890', '0987654321', 'Example College', '0', 5, 'CityName', 'StateName', 'Software Development', '1', '2', '3', 'Fundraising', 'Mentor', '1', '0', '2024-11-14 18:44:59', '$2y$10$py1/x7md81XToVWfoW.2ve/2MkCPyV.LVu89fNbyU0qdHGEMF/1ea');

-- --------------------------------------------------------

--
-- Table structure for table `ideas`
--

CREATE TABLE `ideas` (
  `id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `school` varchar(255) DEFAULT NULL,
  `idea_title` varchar(255) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `idea_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ideas`
--

INSERT INTO `ideas` (`id`, `student_name`, `school`, `idea_title`, `status_id`, `theme_id`, `type`, `idea_description`) VALUES
(13, 'John Doe', 'XYZ University', 'Smart Water Management System', 2, NULL, 'Technology', 'An innovative system for better water conservation.'),
(15, 'John Doe', 'XYZ University', 'Smart Water Management System', 1, NULL, 'Technology', 'An innovative system for better water conservation.'),
(16, 'John Doe', 'XYZ University', 'Smart Water Management System', 1, NULL, 'Technology', 'An innovative system for better water conservation.'),
(17, 'John Doe', 'XYZ University', 'Smart Water Management System', 1, NULL, 'Technology', 'An innovative system for better water conservation.'),
(18, 'John Doe', 'XYZ University', 'Smart Water Management System', 1, NULL, 'Technology', 'An innovative system for better water conservation.'),
(19, 'John Doe', 'XYZ University', 'Smart Water Management System', 3, NULL, 'Technology', 'An innovative system for better water conservation.'),
(20, 'John Doe', 'XYZ University', 'Smart Water Management System', 3, NULL, 'Technology', 'An innovative system for better water conservation.');

-- --------------------------------------------------------

--
-- Table structure for table `idea_evaluators`
--

CREATE TABLE `idea_evaluators` (
  `id` int(11) NOT NULL,
  `idea_id` int(11) DEFAULT NULL,
  `evaluator_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `evaluator_comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `status_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `status_name`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `theme`
--

CREATE TABLE `theme` (
  `id` int(11) NOT NULL,
  `theme_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `theme`
--

INSERT INTO `theme` (`id`, `theme_name`) VALUES
(1, 'Food Processing/Nutrition/Biotech'),
(2, 'Healthcare & Biomedical devices'),
(3, 'ICT, Cyber-physical systems, Blockchain, Cognitive computing, Cloud computing, AI & ML'),
(4, 'Infrastructure'),
(5, 'IoT based technologies (e.g. Security & Surveillance systems etc)'),
(6, 'Consumer Goods and Retail'),
(7, 'Defence & Security'),
(8, 'Education'),
(9, 'Fashion and Textiles'),
(10, 'Finance Life Sciences'),
(11, 'Agriculture & Rural Development'),
(12, 'Clean & Potable water'),
(13, 'Consumer Goods and Retail'),
(14, 'Defence & Security'),
(15, 'Education'),
(16, 'Software-Web App Development'),
(17, 'Sports & Fitness'),
(18, 'Sustainable Environment'),
(19, 'Travel & Tourism'),
(20, 'Waste Management/Waste to Wealth Creation'),
(21, 'Smart Cities'),
(22, 'Smart Education'),
(23, 'Smart Textiles'),
(24, 'Smart Vehicles/Electric vehicle/Electric vehicle motor and battery technology'),
(25, 'Software-Mobile App Development'),
(26, 'Manufacturing'),
(27, 'Mining, Metals, Materials'),
(28, 'Other Emerging Areas Innovation for Start-ups'),
(29, 'Renewable and Affordable Energy'),
(30, 'Robotics and Drones'),
(31, 'PoC Validation'),
(32, 'Policy Expert (Start-up and Innovations)'),
(33, 'Prototype Development'),
(34, 'Technology Transfer'),
(35, 'Venture Planning and Enterprise/Startup'),
(36, 'IP Generation & Protection'),
(37, 'PoC Validation'),
(38, 'Policy Expert (Start-up and Innovations)'),
(39, 'Prototype Development'),
(40, 'Technology Transfer'),
(41, 'Business Modeling/Plan Development'),
(42, 'Design Thinking'),
(43, 'Idea Generation & Validation'),
(44, 'Incubation/Innovation Management'),
(45, 'Investment and Market Analyst');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `evaluator`
--
ALTER TABLE `evaluator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `theme_id` (`theme_id`);

--
-- Indexes for table `idea_evaluators`
--
ALTER TABLE `idea_evaluators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idea_id` (`idea_id`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `evaluator`
--
ALTER TABLE `evaluator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `idea_evaluators`
--
ALTER TABLE `idea_evaluators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `theme`
--
ALTER TABLE `theme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `ideas_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ideas_ibfk_2` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `idea_evaluators`
--
ALTER TABLE `idea_evaluators`
  ADD CONSTRAINT `idea_evaluators_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`),
  ADD CONSTRAINT `idea_evaluators_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `evaluator` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
