<?php


namespace app\plugins\giftpacks\forms\common;


use app\core\ApiCode;
use app\forms\common\UserBalanceModifyForm;
use app\forms\common\UserIntegralGiftpacksForm;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupCancelRefundProcessForm extends BaseModel{

    /**
     * 拼单取消退款操作
     * @param GiftpacksGroup $group
     * @throws \Exception
     */
    public static function refund(GiftpacksGroup $group){

        //获取支付记录
        $payOrders = GiftpacksGroupPayOrder::find()->where([
            "pay_status" => "paid",
            "group_id"   => $group->id
        ])->all();
        if($payOrders){
            foreach($payOrders as $payOrder){ //退金豆
                $user = User::findOne($payOrder->user_id);
                if(!$user) continue;
                if($payOrder->pay_type == "integral") { //金豆支付的
                    static::integralRefund($payOrder, $user);
                }elseif($payOrder->pay_type == "balance"){ //余额支付
                    static::balanceRefund($payOrder, $user);
                }else{
                    $payOrder->remark = "未实现除金豆外的退款";
                }
                if(!$payOrder->save()){
                    throw new \Exception(json_encode($payOrder->getErrors()));
                }
            }
        }

        $group->status = "closed";
        $group->updated_at = time();
        if(!$group->save()){
            throw new \Exception(json_encode($group->getErrors()));
        }
    }

    /**
     * 返还金豆
     * @param GiftpacksGroupPayOrder $payOrder
     * @param User $user
     */
    protected static function integralRefund(GiftpacksGroupPayOrder $payOrder, User $user){
        $res = UserIntegralGiftpacksForm::groupCancelRefundAdd($payOrder, $user, false);
        if($res['code'] != ApiCode::CODE_SUCCESS){
            $payOrder->remark = $res['msg'];
        }else{
            $payOrder->pay_status = "refund";
        }
    }

    /**
     * 退还余额
     * @param GiftpacksGroupPayOrder $payOrder
     * @param User $user
     */
    protected static function balanceRefund(GiftpacksGroupPayOrder $payOrder, User $user){
        $desc = "大礼包拼单取消，支付单[ID:".$payOrder->id."]退款，返还金豆";
        $balanceModifyForm = new UserBalanceModifyForm([
            "type"        => BalanceLog::TYPE_ADD,
            "money"       => $payOrder->pay_price,
            "source_id"   => $payOrder->id,
            "source_type" => "giftpacks_group_pay_order_refund",
            "desc"        => $desc,
            "custom_desc" => ""
        ]);
        $balanceModifyForm->modify($user);
    }
}