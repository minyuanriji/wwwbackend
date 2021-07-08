<?php

namespace app\canal\table;

use app\notification\HotelAfterSalesRejectionNotification;
use app\notification\HotelRefundSuccessfulNotification;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class PluginHotelRefundApplyOrder
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if ((isset($update['status']))) {
                if ($update['status'] == 2) {
                    $hotel_refund_order = HotelRefundApplyOrder::findone($condition);
                    $hotel_refund_order && HotelAfterSalesRejectionNotification::send($hotel_refund_order);
                } elseif ($update['status'] == 1) {
                    $hotel_refund_order = HotelRefundApplyOrder::findone($condition);
                    $hotel_refund_order && HotelRefundSuccessfulNotification::send($hotel_refund_order);
                }
            }
        }
    }
}