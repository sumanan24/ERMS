-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 02, 2026 at 09:51 AM
-- Server version: 8.0.31
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `exam_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `batch`
--

DROP TABLE IF EXISTS `batch`;
CREATE TABLE IF NOT EXISTS `batch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `cid` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `version_id` (`version_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch`
--

INSERT INTO `batch` (`id`, `batch_no`, `start_date`, `end_date`, `cid`, `version_id`, `created_at`, `updated_at`) VALUES
(2, '2024/2025', '2024-01-04', '2025-12-29', 11, 7, '2025-12-29 08:45:08', '2025-12-29 08:45:08');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cname` (`cname`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `cname`, `created_at`, `updated_at`) VALUES
(6, 'Diploma in Automobile Technology', '2025-12-17 09:40:27', '2025-12-17 09:42:49'),
(9, 'Diploma in Production Technology', '2025-12-17 09:41:22', '2025-12-17 09:43:04'),
(11, 'Diploma in Information and Communication Technology', '2025-12-17 09:42:41', '2025-12-17 09:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
CREATE TABLE IF NOT EXISTS `exams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_date` date NOT NULL,
  `time_slot` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` int NOT NULL,
  `module_id` int NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assessment_percentage` decimal(5,2) DEFAULT '0.00',
  `final_exam_percentage` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `idx_exam_date` (`exam_date`),
  KEY `idx_course` (`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_date`, `time_slot`, `course_id`, `module_id`, `location`, `created_at`, `updated_at`, `assessment_percentage`, `final_exam_percentage`) VALUES
(6, '2025-12-22', '01.00 04.00', 11, 56, 'hall 01', '2025-12-29 09:07:50', '2025-12-29 09:17:06', '40.00', '60.00');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `student_id` int NOT NULL,
  `eligibility` enum('eligible','not_eligible') COLLATE utf8mb4_unicode_ci DEFAULT 'eligible',
  `attempt` int DEFAULT '1',
  `assessment_marks` decimal(5,2) DEFAULT '0.00',
  `final_exam_marks` decimal(5,2) DEFAULT '0.00',
  `final_marks` decimal(5,2) DEFAULT '0.00',
  `total_percentage` decimal(5,2) DEFAULT '0.00',
  `status` enum('pass','fail','absent') COLLATE utf8mb4_unicode_ci DEFAULT 'absent',
  `first_marking_marks` decimal(5,2) DEFAULT NULL,
  `second_marking_marks` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `student_offense` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_exam_student_attempt` (`exam_id`,`student_id`,`attempt`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_student` (`student_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `exam_id`, `student_id`, `eligibility`, `attempt`, `assessment_marks`, `final_exam_marks`, `final_marks`, `total_percentage`, `status`, `first_marking_marks`, `second_marking_marks`, `created_at`, `updated_at`, `student_offense`) VALUES
(19, 6, 10, 'not_eligible', 1, '0.00', '0.00', '0.00', '0.00', 'fail', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:43:34', ''),
(20, 6, 11, 'eligible', 1, '0.00', '0.00', '0.00', '0.00', 'fail', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:43:50', 'cheating'),
(21, 6, 12, 'eligible', 1, '60.00', '50.00', '110.00', '0.00', 'pass', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:59:27', NULL),
(22, 6, 13, 'eligible', 1, '100.00', '40.00', '140.00', '0.00', 'pass', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 10:00:03', NULL),
(24, 6, 15, 'eligible', 1, '0.00', '0.00', '0.00', '0.00', 'absent', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:07:50', NULL),
(25, 6, 16, 'eligible', 1, '0.00', '0.00', '0.00', '0.00', 'absent', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:07:50', NULL),
(18, 6, 9, 'eligible', 1, '50.00', '42.00', '92.00', '0.00', 'pass', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:46:09', NULL),
(23, 6, 14, 'eligible', 1, '0.00', '0.00', '0.00', '0.00', 'absent', NULL, NULL, '2025-12-29 09:07:50', '2025-12-29 09:07:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE IF NOT EXISTS `module` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mcode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mname` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cid` int NOT NULL,
  `version_id` int NOT NULL,
  `semester` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit` decimal(3,1) DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_code_version` (`mcode`,`version_id`),
  KEY `cid` (`cid`),
  KEY `version_id` (`version_id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `mcode`, `mname`, `cid`, `version_id`, `semester`, `credit`, `created_at`, `updated_at`) VALUES
(56, 'CS201', 'Database Systems', 11, 7, 'S1-1', '3.0', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(55, 'EN101', 'English Communication', 11, 7, 'S1-1', '2.0', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(54, 'PH105', 'Physics Fundamentals', 11, 7, 'S1-1', '2.5', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(53, 'MA201', 'Linear Algebra', 11, 7, 'S1-2', '3.0', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(52, 'CS102', 'Data Structures and Algorithms', 11, 7, 'S1-2', '3.5', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(51, 'CS101', 'Introduction to Programming', 11, 7, 'S1', '3.0', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(57, 'CS202', 'Web Development', 11, 7, 'S1-2', '3.5', '2025-12-29 08:42:22', '2025-12-29 08:42:22'),
(58, 'MA301', 'Calculus', 11, 7, 'S1-2', '3.0', '2025-12-29 08:42:22', '2025-12-29 08:42:22');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nic` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cid` int NOT NULL,
  `bid` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_regno` (`reg_no`),
  KEY `cid` (`cid`),
  KEY `bid` (`bid`),
  KEY `version_id` (`version_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `reg_no`, `fullname`, `nic`, `cid`, `bid`, `version_id`, `created_at`, `updated_at`) VALUES
(9, 'STU001', 'John Doe', '123456789V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(10, 'STU002', 'Jane Smith', '987654321V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(11, 'STU003', 'Robert Johnson', '456789123V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(12, 'STU004', 'Emily Davis', '789123456V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(13, 'STU005', 'Michael Brown', '321654987V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(14, 'STU006', 'Sarah Wilson', '654987321V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(15, 'STU007', 'David Miller', '147258369V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58'),
(16, 'STU008', 'Lisa Anderson', '258369147V', 11, 2, 7, '2025-12-29 08:48:58', '2025-12-29 08:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher','student') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@exam.com', '$2y$10$FreCoJFPx6pZlbTwlkfNsuZ3cTZXN0Ld2mzyNp1GqmR0rDcUVh/rK', 'System Administrator', 'admin', '2025-12-14 06:17:35', '2025-12-14 06:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `versions`;
CREATE TABLE IF NOT EXISTS `versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `version_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_course` (`version_name`,`course_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `versions`
--

INSERT INTO `versions` (`id`, `course_id`, `version_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(5, 11, '1', '', 'active', '2025-12-17 09:43:22', '2025-12-17 09:43:22'),
(7, 11, '2', '', 'active', '2025-12-17 09:44:15', '2025-12-17 09:44:15');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `versions`
--
ALTER TABLE `versions`
  ADD CONSTRAINT `versions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
