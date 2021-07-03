<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 确认订单
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\component\jobs\OrderCustomerServiceJob;
use app\models\QueueData;
use app\models\Order;
use app\services\wechat\WechatTemplateService;

class OrderConfirmedHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CONFIRMED, function ($event) {
            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $orderAutoSaleTime = \Yii::$app->mall->getMallSettingOne('after_sale_time');
            if (is_numeric($orderAutoSaleTime) && $orderAutoSaleTime >= 0) {
                // 订单过售后
                $id = \Yii::$app->queue->delay($orderAutoSaleTime * 86400)->push(new OrderCustomerServiceJob([
                    'orderId' => $event->order->id
                ]));
                QueueData::add($id, $event->order->token);
                $autoSalesTime = $event->order->confirm_at + $orderAutoSaleTime * 86400;
                $event->order->auto_sales_at = $autoSalesTime;
                $event->order->save();
            }
            $commonOrder = OrderCommon::getCommonOrder($event->order->sign);
            $orderConfig = $commonOrder->getOrderConfig();
            $this->sendWechatTemp($event->order);
            try {
                if ($orderConfig->is_print != 1) {
                    throw new PrintException($event->order->sign . '未开启小票打印');
                }
                $res = (new PrintOrder())->print($event->order, $event->order->id, 'confirm');
            } catch (PrintException $e) {
                \Yii::error("小票打印机打印出错：" . $e->getMessage());
            }
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
