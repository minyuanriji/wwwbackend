<?php

namespace app\plugins\mch\forms\common;


use app\models\Mall;
use app\models\BaseModel;
use app\plugins\mch\models\MchMallSetting;

/**
 * @property Mall $mall
 */
class MallSettingForm extends BaseModel
{
    public function search($mchId)
    {
        $setting = MchMallSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchId
        ])->asArray()->one();

        if (!$setting) {
            $setting = $this->getDefault();
        }

        $setting['is_share'] = (int)$setting['is_share'];

        return $setting;
    }

    private function getDefault()
    {
        return [
            'is_share' => 0,
        ];
    }
}
