<?php

namespace app\plugins\shopping_voucher\helpers;

class ShoppingVoucherHelper{

    /**
     * 通过服务费比例计算商家购物券赠送比例
     * @param $transferRate
     * @return float
     */
    public static function calculateMchRateByTransferRate($transferRate){
        $zk = (100 - $transferRate)/10;
        $arr = [
            "8.7" => 100,
            "8.8" => 90,
            "8.9" => 80,
            "9"   => 70,
            "9.1" => 60,
            "9.2" => 50,
            "9.3" => 40,
            "9.4" => 30,
            "9.5" => 20,
            "9.6" => 10
        ];
        $giveValue = 0;
        foreach($arr as $key => $value){
            if($zk <= floatval($key)){
                $giveValue = $value;
                break;
            }
        }
        return $giveValue;

        /*$value = (100 - $transferRate)/10;
        if($value <= 8){ //8折以下都是100%
            $giveValue = 100;
        }elseif($value > 8 && $value <= 8.5){ //8折-8.5折，70%
            $x = (1 - 0.7)/(8.5-8) * ($value - 8);
            $giveValue = intval((1 - $x) * 100);
        }elseif($value > 8.5 && $value <= 9){ //8.5-9折，50%
            $x = (0.7 - 0.5)/(9-8.5) * ($value - 8.5);
            $giveValue = intval((0.7 - $x) * 100);
        }elseif($value > 9 && $value <= 9.5){ //9折-9.5折，30%
            $x = (0.5 - 0.3)/(9.5-9) * ($value - 9);
            $giveValue = intval((0.5 - $x) * 100);
        }else{
            $x = (0.3/0.5) * ($value - 9.5);
            $giveValue = intval((0.3 - $x) * 100);
        }
        return $giveValue;*/
    }

}