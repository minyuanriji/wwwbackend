<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信小程序插件
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;


class CloudWxapp extends CloudBase
{
    public $classVersion = '4.2.31';

    public function login($args)
    {
        return $this->httpGet('/mall/com-upload/login', $args);
    }

    public function preview($args)
    {
        return $this->httpGet('/mall/com-upload/preview', $args);
    }

    public function upload($args)
    {
        return $this->httpGet('/mall/com-upload/upload', $args);
    }
}
