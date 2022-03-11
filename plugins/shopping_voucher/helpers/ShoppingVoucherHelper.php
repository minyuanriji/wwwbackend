<?php

namespace app\plugins\shopping_voucher\helpers;

class ShoppingVoucherHelper{

    /**
     * 通过服务费比例计算商家购物券赠送比例
     * @param $transferRate
     * @return float
     */
    public static function calculateMchRateByTransferRate($transferRate){
        $value = (100 - $transferRate)/10;
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
        return $giveValue;
    }

}