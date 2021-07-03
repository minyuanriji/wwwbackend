<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-10
 * Time: 22:29
 */

namespace app\plugins\agent\forms\mall;


use app\core\ApiCode;
use app\plugins\agent\forms\common\AgentLevelCommon;
use app\models\BaseModel;

class AgentLevelEnableListForm extends BaseModel
{
    public $level;
    public function getList()
    {
        $list = AgentLevelCommon::getEnableLevelList($this->level);
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['list' => $list]];
    }
}