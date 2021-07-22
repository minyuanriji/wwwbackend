<?php

namespace app\plugins\hotel\forms\mall\platform_setting;

use app\core\ApiCode;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class JinJiangSettingForm extends BaseModel
{
    public $account;
    public $secretKey;

    public function rules()
    {
        return [
            [['account', 'secretKey'], 'required'],
            [['account', 'secretKey'], 'string'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'account' => '账号',
            'secretKey' => '秘钥',
        ];
    }


    public function get()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $this->setting(),
        ];
    }

    public function setting()
    {
        $setting = OptionLogic::get(Option::NAME_HOTEL_JINJIANG, \Yii::$app->mall->id, Option::GROUP_HOTEL, $this->getDefault());
        return $setting;
    }

    public function getDefault()
    {
        return [
            'account' => '',
            'secretKey' => '',
        ];
    }


    public function set($data)
    {
        $option = OptionLogic::set(Option::NAME_HOTEL_JINJIANG, $data, \Yii::$app->mall->id, Option::GROUP_HOTEL);
        if ($option) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '保存失败'
            ];
        }
    }
}
