<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-02
 * Time: 15:36
 */

namespace app\events;


use yii\base\Event;

class CommonOrderDetailEvent extends Event
{
    public $common_order_detail_id;
}