-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 06:50 PM
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
-- Database: `project_vet`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateAppointmentWithPatient` (IN `p_user_id` INT, IN `p_pet_name` VARCHAR(100), IN `p_species` VARCHAR(50), IN `p_breed` VARCHAR(100), IN `p_gender` ENUM('Male','Female'), IN `p_service_type` VARCHAR(100), IN `p_pet_age` VARCHAR(50), IN `p_notes` TEXT, IN `p_vet_id` INT, IN `p_appointment_date` DATE, IN `p_appointment_time` TIME, IN `p_reason` VARCHAR(255))   BEGIN
    DECLARE v_patient_id INT;
    
    -- Find existing patient or create new one
    SELECT patient_id INTO v_patient_id 
    FROM patients 
    WHERE user_id = p_user_id AND name = p_pet_name AND species = p_species
    LIMIT 1;
    
    IF v_patient_id IS NULL THEN
        -- Insert new patient (using pet_age directly as age)
        INSERT INTO patients (user_id, name, species, breed, gender, age)
        VALUES (p_user_id, p_pet_name, p_species, p_breed, p_gender, p_pet_age);
        
        SET v_patient_id = LAST_INSERT_ID();
    END IF;
    
    -- Create appointment
    INSERT INTO appointments (user_id, pet_name, species, breed, gender, service_type, 
                            pet_age, notes, patient_id, vet_id, appointment_date, 
                            appointment_time, reason, status)
    VALUES (p_user_id, p_pet_name, p_species, p_breed, p_gender, p_service_type,
            p_pet_age, p_notes, v_patient_id, p_vet_id, p_appointment_date,
            p_appointment_time, p_reason, 'Scheduled');
            
    SELECT LAST_INSERT_ID() as appointment_id, v_patient_id as patient_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `species` varchar(50) DEFAULT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `pet_age` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `vet_id` int(11) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `pet_name`, `species`, `breed`, `gender`, `service_type`, `pet_age`, `notes`, `patient_id`, `vet_id`, `appointment_date`, `appointment_time`, `reason`, `status`) VALUES
(9, 4, 'Calli', 'Cat', 'Persian', 'Female', 'Medical Checkup', '1 year', '', 4, 2, '2025-10-16', '10:00:00', '', 'Completed'),
(11, 4, 'Nox', 'Guinea Pig', 'California', 'Male', 'Annual Checkup', '6 month', '-', 4, 2, '2025-10-17', '09:00:00', '', 'Completed'),
(13, 7, 'Luna', 'Cat', 'Persian', 'Female', 'Surgery', '2 years', '-', 4, 2, '2025-10-18', '10:00:00', '', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `species` varchar(50) DEFAULT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `name`, `species`, `breed`, `gender`, `age`) VALUES
(4, 5, 'Anya', 'Dog', 'Chow Chow', 'Male', '2 months'),
(5, 4, 'Mori', 'Dog', 'Golden Retriever', 'Female', '5 months'),
(6, 4, 'Calli', 'Cat', 'Persian', 'Female', '12 months'),
(7, 4, 'Nox', 'Guinea Pig', 'California', 'Male', '6 months'),
(8, 7, 'Luna', 'Cat', 'Persian', 'Female', '2 months');

-- --------------------------------------------------------

--
-- Stand-in structure for view `patient_appointment_history`
-- (See below for the actual view)
--
CREATE TABLE `patient_appointment_history` (
`patient_id` int(11)
,`user_id` int(11)
,`pet_name` varchar(100)
,`species` varchar(50)
,`breed` varchar(50)
,`gender` enum('Male','Female')
,`age` varchar(50)
,`appointment_id` int(11)
,`appointment_date` date
,`appointment_time` time
,`service_type` varchar(100)
,`appointment_status` enum('Scheduled','Completed','Cancelled')
,`owner_name` varchar(100)
,`vet_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `vet_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `patient_id`, `vet_id`, `report_date`, `service_type`, `status`) VALUES
(1, 4, 2, '2025-10-16', 'Vaccination', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `treatment`
--

CREATE TABLE `treatment` (
  `record_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `treatment_date` date NOT NULL,
  `procedure` varchar(100) NOT NULL,
  `medications` text NOT NULL,
  `treatment_details` text NOT NULL,
  `followup_date` date DEFAULT NULL,
  `vet_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment`
--

INSERT INTO `treatment` (`record_id`, `patient_name`, `treatment_date`, `procedure`, `medications`, `treatment_details`, `followup_date`, `vet_id`, `created_at`) VALUES
(2, 'Mori', '2025-10-14', 'Vaccination ', 'none', 'vaccination for rabies', '2026-10-14', 2, '2025-10-16 03:52:25'),
(3, 'Nox', '2025-10-17', 'annual checkup', '-', '-', NULL, 2, '2025-10-18 07:21:50');

-- --------------------------------------------------------

--
-- Stand-in structure for view `unified_patients`
-- (See below for the actual view)
--
CREATE TABLE `unified_patients` (
`patient_id` int(11)
,`user_id` int(11)
,`name` varchar(100)
,`species` varchar(50)
,`breed` varchar(50)
,`gender` enum('Male','Female')
,`age` varchar(50)
,`source` varchar(18)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `upcoming_appointments_with_patients`
-- (See below for the actual view)
--
CREATE TABLE `upcoming_appointments_with_patients` (
`appointment_id` int(11)
,`appointment_date` date
,`appointment_time` time
,`service_type` varchar(100)
,`patient_id` int(11)
,`pet_name` varchar(100)
,`species` varchar(50)
,`breed` varchar(50)
,`age` varchar(50)
,`owner_name` varchar(100)
,`owner_phone` varchar(20)
,`vet_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Vet','Owner') DEFAULT 'Owner',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `full_name`, `role`, `phone`, `email`, `password`) VALUES
(1, 'admin_anya', 'Anya Melfisa', 'Admin', '0852123456', 'anya@vetcare.com', 'anya123'),
(2, 'vet_xia', 'Xia Ekavira', 'Vet', '085245361787', 'xia@vetcare.com', 'xia123'),
(3, 'vet_rosemi', 'Rosemi Lovelock', 'Vet', '08122334455', 'rosemi@vetcare.com', 'rose123'),
(4, 'owner_luca', 'Luca Kaneshiro', 'Owner', '08213456789', 'luca@example.com', 'luca123'),
(5, 'owner_nina', 'Nina Kosaka', 'Owner', '08223344556', 'nina@example.com', 'nina123'),
(7, 'cecil', 'Cecillia Liberia', 'Owner', '098123456', 'cecil@example.com', 'cecil123');

-- --------------------------------------------------------

--
-- Table structure for table `veterinarians`
--

CREATE TABLE `veterinarians` (
  `vet_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `veterinarians`
--

INSERT INTO `veterinarians` (`vet_id`, `user_id`, `specialization`, `experience_years`) VALUES
(1, 2, 'Specialty', 8),
(2, 3, 'General Veterinary Medicine', 5);

-- --------------------------------------------------------

--
-- Structure for view `patient_appointment_history`
--
DROP TABLE IF EXISTS `patient_appointment_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `patient_appointment_history`  AS SELECT `p`.`patient_id` AS `patient_id`, `p`.`user_id` AS `user_id`, `p`.`name` AS `pet_name`, `p`.`species` AS `species`, `p`.`breed` AS `breed`, `p`.`gender` AS `gender`, `p`.`age` AS `age`, `a`.`appointment_id` AS `appointment_id`, `a`.`appointment_date` AS `appointment_date`, `a`.`appointment_time` AS `appointment_time`, `a`.`service_type` AS `service_type`, `a`.`status` AS `appointment_status`, `u`.`full_name` AS `owner_name`, `vet`.`full_name` AS `vet_name` FROM ((((`patients` `p` left join `appointments` `a` on(`p`.`patient_id` = `a`.`patient_id`)) left join `users` `u` on(`p`.`user_id` = `u`.`user_id`)) left join `veterinarians` `v` on(`a`.`vet_id` = `v`.`vet_id`)) left join `users` `vet` on(`v`.`user_id` = `vet`.`user_id`)) ORDER BY `p`.`patient_id` ASC, `a`.`appointment_date` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `unified_patients`
--
DROP TABLE IF EXISTS `unified_patients`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `unified_patients`  AS SELECT `p`.`patient_id` AS `patient_id`, `p`.`user_id` AS `user_id`, `p`.`name` AS `name`, `p`.`species` AS `species`, `p`.`breed` AS `breed`, `p`.`gender` AS `gender`, `p`.`age` AS `age`, 'Registered Patient' AS `source` FROM `patients` AS `p` ;

-- --------------------------------------------------------

--
-- Structure for view `upcoming_appointments_with_patients`
--
DROP TABLE IF EXISTS `upcoming_appointments_with_patients`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `upcoming_appointments_with_patients`  AS SELECT `a`.`appointment_id` AS `appointment_id`, `a`.`appointment_date` AS `appointment_date`, `a`.`appointment_time` AS `appointment_time`, `a`.`service_type` AS `service_type`, `p`.`patient_id` AS `patient_id`, `p`.`name` AS `pet_name`, `p`.`species` AS `species`, `p`.`breed` AS `breed`, `p`.`age` AS `age`, `u`.`full_name` AS `owner_name`, `u`.`phone` AS `owner_phone`, `vet`.`full_name` AS `vet_name` FROM ((((`appointments` `a` join `patients` `p` on(`a`.`patient_id` = `p`.`patient_id`)) join `users` `u` on(`p`.`user_id` = `u`.`user_id`)) join `veterinarians` `v` on(`a`.`vet_id` = `v`.`vet_id`)) join `users` `vet` on(`v`.`user_id` = `vet`.`user_id`)) WHERE `a`.`appointment_date` >= curdate() AND `a`.`status` = 'Scheduled' ORDER BY `a`.`appointment_date` ASC, `a`.`appointment_time` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `treatment`
--
ALTER TABLE `treatment`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD PRIMARY KEY (`vet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `treatment`
--
ALTER TABLE `treatment`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `veterinarians`
--
ALTER TABLE `veterinarians`
  MODIFY `vet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`vet_id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`vet_id`) REFERENCES `veterinarians` (`vet_id`) ON DELETE CASCADE;

--
-- Constraints for table `treatment`
--
ALTER TABLE `treatment`
  ADD CONSTRAINT `treatment_ibfk_1` FOREIGN KEY (`vet_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `veterinarians`
--
ALTER TABLE `veterinarians`
  ADD CONSTRAINT `veterinarians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
