<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/10/22
 * Time: 16:47
 */

namespace app\services\mall\order;

use app\helpers\ArrayHelper;
use app\models\Order;
use app\models\OrderDetail;

class OrderSaleStatusService
{
    private $count_refund_no_sales; //未售后订单详情数量
    private $count_refund_sales; //售后中订单详情数量
    private $count_refund_end_sales; //完成售后订单详情数量
    private $count_order_detail; //所有订单详情数量

    /**
     * 获取订单售后状态
     * @param Order $order
     * @return int
     */
    public function getOrderSaleStatus(Order $order)
    {
        $this->getCountOrderDetail($order);
        $this->countOrderDetailRefundStatus($order);

        //只要有一个售后中订单详情,该订单为售后中
        if ($this->count_refund_sales > 0) {
            return Order::SALE_STATUS_YES;
        }

        //没有完成售后订单详情和售后中订单详情,为未售后订单
        if ($this->count_refund_end_sales == 0) {
            return Order::SALE_STATUS_NO;
        }

        //其他则为完成售后订单
        return Order::SALE_STATUS_FINISHED;
    }

    /**
     * 更新订单售后状态
     * @param Order $order
     * @return bool
     */
    public function updateOrderSaleStatus(Order $order)
    {
        $order_sale_status = $this->getOrderSaleStatus($order);

        $order->sale_status = $order_sale_status;
        return $order->save();
    }

    public function getCountOrderDetail(Order $order)
    {
        return $this->count_order_detail = count(ArrayHelper::toArray($order->detail));
    }

    /**
     * 获取各售后订单详情的数量
     * @param Order $order
     */
    public function countOrderDetailRefundStatus(Order $order)
    {
        $this->count_refund_no_sales  = 0; //未售后订单详情数量
        $this->count_refund_sales     = 0; //售后中订单详情数量
        $this->count_refund_end_sales = 0; //完成售后订单详情数量

        foreach ($order->detail as $detail) {
            if (OrderDetail::REFUND_STATUS_NO_SALES == $detail->refund_status) {

                $this->count_refund_no_sales += 1;

            } elseif (OrderDetail::REFUND_STATUS_SALES_AGREE == $detail->refund_status or OrderDetail::REFUND_STATUS_SALES == $detail->refund_status) {

                $this->count_refund_sales += 1;

            } elseif (OrderDetail::REFUND_STATUS_SALES_END_PAY == $detail->refund_status or OrderDetail::REFUND_STATUS_SALES_END_REJECT == $detail->refund_status) {

                $this->count_refund_end_sales += 1;
            }
        }
    }

    /**
     * @param Order $order
     * @return integer
     */
    public function getCountRefundNoSales(Order $order)
    {
        $this->countOrderDetailRefundStatus($order);

        return $this->count_refund_no_sales;
    }

    /**
     * @param Order $order
     * @return integer
     */
    public function getCountRefundSale(Order $order)
    {
        $this->countOrderDetailRefundStatus($order);

        return $this->count_refund_sales;
    }

    /**
     * @param Order $order
     * @return integer
     */
    public function getCountRefundEndSales(Order $order)
    {
        $this->countOrderDetailRefundStatus($order);

        return $this->count_refund_end_sales;
    }
}