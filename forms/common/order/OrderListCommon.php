<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单列表查询
 * Author: zal
 * Date: 2020-05-08
 * Time: 16:50
 */

namespace app\forms\common\order;

use app\core\BasePagination;
use app\models\BaseModel;
use app\models\BaseActiveQuery;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;

/**
 * @property Order $order
 * @property BaseActiveQuery $query
 * @property BasePagination $pagination
 */
class OrderListCommon extends BaseModel
{
    public $query;
    public $pagination;
    public $is_pagination;
    public $goods;

    public $mall_id;
    public $user_id;
    public $sign_id;
    public $all_mch;
    public $mch_id;
    public $limit;
    public $page;
    public $keyword;
    public $is_array;
    public $sort;
    public $status;
    public $sale_status;
    public $is_cancel_status;
    public $add_where = [];
    public $is_recycle;
    public $dateArr = [];
    public $orderType = [];
    /**
     * 关联关系
     * @var
     */
    public $is_detail;
    public $is_mch_order;
    public $is_user;
    public $is_goods;
    public $is_comment;
    public $is_refund;
    public $relations = [];

    public $only_express_order = 0; //只显示寄送订单
    public $only_offline_order = 0; //只显示核销订单
    public $only_offline_used  = 0; //只显示已使用的核销订单

    public $keywords;

    public function rules()
    {
        return [
            [['sign_id', 'keyword', 'keywords'], 'string'],
            [['mall_id', 'limit', 'mch_id', 'is_detail', 'is_refund', 'is_array', 'limit', 'sort',
                'is_pagination', 'is_mch_order', 'is_user', 'is_goods', 'all_mch', 'status','sale_status',
                'is_comment', 'user_id', 'is_recycle'], 'integer'],
            [['limit',], 'default', 'value' => 10],
            [['is_array'], 'default', 'value' => 0],
            [['page', 'is_pagination', 'sort'], 'default', 'value' => 1],
            [['add_where'], 'safe']
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     * 获取字段对应的设置sql方法
     */
    protected function getMethod($key)
    {
        $array = [
            'keyword' => 'setKeyword',
            'sort' => 'setSortWhere',
            'all_mch' => 'setAllMch',
            'mch_id' => 'setMchId',
            'user_id' => 'setUserId',
            'is_detail' => 'setWithDetail',
            'is_refund' => 'setWithRefund',
            'is_mch_order' => 'setWithMchOrder',
            'is_goods' => 'setWithGoods',
            'is_user' => 'setWithUser',
            'is_cancel_status' => 'setCancelStatus',
            'status' => 'setStatus',
            'sale_status' => 'setSaleStatus',
            'is_comment' => 'setWithComment',
            'sign_id' => 'setSignId',
            'add_where' => 'setAddWhere',
            'is_recycle' => 'setIsRecycle',
            'dateArr' => 'setDate',
            'relations' => 'setRelations',
            'keywords' => 'setKeywords',
        ];
        return isset($array[$key]) ? $array[$key] : null;
    }

    public function getQuery()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $this->query = $query = Order::find()->alias('o')->where([
//            'o.mall_id' => \Yii::$app->mall->id,
            'o.is_delete' => 0,
        ]);


        foreach ($this->attributes as $key => $value) {
            $method = $this->getMethod($key);
            if ($method && method_exists($this, $method) && $value !== null && $value !== "" && $value !== false) {
                $this->$method();
            }
        }

        $this->query->andWhere(['!=', 'o.sign', 'group_buy']);
        //$this->query->andWhere(['send_type' => 0]);

        if($this->only_offline_order){ //只显示核销订单
            $this->query->andWhere(["o.is_pay" => 1]);
            $this->query->andWhere(["IN", "o.order_type", ["offline_baopin", "offline_normal"]]);
            if(!$this->only_offline_used){
                $this->query->andWhere([
                    "AND",
                    ["o.is_send" => 0],
                    ["o.sale_status" => Order::SALE_STATUS_NO],
                    ["o.status" => Order::STATUS_WAIT_DELIVER]
                ]);
            }else{
                $this->query->andWhere("(o.is_send=1 OR o.sale_status <> '".Order::SALE_STATUS_NO."' OR o.status <> '".Order::STATUS_WAIT_DELIVER."')");
            }
        }else{ //只显示寄送订单
            $this->query->andWhere(["IN", "o.order_type", ["express_baopin", "express_normal"]]);
        }

        if ($this->is_pagination) {
            $this->query->page($this->pagination, $this->limit, $this->page);
        }

        return $this->query;
    }

    /**
     * 订单列表搜索
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public function search()
    {
        try {
            $this->getQuery();
            $list = $this->query->asArray($this->is_array)->groupBy('o.id')->all();
            /*foreach ($list as &$item) {
                if ($item['order_form']) {
                    $item['order_form'] = \Yii::$app->serializer->decode($item['order_form']);
                }
            }*/

            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function setKeyword()
    {
        $this->query->andWhere(['LIKE', 'o.order_no', $this->keyword]);
    }

    protected function setAllMch()
    {
        $this->query->andWhere(['>', 'o.mch_id', 0]);
    }

    protected function setMchId()
    {
        $this->query->andWhere(['o.mch_id' => $this->mch_id]);
    }

    protected function setUserId()
    {
        $this->query->andWhere(['o.user_id' => $this->user_id]);
    }

    protected function setCancelStatus()
    {
        $this->query->andWhere(['<>', 'o.cancel_status', 1]);
    }

    protected function setSignId()
    {
        $this->query->andWhere(['o.sign' => $this->sign_id]);
    }

    protected function setStatus()
    {
        switch ($this->order_status) {
            case 0:
                break;
            // 待付款
            case 1:
                // TODO 货到付款订单除外
                $this->query->andWhere(['o.is_pay' => 0])->andWhere(['!=', 'o.pay_type', 2]);
                break;
            // 待发货
            case 2:
                $this->query->andWhere(['o.is_send' => 0])->andWhere([
                    'or',
                    ['o.pay_type' => 2],
                    ['o.is_pay' => 1]
                ]);
                break;
            // 待收货
            case 3:
                $this->query->andWhere(['o.is_send' => 1, 'o.is_confirm' => 0]);
                break;
            // 待评价
            case 4:
                $this->query->joinWith(['comments c' => function ($query) {
                    $this->query->andWhere('ISNULL(c.id)');
                }])
                    ->andWhere(['o.is_confirm' => 1, 'is_sale' => 0, 'sale_status' => 0]);
                break;
            // 已取消
            case 6:
                $this->query->andWhere(['o.cancel_status' => 1]);
                break;
            // 取消待处理
            case 7:
                $this->query->andWhere(['o.cancel_status' => 2]);
                break;
            case 8:
                $this->query->andWhere(['is_sale' => 1,]);
            default:
                break;
        }
        return $this->query->andWhere(["o.status" => $this->status]);
    }

    protected function setSaleStatus(){
        return $this->query->andWhere(["o.sale_status" => $this->sale_status]);
    }

    protected function setWithRefund()
    {
        $this->query->with('detail.refund', 'refund');
    }

    protected function setWithDetail()
    {
        $this->query->with('detail');
    }

    protected function setWithMchOrder()
    {
        try {
            \Yii::$app->plugin->getPlugin('mch');
            $this->query->with('mchOrder');
        } catch (\Exception $exception) {
        }
    }

    protected function setWithUser()
    {
        $this->query->with('user');
    }

    protected function setWithGoods()
    {
        $this->query->with('detail.goods.goodsWarehouse');
    }

    protected function setWithComment()
    {
        $this->query->with('comments');
    }

    protected function setSortWhere()
    {
        switch ($this->sort) {
            case 1:
                $this->query->orderBy(['o.created_at' => SORT_DESC]);
                break;
            default:
        }
    }

    protected function setAddWhere()
    {
        $this->query->andWhere($this->add_where);
    }

    protected function setIsRecycle()
    {
        $this->query->andWhere(['is_recycle' => $this->is_recycle]);
    }

    protected function setDate()
    {
        if (isset($this->dateArr[0]) && $this->dateArr[0] && isset($this->dateArr[1]) && $this->dateArr[1]) {
            $this->query->andWhere(['>=', 'created_at', $this->dateArr[0] . ' 00:00:00']);
            $this->query->andWhere(['<=', 'created_at', $this->dateArr[1] . ' 23:59:59']);
        }
    }

    protected function setRelations()
    {
        $this->query->with($this->relations);
    }

    protected function setKeywords ()
    {
        $goodsWarehouseIds = GoodsWarehouse::find()->andWhere(['mall_id' => \Yii::$app->mall->id])
            ->andWhere(['like', 'name', $this->keywords])->select('id');
        $goodsIds = Goods::find()->andWhere(['goods_warehouse_id' => $goodsWarehouseIds])->select('id');
        $orderIds = OrderDetail::find()->andWhere(['goods_id' => $goodsIds])->select('order_id');
        $this->query->andWhere([
            'or',
            ['LIKE', 'o.order_no', $this->keywords],
            ['o.id' => $orderIds]
        ]);
    }
}
