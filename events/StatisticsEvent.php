<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/21
 * Time: 17:23
 */
namespace app\events;

use yii\base\Event;

class StatisticsEvent extends Event
{
    public $mall_id;
    public $browse_type;//浏览类型：0：首页 1：分类 2：商品详情
    public $user_id;//用户id
    public $user_ip;//用户ip
}