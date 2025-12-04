-- ERMS MySQL schema inferred from codebase
-- Database: srms

DROP DATABASE IF EXISTS `srms`;
CREATE DATABASE `srms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `srms`;

-- Table: department
CREATE TABLE `department` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dname` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_department_dname` (`dname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: course
CREATE TABLE `course` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cname` VARCHAR(150) NOT NULL,
  `did` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_did` (`did`),
  CONSTRAINT `fk_course_department` FOREIGN KEY (`did`) REFERENCES `department`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: batch
-- Note: student uses batch_no as the reference value in some pages
CREATE TABLE `batch` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_no` VARCHAR(50) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `cid` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_batch_course_no` (`cid`, `batch_no`),
  KEY `idx_batch_cid` (`cid`),
  CONSTRAINT `fk_batch_course` FOREIGN KEY (`cid`) REFERENCES `course`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: module
CREATE TABLE `module` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mcode` VARCHAR(50) NOT NULL,
  `mname` VARCHAR(200) NOT NULL,
  `cid` INT UNSIGNED NOT NULL,
  `semester` INT NULL,
  `credit` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_module_course_code` (`cid`, `mcode`),
  KEY `idx_module_cid` (`cid`),
  CONSTRAINT `fk_module_course` FOREIGN KEY (`cid`) REFERENCES `course`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: student
-- bid stores batch_no string (not batch id) per UI usage
CREATE TABLE `student` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reg_no` VARCHAR(100) NOT NULL,
  `fullname` VARCHAR(200) NOT NULL,
  `nic` VARCHAR(20) NOT NULL,
  `cid` INT UNSIGNED NOT NULL,
  `bid` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_reg_no` (`reg_no`),
  KEY `idx_student_cid` (`cid`),
  KEY `idx_student_bid` (`bid`),
  CONSTRAINT `fk_student_course` FOREIGN KEY (`cid`) REFERENCES `course`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
  -- No FK on bid because code stores batch_no string, not batch id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: exam
CREATE TABLE `exam` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mid` INT UNSIGNED NOT NULL,
  `bid` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exam_unique` (`mid`, `bid`, `date`, `time`),
  KEY `idx_exam_mid` (`mid`),
  KEY `idx_exam_bid` (`bid`),
  CONSTRAINT `fk_exam_module` FOREIGN KEY (`mid`) REFERENCES `module`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_batch` FOREIGN KEY (`bid`) REFERENCES `batch`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: results
CREATE TABLE `results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `examid` INT UNSIGNED NOT NULL,
  `studentid` INT UNSIGNED NOT NULL,
  `attempt` INT NOT NULL DEFAULT 1,
  `marks` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_results_unique` (`examid`, `studentid`, `attempt`),
  KEY `idx_results_examid` (`examid`),
  KEY `idx_results_studentid` (`studentid`),
  CONSTRAINT `fk_results_exam` FOREIGN KEY (`examid`) REFERENCES `exam`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_results_student` FOREIGN KEY (`studentid`) REFERENCES `student`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: admin
-- Mixed usage in code: columns appear as UserName, Password
CREATE TABLE `admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `UserName` VARCHAR(100) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`UserName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed an initial admin (username: admin, password: admin) - MD5 used in code
INSERT INTO `admin` (`UserName`, `Password`) VALUES ('admin', MD5('admin'));
