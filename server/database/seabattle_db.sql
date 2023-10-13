-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 10.10.1.170
-- Время создания: Окт 12 2023 г., 11:43
-- Версия сервера: 5.7.39-42
-- Версия PHP: 8.1.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `study`
--

-- --------------------------------------------------------

--
-- Структура таблицы `PrivateCoordinates`
--

CREATE TABLE `PrivateCoordinates` (
  `id` int(11) NOT NULL,
  `ship_id` int(11) DEFAULT NULL,
  `coordinate` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `is_hit` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Games`
--

CREATE TABLE `Games` (
  `id` int(11) NOT NULL,
  `first_player` int(11) DEFAULT NULL,
  `second_player` int(11) DEFAULT NULL,
  `winner` int(11) DEFAULT NULL,
  `first_player_roll` int(3) NOT NULL,
  `second_player_roll` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Queues`
--

CREATE TABLE `Queues` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `PrivateShips`
--

CREATE TABLE `PrivateShips` (
  `id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `ship_type` tinyint(4) NOT NULL,
  `direction` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `is_destroyed` tinyint(1) NOT NULL,
  `start_coordinate` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Shots`
--

CREATE TABLE `Shots` (
  `id` int(11) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `target` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `request` tinyint(4) NOT NULL,
  `response` tinyint(4) DEFAULT NULL,
  `turn_number` tinyint(4) NOT NULL,
  `shot_time` datetime NOT NULL,
  `start_coord` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ship_length` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `login` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `is_online` tinyint(1) NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `PrivateCoordinates`
--
ALTER TABLE `PrivateCoordinates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ship_id` (`ship_id`);

--
-- Индексы таблицы `Games`
--
ALTER TABLE `Games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `first_player` (`first_player`),
  ADD KEY `second_player` (`second_player`),
  ADD KEY `winner` (`winner`);

--
-- Индексы таблицы `Queues`
--
ALTER TABLE `Queues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);


--
-- Индексы таблицы `PrivateShips`
--
ALTER TABLE `PrivateShips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Shots`
--
ALTER TABLE `Shots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Индексы таблицы `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Games`
--
ALTER TABLE `Games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- AUTO_INCREMENT для таблицы `Queues`
--
ALTER TABLE `Queues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1591;

--
-- AUTO_INCREMENT для таблицы `PrivateShips`
--
ALTER TABLE `PrivateShips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8983;

--
-- AUTO_INCREMENT для таблицы `Shots`
--
ALTER TABLE `Shots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6454;

--
-- AUTO_INCREMENT для таблицы `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=403;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `PrivateCoordinates`
--
ALTER TABLE `PrivateCoordinates`
  ADD CONSTRAINT `PrivateCoordinates_ibfk_1` FOREIGN KEY (`ship_id`) REFERENCES `PrivateShips` (`id`);

--
-- Ограничения внешнего ключа таблицы `Games`
--
ALTER TABLE `Games`
  ADD CONSTRAINT `Games_ibfk_1` FOREIGN KEY (`first_player`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `Games_ibfk_2` FOREIGN KEY (`second_player`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `Games_ibfk_3` FOREIGN KEY (`winner`) REFERENCES `Users` (`id`);

--
-- Ограничения внешнего ключа таблицы `Queues`
--
ALTER TABLE `Queues`
  ADD CONSTRAINT `Queues_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Ограничения внешнего ключа таблицы `PrivateShips`
--
ALTER TABLE `PrivateShips`
  ADD CONSTRAINT `PrivateShips_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`),
  ADD CONSTRAINT `PrivateShips_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Ограничения внешнего ключа таблицы `Shots`
--
ALTER TABLE `Shots`
  ADD CONSTRAINT `Shots_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `Shots_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `Games` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
