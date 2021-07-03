<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\forms\common\mch\MchSettingForm;
use app\models\BaseModel;

class SettingForm extends BaseModel
{
    public function getSetting()
    {
        try {
            $form = new MchSettingForm();
            $res = $form->search();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'setting' => $res,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
