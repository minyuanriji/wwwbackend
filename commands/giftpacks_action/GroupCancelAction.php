<?php

namespace app\commands\giftpacks_action;

use app\core\ApiCode;
use app\forms\common\UserIntegralGiftpacksForm;
use app\models\Mall;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
use yii\base\Action;

class GroupCancelAction extends Action{

    public function run(){
        \Yii::$app->mall = Mall::findOne(5);

        while(true) {

            //查询已到期未拼成功的拼单
            $group = GiftpacksGroup::find()->andWhere([
                "AND",
                ["status" => "sharing"],
                "user_num < need_num",
                ["<", "expired_at", time()]
            ])->orderBy("updated_at ASC")->one();

            if(!$group){
                sleep(5);
                continue;
            }

            //更新日期
            $group->updated_at = time();
            $group->save();

            $t = \Yii::$app->db->beginTransaction();
            try {

                //获取支付记录
                $payOrders = GiftpacksGroupPayOrder::find()->where([
                    "pay_status" => "paid",
                    "group_id"   => $group->id
                ])->all();
                if($payOrders){
                    foreach($payOrders as $payOrder){ //退红包
                        $user = User::findOne($payOrder->user_id);
                        if(!$user) continue;
                        if($payOrder->pay_type == "integral"){
                            $res = UserIntegralGiftpacksForm::groupCancelRefundAdd($payOrder, $user, false);
                            if($res['code'] != ApiCode::CODE_SUCCESS){
                                $payOrder->remark = $res['msg'];
                            }else{
                                $payOrder->pay_status = "refund";
                            }
                        }else{
                            $payOrder->remark = "未实现除红包外的退款";
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

                $t->commit();

                $this->controller->commandOut("[ID:".$group->id."]拼单取消处理成功");
                
            }catch (\Exception $e){
                $t->rollBack();
                $this->controller->commandOut($e->getMessage());
            }

        }
    }

}