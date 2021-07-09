<?php

namespace app\canal\table;

use app\notification\ReservationSuccessNotification;
use app\plugins\hotel\models\HotelOrder;

class PluginHotelOrders
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if ((isset($update['order_status']) && $update['order_status'] == 3)) {
                $hotel_order = HotelOrder::findone($condition);

                if ($hotel_order && $hotel_order->pay_status == HotelOrder::PAY_STATUS_PAID) {
                    ReservationSuccessNotification::send($hotel_order);
                }
            }
        }
    }
}