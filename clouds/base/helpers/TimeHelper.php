<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 17:33
 */
namespace app\clouds\base\helpers;

class TimeHelper
{
    /**
     * 获取时间戳
     * @param string $unit
     * @return int
     */
    final static function timestamp($unit = "second")
    {
        return time();
    }
}