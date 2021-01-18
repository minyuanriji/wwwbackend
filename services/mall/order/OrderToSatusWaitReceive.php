<?php
namespace app\services\mall\order;
use app\models\Order;
use app\services\mall\order\OrderSendService;
use app\services\ReturnData;

/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 检查是否够条件改变订单状态到待发货
 * Author: xuyaoxiang
 * Date: 2020/10/26
 * Time: 15:31
 */
class OrderToSatusWaitReceive
{
    use ReturnData;

    /**
     * @param Order $order
     */
    public function statusToSatusWaitReceive(Order $order)
    {
        if (Order::STATUS_WAIT_DELIVER != $order->status) {
            return $this->returnApiResultData(98, "该订单不是待发货状态");
        }

        $OrderSendService   = new OrderSendService();
        $getOrderSendStatus = $OrderSendService->getOrderSendStatus($order);
        if (!$getOrderSendStatus) {
            $order->is_send = 1;
            $order->send_at = time();
            $order->status  = Order::STATUS_WAIT_RECEIVE;
            $res            = $order->save();
            if (!$res) {

                return $this->returnApiResultData(99, $this->responseErrorMsg($order));
            }

            return $this->returnApiResultData(0, "更新成功");
        }

        return $this->returnApiResultData(98, "该订单状态更新为待发货");
    }
}