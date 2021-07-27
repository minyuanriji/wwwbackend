<?php

namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Option;

class PlatformsGetSettingForm extends BaseModel{

    public function get(){

        $option = Option::findOne([
            "mall_id" => \Yii::$app->mall->id,
            "group"   => "hotel",
            "name"    => "platforms_setting"
        ]);

        $settingArray = @json_decode($option->value, true);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data'  => [
                'setting' => $settingArray
            ]
        ];
    }

}