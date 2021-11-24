/*
Navicat MySQL Data Transfer

Source Server         : 本地-linux
Source Server Version : 50649
Source Host           : 192.168.220.128:3306
Source Database       : sword

Target Server Type    : MYSQL
Target Server Version : 50649
File Encoding         : 65001

Date: 2021-11-24 15:47:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) NOT NULL DEFAULT '',
  `img` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', '小甜甜', '');
INSERT INTO `admin` VALUES ('2', '小美女', '');

-- ----------------------------
-- Table structure for msg
-- ----------------------------
DROP TABLE IF EXISTS `msg`;
CREATE TABLE `msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '来源用户ID',
  `body` varchar(10000) NOT NULL DEFAULT '',
  `receive_id` int(11) NOT NULL DEFAULT '0' COMMENT '接收用户ID',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '消息类型（0:文本）',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `list` (`receive_id`,`source_id`,`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of msg
-- ----------------------------

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '账户',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `type` smallint(3) NOT NULL DEFAULT '1' COMMENT '1用户2客服',
  `create_time` int(13) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(13) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `delete_time` int(13) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', '胖哥', 'pangge', '0aaa02f4247932bc64233d4e026a4197', './assets/images/3.jpg', '1', '0', '0', null);
INSERT INTO `user` VALUES ('2', '胖妹', 'pangmei', '0aaa02f4247932bc64233d4e026a4197', './assets/images/3.jpg', '1', '0', '0', null);
