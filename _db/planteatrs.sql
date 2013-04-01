-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 01 2013 г., 11:05
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
-- Структура таблицы `auth_assignment`
--

CREATE TABLE IF NOT EXISTS `auth_assignment` (
  `itemname` varchar(64) NOT NULL,
  `userid` varchar(64) NOT NULL,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_item`
--

CREATE TABLE IF NOT EXISTS `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `auth_item_child`
--

CREATE TABLE IF NOT EXISTS `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `db_session`
--

CREATE TABLE IF NOT EXISTS `db_session` (
  `id` char(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `feedbacks`
--

CREATE TABLE IF NOT EXISTS `feedbacks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `text` text NOT NULL,
  `createtime` int(10) DEFAULT '0',
  `access_status` enum('removed','published','pending','unpublished') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table to store user feedbacks' AUTO_INCREMENT=9 ;

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
  `veg` enum('vegan','vegan_on_request','vegetarian','vegetarian_on_request') NOT NULL,
  `gluten_free` tinyint(1) NOT NULL DEFAULT '0',
  `rating` decimal(4,3) NOT NULL DEFAULT '0.000',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `modifiedtime` int(10) NOT NULL DEFAULT '0',
  `access_status` enum('removed','published','pending','unpublished','needs_for_action') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `restaurant_id` (`restaurant_id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table to store meals of restaurant' AUTO_INCREMENT=3904 ;

-- --------------------------------------------------------

--
-- Структура таблицы `password_reset_tokens`
--

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `expire` int(10) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Структура таблицы `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `meal_id` bigint(20) unsigned NOT NULL,
  `mime` varchar(20) NOT NULL,
  `size` int(8) NOT NULL,
  `name` varchar(255) NOT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `access_status` enum('removed','published','pending','unpublished') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `name` (`name`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table to store photos of meals' AUTO_INCREMENT=317 ;

-- --------------------------------------------------------

--
-- Структура таблицы `ratings`
--

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meal_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `photo_id` bigint(20) unsigned DEFAULT NULL,
  `createtime` int(10) NOT NULL DEFAULT '0',
  `rating` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comment` text,
  `veg` enum('vegan','vegan_on_request','vegetarian','vegetarian_on_request') NOT NULL,
  `gluten_free` tinyint(1) NOT NULL,
  `access_status` enum('removed','published','pending','unpublished','needs_for_action') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3972 ;

--
-- Триггеры `ratings`
--
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_on_inserting_new_rating`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_on_inserting_new_rating` AFTER INSERT ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT restaurant_id FROM meals WHERE id=NEW.meal_id);
SET @gluten_free_var:=(SELECT `gluten_free` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);
SET @veg_var_for_meal:=(SELECT `veg` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);
SET @veg:=(SELECT veg FROM (SELECT veg, count(*) AS magnitude FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published' GROUP BY veg  ORDER BY magnitude DESC LIMIT 1) AS t);
SET @vegetarian:=(SELECT STRCMP(@veg,'vegetarian'));
SET @vegetarian_on_request:=(SELECT STRCMP(@veg,'vegetarian_on_request'));


UPDATE `meals` SET 
`meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=NEW.meal_id AND access_status='published'), 
`meals`.`gluten_free`=@gluten_free_var,  
`meals`.`veg`=@veg_var_for_meal 
WHERE `id`=NEW.meal_id;

UPDATE `restaurants` SET 
`restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published')
WHERE `id`=@restaurant_id_var;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_after_rating_update`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_after_rating_update` AFTER UPDATE ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT restaurant_id FROM meals WHERE id=OLD.meal_id);

SET @gluten_free_var:=(SELECT `gluten_free` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);
SET @veg_var_for_meal:=(SELECT `veg` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);

SET @veg:=(SELECT veg FROM (SELECT veg, count(*) AS magnitude FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published' GROUP BY veg  ORDER BY magnitude DESC LIMIT 1) AS t);
SET @vegetarian:=(SELECT STRCMP(@veg,'vegetarian'));
SET @vegetarian_on_request:=(SELECT STRCMP(@veg,'vegetarian_on_request'));


UPDATE `meals` SET 
`meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=OLD.meal_id AND access_status='published'), 
`meals`.`gluten_free`=@gluten_free_var,  
`meals`.`veg`=@veg_var_for_meal 
WHERE `id`=OLD.meal_id;
UPDATE `restaurants` SET 
`restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published')
WHERE `id`=@restaurant_id_var;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_ratings_in_meals_and_restaurants_after_rating_delete`;
DELIMITER //
CREATE TRIGGER `update_ratings_in_meals_and_restaurants_after_rating_delete` AFTER DELETE ON `ratings`
 FOR EACH ROW BEGIN
SET @restaurant_id_var:=(SELECT restaurant_id FROM meals WHERE id=OLD.meal_id);

SET @gluten_free_var:=(SELECT `gluten_free` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);
SET @veg_var_for_meal:=(SELECT `veg` FROM `ratings` WHERE `access_status`='published' ORDER BY `createtime` DESC LIMIT 1);

SET @veg:=(SELECT veg FROM (SELECT veg, count(*) AS magnitude FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published' GROUP BY veg  ORDER BY magnitude DESC LIMIT 1) AS t);
SET @vegetarian:=(SELECT STRCMP(@veg,'vegetarian'));
SET @vegetarian_on_request:=(SELECT STRCMP(@veg,'vegetarian_on_request'));


UPDATE `meals` SET 
`meals`.`rating`=(SELECT AVG(rating) FROM ratings WHERE meal_id=OLD.meal_id AND access_status='published'), 
`meals`.`gluten_free`=@gluten_free_var,  
`meals`.`veg`=@veg_var_for_meal 
WHERE `id`=OLD.meal_id;
UPDATE `restaurants` SET 
`restaurants`.`rating`=(SELECT AVG(rating) FROM meals WHERE restaurant_id=@restaurant_id_var AND access_status='published') 
WHERE `id`=@restaurant_id_var;
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
  `report_code` enum('not_vegetarian','not_glute_free','not_on_the_menu','restaurant_closed','inappropriate_content') NOT NULL,
  `access_status` enum('removed','published','pending','unpublished') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `meal_id` (`meal_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Структура таблицы `restaurants`
--

CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_id` varchar(255) DEFAULT NULL,
  `reference` text NOT NULL,
  `location` point NOT NULL,
  `name` varchar(255) NOT NULL,
  `zip` varchar(50) DEFAULT '',
  `street_address` varchar(255) NOT NULL,
  `street_address_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `veg` enum('vegan','vegetarian') DEFAULT NULL,
  `rating` decimal(4,3) NOT NULL DEFAULT '0.000',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `modifiedtime` int(10) NOT NULL DEFAULT '0',
  `access_status` enum('removed','published','pending','unpublished') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `external_id` (`external_id`),
  KEY `street_address` (`street_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table to store restaurants' AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Дублирующая структура для представления `restaurants_with_lat_lng`
--
CREATE TABLE IF NOT EXISTS `restaurants_with_lat_lng` (
`id` bigint(20) unsigned
,`external_id` varchar(255)
,`reference` text
,`location` point
,`name` varchar(255)
,`street_address` varchar(255)
,`street_address_2` varchar(255)
,`city` varchar(100)
,`state` varchar(10)
,`country` varchar(100)
,`phone` varchar(30)
,`email` varchar(255)
,`website` varchar(255)
,`veg` enum('vegan','vegetarian')
,`rating` decimal(4,3)
,`createtime` int(10)
,`modifiedtime` int(10)
,`access_status` enum('removed','published','pending','unpublished')
,`latitude` double
,`longitude` double
);
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
  `status` enum('active','inactive','banned','removed') NOT NULL DEFAULT 'inactive',
  `role` varchar(45) NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Структура для представления `restaurants_with_lat_lng`
--
DROP TABLE IF EXISTS `restaurants_with_lat_lng`;

CREATE ALGORITHM=UNDEFINED DEFINER=`planteatrs`@`%` SQL SECURITY DEFINER VIEW `restaurants_with_lat_lng` AS select `restaurants`.`id` AS `id`,`restaurants`.`external_id` AS `external_id`,`restaurants`.`reference` AS `reference`,`restaurants`.`location` AS `location`,`restaurants`.`name` AS `name`,`restaurants`.`street_address` AS `street_address`,`restaurants`.`street_address_2` AS `street_address_2`,`restaurants`.`city` AS `city`,`restaurants`.`state` AS `state`,`restaurants`.`country` AS `country`,`restaurants`.`phone` AS `phone`,`restaurants`.`email` AS `email`,`restaurants`.`website` AS `website`,`restaurants`.`veg` AS `veg`,`restaurants`.`rating` AS `rating`,`restaurants`.`createtime` AS `createtime`,`restaurants`.`modifiedtime` AS `modifiedtime`,`restaurants`.`access_status` AS `access_status`,x(`restaurants`.`location`) AS `latitude`,y(`restaurants`.`location`) AS `longitude` from `restaurants`;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_3` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `meals_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `photos_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_6` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reports_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
