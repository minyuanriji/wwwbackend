<?php

namespace app\commands\commission_action;

use app\commands\BaseAction;
use app\forms\common\UserIncomeModifyForm;
use app\models\User;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use app\plugins\commission\models\CommissionSmartshopCyorder3rPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Cyorder;

class SmartShopCyorder3rAction extends BaseAction{

    public function run(){
        $this->controller->commandOut("SmartShopCyorder3rAction start");
        while (true){
            sleep($this->sleepTime);
            try {
                $orderIds = Cyorder::find()->select(["id"])->where([
                    "status" => 1,
                    "commission_3r_status" => 0,
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
     * 处理智慧经营门店上下级分佣
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

            //获取支付用户
            $payUser = User::findOne(["mobile" => $localCyorder->pay_user_mobile]);
            if(empty($localCyorder->pay_user_mobile) || !$payUser){
                throw new \Exception("智慧经营>>门店小程序订单上下级分佣>>ID:".$localCyorder->id.">>无法获取到支付用户");
            }

            //获取门店关联的平台商户
            $mch = Mch::findOne($localCyorder->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("智慧经营>>门店小程序订单推荐分佣>>ID:".$localCyorder->id.">>平台商户信息不存在");
            }

            //获取上级数据
            $parentDatas = $this->controller->getCommissionParents($payUser->id);

            //计算城市服务商、区域服务商、VIP代理商分佣值
            $this->setCommissoinValues($localCyorder, $parentDatas, $mch);

            //红包赠送信息
            $shoppingVoucherInfo = !empty($localCyorder->shopping_voucher_info) ? @json_decode($localCyorder->shopping_voucher_info, true) : [];
            $scoreInfo = !empty($localCyorder->score_info) ? @json_decode($localCyorder->score_info, true) : [];
            $profitPrice = isset($shoppingVoucherInfo['price']) ? (float)$shoppingVoucherInfo['price'] : 0;
            if(isset($scoreInfo['price'])){
                $profitPrice += (float)$scoreInfo['price'];
            }

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData) {
                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if (!$ruleData) continue;

                //新公式
                $ruleData['role_type']    = $parentData['role_type'];
                $ruleData['ver']          = "2021/12/10";
                $ruleData['profit_price'] = $profitPrice;

                $price = $ruleData['profit_price'] * $ruleData['commisson_value'];

                //生成分佣记录
                if($price > 0){
                    $priceLog = CommissionSmartshopCyorder3rPriceLog::findOne([
                        "user_id"    => $parentData['id'],
                        "cyorder_id" => $localCyorder->id
                    ]);

                    //生成分佣记录
                    !$priceLog && $this->newCommissionPriceLog($localCyorder, $price, $ruleData, $parentData);
                }
            }

            $localCyorder->commission_3r_status = 1;
            $localCyorder->commission_3r_info   = json_encode($parentDatas);
            if(!$localCyorder->save()){
                throw new \Exception(json_encode($localCyorder->getErrors()));
            }

            $t->commit();

            $this->controller->commandOut("智慧经营>>门店小程序订单上下级分佣>>ID:".$localCyorder->id.">>上下级分佣处理完毕");
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
            //更新上下级分佣处理状态
            Cyorder::updateAll([
                "commission_3r_status" => 2,
                "commission_3r_text" => json_encode([
                    "message" => $e->getMessage(),
                    "line"    => $e->getLine(),
                    "file"    => $e->getFile()
                ], JSON_UNESCAPED_UNICODE)
            ], ["id" => $id]);
        }
    }

    /**
     * 新增分佣收益
     * @param Cyorder $localCyorder
     * @param float $price
     * @param array $ruleData
     * @param array $parentData
     * @throws \Exception
     */
    private function newCommissionPriceLog(Cyorder $localCyorder, $price, $ruleData, $parentData){
        try {
            $priceLog = new CommissionSmartshopCyorder3rPriceLog([
                "mall_id"        => $localCyorder->mall_id,
                "user_id"        => $parentData['id'],
                "cyorder_id"     => $localCyorder->id,
                "price"          => round($price, 5),
                "status"         => 1,
                "created_at"     => time(),
                "updated_at"     => time(),
                "rule_data_json" => json_encode($ruleData)
            ]);
            if(!$priceLog->save()){
                throw new \Exception("智慧经营>>门店小程序订单上下级分佣>>ID:".$localCyorder->id.">>" . json_encode($priceLog->getErrors()));
            }

            $user = User::findOne($parentData['id']);
            $incomeForm = new UserIncomeModifyForm([
                "type"        => 1,
                "price"       => $priceLog->price,
                "flag"        => 1,
                "source_id"   => $localCyorder->id,
                "source_type" => "smart_shop_cyorder_3r",
                "desc"        => "来自智慧经营门店小程序订单的分佣奖励"
            ]);
            $incomeForm->modify($user, false);

        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 设置分佣值
     * @param Cyorder $localCyorder
     * @param $parentDatas
     * @param Mch $mch
     * @return void
     */
    private function setCommissoinValues(Cyorder $localCyorder, &$parentDatas, Mch $mch){

        //生成规则键
        $keyArr = [];
        foreach($parentDatas as $parentData){
            $keyArr[] = $parentData['role_type'];
        }
        $keyStr = implode("#", $keyArr) . "#all";

        //优先使用独立规则
        $rule = CommissionRules::findOne([
            "item_type"      => "checkout",
            "item_id"        => $mch->store->id,
            "apply_all_item" => 0,
            "is_delete"      => 0
        ]);

        //通用规则
        if(!$rule){
            $rule = CommissionRules::findOne([
                "item_type"      => "checkout",
                "apply_all_item" => 1,
                "is_delete"      => 0
            ]);
        }

        if(!$rule){
            throw new \Exception("智慧经营>>门店小程序订单上下级分佣>>ID:".$localCyorder->id.">>无法获取分佣规则");
        }

        $chains = CommissionRuleChain::find()->where([
            "unique_key" => $keyStr,
            "rule_id"    => $rule->id
        ])->asArray()->all();

        $tmpParentDatas = [];
        foreach($parentDatas as $parentData){
            if(isset($parentData['pingji']) && $parentData['pingji']){
                $tmpParentDatas['pingji'] = $parentData;
            }else{
                $tmpParentDatas[$parentData['role_type']] = $parentData;
            }
        }

        if($chains){
            foreach($chains as $chain){
                if(isset($tmpParentDatas[$chain['role_type']])){
                    $tmpParentDatas[$chain['role_type']]['rule_data'] = [
                        'rule_id'         => $chain['rule_id'],
                        'commission_type' => $rule->commission_type,
                        'level'           => $chain['level'],
                        'commisson_value' => floatval($chain['commisson_value']/100)
                    ];
                }
            }
        }

        $parentDatas = [];
        foreach($tmpParentDatas as &$parentData){
            $parentData['rule_data'] = isset($parentData['rule_data']) ? $parentData['rule_data'] : null;
            $parentDatas[] = $parentData;
        }
    }
}