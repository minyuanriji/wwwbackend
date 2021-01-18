<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\forms\common\MallSettingForm;

class MchMallSettingForm extends BaseModel
{
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'], 'integer']
        ];
    }

    public function getSetting()
    {
        $setting = (new MallSettingForm())->search($this->mch_id);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
