<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件商城后台基础类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\controllers\mall;


use app\plugins\sign_in\Plugin;

class Controller extends \app\plugins\Controller
{
    public $sign;

    public function init()
    {
        parent::init();
        $this->sign = (new Plugin())->getName();
    }
}
