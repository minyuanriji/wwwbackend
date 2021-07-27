<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-15
 * Time: 10:44
 */

namespace app\events;


use yii\base\Event;

class RelationChangeEvent extends Event
{

    public $userId;
    public $parentId;
    public $beforeParentId;
    public $mall;


}