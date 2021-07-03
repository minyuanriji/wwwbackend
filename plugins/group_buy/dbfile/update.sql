#2020/09/26

 #group_buy_goods赠送积分
 ALTER TABLE `jxmall_plugin_group_buy_goods` ADD `send_score` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送积分' AFTER `deleted_at`;


 #赠送余额
  ALTER TABLE `jxmall_plugin_group_buy_goods` ADD `send_balance` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送余额' AFTER `send_score`;

  #是否发放奖励
  ALTER TABLE `jxmall_plugin_group_buy_active` ADD `is_send` TINYINT(1) NOT NULL COMMENT '是否发放奖励' AFTER `is_manual`;

  #2020/09/27.
  #拼团库存
  ALTER TABLE `jxmall_plugin_group_buy_goods_attr` ADD `stock` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '拼团库存' AFTER `group_buy_price`;

  ALTER TABLE `jxmall_plugin_group_buy_goods` ADD `goods_stock` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '拼团库存' AFTER `send_balance`;