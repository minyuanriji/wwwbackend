<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单详情查询
 * Author: zal
 * Date: 2020-05-11
 * Time: 10:50
 */

namespace app\forms\common\order;

use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Order;

/**
 * @property Order $order
 * @property \app\models\BaseActiveQuery $query
 * @property BasePagination $pagination
 */
class OrderDetailCommon extends BaseModel
{
    public $query;

    public $mall_id;
    public $mch_id;
    public $is_array;
    public $id;

    /** @var 关联关系 */
    public $is_detail;
    public $is_goods;
    public $is_user;
    public $is_refund;
    public $is_store;
    public $relations = [];
    public $is_vip_card;

    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'is_detail', 'is_goods', 'is_user', 'is_refund', 'id', 'is_array', 'is_store', 'is_vip_card'], 'integer'],
        ];
    }

    /**
     * 获取字段对应的sql条件方法
     * @param $key
     * @return mixed|null
     *
     */
    private function getMethod($key)
    {
        $array = [
            'mch_id' => 'setMchId',
            //'is_detail' => 'setWithDetail',
            //'is_goods' => 'setWithGoods',
            'is_user' => 'setWithUser',
            'is_refund' => 'setWithRefund',
            'is_store' => 'setWithStore',
            'relations' => 'setRelations',
            'is_vip_card' => 'setWithVipCard',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    /**
     * 搜索
     * @return array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $this->query = $query = Order::find()->alias('o')->where([
//                'o.mall_id' => \Yii::$app->mall->id,
                'o.is_delete' => 0,
                'o.id' => $this->id
            ]);
            foreach ($this->attributes as $key => $value) {
                $method = $this->getMethod($key);
                if ($method && method_exists($this, $method) && $value !== null && $value !== "") {
                    $this->$method();
                }
            }
            $order = $this->query->with(["detailExpress", "detail.orderGoodsConsumeVerification", "mch.store"])->asArray($this->is_array)->one();

            return $order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function setMchId()
    {
        $this->query->andWhere(['o.mch_id' => $this->mch_id]);
    }

    private function setWithStore()
    {
        $this->query->with('store');
    }

    //废弃
    private function setWithDetail()
    {
        $this->query->with('detail.order');
    }

    private function setWithUser()
    {
        $this->query->with('user');
    }

    //废弃
    private function setWithGoods()
    {
        $this->query->with('detail.goods.goodsWarehouse');
    }

    private function setWithRefund()
    {
        $this->query->with('detail.refund');
    }

    private function setRelations()
    {
        $this->query->with($this->relations);
    }

    private function setWithVipCard()
    {
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (in_array('vip_card',$permission) && $plugin) {
                $this->query->with('vipCardDiscount');
            }
        } catch (\Exception $e) {
            //throw $e;
        }
    }
}
