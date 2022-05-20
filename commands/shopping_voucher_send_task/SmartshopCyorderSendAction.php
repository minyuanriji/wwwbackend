<?php

namespace app\commands\shopping_voucher_send_task;

use app\commands\BaseAction;
use app\models\Integral;
use app\models\User;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\smart_shop\forms\common\StoreAccountBalanceModifyForm;
use app\plugins\smart_shop\models\Cyorder;
use app\plugins\smart_shop\models\StoreAccount;
use app\plugins\smart_shop\models\StoreSet;

class SmartshopCyorderSendAction extends BaseAction {

    public function run(){
        while (true){
            sleep($this->sleepTime);
            try {
                $orderIds = Cyorder::find()->select(["id"])->where([
                    "status" => 1,
                    "shopping_voucher_status" => 0
                ])->orderBy("updated_at ASC")->limit(5)->column();
                if($orderIds){
                    $this->activeTime();
                    Cyorder::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderIds).")");
                    foreach($orderIds as $id){
                        $this->processCyorder($id);
                    }
                }else{
                    $this->negativeTime();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }

    /**
     * 处理智慧经营门店红包赠送业务
     * @param integer $id
     * @return bool
     */
    private function processCyorder($id){

        $t = \Yii::$app->db->beginTransaction();
        try {
            $localCyorder = Cyorder::findOne($id);
            if(!$localCyorder){
                throw new \Exception("数据异常，订单[ID:".$id."]不存在");
            }

            //获取账户
            $account = StoreAccount::findOne([
                "mall_id"     => $localCyorder->mall_id,
                "ss_mch_id"   => $localCyorder->ss_mch_id,
                "ss_store_id" => $localCyorder->ss_store_id
            ]);
            if(!$account){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>门店账户不存在");
            }

            //目前业务只有红包储值赠送功能
            $storeSet = StoreSet::findOne([
                "ss_mch_id"   => $localCyorder->ss_mch_id,
                "ss_store_id" => $localCyorder->ss_store_id,
            ]);

            //没有开通红包赠送功能的跳过
            if(!$storeSet || !$storeSet->enable_shopping_voucher){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>没有开通红包赠送功能");
            }

            //没有支付手机号的跳过
            if(empty($localCyorder->pay_user_mobile)){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>支付用户没有绑定手机号");
            }

            //获取要赠送的用户
            $user = User::findOne(["mobile" => $localCyorder->pay_user_mobile]);
            if(!$user){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>赠送用户信息不存在");
            }

            //要赠送的红包数量
            $shoppingVoucherNum = (float)$localCyorder->pay_price;
            if($shoppingVoucherNum <= 0){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>赠送红包小于0");
            }

            //按10%比例计算出门店账户扣减余额
            $price = $shoppingVoucherNum * 0.1;
            if($price <= 0){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>扣减余额小于0");
            }

            if($account->balance < $price){
                throw new \Exception("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>门店余额不足");
            }

            $modifyForm = new StoreAccountBalanceModifyForm([
                "source_type" => "cyorder",
                "source_id"   => $localCyorder->id,
                "balance"     => $price,
                "desc"        => "红包赠送业务储值扣减"
            ]);
            $modifyForm->sub($account);

            //赠送用户红包
            if(!$localCyorder->shopping_voucher_status){
                $modifyForm = new ShoppingVoucherLogModifiyForm([
                    "money"       => $shoppingVoucherNum,
                    "desc"        => "门店消费获得赠送红包",
                    "source_id"   => $localCyorder->id,
                    "source_type" => "from_smart_shop_cyorder"
                ]);
                $modifyForm->add($user, false);
                $localCyorder->shopping_voucher_status = 1;
                $localCyorder->shopping_voucher_info = json_encode([
                    "shopping_voucher_num" => $shoppingVoucherNum,
                    "price" => $price,
                    "rate"  => 0.1
                ], JSON_UNESCAPED_UNICODE);
            }

            //赠送用户积分
            if(!$localCyorder->score_status){
                $scoreSetting = [
                    "integral_num" => (int)$localCyorder->pay_price,
                    "period" => 1,
                    "period_unit" => "month",
                    "expire" => -1,
                    "source_type" => "from_smart_shop_cyorder",
                    "source_id" => $localCyorder->id
                ];
                $localCyorder->score_status = 1;
                $localCyorder->score_info = json_encode($scoreSetting);
                Integral::addIntegralPlan($user->id, $scoreSetting, '门店消费获得赠送积分', 0, 0, $localCyorder->mall_id);
            }

            //更新订单信息
            if(!$localCyorder->save()){
                throw new \Exception(json_encode($localCyorder->getErrors()));
            }

            $t->commit();

            $this->controller->commandOut("智慧经营>>门店红包赠送业务>>ID:".$localCyorder->id.">>处理成功");

        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
            //更新红包业务处理状态为已处理
            Cyorder::updateAll([
                "shopping_voucher_status" => 2,
                "score_status" => 2,
                "shopping_voucher_error" => json_encode([
                    "message" => $e->getMessage(),
                    "line"    => $e->getLine(),
                    "file"    => $e->getFile()
                ], JSON_UNESCAPED_UNICODE)
            ], ["id" => $id]);
        }
    }
}