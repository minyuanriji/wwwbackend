/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : jxmall_sinbel_to

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2020-09-24 09:40:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for jxmall_plugin_video
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video`;
CREATE TABLE `jxmall_plugin_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL COMMENT '商家id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `video_url` varchar(255) NOT NULL COMMENT '视频路径',
  `related_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '关联类型1：关联商品 2：关联店铺 3：关联地址',
  `first_frame` varchar(255) NOT NULL COMMENT '首帧',
  `related_goods_id` int(11) DEFAULT NULL COMMENT '关联商品id',
  `related_store_id` int(11) DEFAULT NULL COMMENT '关联门店id',
  `related_lon` varchar(20) DEFAULT NULL COMMENT '关联经度',
  `related_lat` varchar(20) DEFAULT NULL COMMENT '关联维度',
  `label_id` varchar(255) DEFAULT NULL COMMENT '标签id',
  `video_length` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '视频时长',
  `open_integral_award` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启积分奖励 0：未开启 1：开启',
  `open_money_award` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否开启现金奖励 0：未开启 1：开启',
  `max_integral` decimal(10,2) DEFAULT NULL COMMENT '积分最大值',
  `surplus_integral` decimal(10,0) unsigned DEFAULT '0' COMMENT '剩余的积分',
  `max_money` decimal(10,2) DEFAULT NULL COMMENT '金额最大值',
  `surplus_money` decimal(10,0) unsigned DEFAULT '0' COMMENT '剩余金额',
  `watch_integral_sum` decimal(10,2) DEFAULT NULL COMMENT '观看奖励积分总',
  `watch_integral` decimal(10,2) DEFAULT NULL COMMENT '观看奖励积分百分比',
  `watch_integral_one` decimal(10,2) DEFAULT NULL COMMENT '观看奖励积分一级百分比',
  `watch_integral_two` decimal(10,2) DEFAULT NULL COMMENT '观看奖励现金二级百分比',
  `watch_money_sum` decimal(10,0) DEFAULT NULL COMMENT '观看现金奖励总',
  `watch_money` decimal(10,0) DEFAULT NULL COMMENT '观看奖励现金百分比',
  `watch_money_one` decimal(10,0) DEFAULT NULL COMMENT '观看奖励现金一级百分比',
  `watch_money_two` decimal(10,0) DEFAULT NULL COMMENT '观看奖励现金二级百分比',
  `check_remark` varchar(255) DEFAULT NULL COMMENT '审核备注',
  `check_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核状态：0：未审核 1：审核通过 2：审核不通过',
  `comment_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论量',
  `like_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞量',
  `look_people_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '观看人数',
  `look_complete_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完整观看人数',
  `look_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `share_num` int(10) unsigned DEFAULT '0' COMMENT '分享数量',
  `is_blacklist` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否拉黑0：否 1：是',
  `is_top` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否置顶0：否 1：是',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `check_time` int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `top_time` int(11) NOT NULL DEFAULT '0' COMMENT '置顶时间',
  `blacklist_time` int(11) NOT NULL DEFAULT '0' COMMENT '拉黑时间',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `related_type` (`related_type`) USING BTREE,
  KEY `is_blacklist` (`is_blacklist`) USING BTREE,
  KEY `check_status` (`check_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COMMENT='视频列表';

-- ----------------------------
-- Records of jxmall_plugin_video
-- ----------------------------
INSERT INTO `jxmall_plugin_video` VALUES ('1', '5', '2', '这是第1条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '12', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '996', '0.80', '32.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '2', '119', '7', '1', '0', '0', '0', '0', '0', '0', '1599464796', '1600855003', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('2', '5', '2', '这是第2条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'https://jx.shuzixiangdao.com/web//uploads/video/original/20200921/bcbab65ff3e278a31b35fb88ab04d6cb.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '4995', '1000.00', '998', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '1', '1', '1', '33', '1', '0', '1', '0', '0', '1600417519', '0', '1599464796', '1600867665', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('3', '5', '2', '这是第3条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/6f3845ae4383432f14fef59ef0eaa1ed.mp4', '1', '', '14', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '4979', '1000.00', '990', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '4', '1', '2', '80', '0', '0', '1', '0', '0', '1600417517', '0', '1599464796', '1600867667', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('4', '5', '3', '这是第4条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/da0bb533670a6f74b2b7dad496c35d22.mp4', '1', '', '10', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '4987', '1000.00', '994', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '2', '2', '71', '0', '0', '1', '0', '0', '1600417513', '0', '1599464796', '1600867729', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('5', '5', '5', '这是第5条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/610d2fe3dbf2b4fc8c9cbd4531af27a6.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '996', '0.80', '32.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '2', '119', '9', '1', '0', '0', '0', '0', '0', '0', '1599464796', '1600855005', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('6', '5', '5', '这是第6条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '1', '0', '6', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600855005', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('7', '5', '5', '这是第7条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'https://jx.shuzixiangdao.com/web//uploads/video/original/20200921/8714191c02b0dc35442c199fde431ae6.mp4', '1', '', '4', null, null, null, '4', '85', '1', '1', '5000.00', '4995', '1000.00', '998', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '1', '1', '8', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600867538', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('8', '5', '6', '这是第8条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'https://jx.shuzixiangdao.com/web//uploads/video/original/20200921/bcbab65ff3e278a31b35fb88ab04d6cb.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '1', '0', '10', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600855006', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('9', '5', '6', '这是第9条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'https://jx.shuzixiangdao.com/web//uploads/video/original/20200921/34bcdd51c999a23a7efa9c990bdc7a57.mp4', '1', '', '4', null, null, null, '1,2,3,5', '85', '1', '1', '5000.00', '5000', '1000.00', '990', '0.80', '32.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '3', '121', '21', '1', '0', '0', '0', '0', '0', '0', '1599464796', '1600855006', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('10', '5', '4', '这是第10条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '2', '0', '16', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600855007', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('11', '5', '4', '这是第11条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '2', '0', '14', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600855007', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('12', '5', '4', '这是第12条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '2', '0', '14', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600855008', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('13', '5', '4', '这是第13条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '996', '0.80', '32.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '3', '119', '17', '1', '0', '0', '0', '0', '0', '0', '1599464796', '1600855008', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('14', '5', '3', '这是第14条视频', '这是第2条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '5000', '1000.00', '1000', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '0', '2', '0', '20', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600858788', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('15', '5', '3', '这是第15条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '4', '85', '1', '1', '5000.00', '4979', '1000.00', '990', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '1', '2', '39', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600867822', '0');
INSERT INTO `jxmall_plugin_video` VALUES ('16', '5', '3', '这是第16条视频', '这是第一条视频哈哈哈哈哈哈哈哈哈开心', 'http://jxmall.sinbel.top/web//uploads/video/original/20200811/cbe69585de3efee9c6cd3e88b4ae7abe.mp4', '1', '', '4', null, null, null, '1,2,3', '85', '1', '1', '5000.00', '4979', '1000.00', '990', '10.00', '50.00', '30.00', '20.00', '5', '50', '30', '20', null, '1', '0', '3', '1', '2', '49', '0', '0', '0', '0', '0', '0', '0', '1599464796', '1600867672', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_comment
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_comment`;
CREATE TABLE `jxmall_plugin_video_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `content` varchar(255) NOT NULL COMMENT '评论内容',
  `like_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '点赞量',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COMMENT='视频评论列表';

-- ----------------------------
-- Records of jxmall_plugin_video_comment
-- ----------------------------
INSERT INTO `jxmall_plugin_video_comment` VALUES ('1', '5', '2', '3', '这是第一个评论', '2', '0', '1600756086', '1599723721', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('2', '5', '2', '3', '这是第二个评论', '71', '0', '1600583286', '1600761019', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('3', '5', '2', '3', '这是第三个评论', '36', '0', '1599705424', '1600842882', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('4', '5', '2', '4', '这是个评论', '11', '0', '1599705424', '1600760998', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('5', '5', '2', '4', '这是阿松大个评论', '43', '0', '1599705424', '1600761023', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('6', '5', '2', '3', '12312', '0', '0', '1600761543', '1600761543', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('7', '5', '2', '4', '321', '0', '0', '1600761599', '1600761599', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('8', '5', '1', '3', '123', '0', '0', '1600761737', '1600761737', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('9', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600779997', '1600779997', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('10', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780012', '1600780012', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('11', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780352', '1600780352', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('12', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780421', '1600780421', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('13', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780436', '1600780436', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('14', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780450', '1600780450', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('15', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780478', '1600780478', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('16', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780483', '1600780483', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('17', '5', '2', '3', 'hhhhhhhh', '0', '0', '1600780561', '1600780561', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('18', '5', '1', '2', '123', '0', '0', '1600843319', '1600843376', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('19', '5', '1', '2', '11111', '0', '0', '1600843362', '1600843447', '0');
INSERT INTO `jxmall_plugin_video_comment` VALUES ('20', '5', '1', '2', '111', '1', '0', '1600843450', '1600843453', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_comment_like
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_comment_like`;
CREATE TABLE `jxmall_plugin_video_comment_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用戶id',
  `comment_id` int(11) NOT NULL COMMENT '评论ID',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `comment_id` (`comment_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='评论点赞表';

-- ----------------------------
-- Records of jxmall_plugin_video_comment_like
-- ----------------------------
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('1', '5', '2', '1', '1', '1599723715', '1599723721', '1599723721');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('2', '5', '2', '3', '1', '1600760732', '1600760734', '1600760734');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('3', '5', '2', '3', '1', '1600760736', '1600761004', '1600761004');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('4', '5', '2', '2', '1', '1600760737', '1600760738', '1600760738');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('5', '5', '2', '4', '0', '1600760998', '1600760998', '0');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('6', '5', '2', '5', '1', '1600761009', '1600761023', '1600761023');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('7', '5', '2', '2', '0', '1600761019', '1600761019', '0');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('8', '5', '1', '3', '0', '1600842882', '1600842882', '0');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('9', '5', '1', '19', '1', '1600843368', '1600843370', '1600843370');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('10', '5', '1', '19', '1', '1600843373', '1600843374', '1600843374');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('11', '5', '1', '18', '1', '1600843375', '1600843376', '1600843376');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('12', '5', '1', '19', '1', '1600843445', '1600843447', '1600843447');
INSERT INTO `jxmall_plugin_video_comment_like` VALUES ('13', '5', '1', '20', '0', '1600843453', '1600843453', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_config
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_config`;
CREATE TABLE `jxmall_plugin_video_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启 0--关闭|1--开启',
  `is_check` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否需要审核0：否 1：是',
  `release_rule` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发布权限：0：无条件 1：有条件',
  `award_status` tinyint(4) DEFAULT '0' COMMENT '是否开启全局带货奖励0：否 1：是',
  `award_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖励类型 0：金额 1：百分比',
  `award_percentage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励百分比',
  `award_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `purchase_times_status` tinyint(4) DEFAULT '0' COMMENT '消费达到多少次 是否开启 0：未开启 1：开启',
  `purchase_times` int(10) unsigned DEFAULT '0' COMMENT '消费达到多少次',
  `purchase_cost_status` tinyint(4) DEFAULT '0' COMMENT '消费达标是否开启0：未开启 1：已开启',
  `purchase_cost` decimal(10,2) DEFAULT '0.00' COMMENT '消费达到多少钱',
  `purchase_goods_status` tinyint(4) DEFAULT '0' COMMENT '消费商品是否开启0：未开启 1：已开启',
  `purchase_goods_id` int(11) DEFAULT NULL COMMENT '消费某个商品',
  `video_lenght` int(11) NOT NULL DEFAULT '0' COMMENT '视频发布时长',
  `agreement` text COLLATE utf8mb4_bin COMMENT '视频协议',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='视频配置';

-- ----------------------------
-- Records of jxmall_plugin_video_config
-- ----------------------------
INSERT INTO `jxmall_plugin_video_config` VALUES ('2', '5', '1', '1', '0', '1', '0', '0.00', '0.00', '0', '33', '0', '5.00', '1', '15', '100', 0xE9809FE5BAA6E69292E5A4A7E5A4A7, '0', '1598943235', '1599464542', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_follow
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_follow`;
CREATE TABLE `jxmall_plugin_video_follow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `follow_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `follow_id` (`follow_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='关注列表';

-- ----------------------------
-- Records of jxmall_plugin_video_follow
-- ----------------------------
INSERT INTO `jxmall_plugin_video_follow` VALUES ('1', '5', '4', '2', '1599560511', '0', '1599560511', '0');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('2', '5', '2', '3', '1599560511', '1599640731', '1599640731', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('3', '5', '2', '4', '1599560511', '1599640898', '1599640898', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('4', '5', '2', '3', '1599641661', '0', '1599641661', '0');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('5', '5', '1', '2', '1600780117', '1600780305', '1600780305', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('6', '5', '1', '2', '1600780332', '1600780339', '1600780339', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('7', '5', '1', '2', '1600780368', '1600780375', '1600780375', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('8', '5', '1', '2', '1600780386', '1600780391', '1600780391', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('9', '5', '1', '2', '1600831836', '1600831837', '1600831837', '1');
INSERT INTO `jxmall_plugin_video_follow` VALUES ('10', '5', '1', '2', '1600855115', '0', '1600855115', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_goods
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_goods`;
CREATE TABLE `jxmall_plugin_video_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `share_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '佣金类型 0，固定金额，1百分比',
  `percentage` decimal(10,0) DEFAULT '0' COMMENT '百分比',
  `fixed_value` decimal(10,0) DEFAULT '0' COMMENT '固定值',
  `is_alone` tinyint(4) NOT NULL DEFAULT '0' COMMENT '独立设置 0：否 1：是',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE,
  KEY `share_type` (`share_type`) USING BTREE,
  KEY `is_alone` (`is_alone`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='短视频商品设置';

-- ----------------------------
-- Records of jxmall_plugin_video_goods
-- ----------------------------
INSERT INTO `jxmall_plugin_video_goods` VALUES ('1', '5', '17', '0', '0', '10', '1', '1600692588', '0', '1600692588', '0');
INSERT INTO `jxmall_plugin_video_goods` VALUES ('2', '5', '16', '1', '5', '0', '1', '1600692588', '0', '1600692588', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_lable
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_lable`;
CREATE TABLE `jxmall_plugin_video_lable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '标签名称',
  `create_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '创建类型1：用户创建 2：平台创建',
  `user_id` int(11) DEFAULT NULL COMMENT '发布用户id',
  `admin_id` int(11) DEFAULT NULL COMMENT '发布管理员id',
  `is_recommend` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否推荐 1：否 2：是',
  `recommend_time` int(11) NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `hot_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '热量',
  `usage_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `create_type` (`create_type`) USING BTREE,
  KEY `is_recommend` (`is_recommend`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='视频标签';

-- ----------------------------
-- Records of jxmall_plugin_video_lable
-- ----------------------------
INSERT INTO `jxmall_plugin_video_lable` VALUES ('1', '5', '猫', '1', '3', null, '1', '0', '0', '0', '0', '1599031893', '1599031893', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('2', '5', '狗', '2', null, '1', '1', '1599097625', '0', '0', '0', '1599097625', '1599097625', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('3', '5', '风景', '2', null, '1', '2', '1599118891', '0', '0', '0', '1599099957', '1599118891', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('4', '5', '美食', '2', null, '1', '1', '1599118866', '0', '0', '0', '1599100560', '1599118866', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('5', '5', '宠物', '1', '3', null, '0', '0', '0', '0', '0', '1599204377', '1599204377', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('6', '5', '宠物是对方身', '1', '3', null, '0', '0', '0', '0', '0', '1599204787', '1599204787', '0');
INSERT INTO `jxmall_plugin_video_lable` VALUES ('7', '5', '别人家男朋友', '1', '2', null, '0', '0', '0', '0', '0', '1599204973', '1599204973', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_like
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_like`;
CREATE TABLE `jxmall_plugin_video_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`),
  KEY `user_id` (`user_id`),
  KEY `video_id` (`video_id`),
  KEY `is_delete` (`is_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='点赞记录';

-- ----------------------------
-- Records of jxmall_plugin_video_like
-- ----------------------------
INSERT INTO `jxmall_plugin_video_like` VALUES ('1', '5', '2', '2', '0', '1599615865', '1599615865', '0');
INSERT INTO `jxmall_plugin_video_like` VALUES ('3', '5', '2', '1', '1', '1599727586', '1599727611', '1599727611');
INSERT INTO `jxmall_plugin_video_like` VALUES ('4', '5', '2', '3', '1', '1600738443', '1600738450', '1600738450');
INSERT INTO `jxmall_plugin_video_like` VALUES ('5', '5', '2', '3', '1', '1600738454', '1600738459', '1600738459');
INSERT INTO `jxmall_plugin_video_like` VALUES ('6', '5', '2', '3', '1', '1600738462', '1600738467', '1600738467');
INSERT INTO `jxmall_plugin_video_like` VALUES ('7', '5', '2', '3', '1', '1600738471', '1600743151', '1600743151');
INSERT INTO `jxmall_plugin_video_like` VALUES ('8', '5', '2', '3', '1', '1600743164', '1600743228', '1600743228');
INSERT INTO `jxmall_plugin_video_like` VALUES ('9', '5', '2', '3', '1', '1600743235', '1600744328', '1600744328');
INSERT INTO `jxmall_plugin_video_like` VALUES ('10', '5', '2', '3', '1', '1600744329', '1600744443', '1600744443');
INSERT INTO `jxmall_plugin_video_like` VALUES ('11', '5', '2', '3', '1', '1600744447', '1600744453', '1600744453');
INSERT INTO `jxmall_plugin_video_like` VALUES ('12', '5', '2', '5', '0', '1600744457', '1600744457', '0');
INSERT INTO `jxmall_plugin_video_like` VALUES ('13', '5', '2', '9', '1', '1600745919', '1600745920', '1600745920');
INSERT INTO `jxmall_plugin_video_like` VALUES ('14', '5', '1', '2', '1', '1600767515', '1600767516', '1600767516');
INSERT INTO `jxmall_plugin_video_like` VALUES ('15', '5', '1', '2', '1', '1600779579', '1600779582', '1600779582');
INSERT INTO `jxmall_plugin_video_like` VALUES ('16', '5', '1', '2', '1', '1600831834', '1600831835', '1600831835');
INSERT INTO `jxmall_plugin_video_like` VALUES ('17', '5', '1', '2', '0', '1600844489', '1600844489', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_look
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_look`;
CREATE TABLE `jxmall_plugin_video_look` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `is_share` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否分享观看 0：否 1：是',
  `share_id` int(11) DEFAULT NULL COMMENT '分享者id',
  `is_complete_look` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否完整观看0：否 1：是',
  `complete_time` int(11) NOT NULL DEFAULT '0' COMMENT '完整观看时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `is_complete_look` (`is_complete_look`) USING BTREE,
  KEY `is_share` (`is_share`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=414 DEFAULT CHARSET=utf8mb4 COMMENT='视频浏览记录';

-- ----------------------------
-- Records of jxmall_plugin_video_look
-- ----------------------------
INSERT INTO `jxmall_plugin_video_look` VALUES ('1', '5', '1', '2', '1', '3', '0', '0', '0', '1600157465', '1600157465', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('2', '5', '1', '2', '1', '3', '1', '1600339620', '0', '1600157495', '1600339620', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('3', '5', '1', '2', '1', '3', '0', '0', '0', '1600392168', '1600392168', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('4', '5', '1', '2', '1', '6', '0', '0', '0', '1600511424', '1600511424', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('9', '5', '16', '2', '0', null, '1', '1600693952', '0', '1600693952', '1600693952', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('10', '5', '3', '2', '0', null, '0', '0', '0', '1600694024', '1600694024', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('11', '5', '4', '2', '0', null, '0', '0', '0', '1600694025', '1600694025', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('12', '5', '16', '2', '0', null, '1', '1600694154', '0', '1600694025', '1600694154', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('13', '5', '15', '2', '0', null, '1', '1600694044', '0', '1600694044', '1600694044', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('14', '5', '3', '2', '0', null, '1', '1600694288', '0', '1600694063', '1600694288', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('15', '5', '4', '2', '0', null, '0', '0', '0', '1600694064', '1600694064', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('16', '5', '4', '2', '0', null, '0', '0', '0', '1600694169', '1600694169', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('17', '5', '16', '2', '0', null, '1', '1600694540', '0', '1600694170', '1600694540', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('18', '5', '4', '2', '0', null, '0', '0', '0', '1600694354', '1600694354', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('19', '5', '3', '2', '0', null, '1', '1600741833', '0', '1600694411', '1600741833', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('20', '5', '4', '2', '0', null, '0', '0', '0', '1600741892', '1600741892', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('21', '5', '3', '2', '0', null, '1', '1600742561', '0', '1600741892', '1600742561', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('22', '5', '4', '2', '0', null, '0', '0', '0', '1600742696', '1600742696', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('23', '5', '3', '2', '0', null, '0', '0', '0', '1600742699', '1600742699', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('24', '5', '4', '2', '0', null, '0', '0', '0', '1600742720', '1600742720', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('25', '5', '3', '2', '0', null, '0', '0', '0', '1600742724', '1600742724', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('26', '5', '4', '2', '0', null, '0', '0', '0', '1600742725', '1600742725', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('27', '5', '3', '2', '0', null, '0', '0', '0', '1600742726', '1600742726', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('28', '5', '4', '2', '0', null, '0', '0', '0', '1600742727', '1600742727', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('29', '5', '16', '2', '0', null, '0', '0', '0', '1600742727', '1600742727', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('30', '5', '15', '2', '0', null, '1', '1600742747', '0', '1600742729', '1600742747', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('31', '5', '16', '2', '0', null, '0', '0', '0', '1600742751', '1600742751', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('32', '5', '15', '2', '0', null, '0', '0', '0', '1600742752', '1600742752', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('33', '5', '16', '2', '0', null, '0', '0', '0', '1600742753', '1600742753', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('34', '5', '15', '2', '0', null, '0', '0', '0', '1600742754', '1600742754', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('35', '5', '16', '2', '0', null, '0', '0', '0', '1600742756', '1600742756', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('36', '5', '4', '2', '0', null, '0', '0', '0', '1600742757', '1600742757', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('37', '5', '3', '2', '0', null, '0', '0', '0', '1600742758', '1600742758', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('38', '5', '4', '2', '0', null, '0', '0', '0', '1600742759', '1600742759', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('39', '5', '16', '2', '0', null, '0', '0', '0', '1600742759', '1600742759', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('40', '5', '15', '2', '0', null, '0', '0', '0', '1600742760', '1600742760', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('41', '5', '16', '2', '0', null, '0', '0', '0', '1600742762', '1600742762', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('42', '5', '4', '2', '0', null, '0', '0', '0', '1600742762', '1600742762', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('43', '5', '3', '2', '0', null, '0', '0', '0', '1600742763', '1600742763', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('44', '5', '4', '2', '0', null, '0', '0', '0', '1600742848', '1600742848', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('45', '5', '16', '2', '0', null, '0', '0', '0', '1600742849', '1600742849', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('46', '5', '15', '2', '0', null, '0', '0', '0', '1600742850', '1600742850', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('47', '5', '16', '2', '0', null, '0', '0', '0', '1600742851', '1600742851', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('48', '5', '4', '2', '0', null, '0', '0', '0', '1600742853', '1600742853', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('49', '5', '3', '2', '0', null, '0', '0', '0', '1600742855', '1600742855', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('50', '5', '4', '2', '0', null, '0', '0', '0', '1600742856', '1600742856', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('51', '5', '3', '2', '0', null, '0', '0', '0', '1600742857', '1600742857', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('52', '5', '4', '2', '0', null, '0', '0', '0', '1600742938', '1600742938', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('53', '5', '16', '2', '0', null, '0', '0', '0', '1600742940', '1600742940', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('54', '5', '15', '2', '0', null, '0', '0', '0', '1600742941', '1600742941', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('55', '5', '16', '2', '0', null, '0', '0', '0', '1600742942', '1600742942', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('56', '5', '15', '2', '0', null, '0', '0', '0', '1600742943', '1600742943', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('57', '5', '14', '2', '0', null, '0', '0', '0', '1600742943', '1600742943', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('58', '5', '15', '2', '0', null, '0', '0', '0', '1600742944', '1600742944', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('59', '5', '16', '2', '0', null, '0', '0', '0', '1600742945', '1600742945', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('60', '5', '4', '2', '0', null, '0', '0', '0', '1600742946', '1600742946', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('61', '5', '3', '2', '0', null, '0', '0', '0', '1600742947', '1600742947', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('62', '5', '4', '2', '0', null, '0', '0', '0', '1600742949', '1600742949', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('63', '5', '16', '2', '0', null, '1', '1600742968', '0', '1600742950', '1600742968', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('64', '5', '15', '2', '0', null, '0', '0', '0', '1600742970', '1600742970', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('65', '5', '16', '2', '0', null, '0', '0', '0', '1600742971', '1600742971', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('66', '5', '4', '2', '0', null, '0', '0', '0', '1600742972', '1600742972', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('67', '5', '16', '2', '0', null, '0', '0', '0', '1600742973', '1600742973', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('68', '5', '4', '2', '0', null, '0', '0', '0', '1600742974', '1600742974', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('69', '5', '3', '2', '0', null, '0', '0', '0', '1600742974', '1600742974', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('70', '5', '4', '2', '0', null, '0', '0', '0', '1600742976', '1600742976', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('71', '5', '16', '2', '0', null, '0', '0', '0', '1600742977', '1600742977', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('72', '5', '15', '2', '0', null, '0', '0', '0', '1600742978', '1600742978', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('73', '5', '14', '2', '0', null, '0', '0', '0', '1600742978', '1600742978', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('74', '5', '15', '2', '0', null, '0', '0', '0', '1600742979', '1600742979', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('75', '5', '16', '2', '0', null, '0', '0', '0', '1600742980', '1600742980', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('76', '5', '4', '2', '0', null, '0', '0', '0', '1600742981', '1600742981', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('77', '5', '3', '2', '0', null, '1', '1600761702', '0', '1600742981', '1600761702', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('78', '5', '4', '2', '0', null, '0', '0', '0', '1600745603', '1600745603', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('79', '5', '16', '2', '0', null, '0', '0', '0', '1600745604', '1600745604', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('80', '5', '15', '2', '0', null, '0', '0', '0', '1600745605', '1600745605', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('81', '5', '14', '2', '0', null, '0', '0', '0', '1600745606', '1600745606', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('82', '5', '15', '2', '0', null, '0', '0', '0', '1600745607', '1600745607', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('83', '5', '14', '2', '0', null, '0', '0', '0', '1600745608', '1600745608', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('84', '5', '15', '2', '0', null, '0', '0', '0', '1600745609', '1600745609', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('85', '5', '16', '2', '0', null, '0', '0', '0', '1600745610', '1600745610', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('86', '5', '15', '2', '0', null, '0', '0', '0', '1600745610', '1600745610', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('87', '5', '14', '2', '0', null, '0', '0', '0', '1600745611', '1600745611', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('88', '5', '13', '2', '0', null, '0', '0', '0', '1600745612', '1600745612', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('89', '5', '12', '2', '0', null, '0', '0', '0', '1600745612', '1600745612', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('90', '5', '11', '2', '0', null, '0', '0', '0', '1600745613', '1600745613', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('91', '5', '10', '2', '0', null, '0', '0', '0', '1600745613', '1600745613', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('92', '5', '9', '2', '0', null, '0', '0', '0', '1600745614', '1600745614', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('93', '5', '4', '2', '0', null, '0', '0', '0', '1600745677', '1600745677', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('94', '5', '16', '2', '0', null, '0', '0', '0', '1600745677', '1600745677', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('95', '5', '15', '2', '0', null, '0', '0', '0', '1600745678', '1600745678', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('96', '5', '14', '2', '0', null, '0', '0', '0', '1600745679', '1600745679', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('97', '5', '13', '2', '0', null, '0', '0', '0', '1600745680', '1600745680', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('98', '5', '12', '2', '0', null, '0', '0', '0', '1600745681', '1600745681', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('99', '5', '11', '2', '0', null, '0', '0', '0', '1600745681', '1600745681', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('100', '5', '10', '2', '0', null, '0', '0', '0', '1600745682', '1600745682', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('101', '5', '9', '2', '0', null, '0', '0', '0', '1600745682', '1600745682', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('102', '5', '4', '2', '0', null, '0', '0', '0', '1600745721', '1600745721', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('103', '5', '16', '2', '0', null, '0', '0', '0', '1600745721', '1600745721', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('104', '5', '15', '2', '0', null, '0', '0', '0', '1600745722', '1600745722', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('105', '5', '14', '2', '0', null, '0', '0', '0', '1600745723', '1600745723', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('106', '5', '13', '2', '0', null, '0', '0', '0', '1600745723', '1600745723', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('107', '5', '12', '2', '0', null, '0', '0', '0', '1600745724', '1600745724', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('108', '5', '11', '2', '0', null, '0', '0', '0', '1600745725', '1600745725', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('109', '5', '10', '2', '0', null, '0', '0', '0', '1600745725', '1600745725', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('110', '5', '9', '2', '0', null, '1', '1600745811', '0', '1600745726', '1600745811', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('111', '5', '4', '2', '0', null, '0', '0', '0', '1600745855', '1600745855', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('112', '5', '16', '2', '0', null, '0', '0', '0', '1600745855', '1600745855', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('113', '5', '15', '2', '0', null, '0', '0', '0', '1600745856', '1600745856', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('114', '5', '14', '2', '0', null, '0', '0', '0', '1600745857', '1600745857', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('115', '5', '13', '2', '0', null, '0', '0', '0', '1600745858', '1600745858', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('116', '5', '12', '2', '0', null, '0', '0', '0', '1600745858', '1600745858', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('117', '5', '11', '2', '0', null, '0', '0', '0', '1600745859', '1600745859', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('118', '5', '10', '2', '0', null, '0', '0', '0', '1600745860', '1600745860', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('119', '5', '9', '2', '0', null, '1', '1600746073', '0', '1600745861', '1600746073', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('120', '5', '4', '2', '0', null, '0', '0', '0', '1600746434', '1600746434', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('121', '5', '4', '2', '0', null, '0', '0', '0', '1600760743', '1600760743', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('122', '5', '4', '2', '0', null, '1', '1600760977', '0', '1600760780', '1600760977', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('123', '5', '4', '2', '0', null, '0', '0', '0', '1600760995', '1600760995', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('124', '5', '4', '2', '0', null, '0', '0', '0', '1600761005', '1600761005', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('125', '5', '4', '2', '0', null, '0', '0', '0', '1600761016', '1600761016', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('126', '5', '4', '2', '0', null, '1', '1600761119', '0', '1600761020', '1600761119', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('127', '5', '4', '2', '0', null, '0', '0', '0', '1600761594', '1600761594', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('128', '5', '4', '1', '0', null, '0', '0', '0', '1600761933', '1600761933', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('129', '5', '4', '1', '0', null, '0', '0', '0', '1600761963', '1600761963', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('130', '5', '2', '2', '0', null, '0', '0', '0', '1600762135', '1600762135', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('131', '5', '3', '1', '0', null, '1', '1600763950', '0', '1600763099', '1600763950', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('132', '5', '4', '1', '0', null, '0', '0', '0', '1600763803', '1600763803', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('133', '5', '16', '1', '0', null, '1', '1600763866', '0', '1600763830', '1600763866', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('134', '5', '2', '1', '0', null, '1', '1600764063', '0', '1600764001', '1600764063', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('135', '5', '3', '1', '0', null, '0', '0', '0', '1600764076', '1600764076', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('136', '5', '2', '1', '0', null, '1', '1600764512', '0', '1600764081', '1600764512', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('137', '5', '3', '1', '0', null, '0', '0', '0', '1600764518', '1600764518', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('138', '5', '4', '1', '0', null, '0', '0', '0', '1600764518', '1600764518', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('139', '5', '16', '1', '0', null, '0', '0', '0', '1600764519', '1600764519', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('140', '5', '15', '1', '0', null, '0', '0', '0', '1600764521', '1600764521', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('141', '5', '14', '1', '0', null, '0', '0', '0', '1600764524', '1600764524', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('142', '5', '13', '1', '0', null, '0', '0', '0', '1600764525', '1600764525', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('143', '5', '12', '1', '0', null, '0', '0', '0', '1600764527', '1600764527', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('144', '5', '11', '1', '0', null, '0', '0', '0', '1600764529', '1600764529', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('145', '5', '10', '1', '0', null, '0', '0', '0', '1600764530', '1600764530', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('146', '5', '9', '1', '0', null, '0', '0', '0', '1600764538', '1600764538', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('147', '5', '10', '1', '0', null, '0', '0', '0', '1600764540', '1600764540', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('148', '5', '3', '1', '0', null, '0', '0', '0', '1600764552', '1600764552', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('149', '5', '4', '1', '0', null, '0', '0', '0', '1600764554', '1600764554', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('150', '5', '16', '1', '0', null, '0', '0', '0', '1600764555', '1600764555', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('151', '5', '15', '1', '0', null, '0', '0', '0', '1600764556', '1600764556', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('152', '5', '14', '1', '0', null, '0', '0', '0', '1600764558', '1600764558', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('153', '5', '13', '1', '0', null, '0', '0', '0', '1600764565', '1600764565', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('154', '5', '12', '1', '0', null, '0', '0', '0', '1600764567', '1600764567', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('155', '5', '11', '1', '0', null, '0', '0', '0', '1600764568', '1600764568', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('156', '5', '10', '1', '0', null, '0', '0', '0', '1600764570', '1600764570', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('157', '5', '9', '1', '0', null, '0', '0', '0', '1600764573', '1600764573', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('158', '5', '10', '1', '0', null, '0', '0', '0', '1600764581', '1600764581', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('159', '5', '9', '1', '0', null, '0', '0', '0', '1600764583', '1600764583', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('160', '5', '8', '1', '0', null, '0', '0', '0', '1600764585', '1600764585', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('161', '5', '9', '1', '0', null, '0', '0', '0', '1600764594', '1600764594', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('162', '5', '8', '1', '0', null, '0', '0', '0', '1600764605', '1600764605', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('163', '5', '9', '1', '0', null, '1', '1600764694', '0', '1600764609', '1600764694', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('164', '5', '8', '1', '0', null, '0', '0', '0', '1600764720', '1600764720', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('165', '5', '9', '1', '0', null, '1', '1600764771', '0', '1600764727', '1600764771', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('166', '5', '3', '1', '0', null, '0', '0', '0', '1600766291', '1600766291', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('167', '5', '4', '1', '0', null, '0', '0', '0', '1600766292', '1600766292', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('168', '5', '16', '1', '0', null, '0', '0', '0', '1600766292', '1600766292', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('169', '5', '15', '1', '0', null, '0', '0', '0', '1600766293', '1600766293', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('170', '5', '14', '1', '0', null, '0', '0', '0', '1600766293', '1600766293', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('171', '5', '13', '1', '0', null, '0', '0', '0', '1600766294', '1600766294', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('172', '5', '12', '1', '0', null, '0', '0', '0', '1600766295', '1600766295', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('173', '5', '11', '1', '0', null, '0', '0', '0', '1600766295', '1600766295', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('174', '5', '10', '1', '0', null, '0', '0', '0', '1600766295', '1600766295', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('175', '5', '9', '1', '0', null, '0', '0', '0', '1600766296', '1600766296', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('176', '5', '8', '1', '0', null, '0', '0', '0', '1600766297', '1600766297', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('177', '5', '7', '1', '0', null, '0', '0', '0', '1600766297', '1600766297', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('178', '5', '6', '1', '0', null, '0', '0', '0', '1600766298', '1600766298', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('179', '5', '5', '1', '0', null, '0', '0', '0', '1600766298', '1600766298', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('180', '5', '1', '1', '0', null, '0', '0', '0', '1600766299', '1600766299', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('181', '5', '5', '1', '0', null, '0', '0', '0', '1600766305', '1600766305', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('182', '5', '6', '1', '0', null, '0', '0', '0', '1600766306', '1600766306', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('183', '5', '7', '1', '0', null, '1', '1600853550', '0', '1600766306', '1600853550', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('184', '5', '8', '1', '0', null, '0', '0', '0', '1600766307', '1600766307', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('185', '5', '9', '1', '0', null, '0', '0', '0', '1600766307', '1600766307', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('186', '5', '11', '1', '0', null, '0', '0', '0', '1600766308', '1600766308', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('187', '5', '10', '1', '0', null, '0', '0', '0', '1600766309', '1600766309', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('188', '5', '12', '1', '0', null, '0', '0', '0', '1600766309', '1600766309', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('189', '5', '13', '1', '0', null, '0', '0', '0', '1600766309', '1600766309', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('190', '5', '14', '1', '0', null, '0', '0', '0', '1600766309', '1600766309', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('191', '5', '15', '1', '0', null, '0', '0', '0', '1600766310', '1600766310', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('192', '5', '16', '1', '0', null, '0', '0', '0', '1600766310', '1600766310', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('193', '5', '4', '1', '0', null, '0', '0', '0', '1600766311', '1600766311', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('194', '5', '3', '1', '0', null, '0', '0', '0', '1600766311', '1600766311', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('195', '5', '4', '1', '0', null, '0', '0', '0', '1600766321', '1600766321', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('196', '5', '3', '1', '0', null, '0', '0', '0', '1600766322', '1600766322', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('197', '5', '2', '1', '0', null, '1', '1600767194', '0', '1600766323', '1600767194', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('198', '5', '3', '1', '0', null, '0', '0', '0', '1600767210', '1600767210', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('199', '5', '2', '1', '0', null, '1', '1600776659', '0', '1600767211', '1600776659', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('200', '5', '3', '1', '0', null, '0', '0', '0', '1600776715', '1600776715', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('201', '5', '2', '1', '0', null, '1', '1600778751', '0', '1600776717', '1600778751', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('202', '5', '3', '1', '0', null, '0', '0', '0', '1600778754', '1600778754', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('203', '5', '4', '1', '0', null, '0', '0', '0', '1600778754', '1600778754', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('204', '5', '15', '1', '0', null, '0', '0', '0', '1600778755', '1600778755', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('205', '5', '16', '1', '0', null, '0', '0', '0', '1600778756', '1600778756', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('206', '5', '14', '1', '0', null, '0', '0', '0', '1600778757', '1600778757', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('207', '5', '16', '1', '0', null, '0', '0', '0', '1600778757', '1600778757', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('208', '5', '4', '1', '0', null, '0', '0', '0', '1600778758', '1600778758', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('209', '5', '15', '1', '0', null, '0', '0', '0', '1600778758', '1600778758', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('210', '5', '2', '1', '0', null, '1', '1600824184', '0', '1600778759', '1600824184', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('211', '5', '3', '1', '0', null, '0', '0', '0', '1600778759', '1600778759', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('212', '5', '3', '1', '0', null, '0', '0', '0', '1600831831', '1600831831', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('213', '5', '2', '1', '0', null, '1', '1600841008', '0', '1600831833', '1600841008', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('214', '5', '3', '1', '0', null, '0', '0', '0', '1600841035', '1600841035', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('215', '5', '2', '1', '0', null, '0', '0', '0', '1600841040', '1600841040', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('216', '5', '3', '1', '0', null, '0', '0', '0', '1600841068', '1600841068', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('217', '5', '4', '1', '0', null, '0', '0', '0', '1600841073', '1600841073', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('218', '5', '16', '1', '0', null, '1', '1600841102', '0', '1600841084', '1600841102', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('219', '5', '3', '1', '0', null, '0', '0', '0', '1600841119', '1600841119', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('220', '5', '4', '1', '0', null, '0', '0', '0', '1600841123', '1600841123', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('221', '5', '3', '1', '0', null, '0', '0', '0', '1600841125', '1600841125', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('222', '5', '2', '1', '0', null, '0', '0', '0', '1600841126', '1600841126', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('223', '5', '3', '1', '0', null, '0', '0', '0', '1600841136', '1600841136', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('224', '5', '2', '1', '0', null, '1', '1600841649', '0', '1600841137', '1600841649', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('225', '5', '3', '1', '0', null, '0', '0', '0', '1600841683', '1600841683', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('226', '5', '2', '1', '0', null, '1', '1600841902', '0', '1600841685', '1600841902', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('227', '5', '3', '1', '0', null, '0', '0', '0', '1600842181', '1600842181', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('228', '5', '2', '1', '0', null, '0', '0', '0', '1600842183', '1600842183', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('229', '5', '3', '1', '0', null, '0', '0', '0', '1600842195', '1600842195', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('230', '5', '2', '1', '0', null, '0', '0', '0', '1600842202', '1600842202', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('231', '5', '3', '1', '0', null, '1', '1600842707', '0', '1600842206', '1600842707', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('232', '5', '3', '1', '0', null, '0', '0', '0', '1600842766', '1600842766', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('233', '5', '3', '1', '0', null, '0', '0', '0', '1600842784', '1600842784', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('234', '5', '3', '1', '0', null, '0', '0', '0', '1600842820', '1600842820', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('235', '5', '3', '1', '0', null, '0', '0', '0', '1600842864', '1600842864', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('236', '5', '2', '1', '0', null, '0', '0', '0', '1600842919', '1600842919', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('237', '5', '3', '1', '0', null, '0', '0', '0', '1600842922', '1600842922', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('238', '5', '2', '1', '0', null, '0', '0', '0', '1600842941', '1600842941', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('239', '5', '3', '1', '0', null, '1', '1600843035', '0', '1600842963', '1600843035', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('240', '5', '3', '1', '0', null, '0', '0', '0', '1600843130', '1600843130', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('241', '5', '3', '1', '0', null, '0', '0', '0', '1600843161', '1600843161', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('242', '5', '3', '1', '0', null, '0', '0', '0', '1600843237', '1600843237', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('243', '5', '4', '1', '0', null, '0', '0', '0', '1600843259', '1600843259', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('244', '5', '16', '1', '0', null, '0', '0', '0', '1600843263', '1600843263', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('245', '5', '4', '1', '0', null, '0', '0', '0', '1600843266', '1600843266', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('246', '5', '3', '1', '0', null, '0', '0', '0', '1600843267', '1600843267', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('247', '5', '2', '1', '0', null, '1', '1600844161', '0', '1600843267', '1600844161', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('248', '5', '3', '1', '0', null, '0', '0', '0', '1600844208', '1600844208', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('249', '5', '3', '1', '0', null, '0', '0', '0', '1600844245', '1600844245', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('250', '5', '3', '1', '0', null, '0', '0', '0', '1600844266', '1600844266', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('251', '5', '3', '1', '0', null, '0', '0', '0', '1600844293', '1600844293', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('252', '5', '2', '1', '0', null, '1', '1600844429', '0', '1600844317', '1600844429', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('253', '5', '3', '1', '0', null, '0', '0', '0', '1600844463', '1600844463', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('254', '5', '2', '1', '0', null, '1', '1600845588', '0', '1600844469', '1600845588', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('255', '5', '3', '1', '0', null, '1', '1600845731', '0', '1600845659', '1600845731', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('256', '5', '3', '1', '0', null, '0', '0', '0', '1600845752', '1600845752', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('257', '5', '2', '1', '0', null, '1', '1600845849', '0', '1600845754', '1600845849', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('258', '5', '3', '1', '0', null, '0', '0', '0', '1600845867', '1600845867', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('259', '5', '4', '1', '0', null, '0', '0', '0', '1600845869', '1600845869', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('260', '5', '3', '1', '0', null, '0', '0', '0', '1600845871', '1600845871', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('261', '5', '2', '1', '0', null, '1', '1600848128', '0', '1600845872', '1600848128', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('262', '5', '3', '1', '0', null, '0', '0', '0', '1600848136', '1600848136', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('263', '5', '2', '1', '0', null, '1', '1600848885', '0', '1600848138', '1600848885', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('264', '5', '3', '1', '0', null, '0', '0', '0', '1600848910', '1600848910', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('265', '5', '4', '1', '0', null, '0', '0', '0', '1600848910', '1600848910', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('266', '5', '3', '1', '0', null, '0', '0', '0', '1600848990', '1600848990', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('267', '5', '2', '1', '0', null, '0', '0', '0', '1600848990', '1600848990', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('268', '5', '3', '1', '0', null, '0', '0', '0', '1600848992', '1600848992', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('269', '5', '4', '1', '0', null, '0', '0', '0', '1600848993', '1600848993', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('270', '5', '14', '1', '0', null, '0', '0', '0', '1600848993', '1600848993', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('271', '5', '16', '1', '0', null, '0', '0', '0', '1600848994', '1600848994', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('272', '5', '13', '1', '0', null, '0', '0', '0', '1600848994', '1600848994', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('273', '5', '15', '1', '0', null, '0', '0', '0', '1600848994', '1600848994', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('274', '5', '12', '1', '0', null, '0', '0', '0', '1600848994', '1600848994', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('275', '5', '11', '1', '0', null, '0', '0', '0', '1600848994', '1600848994', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('276', '5', '10', '1', '0', null, '0', '0', '0', '1600848996', '1600848996', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('277', '5', '9', '1', '0', null, '0', '0', '0', '1600848997', '1600848997', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('278', '5', '8', '1', '0', null, '0', '0', '0', '1600848998', '1600848998', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('279', '5', '9', '1', '0', null, '0', '0', '0', '1600848998', '1600848998', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('280', '5', '10', '1', '0', null, '0', '0', '0', '1600848999', '1600848999', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('281', '5', '11', '1', '0', null, '0', '0', '0', '1600848999', '1600848999', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('282', '5', '12', '1', '0', null, '0', '0', '0', '1600849000', '1600849000', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('283', '5', '13', '1', '0', null, '0', '0', '0', '1600849000', '1600849000', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('284', '5', '14', '1', '0', null, '0', '0', '0', '1600849001', '1600849001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('285', '5', '15', '1', '0', null, '0', '0', '0', '1600849001', '1600849001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('286', '5', '16', '1', '0', null, '0', '0', '0', '1600849001', '1600849001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('287', '5', '4', '1', '0', null, '0', '0', '0', '1600849002', '1600849002', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('288', '5', '3', '1', '0', null, '0', '0', '0', '1600849002', '1600849002', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('289', '5', '2', '1', '0', null, '1', '1600851616', '0', '1600849002', '1600851616', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('290', '5', '3', '1', '0', null, '0', '0', '0', '1600851695', '1600851695', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('291', '5', '2', '1', '0', null, '1', '1600853278', '0', '1600851696', '1600853278', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('292', '5', '3', '1', '0', null, '0', '0', '0', '1600853662', '1600853662', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('293', '5', '2', '1', '0', null, '0', '0', '0', '1600853664', '1600853664', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('294', '5', '3', '1', '0', null, '0', '0', '0', '1600853665', '1600853665', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('295', '5', '4', '1', '0', null, '0', '0', '0', '1600853667', '1600853667', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('296', '5', '16', '1', '0', null, '0', '0', '0', '1600853668', '1600853668', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('297', '5', '15', '1', '0', null, '0', '0', '0', '1600853669', '1600853669', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('298', '5', '14', '1', '0', null, '0', '0', '0', '1600853669', '1600853669', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('299', '5', '13', '1', '0', null, '0', '0', '0', '1600853670', '1600853670', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('300', '5', '12', '1', '0', null, '0', '0', '0', '1600853671', '1600853671', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('301', '5', '11', '1', '0', null, '0', '0', '0', '1600853672', '1600853672', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('302', '5', '10', '1', '0', null, '0', '0', '0', '1600853675', '1600853675', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('303', '5', '9', '1', '0', null, '0', '0', '0', '1600853683', '1600853683', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('304', '5', '8', '1', '0', null, '0', '0', '0', '1600853684', '1600853684', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('305', '5', '7', '1', '0', null, '0', '0', '0', '1600853684', '1600853684', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('306', '5', '6', '1', '0', null, '0', '0', '0', '1600853685', '1600853685', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('307', '5', '5', '1', '0', null, '0', '0', '0', '1600853686', '1600853686', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('308', '5', '1', '1', '0', null, '0', '0', '0', '1600853686', '1600853686', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('309', '5', '5', '1', '0', null, '0', '0', '0', '1600853693', '1600853693', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('310', '5', '6', '1', '0', null, '0', '0', '0', '1600853694', '1600853694', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('311', '5', '7', '1', '0', null, '0', '0', '0', '1600853694', '1600853694', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('312', '5', '8', '1', '0', null, '0', '0', '0', '1600853694', '1600853694', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('313', '5', '10', '1', '0', null, '0', '0', '0', '1600853695', '1600853695', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('314', '5', '9', '1', '0', null, '0', '0', '0', '1600853696', '1600853696', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('315', '5', '11', '1', '0', null, '0', '0', '0', '1600853696', '1600853696', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('316', '5', '12', '1', '0', null, '0', '0', '0', '1600853697', '1600853697', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('317', '5', '15', '1', '0', null, '0', '0', '0', '1600853697', '1600853697', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('318', '5', '13', '1', '0', null, '0', '0', '0', '1600853697', '1600853697', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('319', '5', '16', '1', '0', null, '0', '0', '0', '1600853697', '1600853697', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('320', '5', '14', '1', '0', null, '0', '0', '0', '1600853697', '1600853697', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('321', '5', '3', '1', '0', null, '0', '0', '0', '1600853698', '1600853698', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('322', '5', '2', '1', '0', null, '1', '1600853809', '0', '1600853698', '1600853809', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('323', '5', '4', '1', '0', null, '0', '0', '0', '1600853699', '1600853699', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('324', '5', '3', '1', '0', null, '0', '0', '0', '1600853845', '1600853845', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('325', '5', '2', '1', '0', null, '1', '1600858758', '0', '1600853846', '1600858758', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('326', '5', '3', '1', '0', null, '0', '0', '0', '1600854995', '1600854995', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('327', '5', '4', '1', '0', null, '0', '0', '0', '1600854996', '1600854996', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('328', '5', '16', '1', '0', null, '0', '0', '0', '1600854997', '1600854997', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('329', '5', '15', '1', '0', null, '0', '0', '0', '1600854997', '1600854997', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('330', '5', '13', '1', '0', null, '0', '0', '0', '1600854998', '1600854998', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('331', '5', '12', '1', '0', null, '0', '0', '0', '1600854998', '1600854998', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('332', '5', '14', '1', '0', null, '0', '0', '0', '1600854999', '1600854999', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('333', '5', '11', '1', '0', null, '0', '0', '0', '1600854999', '1600854999', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('334', '5', '10', '1', '0', null, '0', '0', '0', '1600854999', '1600854999', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('335', '5', '9', '1', '0', null, '0', '0', '0', '1600855001', '1600855001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('336', '5', '8', '1', '0', null, '0', '0', '0', '1600855001', '1600855001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('337', '5', '7', '1', '0', null, '0', '0', '0', '1600855001', '1600855001', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('338', '5', '6', '1', '0', null, '0', '0', '0', '1600855002', '1600855002', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('339', '5', '5', '1', '0', null, '0', '0', '0', '1600855002', '1600855002', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('340', '5', '1', '1', '0', null, '0', '0', '0', '1600855003', '1600855003', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('341', '5', '5', '1', '0', null, '0', '0', '0', '1600855005', '1600855005', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('342', '5', '6', '1', '0', null, '0', '0', '0', '1600855005', '1600855005', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('343', '5', '7', '1', '0', null, '1', '1600859893', '0', '1600855005', '1600859893', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('344', '5', '8', '1', '0', null, '0', '0', '0', '1600855006', '1600855006', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('345', '5', '9', '1', '0', null, '0', '0', '0', '1600855006', '1600855006', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('346', '5', '10', '1', '0', null, '0', '0', '0', '1600855007', '1600855007', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('347', '5', '11', '1', '0', null, '0', '0', '0', '1600855007', '1600855007', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('348', '5', '12', '1', '0', null, '0', '0', '0', '1600855008', '1600855008', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('349', '5', '13', '1', '0', null, '0', '0', '0', '1600855008', '1600855008', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('350', '5', '14', '1', '0', null, '0', '0', '0', '1600855009', '1600855009', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('351', '5', '15', '1', '0', null, '0', '0', '0', '1600855009', '1600855009', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('352', '5', '16', '1', '0', null, '0', '0', '0', '1600855009', '1600855009', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('353', '5', '4', '1', '0', null, '0', '0', '0', '1600855010', '1600855010', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('354', '5', '3', '1', '0', null, '0', '0', '0', '1600855011', '1600855011', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('355', '5', '3', '1', '0', null, '0', '0', '0', '1600856311', '1600856311', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('356', '5', '4', '1', '0', null, '0', '0', '0', '1600856312', '1600856312', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('357', '5', '16', '1', '0', null, '1', '1600857090', '0', '1600856313', '1600857090', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('358', '5', '3', '1', '0', null, '0', '0', '0', '1600858786', '1600858786', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('359', '5', '4', '1', '0', null, '0', '0', '0', '1600858787', '1600858787', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('360', '5', '16', '1', '0', null, '0', '0', '0', '1600858787', '1600858787', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('361', '5', '15', '1', '0', null, '0', '0', '0', '1600858788', '1600858788', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('362', '5', '14', '1', '0', null, '0', '0', '0', '1600858788', '1600858788', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('363', '5', '15', '1', '0', null, '0', '0', '0', '1600858789', '1600858789', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('364', '5', '16', '1', '0', null, '0', '0', '0', '1600858790', '1600858790', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('365', '5', '4', '1', '0', null, '0', '0', '0', '1600858790', '1600858790', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('366', '5', '3', '1', '0', null, '0', '0', '0', '1600858790', '1600858790', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('367', '5', '2', '1', '0', null, '1', '1600859248', '0', '1600858791', '1600859248', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('368', '5', '3', '1', '0', null, '0', '0', '0', '1600859296', '1600859296', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('369', '5', '3', '1', '0', null, '0', '0', '0', '1600859297', '1600859297', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('370', '5', '4', '1', '0', null, '0', '0', '0', '1600859298', '1600859298', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('371', '5', '2', '1', '0', null, '1', '1600864337', '0', '1600859298', '1600864337', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('372', '5', '3', '1', '0', null, '0', '0', '0', '1600864216', '1600864216', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('373', '5', '4', '1', '0', null, '0', '0', '0', '1600864216', '1600864216', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('374', '5', '3', '1', '0', null, '0', '0', '0', '1600864574', '1600864574', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('375', '5', '4', '1', '0', null, '0', '0', '0', '1600864575', '1600864575', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('376', '5', '3', '1', '0', null, '0', '0', '0', '1600864577', '1600864577', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('377', '5', '2', '1', '0', null, '1', '1600866087', '0', '1600864578', '1600866087', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('378', '5', '3', '1', '0', null, '0', '0', '0', '1600866105', '1600866105', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('379', '5', '4', '1', '0', null, '0', '0', '0', '1600866106', '1600866106', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('380', '5', '3', '1', '0', null, '0', '0', '0', '1600866110', '1600866110', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('381', '5', '2', '1', '0', null, '1', '1600866170', '0', '1600866111', '1600866170', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('382', '5', '3', '1', '0', null, '0', '0', '0', '1600866327', '1600866327', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('383', '5', '4', '1', '0', null, '0', '0', '0', '1600866329', '1600866329', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('384', '5', '16', '1', '0', null, '0', '0', '0', '1600866329', '1600866329', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('385', '5', '15', '1', '0', null, '0', '0', '0', '1600866330', '1600866330', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('386', '5', '16', '1', '0', null, '0', '0', '0', '1600866331', '1600866331', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('387', '5', '15', '1', '0', null, '0', '0', '0', '1600866332', '1600866332', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('388', '5', '16', '1', '0', null, '0', '0', '0', '1600866332', '1600866332', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('389', '5', '4', '1', '0', null, '0', '0', '0', '1600866333', '1600866333', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('390', '5', '3', '1', '0', null, '0', '0', '0', '1600866333', '1600866333', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('391', '5', '2', '1', '0', null, '1', '1600866751', '0', '1600866334', '1600866751', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('392', '5', '7', '1', '0', null, '0', '0', '0', '1600867532', '1600867532', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('393', '5', '4', '1', '0', null, '0', '0', '0', '1600867534', '1600867534', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('394', '5', '7', '1', '0', null, '0', '0', '0', '1600867538', '1600867538', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('395', '5', '4', '1', '0', null, '1', '1600867637', '0', '1600867539', '1600867637', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('396', '5', '3', '1', '0', null, '0', '0', '0', '1600867657', '1600867657', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('397', '5', '4', '1', '0', null, '0', '0', '0', '1600867658', '1600867658', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('398', '5', '16', '1', '0', null, '0', '0', '0', '1600867659', '1600867659', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('399', '5', '15', '1', '0', null, '0', '0', '0', '1600867660', '1600867660', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('400', '5', '16', '1', '0', null, '0', '0', '0', '1600867663', '1600867663', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('401', '5', '3', '1', '0', null, '0', '0', '0', '1600867664', '1600867664', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('402', '5', '4', '1', '0', null, '0', '0', '0', '1600867664', '1600867664', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('403', '5', '2', '1', '0', null, '0', '0', '0', '1600867665', '1600867665', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('404', '5', '4', '1', '0', null, '0', '0', '0', '1600867667', '1600867667', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('405', '5', '3', '1', '0', null, '0', '0', '0', '1600867667', '1600867667', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('406', '5', '16', '1', '0', null, '0', '0', '0', '1600867667', '1600867667', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('407', '5', '15', '1', '0', null, '0', '0', '0', '1600867668', '1600867668', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('408', '5', '16', '1', '0', null, '0', '0', '0', '1600867670', '1600867670', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('409', '5', '15', '1', '0', null, '0', '0', '0', '1600867672', '1600867672', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('410', '5', '16', '1', '0', null, '0', '0', '0', '1600867672', '1600867672', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('411', '5', '15', '1', '0', null, '0', '0', '0', '1600867673', '1600867673', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('412', '5', '15', '1', '0', null, '1', '1600867821', '0', '1600867709', '1600867821', '0');
INSERT INTO `jxmall_plugin_video_look` VALUES ('413', '5', '4', '1', '0', null, '0', '0', '0', '1600867729', '1600867729', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_look_award
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_look_award`;
CREATE TABLE `jxmall_plugin_video_look_award` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `source_user_id` int(11) DEFAULT NULL COMMENT '源头id',
  `user_type` tinyint(4) DEFAULT '0' COMMENT '用户类型：0：本人 1：一级 2：二级',
  `award_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖励类型 0：积分 1：现金',
  `award_integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励积分',
  `award_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `award_type` (`award_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COMMENT='观看视频奖励';

-- ----------------------------
-- Records of jxmall_plugin_video_look_award
-- ----------------------------
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('29', '5', '3', '2', null, '0', '0', '5.00', '0.00', '0', '1600740557', '1600740557', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('30', '5', '3', '4', '2', '1', '0', '3.00', '0.00', '0', '1600740557', '1600740557', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('31', '5', '3', '2', null, '0', '1', '0.00', '2.50', '0', '1600740557', '1600740557', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('32', '5', '3', '4', '2', '1', '1', '0.00', '1.50', '0', '1600740557', '1600740557', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('33', '5', '15', '2', null, '0', '0', '5.00', '0.00', '0', '1600742747', '1600742747', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('34', '5', '15', '4', '2', '1', '0', '3.00', '0.00', '0', '1600742747', '1600742747', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('35', '5', '15', '2', null, '0', '1', '0.00', '2.50', '0', '1600742747', '1600742747', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('36', '5', '15', '4', '2', '1', '1', '0.00', '1.50', '0', '1600742747', '1600742747', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('37', '5', '16', '2', null, '0', '0', '5.00', '0.00', '0', '1600742968', '1600742968', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('38', '5', '16', '4', '2', '1', '0', '3.00', '0.00', '0', '1600742969', '1600742969', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('39', '5', '16', '2', null, '0', '1', '0.00', '2.50', '0', '1600742969', '1600742969', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('40', '5', '16', '4', '2', '1', '1', '0.00', '1.50', '0', '1600742969', '1600742969', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('41', '5', '9', '2', null, '0', '0', '0.26', '0.00', '0', '1600745768', '1600745768', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('42', '5', '9', '4', '2', '1', '0', '0.24', '0.00', '0', '1600745768', '1600745768', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('43', '5', '9', '2', null, '0', '1', '0.00', '2.50', '0', '1600745768', '1600745768', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('44', '5', '9', '4', '2', '1', '1', '0.00', '1.50', '0', '1600745768', '1600745768', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('45', '5', '4', '2', null, '0', '0', '5.00', '0.00', '0', '1600760879', '1600760879', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('46', '5', '4', '4', '2', '1', '0', '3.00', '0.00', '0', '1600760879', '1600760879', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('47', '5', '4', '2', null, '0', '1', '0.00', '2.50', '0', '1600760879', '1600760879', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('48', '5', '4', '4', '2', '1', '1', '0.00', '1.50', '0', '1600760879', '1600760879', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('49', '5', '3', '1', null, '0', '0', '5.00', '0.00', '0', '1600763099', '1600763099', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('50', '5', '3', '1', null, '0', '1', '0.00', '2.50', '0', '1600763099', '1600763099', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('51', '5', '16', '1', null, '0', '0', '5.00', '0.00', '0', '1600763848', '1600763848', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('52', '5', '16', '1', null, '0', '1', '0.00', '2.50', '0', '1600763848', '1600763848', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('53', '5', '2', '1', null, '0', '0', '5.00', '0.00', '0', '1600764001', '1600764001', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('54', '5', '2', '1', null, '0', '1', '0.00', '2.50', '0', '1600764001', '1600764001', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('55', '5', '9', '1', null, '0', '0', '0.26', '0.00', '0', '1600764653', '1600764653', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('56', '5', '9', '1', null, '0', '1', '0.00', '2.50', '0', '1600764653', '1600764653', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('57', '5', '7', '1', null, '0', '0', '5.00', '0.00', '0', '1600852785', '1600852785', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('58', '5', '7', '1', null, '0', '1', '0.00', '2.50', '0', '1600852785', '1600852785', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('59', '5', '4', '1', null, '0', '0', '5.00', '0.00', '0', '1600867637', '1600867637', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('60', '5', '4', '1', null, '0', '1', '0.00', '2.50', '0', '1600867637', '1600867637', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('61', '5', '15', '1', null, '0', '0', '5.00', '0.00', '0', '1600867821', '1600867821', '0');
INSERT INTO `jxmall_plugin_video_look_award` VALUES ('62', '5', '15', '1', null, '0', '1', '0.00', '2.50', '0', '1600867821', '1600867821', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_look_num
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_look_num`;
CREATE TABLE `jxmall_plugin_video_look_num` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `look_num` int(11) unsigned DEFAULT '1' COMMENT '观看次数',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='用户观看视频次数';

-- ----------------------------
-- Records of jxmall_plugin_video_look_num
-- ----------------------------
INSERT INTO `jxmall_plugin_video_look_num` VALUES ('5', '5', '2', '104', '0', '1600740484', '1600779223', '0');
INSERT INTO `jxmall_plugin_video_look_num` VALUES ('6', '5', '1', '373', '0', '1600761732', '1600867798', '0');
INSERT INTO `jxmall_plugin_video_look_num` VALUES ('8', '5', '5', '13', '0', '1600845441', '1600864633', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_order_award
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_order_award`;
CREATE TABLE `jxmall_plugin_video_order_award` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL COMMENT '子订单id',
  `video_id` int(11) NOT NULL,
  `money` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '奖励金额',
  `award_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '奖励类型 0：金额 1：百分比',
  `award_percentage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励百分比',
  `award_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `is_settlement` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否结算 0：否 1：是',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `is_settlement` (`is_settlement`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COMMENT='短视频带货订单奖励';

-- ----------------------------
-- Records of jxmall_plugin_video_order_award
-- ----------------------------
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('1', '5', '1', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('2', '5', '2', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('3', '5', '3', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('4', '5', '4', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('5', '5', '5', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('6', '5', '6', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('7', '5', '7', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('8', '5', '8', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('9', '5', '9', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('10', '5', '10', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('11', '5', '11', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('12', '5', '12', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('13', '5', '13', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('14', '5', '14', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('15', '5', '15', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');
INSERT INTO `jxmall_plugin_video_order_award` VALUES ('16', '5', '16', '2', '0', '0', '0.00', '0.00', '1', '0', '1600757916', '1600757916', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_related_goods
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_related_goods`;
CREATE TABLE `jxmall_plugin_video_related_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='关联商品列表';

-- ----------------------------
-- Records of jxmall_plugin_video_related_goods
-- ----------------------------
INSERT INTO `jxmall_plugin_video_related_goods` VALUES ('1', '5', '1', '16', '0', '0', '0', '0');
INSERT INTO `jxmall_plugin_video_related_goods` VALUES ('2', '5', '1', '7', '1599615865', '0', '1599615865', '0');

-- ----------------------------
-- Table structure for jxmall_plugin_video_share
-- ----------------------------
DROP TABLE IF EXISTS `jxmall_plugin_video_share`;
CREATE TABLE `jxmall_plugin_video_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='视频分享记录';

-- ----------------------------
-- Records of jxmall_plugin_video_share
-- ----------------------------
INSERT INTO `jxmall_plugin_video_share` VALUES ('1', '5', '2', '1', '0', '1600079070', '1600079070', '0');
INSERT INTO `jxmall_plugin_video_share` VALUES ('2', '5', '1', '2', '0', '1600844630', '1600844630', '0');





-- 修改订单
ALTER TABLE `jxmall_plugin_video_order_award`
CHANGE COLUMN `video_id` `user_id`  int(11) NOT NULL COMMENT '用户id' AFTER `order_id`,
ADD COLUMN `goods_id`  int NOT NULL COMMENT '商品id' AFTER `user_id`;


-- 结算时间
ALTER TABLE `jxmall_plugin_video_order_award`
ADD COLUMN `settlement_time`  time NULL DEFAULT 0 COMMENT '结算时间' AFTER `is_settlement`;

-- 2020.10.7 视频剩余金额
ALTER TABLE `jxmall_plugin_video`
MODIFY COLUMN `surplus_money`  decimal(10,2) UNSIGNED NULL DEFAULT 0 COMMENT '剩余金额' AFTER `max_money`,
ADD COLUMN `spend_integral`  decimal(10,2) NULL AFTER `surplus_integral`,
ADD COLUMN `spend_money`  decimal(10,2) NULL DEFAULT 0 COMMENT '花费金额' AFTER `surplus_money`;

--2020.10.8备注
ALTER TABLE `jxmall_plugin_video`
MODIFY COLUMN `surplus_integral`  decimal(10,2) UNSIGNED NULL DEFAULT 0 COMMENT '剩余的积分' AFTER `max_integral`,
MODIFY COLUMN `spend_integral`  decimal(10,2) NULL DEFAULT 0.00 COMMENT '花费积分' AFTER `surplus_integral`,
MODIFY COLUMN `watch_money_sum`  decimal(10,2) NULL DEFAULT NULL COMMENT '观看现金奖励总' AFTER `watch_integral_two`,

-- 订单分红表加上视频id
ALTER TABLE `jxmall_plugin_video_order_award`
ADD COLUMN `video_id`  int NOT NULL COMMENT '视频id' AFTER `mall_id`;



-- 2020.10.10 视频参数
ALTER TABLE `jxmall_plugin_video`
ADD COLUMN `video_wide`  int(11) NOT NULL DEFAULT 0 COMMENT '视频宽度' AFTER `deleted_at`,
ADD COLUMN `video_long`  int(11) NOT NULL DEFAULT 0 COMMENT '视频长度' AFTER `video_wide`,
ADD COLUMN `related_address`  varchar(255) NULL COMMENT '关联地址' AFTER `video_long`,
ADD COLUMN `related_detailed_address`  varchar(255) NULL COMMENT '关联详细地址' AFTER `related_address`;


-- 2020.10.12 文本审核
ALTER TABLE `jxmall_plugin_video_config`
ADD COLUMN `client_status`  tinyint NULL DEFAULT 0 COMMENT '是否开启文本审核 0：否 1：是' AFTER `deleted_at`,
ADD COLUMN `client_id`  varchar(255) NULL COMMENT '百度文本审核API Key' AFTER `client_status`,
ADD COLUMN `client_secret`  varchar(255) NULL COMMENT '百度文本审核Secret Key' AFTER `client_id`;


--2020.10.13
ALTER TABLE `jxmall_plugin_video_order_award`
MODIFY COLUMN `settlement_time`  int NULL DEFAULT NULL COMMENT '结算时间' AFTER `is_settlement`;

ALTER TABLE `jxmall_plugin_video`
MODIFY COLUMN `related_type`  tinyint(4) NOT NULL DEFAULT 4 COMMENT '关联类型 1：关联商品 2：关联店铺 3：关联地址4：暂无关联 ' AFTER `video_url`;
