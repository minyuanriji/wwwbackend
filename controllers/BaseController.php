<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-06
 * Time: 17:36
 */

namespace app\controllers;

use app\core\ApiCode;
use app\helpers\WechatHelper;
use app\logic\OptionLogic;
use app\models\Option;
use app\models\Wechat;
use app\plugins\mpwx\models\MpwxConfig;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class BaseController
 * @package app\controllers
 * @Notes 基础控制器负责获取一些常用的变量，例如mall_id
 */
class BaseController extends Controller
{
    public $enableCsrfValidation = false;
    public $mall_id;//商城id
    public $pageSize = 10;
    public function init()
    {
        header("Origin:*");
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Request-Headers:*");
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Request-Method:*");
        header("Access-Control-Allow-Headers: Origin,  X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie, Cache-Control,Authorization");

        $this->mall_id = \Yii::$app->getMallId();
        if (!$this->mall_id) {
       //     $this->mall_id = \Yii::$app->getSessionJxMallId();
            if (!$this->mall_id) {
                $headers = \Yii::$app->request->headers;
                if(isset($headers["x-mall-id"]) && !empty($headers["x-mall-id"])){
                    $mallId = isset($headers["x-mall-id"]) ? $headers["x-mall-id"] : 0;
                }else{
                    $mallId = \Yii::$app->request->get('mall_id');
                }
                $this->mall_id = $mallId; //加多一层判断连接是否存在mall_id
                if (!$this->mall_id) {
                    //微信支付回调返回商城id
                    \Yii::$app->response->format = Response::FORMAT_XML;
                    $xml = \Yii::$app->request->rawBody;
                    $res = WechatHelper::xmlToArray($xml);
                    \Yii::error("baseController res=".var_export($res,true));
                    if(isset($res["attach"])){
                        $this->mall_id = $res["attach"];
                    }else{
                        $this->mall_id = 0;
                        return ['code' => ApiCode::CODE_MALL_NOT_EXIST, 'msg' => '商城不存在'];
                    }
                }
            }
        }
        /**
         * 这里不管有没有设置mallId 都刷新一遍，会不会有什么问题
         */
        \Yii::$app->setMallId($this->mall_id);
        //\Yii::$app->setSessionJxMallId($this->mall_id);
        $this->setWechatParmas($this->mall_id, \Yii::$app->request->get('stands_mall_id'));
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-20
     * @Time: 18:12
     * @Note:设置微信公众号，微信支付
     */
    protected function setWechatParmas($mall_id, $stands_mall_id)
    {
/*        if (!$mall_id) {
            return;
        }*/
        if ($stands_mall_id) {
            $info = MpwxConfig::findOne(['mall_id' => $mall_id, 'is_delete' => 0]);
            if ($info) {
                \Yii::$app->params['wechatConfig'] = [
                    'app_id' => $info->app_id,
                    'secret' => $info->secret,
                ];
            }
        } elseif ($mall_id) {
            $info = Wechat::findOne(['mall_id' => $mall_id, 'is_delete' => 0]);
            if ($info) {
                \Yii::$app->params['wechatConfig'] = [
                    'app_id' => $info->app_id,
                    'secret' => $info->secret,
                    'token' => $info->token,
                    'aes_key' => $info->aes_key,
                ];
            }
        } else {
            return;
        }
        $payment = OptionLogic::get(Option::NAME_PAYMENT, $mall_id, Option::GROUP_APP);
        if (!empty($payment) && $payment["wechat_status"] == 1) {
            \Yii::$app->params['wechatPaymentConfig'] = [
                'app_id' => $payment['wechat_app_id'],
                'mch_id' => $payment['wechat_mch_id'],
                'key' => $payment['wechat_pay_secret'],
                'cert_path' => $payment['wechat_cert_pem_path'],
                'key_path' => $payment['wechat_key_pem_path'],
                'notify_url' => ''//回调地址
            ];
        }
        $mpwx = OptionLogic::get(Option::NAME_MPWX, $mall_id, Option::GROUP_APP);
        if (!empty($mpwx)) {
            \Yii::$app->params['wechatMiniProgramConfig'] = [
                'app_id' => $mpwx['app_id'],
                'secret' => $mpwx['secret'],
                'mch_id' => $mpwx['mch_id'],
                'key' => $mpwx['pay_secret'],
                'cert_path' => $mpwx['cert_pem_path'],
                'key_path' => $mpwx['key_pem_path'],
                'notify_url' => ''//回调地址
            ];
        }
    }

    

    /**
     * 请求成功统消息格式化处理
     * @param array $url
     * @param string $msg
     * @param array $data
     * @return void
     */
    public function success($msg='success',$data=[],$url=[]){
        return $this->asJson(array(
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => $msg,
            'data' => $data
        ));
    }

    /**
     * 请求失败消息格式化处理
     * @param array $url
     * @param string $msg
     * @param array $data
     * @return void
     */
    public function error($msg='failed',$data=[],$url=[]){
        return $this->asJson(array(
            'code' => ApiCode::CODE_FAIL,
            'msg' => $msg,
            'data' => $data
        ));
    }
}