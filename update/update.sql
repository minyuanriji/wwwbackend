--
-- 数据库更新sql
-- create_time 2020-04-13
--

-- jxmall_attachment_info表,新增字段如下
-- author：zal
-- time：2020-04-13 14:05
ALTER TABLE jxmall_attachment_info ADD COLUMN `is_recycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否加入回收站 0.否|1.是' AFTER `is_delete`;

-- jxmall_user表,新增字段如下 author：zal  time：2020-04-27 11:05
ALTER TABLE jxmall_user ADD COLUMN `login_ip` char(15) NOT NULL DEFAULT '0' COMMENT '用户登录ip' AFTER `last_login_at`;

-- jxmall_validate_code_log表,修改字段如下 author：zal  time：2020-04-27 17:05
ALTER TABLE jxmall_validate_code_log CHANGE updated_at `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间';

-- 新增 jxmall_postage_rules表 author：zal  time：2020-04-29 15:05
DROP TABLE IF EXISTS `jxmall_postage_rules`;
CREATE TABLE `jxmall_postage_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(65) NOT NULL DEFAULT '',
  `detail` longtext NOT NULL COMMENT '规则详情',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '计费方式【1=>按重计费、2=>按件计费】',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp(0) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='运费规则';

-- jxmall_user_address表,修改字段如下 author：zal  time：2020-05-04 17:05
alter table jxmall_user_address modify column `deleted_at` int(11);

-- 新增 jxmall_exception_log表 author：zal  time：2020-05-05 17:05
DROP TABLE IF EXISTS `jxmall_exception_log`;
CREATE TABLE `jxmall_exception_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '异常等级1.报错|2.警告|3.记录信息',
  `title` mediumtext NOT NULL COMMENT '异常标题',
  `content` mediumtext NOT NULL COMMENT '异常内容',
  `created_at` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8mb4 COMMENT='异常日志表';

ALTER TABLE `jxmall_validate_code_log` CHANGE `updated_at` `updated_at` INT NOT NULL DEFAULT '0';

-- jxmall_user表,新增字段如下 author：zal  time：2020-05-07 14:05
ALTER TABLE jxmall_user
ADD COLUMN `transaction_password` varchar(128) NOT NULL DEFAULT '' COMMENT '交易密码' AFTER `password`,
ADD COLUMN `birthday` int(11) NOT NULL DEFAULT '0' COMMENT '生日' AFTER `nickname`;

-- jxmall_validate_code_log表,新增字段如下 author：zal  time：2020-05-07 20:05
ALTER TABLE jxmall_validate_code_log
ADD COLUMN `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '' ,
ADD COLUMN `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '' ,
ADD COLUMN `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '';

--
-- 表的结构 `jxmall_relation_setting`
--

DROP TABLE IF EXISTS `jxmall_relation_setting`;
CREATE TABLE IF NOT EXISTS `jxmall_relation_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `use_relation` int(11) DEFAULT '0' COMMENT '是否启用关系链',
  `get_power_way` int(11) DEFAULT '0' COMMENT '1无条件、2、申请 3、或 4、与',
  `buy_num_selected` int(11) NOT NULL DEFAULT '0' COMMENT '消费次数达',
  `buy_num` int(11) NOT NULL DEFAULT '0' COMMENT '消费次数',
  `buy_price_selected` int(11) NOT NULL DEFAULT '0' COMMENT '消费金额达',
  `buy_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '消费金额',
  `buy_goods_selected` int(11) NOT NULL DEFAULT '0' COMMENT '购买商品',
  `buy_goods_way` int(11) NOT NULL DEFAULT '0' COMMENT '1 任意商品  2 指定商品  3 指定分类',
  `goods_ids` varchar(255) DEFAULT NULL COMMENT '指定商品的goods_warehouse_id',
  `cat_ids` varchar(255) DEFAULT NULL COMMENT '指定分类',
  `buy_compute_way` int(11) DEFAULT '0' COMMENT '1、付款后 2完成后',
  `become_child_way` int(11) DEFAULT '0' COMMENT '1、首次点击分享链接 2、首次下单 3、首次付款',
  `protocol` longtext COMMENT '申请协议',
  `notice` longtext COMMENT '用户须知',
  `status_pic_url` varchar(255) DEFAULT NULL COMMENT '审核状态图片',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `cat_list` varchar(255) DEFAULT NULL COMMENT '分类列表',
  `goods_list` mediumtext COMMENT '商品列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户关系表';
COMMIT;

-- jxmall_user表,新增字段如下 author：zal  time：2020-05-08 12:05
ALTER TABLE jxmall_attachment_info
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT '1' COMMENT '来源1后台2前台' AFTER `size`;

--
-- 表的结构 `jxmall_plugin_distribution`
--

DROP TABLE IF EXISTS `jxmall_plugin_distribution`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_distribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `remarks` longtext COMMENT '备注',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `total_childs` int(11) NOT NULL DEFAULT '0' COMMENT '所有下级数量',
  `total_order` int(11) NOT NULL DEFAULT '0' COMMENT '分销订单数量',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '分销商等级',
  `upgrade_level_at` int(11) NOT NULL DEFAULT '0' COMMENT '分销商等级升级时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销商信息' ROW_FORMAT=DYNAMIC;
COMMIT;

-- 2020-05-11 11:04
-- 表的结构 `jxmall_plugin_distribution_level`
--

DROP TABLE IF EXISTS `jxmall_plugin_distribution_level`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_distribution_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '-1',
  `name` varchar(45) NOT NULL,
  `is_auto_upgrade` int(11) NOT NULL DEFAULT '0',
  `price_type` int(11) NOT NULL DEFAULT '0' COMMENT '佣金类型 1百分比 2 固定金额',
  `detail` longtext,
  `first_price` decimal(10,2) NOT NULL,
  `second_price` decimal(10,2) NOT NULL,
  `third_price` decimal(10,2) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(11) NOT NULL DEFAULT '0',
  `checked_condition_values` longtext COMMENT '升级条件',
  `is_use` smallint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `checked_condition_keys` text COMMENT '升级条件选中的key集合',
  `condition_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '0 未选择、1满足其一  2、满足所有',
  `upgrade_type_goods` smallint(6) NOT NULL DEFAULT '0',
  `upgrade_type_condition` smallint(6) NOT NULL DEFAULT '0',
  `goods_warehouse_ids` mediumint(9) DEFAULT NULL COMMENT '商品仓库的ID',
  `goods_list` longtext COMMENT '商品列表',
  `goods_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '商品升级类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销商等级';
COMMIT;


--
-- 表的结构 `jxmall_plugin_distribution_setting`
--

DROP TABLE IF EXISTS `jxmall_plugin_distribution_setting`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_distribution_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除 0--未删除 1--已删除',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销设置' ROW_FORMAT=DYNAMIC;
COMMIT;

ALTER TABLE `jxmall_plugin_distribution` ADD `total_price` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT '累计佣金' AFTER `upgrade_level_at`;

--
-- 表的结构 `jxmall_user_children`
--

DROP TABLE IF EXISTS `jxmall_user_children`;
CREATE TABLE IF NOT EXISTS `jxmall_user_children` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT '层级',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户下级表';
COMMIT;



--
-- 表的结构 `jxmall_user_parent`
--

DROP TABLE IF EXISTS `jxmall_user_parent`;
CREATE TABLE IF NOT EXISTS `jxmall_user_parent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL COMMENT '层级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户父级表';
COMMIT;

ALTER TABLE `jxmall_user` ADD `is_inviter` SMALLINT NOT NULL DEFAULT '0' COMMENT '是否是邀请者' AFTER `login_ip`;


DROP TABLE IF EXISTS `jxmall_goods_footmark`;
CREATE TABLE IF NOT EXISTS `jxmall_goods_footmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户商品足迹';
COMMIT;

ALTER TABLE `jxmall_plugin_diy_page` ADD `active_color` VARCHAR(10) NULL DEFAULT NULL COMMENT '选中颜色' AFTER `background`;
ALTER TABLE `jxmall_user` CHANGE `total_money` `total_balance` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计余额';

-- jxmall_order_refund表,新增字段如下 author：zal  time：2020-05-13 15:05
ALTER TABLE jxmall_order_refund
ADD COLUMN `is_receipt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否收到货' AFTER `remark`,
ADD COLUMN `refund_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退货方式：0--快递配送 1--到店自提 2--同城配送' AFTER `status_at`,
ADD COLUMN `reason` varchar(45) NOT NULL DEFAULT '' COMMENT '退款原因' AFTER `refund_price`;

DROP TABLE IF EXISTS `jxmall_cash_log`;
CREATE TABLE IF NOT EXISTS `jxmall_cash_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '类型 1--收入 2--支出',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变动佣金',
  `desc` longtext,
  `custom_desc` longtext,
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='提现记录';
COMMIT;

ALTER TABLE `jxmall_plugin_diy_page_nav` ADD `is_delete` SMALLINT NOT NULL DEFAULT '0' AFTER `nav_pic_active`;
ALTER TABLE `jxmall_plugin_diy_page_nav` ADD `created_at` INT NOT NULL DEFAULT '0' AFTER `is_delete`, ADD `deleted_at` INT NOT NULL DEFAULT '0' AFTER `created_at`, ADD `updated_at` INT NOT NULL DEFAULT '0' AFTER `deleted_at`;

DROP TABLE IF EXISTS `jxmall_common_order`;
CREATE TABLE IF NOT EXISTS `jxmall_common_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `status` smallint(6) NOT NULL DEFAULT '0' COMMENT '对应Order的status',
  `order_type` varchar(11) NOT NULL COMMENT '订单类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共订单表';
COMMIT;


--
-- 表的结构 `jxmall_common_order_detail`
--

DROP TABLE IF EXISTS `jxmall_common_order_detail`;
CREATE TABLE IF NOT EXISTS `jxmall_common_order_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '该笔支付金额',
  `num` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `common_order_id` int(11) NOT NULL COMMENT '公共订单ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共订单表';
COMMIT;

-- jxmall_user表,新增字段如下 author：zal  time：2020-05-13 15:05
ALTER TABLE jxmall_user MODIFY COLUMN avatar_url  varchar(255) NOT NULL DEFAULT '' COMMENT '头像';


--
-- 表的结构 `jxmall_plugin_distribution_goods`
--

DROP TABLE IF EXISTS `jxmall_plugin_distribution_goods_detail`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_distribution_goods_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commission_first` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '一级分销佣金比例',
  `commission_second` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '二级分销佣金比例',
  `commission_third` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '三级分销佣金比例',
  `goods_id` int(11) NOT NULL,
  `goods_attr_id` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '分销商等级',
  `goods_type` varchar(11) NOT NULL DEFAULT 'MALL_GOODS' COMMENT '商品类型 MALL_GOODS、',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `goods_attr_id` (`goods_attr_id`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='商品分销详情表' ROW_FORMAT=DYNAMIC;


--
-- 表的结构 `jxmall_plugin_distribution_goods`
--

DROP TABLE IF EXISTS `jxmall_plugin_distribution_goods`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_distribution_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `goods_type` varchar(11) DEFAULT 'MALL_GOODS',
  `attr_setting_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '按规格设置',
  `share_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '佣金类型 0，固定金额，1百分比',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分销商品设置表';
COMMIT;



ALTER TABLE `jxmall_plugin_distribution_goods` ADD `is_alone` SMALLINT NULL DEFAULT '0' COMMENT '独立设置' ;

-- jxmall_balance_log表,新增字段如下 author：zal  time：2020-05-20 11:05
ALTER TABLE jxmall_balance_log
ADD COLUMN `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当前余额' AFTER `money`;

-- jxmall_score_log表,新增字段如下 author：zal  time：2020-05-20 11:05
ALTER TABLE jxmall_score_log
ADD COLUMN `current_score` int(11) NOT NULL DEFAULT '0' COMMENT '当前积分' AFTER `score`;


DROP TABLE IF EXISTS `jxmall_parent_log`;
CREATE TABLE IF NOT EXISTS `jxmall_parent_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `before_parent_id` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户上级变更记录';
COMMIT;


ALTER TABLE `jxmall_parent_log` ADD `after_parent_id` INT NOT NULL DEFAULT '0' COMMENT '变更之后的父级' AFTER `is_delete`;
ALTER TABLE `jxmall_parent_log` CHANGE `is_delete` `is_delete` INT(11) NOT NULL DEFAULT '0';



--
-- 表的结构 `jxmall_user_growth`
--

DROP TABLE IF EXISTS `jxmall_user_growth`;
CREATE TABLE IF NOT EXISTS `jxmall_user_growth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `keyword` varchar(45) NOT NULL COMMENT '关键字',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '数值',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户成长记录';
COMMIT;


-- jxmall_score_log表,新增字段如下 author：zal  time：2020-05-21 14:05
ALTER TABLE jxmall_core_action_log
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT '1' COMMENT '来源1前台2后台' AFTER `admin_id`;

-- jxmall_validate_code_log表,修改字段如下 author：zal  time：2020-05-21 15:05
ALTER TABLE jxmall_core_action_log CHANGE admin_id `operator` int(11) NOT NULL DEFAULT '0' COMMENT '操作人';

-- 删除jxmall_mall_goods表的 is_quick_buy 字段 author：zal  time：2020-05-21 18:05
ALTER TABLE jxmall_mall_goods DROP COLUMN is_quick_buy;



ALTER TABLE `jxmall_plugin_distribution` ADD `upgrade_status` SMALLINT NOT NULL DEFAULT '0' COMMENT '1条件升级 2 购买指定商品升级 3手动升级' ;

-- 新增jxmall_user_info 表 author：zal  time：2020-05-22 10:05
DROP TABLE IF EXISTS `jxmall_user_info`;
CREATE TABLE `jxmall_user_info` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`mall_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城ID',
`mch_id` int(11) NOT NULL DEFAULT '0' COMMENT '商户ID',
`user_id` int(11) NOT NULL,
`openid` varchar(64) NOT NULL DEFAULT '' COMMENT 'openid',
`unionid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户所属平台的唯一标识',
`platform` varchar(45) NOT NULL DEFAULT '' COMMENT '平台名字：WXAPP、WECHAT、MP-WX',
`platform_data` varchar(3000) NOT NULL DEFAULT '' COMMENT '平台数据，json格式',
`remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
`is_delete` tinyint(1) NOT NULL DEFAULT '0',
`created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
`updated_at` varchar(255) NOT NULL DEFAULT '' COMMENT '更新时间',
`deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户额外信息';

-- 删除jxmall_user_info表的 is_quick_buy 字段 author：zal  time：2020-05-22 10:05
ALTER TABLE jxmall_user DROP COLUMN unionid;
ALTER TABLE jxmall_user DROP COLUMN openid;

drop table jxmall_favorite;

--
-- 表的结构 `jxmall_home_page`
--

DROP TABLE IF EXISTS `jxmall_home_page`;
CREATE TABLE IF NOT EXISTS `jxmall_home_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `page_data` longtext,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='首页数据';
COMMIT;

--
-- 表的结构 `jxmall_plugin_mpwx_config`
--

DROP TABLE IF EXISTS `jxmall_plugin_mpwx_config`;
CREATE TABLE IF NOT EXISTS `jxmall_plugin_mpwx_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `app_id` varchar(64) NOT NULL COMMENT 'appid',
  `secret` varchar(64) NOT NULL COMMENT '密钥',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `cert_pem` text,
  `key_pem` text,
  `mch_id` int(11) NOT NULL DEFAULT '0',
  `pay_secret` varchar(255) DEFAULT NULL COMMENT '支付密钥',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `cert_pem_path` varchar(255) DEFAULT NULL COMMENT 'cert_pem路径',
  `key_pem_path` varchar(255) DEFAULT NULL COMMENT 'key_pem路径',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信小程序' ROW_FORMAT=DYNAMIC;
COMMIT;


drop table jxmall_favorite;

-- jxmall_balance_log 表,新增字段如下 author：zal  time：2020-05-25 14:05
ALTER TABLE jxmall_balance_log
    ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT '0' COMMENT '余额来源1后台2商城订单3分佣' AFTER `type`;

-- 新增jxmall_commission_log表 author：zal  time：2020-05-25 14:45
DROP TABLE IF EXISTS `jxmall_plugin_distribution_commission_log`;
CREATE TABLE `jxmall_plugin_distribution_commission_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `mall_id` int(11) NOT NULL,
 `user_id` int(11) NOT NULL,
 `order_id` int(11) NOT NULL,
 `distribution_order_id` int(11) NOT NULL DEFAULT '0' COMMENT '对应的分销订单id',
 `type` tinyint(1) NOT NULL default '1' COMMENT '类型：1=收入，2=支出',
 `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '佣金等级1一级分佣2二级分佣3三级分佣',
 `money` decimal(10,2) NOT NULL COMMENT '变动金额',
 `commission` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '当前佣金',
 `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '佣金状态0冻结1已结算',
 `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '变动说明',
 `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
 PRIMARY KEY (`id`) USING BTREE,
 KEY `mall_id` (`mall_id`) USING BTREE,
 KEY `user_id` (`user_id`) USING BTREE,
 KEY `order_id` (`order_id`) USING BTREE,
 KEY `type` (`type`) USING BTREE,
 KEY `level` (`level`) USING BTREE,
 KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='分佣明细日志表';

-- 新增 jxmall_plugin_distribution_order 表 author：zal  time：2020-05-25 17:45
DROP TABLE IF EXISTS `jxmall_plugin_distribution_order`;
CREATE TABLE `jxmall_plugin_distribution_order` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `mall_id` int(11) NOT NULL,
 `order_id` int(11) NOT NULL,
 `order_detail_id` int(11) NOT NULL,
 `user_id` int(11) NOT NULL COMMENT '购物者用户id',
 `first_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上一级用户id',
 `second_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上二级用户id',
 `third_parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '上三级用户id',
 `first_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上一级分销佣金',
 `second_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上二级分销佣金',
 `third_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '上三级分销佣金',
 `is_refund` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未退款 1退款',
 `is_transfer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '佣金发放状态：0=未发放，1=已发放',
 `is_delete` tinyint(1) NOT NULL DEFAULT '0',
 `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
 `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
 `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
 PRIMARY KEY (`id`) USING BTREE,
 KEY `mall_id` (`mall_id`) USING BTREE,
 KEY `order_id` (`order_id`) USING BTREE,
 KEY `order_detail_id` (`order_detail_id`) USING BTREE,
 KEY `user_id` (`user_id`) USING BTREE,
 KEY `first_parent_id` (`first_parent_id`) USING BTREE,
 KEY `second_parent_id` (`second_parent_id`) USING BTREE,
 KEY `third_parent_id` (`third_parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='分销订单';

-- jxmall_user 表,新增字段如下 author：zal  time：2020-05-26 11:05
ALTER TABLE jxmall_user
    ADD COLUMN `commission_frozen` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '冻结分佣金额' AFTER `commission`;

-- jxmall_plugin_distribution 表,新增字段如下 author：zal  time：2020-05-26 11:05
ALTER TABLE jxmall_plugin_distribution
    ADD COLUMN `commission_frozen` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '冻结分佣金额' AFTER `total_price`,
    ADD COLUMN `commission_usable` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '可用分佣金额' AFTER `total_price`;

-- jxmall_user 表,新增字段如下 author：zal  time：2020-05-26 11:05
ALTER TABLE `jxmall_user`
    CHANGE `total_commission` `total_income` decimal(10,2) COMMENT '总收益' NOT NULL DEFAULT '0',
    CHANGE `commission_frozen` `income_frozen` decimal(10,2) COMMENT '冻结收益' NOT NULL DEFAULT '0',
    CHANGE `commission` `income` decimal(10,2) COMMENT '已收益' NOT NULL DEFAULT '0';

-- jxmall_income_log 表,新增字段如下 author：zal  time：2020-05-28 17:05
DROP TABLE IF EXISTS `jxmall_income_log`;
CREATE TABLE `jxmall_income_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '类型：1=收入，2=支出',
  `money` decimal(10,2) NOT NULL COMMENT '变动金额',
  `income` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '变动说明',
  `flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0冻结1结算',
  `from` tinyint(1) NOT NULL DEFAULT '1' COMMENT '来源1分销',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='收益变动日志表';


ALTER TABLE `jxmall_cash` CHANGE `type` `type` VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额';



--
-- 表的结构 `jxmall_cash`
--

DROP TABLE IF EXISTS `jxmall_cash`;
CREATE TABLE IF NOT EXISTS `jxmall_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_no` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现金额',
  `service_fee_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现手续费（%）',
  `type` varchar(45) NOT NULL DEFAULT '' COMMENT '提现方式 auto--自动打款 wechat--微信打款 alipay--支付宝打款 bank--银行转账 balance--打款到余额',
  `extra` longtext COMMENT '额外信息 例如微信账号、支付宝账号等',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '提现状态 0--申请 1--同意 2--已打款 3--驳回',
  `is_delete` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `content` longtext,
  `fact_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际到账',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='提现记录表' ROW_FORMAT=DYNAMIC;
COMMIT;



--
-- 表的结构 `jxmall_relation_setting`
--

DROP TABLE IF EXISTS `jxmall_relation_setting`;
CREATE TABLE IF NOT EXISTS `jxmall_relation_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `use_relation` int(11) DEFAULT '0' COMMENT '是否启用关系链',
  `get_power_way` int(11) DEFAULT '0' COMMENT '1无条件、2、申请 3、或 4、与',
  `buy_num_selected` int(11) NOT NULL DEFAULT '0' COMMENT '消费次数达',
  `buy_num` int(11) NOT NULL DEFAULT '0' COMMENT '消费次数',
  `buy_price_selected` int(11) NOT NULL DEFAULT '0' COMMENT '消费金额达',
  `buy_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '消费金额',
  `buy_goods_selected` int(11) NOT NULL DEFAULT '0' COMMENT '购买商品',
  `buy_goods_way` int(11) NOT NULL DEFAULT '0' COMMENT '1 任意商品  2 指定商品  3 指定分类',
  `goods_ids` varchar(255) DEFAULT NULL COMMENT '指定商品的goods_warehouse_id',
  `cat_ids` varchar(255) DEFAULT NULL COMMENT '指定分类',
  `buy_compute_way` int(11) DEFAULT '0' COMMENT '1、付款后 2完成后',
  `become_child_way` int(11) DEFAULT '0' COMMENT '1、首次点击分享链接 2、首次下单 3、首次付款',
  `protocol` longtext COMMENT '申请协议',
  `notice` longtext COMMENT '用户须知',
  `status_pic_url` varchar(255) DEFAULT NULL COMMENT '审核状态图片',
  `is_delete` smallint(6) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `cat_list` varchar(255) DEFAULT NULL COMMENT '分类列表',
  `goods_list` mediumtext COMMENT '商品列表',
  `cash_type` varchar(255) DEFAULT NULL COMMENT '提现类型 ["auto","wechat","balance","alipay","bank"]',
  `cash_service_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提现手续费',
  `min_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '每次最低提现金额',
  `day_max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '每天最多提现金额',
  `is_income_cash` tinyint(4) NOT NULL DEFAULT '0' COMMENT '收入提现',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户关系表';
COMMIT;

-- jxmall_income_log 表,新增字段如下 author：zal  time：2020-05-30 10:05
ALTER TABLE jxmall_income_log
    ADD COLUMN `order_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单详情id' AFTER `user_id`,
    ADD COLUMN `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '';

-- 删除没用的表,新增字段如下 author：zal  time：2020-05-30 14:25
drop table jxmall_distribution;
drop table jxmall_distribution_cash;
drop table jxmall_distribution_cash_log;
drop table jxmall_distribution_level;
drop table jxmall_distribution_order;
drop table jxmall_distribution_order_log;
drop table jxmall_distribution_setting;
ALTER TABLE `jxmall_user_coupon` ADD `is_failure` TINYINT NOT NULL DEFAULT '0' COMMENT '已失效' ;

-- jxmall_income_log 表,新增字段如下 author：zal  time：2020-05-30 10:05
ALTER TABLE jxmall_income_log
    ADD COLUMN `order_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单详情id' AFTER `user_id`,
    ADD COLUMN `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '';

-- jxmall_order_detail 表,新增字段如下 author：zal  time：2020-06-02 10:05
ALTER TABLE jxmall_order_detail
    ADD COLUMN `use_user_coupon_id` int(11) NOT NULL COMMENT '使用的用户优惠券id',
    ADD COLUMN `coupon_discount_price` decimal(10,2) NOT NULL COMMENT '优惠券优惠金额';

-- jxmall_validate_code_log表,修改字段如下 author：zal  time：2020-06-03 16:05
ALTER TABLE jxmall_recharge_orders
    CHANGE send_price `give_money` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '赠送金额',
    CHANGE send_score `give_score` int(11) NOT NULL DEFAULT '0' COMMENT '赠送积分';


ALTER TABLE `jxmall_postage_rules` CHANGE `deleted_at` `deleted_at` INT NOT NULL DEFAULT '0';

-- jxmall_coupon 表,修改字段如下 author：zal  time：2020-06-04 11:05
ALTER TABLE `jxmall_coupon` ADD `is_failure` TINYINT NOT NULL DEFAULT '0' COMMENT '是否失效1失效' ;

-- jxmall_plugin_distribution_order 表,修改字段如下 author：zal  time：2020-06-09 15:05
ALTER TABLE `jxmall_plugin_distribution_order`
    ADD `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支付0未支付1已支付' AFTER `is_refund`;

ALTER TABLE `jxmall_plugin_distribution_commission_log`
    ADD `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支付0未支付1已支付' AFTER `status`;

-- 删除 jxmall_mall_members 表 author：zal  time：2020-06-20 09:55
drop table jxmall_mall_members;

ALTER TABLE `jxmall_order`
    ADD `complete_at` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间';

ALTER TABLE `jxmall_common_order`
    ADD `pay_price` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '支付金额';

ALTER TABLE `jxmall_order_detail_express` ADD `express_code` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '物流编号' ;

DROP TABLE IF EXISTS `jxmall_common_order_goods`;
CREATE TABLE IF NOT EXISTS `jxmall_common_order_goods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `order_detail_id` int(11) NOT NULL COMMENT '订单详情ID',
    `goods_id` int(11) NOT NULL,
    `attr_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品规格ID',
    `num` int(11) NOT NULL DEFAULT '0',
    `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
    `status` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态0未生效1待结算2已完成3已失效',
    `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付0否1是',
    `from_type` tinyint(2) not null DEFAULT '0' COMMENT '来源类型0商城',
    `is_delete` int(11) NOT NULL DEFAULT '0',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共订单表';
ALTER TABLE `jxmall_order_detail_express` ADD `express_code` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '物流编号' ;

DROP TABLE IF EXISTS `jxmall_common_order_goods`;
CREATE TABLE IF NOT EXISTS `jxmall_common_order_goods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `order_detail_id` int(11) NOT NULL COMMENT '订单详情ID',
    `goods_id` int(11) NOT NULL,
    `attr_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品规格ID',
    `num` int(11) NOT NULL DEFAULT '0',
    `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
    `status` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态0未生效1待结算2已完成3已失效',
    `is_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支付0否1是',
    `from_type` tinyint(2) not null DEFAULT '0' COMMENT '来源类型0商城',
    `is_delete` int(11) NOT NULL DEFAULT '0',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公共订单表';

DROP TABLE IF EXISTS `jxmall_plugin_distribution_log`;
CREATE TABLE `jxmall_plugin_distribution_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `common_order_detail_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_price` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否发放',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否有效，-1 无效  1有效',
  `created_at` int(11) NOT NULL,
  `deleted_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='分销记录表';

ALTER TABLE jxmall_common_order_detail
    CHANGE pay_price `price` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '商品支付价格';

ALTER TABLE jxmall_common_order_detail
    ADD COLUMN `goods_type` int(11) NOT NULL DEFAULT '0' COMMENT '商品规格id',
    ADD COLUMN `attr_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品规格id',
    ADD COLUMN `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
    ADD COLUMN `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态',
    ADD COLUMN `order_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单详情id';

-- author:zal time:2020-07-01 11:41
ALTER TABLE jxmall_plugin_distribution
    CHANGE `commission_frozen` `frozen_price` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '冻结佣金';

ALTER TABLE jxmall_plugin_distribution DROP COLUMN commission_usable;


ALTER TABLE `jxmall_plugin_distribution_log` ADD `order_id` INT NOT NULL DEFAULT '0' COMMENT '订单ID' ;
ALTER TABLE `jxmall_income_log` ADD `deleted_at` INT NOT NULL DEFAULT '0' COMMENT '删除时间' ;

ALTER TABLE `jxmall_income_log` ADD `is_delete` TINYINT NOT NULL DEFAULT '0' COMMENT '是否删除' ;


DROP TABLE IF EXISTS `jxmall_price_log`;

CREATE TABLE `jxmall_price_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `common_order_detail_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_price` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否发放',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否有效，-1 无效  1有效',
  `created_at` int(11) NOT NULL,
  `deleted_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `sign` varchar(45) DEFAULT 'mall' COMMENT '插件标识，如果没有插件就是mall',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='分销记录表';

DROP TABLE IF EXISTS `jxmall_plugin_agent`;
CREATE TABLE `jxmall_plugin_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `remarks` longtext COMMENT '备注',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `total_childs` int(11) NOT NULL DEFAULT '0' COMMENT '所有下级数量',
  `total_order` int(11) NOT NULL DEFAULT '0' COMMENT '分销订单数量',
  `upgrade_level_at` int(11) NOT NULL DEFAULT '0' COMMENT '分销商等级升级时间',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计佣金',
  `frozen_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结佣金',
  `delete_reason` text COMMENT '删除原因',
  `upgrade_status` smallint(6) NOT NULL DEFAULT '0' COMMENT '1条件升级  2 购买指定商品升级   3手动升级',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '经销商等级',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='经销商信息';


DROP TABLE IF EXISTS `jxmall_plugin_agent_setting`;
CREATE TABLE `jxmall_plugin_agent_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除 0--未删除 1--已删除',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='经销插件设置';

DROP TABLE IF EXISTS `jxmall_plugin_agent_level`;
CREATE TABLE `jxmall_plugin_agent_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '-1',
  `name` varchar(45) NOT NULL,
  `is_auto_upgrade` int(11) NOT NULL DEFAULT '0',
  `equal_price_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '平级奖佣金类型 0百分比 1 固定金额',
  `detail` longtext,
  `equal_price` decimal(10,2) NOT NULL COMMENT '平级奖',
  `agent_price` decimal(10,2) NOT NULL COMMENT '团队奖',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` smallint(11) NOT NULL DEFAULT '0',
  `checked_condition_values` longtext COMMENT '升级条件',
  `is_use` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `checked_condition_keys` text COMMENT '升级条件选中的key集合',
  `condition_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '0 未选择、1满足其一  2、满足所有',
  `upgrade_type_goods` smallint(6) NOT NULL DEFAULT '0',
  `upgrade_type_condition` smallint(6) NOT NULL DEFAULT '0',
  `goods_warehouse_ids` text COMMENT '商品仓库的ID',
  `goods_list` longtext COMMENT '商品列表',
  `goods_type` smallint(6) NOT NULL DEFAULT '0' COMMENT '商品升级类型',
  `agent_price_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '团队奖佣金类型 0 百分比 1 固定金额',
  `buy_goods_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0订单完成 1下单 完成',
  `over_agent_price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '被越级的奖励',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='经销商等级';

DROP TABLE IF EXISTS `jxmall_plugin_agent_price_log_type`;
CREATE TABLE `jxmall_plugin_agent_price_log_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `price_log_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0、经销商提成 1、平级奖 2、越级奖',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `jxmall_plugin_agent_goods_detail`;
CREATE TABLE `jxmall_plugin_agent_goods_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `agent_price` decimal(10,2) NOT NULL,
  `equal_price` decimal(10,2) NOT NULL,
  `over_agent_price` decimal(10,2) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `goods_id` tinyint(4) DEFAULT NULL,
  `agent_goods_id` int(11) NOT NULL COMMENT 'agent_goods表中的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `jxmall_plugin_agent_goods`;
CREATE TABLE `jxmall_plugin_agent_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `agent_price_type` tinyint(4) NOT NULL DEFAULT '0',
  `equal_price_type` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `goods_type` tinyint(4) NOT NULL DEFAULT '0',
  `is_alone` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否单独设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `jxmall_common_order` ADD `is_pay` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否支付';


-- 新增名片插件相关表 zal 2020-07-09 19:15
-- 我的名片相关表
-- 2020-07-03 10:00

DROP TABLE IF EXISTS `jxmall_plugin_business_card`;
CREATE TABLE `jxmall_plugin_business_card` (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
       `mall_id` int(11) NOT NULL,
       `user_id` int(11) NOT NULL,
       `head_img` varchar(255) not null DEFAULT '' COMMENT '头像',
       `full_name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
       `department_id` varchar(30) NOT NULL DEFAULT '' COMMENT '部门id',
       `position_id` varchar(30) NOT NULL DEFAULT '' COMMENT '职位id',
       `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
       `email` varchar(64) NOT NULL DEFAULT '' COMMENT '邮箱',
       `wechat_qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '微信二维码',
       `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
       `company_name` varchar(255) NOT NULL DEFAULT '' COMMENT '公司名称',
       `company_address` varchar(255) NOT NULL DEFAULT '' COMMENT '公司地址',
       `landline` varchar(20) NOT NULL DEFAULT '' COMMENT '座机',
       `likes` int(11) NOT NULL DEFAULT '0' COMMENT '靠谱数',
       `visitors` int(11) NOT NULL DEFAULT 0 COMMENT '浏览数',
       `introduction` varchar(1500) not null DEFAULT '' COMMENT '简介',
       `images` varchar(1000) not null DEFAULT '' COMMENT '图片展示，json格式存储',
       `videos` varchar(255) not null DEFAULT '' COMMENT '视频展示',
       `voices` varchar(255) not null DEFAULT '' COMMENT '语音介绍',
       `status` tinyint(1) not null default '0' COMMENT '状态',
       `created_at` int(11) NOT NULL DEFAULT '0',
       `updated_at` int(11) NOT NULL DEFAULT '0',
       `deleted_at` int(11) NOT NULL DEFAULT '0',
       `is_delete` tinyint(1) NOT NULL DEFAULT '0',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_tag`;
CREATE TABLE `jxmall_plugin_business_card_tag` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `mall_id` int(11) NOT NULL,
       `user_id` int(11) NOT NULL,
       `add_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '新增此标签的用户id,0表示自动标签',
       `bcid` int(45) NOT NULL DEFAULT '0' COMMENT '名片id',
       `name` varchar(45) NOT NULL DEFAULT '' COMMENT '标签名称',
       `likes` int(11) NOT NULL DEFAULT 0 COMMENT '点赞数',
       `is_diy` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否自定义标签',
       `created_at` int(11) NOT NULL DEFAULT '0',
       `updated_at` int(11) NOT NULL DEFAULT '0',
       `deleted_at` int(11) NOT NULL DEFAULT '0',
       `is_delete` tinyint(1) NOT NULL DEFAULT '0',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片标签表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_customer`;
CREATE TABLE `jxmall_plugin_business_card_customer` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `user_type` tinyint(1) not null default '0' COMMENT '客户类型0普通客户1意向客户2比较客户3待成交客户4已成交客户',
    `operate_id` int(11) NOT NULL DEFAULT 0 COMMENT '操作人id',
    `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态0待授权1新增线索2跟进中3成交',
    `basic_info` varchar(1000) NOT NULL DEFAULT '' COMMENT '基础信息，json格式存储',
    `is_tag` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否生成了自动标签',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='客户资料表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_customer_log`;
CREATE TABLE `jxmall_plugin_business_card_customer_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `operate_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '记录内容',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户客户状态变更记录';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_track_log`;
CREATE TABLE `jxmall_plugin_business_card_track_log` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `mall_id` int(11) NOT NULL,
     `user_id` int(11) NOT NULL,
     `track_user_id` int(11) NOT NULL COMMENT '轨迹对象id',
     `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
     `ip` char(15) not null default '0' COMMENT '访问ip',
     `track_type` tinyint(2) not null default '0' COMMENT '轨迹类型1商城首页2查看图文3产品4授权号码5转发名片6查看名片7查看视频8保存电话9点赞10收藏11评论12查看动态',
     `model_id` int(11) not null default 0 COMMENT '模块id,如名片存的是名片id，列表显示0',
     `created_at` int(11) NOT NULL DEFAULT '0',
     `updated_at` int(11) NOT NULL DEFAULT '0',
     `deleted_at` int(11) NOT NULL DEFAULT '0',
     `is_delete` tinyint(1) NOT NULL DEFAULT '0',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户轨迹';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_auth`;
CREATE TABLE `jxmall_plugin_business_card_auth` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `department_id` int(11) NOT NULL COMMENT '部门id',
    `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色id',
    `permissions` varchar(3000) not null DEFAULT '0' COMMENT '权限菜单',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片权限表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_department`;
CREATE TABLE `jxmall_plugin_business_card_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '父级id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `sort` int(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片部门表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_position`;
CREATE TABLE `jxmall_plugin_business_card_position` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `bcpid` int(11) NOT NULL DEFAULT 0 COMMENT '部门id',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片部门职位表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_role`;
CREATE TABLE `jxmall_plugin_business_card_role` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='名片角色表';

DROP TABLE IF EXISTS `jxmall_plugin_business_card_setting`;
CREATE TABLE `jxmall_plugin_business_card_setting` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `mall_id` int(11) NOT NULL,
   `key` varchar(255) NOT NULL,
   `value` longtext NOT NULL,
   `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
   `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
   `is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除 0--未删除 1--已删除',
   `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
   PRIMARY KEY (`id`) USING BTREE,
   KEY `mall_id` (`mall_id`) USING BTREE,
   KEY `key` (`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='名片配置';


DROP TABLE IF EXISTS `jxmall_plugin_area_agent`;
CREATE TABLE `jxmall_plugin_area_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `remarks` longtext COMMENT '备注',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `total_childs` int(11) NOT NULL DEFAULT '0' COMMENT '所有下级数量',
  `total_order` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `upgrade_level_at` int(11) NOT NULL DEFAULT '0' COMMENT '区域等级升级时间',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计佣金',
  `frozen_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结佣金',
  `delete_reason` text COMMENT '删除原因',
  `upgrade_status` smallint(6) NOT NULL DEFAULT '0' COMMENT '1条件升级  2 购买指定商品升级   3手动升级',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0普通用户 1、镇代 2、区代 3、市代 4、省代',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='区域代理商信息';

-- 新增名片行为轨迹统计表 zal 2020-07-11 17:15
DROP TABLE IF EXISTS `jxmall_plugin_business_card_track_stat`;
CREATE TABLE `jxmall_plugin_business_card_track_stat` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `mall_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `track_user_id` int(11) NOT NULL COMMENT '轨迹对象id',
      `total` int(11) NOT NULL DEFAULT '0' COMMENT '次数',
      `track_type` tinyint(2) not null default '0' COMMENT '轨迹类型1商城首页2查看图文3产品4授权号码5转发名片6查看名片7查看视频8保存电话9点赞10收藏11评论12查看动态',
      `model_id` int(11) not null default 0 COMMENT '模块id,如名片存的是名片id，列表显示0',
      `created_at` int(11) NOT NULL DEFAULT '0',
      `updated_at` int(11) NOT NULL DEFAULT '0',
      `deleted_at` int(11) NOT NULL DEFAULT '0',
      `is_delete` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='用户行为轨迹统计';

CREATE TABLE `jxmall_plugin_area_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `price_type` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `goods_type` tinyint(4) NOT NULL DEFAULT '0',
  `is_alone` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否单独设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `jxmall_plugin_area_goods_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `province_price` decimal(10,2) NOT NULL,
  `district_price` decimal(10,2) NOT NULL,
  `town_price` decimal(10,2) NOT NULL,
  `city_price` decimal(10,2) NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `goods_id` int(11) DEFAULT NULL,
  `area_goods_id` int(11) NOT NULL COMMENT 'area_goods表中的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `jxmall_order` ADD `address_id` INT NOT NULL DEFAULT '0' AFTER `complete_at`;
ALTER TABLE `jxmall_user_address` ADD `town_id` INT NOT NULL DEFAULT '0' COMMENT '镇ID' AFTER `location`, ADD `town` VARCHAR(255) NULL DEFAULT NULL COMMENT '镇' AFTER `town_id`;

-- 新增用户来源字段 zal 2020-07-17 16:00
ALTER TABLE `jxmall_user` ADD `source` tinyint(2) NOT NULL DEFAULT '1' COMMENT '用户来源1分享首页2分享海报3分享商品4分享内容5分享视频6分享资讯7分享名片';

-- 新增类型字段 zal 2020-07-18 14:40
ALTER TABLE `jxmall_plugin_business_card_customer_log` ADD `log_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '记录类型1添加商机2新增线索3新增跟进记录4拨打电话5私聊6修改设置7订单成交';

-- 新增ai分析表 author:zal date:2020-07-23 19:11:00
DROP TABLE IF EXISTS `jxmall_plugin_business_card_ai_analysis`;
CREATE TABLE `jxmall_plugin_business_card_ai_analysis` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `sales_active` int(11) NOT NULL DEFAULT '0' COMMENT '销售主动值',
    `website_promote` int(11) NOT NULL DEFAULT '0' COMMENT '官网推广值',
    `goods_promote` int(11) NOT NULL DEFAULT '0' COMMENT '产品推广值',
    `deal_ability` int(11) NOT NULL DEFAULT '0' COMMENT '成交能力',
    `customers_ability` int(11) NOT NULL DEFAULT '0' COMMENT '获客能力',
    `personal_appeal` int(11) NOT NULL DEFAULT '0' COMMENT '个人魅力',
    `average`  decimal(10,2) not null default '0' COMMENT '平均值',
    `total` int(11) not null default 0 COMMENT '总值',
    `year` int(4) not null default 0 COMMENT '年',
    `month` tinyint(2) not null default 0 COMMENT '月',
    `day` tinyint(2) not null default 0 COMMENT '日',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='雷达ai分析表';

-- jxmall_plugin_business_card_auth表,新增字段如下 author：zal  time：2020-07-28 14:05
ALTER TABLE jxmall_plugin_business_card_auth
    ADD COLUMN `position_id` int(11) NOT NULL DEFAULT '0' COMMENT '职位id' AFTER `department_id`;

-- 新增 jxmall_tag 表, author：zal  time：2020-07-31 11:05
DROP TABLE IF EXISTS `jxmall_tag`;
CREATE TABLE `jxmall_tag` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `mall_id` int(11) NOT NULL,
   `cat_id` int(11) NOT NULL DEFAULT 0 COMMENT '标签分类id',
   `name` varchar(45) NOT NULL DEFAULT '' COMMENT '标签名称',
   `type` int(45) NOT NULL DEFAULT '0' COMMENT '标签类型',
   `condition` varchar(3000) NOT NULL DEFAULT '' COMMENT '条件',
   `created_at` int(11) NOT NULL DEFAULT '0',
   `updated_at` int(11) NOT NULL DEFAULT '0',
   `deleted_at` int(11) NOT NULL DEFAULT '0',
   `is_delete` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='标签表';

-- 新增 jxmall_tag_category 表, author：zal  time：2020-07-31 11:05
DROP TABLE IF EXISTS `jxmall_tag_category`;
CREATE TABLE `jxmall_tag_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL DEFAULT '' COMMENT '标签分类名称',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='标签分类表';

-- 新增 jxmall_object_tag 表, author：zal  time：2020-07-31 11:05
DROP TABLE IF EXISTS `jxmall_object_tag`;
CREATE TABLE `jxmall_object_tag` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `mall_id` int(11) NOT NULL,
   `object_id` int(11) NOT NULL DEFAULT 0 COMMENT '对象id，用户，文章，视频等id',
   `cat_id` int(11) NOT NULL DEFAULT 0 COMMENT '标签分类id',
   `tag_id` int(11) NOT NULL DEFAULT 0 COMMENT '标签id',
   `likes` int(11) NOT NULL DEFAULT 0 COMMENT '点赞数',
   `visitors` int(11) NOT NULL DEFAULT 0 COMMENT '浏览数',
   `created_at` int(11) NOT NULL DEFAULT '0',
   `updated_at` int(11) NOT NULL DEFAULT '0',
   `deleted_at` int(11) NOT NULL DEFAULT '0',
   `is_delete` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='不同对象标签表';

-- 新增 jxmall_goods_search_log 表, author：zal  time：2020-08-07 11:05 暂时用不到
DROP TABLE IF EXISTS `jxmall_goods_search_log`;
CREATE TABLE `jxmall_goods_search_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mall_id` int(11) NOT NULL,
    `goods_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL DEFAULT 0,
    `keywords` varchar(128) NOT NULL DEFAULT '' COMMENT '搜索关键词',
    `created_at` int(11) NOT NULL DEFAULT '0',
    `updated_at` int(11) NOT NULL DEFAULT '0',
    `deleted_at` int(11) NOT NULL DEFAULT '0',
    `is_delete` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- jxmall_goods 表新增以下字段, author：zal  time：2020-09-03 19:05
ALTER TABLE `jxmall_goods` ADD `full_relief_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '单品满额减免金额' AFTER `labels`;
ALTER TABLE `jxmall_goods` ADD `fulfil_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '单品满额金额' AFTER `full_relief_price`;

--
-- 新增 jxmall_plugin_distribution_apply 表, author：zal  time：2020-09-10 11:05
DROP TABLE IF EXISTS `jxmall_plugin_distribution_apply`;
CREATE TABLE `jxmall_plugin_distribution_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mobile` varchar(45) NOT NULL,
  `realname` varchar(45) NOT NULL,
  `marks` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0待审核1审核通过2不通过',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='分销商申请记录';

-- 分销等级 author：zal  time：2020-09-11 19:05
ALTER TABLE `jxmall_plugin_distribution_level` CHANGE `goods_warehouse_ids` `goods_warehouse_ids` varchar(3000) COMMENT '商品仓库的ID' NOT NULL DEFAULT '';

-- 提现汇款记录表 author：zal  time：2020-09-16 19:05
DROP TABLE IF EXISTS `jxmall_remit_log`;
CREATE TABLE `jxmall_remit_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) DEFAULT 0 NOT NULL,
  `user_id` int(11) DEFAULT 0 NOT NULL,
  `operator_id` int(11) DEFAULT 0 NOT NULL,
  `type` tinyint(1) DEFAULT 0 NOT NULL,
  `price` decimal(10,2) DEFAULT '0' NOT NULL,
  `content` varchar(3000) DEFAULT '' NOT NULL COMMENT '汇款原因',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8mb4 COMMENT='提现汇款记录表';

-- 公共订单详情表 插件标识字段类型修改 int改成varchar 用于记录插件标识 等同于order表中的sign字段
ALTER TABLE `jxmall_common_order_detail` CHANGE `goods_type` `goods_type` VARCHAR(11) NOT NULL DEFAULT '0' COMMENT '插件标识';

-- 新增操作日志表 2020-09-26 @author zal
DROP TABLE IF EXISTS `jxmall_admin_operate_log`;
CREATE TABLE `jxmall_admin_operate_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL COMMENT '操作人ID',
  `name` varchar(255) not null default '' COMMENT '名称',
  `model` varchar(155) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '模型名称',
  `module` varchar(50) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '模块名',
  `model_id` int(11) NOT NULL COMMENT '模型ID',
  `operate_ip` varchar(15) not null default '' COMMENT '操作ip',
  `before_update` longtext COLLATE utf8mb4_german2_ci,
  `after_update` longtext COLLATE utf8mb4_german2_ci,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  `remark` varchar(255) COLLATE utf8mb4_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_mall_admin_id` (`mall_id`,`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci ROW_FORMAT=DYNAMIC COMMENT='后台操作日志表';

-- 微信消息模板 2020/10/08 xuyaoxiang
-- 表的结构 `jxmall_template_message`
--
CREATE TABLE `jxmall_template_message` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '模板id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=订阅消息,1=微信模板消息',
  `tempkey` char(50) NOT NULL COMMENT '模板编号',
  `name` char(100) DEFAULT NULL COMMENT '模板名',
  `content` varchar(1000) DEFAULT NULL COMMENT '回复内容',
  `tempid` char(100) NOT NULL COMMENT '模板ID',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `mall_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商城id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信模板' ROW_FORMAT=COMPACT;

ALTER TABLE `jxmall_template_message`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `jxmall_template_message`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模板id';
COMMIT;
-- 微信消息模板 2020/10/08 xuyaoxiang

-- 微信消息模板 2020/10/12 xuyaoxiang
INSERT INTO `jxmall_mall_setting` (`id`, `mall_id`, `key`, `value`, `is_delete`) VALUES
(63, 5, 'wechat_notice', '{\"is_open\":\"0\",\"is_miniapp_priority\":\"1\"}', 0);
-- 微信消息模板 2020/10/12 xuyaoxiang

-- 新建微信消息模板日志表 jxmall_wechat_temp_notice_log 2020/10/12 xuyaoxiang
CREATE TABLE `jxmall_wechat_temp_notice_log` ( `id` INT NOT NULL AUTO_INCREMENT , `params` TEXT NULL DEFAULT NULL COMMENT '参数' , `result` VARCHAR(255) NULL DEFAULT NULL COMMENT '返回值' , `created_at` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' , PRIMARY KEY (`id`)) ENGINE = MyISAM COMMENT = '微信模板消息日志';
-- 新建微信消息模板日志表 jxmall_wechat_temp_notice_log 2020/10/12 xuyaoxiang

-- 新建错误信息日志表 2020/10/14 xuyaoxiang
CREATE TABLE `jxmall_error_log` ( `id` INT(11) NOT NULL , `error_key` VARCHAR(255) NULL DEFAULT NULL COMMENT '关键词' , `data` TEXT NULL DEFAULT NULL COMMENT '错误信息' , `created_at` INT NOT NULL DEFAULT '0' COMMENT '创建时间' ) ENGINE = MyISAM COMMENT = '错误信息日志表';
--

-- 修改错误信息日志表 2020/10/14 xuyaoxiang
ALTER TABLE `jxmall_error_log` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
--

-- 修改小程序二维码表中admin字段改成user_id 2020/10/17 zal
ALTER TABLE `jxmall_qrcode_parameter` CHANGE `admin_id` `user_id` INT(11) NOT NULL DEFAULT '0';

-- 订单主表增加满额减免金额字段 xuyaoxiang 2020/10/19
ALTER TABLE `jxmall_order` ADD `full_relief_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '满额减免金额' AFTER `address_id`

-- 订单详情表曾满额减免金额字段 xuyaoxiang 2020/10/19
ALTER TABLE `jxmall_order_detail` ADD `full_relief_price` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '满额减免金额' AFTER `score_price`;

-- 前端用户设置表 2020/10/19 xuyaoxiang

-- --------------------------------------------------------

--
-- 表的结构 `jxmall_user_setting`
--

CREATE TABLE `jxmall_user_setting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `setting_key` varchar(255) NOT NULL COMMENT '设置键',
  `data` text NOT NULL COMMENT '设置数据',
  `mall_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城id',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deleted_at` int(11) NOT NULL DEFAULT '0' COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户设置配置表';

--
-- 转储表的索引
--

--
-- 表的索引 `jxmall_user_setting`
--
ALTER TABLE `jxmall_user_setting`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `jxmall_user_setting`
--
ALTER TABLE `jxmall_user_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
-- 前端用户设置表 2020/10/19 xuyaoxiang

-- 新增一条关于我们数据 2020/10/19 zal
INSERT INTO `jxmall_article`(`id`, `mall_id`, `article_cat_id`, `status`, `title`, `content`, `sort`, `is_delete`, `created_at`, `deleted_at`, `updated_at`)
VALUES (1, 5, 1, 1, '关于我们', '<p>关于我们</p>', 0, 0, 1590739645, 0, 1603079820);

-- 订单详情售后状态注释 xuyaoxiang 2020/10/23
ALTER TABLE `jxmall_order_detail` CHANGE `refund_status` `refund_status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '售后状态 0--未售后 10--售后中,待处理 11--售后中,仅退款,已同意;12--售后中,退款退货,已同意;20--完成售后,已退款;21--完成售后,已拒绝';


-- jxmall_plugin_business_card表,新增字段如下 author：zal  time：2020-10-23 17:05
ALTER TABLE jxmall_plugin_business_card
    ADD COLUMN `is_auth` int(11) NOT NULL DEFAULT '0' COMMENT '是否授权' AFTER `status`,
    ADD COLUMN `auth_mobile` varchar(13) NOT NULL DEFAULT '' COMMENT '授权手机号' AFTER `status`;


-- jxmall_order表 新增省份字段  2020/10/23  ly
ALTER TABLE `jxmall_order`
ADD COLUMN `province_id`  int NULL COMMENT '省份id' AFTER `address_id`;


-- jxmall_user 升级时间  2020/10/23  ly
ALTER TABLE `jxmall_user`
ADD COLUMN `upgrade_time`  int NOT NULL COMMENT '升级时间' AFTER `background`;


-- 新增数据统计日志  2020/10/24  ly
CREATE TABLE `jxmall_statistics_browse_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型 ：0：用户id（存用户id） ：1：游客（存ip）',
  `browse_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '浏览类型：0：首页 1：分类 2：商品详情',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `user_ip` varchar(255) DEFAULT NULL COMMENT '用户ip',
  `created_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `browse_type` (`browse_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='浏览日志';

-- 日志  2020/10/24  ly
CREATE TABLE `jxmall_statistics_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL COMMENT '商城ID',
  `update_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '更新类型：0：一小时一更新 1：每天一次更新',
  `type` varchar(255) NOT NULL COMMENT '类型（详情看控制器模型等）',
  `num` double DEFAULT '0' COMMENT '数量',
  `remark` varchar(1023) DEFAULT NULL COMMENT '备注（例如省份数据，以及等级数据）',
  `date` int(10) unsigned NOT NULL COMMENT '数据日期',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='数据统计';

-- 配置  2020/10/24  ly
CREATE TABLE `jxmall_statistics_virtual_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mall_id` int(11) NOT NULL COMMENT '商城id',
  `set_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '设置类型：0：真实数据 1：虚拟数据',
  `total_transactions` double DEFAULT '0' COMMENT '交易总额设置（元）',
  `today_earnings` double DEFAULT '0' COMMENT '今日收益（元）',
  `user_sum` int(10) unsigned DEFAULT '0' COMMENT '用户总数（人）',
  `visitor_num` int(10) unsigned DEFAULT '0' COMMENT '访客量',
  `browse_num` int(10) unsigned DEFAULT '0' COMMENT '浏览量',
  `province_data` varchar(1023) DEFAULT NULL COMMENT '省份数据',
  `member_level` varchar(1023) DEFAULT NULL COMMENT '用户等级',
  `conversion_browse_num` int(10) unsigned DEFAULT '0' COMMENT '转化浏览量',
  `conversion_visitor_num` int(10) unsigned DEFAULT '0' COMMENT '转化访客量',
  `follow_num` int(10) unsigned DEFAULT '0' COMMENT '关注量',
  `order_visit_num` int(10) unsigned DEFAULT '0' COMMENT '订单处访问量',
  `order_num` int(10) unsigned DEFAULT '0' COMMENT '下单数量',
  `pay_num` int(10) unsigned DEFAULT '0' COMMENT '支付人数',
  `add_user` int(10) unsigned DEFAULT '0' COMMENT '新增用户',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mall_id` (`mall_id`) USING BTREE,
  KEY `set_type` (`set_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='数据统计虚拟数据配置';


-- 订单表 省份id 默认为0 2020/10/26  ly
ALTER TABLE `jxmall_order`
MODIFY COLUMN `province_id`  int(11) NOT NULL DEFAULT 0 COMMENT '省份id' AFTER `address_id`;

-- 用户表  升级时间默认为0 2020/10/26  ly
ALTER TABLE `jxmall_user`
MODIFY COLUMN `upgrade_time`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '升级时间' AFTER `background`;

-- 系统公告 2020/10/27  ly
DROP TABLE IF EXISTS `jxmall_notice`;
CREATE TABLE `jxmall_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `deleted_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='系统公告';

-- ----------------------------
-- Records of jxmall_notice
-- ----------------------------
INSERT INTO `jxmall_notice` VALUES ('1', '测试1', null, '0', '0', '0', '0');
INSERT INTO `jxmall_notice` VALUES ('2', '测试2', null, '0', '0', '0', '0');
INSERT INTO `jxmall_notice` VALUES ('3', '测试3', null, '0', '0', '0', '0');
INSERT INTO `jxmall_notice` VALUES ('4', '测试4', null, '0', '0', '0', '0');

-- 自定义商品价格显示名称表 2020/10/27 xuyaoxiang
CREATE TABLE `jxmall_goods_price_display` ( `id` INT(11) NOT NULL , `name` VARCHAR(255) NOT NULL COMMENT '自定义商品价格显示名称' , `created_at` INT(9) NOT NULL DEFAULT '0' COMMENT '创建时间' , `updated_at` INT(9) NOT NULL DEFAULT '0' COMMENT '更新时间' , `deleted_at` INT(9) NOT NULL DEFAULT '0' COMMENT '删除时间' , `sort` INT(0) NOT NULL DEFAULT '0' COMMENT '排序;由大到小' ) ENGINE = InnoDB COMMENT = '自定义商品价格显示名称表';

ALTER TABLE `jxmall_goods_price_display` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);

ALTER TABLE `jxmall_goods_price_display` ADD `mall_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商城id' AFTER `sort`;
-- 自定义商品价格显示名称表 xuyaoxiang

-- 自定义商品价格显示名称表 xuyaoxiang 2020/10/28
ALTER TABLE `jxmall_goods` ADD `price_display` TEXT NULL DEFAULT NULL COMMENT '自定义显示商品价格字样' AFTER `fulfil_price`;
-- 自定义商品价格显示名称表 xuyaoxiang 2020/10/28

-- 商城配置表 xuyaoxiang 2020/10/28
ALTER TABLE `jxmall_mall_setting` ADD `name` VARCHAR(255) NULL DEFAULT NULL COMMENT '配置名称' AFTER `is_delete`;
ALTER TABLE `jxmall_mall_setting` ADD `setting_desc` TEXT NULL DEFAULT NULL COMMENT '配置说明' AFTER `name`;
-- 商城配置表 xuyaoxiang 2020/10/28
