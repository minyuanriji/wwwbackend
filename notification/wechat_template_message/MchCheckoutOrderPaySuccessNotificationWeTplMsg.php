<?php

namespace app\notification\wechat_template_message;

/***
 * 商户收款到账通知
 * Class MchCashNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $data
 * @property string $template_id
 */
class MchCheckoutOrderPaySuccessNotificationWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $data;
    public $template_id;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser'        => $this->openid,
            'template_id'   => $this->template_id,
            'data'          => $this->data,
        ]);

        return $res;
    }
}