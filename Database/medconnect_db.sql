-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 09:46 AM
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
-- Database: `medconnect_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `start_time`, `end_time`, `status`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 5, '2026-02-04', '13:14:00', '15:14:00', 'completed', NULL, '2026-02-04 13:14:14', '2026-02-04 13:44:06');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `rating` decimal(2,1) DEFAULT 0.0,
  `location` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `full_name`, `email`, `password`, `phone`, `specialization`, `qualification`, `experience_years`, `consultation_fee`, `profile_image`, `bio`, `status`, `rating`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Dr. Ahmed Khan', 'ahmed.khan@example.com', 'password123', '03001234567', 'Cardiology', 'MBBS, FCPS', 10, 3000.00, 'uploads/1770190442_Doctor.jpg', 'Experienced cardiologist with 10 years practice.', 'active', 4.5, 'Karachi', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(2, 'Dr. Sara Malik', 'sara.malik@example.com', 'password123', '03007654321', 'Dermatology', 'MBBS, FCPS', 8, 2500.00, 'uploads/1770190442_Doctor.jpg', 'Skin specialist with excellent patient care.', 'active', 4.2, 'Lahore', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(3, 'Dr. Imran Ali', 'imran.ali@example.com', 'password123', '03009876543', 'Orthopedics', 'MBBS, FCPS', 12, 3500.00, 'uploads/1770190442_Doctor.jpg', 'Expert in bone and joint surgery.', 'active', 4.7, 'Islamabad', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(4, 'Dr. Ayesha Siddiqui', 'ayesha.siddiqui@example.com', 'password123', '03006543210', 'Pediatrics', 'MBBS, FCPS', 7, 2000.00, 'uploads/1770190442_Doctor.jpg', 'Child health specialist with 7 years experience.', 'active', 4.3, 'Karachi', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(5, 'Dr. Bilal Qureshi', 'bilal.qureshi@example.com', 'password123', '03005432109', 'Neurology', 'MBBS, FCPS', 15, 4000.00, 'uploads/1770190442_Doctor.jpg', 'Neurologist specializing in brain and nerve disorders.', 'active', 4.8, 'Lahore', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(6, 'Dr. Nadia Khan', 'nadia.khan@example.com', 'password123', '03004321098', 'Gynecology', 'MBBS, FCPS', 9, 3000.00, 'uploads/1770190442_Doctor.jpg', 'Women health specialist with compassionate care.', 'active', 4.6, 'Islamabad', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(7, 'Dr. Faisal Ahmed', 'faisal.ahmed@example.com', 'password123', '03003210987', 'General Surgery', 'MBBS, FCPS', 11, 3200.00, 'uploads/1770190442_Doctor.jpg', 'Skilled general surgeon with 11 years experience.', 'active', 4.4, 'Karachi', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(8, 'Dr. Hina Riaz', 'hina.riaz@example.com', 'password123', '03002109876', 'Ophthalmology', 'MBBS, FCPS', 6, 2200.00, 'uploads/1770190442_Doctor.jpg', 'Eye specialist with modern surgical techniques.', 'active', 4.1, 'Lahore', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(9, 'Dr. Zubair Hassan', 'zubair.hassan@example.com', 'password123', '03001098765', 'ENT', 'MBBS, FCPS', 8, 2500.00, 'uploads/1770190442_Doctor.jpg', 'ENT specialist for ear, nose, and throat disorders.', 'active', 4.3, 'Islamabad', '2026-02-04 12:41:45', '2026-02-04 12:41:45'),
(10, 'Dr. Samina Iqbal', 'samina.iqbal@example.com', 'password123', '03009987654', 'Psychiatry', 'MBBS, FCPS', 10, 2800.00, 'uploads/1770190442_Doctor.jpg', 'Mental health specialist with 10 years of practice.', 'active', 4.5, 'Karachi', '2026-02-04 12:41:45', '2026-02-04 12:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--

CREATE TABLE `doctor_availability` (
  `availability_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_availability`
--

INSERT INTO `doctor_availability` (`availability_id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 'Mon', '09:00:00', '17:00:00'),
(2, 1, 'Tue', '09:00:00', '17:00:00'),
(3, 1, 'Wed', '09:00:00', '17:00:00'),
(4, 1, 'Thu', '09:00:00', '17:00:00'),
(5, 1, 'Fri', '09:00:00', '17:00:00'),
(6, 2, 'Mon', '09:00:00', '17:00:00'),
(7, 2, 'Tue', '09:00:00', '17:00:00'),
(8, 2, 'Wed', '09:00:00', '17:00:00'),
(9, 2, 'Thu', '09:00:00', '17:00:00'),
(10, 2, 'Fri', '09:00:00', '17:00:00'),
(11, 3, 'Mon', '09:00:00', '17:00:00'),
(12, 3, 'Tue', '09:00:00', '17:00:00'),
(13, 3, 'Wed', '09:00:00', '17:00:00'),
(14, 3, 'Thu', '09:00:00', '17:00:00'),
(15, 3, 'Fri', '09:00:00', '17:00:00'),
(16, 4, 'Mon', '09:00:00', '17:00:00'),
(17, 4, 'Tue', '09:00:00', '17:00:00'),
(18, 4, 'Wed', '09:00:00', '17:00:00'),
(19, 4, 'Thu', '09:00:00', '17:00:00'),
(20, 4, 'Fri', '09:00:00', '17:00:00'),
(21, 5, 'Mon', '09:00:00', '17:00:00'),
(22, 5, 'Tue', '09:00:00', '17:00:00'),
(23, 5, 'Wed', '09:00:00', '17:00:00'),
(24, 5, 'Thu', '09:00:00', '17:00:00'),
(25, 5, 'Fri', '09:00:00', '17:00:00'),
(26, 6, 'Mon', '09:00:00', '17:00:00'),
(27, 6, 'Tue', '09:00:00', '17:00:00'),
(28, 6, 'Wed', '09:00:00', '17:00:00'),
(29, 6, 'Thu', '09:00:00', '17:00:00'),
(30, 6, 'Fri', '09:00:00', '17:00:00'),
(31, 7, 'Mon', '09:00:00', '17:00:00'),
(32, 7, 'Tue', '09:00:00', '17:00:00'),
(33, 7, 'Wed', '09:00:00', '17:00:00'),
(34, 7, 'Thu', '09:00:00', '17:00:00'),
(35, 7, 'Fri', '09:00:00', '17:00:00'),
(36, 8, 'Mon', '09:00:00', '17:00:00'),
(37, 8, 'Tue', '09:00:00', '17:00:00'),
(38, 8, 'Wed', '09:00:00', '17:00:00'),
(39, 8, 'Thu', '09:00:00', '17:00:00'),
(40, 8, 'Fri', '09:00:00', '17:00:00'),
(41, 9, 'Mon', '09:00:00', '17:00:00'),
(42, 9, 'Tue', '09:00:00', '17:00:00'),
(43, 9, 'Wed', '09:00:00', '17:00:00'),
(44, 9, 'Thu', '09:00:00', '17:00:00'),
(45, 9, 'Fri', '09:00:00', '17:00:00'),
(46, 10, 'Mon', '09:00:00', '17:00:00'),
(47, 10, 'Tue', '09:00:00', '17:00:00'),
(48, 10, 'Wed', '09:00:00', '17:00:00'),
(49, 10, 'Thu', '09:00:00', '17:00:00'),
(50, 10, 'Fri', '09:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `mr_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(15) DEFAULT NULL,
  `registration_date` date DEFAULT curdate(),
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `mr_number`, `name`, `email`, `phone`, `password`, `blood_group`, `allergies`, `emergency_contact_name`, `date_of_birth`, `gender`, `address`, `guardian_name`, `guardian_phone`, `registration_date`, `status`, `created_at`) VALUES
(1, 'MR10', 'NASIR ABBAS', 'nasiryt.827@gmail.com', '03176526827', '123', 'O-', 'Yes , Scnet', 'NASIR ABBAS', '2026-02-04', 'male', 'Street jeff xxxxAp04', 'Haider Ali', '03176526827', '2026-02-04', 'active', '2026-02-04 07:46:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `unique_slot` (`doctor_id`,`appointment_date`,`start_time`,`end_time`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mr_number` (`mr_number`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD CONSTRAINT `doctor_availability_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
