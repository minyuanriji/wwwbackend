<?php

namespace app\commands\commission_action;

use app\commands\BaseAction;
use app\forms\common\UserIncomeModifyForm;
use app\models\User;
use app\plugins\commission\models\CommissionRules;
use app\plugins\commission\models\CommissionSmartshopCyorderPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Cyorder;

class SmartShopCyorderAction extends BaseAction{

    public function run(){
        $this->controller->commandOut("SmartShopCyorderAction start");
        while (true){
            sleep($this->sleepTime);
            try {
                $orderIds = Cyorder::find()->select(["id"])->where([
                    "status" => 1,
                    "commission_status" => 0,
                    "shopping_voucher_status" => 1
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
     * 处理智慧经营门店推荐分佣
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

            //获取门店关联的平台商户
            $mch = Mch::findOne($localCyorder->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>平台商户信息不存在");
            }

            //平台商户关联的用户
            $mchUser = User::findOne($mch->user_id);
            if(!$mchUser){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>平台商户所绑定的用户信息不存在");
            }

            //获取商户推荐人
            $parent = User::findOne($mchUser->parent_id);
            if (!$parent){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>商户推荐人信息不存在");
            }
            if ($parent->role_type == 'user'){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>普通用户不分佣");
            }

            //获取当前店铺分佣规则
            $query = CommissionRules::find()->alias("cr")
                ->innerJoin("{{%plugin_commission_rule_chain}} crc", "cr.id=crc.rule_id");
            $newQuery = clone $query;
            $query->andWhere([
                "AND",
                ["cr.item_type"  => 'store'],
                ["cr.item_id"    => $mch->store->id],
                ['cr.is_delete'  => 0],
            ]);
            $commissionRule = $query->select(["cr.commission_type", "crc.level", "crc.commisson_value"])->asArray()->one();
            if (!$commissionRule) {
                //查询是否设置公共规则
                $commissionRule = $newQuery->andWhere([
                    "AND",
                    ["cr.item_type"         => 'store'],
                    ["cr.apply_all_item"    => 1],
                    ['cr.is_delete'         => 0],
                ])->select(["cr.commission_type", "crc.level", "crc.commisson_value"])->asArray()->one();
                if (!$commissionRule) {
                    throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>没有分佣规则");
                }
            }

            $shoppingVoucherInfo = !empty($localCyorder->shopping_voucher_info) ? @json_decode($localCyorder->shopping_voucher_info, true) : [];
            $scoreInfo = !empty($localCyorder->score_info) ? @json_decode($localCyorder->score_info, true) : [];
            $profitPrice = isset($shoppingVoucherInfo['price']) ? (float)$shoppingVoucherInfo['price'] : 0;
            if(isset($scoreInfo['price'])){
                $profitPrice += (float)$scoreInfo['price'];
            }

            //新公式
            $commissionRule['role_type']       = $parent->role_type;
            $commissionRule['ver']             = "2021/10/25";
            $commissionRule['commisson_value'] = min(0.02, (float)($commissionRule['commisson_value']/100));
            $commissionRule['profit_price']    = $profitPrice;
            $price = (float)$commissionRule['commisson_value'] * $commissionRule['profit_price'];
            if($price <= 0){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>分佣金额小于0");
            }

            $priceLog = CommissionSmartshopCyorderPriceLog::findOne([
                "mall_id"    => $localCyorder->mall_id,
                "user_id"    => $parent->id,
                "cyorder_id" => $localCyorder->id
            ]);
            if(!$priceLog){
                $priceLog = new CommissionSmartshopCyorderPriceLog([
                    "mall_id"        => $localCyorder->mall_id,
                    "user_id"        => $parent->id,
                    "cyorder_id"     => $localCyorder->id,
                    "price"          => round($price, 5),
                    "status"         => 1,
                    "created_at"     => time(),
                    "updated_at"     => time(),
                    "rule_data_json" => json_encode($commissionRule)
                ]);
                if(!$priceLog->save()){
                    throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>".json_encode($priceLog->getErrors()));
                }

                $user = User::findOne($parent->id);

                $incomeForm = new UserIncomeModifyForm([
                    "type"        => 1,
                    "price"       => $priceLog->price,
                    "flag"        => 1,
                    "source_id"   => $localCyorder->id,
                    "source_type" => "smart_shop_cyorder",
                    "desc"        => "来自智慧经营门店订单的分佣记录"
                ]);
                $incomeForm->modify($user, false);
            }

            $localCyorder->commission_status = 1;
            $localCyorder->commission_info   = json_encode($commissionRule);
            if(!$localCyorder->save()){
                throw new \Exception(json_encode($localCyorder->getErrors()));
            }

            $t->commit();

            $this->controller->commandOut("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>直推分佣处理完毕");
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
            //更新直推分佣处理状态
            Cyorder::updateAll([
                "commission_status" => 2,
                "commission_text" => json_encode([
                    "message" => $e->getMessage(),
                    "line"    => $e->getLine(),
                    "file"    => $e->getFile()
                ], JSON_UNESCAPED_UNICODE)
            ], ["id" => $id]);
        }
    }
}