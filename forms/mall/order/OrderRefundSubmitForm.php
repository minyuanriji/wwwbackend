<?php

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use yii\db\Query;

class OrderRefundSubmitForm extends BaseModel
{
    public $order_detail_id;// 订单详情ID
    /** @var 售后类型0退款1退款退货2换货 */
    public $type;
    public $refund_price;
    public $remark;
    public $pic_list;
    public $reason;
    /** @var int 货物状态，是否收到货 */
    public $is_receipt = -1;
    /** @var int 退货方式0快递配送 */
    public $refund_type = 0;
    public $is_backstage;

    public $refund_total_price;

    public function rules()
    {
        return [
            [['order_detail_id', 'type', 'refund_price'], 'required'],
            [['order_detail_id','is_receipt', 'type', 'is_backstage'], 'integer'],
            [['refund_price'],'number'],
            [['remark'], 'string'],
            [['refund_total_price'],'safe']
        ];
    }

    /**
     * 退款申请
     * @return array
     */
    public function submit()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            $this->checkIsRefund();
            if ($this->type <= OrderRefund::TYPE_REFUND_RETURN && !$this->refund_price) {
                throw new \Exception('退款金额需大于0');
            }

            if($this->type == OrderRefund::TYPE_ONLY_REFUND && ($this->is_receipt == -1 || $this->is_receipt === "")){
                return $this->returnApiResultData(1,'请选择货物状态');
            }

            /** @var OrderDetail $orderDetail */
            $orderDetail = OrderDetail::find()->where([
                'id' => $this->order_detail_id,
                'is_delete' => 0
            ])->with(['order', 'userCards' => function ($query) {
                /** @var Query $query */
                $query->andWhere(['is_use' => 1]);
            }])->one();


            if (empty($orderDetail)) {
                throw new \Exception('订单不存在');
            }
            if ($orderDetail->order->is_sale == Order::IS_SALE_YES) {
                throw new \Exception('订单已过售后时间,无法申请售后');
            }
            if (!in_array($orderDetail->order->status,[Order::STATUS_WAIT_DELIVER,Order::STATUS_WAIT_RECEIVE,Order::STATUS_WAIT_COMMENT]))
                throw new \Exception('该订单状态下,无法申请售后');

            $realityPrice = price_format($orderDetail->total_price);

            if ($this->refund_price > $realityPrice) {
                throw new \Exception('最多可退款金额￥' .$realityPrice);
            }

            if (count($orderDetail->userCards) > 0 && $this->type <= OrderRefund::TYPE_REFUND_RETURN) {
                throw new \Exception('商品赠送的卡券已使用,该商品无法申请退货');
            }

            $order = Order::findOne($orderDetail->order_id);
            if (!$order) {
                throw new \Exception('订单不存在');
            }
            // 生成售后订单
            $t = \Yii::$app->db->beginTransaction();
            $orderRefund = new OrderRefund();
            $orderRefund->mall_id = $order->mall_id;
            $orderRefund->mch_id = $orderDetail->order->mch_id;
            $orderRefund->user_id = $order->user_id;
            $orderRefund->order_id = $orderDetail->order_id;
            $orderRefund->order_detail_id = $this->order_detail_id;
            $orderRefund->order_no = Order::getOrderNo('RE');
            $orderRefund->type = $this->type;
            $orderRefund->refund_type = $this->refund_type;
            $orderRefund->is_receipt = ($this->is_receipt == -1 || $this->is_receipt == "") ? 0 : $this->is_receipt;
            $orderRefund->reason = $this->reason ?: '后台管理员手动申请售后ID：' . \Yii::$app->admin->id;
            $orderRefund->refund_price = $this->refund_total_price;
            $orderRefund->remark = $this->remark;
            $orderRefund->pic_list = '';//$this->pic_list;
            $orderRefund->is_refund = OrderRefund::NO;
            $orderRefund->is_backstage = $this->is_backstage;
            $orderRefund->admin_id = \Yii::$app->admin->id;
            $res = $orderRefund->save();
            if (!$res) {
                $t->rollBack();
                throw new \Exception($this->responseErrorMsg($orderRefund));
            }

            // 更新订单详情售后状态
            $orderDetail->refund_status = OrderDetail::REFUND_STATUS_SALES;
            if (!$orderDetail->save()) {
                $t->rollBack();
                throw new \Exception($this->responseErrorMsg($orderDetail));
            }

            // 更新订单售后状态
            // TODO 如果一个订单多个商品第一个商品就申请了售后，统计会不会有什么问题
            if ($orderDetail->order->sale_status == 0) {
                $orderDetail->order->sale_status = 1;
                if ($orderDetail->order->save() === false) {
                    $t->rollBack();
                    throw new \Exception($this->responseErrorMsg($orderDetail->order));
                }
            }
            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'提交成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 每个订单商品只能进行一次售后操作, 检测该订单商品是否已经售后过.
     */
    private function checkIsRefund()
    {
        $orderRefund = OrderRefund::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'order_detail_id' => $this->order_detail_id,
            'is_delete' => 0
        ])->one();

        if ($orderRefund) {
            throw new \Exception('该订单已生成售后订单,无需重复申请');
        }
    }
}