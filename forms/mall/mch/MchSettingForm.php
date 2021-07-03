<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商户设置
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\forms\mall\mch;

use app\core\ApiCode;
use app\forms\common\mch\MchMallSettingForm;
use app\forms\common\mch\SettingForm;
use app\models\BaseModel;

class MchSettingForm extends BaseModel
{
    public function getSetting()
    {
        $setting = (new SettingForm())->search();
        $mchMallSetting = (new MchMallSettingForm())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $setting,
                'mchMallSetting' => $mchMallSetting
            ]
        ];
    }

    public function getMchMallSetting()
    {
        $setting = (new MchMallSettingForm())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'mch_mall_setting' => $setting
            ]
        ];
    }


    public function getMchSetting()
    {
        try {
            $form = new \app\forms\common\mch\MchSettingForm();
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
