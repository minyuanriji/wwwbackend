<?php

namespace app\commands\commission_action;

use app\commands\CommissionController;
use app\models\DistrictArr;
use app\models\IncomeLog;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\area\models\AreaSetting;
use app\plugins\commission\models\CommissionRegionPriceLog;
use yii\base\Action;
use yii\db\ActiveQuery;

class RegionGoodsAction extends Action
{

    public function run()
    {
        while (true) {
            if (!defined("ENV") || ENV != "pro") {
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission goods action start");
            }
            sleep(1);
            //商品订单分佣。先检查新增情况，再处理状态改变
            if (!$this->doNew()) {
                $this->doStatusChanged();
            }
            if (!defined("ENV") || ENV != "pro") {
                //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission goods action end");
            }
        }
    }

    /**
     * 商品订单，新增分佣记录
     * @return boolean
     */
    private function doNew()
    {
        //订单已付款、分佣状态未处理
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin("{{%order}} o", "o.id=od.order_id");
        $query->innerJoin(["url" => UserRelationshipLink::tableName()], "url.user_id=o.user_id");
        $query->innerJoin("{{%goods}} g", "g.id=od.goods_id");
        $query->innerJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->andWhere([
            "AND",
            ["o.is_pay" => 1],
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["od.is_delete" => 0],
            ["od.region_commission_status" => 0]
        ]);
        $query->select([
            "o.mall_id", "o.user_id", "o.address",
            "od.id as order_detail_id", "od.num", "od.is_refund", "od.refund_status", "od.created_at",
            "od.updated_at", "od.order_id", "od.goods_id", "od.total_original_price", "od.total_price",
            "g.profit_price", "gw.name",
        ]);
        $orderDetailData = $query->asArray()->one();
        if (!$orderDetailData) {
            return false;
        }

        //获取省市区分佣比列
        $AreaSetting = AreaSetting::find()->select('key,value')->where(['is_delete' => 0])->asArray()->all();
        if (!$AreaSetting) {
            return false;
        }
        $newAreaSetting = array_combine(array_column($AreaSetting, 'key'), $AreaSetting);
        if ($orderDetailData['address']) {
            $address = explode(' ', $orderDetailData['address']);
            if ($address) {
                $DistrictArr = new DistrictArr();
                $province_id = $DistrictArr->getId($address[0]);
                $city_id     = $DistrictArr->getId($address[1], 'city');
                $district_id = $DistrictArr->getId($address[2], 'district');
            } else {
                return false;
            }
        } else {
            return false;
        }

        //获取符合当前门店区域的用户
        $region_user = $this->controller->getRegion($orderDetailData['mall_id'], $province_id, $city_id, $district_id);
        if (!$region_user) {
            return false;
        }

        /**
         * 新增待结算分佣记录
         * @param $userId          用户ID
         * @param $isLianc         是否联创用户收益
         * @param $price           待结算金额
         * @param $totalIncome     当前用户总收益
         * @param $ruleData        分佣规则数据
         * @param $orderDetailData 订单详情数据
         */
        $newPriceLogFunc = function ($userId, $price, $totalIncome, $ruleData, $orderDetailData) {

            $price = min($price, $orderDetailData['profit_price']);

            $uniqueData = [
                "mall_id" => $orderDetailData['mall_id'],
                "item_id" => $orderDetailData['order_detail_id'],
                "item_type" => 'goods',
                "user_id" => $userId,
            ];
            $priceLog = CommissionRegionPriceLog::findOne($uniqueData);
            if (!$priceLog) { //没有生成过再去生成
                $trans = \Yii::$app->db->beginTransaction();
                try {
                    $ruleData['price'] = $price;
                    $priceLog = new CommissionRegionPriceLog(array_merge($uniqueData, [
                        "price" => round($price, 5),
                        "status" => 0,
                        "created_at" => $orderDetailData['created_at'],
                        "updated_at" => $orderDetailData['updated_at'],
                        "rule_data_json" => json_encode($ruleData)
                    ]));
                    if (!$priceLog->save()) {
                        throw new \Exception(json_encode($priceLog->getErrors()));
                    }
                    $this->controller->commandOut("[RegionGoodsAction]生成分佣记录 [ID:" . $priceLog->id . "]");
                    $incomeLog = new IncomeLog([
                        'mall_id' => $orderDetailData['mall_id'],
                        'user_id' => $userId,
                        'type' => 1,
                        'money' => $totalIncome,
                        'income' => $priceLog->price,
                        'desc' => "来自区域商品“" . $orderDetailData['name'] . "”消费分红[ID:" . $priceLog->id . "]",
                        'flag' => 0, //冻结
                        'source_id' => $priceLog->id,
                        'source_type' => 'region_goods',
                        'created_at' => $orderDetailData['created_at'],
                        'updated_at' => $orderDetailData['updated_at']
                    ]);
                    if (!$incomeLog->save()) {
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    User::updateAllCounters([
                        "total_income" => $priceLog->price,
                        "income_frozen" => $priceLog->price
                    ], ["id" => $userId]);

                    $trans->commit();
                } catch (\Exception $e) {
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }
        };

        try {

            if ($orderDetailData['is_refund'] && $orderDetailData['refund_status'] == 20) {
                throw new \Exception("订单商品[ID:" . $orderDetailData['order_detail_id'] . "]已退款");
            }

            foreach ($region_user as $value) {
                $user = User::findOne($value['user_id']);
                if (!$user) {
                    throw new \Exception("用户[ID:".($user ? $user->id : 0)."]不存在");
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
                $rule_data_json['profit_price'] = $orderDetailData['profit_price'];
                $price = (floatval($rule_data_json['commisson_value']) / 100) * floatval($rule_data_json['profit_price']);
                $rule_data_json['commission_type'] = 1;
                $rule_data_json['agent_level'] = $value['level'];
                //生成分佣记录
                if ($price > 0) {
                    $newPriceLogFunc($value['user_id'], $price, $user->total_income, $rule_data_json, $orderDetailData);
                }
            }
        } catch (\Exception $e) {
            $this->controller->commandOut($e->getMessage());
        }

        //更新为已处理
        OrderDetail::updateAll(["region_commission_status" => 1], ["id" => $orderDetailData['order_detail_id']]);
        return true;
    }

    /**
     * 商品订单状态改变，更新分佣记录
     * @return boolean
     */
    private function doStatusChanged()
    {
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin("{{%order}} o", "o.id=od.order_id");
        $query->innerJoin("{{%plugin_commission_region_price_log}} crpl", "crpl.item_id=od.id");
        $query->andWhere([
            "AND",
            ["crpl.status" => 0],
            ["crpl.item_type" => 'goods'],
        ])
            ->orderBy("crpl.updated_at ASC")->asArray();
        $query->select(["crpl.*"]);

        //商品订单已确认收货，分佣到账
        $newQuery = clone $query;
        $this->doStatusSuccess($newQuery);

        //商品订单退款、取消，分佣扣除
        $newQuery = clone $query;
        $this->doStatusCancel($newQuery);
    }

    //商品订单已确认收货，分佣到账
    private function doStatusSuccess(ActiveQuery $query)
    {
        $priceLogs = $query->andWhere([
            "AND",
            ["IN", "o.status", [3, 6, 7, 8]],
            "(od.is_refund='0' OR (od.is_refund='1' AND od.refund_status='21'))"
        ])->limit(10)->all();
        if ($priceLogs) {
            $priceLogIds = [];
            //先更新时间
            foreach ($priceLogs as $priceLog) {
                $priceLogIds[] = $priceLog['id'];
            }
            CommissionRegionPriceLog::updateAll(["updated_at" => time()], "id IN(" . implode(",", $priceLogIds) . ")");

            //开始分佣到账
            foreach ($priceLogs as $priceLog) {
                $trans = \Yii::$app->db->beginTransaction();
                try {

                    //更新分佣记录为已完成
                    CommissionRegionPriceLog::updateAll([
                        "status" => 1
                    ], ["id" => $priceLog['id']]);

                    //取消收入记录的冻结状态
                    $incomeLog = IncomeLog::findOne([
                        "source_id" => $priceLog['id'],
                        "source_type" => "region_goods",
                        "flag" => 0
                    ]);
                    if ($incomeLog) {
                        $incomeLog->flag = 1;
                        $incomeLog->updated_at = time();
                        if (!$incomeLog->save()) {
                            throw new \Exception(json_encode($incomeLog->getErrors()));
                        }
                        //更新用户收入信息
                        User::updateAllCounters([
                            "income" => $priceLog['price'],
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }

                    $trans->commit();
                    $this->controller->commandOut("分佣记录 [ID:" . $priceLog['id'] . "] 已完成");
                } catch (\Exception $e) {
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }

        }
    }

    //商品订单退款、取消，分佣扣除
    private function doStatusCancel(ActiveQuery $query)
    {
        $priceLogs = $query->andWhere([
            "OR",
            ["od.is_refund" => 1],
            ["IN", "od.refund_status", [20]]
        ])->limit(10)->all();
        if ($priceLogs) {
            $priceLogIds = [];
            //先更新时间
            foreach ($priceLogs as $priceLog) {
                $priceLogIds[] = $priceLog['id'];
            }
            CommissionRegionPriceLog::updateAll(["updated_at" => time()], "id IN(" . implode(",", $priceLogIds) . ")");

            //取消分佣
            foreach ($priceLogs as $priceLog) {
                $trans = \Yii::$app->db->beginTransaction();
                try {

                    //更新分佣记录为已取消
                    CommissionRegionPriceLog::updateAll([
                        "status" => -1
                    ], ["id" => $priceLog['id']]);

                    //新增一条收入支出记录
                    $user = User::findOne($priceLog['user_id']);
                    if ($user) {
                        $totalIncome = $user->income + $user->income_frozen;
                        $incomeLog = new IncomeLog([
                            'mall_id' => $priceLog['mall_id'],
                            'user_id' => $priceLog['user_id'],
                            'type' => 2,
                            'money' => $totalIncome,
                            'income' => $priceLog['price'],
                            'desc' => "分佣记录 [ID:" . $priceLog['id'] . "] 已取消，扣除冻结佣金",
                            'flag' => 0, //冻结
                            'source_id' => $priceLog['id'],
                            'source_type' => 'region_goods',
                            'created_at' => time(),
                            'updated_at' => time()
                        ]);
                        if (!$incomeLog->save()) {
                            throw new \Exception(json_encode($incomeLog->getErrors()));
                        }

                        //更新用户收入信息
                        User::updateAllCounters([
                            "total_income" => -1 * abs($priceLog['price']),
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }

                    $trans->commit();
                    $this->controller->commandOut("分佣记录 [ID:" . $priceLog['id'] . "] 已取消");
                } catch (\Exception $e) {
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }
        }
    }
}