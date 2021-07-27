<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 16:28
 */

namespace app\forms\mall\setting;


use app\core\ApiCode;
use app\forms\mall\option\RechargeSettingForm;
use app\models\BaseModel;
use app\models\Mall;

class MallForm extends BaseModel
{
    public function getDetail()
    {
        $mall = new Mall();
        $mall = $mall->getMallSetting();
        $rechargeForm = new RechargeSettingForm();
        $setting = $rechargeForm->setting();

        $mall['recharge'] = $setting;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $mall
            ],
        ];
    }

}