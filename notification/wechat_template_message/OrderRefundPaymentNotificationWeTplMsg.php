<?php

namespace app\notification\wechat_template_message;

use app\notification\TemConfig;

/***
 * 售后退款成功通知
 * Class OrderRefundPaymentNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $first
 * @property string $price
 */
class OrderRefundPaymentNotificationWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $first;
    public $price;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser'        => $this->openid,
            'template_id'   => TemConfig::OrderRefundSuccess,
            'data'          => [
                'first'     => $this->first,
                'keyword1'  => $this->price,
                'keyword2'  => '原路退回',
                'keyword3'  => '具体到账时间以收到时间为准',
                'remark'    => '若有疑问请拨打020-31923526',
            ],
        ]);
        return $res;
    }
}