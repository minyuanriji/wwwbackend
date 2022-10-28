<?php

namespace app\commands\commission_action;


use app\models\IncomeLog;
use app\models\User;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\base\Action;

class CheckoutAction extends Action{

    public function run(){
        while (true){
            if(!defined("ENV") || ENV != "pro"){
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission checkout action start");
            }
            sleep(1);
            $this->doNew(); //店铺二维码收款分佣
            if(!defined("ENV") || ENV != "pro"){
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission checkout action end");
            }
        }
    }

    /**
     * 二维码收款订单，新增分佣记录
     * @return boolean
     */
    private function doNew(){
        $query = MchCheckoutOrder::find()->alias("mco");
        $query->innerJoin("{{%store}} s", "s.id=mco.store_id");
        $query->innerJoin("{{%plugin_mch}} m", "m.id=s.mch_id");
        $query->andWhere([
            "AND",
            ["mco.is_pay" => 1],
            ["mco.is_delete" => 0],
            ["mco.commission_status" => 0]
        ]);
        $query->select(["mco.*", "s.name", "m.transfer_rate", "m.integral_fee_rate"]);
        $checkoutOrders = $query->asArray()->limit(10)->all();
        if(!$checkoutOrders){
            return false;
        }

        foreach($checkoutOrders as $checkoutOrder){
            try {

                $parentDatas = $this->controller->getCommissionParents($checkoutOrder['pay_user_id']);

                //计算城市服务商、区域服务商、VIP代理商分佣值
                $this->setCommissoinValues($checkoutOrder, $parentDatas);

                //通过相关规则键获取分佣规则进行分佣
                foreach($parentDatas as $parentData) {

                    $ruleData = $parentData['rule_data'];

                    //无分佣规则 跳过
                    if (!$ruleData) continue;

                    //计算分佣金额
                    $transferRate = (int)$checkoutOrder['transfer_rate'];
                    $integralFeeRate = (int)$checkoutOrder['integral_fee_rate'];

                    /*
                     * 分佣旧公式
                    $ruleData['profit_price'] = $this->controller->calculateCheckoutOrderProfitPrice($checkoutOrder['order_price'], $transferRate, $integralFeeRate);
                    if($ruleData['commission_type'] == 1){ //按百分比
                        $price = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                    }else{ //按固定值
                        $price = (float)$ruleData['commisson_value'];
                    }
                    */

                    //新公式
                    $ruleData['role_type'] = $parentData['role_type'];
                    $ruleData['ver'] = "2021/12/10";
                    $ruleData['profit_price'] = ($transferRate/100) * $checkoutOrder['order_price'];
                    $price = $ruleData['profit_price'] * $ruleData['commisson_value'];

                    //生成分佣记录
                    if($price > 0){
                        $priceLog = CommissionCheckoutPriceLog::findOne([
                            "checkout_order_id" => $checkoutOrder['id'],
                            "user_id"           => $parentData['id'],
                        ]);
                        if(!$priceLog){ //没有生成过再去生成
                            $trans = \Yii::$app->db->beginTransaction();
                            try {
                                $priceLog = new CommissionCheckoutPriceLog([
                                    "mall_id"           => $checkoutOrder['mall_id'],
                                    "checkout_order_id" => $checkoutOrder['id'],
                                    "user_id"           => $parentData['id'],
                                    "price"             => round($price, 5),
                                    "status"            => 1,
                                    "created_at"        => $checkoutOrder['created_at'],
                                    "updated_at"        => $checkoutOrder['updated_at'],
                                    "rule_data_json"    => json_encode($ruleData)
                                ]);
                                if(!$priceLog->save()){
                                    throw new \Exception(json_encode($priceLog->getErrors()));
                                }
                                $this->controller->commandOut("收款消费分佣>>ID:".$checkoutOrder['id'].">>生成分佣记录 [ID:".$priceLog->id."]");

                                //收入记录
                                $incomeLog = new IncomeLog([
                                    'mall_id'     => $checkoutOrder['mall_id'],
                                    'user_id'     => $parentData['id'],
                                    'type'        => 1,
                                    'money'       => $parentData['total_income'],
                                    'income'      => $priceLog->price,
                                    'desc'        => "来自店铺“".$checkoutOrder['name']."”的用户消费分佣记录[ID:".$priceLog->id."]",
                                    'flag'        => 1, //到账
                                    'source_id'   => $priceLog->id,
                                    'source_type' => 'checkout',
                                    'created_at'  => $checkoutOrder['created_at'],
                                    'updated_at'  => $checkoutOrder['updated_at']
                                ]);
                                if(!$incomeLog->save()){
                                    throw new \Exception("收款消费分佣>>ID:".$checkoutOrder['id'].">>" .  json_encode($incomeLog->getErrors()));
                                }

                                User::updateAllCounters([
                                    "total_income"  => $priceLog->price,
                                    "income" => $priceLog->price
                                ], ["id" => $parentData['id']]);

                                $trans->commit();
                            }catch (\Exception $e){
                                $trans->rollBack();
                                $this->controller->commandOut("CheckoutAction::doNew>>" . $e->getMessage());
                            }
                        }
                    }
                }
            }catch (\Exception $e){
                $this->controller->commandOut("CheckoutAction::doNew>>" . $e->getMessage());
            }

            MchCheckoutOrder::updateAll([
                "commission_status" => 1
            ], ["id" => $checkoutOrder['id']]);
        }

        return true;
    }

    /**
     * 设置分佣值
     * @param $checkoutOrder
     * @param $parentDatas
     * @return void
     */
    private function setCommissoinValues($checkoutOrder, &$parentDatas){

        //生成规则键
        $keyArr = [];
        foreach($parentDatas as $parentData){
            $keyArr[] = $parentData['role_type'];
        }
        $keyStr = implode("#", $keyArr) . "#all";

        //优先使用独立规则
        $rule = CommissionRules::findOne([
            "item_type"      => "checkout",
            "item_id"        => $checkoutOrder['store_id'],
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
            throw new \Exception("收款消费分佣>>ID:".$checkoutOrder['id'].">>无法获取分佣规则");
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

        //分佣规则
        /*$fitRules = [
            "branch_office#partner#partner#store" => [
                "branch_office" => 0.034,
                "pingji"        => 0.016,
                "partner"       => 0.08,
                "store"         => 0.02
            ],
            "branch_office#partner#partner" => [
                "branch_office" => 0.05,
                "pingji"        => 0.02,
                "partner"       => 0.08,
                "store"         => 0
            ],
            "branch_office#partner#store" => [
                "branch_office" => 0.05,
                "pingji"        => 0,
                "partner"       => 0.08,
                "store"         => 0.02
            ],
            "branch_office#partner" => [
                "branch_office" => 0.05,
                "pingji"        => 0,
                "partner"       => 0.1,
                "store"         => 0
            ],
            "branch_office#store" => [
                "branch_office" => 0.13,
                "pingji"        => 0,
                "partner"       => 0,
                "store"         => 0.02
            ],
            "branch_office" => [
                "branch_office" => 0.15,
                "pingji"        => 0,
                "partner"       => 0,
                "store"         => 0
            ],
            "partner#partner" => [
                "branch_office" => 0,
                "pingji"        => 0.03,
                "partner"       => 0.07,
                "store"         => 0
            ],
            "partner#store" => [
                "branch_office" => 0,
                "pingji"        => 0,
                "partner"       => 0.08,
                "store"         => 0.02
            ],
            "partner" => [
                "branch_office" => 0,
                "pingji"        => 0,
                "partner"       => 0.1,
                "store"         => 0
            ],
            "store" => [
                "branch_office" => 0,
                "pingji"        => 0,
                "partner"       => 0,
                "store"         => 0.02
            ]
        ];
        $keys = [];
        foreach($parentDatas as $parentData){
            if(isset($parentData['pingji']) && $parentData['pingji'] == 1){
                $keys[] = "partner";
            }else{
                $keys[] = $parentData['role_type'];
            }
        }
        $keyVal = implode("#", $keys);
        if(!empty($keyVal) && isset($fitRules[$keyVal])){
            $rule = $fitRules[$keyVal];
        }else{
            $rule = ["branch_office" => 0, "pingji" => 0, "partner" => 0, "store" => 0];
        }
        foreach($parentDatas as &$parentData){
            if(isset($parentData['pingji']) && $parentData['pingji'] == 1){
                $parentData['rule_data']['commisson_value'] = $rule['pingji'];
            }elseif(isset($rule[$parentData['role_type']])){
                $parentData['rule_data']['commisson_value'] = $rule[$parentData['role_type']];
            }
        }*/
    }
}