/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50616
 Source Host           : localhost
 Source Database       : askbasket

 Target Server Type    : MySQL
 Target Server Version : 50616
 File Encoding         : utf-8

 Date: 06/26/2014 15:05:12 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `ask_answer`
-- ----------------------------
DROP TABLE IF EXISTS `ask_answer`;
CREATE TABLE `ask_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(255) DEFAULT NULL,
  `response_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `textData` varchar(255) DEFAULT NULL,
  `numberData` int(11) DEFAULT NULL,
  `lngData` decimal(10,5) DEFAULT NULL,
  `latData` decimal(10,5) DEFAULT NULL,
  `imageData` text,
  PRIMARY KEY (`id`),
  KEY `answer_question_id` (`question_id`),
  KEY `answer_response_id` (`response_id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ask_basket`
-- ----------------------------
DROP TABLE IF EXISTS `ask_basket`;
CREATE TABLE `ask_basket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `basket_project_id` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ask_project`
-- ----------------------------
DROP TABLE IF EXISTS `ask_project`;
CREATE TABLE `ask_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ask_question`
-- ----------------------------
DROP TABLE IF EXISTS `ask_question`;
CREATE TABLE `ask_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `basket_id` int(11) NOT NULL,
  `questionType` varchar(255) NOT NULL,
  `options` text,
  PRIMARY KEY (`id`),
  KEY `question_basket_id` (`basket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ask_response`
-- ----------------------------
DROP TABLE IF EXISTS `ask_response`;
CREATE TABLE `ask_response` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basket_id` int(11) NOT NULL,
  `submitTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `response_basket_id` (`basket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ask_user`
-- ----------------------------
DROP TABLE IF EXISTS `ask_user`;
CREATE TABLE `ask_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_masteruser` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

