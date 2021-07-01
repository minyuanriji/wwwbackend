<?php
namespace app\notification\wechat_template_message;

/***
 * 商家申请通过微信公众号通知
 * Class MchApplyPassedWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $data
 * @property string $template_id
 */
class MchApplyPassedWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $template_id;
    public $data;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser' => $this->openid,//用户openid
<<<<<<< HEAD
            'template_id' => 'P7xEjRG_Mmo-daLn2WVT7VBS8KXEJ1p3Np7nu26v_IQ',//发送的模板id
            //'url' => 'https://', //发送后用户点击跳转的链接
            'data' => [
                'first'    => '您申请的店铺审核已通过',
                'keyword1' => $this->name,
                'keyword2' => $this->nickname . "[".$this->user_id."]",
                'keyword3' => date("Y-m-d H:i", $this->updated_at),
                'remark'   => '如有疑问请联系020-31923526'
            ],
=======
            'template_id' => $this->template_id,//发送的模板id
            'data' => $this->data,
>>>>>>> 5f84071819a097f8f1ffaa3c50a6b111850baabe
        ]);

        return $res;
    }
}