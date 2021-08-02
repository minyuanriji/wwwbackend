<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件接口基础表单类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\api;

use app\models\User;
use app\plugins\sign_in\forms\BaseModel;

/**
 * @property User $user
 */
class ApiModel extends BaseModel
{
    protected $user;

    public function setUser($val)
    {
        $this->user = $val;
    }
}
