<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 版权新增或编辑表单
 * Author: zal
 * Date: 2020-04-14
 * Time: 10:16
 */

namespace app\forms\mall\shop;


use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\models\BaseModel;

class CopyrightForm extends BaseModel
{
    public function getDetail()
    {
        $option = AppConfigLogic::getCoryRight();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
            ]
        ];
    }

    public function getDefault()
    {
        return [
            'pic_url' => '',
            'description' => '',
            'type' => '1',
            'link_url' => '',
            'mobile' => '',
            'link' => '',
            'status' => '1'
        ];
    }
}
