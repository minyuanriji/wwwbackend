<?php

namespace app\notification\wechat_template_message;

use app\notification\TemConfig;

/***
 * 用户提现通知
 * Class OrderRefundSuccessNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $price
 * @property string $goods_name
 * @property string $order_no
 * @property string $remark
 */
class OrderRefundSuccessNotificationWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $price;
    public $goods_name;
    public $order_no;
    public $remark;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser'        => $this->openid,
            'template_id'   => TemConfig::OrderRefundSuccess,
            'data'          => [
                'first'     => '商家已同意您的退款申请',
                'keyword1'  => $this->price,
                'keyword2'  => $this->goods_name,
                'keyword3'  => $this->order_no,
                'remark'    => $this->remark,
            ],
        ]);
        return $res;
    }
}