<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 14:49
 */

namespace app\plugins\group_buy\forms\common;

use app\forms\common\order\OrderListCommon as ParentOrderListCommon;
use app\plugins\group_buy\models\Order;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;
use app\plugins\group_buy\models\PluginGroupBuyActive;

class OrderListCommon extends ParentOrderListCommon
{
    public $active_status;

    public function rules()
    {
        $return = [
            [['active_status'], 'integer']
        ];
        return array_merge($return, parent::rules());
    }

    public function getQuery()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $this->query = $query = Order::find()
            ->alias('o')
            ->leftJoin(PluginGroupBuyActiveItem::tableName() . " ai", "ai.order_id=o.id")
            ->leftJoin(PluginGroupBuyActive::tableName() . " a", "a.id=ai.active_id")
            ->with('activeItem')
            ->with('active')
            ->where([
                'o.mall_id'   => \Yii::$app->mall->id,
                'o.is_delete' => 0,
                'o.sign'      => 'group_buy'
            ]);

        if ($this->active_status) {
            $this->query->andWhere(['a.status' => $this->active_status]);
        }

        foreach ($this->attributes as $key => $value) {
            $method = $this->getMethod($key);
            if ($method && method_exists($this, $method) && $value !== null && $value !== "" && $value !== false) {
                $this->$method();
            }
        }

        if ($this->is_pagination) {
            $this->query->page($this->pagination, $this->limit, $this->page);
        }

        return $this->query;
    }

    protected function setSaleStatus(){
        return $this->query->andWhere(["o.sale_status" => $this->sale_status]);
    }
}