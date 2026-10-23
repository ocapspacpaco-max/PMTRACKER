-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 07:30 AM
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
-- Database: `pmtracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `project_id`, `user_id`, `action`, `created_at`) VALUES
(1, 1, 1, 'created the project', '2026-10-09 00:36:07'),
(2, 1, 1, 'created sprint kai sottie', '2026-10-22 09:38:25'),
(3, 1, 1, 'set sprint #1 to active', '2026-10-22 09:38:29'),
(4, 1, 1, 'set sprint #1 to planned', '2026-10-22 09:38:32'),
(5, 1, 1, 'set sprint #1 to active', '2026-10-22 09:38:36'),
(6, 1, 1, 'set sprint #1 to planned', '2026-10-22 09:38:39'),
(7, 1, 1, 'set sprint #1 to active', '2026-10-22 09:38:41'),
(8, 1, 1, 'set sprint #1 to planned', '2026-10-22 09:38:44'),
(9, 1, 1, 'set sprint #1 to active', '2026-10-22 09:38:46'),
(10, 1, 1, 'set sprint #1 to completed', '2026-10-22 09:38:48'),
(11, 1, 1, 'set sprint #1 to active', '2026-10-22 09:39:03'),
(12, 1, 1, 'set sprint #1 to planned', '2026-10-22 09:39:09'),
(13, 1, 1, 'set sprint #1 to completed', '2026-10-22 09:39:15'),
(14, 1, 1, 'set sprint #1 to planned', '2026-10-22 09:39:30'),
(15, 1, 1, 'set sprint #1 to active', '2026-10-22 09:39:38'),
(16, 1, 1, 'set sprint #1 to completed', '2026-10-22 09:40:13'),
(17, 1, 1, 'created Task: kad', '2026-10-22 09:41:44'),
(18, 1, 1, 'updated Requirement #1: kad', '2026-10-22 09:41:50'),
(19, 1, 1, 'set sprint #1 to active', '2026-10-22 09:42:21'),
(20, 1, 1, 'moved item #1 to In Progress', '2026-10-22 09:42:24'),
(21, 1, 1, 'moved item #1 to Done', '2026-10-22 09:42:27'),
(22, 1, 1, 'moved item #1 to In Review', '2026-10-22 09:42:31'),
(23, 1, 1, 'moved item #1 to To Do', '2026-10-22 09:42:35'),
(24, 1, 1, 'set sprint #1 to completed', '2026-10-22 09:43:05'),
(25, 1, 1, 'moved item #1 to In Progress', '2026-10-23 02:13:31'),
(26, 1, 1, 'moved item #1 to In Review', '2026-10-23 02:13:34'),
(27, 1, 1, 'moved item #1 to To Do', '2026-10-23 02:13:37'),
(28, 1, 1, 'created sprint Sprint 1', '2026-10-23 02:20:24'),
(29, 1, 1, 'set sprint #2 to active', '2026-10-23 02:20:30'),
(30, 1, 1, 'set sprint #2 to completed', '2026-10-23 02:20:46'),
(31, 1, 1, 'set sprint #2 to planned', '2026-10-23 02:20:56'),
(32, 1, 1, 'updated Requirement #1: kad', '2026-10-23 02:26:56'),
(33, 1, 1, 'set sprint #2 to active', '2026-10-23 02:27:12'),
(34, 1, 1, 'set sprint #2 to completed', '2026-10-23 02:27:27'),
(35, 1, 1, 'moved item #1 to In Progress', '2026-10-23 02:36:48'),
(36, 1, 1, 'moved item #1 to To Do', '2026-10-23 02:36:50'),
(37, 1, 1, 'removed item #1 from its sprint', '2026-10-23 02:37:29'),
(38, 1, 1, 'moved item #1 to In Progress', '2026-10-23 02:37:52'),
(39, 1, 1, 'moved item #1 to To Do', '2026-10-23 02:37:53'),
(40, 1, 1, 'moved item #1 to In Progress', '2026-10-23 03:09:33'),
(41, 1, 1, 'moved item #1 to In Review', '2026-10-23 03:09:35'),
(42, 1, 1, 'moved item #1 to Done', '2026-10-23 03:09:37'),
(43, 1, 1, 'moved item #1 to In Review', '2026-10-23 03:09:38'),
(44, 1, 1, 'moved item #1 to In Progress', '2026-10-23 03:09:39'),
(45, 1, 1, 'moved item #1 to To Do', '2026-10-23 03:09:41'),
(46, 2, 2, 'created the project', '2026-10-23 04:28:17');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `sprint_id` int(11) DEFAULT NULL,
  `release_id` int(11) DEFAULT NULL,
  `type` enum('requirement','story','task','bug') NOT NULL DEFAULT 'task',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
  `assignee_id` int(11) DEFAULT NULL,
  `reporter_id` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `project_id`, `sprint_id`, `release_id`, `type`, `title`, `description`, `priority`, `status`, `assignee_id`, `reporter_id`, `points`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'requirement', 'kad', '', 'medium', 'todo', 1, 1, 0, '2026-10-22 09:41:44', '2026-10-23 03:09:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `owner_id`, `created_at`) VALUES
(1, 'LMS', 'Learning Material System', 1, '2026-10-09 00:36:07'),
(2, 'Pj1', 'pjqqbx', 2, '2026-10-23 04:28:17');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('manager','developer','tester') NOT NULL DEFAULT 'developer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`project_id`, `user_id`, `role`) VALUES
(1, 1, 'manager'),
(2, 2, 'manager');

-- --------------------------------------------------------

--
-- Table structure for table `releases`
--

CREATE TABLE `releases` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `version` varchar(30) NOT NULL,
  `release_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sprints`
--

CREATE TABLE `sprints` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `goal` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planned','active','completed') NOT NULL DEFAULT 'planned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sprints`
--

INSERT INTO `sprints` (`id`, `project_id`, `name`, `goal`, `start_date`, `end_date`, `status`) VALUES
(1, 1, 'kai sottie', 'dwad', '2026-10-22', '2026-10-23', 'completed'),
(2, 1, 'Sprint 1', 'Finished', '2026-10-23', '2026-10-23', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Zefanya', 'nikizef@gemail.com', '$2y$10$JNJu9UXKBSam5.lOIHyu2.WltYAZrEXWwIUZkN4m8Prm9rlzaOLS6', '2026-10-09 00:35:01'),
(2, 'jayr pacpaco', 'jayrpacpaco@gmail.com', '$2y$10$n9YuH8dhpahL/HOOdu46Nu.swrzTDWJ3.w.0vLsM9of9YFHDbLBIu', '2026-10-23 04:27:19'),
(3, 'jayr p', 'ocapspacpaco@gmail.com', '$2y$10$DwTpt93zyIEKbqS2xSURtOu1fxLCL5r7MBHhQ7QS31JypG1pSToQ6', '2026-10-23 04:34:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `sprint_id` (`sprint_id`),
  ADD KEY `release_id` (`release_id`),
  ADD KEY `assignee_id` (`assignee_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `releases`
--
ALTER TABLE `releases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `sprints`
--
ALTER TABLE `sprints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `releases`
--
ALTER TABLE `releases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sprints`
--
ALTER TABLE `sprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`sprint_id`) REFERENCES `sprints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_ibfk_4` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `releases`
--
ALTER TABLE `releases`
  ADD CONSTRAINT `releases_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sprints`
--
ALTER TABLE `sprints`
  ADD CONSTRAINT `sprints_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
