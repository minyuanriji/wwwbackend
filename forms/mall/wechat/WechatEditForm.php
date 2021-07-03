<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 16:29
 */

namespace app\forms\mall\wechat;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Wechat;

class WechatEditForm extends BaseModel
{


    public $name;
    public $app_id;
    public $secret;
    public $token;
    public $aes_key;
    public $qrcode;


    public function rules()
    {
        return [
            [['app_id', 'secret', 'token', 'name', 'aes_key'], 'required'],
            [['app_id', 'name'], 'string', 'max' => 45],
            [['secret', 'token', 'aes_key'], 'string', 'max' => 64],
            [['qrcode'], 'string', 'max' => 255],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            $wechat = Wechat::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);
            if (!$wechat) {
                $wechat = new Wechat();
                $wechat->mall_id = \Yii::$app->mall->id;
            }
            $wechat->name = $this->name;
            $wechat->app_id = $this->app_id;
            $wechat->secret = $this->secret;
            $wechat->name = $this->name;
            $wechat->qrcode = $this->qrcode;
            $wechat->token = $this->token;
            $wechat->aes_key = $this->aes_key;
            $res = $wechat->save();

            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败',
                'error' => $this->responseErrorMsg($wechat)
            ];


        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }


}