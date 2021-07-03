<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 控制台请求类
 * Author: zal
 * Date: 2020-05-05
 * Time: 14:56
 */

namespace app\core;

use yii\console\Request;

class ConsoleRequest extends Request
{
    public $enableCsrfCookie;

    public function getUserIp()
    {
        return '0.0.0.0';
    }

    public function getCsrfToken()
    {
        return null;
    }
}
