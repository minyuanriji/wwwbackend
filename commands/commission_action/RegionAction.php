<?php

namespace app\commands\commission_action;

use app\models\IncomeLog;
use app\models\User;
use app\plugins\area\models\AreaAgent;
use app\plugins\area\models\AreaSetting;
use app\plugins\commission\models\CommissionRegionPriceLog;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\base\Action;

class RegionAction extends Action
{

    public function run()
    {
        while (true) {
            if (!defined("ENV") || ENV != "pro") {
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission Region action start");
            }
            sleep(1);
            $this->doNew();
            if (!defined("ENV") || ENV != "pro") {
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission Region action end");
            }
        }
    }

    /**
     * 二维码收款订单，新增分红记录----区域分红
     * @return boolean
     */
    private function doNew()
    {
        $query = MchCheckoutOrder::find()->alias("mco");
        $query->innerJoin("{{%store}} s", "s.id=mco.store_id");
        $query->innerJoin("{{%plugin_mch}} m", "m.id=s.mch_id");
        $query->andWhere([
            "AND",
            ["mco.is_pay" => 1],
            ["mco.is_delete" => 0],
            ["mco.region_commission_status" => 0]
        ]);
        $query->select(["mco.*", "s.name", "m.transfer_rate", "m.integral_fee_rate", "m.user_id", "s.province_id", "s.city_id", "s.district_id"]);
        $checkoutOrders = $query->asArray()->limit(10)->orderBy('id DESC')->all();

        if (!$checkoutOrders) {
            return false;
        }

        //获取省市区分佣比列
        $AreaSetting = AreaSetting::find()->select('key,value')->where(['is_delete' => 0])->asArray()->all();
        if (!$AreaSetting) {
            return false;
        }
        $newAreaSetting = array_combine(array_column($AreaSetting, 'key'),$AreaSetting);

        foreach ($checkoutOrders as $checkoutOrder) {

            try {
                //获取符合当前门店区域的用户
                $region_user = $this->controller->getRegion($checkoutOrder['mall_id'], $checkoutOrder['province_id'], $checkoutOrder['city_id'], $checkoutOrder['district_id']);
                if (!$region_user) {
                    continue;
                }

                //计算分佣金额
                $transferRate = (int)$checkoutOrder['transfer_rate'];//商户手续费
                $integralFeeRate = (int)$checkoutOrder['integral_fee_rate'];
                $rule_data_json['profit_price'] = $this->controller->calculateCheckoutOrderProfitPrice($checkoutOrder['order_price'], $transferRate, $integralFeeRate);

                foreach ($region_user as $value) {
                    $user = User::findOne($value['user_id']);
                    if (!$user) {
                        throw new \Exception("商铺用户[ID:".($user ? $user->id : 0)."]不存在");
                    }

                    if ($value['level'] == 4) {
                        $rule_data_json['commisson_value'] = $newAreaSetting['province_price']['value'];
                    } else if ($value['level'] == 3) {
                        $rule_data_json['commisson_value'] = $newAreaSetting['city_price']['value'];
                    } else if ($value['level'] == 2) {
                        $rule_data_json['commisson_value'] = $newAreaSetting['district_price']['value'];
                    } else {
                        continue;
                    }
                    $price = (floatval($rule_data_json['commisson_value']) / 100) * floatval($rule_data_json['profit_price']);
                    $rule_data_json['commission_type'] = 1;
                    //生成分佣记录
                    if ($price > 0) {
                        $priceLog = CommissionRegionPriceLog::findOne([
                            "user_id" => $value['user_id'],
                            "item_id" => $checkoutOrder['id'],
                            "item_type" => 'checkout',
                        ]);
                        if (!$priceLog) { //没有生成过再去生成
                            $trans = \Yii::$app->db->beginTransaction();
                            try {
                                $priceLog = new CommissionRegionPriceLog([
                                    "mall_id" => $checkoutOrder['mall_id'],
                                    "item_id" => $checkoutOrder['id'],
                                    "item_type" => 'checkout',
                                    "user_id" => $value['user_id'],
                                    "price" => round($price, 5),
                                    "status" => 1,
                                    "created_at" => time(),
                                    "updated_at" => time(),
                                    "rule_data_json" => json_encode($rule_data_json)
                                ]);
                                if (!$priceLog->save()) {
                                    throw new \Exception(json_encode($priceLog->getErrors()));
                                }
                                $this->controller->commandOut("[RegionAction]生成分佣记录 [ID:" . $priceLog->id . "]");

                                //收入记录
                                $incomeLog = new IncomeLog([
                                    'mall_id' => $checkoutOrder['mall_id'],
                                    'user_id' => $value['user_id'],
                                    'type' => 1,
                                    'money' => $user->total_income,
                                    'income' => $priceLog->price,
                                    'desc' => "来自店铺“" . $checkoutOrder['name'] . "”的区域分红记录[ID:" . $priceLog->id . "]",
                                    'flag' => 1, //到账
                                    'source_id' => $priceLog->id,
                                    'source_type' => 'region_checkout',
                                    'created_at' => time(),
                                    'updated_at' => time()
                                ]);
                                if (!$incomeLog->save()) {
                                    throw new \Exception(json_encode($incomeLog->getErrors()));
                                }

                                User::updateAllCounters([
                                    "total_income" => $priceLog->price,
                                    "income" => $priceLog->price
                                ], ["id" => $user->id]);

                                $trans->commit();
                            } catch (\Exception $e) {
                                $trans->rollBack();
                                $this->controller->commandOut($e->getMessage());
                                $this->controller->commandOut("line:" . $e->getLine());
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->controller->commandOut($e->getMessage());
                $this->controller->commandOut("line:" . $e->getLine());
            }

            MchCheckoutOrder::updateAll([
                "region_commission_status" => 1
            ], ["id" => $checkoutOrder['id']]);
        }

        return true;
    }
}
