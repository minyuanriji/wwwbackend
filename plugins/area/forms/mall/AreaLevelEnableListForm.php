<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-10
 * Time: 22:29
 */

namespace app\plugins\area\forms\mall;


use app\core\ApiCode;
use app\plugins\area\forms\common\AreaLevelCommon;
use app\models\BaseModel;

class AreaLevelEnableListForm extends BaseModel
{
    public function getList()
    {
        $list = AreaLevelCommon::getEnableLevelList();
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['list' => $list]];
    }
}