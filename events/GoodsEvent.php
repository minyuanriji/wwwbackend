<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-15
 * Time: 19:19
 */

namespace app\events;

use yii\base\Event;

class GoodsEvent extends Event
{
    public $goods;
    public $diffAttrIds;
    public $isVipCardGoods;
}
