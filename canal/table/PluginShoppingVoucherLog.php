<?php

namespace app\canal\table;

use app\notification\StorePayVoucherNotification;
use app\notification\VoucherConsumptionNotification;

class PluginShoppingVoucherLog
{
    const VOUCHER_TYPE = [1, 5];

    public function insert($rows)
    {
        foreach ($rows as $row)
        {
            if (isset($row['source_type'])) {
                if (in_array($row['source_type'], self::VOUCHER_TYPE)) {
                    if ($row['source_type'] == 5 && $row['type'] == 1) {
                        $row['source_type'] = 'from_mch_checkout_order';
                        StorePayVoucherNotification::send($row);
                    } elseif ($row['source_type'] == 1 && $row['type'] == 2){
                        $row['source_type'] = 'target_order';
                        VoucherConsumptionNotification::send($row);
                    }
                    \Yii::error('IncomeLogNotice:' . json_encode($row) . '---time:' . date("Y-m-d H:i:s", time()));
                }
            }
        }
    }

    public function update($mixDatas)
    {
    }
}