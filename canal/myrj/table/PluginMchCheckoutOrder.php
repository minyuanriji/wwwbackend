<?php

namespace app\canal\myrj\table;

use app\notification\MchCheckoutOrderPaySuccessNotification;
use app\plugins\mch\models\MchCheckoutOrder;

class PluginMchCheckoutOrder
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];

            if ((isset($update['is_pay']) && $update['is_pay'] == MchCheckoutOrder::IS_PAY)) {
                $mchCheckoutOrder = MchCheckoutOrder::findone($condition);
                if ($mchCheckoutOrder && !$mchCheckoutOrder->is_delete) {
                    MchCheckoutOrderPaySuccessNotification::send($mchCheckoutOrder);
                }
            }
        }
    }
}