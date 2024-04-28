-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2024 at 01:30 PM
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
-- Database: `petfaves_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `pets_tb`
--

CREATE TABLE `pets_tb` (
  `id` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `petName` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `petType` varchar(100) DEFAULT NULL,
  `petBreed` varchar(100) DEFAULT NULL,
  `petVacHistory` varchar(255) DEFAULT NULL,
  `petHistory` varchar(255) DEFAULT NULL,
  `petPhotoURL` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets_tb`
--

INSERT INTO `pets_tb` (`id`, `userID`, `petName`, `age`, `gender`, `petType`, `petBreed`, `petVacHistory`, `petHistory`, `petPhotoURL`, `created_at`, `updated_at`) VALUES
(2, NULL, 'Blackie', 4, 'male', 'Dog', 'Aspin', '2022-10-28', 'qwqweqweqweqeqweqweqweqweqweqweqweqweqwe', 'https://firebasestorage.googleapis.com/v0/b/pet-faves.appspot.com/o/Dog%2FAspin%2F2024-04-28_Blackie_blackie_pic.jpg?alt=media&token=15b38ddb-1fc5-4f81-bdc3-7dcf3d1905b7', '2024-04-28 10:48:12', '2024-04-28 10:48:12'),
(3, NULL, 'Blackie', 4, 'male', 'Dog', 'Aspin', '2022-10-28', 'I have a lot of wounds on my body and am a homeless puppy; my now-fur parent found me and took me to the shelter.', 'https://firebasestorage.googleapis.com/v0/b/pet-faves.appspot.com/o/Dog%2FAspin%2F2024-04-28_Blackie_blackie_pic.jpg?alt=media&token=34e6f3ed-08ea-4763-b035-49648fff4b4c', '2024-04-28 11:19:53', '2024-04-28 11:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `users_tb`
--

CREATE TABLE `users_tb` (
  `id` int(11) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pets_tb`
--
ALTER TABLE `pets_tb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `users_tb`
--
ALTER TABLE `users_tb`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pets_tb`
--
ALTER TABLE `pets_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users_tb`
--
ALTER TABLE `users_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pets_tb`
--
ALTER TABLE `pets_tb`
  ADD CONSTRAINT `pets_tb_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users_tb` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
