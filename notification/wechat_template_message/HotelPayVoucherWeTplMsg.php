<?php
namespace app\notification\wechat_template_message;

/***
 * 酒店下单获取购物券微信公众号通知
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $data
 * @property string $template_id
 */
class HotelPayVoucherWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $template_id;
    public $data;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser' => $this->openid,//用户openid
            'template_id' => $this->template_id,//发送的模板id
            'data' => $this->data,
        ]);

        return $res;
    }
}