/*
Navicat MySQL Data Transfer

Target Server Type    : MYSQL
Target Server Version : 50537
File Encoding         : 65001

Date: 2018-02-23 14:56:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ak_bargain_configs
-- ----------------------------
DROP TABLE IF EXISTS `ak_bargain_configs`;
CREATE TABLE `ak_bargain_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长的主键id',
  `config_name` varchar(50) DEFAULT NULL COMMENT '配置信息的名称',
  `config_val` varchar(100) DEFAULT NULL COMMENT '配置信息的值',
  `status` tinyint(1) DEFAULT '1' COMMENT '当前记录的状态的信息（0=>删除   1=>启用）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录的创建的时间的信息',
  PRIMARY KEY (`id`),
  KEY `iname` (`config_name`),
  KEY `istatus` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ak_bargain_details
-- ----------------------------
DROP TABLE IF EXISTS `ak_bargain_details`;
CREATE TABLE `ak_bargain_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长的主键ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户的ID',
  `order_id` int(11) DEFAULT NULL COMMENT '砍价的订单的ID',
  `money` float(10,0) DEFAULT '0' COMMENT '砍价的或者加价的钱',
  `friend_wechat_name` varchar(50) DEFAULT NULL COMMENT '砍价的好友的名称',
  `friend_openid` varchar(255) DEFAULT NULL COMMENT '砍价的好友的openID',
  `friend_headimgurl` varchar(500) DEFAULT NULL COMMENT '砍价好友的头像',
  `is_type` tinyint(1) DEFAULT '1' COMMENT '好友还是损友，砍价了是好友，加价了是损友，（1->好友，2->损友）',
  `status` tinyint(1) DEFAULT '1' COMMENT '当前记录的状态的信息（0=>删除   1=>启用）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录的创建的时间的信息',
  PRIMARY KEY (`id`),
  KEY `itype` (`is_type`),
  KEY `istatus` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for ak_bargain_goods
-- ----------------------------
DROP TABLE IF EXISTS `ak_bargain_goods`;
CREATE TABLE `ak_bargain_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长的主键的ID',
  `goods_name` varchar(100) DEFAULT NULL COMMENT '产品的名称',
  `origin_price` float DEFAULT NULL COMMENT '产品的原始价格',
  `low_price` float DEFAULT NULL COMMENT '产品的最低价格',
  `attend_persons` int(11) DEFAULT NULL COMMENT '参与的人数',
  `rest_nums` int(11) DEFAULT NULL COMMENT '库存的数量',
  `red_money` float DEFAULT NULL COMMENT '红包的钱数',
  `status` tinyint(1) DEFAULT '1' COMMENT '当前记录的状态的信息（0=>删除   1=>启用）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录的创建的时间的信息',
  `attend_img` varchar(500) DEFAULT NULL COMMENT '参加的人的头像',
  PRIMARY KEY (`id`),
  KEY `istatus` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;



-- ----------------------------
-- Table structure for ak_bargain_orders
-- ----------------------------
DROP TABLE IF EXISTS `ak_bargain_orders`;
CREATE TABLE `ak_bargain_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长的主键的ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户的ID',
  `openid` varchar(100) DEFAULT NULL,
  `goods_id` int(10) DEFAULT NULL,
  `friend_price` float(10,0) DEFAULT '0' COMMENT '砍掉的价格',
  `red_price` float(10,0) DEFAULT '0' COMMENT '红包抵用的价格',
  `goods_name` varchar(100) DEFAULT NULL COMMENT '商品的名称',
  `origin_price` float(10,0) DEFAULT NULL,
  `low_price` float(10,0) DEFAULT NULL,
  `is_my` tinyint(1) DEFAULT '0' COMMENT '自己是否已经砍了 0->未砍  1->砍过了',
  `order_num` varchar(50) DEFAULT NULL COMMENT '订单号',
  `stime` varchar(20) DEFAULT NULL COMMENT '开始发起的时间',
  `is_valid` tinyint(1) DEFAULT '1' COMMENT '是否还有效，（1->有效,2->无效）',
  `status` tinyint(1) DEFAULT '1' COMMENT '当前记录的状态的信息（0=>删除   1=>启用）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录的创建的时间的信息',
  PRIMARY KEY (`id`),
  KEY `iuid` (`uid`),
  KEY `ivalid` (`is_valid`),
  KEY `istatus` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ak_bargain_users
-- ----------------------------
DROP TABLE IF EXISTS `ak_bargain_users`;
CREATE TABLE `ak_bargain_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增长的主键的ID',
  `openid` varchar(255) DEFAULT NULL COMMENT '微信用户的openid',
  `wechat_name` varchar(50) DEFAULT NULL COMMENT '微信的昵称',
  `true_name` varchar(50) DEFAULT NULL COMMENT '真实的用户姓名',
  `phone` varchar(20) DEFAULT NULL COMMENT '用户的电话号码',
  `address` varchar(255) DEFAULT NULL COMMENT '家庭住址',
  `sex` tinyint(1) DEFAULT NULL,
  `headimgurl` varchar(500) DEFAULT NULL COMMENT '用户的微信头像',
  `country` varchar(20) DEFAULT NULL COMMENT '国家',
  `province` varchar(20) DEFAULT NULL COMMENT '省份',
  `city` varchar(20) DEFAULT NULL COMMENT '城市',
  `status` tinyint(1) DEFAULT '1' COMMENT '当前记录的状态的信息（0=>删除   1=>启用）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录的创建的时间的信息',
  PRIMARY KEY (`id`),
  KEY `i_openid` (`openid`),
  KEY `istatus` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8;






























