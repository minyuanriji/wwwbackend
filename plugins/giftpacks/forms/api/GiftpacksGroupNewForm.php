<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupNewForm extends BaseModel{

    public $pack_id;

    public function rules(){
        return [
            [['pack_id'], 'required']
        ];
    }

    public function addGroup(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            GiftpacksOrderSubmitForm::check($giftpacks);

            if(!$giftpacks->group_enable){
                throw new \Exception("不支持拼单功能");
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

            //生成拼单记录，把状态设置为已关闭
            $group = new GiftpacksGroup([
                'mall_id'       => \Yii::$app->mall->id,
                'pack_id'       => $giftpacks->id,
                'user_id'       => \Yii::$app->user->id,
                'need_num'      => $giftpacks->group_need_num,
                'user_num'      => 0,
                'status'        => 'closed',
                'expired_at'    => (time() + $giftpacks->group_expire_time),
                'created_at'    => time(),
                'updated_at'    => time(),
                "process_class" => "app\\plugins\\giftpacks\\forms\\common\\GiftpacksGroupPaidProcessForm"
            ]);
            if(!$group->save()){
                throw new \Exception($this->responseErrorMsg($group));
            }

            //生成待支付记录
            $payOrder = new GiftpacksGroupPayOrder([
                'mall_id'    => \Yii::$app->mall->id,
                "order_sn"   => "GPPO" . date("ymdHis") . rand(1000, 9999),
                'group_id'   => $group->id,
                'user_id'    => \Yii::$app->user->id,
                'pay_status' => 'unpaid'
            ]);
            if(!$payOrder->save()){
                throw new \Exception($this->responseErrorMsg($payOrder));
            }

            $t->commit();

            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'group_id'                 => $group->id,
					'balance'                  => $user->balance,
					'user_integral'            => $user->static_integral,
					'group_price'              => $giftpacks->group_price,
					'integral_deduction_price' => GiftpacksDetailForm::groupIntegralDeductionPrice($giftpacks, $user)
                ]
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}