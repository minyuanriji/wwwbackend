<?php

namespace app\forms\common;


use app\core\ApiCode;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class UserIntegralGiftpacksForm extends UserIntegralForm{

    /**
     * 大礼包拼单取消退款
     * @param GiftpacksGroupPayOrder $payOrder
     * @param User $user
     * @param boolean $trans
     * @return array
     */
    public static function groupCancelRefundAdd(GiftpacksGroupPayOrder $payOrder, User $user, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {
            $desc = "大礼包拼单取消，支付单[ID:".$payOrder->id."]退款，返还金豆";

            static::change($user, $payOrder->integral_deduction_price, self::TYPE_ADD, "giftpacks_group_pay_order_refund", $payOrder->id, $desc);

            $trans && ($t->commit());

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans && ($t->rollBack());
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}