<?php

namespace app\notification\wechat_template_message;

use app\notification\TemConfig;

/***
 * 订单支付成功通过微信公众号通知
 * Class OrderPaymentSuccessNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $goods_name
 * @property string $title
 * @property string $order_no
 * @property string $store
 * @property int $pay_at
 * @property int $total_pay_price
 */
class OrderPaymentSuccessNotificationWeTplMsg extends WechatTemplateMessage
{
    public $title;
    public $openid;
    public $order_no;
    public $goods_name;
    public $total_pay_price;
    public $store;
    public $pay_at;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser' => $this->openid,
            'template_id' => TemConfig::OrderPlacedSuccess,
            'data' => [
                'first'    => $this->title,
                'keyword1' => $this->store,
                'keyword2' => $this->order_no,
                'keyword3' => $this->goods_name,
                'keyword4' => $this->total_pay_price,
                'keyword5' => date("Y-m-d H:i", $this->pay_at),
                'remark'   => '如有疑问请联系020-31923526'
            ],
        ]);

        return $res;
    }
}