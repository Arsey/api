-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Янв 29 2013 г., 09:45
-- Версия сервера: 5.1.66
-- Версия PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `planteatrs`
--

-- --------------------------------------------------------

--
-- Структура таблицы `AuthAssignment`
--

CREATE TABLE IF NOT EXISTS `AuthAssignment` (
  `itemname` varchar(64) NOT NULL,
  `userid` varchar(64) NOT NULL,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `AuthAssignment`
--


-- --------------------------------------------------------

--
-- Структура таблицы `AuthItem`
--

CREATE TABLE IF NOT EXISTS `AuthItem` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `AuthItem`
--


-- --------------------------------------------------------

--
-- Структура таблицы `AuthItemChild`
--

CREATE TABLE IF NOT EXISTS `AuthItemChild` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `AuthItemChild`
--


-- --------------------------------------------------------

--
-- Структура таблицы `feedbacks`
--

CREATE TABLE IF NOT EXISTS `feedbacks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `text` text NOT NULL,
  `createtime` int(10) DEFAULT '0',
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to store user feedbacks' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `feedbacks`
--


-- --------------------------------------------------------

--
-- Структура таблицы `meals`
--

CREATE TABLE IF NOT EXISTS `meals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `vegan` tinyint(1) NOT NULL DEFAULT '0',
  `gluten_free` tinyint(1) NOT NULL DEFAULT '0',
  `rating` decimal(4,3) NOT NULL DEFAULT '0.000',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `modifiedtime` int(10) NOT NULL DEFAULT '0',
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to store meals of restaurant' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `meals`
--


-- --------------------------------------------------------

--
-- Структура таблицы `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meal_id` bigint(20) unsigned NOT NULL,
  `mime` varchar(20) NOT NULL,
  `size` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to store photos of meals' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `photos`
--


-- --------------------------------------------------------

--
-- Структура таблицы `ratings`
--

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meal_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `photo_id` bigint(20) unsigned NOT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `rating` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` text,
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `user_id` (`user_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `ratings`
--


--
-- Триггеры `ratings`
--
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_on_inserting_new_rating`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_on_inserting_new_rating` AFTER INSERT ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT restaurant_id FROM meals WHERE id=NEW.meal_id);
UPDATE `meals` SET `meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=NEW.meal_id) WHERE `id`=NEW.meal_id;
UPDATE `restaurants` SET `restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var) WHERE `id`=@restaurant_id_var;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_after_rating_update`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_after_rating_update` AFTER UPDATE ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT `restaurant_id` FROM `meals` WHERE `id`=OLD.`meal_id`);
SET @old_id:=OLD.meal_id;
UPDATE `meals` SET `meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=@old_id) WHERE `id`=@old_id;
UPDATE `restaurants` SET `restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var) WHERE `id`=(SELECT restaurant_id FROM meals WHERE id=@old_id);
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_after_rating_delete`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_after_rating_delete` AFTER DELETE ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT `restaurant_id` FROM `meals` WHERE `id`=OLD.`meal_id`);
SET @old_id:=OLD.meal_id;
UPDATE `meals` SET `meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=@old_id) WHERE `id`=@old_id;
UPDATE `restaurants` SET `restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var) WHERE `id`=(SELECT restaurant_id FROM meals WHERE id=@old_id);
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meal_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `report_code` tinyint(1) unsigned NOT NULL,
  `text` text,
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `reports`
--


-- --------------------------------------------------------

--
-- Структура таблицы `restaurants`
--

CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_id` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `latitude` decimal(18,12) NOT NULL,
  `longitude` decimal(18,12) NOT NULL,
  `name` varchar(255) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `street_address_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `vegan` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `rating` decimal(4,3) NOT NULL DEFAULT '0.000',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `modifiedtime` int(10) NOT NULL DEFAULT '0',
  `access_status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `user_id` (`user_id`),
  KEY `external_id` (`external_id`),
  KEY `lat_lng` (`latitude`,`longitude`),
  KEY `street_address` (`street_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to store restaurants' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `restaurants`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(128) NOT NULL,
  `salt` varchar(128) NOT NULL,
  `activation_key` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `username` varchar(20) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT '',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `lastvisit` int(10) NOT NULL DEFAULT '0',
  `lastaction` int(10) NOT NULL DEFAULT '0',
  `lastpasswordchange` int(10) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  `role` varchar(45) NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `users`
--


--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`);

--
-- Ограничения внешнего ключа таблицы `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`);

--
-- Ограничения внешнего ключа таблицы `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_6` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`),
  ADD CONSTRAINT `ratings_ibfk_4` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`),
  ADD CONSTRAINT `ratings_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`);

--
-- Ограничения внешнего ключа таблицы `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
