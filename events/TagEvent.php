<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 标签事件
 * Author: zal
 * Date: 2020-08-03
 * Time: 9:49
 */

namespace app\events;


use yii\base\Event;

class TagEvent extends Event
{
    //类型1价值分层2生命周期3营销偏好4行为偏好
    public $type;
    //行为动作，如下单，收藏，点赞等
    public $action;
    //分类id
    public $cat_id;
    public $user_id;
    public $mall_id;
}