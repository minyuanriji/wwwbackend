<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 链接云插件
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;


class CloudCollect extends CloudBase
{
    public $classVersion = '4.2.31';

    public function collect($id)
    {
        $api = "/mall/copy/index";
        return $this->httpGet($api, ['vid' => $id]);
    }
}
