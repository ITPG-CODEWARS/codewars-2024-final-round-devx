-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Време на генериране: 23 ное 2024 в 11:38
-- Версия на сървъра: 10.4.32-MariaDB
-- Версия на PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данни: `devx_train`
--

-- --------------------------------------------------------

--
-- Структура на таблица `ticket_reservations`
--

CREATE TABLE `ticket_reservations` (
  `reservation_id` int(11) NOT NULL,
  `train_number` varchar(50) NOT NULL,
  `seat_number` int(11) NOT NULL,
  `reserved_by` varchar(100) NOT NULL,
  `reservation_timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура на таблица `train_schedule`
--

CREATE TABLE `train_schedule` (
  `train_id` int(11) NOT NULL,
  `train_number` varchar(50) NOT NULL,
  `from` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `arrival_time` time NOT NULL,
  `start_time` time NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `train_schedule`
--

INSERT INTO `train_schedule` (`train_id`, `train_number`, `from`, `destination`, `arrival_time`, `start_time`, `ticket_price`) VALUES
(4, 'Q1337', 'Плевен', 'Варна', '21:30:00', '16:30:00', 13.00),
(5, 'Q8437', 'София', 'Бургас', '13:40:00', '10:00:00', 15.00),
(6, 'Q3812', 'Габрово', 'Пловдив', '17:30:00', '13:05:00', 20.00);

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `is_admin`) VALUES
(3, 'Георги Петков', 'bglineblack@gmail.com', '$2y$10$1QEgLArlfdtW5aOaKekm/Oq5k/QcJo1OLaIrWW1H6Bv.bpK/tlJXO', '2024-11-23 07:06:33', 1);

--
-- Indexes for dumped tables
--

--
-- Индекси за таблица `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `train_number` (`train_number`);

--
-- Индекси за таблица `train_schedule`
--
ALTER TABLE `train_schedule`
  ADD PRIMARY KEY (`train_id`),
  ADD UNIQUE KEY `train_number` (`train_number`);

--
-- Индекси за таблица `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `train_schedule`
--
ALTER TABLE `train_schedule`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения за дъмпнати таблици
--

--
-- Ограничения за таблица `ticket_reservations`
--
ALTER TABLE `ticket_reservations`
  ADD CONSTRAINT `ticket_reservations_ibfk_1` FOREIGN KEY (`train_number`) REFERENCES `train_schedule` (`train_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
