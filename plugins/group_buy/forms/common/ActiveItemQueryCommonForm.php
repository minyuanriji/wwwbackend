<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼单记录查询form
 * Author: xuyaoxiang
 * Date: 2020/9/4
 * Time: 18:00
 */

namespace app\plugins\group_buy\forms\common;

use app\models\BaseModel;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;
use app\models\Order;

class ActiveItemQueryCommonForm extends BaseModel
{
    public $page;
    public $limit;
    public $active_id;
    public $is_creator;
    public $attr_id;
    public $mall_id;
    public $order_no;
    public $is_page=true;
    public $cancel_status;
    public $as_array = true;
    private $pagination = null;

    public function rules()
    {
        return [
            [['page', 'limit', 'active_id', 'cancel_status','mall_id'], 'integer'],
            [['active_id'], 'required', 'on' => 'list'],
            [['order_no'], 'string']
        ];
    }

    protected function queryData()
    {
        if (!$this->mall_id) {
            $this->mall_id = \Yii::$app->mall->id;
        }

        $query = PluginGroupBuyActiveItem::find()
            ->alias('ai')
            ->leftJoin(['o' => Order::tableName()], 'o.id=ai.order_id')
            ->with('user')
            ->with('order')
            ->where(['ai.mall_id' => $this->mall_id]);

        if ($this->active_id) {
            $query->andWhere(['ai.active_id' => $this->active_id]);
        }

        if (is_numeric($this->cancel_status)) {
            $query->andWhere(['o.cancel_status' => $this->cancel_status]);
        }

        $query->asArray($this->as_array);

        return $query;
    }

    public function returnAll()
    {
        $query = $this->queryData();

        if ($this->is_page) {
            $query->page($this->pagination, $this->limit, $this->page);
        }

        $all = $query->all();

        return $all;
    }

    /**
     * 对外返回数据
     * @param $all
     * @return array
     */
    protected function returnData($all)
    {
        return $this->returnApiResultData(0, "", [
            'list'       => $all,
            'pagination' => $this->pagination
        ]);
    }

    /**
     *
     * 拼团详情连订单表查询
     */
    public function queryJoinOrder()
    {
        $query = PluginGroupBuyActiveItem::find()
            ->alias('item')
            ->leftJoin(Order::tableName() . ' o', 'o.id=item.order_id')
            ->select("item.*");

        if ($this->order_no) {
            $query->andWhere(['o.order_no' => $this->order_no]);
        }

        $query->asArray($this->as_array);

        return $query;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public function returnJoinOrderOne()
    {
        $query = $this->queryJoinOrder();

        $data = $query->one();

        return $this->returnApiResultData(0, "返回成功", $data);
    }
}