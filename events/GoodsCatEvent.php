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

use app\models\GoodsCats;
use yii\base\Event;

class GoodsCatEvent extends Event
{
    /** @var GoodsCats */
    public $cats;

    public $catsList;

    public $isVipCardCats;
}
