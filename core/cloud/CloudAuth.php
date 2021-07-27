<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 云插件权限
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;

class CloudAuth extends CloudBase
{
    public $classVersion = '4.2.31';
    private $authInfo;

    public function getAuthInfo($refreshCache = false)
    {
        if ($this->authInfo) {
            return $this->authInfo;
        }
        $this->authInfo = $this->httpGet('/mall/site/info');
        return $this->authInfo;
    }
}
