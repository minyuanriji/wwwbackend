<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付触发处理类
 * Author: zal
 * Date: 2020-04-21
 * Time: 16:10
 */

namespace app\handlers\orderHandler;


use app\logic\CommonLogic;
use app\services\wechat\WechatTemplateService;


class OrderPayedHandlerClass extends BaseOrderPayedHandler
{
    public function handle()
    {
        \Yii::error('mall order payed');
        $this->paid();
        self::execute();
        $this->action();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        if ($this->event->order->pay_type == 2) {
            if ($this->event->order->is_pay == 0) {
                // 支付方式：货到付款未支付时，只触发部分通知类
                self::notice();
            } else {
                // 支付方式：货到付款，订单支付时，触发剩余部分
                self::pay();
            }
        } else {
            self::notice();
            self::pay();
        }

    }

    protected function notice()
    {
        \Yii::error('--mall notice--');
        $this->sendSms()->sendMail()->receiptPrint('pay')->setGoods();

        if ('group_buy' != $this->event->order->sign) {
            $this->sendWechatTemp();
        }
        
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--mall pay--');
        $this->saveResult();
        return $this;
    }

    protected function sendWechatTemp(){
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

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_pay_success']['tem_key'], $h5_url, $send_data, $platform,$url);
        return $this;
    }
}
