<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:01
 */

namespace app\handlers\orderHandler;


use app\services\wechat\WechatTemplateService;

class OrderChangePriceHandlerClass extends BaseOrderHandler
{
    public function handle()
    {
        \Yii::error('--改价事件触发--');
        $this->addDistributionOrder();
    }
}
