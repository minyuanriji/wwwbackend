<?php

namespace app\notification\wechat_template_message;

use app\notification\TemConfig;

/***
 * 用户提现通知
 * Class OrderRefundRefuseNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $first
 * @property string $refund_no
 * @property string $order_no
 * @property string $remark
 * @property string $reasons_refusal
 */
class OrderRefundRefuseNotificationWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $first;
    public $refund_no;
    public $order_no;
    public $reasons_refusal;
    public $remark;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser'        => $this->openid,
            'template_id'   => TemConfig::OrderRefundSuccess,
            'data'          => [
                'first'     => $this->first,
                'keyword1'  => $this->order_no,
                'keyword2'  => $this->refund_no,
                'keyword3'  => $this->reasons_refusal,
                'remark'    => $this->remark,
            ],
        ]);
        return $res;
    }
}