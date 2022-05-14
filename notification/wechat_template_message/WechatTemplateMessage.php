<?php
namespace app\notification\wechat_template_message;

use app\models\Wechat;
use yii\base\Component;

/***
 * 微信公众号模板消息
 * Class CashNotificationWeTplMsg
 * @package app\notification\wechat_template_message
 * @deprecated 后续用app\notification\wechat_template_message\WechatTemplateMessageNew
 */
abstract class WechatTemplateMessage extends Component{

    public $mall_id;

    public function init(){

        parent::init();

        $info = Wechat::findOne(['mall_id' => $this->mall_id, 'is_delete' => 0]);

        if($info){
            \Yii::$app->params['wechatConfig'] = [
                'app_id'  => $info->app_id,
                'secret'  => $info->secret,
                'token'   => $info->token,
                'aes_key' => $info->aes_key,
            ];
        }

    }

    abstract public function send();
}