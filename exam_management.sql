-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 24, 2026 at 05:18 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

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
  `batch_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `cid` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `version_id` (`version_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch`
--

INSERT INTO `batch` (`id`, `batch_no`, `start_date`, `end_date`, `cid`, `version_id`, `created_at`, `updated_at`) VALUES
(3, '01', '2026-01-01', '2026-12-31', 11, 8, '2026-02-22 15:17:57', '2026-02-22 15:17:57');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `time_slot` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` int NOT NULL,
  `module_id` int NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assessment_percentage` decimal(5,2) DEFAULT '0.00',
  `final_exam_percentage` decimal(5,2) DEFAULT '0.00',
  `resulted_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `idx_exam_date` (`exam_date`),
  KEY `idx_course` (`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_date`, `time_slot`, `course_id`, `module_id`, `location`, `created_at`, `updated_at`, `assessment_percentage`, `final_exam_percentage`, `resulted_status`) VALUES
(7, '2026-02-25', '01.00 - 4.00', 11, 64, 'Hall 01', '2026-02-22 15:34:04', '2026-02-22 15:34:04', 40.00, 60.00, 'pending'),
(8, '2026-02-26', '01.00 - 4.00', 11, 61, 'Hall 01', '2026-02-22 15:34:04', '2026-02-22 15:34:04', 30.00, 70.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `student_id` int NOT NULL,
  `eligibility` enum('eligible','not_eligible') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'eligible',
  `attempt` int DEFAULT '1',
  `assessment_marks` decimal(5,2) DEFAULT '0.00',
  `final_exam_marks` decimal(5,2) DEFAULT '0.00',
  `final_marks` decimal(5,2) DEFAULT '0.00',
  `total_percentage` decimal(5,2) DEFAULT '0.00',
  `status` enum('pass','fail','absent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'absent',
  `first_marking_marks` decimal(5,2) DEFAULT NULL,
  `second_marking_marks` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `student_offense` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_exam_student_attempt` (`exam_id`,`student_id`,`attempt`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_student` (`student_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `exam_id`, `student_id`, `eligibility`, `attempt`, `assessment_marks`, `final_exam_marks`, `final_marks`, `total_percentage`, `status`, `first_marking_marks`, `second_marking_marks`, `created_at`, `updated_at`, `student_offense`) VALUES
(33, 7, 24, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(32, 7, 23, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(31, 7, 22, 'eligible', 1, 90.00, 30.00, 120.00, 0.00, 'fail', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 16:15:33', NULL),
(30, 7, 21, 'eligible', 1, 30.00, 50.00, 80.00, 0.00, 'pass', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 16:15:16', NULL),
(29, 7, 20, 'not_eligible', 1, 0.00, 0.00, 0.00, 0.00, 'fail', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:37:19', ''),
(28, 7, 19, 'not_eligible', 1, 0.00, 0.00, 0.00, 0.00, 'fail', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:37:07', ''),
(27, 7, 18, 'not_eligible', 1, 0.00, 0.00, 0.00, 0.00, 'fail', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:36:53', ''),
(26, 7, 17, 'eligible', 1, 50.00, 60.00, 110.00, 0.00, 'pass', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 16:15:06', ''),
(34, 8, 17, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(35, 8, 18, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(36, 8, 19, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(37, 8, 20, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(38, 8, 21, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(39, 8, 22, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(40, 8, 23, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL),
(41, 8, 24, 'eligible', 1, 0.00, 0.00, 0.00, 0.00, 'absent', NULL, NULL, '2026-02-22 15:34:04', '2026-02-22 15:34:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE IF NOT EXISTS `module` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mname` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cid` int NOT NULL,
  `version_id` int NOT NULL,
  `semester` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit` decimal(3,1) DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_code_version` (`mcode`,`version_id`),
  KEY `cid` (`cid`),
  KEY `version_id` (`version_id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `mcode`, `mname`, `cid`, `version_id`, `semester`, `credit`, `created_at`, `updated_at`) VALUES
(64, 'CS201', 'Database Systems', 11, 8, 'S1-3', 5.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(63, 'EN101', 'English Communication', 11, 8, 'S1-2', 4.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(62, 'PH105', 'Physics Fundamentals', 11, 8, 'S1-1', 2.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(61, 'MA201', 'Linear Algebra', 11, 8, 'S1-3', 4.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(60, 'CS102', 'Data Structures and Algorithms', 11, 8, 'S1-2', 2.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(59, 'CS101', 'Introduction to Programming', 11, 8, 'S1-1', 3.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(65, 'CS202', 'Web Development', 11, 8, 'S1-1', 4.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24'),
(66, 'MA301', 'Calculus', 11, 8, 'S1-2', 2.0, '2026-02-22 15:17:24', '2026-02-22 15:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nic` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `reg_no`, `fullname`, `nic`, `cid`, `bid`, `version_id`, `created_at`, `updated_at`) VALUES
(17, 'STU001', 'John Doe', '123456789V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(18, 'STU002', 'Jane Smith', '987654321V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(19, 'STU003', 'Robert Johnson', '456789123V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(20, 'STU004', 'Emily Davis', '789123456V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(21, 'STU005', 'Michael Brown', '321654987V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(22, 'STU006', 'Sarah Wilson', '654987321V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(23, 'STU007', 'David Miller', '147258369V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35'),
(24, 'STU008', 'Lisa Anderson', '258369147V', 11, 3, 8, '2026-02-22 15:18:35', '2026-02-22 15:18:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_2` (`email`)
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
  `version_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_course` (`version_name`,`course_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `versions`
--

INSERT INTO `versions` (`id`, `course_id`, `version_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(8, 11, '2', '', 'active', '2026-01-18 07:47:49', '2026-01-18 07:47:49'),
(9, 9, '1', '', 'active', '2026-01-18 07:48:05', '2026-01-18 07:48:05'),
(10, 11, '1', '', 'active', '2026-01-18 07:48:25', '2026-01-18 07:48:25');

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
