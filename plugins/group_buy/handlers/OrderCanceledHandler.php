<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单取消
 * Author: xuyaoxiang
 * Date: 2020/9/25
 * Time: 10:59
 */

namespace app\plugins\group_buy\handlers;

use app\handlers\BaseHandler;
use app\models\Order;
use app\plugins\group_buy\services\GroupBuyGoodsAttrServices;
use app\services\wechat\WechatTemplateService;

class OrderCanceledHandler extends BaseHandler
{
    public $event;

    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            $this->event=$event;

            $order = $event->order;

            if ($order->sign != 'group_buy') {
                return false;
            }
            $this->sendWechatTemp();
            $GiveScoreServices = new GroupBuyGoodsAttrServices();
            $return            = $GiveScoreServices->goodsAddStock($order);
            if (!$return) {
                \Yii::error("拼团取消订单，退库存失败");
            }
        });
    }

    protected function sendWechatTemp(){
        $WechatTemplateService = new WechatTemplateService($this->event->order->mall_id);

        $url = "/pages/order/detail?orderId=" . $this->event->order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '你好，订单已取消',
            'keyword1' => $this->event->order->order_no,
            'keyword2' => date("Y-m-d H:i:s",time()),
            'keyword3' => $this->event->order->total_pay_price,
            'remark'   => ""
        ];

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_cancel']['tem_key'], $h5_url, $send_data, $platform,$url);

        return $this;
    }
}

