<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Json服务类
 * Author: xuyaoxiang
 * Date: 2020/10/12
 * Time: 10:16
 */

namespace app\services\MallSetting;

class JsonService
{
    /**
     * 判断返回数据是否json
     * @param $string
     * @return bool
     */
    static public function is_json($string)
    {
        if(!is_string($string)){
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}