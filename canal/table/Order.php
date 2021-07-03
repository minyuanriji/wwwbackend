<?php
namespace app\canal\table;

use app\notification\OrderPaymentSuccessNotification;

class Order{

    public function insert($rows){}

    public function update($mixDatas)
    {
        foreach($mixDatas as $mixData){
            $condition = $mixData['condition'];
            $update = $mixData['update'];
            if(isset($update['is_pay']) && $update['is_pay'] == \app\models\Order::IS_PAY_YES){
                $order = \app\models\Order::findone($condition);
                $order && OrderPaymentSuccessNotification::send($order);
            }
        }
    }
}