<?php
namespace app\canal\table;

use app\notification\CashNotification;

class Cash{

    public function insert($rows){}

    public function update($mixDatas)
    {
        foreach($mixDatas as $mixData){
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if(isset($update['status']) && $update['status'] > \app\models\Cash::STATUS_APPLY){
                $cash = \app\models\Cash::findone($condition);
                if ($cash->type == 'bank') {
                    $cash && CashNotification::send($cash);
                }
            }
        }
    }
}