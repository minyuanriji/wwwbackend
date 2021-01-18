<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-10
 * Time: 22:29
 */

namespace app\plugins\boss\forms\mall;


use app\core\ApiCode;
use app\plugins\boss\forms\common\BossLevelCommon;
use app\models\BaseModel;

class BossLevelEnableListForm extends BaseModel
{
    public function getList()
    {
        $list = BossLevelCommon::getEnableLevelList();
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['list' => $list]];
    }
}