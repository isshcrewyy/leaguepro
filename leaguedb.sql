-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2024 at 06:25 AM
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
-- Database: `leaguedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(3, 'Diwas Shrestha', 'sthadiwas106@gmail.com', '$2y$10$mGk4crnuz9jSAPL00w8xr.mrFde86LUQYVG0bFxZXpElB9TZpZiiO', '2024-12-08 13:34:27'),
(4, 'admin', 'admin@gmail.com', '$2y$10$TGVihFud1o3Xuvtj6eHltuBVR5DfiPC0E/ksffL7p4X/5NPxyjudy', '2024-12-09 16:19:36');

-- --------------------------------------------------------

--
-- Table structure for table `club`
--

CREATE TABLE `club` (
  `club_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL,
  `league_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club`
--

INSERT INTO `club` (`club_id`, `c_name`, `league_id`, `location`, `created_by`) VALUES
(49, 'BARCA', 43, 'spain', 'Diwas Shrestha'),
(51, 'Real', 43, 'madrid', 'Diwas Shrestha'),
(52, 'inter', 43, 'milano', 'Diwas Shrestha'),
(53, 'city', 43, 'city', 'Diwas Shrestha'),
(56, 'Kathmandu rayzers', 42, 'Kathmandu', 'org'),
(57, 'Lalitpur pirates', 42, 'Lalitpur', 'org'),
(58, 'dhangadhi fc', 42, 'dhangadhi', 'org');

-- --------------------------------------------------------

--
-- Table structure for table `coach`
--

CREATE TABLE `coach` (
  `coach_id` int(11) NOT NULL,
  `co_name` varchar(100) NOT NULL,
  `experience` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coach`
--

INSERT INTO `coach` (`coach_id`, `co_name`, `experience`, `age`, `club_id`, `phone_number`, `created_by`) VALUES
(17, 'carlo', 5, 70, 56, '3457483', 'org');

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

CREATE TABLE `form` (
  `form_id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `max_teams` int(11) NOT NULL,
  `one_league` enum('yes','no') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `experience` text DEFAULT NULL,
  `active` enum('yes','no') NOT NULL,
  `location` varchar(255) NOT NULL,
  `rules` text DEFAULT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `league_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form`
--

INSERT INTO `form` (`form_id`, `userId`, `duration`, `max_teams`, `one_league`, `start_date`, `end_date`, `experience`, `active`, `location`, `rules`, `status`, `created_at`, `league_name`) VALUES
(16, 61, 1, 9, '', '2024-12-01', '2025-01-01', '5 years', 'yes', 'Kathmandu', 'lots of rules', 'approved', '2024-12-26 16:48:24', 'Nepal Super League'),
(17, 62, 1, 1, '', '2024-12-02', '2025-01-02', '5 leagues', 'yes', 'kuleshwar', 'yes', 'approved', '2024-12-27 07:07:02', 'Janamaitri BCA CUP'),
(18, 63, 1, 1, '', '2024-01-02', '2024-02-02', 'w', 'yes', 'Kathmandu', 'w', 'approved', '2024-12-27 08:40:06', 'Laliga');

-- --------------------------------------------------------

--
-- Table structure for table `game`
--

CREATE TABLE `game` (
  `match_id` int(11) NOT NULL,
  `league_id` int(11) DEFAULT NULL,
  `home_club_id` int(11) DEFAULT NULL,
  `away_club_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `score_home` int(11) DEFAULT NULL,
  `score_away` int(11) DEFAULT NULL,
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game`
--

INSERT INTO `game` (`match_id`, `league_id`, `home_club_id`, `away_club_id`, `date`, `time`, `score_home`, `score_away`, `created_by`) VALUES
(32, 42, 56, 57, '2024-12-02', '02:02:00', 6, 0, 'org'),
(33, 43, 49, 51, '2024-12-04', '01:02:00', 9, 0, 'Diwas Shrestha');

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `leaderboard_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `league_id` int(11) NOT NULL,
  `matches_played` int(11) DEFAULT 0,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `draws` int(11) DEFAULT 0,
  `goals_scored` int(11) DEFAULT 0,
  `goals_against` int(11) DEFAULT 0,
  `goal_difference` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaderboard`
--

INSERT INTO `leaderboard` (`leaderboard_id`, `club_id`, `league_id`, `matches_played`, `wins`, `losses`, `draws`, `goals_scored`, `goals_against`, `goal_difference`, `points`, `created_by`) VALUES
(21, 56, 42, 1, 1, 0, 0, 6, 0, 6, 3, 'org'),
(22, 57, 42, 1, 0, 1, 0, 0, 0, -6, 0, 'org'),
(23, 49, 43, 1, 1, 0, 0, 9, 0, 9, 3, 'Diwas Shrestha'),
(24, 51, 43, 1, 0, 1, 0, 0, 0, -9, 0, 'Diwas Shrestha');

-- --------------------------------------------------------

--
-- Table structure for table `league`
--

CREATE TABLE `league` (
  `league_id` int(11) NOT NULL,
  `league_name` varchar(100) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `start_date` int(11) DEFAULT NULL,
  `end_date` int(11) DEFAULT NULL,
  `season` int(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `duration` int(11) NOT NULL,
  `max_teams` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `league`
--

INSERT INTO `league` (`league_id`, `league_name`, `userId`, `start_date`, `end_date`, `season`, `status`, `duration`, `max_teams`, `location`) VALUES
(42, 'Nepal Super League 2', 61, 2024, 2025, 4, 'pending', 1, 9, NULL),
(43, 'Janamaitri BCA CUP', 62, 2024, 2025, NULL, 'pending', 1, 1, 'kuleshwar'),
(44, 'Laliga', 63, 2024, 2024, NULL, 'pending', 1, 1, 'Kathmandu');

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE `player` (
  `player_id` int(11) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `player`
--

INSERT INTO `player` (`player_id`, `p_name`, `age`, `position`, `club_id`, `phone_number`, `created_by`) VALUES
(18, 'neymar', 34, 'lw', 49, '878677777777777', 'Diwas Shrestha'),
(19, 'messi', 37, 'rw', 51, '8', 'Diwas Shrestha'),
(20, 'ronaldo', 40, 'st', 52, '766766', 'Diwas Shrestha'),
(21, 'pedri', 22, 'amf', 53, '78668', 'Diwas Shrestha'),
(22, 'jude', 20, 'cmf', 49, '8678', 'Diwas Shrestha');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','denied') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userId`, `name`, `email`, `password`, `created_at`, `status`) VALUES
(61, 'org', 'org@gmail.com', '$2y$10$b3l7sT50gAxn64TGViZDBeJB0j0hIB4vDjTPcz1ddR5g/3ksJ5bnG', '2024-12-26 16:47:42', 'approved'),
(62, 'Diwas Shrestha', 'diwas254@gmail.com', '$2y$10$9wMbzLaLbSKYeOR10gUbdeWkE6ewR3tzvaTv1HBBZR0KyLM8F0EaW', '2024-12-27 07:06:17', 'approved'),
(63, 'deepak', 'deepak@gmail.com', '$2y$10$vmtjCd.0FXnFVZ26ANhpbuzhv6xHvGdAP4.ikf78FhGC7oR4YLYqO', '2024-12-27 08:39:27', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `club`
--
ALTER TABLE `club`
  ADD PRIMARY KEY (`club_id`),
  ADD KEY `LeagueId` (`league_id`);

--
-- Indexes for table `coach`
--
ALTER TABLE `coach`
  ADD PRIMARY KEY (`coach_id`),
  ADD KEY `coach_ibfk_1` (`club_id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`form_id`),
  ADD KEY `form_ibfk_1` (`userId`);

--
-- Indexes for table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`match_id`),
  ADD KEY `leagueId` (`league_id`),
  ADD KEY `homeClubId` (`home_club_id`),
  ADD KEY `awayClubId` (`away_club_id`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`leaderboard_id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `league_id` (`league_id`);

--
-- Indexes for table `league`
--
ALTER TABLE `league`
  ADD PRIMARY KEY (`league_id`),
  ADD UNIQUE KEY `unique_league_name` (`league_name`),
  ADD KEY `fk_user_id` (`userId`);

--
-- Indexes for table `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `clubId` (`club_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `club`
--
ALTER TABLE `club`
  MODIFY `club_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `coach`
--
ALTER TABLE `coach`
  MODIFY `coach_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `game`
--
ALTER TABLE `game`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `leaderboard_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `league`
--
ALTER TABLE `league`
  MODIFY `league_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `player`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `club`
--
ALTER TABLE `club`
  ADD CONSTRAINT `club_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`league_id`) ON DELETE CASCADE;

--
-- Constraints for table `coach`
--
ALTER TABLE `coach`
  ADD CONSTRAINT `coach_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`club_id`) ON DELETE CASCADE;

--
-- Constraints for table `form`
--
ALTER TABLE `form`
  ADD CONSTRAINT `form_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `game`
--
ALTER TABLE `game`
  ADD CONSTRAINT `game_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`league_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_ibfk_2` FOREIGN KEY (`home_club_id`) REFERENCES `club` (`club_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_ibfk_3` FOREIGN KEY (`away_club_id`) REFERENCES `club` (`club_id`) ON DELETE CASCADE;

--
-- Constraints for table `league`
--
ALTER TABLE `league`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`userId`) REFERENCES `user` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `player`
--
ALTER TABLE `player`
  ADD CONSTRAINT `player_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`club_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
