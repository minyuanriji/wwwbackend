<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/10/22
 * Time: 20:59
 */

namespace app\services\mall\order;

use app\models\ErrorLog;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\services\ReturnData;
use yii\db\Exception;

class OrderSendActionService
{
    use ReturnData;

    /**
     * 售后中的订单详情,强制发货售后状态变为完成售后拒绝;
     * @param Order $order
     */
    public function action(array $order_detail_id)
    {
        foreach ($order_detail_id as $detail_id) {
            $detail = OrderDetail::findOne($detail_id);
            if (!$detail) {
                continue;
            }

            //售后中得状态
            if (OrderDetail::REFUND_STATUS_SALES == $detail->refund_status
                or OrderDetail::REFUND_STATUS_SALES_AGREE == $detail->refund_status
                or OrderDetail::REFUND_STATUS_SALES_SEND_AGREE == $detail->refund_status
            ) {

                $return = $this->saveOrderDetail($detail);

                if (0 != $return['code']) {
                    return $this->returnApiResultData($return['code'], $return['msg']);
                }

                //更新订单状态
                $OrderSaleStatusService = new OrderSaleStatusService();
                $bool                   = $OrderSaleStatusService->updateOrderSaleStatus($detail->order);

                if (!$bool) {
                    return $this->returnApiResultData(98, "更新订单状态失败");
                }

                return $this->returnApiResultData(0, "更新订单状态成功");
            }
        }
    }

    public function saveOrderDetail(OrderDetail $detail)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {

            $detail->refund_status = OrderDetail::REFUND_STATUS_SALES_END_REJECT;

            if (!$detail->save()) {
                throw new Exception($this->responseErrorMsg($detail), [], 97);
            }

            if (!$detail->refund) {
                throw new Exception("售后订单不存在", [], 98);
            }

            $refund             = $detail->refund;
            $refund->status     = OrderRefund::STATUS_REFUSE;
            $refund->is_confirm = OrderRefund::IS_CONFIRM_YES;
            $refund->save();

            if (!$refund->save()) {
                throw new Exception($this->responseErrorMsg($refund), [], 96);
            }

            $t->commit();

        } catch (Exception $e) {

            $t->rollBack();

            $code = intval($e->getCode()) > 0 ? $e->getCode() : 99;

            return $this->returnApiResultData($code, $e->getMessage());
        }

        return $this->returnApiResultData(0, "更新订单详情成功");
    }
}