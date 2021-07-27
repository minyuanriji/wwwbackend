<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 20:30
 */

namespace app\plugins\distribution\events;


use yii\base\Event;

class DistributionInsertEvent extends Event
{
    public $user_id;
    public $mall_id;

}