<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 创建订单的事件监听
 * Author: xuyaoxiang
 * Date: 2020/9/3
 * Time: 10:34
 */

namespace app\plugins\group_buy\handlers;

use app\plugins\group_buy\models\Order;
use app\handlers\BaseHandler;
use app\services\wechat\WechatTemplateService;
use Yii;
use yii\helpers\ArrayHelper;
use app\plugins\group_buy\forms\mall\MultiActiveEditForm;

class OrderCreatedHandler extends BaseHandler
{
    public $event;
    /**
     * @return mixed|void
     * [
     * 'active_item' => [
     * 'attr_id' => 133,
     * 'user_id' => 11,
     * 'group_buy_price' => 22.0,
     * 'order_id' => 175,
     * ],
     * 'active' => [
     * 'goods_id' => 42,
     * 'id' => 41,
     * ]];
     *
     *
     */
    public function register()
    {
        Yii::$app->on(Order::EVENT_GROUP_BUY_CREATED, function ($event) {
            $this->event = $event;
            $this->sendWechatTemp();
        });
    }

    protected function sendWechatTemp(){
        $WechatTemplateService = new WechatTemplateService($this->event->order->mall_id);

        $url="/pages/order/detail?orderId=".$this->event->order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#".$url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '您好，您的订单已生成',
            'keyword1' => date("Y-m-d H:i:s",$this->event->order->created_at),
            'keyword2' => $this->event->orderItem['goods_list'][0]['name'],
            'keyword3' => $this->event->order->order_no,
            'remark'   => '感谢您的使用'
        ];

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_create']['tem_key'], $h5_url, $send_data, $platform,$url);

        return $this;
    }
}