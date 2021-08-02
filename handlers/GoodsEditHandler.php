<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品编辑
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */


namespace app\handlers;


use app\events\OrderEvent;
use app\models\BaseModel;
use app\models\Goods;

class GoodsEditHandler extends BaseModel
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Goods::EVENT_EDIT, function ($event) {
            /** @var OrderEvent $event */
        });
    }
}
