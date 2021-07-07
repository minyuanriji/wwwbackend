-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2020-09-23 16:48:13
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `jxmall_test`
--

-- --------------------------------------------------------

--
-- 表的结构 `jxmall_plugin_group_buy_active`
--

CREATE TABLE `jxmall_plugin_group_buy_active` (
  `id` int(11) NOT NULL COMMENT '开团id',
  `goods_id` int(11) NOT NULL COMMENT '商品id;goods.id',
  `group_buy_id` int(11) NOT NULL COMMENT '拼团商品活动id;group_buy_goods.id',
  `people` int(11) NOT NULL COMMENT '成团人数;',
  `virtual_people` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟成团人数;',
  `actual_people` int(11) NOT NULL DEFAULT '0' COMMENT '当前已拼人数;',
  `cancel_people` int(11) NOT NULL COMMENT '取消订单人数',
  `creator_id` int(11) NOT NULL COMMENT '团长user_id;user_id.id;',
  `start_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `end_at` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '拼单状态:0未拼单; 1拼单中; 2拼单成功; 3拼单失败;',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_virtual` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否开启虚拟成团',
  `mall_id` int(11) NOT NULL COMMENT '商城id',
  `is_manual` int(11) NOT NULL COMMENT '是否手动结束'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团开团表';

-- --------------------------------------------------------

--
-- 表的结构 `jxmall_plugin_group_buy_active_item`
--

CREATE TABLE `jxmall_plugin_group_buy_active_item` (
  `id` int(11) NOT NULL,
  `active_id` int(11) NOT NULL COMMENT '拼单id;avtive.id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `order_id` int(11) NOT NULL COMMENT '拼团订单id;order.id;',
  `is_creator` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否团长',
  `attr_id` int(11) NOT NULL COMMENT '商品规格id',
  `group_buy_price` decimal(10,2) NOT NULL COMMENT '拼团商品价格',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `mall_id` int(11) NOT NULL COMMENT '商城id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团订单表';

-- --------------------------------------------------------

--
-- 表的结构 `jxmall_plugin_group_buy_goods`
--

CREATE TABLE `jxmall_plugin_group_buy_goods` (
  `id` int(11) NOT NULL COMMENT '拼团商品活动id',
  `mall_id` int(11) NOT NULL COMMENT '商城id',
  `goods_id` int(11) NOT NULL COMMENT '商品id;对应goods主表',
  `start_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '拼团开始时间',
  `vaild_time` int(11) NOT NULL COMMENT '拼团有效时间(分钟)',
  `people` int(11) NOT NULL COMMENT '成团人数',
  `virtual_people` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟成团人数',
  `is_virtual` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否开启虚拟成团',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '拼团商品状态;0未开始;1开团中;已结束2',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `update_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团商品表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `jxmall_plugin_group_buy_goods_attr`
--

CREATE TABLE `jxmall_plugin_group_buy_goods_attr` (
  `id` int(11) UNSIGNED NOT NULL,
  `attr_id` int(11) NOT NULL COMMENT '商品规格id;对应goods_attr.id;',
  `group_buy_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品规格表' ROW_FORMAT=DYNAMIC;

--
-- 转储表的索引
--

--
-- 表的索引 `jxmall_plugin_group_buy_active`
--
ALTER TABLE `jxmall_plugin_group_buy_active`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `jxmall_plugin_group_buy_active_item`
--
ALTER TABLE `jxmall_plugin_group_buy_active_item`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `jxmall_plugin_group_buy_goods`
--
ALTER TABLE `jxmall_plugin_group_buy_goods`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `mall_id` (`mall_id`) USING BTREE,
  ADD KEY `index1` (`mall_id`) USING BTREE,
  ADD KEY `index2` (`mall_id`) USING BTREE;

--
-- 表的索引 `jxmall_plugin_group_buy_goods_attr`
--
ALTER TABLE `jxmall_plugin_group_buy_goods_attr`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `jxmall_plugin_group_buy_active`
--
ALTER TABLE `jxmall_plugin_group_buy_active`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '开团id', AUTO_INCREMENT=98;

--
-- 使用表AUTO_INCREMENT `jxmall_plugin_group_buy_active_item`
--
ALTER TABLE `jxmall_plugin_group_buy_active_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- 使用表AUTO_INCREMENT `jxmall_plugin_group_buy_goods`
--
ALTER TABLE `jxmall_plugin_group_buy_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '拼团商品活动id', AUTO_INCREMENT=106;

--
-- 使用表AUTO_INCREMENT `jxmall_plugin_group_buy_goods_attr`
--
ALTER TABLE `jxmall_plugin_group_buy_goods_attr`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
