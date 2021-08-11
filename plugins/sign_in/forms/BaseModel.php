<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-商城后台基础model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\forms;

use app\models\Mall;

/**
 * @property Mall $mall
 */
class BaseModel extends \app\models\BaseModel
{
    protected $mall;

    public function setMall($val)
    {
        $this->mall = $val;
    }
}
