<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单消息通知
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderEvent;
use app\forms\common\template\tplmsg\Tplmsg;
use app\component\jobs\OrderConfirmJob;
use app\models\Order;
use app\models\OrderDetailExpress;
use app\services\wechat\WechatTemplateService;
use yii\log\Logger;

class OrderSentHandler extends BaseHandler
{
    /**
     * todo
     * 没有触发成功
     * @return mixed|void
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_SENT, function ($event) {

            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $orderAutoConfirmTime = \Yii::$app->mall->getMallSettingOne('delivery_time');

            // 发送模板消息
            $tplMsg = new Tplmsg();
            $tplMsg->orderSendMsg($event->order);
            $this->sendWechatTemp($event->order);

            if (is_numeric($orderAutoConfirmTime) && $orderAutoConfirmTime >= 0) {
                // 订单自动收货任务
                \Yii::$app->queue->delay($orderAutoConfirmTime * 86400)->push(new OrderConfirmJob([
                    'orderId' => $event->order->id
                ]));
                $autoConfirmTime               = $event->order->send_at + $orderAutoConfirmTime * 86400;
                $event->order->auto_confirm_at = $autoConfirmTime;
                $event->order->save();
            }
        });
    }

    /**
     * @param Order $order
     * @return array
     */
    protected function sendWechatTemp(Order $order)
    {

        $WechatTemplateService = new WechatTemplateService($order->mall_id);

        $url = "/pages/order/detail?orderId=" . $order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $OrderDetailExpress = new OrderDetailExpress();
        $orderDetailExpress = $OrderDetailExpress->getNewestOrderDetailExpress($order->id);

        if ($orderDetailExpress) {
            //1.快递|2.其它方式
            if ($orderDetailExpress->send_type == 1) {
                $express    = $orderDetailExpress->express;
                $express_no = $orderDetailExpress->express_no;
                //商家留言
                $merchant_remark = $orderDetailExpress->merchant_remark;
            } else {
                //物流内容
                $merchant_remark = $orderDetailExpress->express_content;
            }
        }

        $send_data = [
            'first'    => '您的订单已发货',
            'keyword1' => $order->order_no,
            'keyword2' => $express,
            'keyword3' => $express_no,
            'remark'   => $merchant_remark
        ];

        return $WechatTemplateService->send($order->user_id, WechatTemplateService::TEM_KEY['order_sent']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}
