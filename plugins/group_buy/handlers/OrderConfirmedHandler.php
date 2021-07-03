<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/25
 * Time: 10:59
 */

namespace app\plugins\group_buy\handlers;

use app\handlers\BaseHandler;
use app\models\ErrorLog;
use app\models\Order;
use app\plugins\group_buy\services\GiveScoreServices;
use app\services\wechat\WechatTemplateService;

class OrderConfirmedHandler extends BaseHandler
{
    public $event;

    public function register()
    {
        \Yii::$app->on(Order::EVENT_CONFIRMED, function ($event) {
            $order = $event->order;

            if ($order->sign != 'group_buy') {
                return false;
            }

            $this->sendWechatTemp($order);

            $GiveScoreServices = new GiveScoreServices($event->order->mall_id);
            $return            = $GiveScoreServices->send($order->id);

            $ErrorLog = new ErrorLog();
            $ErrorLog->store('拼团订单确认事件:', json_encode($return));
        });
    }

    protected function sendWechatTemp(Order $order){

        $WechatTemplateService = new WechatTemplateService($order->mall_id);

        $url="/pages/order/detail?orderId=".$order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#".$url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '您好，您的一个订单已经确认收货了。',
            'keyword1' => date("Y-m-d H:i:s",$order->created_at),
            'keyword2' => $order->total_pay_price,
            'keyword3' => $order->order_no,
            'remark'   => '感谢您在此购物成功，同时希望您的再次光临！'
        ];

        $WechatTemplateService->send($order->user_id, WechatTemplateService::TEM_KEY['order_confirmed']['tem_key'], $h5_url, $send_data, $platform,$url);
        return $this;
    }
}

