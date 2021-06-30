<?php

namespace app\notification\wechat_template_message;

/***
 * 用户提现通知
 * Class CashNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $data
 * @property string $template_id
 */
class CashNotificationWeTplMsg extends WechatTemplateMessage
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