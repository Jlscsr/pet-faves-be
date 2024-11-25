-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2024 at 10:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petfaves_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoption_requests_tb`
--

CREATE TABLE `adoption_requests_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) DEFAULT NULL,
  `userOwnerID` varchar(36) DEFAULT NULL,
  `petID` varchar(36) DEFAULT NULL,
  `status` enum('pending','approved','on going process','cancelled','completed') NOT NULL DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments_tb`
--

CREATE TABLE `appointments_tb` (
  `id` varchar(36) NOT NULL,
  `requestID` varchar(36) NOT NULL,
  `userOwnerID` varchar(36) DEFAULT NULL,
  `userID` varchar(36) NOT NULL,
  `petID` varchar(36) NOT NULL,
  `appointmentDate` varchar(255) NOT NULL,
  `appointmentTime` time NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations_tb`
--

CREATE TABLE `donations_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) NOT NULL,
  `donationAmount` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_posts_tb`
--

CREATE TABLE `event_posts_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) DEFAULT NULL,
  `postDescription` varchar(255) DEFAULT NULL,
  `eventDate` date DEFAULT NULL,
  `eventTime` time DEFAULT NULL,
  `eventLocation` varchar(255) NOT NULL,
  `approvalStatus` enum('pending','approved','cancelled') DEFAULT NULL,
  `postType` varchar(50) DEFAULT '''event''',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_posts_tb`
--

CREATE TABLE `media_posts_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) NOT NULL,
  `postDescription` varchar(255) NOT NULL,
  `mediaURL` varchar(255) DEFAULT NULL,
  `mediaType` varchar(255) NOT NULL,
  `postType` varchar(255) NOT NULL,
  `approvalStatus` enum('pending','approved','cancelled') NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications_tb`
--

CREATE TABLE `notifications_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) DEFAULT NULL,
  `requestID` varchar(36) DEFAULT NULL,
  `postID` varchar(36) DEFAULT NULL,
  `typeOfRequest` varchar(50) NOT NULL,
  `notificationStatus` varchar(50) DEFAULT NULL,
  `requestStatus` enum('pending','approved','on going process','cancelled','completed') NOT NULL DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets_tb`
--

CREATE TABLE `pets_tb` (
  `id` varchar(36) NOT NULL,
  `userOwnerID` varchar(36) DEFAULT NULL,
  `petName` varchar(255) DEFAULT NULL,
  `petAge` varchar(50) DEFAULT NULL,
  `petAgeCategory` varchar(50) NOT NULL,
  `petGender` varchar(10) DEFAULT NULL,
  `petType` varchar(100) DEFAULT NULL,
  `petBreed` varchar(100) DEFAULT NULL,
  `petColor` enum('white','black','brown','mixed') NOT NULL,
  `petVacHistory` varchar(255) DEFAULT NULL,
  `petHistory` varchar(255) DEFAULT NULL,
  `petPhotoURL` varchar(255) NOT NULL,
  `adoptionStatus` tinyint(1) NOT NULL DEFAULT 3 COMMENT '0 - available, 1 - pending, 2 - adopted, 3 - not available',
  `approvalStatus` enum('pending','approved','cancelled') DEFAULT NULL,
  `postType` varchar(50) NOT NULL DEFAULT 'post-adoption',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_care_tb`
--

CREATE TABLE `pet_care_tb` (
  `id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `featuredImageURL` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `status` enum('published','draft') NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts_likes_tb`
--

CREATE TABLE `posts_likes_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) NOT NULL,
  `postID` varchar(36) NOT NULL,
  `postType` enum('post','media','event','post-adoption') NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts_tb`
--

CREATE TABLE `posts_tb` (
  `id` varchar(36) NOT NULL,
  `userID` varchar(36) NOT NULL,
  `postDescription` varchar(255) DEFAULT NULL,
  `approvalStatus` enum('pending','approved','cancelled') DEFAULT NULL,
  `postType` varchar(50) DEFAULT '''post''',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_tb`
--

CREATE TABLE `users_tb` (
  `id` varchar(36) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `job` varchar(255) DEFAULT NULL,
  `lifeStyle` varchar(255) DEFAULT NULL,
  `livingStatus` varchar(255) DEFAULT NULL,
  `petCareTimeCommitment` varchar(255) DEFAULT NULL,
  `budgetForPetCare` varchar(255) DEFAULT NULL,
  `validIDImageURL` varchar(255) DEFAULT NULL,
  `selfieImageURL` varchar(255) DEFAULT NULL,
  `role` varchar(8) NOT NULL DEFAULT 'customer',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_tb`
--

INSERT INTO `users_tb` (`id`, `firstName`, `lastName`, `email`, `phoneNumber`, `password`, `address`, `region`, `province`, `city`, `barangay`, `age`, `job`, `lifeStyle`, `livingStatus`, `petCareTimeCommitment`, `budgetForPetCare`, `validIDImageURL`, `selfieImageURL`, `role`, `createdAt`, `updatedAt`) VALUES
('0192e468-7b9b-7337-8c8b-8872b3f4a0a5', 'PetFaves', 'Admin', 'petfavesad@gmail.com', '', '$2y$15$zTU.A/TJkchFqOA.yUtU1OzO62IpmmBnzyz8IPXaVWJw.RtThfojG', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '', '', 'admin', '2024-10-31 21:08:27', '2024-11-15 09:42:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoption_requests_tb`
--
ALTER TABLE `adoption_requests_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`),
  ADD KEY `petID` (`petID`);

--
-- Indexes for table `appointments_tb`
--
ALTER TABLE `appointments_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_requestID` (`requestID`),
  ADD KEY `fk_user_id` (`userID`),
  ADD KEY `fk_pet_id` (`petID`);

--
-- Indexes for table `event_posts_tb`
--
ALTER TABLE `event_posts_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `media_posts_tb`
--
ALTER TABLE `media_posts_tb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications_tb`
--
ALTER TABLE `notifications_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`),
  ADD KEY `requestID` (`requestID`);

--
-- Indexes for table `pets_tb`
--
ALTER TABLE `pets_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userOwnerID`);

--
-- Indexes for table `pet_care_tb`
--
ALTER TABLE `pet_care_tb`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts_likes_tb`
--
ALTER TABLE `posts_likes_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userID` (`userID`);

--
-- Indexes for table `posts_tb`
--
ALTER TABLE `posts_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `users_tb`
--
ALTER TABLE `users_tb`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
