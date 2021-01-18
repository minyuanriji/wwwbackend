<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色基础设置
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:19
 */

namespace app\forms\mall\shop\role_setting;

use app\core\ApiCode;
use app\models\BaseModel;

class RoleSettingForm extends BaseModel
{
    public function getDetail()
    {
        $form = new \app\forms\common\RoleSettingForm();
        $setting = $form->getSettingInfo();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
