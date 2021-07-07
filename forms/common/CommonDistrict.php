<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-16
 * Time: 18:24
 */

namespace app\forms\common;

use app\models\DistrictData;

class CommonDistrict
{
    public function search()
    {
        $d = new DistrictData();
        $arr = $d->getArr();
        $province_list = $d->getList($arr);
        $cache_key = md5('district');
        \Yii::$app->cache->set($cache_key, $province_list, 86400 * 7);
        return $province_list;
    }
}
