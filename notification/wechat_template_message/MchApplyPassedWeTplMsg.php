<?php
namespace app\notification\wechat_template_message;

/***
 * 商家申请通过微信公众号通知
 * Class MchApplyPassedWeTplMsg
 * @package app\notification\wechat_template_message
 * @property string $openid
 * @property string $name
 * @property string $nickname
 * @property int $updated_at
 * @property int $user_id
 */
class MchApplyPassedWeTplMsg extends WechatTemplateMessage
{
    public $openid;
    public $name;
    public $nickname;
    public $updated_at;
    public $user_id;

    public function send()
    {
        $wechatModel = \Yii::$app->wechat;
        $res = $wechatModel->app->template_message->send([
            'touser' => $this->openid,//用户openid
            'template_id' => 'P7xEjRG_Mmo-daLn2WVT7VBS8KXEJ1p3Np7nu26v_IQ',//发送的模板id
            //'url' => 'https://', //发送后用户点击跳转的链接
            'data' => [
                'first'    => '店铺审核已通过',
                'keyword1' => $this->name,
                'keyword2' => $this->nickname . "[".$this->user_id."]",
                'keyword3' => date("Y-m-d H:i", $this->updated_at),
                'remark'   => '如有疑问请联系020-31923526'
            ],
        ]);
        print_r($res);
        echo "\n";
        return $res;
    }
}