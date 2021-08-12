<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupJoinForm extends BaseModel{

    public $group_id;

    public function rules(){
        return [
            [['group_id'], 'required']
        ];
    }

    public function join(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //获取拼单信息
            $group = GiftpacksGroup::findOne($this->group_id);
            if(!$group || $group->status != "sharing"){
                throw new \Exception("拼单信息不存在");
            }

            //大礼包信息判断
            $giftpacks = Giftpacks::findOne($group->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            GiftpacksOrderSubmitForm::check($giftpacks);

            if(!$giftpacks->group_enable){
                throw new \Exception("不支持拼单功能");
            }

            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            //获取我发起或我参与未结束的团
            $userId = \Yii::$app->user->id;
            $existGroup = GiftpacksGroupPayOrder::find()->alias("ggpo")
                ->innerJoin(["gg" => GiftpacksGroup::tableName()], "gg.id=ggpo.group_id")
                ->andWhere([
                    "AND",
                    ["gg.pack_id" => $giftpacks->id],
                    ["gg.status"  => "sharing"]
                ])->andWhere([
                    "OR",
                    ["gg.user_id" => $userId],
                    "ggpo.user_id='{$userId}' AND ggpo.pay_status='paid'"
                ])->exists();
            if($existGroup){
                throw new \Exception("你有正在参与未结束的活动，请勿重复参与");
            }


            //拼单信息判断
            if($group->expired_at < time()){
                throw new \Exception("拼单已结束或已过期");
            }

            if($group->need_num <= $group->user_num){
                throw new \Exception("拼单已达到最大人数");
            }

            //每个人只能参与一次
            $payOrder = GiftpacksGroupPayOrder::find()->where([
                "mall_id"    => $group->mall_id,
                "group_id"   => $group->id,
                "user_id"    => \Yii::$app->user->id
            ])->one();
            if($payOrder && $payOrder->pay_status != "unpaid"){
                throw new \Exception("请勿重复参与 id:" . $group->id);
            }

            if(!$payOrder){
                $payOrder = new GiftpacksGroupPayOrder([
                    "mall_id"    => $group->mall_id,
                    "order_sn"   => "GPPO" . date("ymdHis") . rand(1000, 9999),
                    "group_id"   => $group->id,
                    "user_id"    => \Yii::$app->user->id,
                    "pay_status" => "unpaid"
                ]);
                if(!$payOrder->save()){
                    throw new \Exception($this->responseErrorMsg($payOrder));
                }
            }



            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'balance'                  => $user->balance,
                    'user_integral'            => $user->static_integral,
                    'group_price'              => $giftpacks->group_price,
                    'integral_deduction_price' => GiftpacksDetailForm::groupIntegralDeductionPrice($giftpacks, $user)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}