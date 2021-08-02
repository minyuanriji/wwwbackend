<?php

namespace app\forms\common;


use app\core\ApiCode;
use app\models\User;
use app\plugins\commission\models\CommissionGiftpacksPriceLog;

class UserIncomeCommissionGiftpacksForm extends UserIncomeForm{

    /**
     * 新增已结算分佣
     * @param User $user
     * @param string $packName
     * @param CommissionGiftpacksPriceLog $priceLog
     * @param boolean $trans
     * @return array
     */
    public static function doCommissionAdd(User $user, $packName, CommissionGiftpacksPriceLog $priceLog, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            if(empty($desc)){
                $desc = "来自大礼包“".$packName."”订单[ID:".$priceLog->order_id."]结算分佣";
            }

            static::change($user, $priceLog->price, self::TYPE_ADD, self::FLAG_INCOME, "giftpacks_commission", $priceLog->id, $desc);

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}