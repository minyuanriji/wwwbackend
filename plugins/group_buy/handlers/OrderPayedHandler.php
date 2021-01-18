<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\plugins\group_buy\handlers;

use app\models\Order;
use app\handlers\BaseHandler;
use app\services\wechat\WechatTemplateService;

class OrderPayedHandler extends BaseHandler
{
    public $event;
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            $order = $event->order;
            $this->event = $event;
            if ($order->sign != 'group_buy') {
                return false;
            }

            $this->sendWechatTemp();
        });
    }

    protected function sendWechatTemp()
    {
        $WechatTemplateService = new WechatTemplateService($this->event->order->mall_id);

        $url = "/pages/order/detail?orderId=" . $this->event->order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '您已支付成功',
            'keyword1' => $this->event->order->detail[0]->goods->name,
            'keyword2' => $this->event->order->order_no,
            'keyword3' => $this->event->order->total_pay_price,
            'remark'   => '我们将尽快为您发货'
        ];

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_pay_success']['tem_key'], $h5_url, $send_data, $platform, $url);
        return $this;
    }
}
