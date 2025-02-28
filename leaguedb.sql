-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2025 at 05:57 AM
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
(4, 'admin', 'admin@gmail.com', '$2y$10$TGVihFud1o3Xuvtj6eHltuBVR5DfiPC0E/ksffL7p4X/5NPxyjudy', '2024-12-09 16:19:36'),
(5, 'JMC', 'diwas@gmail.com', '$2y$10$/GmQnx2sqEBHqZSrUMM.ReNl2fE9hFPAcXfGSgs5RdCSVee8y5X7S', '2025-01-15 04:18:45'),
(6, 'carlo', 'shrestha@gmail.com', '$2y$10$hBCKX/jiAt9jzdv.oUQF0eL1BjyHryACYuGr.S7MMvnFHvSDfASKS', '2025-01-15 04:20:01'),
(7, 'diwas', 'hhh@gmail.com', '$2y$10$PP36NW8OWLaDNXmX2WFn0.5c9heP8fOUgY8AQFk.AQtXcpDfnKJYi', '2025-01-15 04:37:55');

-- --------------------------------------------------------

--
-- Table structure for table `club`
--

CREATE TABLE `club` (
  `club_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL,
  `league_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club`
--

INSERT INTO `club` (`club_id`, `c_name`, `league_id`, `location`, `created_by`) VALUES
(99, 'fc manang ', 46, 'ktm', 67),
(101, 'manag', 46, 'ktm', 67),
(108, 'Kathmandu rayzers', 50, 'Kathmandu', 72),
(109, 'Lalitpur pirates', 50, 'Lalitpur', 72),
(110, 'Dhangadhi fc', 50, 'Dhangadhi', 72),
(111, 'F.C. Chitwan', 50, 'Chitwan', 72),
(112, 'Pokhara Thunders', 50, 'Pokhara', 72),
(114, 'Butwal Lumbini FC', 50, 'Butwal', 72),
(115, 'Birgunj United FC', 50, 'Birgunj', 72),
(116, 'Sporting Illam', 50, 'Illam', 72),
(117, 'Jhaapa FC', 50, 'Jhapa', 72);

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
(22, 'Rajendra Tamang', 6, 56, 117, '982343243', 'org'),
(23, 'Binod Dangol', 6, 66, 116, '3432324', 'org'),
(24, 'Prabin Shrestha', 6, 66, 108, '0', 'org'),
(25, 'Suraj Bhusal', 3, 30, 112, '98766756', 'org');

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
(20, 67, 1, 1, '', '2080-01-02', '2080-02-02', 'y', 'yes', 'Kathmandu', 'y', 'approved', '2025-02-23 08:13:50', 'Leaguepro'),
(26, 72, 1, 4, 'yes', '0000-00-00', '2025-03-28', 'yes', 'yes', 'Kathmandu', 'Yes', 'approved', '2025-02-26 10:03:08', 'Nepal Super League');

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
(68, 46, 99, 101, '2045-02-20', '01:02:00', 7, 0, '67'),
(74, 50, 109, 108, '2025-03-09', '01:02:00', 1, 2, '72'),
(75, 50, 117, 110, '2025-03-02', '01:02:00', 1, 0, '72');

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
(40, 102, 46, 1, 1, 0, 0, 9, 0, 7, 3, 'Diwas Shrestha'),
(41, 99, 46, 1, 1, 0, 0, 7, 0, 7, 3, 'Diwas Shrestha'),
(42, 105, 46, 4, 3, 1, 0, 18, 0, 12, 9, 'Diwas Shrestha'),
(43, 107, 46, 1, 0, 1, 0, 0, 0, -9, 0, 'Diwas Shrestha'),
(44, 106, 46, 1, 1, 0, 0, 9, 0, 9, 3, 'Diwas Shrestha'),
(45, 101, 46, 1, 0, 1, 0, 0, 0, -7, 0, 'Diwas Shrestha'),
(46, 108, 50, 1, 1, 0, 0, 2, 0, 1, 3, 'org'),
(47, 109, 50, 1, 0, 1, 0, 1, 0, -1, 0, 'org'),
(48, 111, 50, 1, 0, 0, 1, 0, 0, 0, 1, 'org'),
(49, 112, 50, 1, 0, 0, 1, 0, 0, 0, 1, 'org'),
(50, 117, 50, 1, 1, 0, 0, 1, 0, 1, 3, 'org'),
(51, 110, 50, 1, 0, 1, 0, 0, 0, -1, 0, 'org'),
(52, 114, 50, 1, 1, 0, 0, 7, 0, 7, 3, 'org'),
(53, 116, 50, 1, 0, 1, 0, 0, 0, -7, 0, 'org');

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
  `status` varchar(20) DEFAULT 'pending',
  `duration` int(11) NOT NULL,
  `max_teams` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `league`
--

INSERT INTO `league` (`league_id`, `league_name`, `userId`, `start_date`, `end_date`, `status`, `duration`, `max_teams`, `location`) VALUES
(46, 'Leaguepro', 67, 2080, 2080, 'pending', 1, 1, 'Kathmandu'),
(50, 'Nepal Super League', 72, 2025, 2025, 'pending', 1, 4, 'Kathmandu');

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
(32, 'Bipin Kandel', 22, 'fw', 101, '98873284', 'org'),
(33, 'Kamal Bahadur Thapa', 22, 'MF', 117, '9878767667', 'org'),
(34, 'Joe Aidoo', 33, 'DF', 111, '987346743', 'org');

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
(67, 'Diwas Shrestha', 'diwas254@gmail.com', '$2y$10$/i6NlzYTQmqw.JkHkThx5eOdX4Ho3X.9ckvyQQxQyH9pUDXlgKsym', '2025-02-23 08:13:31', 'approved'),
(72, 'org', 'org1@gmail.com', '$2y$10$mThD0ZHzz7AKO8pkH8Erf.DpKZZjCzvScsW/5u3DwLNw2pVZr6xvm', '2025-02-26 10:02:12', 'approved');

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `club`
--
ALTER TABLE `club`
  MODIFY `club_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `coach`
--
ALTER TABLE `coach`
  MODIFY `coach_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `form_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `game`
--
ALTER TABLE `game`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `leaderboard_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `league`
--
ALTER TABLE `league`
  MODIFY `league_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `player`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

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
