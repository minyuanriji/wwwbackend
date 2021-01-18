<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理连续签到公共基础类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\continue_type;

use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\forms\BaseModel;

/**
 * @property Common $common;
 */
abstract class BaseState extends BaseModel
{
    public $common;

    abstract public function setJob();

    abstract public function clearContinue();
}
