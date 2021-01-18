<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单发货状态
 * Author: xuyaoxiang
 * Date: 2020/10/21
 * Time: 9:58
 */

namespace app\services\mall\order;

use app\helpers\ArrayHelper;
use app\models\ErrorLog;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;

/**
 * Class OrderSendService
 * @package app\services\mall\order
 *
 * jxmall_order_detail.refund_status 售后状态 0--未售后 1--售后中 2--售后结束
 * jxmall_order_refund.status 1.待商家处理 2.同意 3.拒绝
 *  一 order.status=发货 和 jxmall_order_detail.refund_status = 0 为true
 *  二 order.status=发货 和 jxmall_order_detail.refund_status = 2 和 jxmall_order_refund.refund_status=3 为true
 * 只要其中一个order_detail为true 该order发货为true
 */
class OrderSendService
{
    const ALLOW_SEND_ORDER_DETAIL_REFUND_STATUS = [
        OrderDetail::REFUND_STATUS_NO_SALES,
        OrderDetail::REFUND_STATUS_SALES,
        OrderDetail::REFUND_STATUS_SALES_AGREE,
        OrderDetail::REFUND_STATUS_SALES_SEND_AGREE,
        OrderDetail::REFUND_STATUS_SALES_END_REJECT
    ];
    /**
     * 订单发货的基本判断
     * 1。不能为非待发货状态
     * 2. 不能为回收站订单
     * 3. 不能为已删除订单
     * @param Order $order
     * @return bool
     */
    public function sendOrderCondition(Order $order)
    {
        if (Order::STATUS_WAIT_DELIVER != $order->status) {
            return false;
        }

        //若为回收站订单
        if (1 == $order->is_recycle) {
            return false;
        }
        //若为已删除订单
        if (1 == $order->is_delete) {
            return false;
        }

        return true;
    }

    /*
     * 获取订单的发货状态
     * return bool
     */
    public function getOrderSendStatus(Order $order)
    {
        //订单的发货基本判断
        if (false == $this->sendOrderCondition($order)) {
            return false;
        }

        //若未发货订单详情数量为零
        if (0 == $this->notYetOrderDetialSendCount($order)) {
            return false;
        }

        //订单详情的发货基本判断
        foreach ($order->detail as $detail) {
            if ($this->sendOrderDetailContidion($detail)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 未发货订单详情数量
     * @param Order $order
     */
    public function notYetOrderDetialSendCount(Order $order)
    {
        $detail_send_count = count(ArrayHelper::toArray($order->detail));

        return $detail_send_count - $this->orderDetialSendCount($order);
    }

    /**
     * 已发货订单详情数量
     */
    public function orderDetialSendCount(Order $order)
    {
        if (!$order->detailExpressRelation) {
            return 0;
        }

        return count(ArrayHelper::toArray($order->detailExpressRelation));
    }

    /**
     * 订单详情允许发货的基本条件
     * 1.refund_status = 0 未售后
     * 2.refund_status = 1 or 2 or 4
     * 3.已发货 返回flase
     * @param OrderDetail $orderDetail
     * @return bool
     */
    public function sendOrderDetailContidion(OrderDetail $orderDetail)
    {
        //该订单详情是否已发货
        if ($orderDetail->expressRelation) {
            return false;
        }

        if (in_array($orderDetail->refund_status, self::ALLOW_SEND_ORDER_DETAIL_REFUND_STATUS)) {
            return true;
        }

        return false;
    }

    /**
     * 获取订单详情的发货状态
     * @param OrderDetail $orderDetail
     * @return bool
     */
    public function getOrderDetailSend(OrderDetail $orderDetail)
    {
        //订单基本判断
        if (false == $this->SendOrderCondition($orderDetail->order)) {
            return false;
        }

        //订单详情基本判断
        return $this->sendOrderDetailContidion($orderDetail);
    }
}