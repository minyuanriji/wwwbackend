<?php

namespace app\canal\table;

use app\notification\MchCashNotification;
use app\plugins\mch\models\MchCash;

class PluginMchCash
{

    public function insert($rows)
    {
    }

    public function update($mixDatas)
    {
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if ((isset($update['status']) && $update['status'] == MchCash::STATUS_TWO) ||
                (isset($update['transfer_status']) && $update['transfer_status'] > MchCash::TRANSFER_STATUS_ZERO)) {
                $mch_cash = MchCash::findone($condition);
                if ($mch_cash->type == 'efps_bank') {
                    $mch_cash && MchCashNotification::send($mch_cash);
                }
            }
        }
    }
}