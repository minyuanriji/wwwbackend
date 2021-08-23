<?php

namespace app\commands\commission_action;


use app\models\IncomeLog;
use app\models\User;
use app\plugins\commission\models\CommissionRules;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\base\Action;

class StoreAction extends Action{

    public function run(){
        while (true){
            if(!defined("ENV") || ENV != "pro"){
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission store action start");
            }
            sleep(1);
            $this->doNew(); //店铺二维码收款分佣
            if(!defined("ENV") || ENV != "pro"){
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission store action end");
            }
        }
    }

    /**
     * 二维码收款订单，新增分佣记录----店铺二维码收款上级推荐人分佣
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
            ["mco.store_commission_status" => 0]
        ]);
        $query->select(["mco.*", "s.name", "m.transfer_rate", "m.integral_fee_rate", "m.user_id"]);
        $checkoutOrders = $query->asArray()->limit(10)->all();
        if(!$checkoutOrders){
            return false;
        }
        foreach ($checkoutOrders as $checkoutOrder){

            try {
                //获取店铺用户信息及上级用户
                $user = User::findOne($checkoutOrder['user_id']);
                if (!$user)
                    throw new \Exception("商铺用户[ID:".($user ? $user->id : 0)."]不存在");

                $parent_user = User::findOne($user->parent_id);
                if (!$parent_user)
                    throw new \Exception("商铺上级用户[ID:".($parent_user ? $parent_user->id : 0)."]不存在");

                if ($parent_user->role_type == 'user')
                    throw new \Exception("普通用户不分佣");

                //获取当前店铺分佣规则
                $query = CommissionRules::find()->alias("cr");
                $query->leftJoin("{{%plugin_commission_rule_chain}} crc", "cr.id=crc.rule_id");
                $newQuery = clone $query;
                $query->andWhere([
                    "AND",
                    ["cr.item_type"  => 'store'],
                    ["cr.item_id"    => $checkoutOrder['store_id']],
                    ['cr.is_delete'  => 0],
                ]);
                $commission_res = $query->select(["cr.commission_type", "crc.level", "crc.commisson_value"])->asArray()->one();

                if (!$commission_res) {

                    //查询是否设置公共规则
                    $commission_res = $newQuery->andWhere([
                        "AND",
                        ["cr.item_type"         => 'store'],
                        ["cr.apply_all_item"    => 1],
                        ['cr.is_delete'         => 0],
                    ])->select(["cr.commission_type", "crc.level", "crc.commisson_value"])->asArray()->one();

                    if (!$commission_res) {
                        $this->controller->commandOut('没有分佣规则');
                        continue;
                    }
                }
                //计算分佣金额
                $transferRate = (int)$checkoutOrder['transfer_rate'];//商户手续费
                $integralFeeRate = (int)$checkoutOrder['integral_fee_rate'];
                $commission_res['profit_price'] = $this->controller->calculateCheckoutOrderProfitPrice($checkoutOrder['order_price'], $transferRate, $integralFeeRate);
                if($commission_res['commission_type'] == 1){ //按百分比
                    $price = (floatval($commission_res['commisson_value'])/100) * floatval($commission_res['profit_price']);
                }else{ //按固定值
                    $price = (float)$commission_res['commisson_value'];
                }
                //生成分佣记录
                if($price > 0){
                    $priceLog = CommissionStorePriceLog::findOne([
                        "user_id"           => $user->parent_id,
                        "item_id"           => $checkoutOrder['id'],
                        "item_type"         => 'checkout',
                    ]);
                    if(!$priceLog){ //没有生成过再去生成
                        $trans = \Yii::$app->db->beginTransaction();
                        try {
                            $priceLog = new CommissionStorePriceLog([
                                "mall_id"           => $checkoutOrder['mall_id'],
                                "item_id"           => $checkoutOrder['id'],
                                "item_type"         => 'checkout',
                                "user_id"           => $user->parent_id,
                                "price"             => round($price, 5),
                                "status"            => 1,
                                "created_at"        => $checkoutOrder['created_at'],
                                "updated_at"        => $checkoutOrder['updated_at'],
                                "rule_data_json"    => json_encode($commission_res)
                            ]);
                            if(!$priceLog->save()){
                                throw new \Exception(json_encode($priceLog->getErrors()));
                            }
                            $this->controller->commandOut("生成分佣记录 [ID:".$priceLog->id."]");

                            //收入记录
                            $incomeLog = new IncomeLog([
                                'mall_id'     => $checkoutOrder['mall_id'],
                                'user_id'     => $user->parent_id,
                                'type'        => 1,
                                'money'       => $parent_user->total_income,
                                'income'      => $priceLog->price,
                                'desc'        => "来自店铺“".$checkoutOrder['name']."”的营业额分佣记录[ID:".$priceLog->id."]",
                                'flag'        => 1, //到账
                                'source_id'   => $priceLog->id,
                                'source_type' => 'store',
                                'created_at'  => $checkoutOrder['created_at'],
                                'updated_at'  => $checkoutOrder['updated_at']
                            ]);
                            if(!$incomeLog->save()){
                                throw new \Exception(json_encode($incomeLog->getErrors()));
                            }

                            User::updateAllCounters([
                                "total_income"  => $priceLog->price,
                                "income" => $priceLog->price
                            ], ["id" => $parent_user->id]);

                            $trans->commit();
                        }catch (\Exception $e){
                            $trans->rollBack();
                            $this->controller->commandOut($e->getMessage());
                            $this->controller->commandOut("line:" . $e->getLine());
                        }
                    }
                }

            }catch (\Exception $e){
                $this->controller->commandOut($e->getMessage());
                $this->controller->commandOut("line:" . $e->getLine());
            }

            MchCheckoutOrder::updateAll([
                "store_commission_status" => 1
            ], ["id" => $checkoutOrder['id']]);
        }

        return true;
    }
}
