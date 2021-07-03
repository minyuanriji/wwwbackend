<?php
namespace app\canal\table;

use app\notification\CashAgreeNotification;
use app\notification\CashPaidNotification;
use app\notification\CashRejectNotification;

class Cash{

    public function insert($rows){}

    public function update($mixDatas)
    {
        foreach($mixDatas as $mixData){
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if (isset($update['status'])) {
                $cash = \app\models\Cash::findone($condition);
                if ($cash && $cash->type == 'bank') {
                    $cash_model = new \app\models\Cash();
                    if ($update['status'] == $cash_model::STATUS_AGREE) {
                        CashAgreeNotification::send($cash);
                    } elseif ($update['status'] == $cash_model::STATUS_PAID) {
                        CashPaidNotification::send($cash);
                    } elseif ($update['status'] == $cash_model::STATUS_REJECT) {
                        CashRejectNotification::send($cash);
                    }
                }
            }
        }
    }
}