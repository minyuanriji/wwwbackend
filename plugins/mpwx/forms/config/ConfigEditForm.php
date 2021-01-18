<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信小程序插件-微信基础配置操作
 * Author: zal
 * Date: 2020-04-20
 * Time: 17:50
 */

namespace app\plugins\mpwx\forms\config;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;
use app\plugins\mpwx\models\MpwxConfig;
use EasyWeChat\Kernel\Exceptions\HttpException;
use jianyan\easywechat\Wechat;
use yii\base\Exception;


class ConfigEditForm extends BaseModel
{
    public $app_id;
    public $secret;
    public $cert_pem;
    public $pay_secret;
    public $key_pem;
    public $mch_id;
    public $id;
    public $name;
    public $cert_pem_path;

    public $key_pem_path;


    public function rules()
    {
        return [
            [['app_id', 'secret', 'name'], 'required'],
            [['app_id', 'secret', 'key_pem', 'cert_pem', 'name'], 'string'],
            [['mch_id'], 'string', 'max' => 32],
            [['pay_secret',], 'string', 'max' => 255],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'app_id' => '小程序AppId',
            'secret' => '小程序appSecret',
            'pay_secret' => '微信支付Api密钥',
            'mch_id' => '微信支付商户号',
            'name' => '小程序名称'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }


        if ($this->cert_pem && $this->key_pem) {
            $pemDir = \Yii::$app->runtimePath . '/pem';
            make_dir($pemDir);
            $certPemFile = $pemDir . '/' . md5($this->cert_pem);
            if ($this->cert_pem) {
                file_put_contents($certPemFile, $this->cert_pem);
                $this->cert_pem_path = $certPemFile;
            }
            $keyPemFile = $pemDir . '/' . md5($this->key_pem);
            if ($this->key_pem) {
                file_put_contents($keyPemFile, $this->key_pem);
                $this->key_pem_path = $keyPemFile;
            }
        }
        // 检测参数是否有效
        $config = \Yii::$app->params['wechatMiniProgramConfig'];
        $config['app_id'] = $this->app_id;
        $config['secret'] = $this->secret;
        \Yii::$app->params['wechatMiniProgramConfig'] = $config;
        if (!$this->app_id) {
            throw new \Exception('小程序AppId有误');
        }
        if (!$this->secret) {
            throw new \Exception('小程序appSecret有误');
        }
        /** @var Wechat $wechat */
        $wechat = \Yii::$app->wechat;
        $app = $wechat->miniProgram;
        $accessToken = $app->access_token;
        try {
            $token = $accessToken->getToken(); // token 数组  token['access_token'] 字符串
            $token = $accessToken->getToken(true); // 强制重新从微信服务器获取 token.
        } catch (HttpException $e) {
            if ($e->formattedResponse['errcode'] == '41002') {
                $message = '小程序AppId有误(' . $e->formattedResponse['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $message,
                ];
            }
            if ($e->formattedResponse['errcode'] == '41013') {
                $message = '小程序AppId有误(' . $e->formattedResponse['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $message,
                ];
            }
            if ($e->formattedResponse['errcode'] == '40125') {
                $message = '小程序密钥有误(' . $e->formattedResponse['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $message,
                ];
            }
        }
        if ($this->mch_id || $this->pay_secret) {
            // 检测参数是否有效
            $config = \Yii::$app->params['wechatPaymentConfig'];
            $config['app_id'] = $this->app_id;
            $config['mch_id'] = $this->mch_id;
            $config['key'] = $this->pay_secret;
            \Yii::$app->params['wechatPaymentConfig'] = $config;
            $payment = \Yii::$app->wechat->payment;
            $res = $payment->order->queryByOutTradeNumber('88888888');
            if ($res && $res['return_code'] == 'FAIL') {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $res['return_msg'],
                ];
            }
            if (!$res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '保存失败',
                ];
            }
        }
        try {

            $config = null;
            if ($this->id) {
                $config = MpwxConfig::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
                if (!$config) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '数据异常,该条数据不存在',
                    ];
                }
            } else {
                $config = new MpwxConfig();
            }
            $config->attributes = $this->attributes;
            $config->mall_id = \Yii::$app->mall->id;
            $config->cert_pem_path = $this->cert_pem_path;
            $config->key_pem_path = $this->key_pem_path;
            if ($config->save()) {
                OptionLogic::set(Option::NAME_MPWX, $this->attributes, \Yii::$app->mall->id, Option::GROUP_APP);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
                'error' => $config->getErrors()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
