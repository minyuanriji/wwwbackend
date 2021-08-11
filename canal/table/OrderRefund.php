<?php

namespace app\canal\table;

use app\notification\OrderRefundPaymentNotification;
use app\notification\OrderRefundRefuseNotification;
use app\notification\OrderRefundSuccessNotification;

class OrderRefund
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['status'])) {
                $order_refund = \app\models\OrderRefund::findone($condition);
                if ($order_refund) {
                    if ($update['status'] == \app\models\OrderRefund::STATUS_AGREE) {
                        OrderRefundSuccessNotification::send($order_refund);
                    } elseif ($update['status'] == \app\models\OrderRefund::STATUS_REFUSE) {
                        OrderRefundRefuseNotification::send($order_refund);
                    }
                }
            }
            if (isset($update['is_refund']) && $update['is_refund'] == \app\models\OrderRefund::IS_REFUND_YES) {
                $order_refund = \app\models\OrderRefund::findone($condition);
                $order_refund && OrderRefundPaymentNotification::send($order_refund);
            }
        }
    }
}