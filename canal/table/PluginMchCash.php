<?php

namespace app\canal\table;

use app\notification\MchCashRefuseNotification;
use app\notification\MchCashAgreeNotification;
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

            if ((isset($update['status']) && $update['status'] == MchCash::STATUS_TWO)) {
                $mch_cash = MchCash::findone($condition);
                if ($mch_cash && $mch_cash->type == 'efps_bank') {
                    MchCashRefuseNotification::send($mch_cash);
                }
            }

            if (isset($update['transfer_status'])) {
                $mch_cash = MchCash::findone($condition);
                if ($mch_cash && $mch_cash->type == 'efps_bank') {
                    if ($update['transfer_status'] == MchCash::TRANSFER_STATUS_ONE) {
                        MchCashAgreeNotification::send($mch_cash);
                    } elseif ($update['transfer_status'] == MchCash::TRANSFER_STATUS_TWO) {
                        MchCashRefuseNotification::send($mch_cash);
                    }
                }
            }
        }
    }
}