<?php

namespace app\commands\commission_action;

use app\forms\common\UserIncomeModifyForm;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use app\plugins\commission\models\CommissionSmartshop3rPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

/**
 * @deprecated
 */
class SmartShopOrder3rAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " SmartShopOrder3rAction start");
        $sleep = 1;
        while (true) {
            try {
                $query = Order::find()->alias("o")
                    ->innerJoin(["m" => Mch::tableName()], "m.id=o.bsh_mch_id")
                    ->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id");
                $query->andWhere([
                    "AND",
                    ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
                    ["m.is_delete" => 0],
                    ["o.is_delete" => 0],
                    ["o.status" => Order::STATUS_FINISHED],
                    [">", "o.split_amount", 0],
                    ["o.commission_3r_status" => 0]
                ]);
                $query->select(["o.id", "o.mall_id", "o.from_table_name", "o.from_table_record_id", "o.bsh_mch_id", "s.id as bsh_store_id", "o.ss_mch_id", "o.ss_store_id",
                    "o.split_amount", "s.name as store_name", "m.transfer_rate", "m.integral_fee_rate", "o.pay_user_mobile"]);
                $orders = $query->asArray()->orderBy("o.updated_at ASC")->limit(1)->all();
                if($orders){
                    $sleep = max(1, --$sleep);
                    foreach($orders as $order){
                        $this->processNew($order);
                    }
                }else{
                    $sleep = min(30, ++$sleep);
                }
            } catch (\Exception $e) {
                $this->controller->commandOut("SmartShopOrder3rAction::run>>" . $e->getMessage());
            }
            $this->controller->sleep($sleep);
        }
    }

    /**
     * 处理订单分佣
     * @param $order
     * @return boolean
     */
    private function processNew($order){
        $t = \Yii::$app->db->beginTransaction();
        try {
            if(empty($order['pay_user_mobile'])){
                throw new \Exception("智慧门店小程序订单消费分佣>>订单[ID:".$order['id']."]>>支付用户手机为空");
            }

            $payUser = User::findOne(["mobile" => $order['pay_user_mobile']]);
            if(!$payUser){
                throw new \Exception("智慧门店小程序订单消费分佣>>订单[ID:".$order['id']."]>>无法获取到支付用户");
            }

            $parentDatas = $this->controller->getCommissionParents($payUser->id);

            //计算分公司、合伙人、VIP代理商分佣值
            $this->setCommissoinValues($order, $parentDatas);

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData) {
                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if (!$ruleData) continue;

                //新公式
                $ruleData['role_type']    = $parentData['role_type'];
                $ruleData['ver']          = "2021/12/10";
                $ruleData['profit_price'] = $order['split_amount'];

                $price = $ruleData['profit_price'] * $ruleData['commisson_value'];

                //生成分佣记录
                if($price > 0){
                    $priceLog = CommissionSmartshop3rPriceLog::findOne([
                        "user_id"  => $parentData['id'],
                        "order_id" => $order['id']
                    ]);

                    //生成分佣记录
                    !$priceLog && $this->newCommissionPriceLog($order, $price, $ruleData, $parentData);
                }
            }

            Order::updateAll([
                "updated_at"           => time(),
                "commission_3r_status" => 1
            ], ["id" => $order['id']]);

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            Order::updateAll([
                "updated_at"           => time(),
                "commission_3r_status" => 1,
                "error_text"           => json_encode([
                    "msg"  => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ])
            ], ["id" => $order['id']]);
            $this->controller->commandOut("SmartShopOrder3rAction::processNew>>" . $e->getMessage());
        }

        $this->controller->commandOut("智慧门店小程序订单消费分佣>>订单[ID:".$order['id']."]>>上下级分佣记录处理完毕");

    }

    /**
     * 新增分佣收益
     * @param $order
     * @param $price
     * @param $ruleData
     * @param $parentData
     * @throws \Exception
     */
    private function newCommissionPriceLog($order, $price, $ruleData, $parentData){
        try {
            $priceLog = new CommissionSmartshop3rPriceLog([
                "mall_id"           => $order['mall_id'],
                "user_id"           => $parentData['id'],
                "order_id"          => $order['id'],
                "price"             => round($price, 5),
                "status"            => 1,
                "created_at"        => time(),
                "updated_at"        => time(),
                "rule_data_json"    => json_encode($ruleData)
            ]);
            if(!$priceLog->save()){
                throw new \Exception("智慧门店小程序订单消费分佣>>订单[ID:".$order['id']."]>>" . json_encode($priceLog->getErrors()));
            }

            $user = User::findOne($parentData['id']);

            $incomeForm = new UserIncomeModifyForm([
                "type"        => 1,
                "price"       => $priceLog->price,
                "flag"        => 1,
                "source_id"   => $order['id'],
                "source_type" => "smart_shop_order_3r",
                "desc"        => "来自智慧经营门店订单的分佣记录"
            ]);
            $incomeForm->modify($user, false);

        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 设置分佣值
     * @param $order
     * @param $parentDatas
     * @return void
     */
    private function setCommissoinValues($order, &$parentDatas){

        //生成规则键
        $keyArr = [];
        foreach($parentDatas as $parentData){
            $keyArr[] = $parentData['role_type'];
        }
        $keyStr = implode("#", $keyArr) . "#all";

        //优先使用独立规则
        $rule = CommissionRules::findOne([
            "item_type"      => "checkout",
            "item_id"        => $order['bsh_store_id'],
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
            throw new \Exception("智慧门店小程序订单消费分佣>>订单[ID:".$order['id']."]>>无法获取分佣规则");
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