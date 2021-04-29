<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_detail}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $goods_id
 * @property int $num 购买商品数量
 * @property string $unit_price 商品单价
 * @property string $total_original_price 商品原总价(优惠前)
 * @property string $total_price 商品总价(优惠后)
 * @property string $member_discount_price 会员优惠金额(正数表示优惠，负数表示加价)
 * @property string $goods_info 购买商品信息
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_refund 是否退款
 * @property int $refund_status 售后状态 0--未售后 1--售后中 2--售后结束
 * @property string $back_price 后台优惠(正数表示优惠，负数表示加价)
 * @property string $sign 订单详情标识，用于区分插件
 * @property string $goods_no 商品货号
 * @property int $use_user_coupon_id
 * @property int $coupon_discount_price
 * @property Goods $goods
 * @property DistributionOrder $area
 * @property OrderRefund $refund
 * @property GoodsWarehouse $goodsWarehouse
 * @property Order $order
 * @property string $refundStatusText 售后状态文字
 * @property GoodsCards[] $card
 * @property $userCards
 * @property string $form_data 自定义表单提交的数据
 * @property int $form_id 自定义表单的id
 * @property OrderDetailExpressRelation $expressRelation
 */
class OrderDetail extends BaseActiveRecord
{
    /** @var int 是否退款 1是 */
    const IS_REFUND_YES = 1;
    /** @var int 是否退款 0否 */
    const IS_REFUND_NO = 0;


    const REFUND_STATUS_NO_SALES = 0; //未售后

    const REFUND_STATUS_SALES = 10; //售后中,待处理

    const REFUND_STATUS_SALES_AGREE = 11; //售后中,仅退款,已同意

    const REFUND_STATUS_SALES_SEND_AGREE = 12; //售后中,退款退货,已同意

    const REFUND_STATUS_SALES_END_PAY = 20; //完成售后,已退款

    const REFUND_STATUS_SALES_END_REJECT = 21; //完成售后,已拒绝

    //允许发放积分的订单详情的售后状态
    const ALLOW_ADD_SCORE_REFUND_STATUS = [
        self::REFUND_STATUS_NO_SALES, self::REFUND_STATUS_SALES_END_REJECT
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'num', 'unit_price', 'total_original_price', 'total_price', 'member_discount_price', 'goods_info', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['order_id', 'goods_id', 'num', 'is_delete', 'is_refund', 'refund_status', 'form_id', 'use_user_coupon_id', 'integral_fee_rate', 'is_score_send'], 'integer'],
            [['unit_price', 'total_original_price', 'total_price', 'member_discount_price', 'back_price', 'coupon_discount_price', 'use_score_price', 'use_score', 'score_price', 'full_relief_price', 'integral_price'], 'number'],
            [['goods_info', 'goods_no', 'form_data'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['sign'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'order_id'              => 'Order ID',
            'goods_id'              => 'Goods ID',
            'num'                   => '购买商品数量',
            'unit_price'            => '商品单价',
            'total_original_price'  => '商品原总价(优惠前)',
            'total_price'           => '商品总价(优惠后)',
            'member_discount_price' => '会员优惠金额(正数表示优惠，负数表示加价)',
            'goods_info'            => '购买商品信息',
            'is_delete'             => 'Is Delete',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At',
            'deleted_at'            => 'Deleted At',
            'is_refund'             => '是否退款',
            'refund_status'         => '售后状态 0--未售后 10--售后中,待处理 11--售后中,仅退款,已同意;12--售后中,退款退货,已同意;20--完成售后,已退款;21--完成售后,已拒绝',
            'back_price'            => '后台优惠(正数表示优惠，负数表示加价)',
            'sign'                  => '订单详情标识，用于区分插件',
            'goods_no'              => '商品货号',
            'form_data'             => '自定义表单提交的数据',
            'form_id'               => '自定义表单的id',
            'use_user_coupon_id'    => '优惠券id',
            'coupon_discount_price' => '优惠券价格',
            'full_relief_price'     => '满减金额',
            'integral_price'        => '红包券抵扣金额',
        ];
    }

    /**
     * @param null $goodsInfo
     * @return string
     * @throws \Exception
     */
    public function encodeGoodsInfo($goodsInfo = null)
    {
        if (!$goodsInfo) {
            $goodsInfo = $this->goods_info;
        }
        if (!$goodsInfo) {
            throw new \Exception('goodsInfo不能为空。');
        }
        return Yii::$app->serializer->encode($goodsInfo);
    }

    /**
     * @param null $goodsInfo
     * @return \ArrayObject|mixed
     * @throws \Exception
     */
    public function decodeGoodsInfo($goodsInfo = null)
    {
        if (!$goodsInfo) {
            $goodsInfo = $this->goods_info;
        }
        if (!$goodsInfo) {
            throw new \Exception('goodsInfo不能为空。');
        }
        return Yii::$app->serializer->decode($goodsInfo);
    }

    public function getGoodsCard()
    {
        return $this->hasMany(GoodsCardRelation::className(), ['goods_id' => 'goods_id'])->where(['is_delete' => 0]);
    }

    public function getCard()
    {
        return $this->hasMany(GoodsCards::className(), ['id' => 'card_id'])
            ->via('goodsCard');
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getRefund()
    {
        return $this->hasOne(OrderRefund::className(), ['order_detail_id' => 'id']);
    }

    public function getUserCards()
    {
        return $this->hasMany(UserCard::className(), ['order_detail_id' => 'id']);
    }

    public function getShare()
    {
        return $this->hasOne(DistributionOrder::className(), ['order_detail_id' => 'id']);
    }

    public function getGoodsWarehouse()
    {
        return $this->hasOne(GoodsWarehouse::className(), ['id' => 'goods_warehouse_id'])
            ->via('goods');
    }

    public function getExpressRelation()
    {
        return $this->hasOne(OrderDetailExpressRelation::className(), ['order_detail_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getRefundStatusText()
    {
        if ($this->refund_status == OrderDetail::REFUND_STATUS_NO_SALES) {
            $refundStatusText = '未售后';
        } elseif ($this->refund_status == OrderDetail::REFUND_STATUS_SALES or $this->refund_status == OrderDetail::REFUND_STATUS_SALES_AGREE) {
            $refundStatusText = '售后申请中';
        } elseif ($this->refund_status == OrderDetail::REFUND_STATUS_SALES_END_PAY or $this->refund_status == OrderDetail::REFUND_STATUS_SALES_END_REJECT) {
            $refundStatusText = '售后完成';
        } else {
            $refundStatusText = '位置状态';
        }
        return $refundStatusText;
    }

    public function getOrderRefund()
    {
        return $this->hasOne(OrderRefund::className(), ['order_detail_id' => 'id']);
    }

    /**
     * 获取同一类型商品的购买次数
     * @param $params
     * @param $fields 字段
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getSameCatsGoodsOrderTotal($params, $fields = [])
    {
        $returnData = [];
        $query      = self::find()->alias("od");
        if (isset($params["order"])) {
            $query->leftJoin(['ord' => Order::tableName()], 'ord.id = od.order_id');
        }
        $query->where(["is_delete" => self::NO]);
        if (isset($params["id"]) && !empty($params["id"])) {
            $params["is_one"] = 1;
            $query->andWhere(["od.id" => $params["id"]]);
        }
        if (isset($params["mall_id"]) && !empty($params["mall_id"])) {
            $query->andWhere(["ord.mall_id" => $params["mall_id"]]);
        }
        if (isset($params["user_id"]) && !empty($params["user_id"])) {
            $query->andWhere(["ord.user_id" => $params["user_id"]]);
        }
        if (isset($params["goods_id"]) && !empty($params["goods_id"])) {
            $query->andWhere(["od.goods_id" => $params["goods_id"]]);
        }
        if (isset($params["is_pay"]) && !empty($params["is_pay"])) {
            $query->andWhere(["ord.is_pay" => $params["is_pay"]]);
        }
        //排序
        $orderByColumn = isset($params["sort_key"]) ? $params["sort_key"] : "id";
        $orderByType   = isset($params["sort_val"]) ? $params["sort_val"] : " desc";
        $orderBy       = $orderByColumn . " " . $orderByType;
        if (!empty($fields)) {
            $query->select($fields);
        }
        if (isset($params["return_count"])) {
            return $query->count();
        }

        if (isset($params["group_by"])) {
            $query->groupBy($params["group_by"]);
        }

        $pagination = null;
        if (isset($params["limit"]) && isset($params["page"])) {
            $query->page($pagination, $params['limit'], $params['page']);
        }
        if (isset($params["user"])) {
            $query->with(["user"]);
        }
        $query->asArray()->orderBy($orderBy);
        if (isset($params["is_one"]) && $params["is_one"] == 1) {
            $list       = $query->one();
            $returnData = $list;
        } else {
            $list = $query->all();
            if (isset($params["limit"]) && isset($params["page"])) {
                $returnData["list"]       = $list;
                $returnData["pagination"] = $pagination;
            } else {
                $returnData = $list;
            }
        }
        return $returnData;
    }

    public function getOrderGoodsConsumeVerification(){
        return $this->hasOne(OrderGoodsConsumeVerification::className(), ['order_detail_id' => 'id']);
    }
}
