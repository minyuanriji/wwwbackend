<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 导航图标
 * Author: zal
 * Date: 2020-09-14
 * Time: 14:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class ColorForm extends BaseModel
{
    public function getDetail()
    {
        $navbar = AppConfigLogic::getColor();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $navbar,
            ]
        ];
    }

    public function restoreDefault()
    {
        $res = OptionLogic::set(Option::NAME_COLOR, $this->getDefault(), \Yii::$app->mall->id, Option::GROUP_APP);

        if (!$res) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '恢复失败',
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '恢复成功',
        ];
    }

    public function getDefault()
    {
        return [
            'global_text_color' => '#bc0100',
        ];
    }
}
