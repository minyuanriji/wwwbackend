<?php

namespace app\plugins\hotel\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\Option;

class HotelSettingForm extends BaseModel
{
    public function getDetail($param)
    {

        $option = Option::findOne([
            'name'      => $param,
            'mall_id'   => \Yii::$app->mall->id,
            'group'     => Option::GROUP_HOTEL
        ]);
        if ($option) {
            $value = SerializeHelper::decode($option->value);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => $value ? $value : [],
        ];
    }

}