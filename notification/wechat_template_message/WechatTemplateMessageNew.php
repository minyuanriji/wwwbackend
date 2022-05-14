<?php
namespace app\notification\wechat_template_message;

use app\models\Wechat;
use yii\base\Component;

/***
 * 发送微信公众号模板消息
 */
abstract class WechatTemplateMessageNew extends Component{

    public $mall_id;

    public static $wechatConfig = [];

    public function init(){

        parent::init();

        if(!isset(static::$wechatConfig[$this->mall_id]) || !static::$wechatConfig[$this->mall_id]){
            static::$wechatConfig[$this->mall_id] = Wechat::findOne(['mall_id' => $this->mall_id, 'is_delete' => 0]);
        }

        $info = static::$wechatConfig[$this->mall_id];

        if($info){
            \Yii::$app->params['wechatConfig'] = [
                'app_id'  => $info->app_id,
                'secret'  => $info->secret,
                'token'   => $info->token,
                'aes_key' => $info->aes_key,
            ];
        }

    }

    /**
     * 发送公众号模板消息
     * @return void
     */
    public function send($openid){
        $miniprogram = $this->miniprogram();
        $option = [
            'touser'        => $openid,
            'template_id'   => $this->templateId(),
            'data'          => $this->data(),
        ];
        if(!empty($miniprogram)){
            $option['miniprogram'] = $miniprogram;
        }
        $res = \Yii::$app->wechat->app->template_message->send($option);
    }


    /**
     * 数据内容
     * @return array
     */
    abstract protected function data();

    /**
     * 模板ID
     * @return string
     */
    abstract protected function templateId();

    /**
     * 跳转小程序
     * @return string
     */
    protected function miniprogram(){
        return '';
    }
}