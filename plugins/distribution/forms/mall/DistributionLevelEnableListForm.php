<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-10
 * Time: 22:29
 */

namespace app\plugins\distribution\forms\mall;


use app\core\ApiCode;
use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\models\BaseModel;

class DistributionLevelEnableListForm extends BaseModel
{

    public function getList()
    {
        $list = DistributionLevelCommon::getEnableLevelList();
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data'=>['list' => $list]];
     }
}