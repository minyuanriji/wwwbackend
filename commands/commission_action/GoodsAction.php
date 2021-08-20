<?php

namespace app\commands\commission_action;

use app\models\IncomeLog;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use yii\base\Action;
use yii\db\ActiveQuery;

class GoodsAction extends Action{

    public function run(){
       while (true){
           if(!defined("ENV") || ENV != "pro"){
               //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission goods action start");
           }
           sleep(1);
           //商品订单分佣。先检查新增情况，再处理状态改变
           if(!$this->doNew()){
               $this->doStatusChanged();
           }
           if(!defined("ENV") || ENV != "pro"){
               //$this->controller->commandOut(date("Y/m/d H:i:s") . " commission goods action end");
           }
       }
    }

    /**
     * 商品订单，新增分佣记录
     * @return boolean
     */
    private function doNew(){

        //订单已付款、分佣状态未处理
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin("{{%order}} o", "o.id=od.order_id");
        $query->innerJoin(["u" => User::tableName()], "u.id=o.user_id");
        $query->innerJoin("{{%goods}} g", "g.id=od.goods_id");
        $query->innerJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->leftJoin(["lianc_u" => User::tableName()], "lianc_u.id=g.lianc_user_id AND lianc_u.is_lianc=1 AND lianc_u.is_delete=0");
        $query->andWhere([
            "AND",
            ["o.is_pay" => 1],
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["od.is_delete" => 0],
            ["od.commission_status" => 0]
        ]);
        $query->select([
            "od.id as order_detail_id", "od.num", "od.is_refund", "od.refund_status", "od.created_at",
            "od.updated_at", "o.mall_id", "o.user_id", "u.parent_id", "od.order_id", "od.goods_id", "od.total_original_price",
            "od.total_price", "g.profit_price", "gw.name","g.first_buy_setting",
            "lianc_u.id as lianc_user_id", "g.lianc_commission_type", "g.lianc_commisson_value",
            "(lianc_u.income+lianc_u.income_frozen) as lianc_total_income"
        ]);
        $orderDetailData = $query->asArray()->one();
        if(!$orderDetailData){
            return false;
        }

        /**
         * 计算分佣金额
         * @param $profitPrice      商品利润
         * @param $num              数量
         * @param $commissonType    分佣类型
         * @param $commissonValue   分佣值
         * @return float|int
         */
        $getCommissionPriceFunc = function($profitPrice, $num, $commissonType, $commissonValue){
            if($commissonType == 1){ //按百分比
                $price = (floatval($commissonValue)/100) * floatval($profitPrice);
            }else{ //按固定值
                $price = (float)$commissonValue;
            }
            return $price * intval($num);
        };

        /**
         * 新增待结算分佣记录
         * @param $userId          用户ID
         * @param $isLianc         是否联创用户收益
         * @param $price           待结算金额
         * @param $totalIncome     当前用户总收益
         * @param $ruleData        分佣规则数据
         * @param $orderDetailData 订单详情数据
         */
        $newPriceLogFunc = function($userId, $isLianc, $price, $totalIncome, $ruleData, $orderDetailData){

            $price = min($price, $orderDetailData['profit_price']);

            $uniqueData = [
                "mall_id"         => $orderDetailData['mall_id'],
                "order_id"        => $orderDetailData['order_id'],
                "order_detail_id" => $orderDetailData['order_detail_id'],
                "goods_id"        => $orderDetailData['goods_id'],
                "user_id"         => $userId,
                "is_lianc"        => (int)$isLianc
            ];
            $priceLog = CommissionGoodsPriceLog::findOne($uniqueData);
            if(!$priceLog){ //没有生成过再去生成
                $trans = \Yii::$app->db->beginTransaction();
                try {
                    $ruleData['price'] = $price;
                    $priceLog = new CommissionGoodsPriceLog(array_merge($uniqueData, [
                        "price"           => round($price, 5),
                        "status"          => 0,
                        "created_at"      => $orderDetailData['created_at'],
                        "updated_at"      => $orderDetailData['updated_at'],
                        "rule_data_json"  => json_encode($ruleData)
                    ]));
                    if(!$priceLog->save()){
                        throw new \Exception(json_encode($priceLog->getErrors()));
                    }
                    $this->controller->commandOut("生成分佣记录 [ID:".$priceLog->id."]");

                    //收入记录
                    if($isLianc){
                        $desc = "来自品牌商合作商品“".$orderDetailData['name']."”消费结算[ID:".$priceLog->id."]";
                    }else{
                        $desc = "来自商品“".$orderDetailData['name']."”消费分佣[ID:".$priceLog->id."]";
                    }
                    $incomeLog = new IncomeLog([
                        'mall_id'     => $orderDetailData['mall_id'],
                        'user_id'     => $userId,
                        'type'        => 1,
                        'money'       => $totalIncome,
                        'income'      => $priceLog->price,
                        'desc'        => $desc,
                        'flag'        => 0, //冻结
                        'source_id'   => $priceLog->id,
                        'source_type' => 'goods',
                        'created_at'  => $orderDetailData['created_at'],
                        'updated_at'  => $orderDetailData['updated_at']
                    ]);
                    if(!$incomeLog->save()){
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    User::updateAllCounters([
                        "total_income"  => $priceLog->price,
                        "income_frozen" => $priceLog->price
                    ], ["id" => $userId]);

                    $trans->commit();
                }catch (\Exception $e){
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }
        };

        try {

            if($orderDetailData['is_refund'] && $orderDetailData['refund_status'] == 20){
                throw new \Exception("订单商品[ID:".$orderDetailData['order_detail_id']."]已退款");
            }


            //联创合伙人收益
            $liancUserId = null;
            if(!empty($orderDetailData['lianc_user_id'])){
                $liancData = [
                    'lianc_commission_type' => $orderDetailData['lianc_commission_type'],
                    'lianc_commisson_value' => $orderDetailData['lianc_commisson_value']
                ];

                $liancData['profit_price'] = $orderDetailData['profit_price'];
                $price = $getCommissionPriceFunc($liancData['profit_price'], $orderDetailData['num'], $liancData['lianc_commission_type'], $liancData['lianc_commisson_value']);
                if($price > 0){
                    $newPriceLogFunc($orderDetailData['lianc_user_id'], 1, $price, $orderDetailData['lianc_total_income'], $liancData, $orderDetailData);
                }

                //如果消费用户是品牌商直推的，品牌商临时升级成分公司
                if($orderDetailData['parent_id'] == $orderDetailData['lianc_user_id']){
                    $liancUserId = $orderDetailData['lianc_user_id'];
                }
            }

            $parentDatas = $this->controller->getCommissionParentRuleDatas($orderDetailData['user_id'], $orderDetailData['goods_id'], 'goods', $liancUserId);

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData){

                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if(!$ruleData) continue;

                //计算分佣金额
                $ruleData['profit_price'] = $orderDetailData['profit_price'];
                $price = $getCommissionPriceFunc($ruleData['profit_price'], $orderDetailData['num'], $ruleData['commission_type'], $ruleData['commisson_value']);

                //判断该商品是否设置首次利润
                if ($orderDetailData['first_buy_setting']) {
                    $first_buy_setting = json_decode($orderDetailData['first_buy_setting'], true);
                    if (isset($first_buy_setting['buy_num']) && $first_buy_setting['buy_num'] > 0) {
                        //查询该商品该用户购买过几次
                        $fields = ["sum(od.num) as total","od.goods_id"];
                        $params["order"] = 1;
                        $params["user_id"] = $orderDetailData['user_id'];
                        $params["is_pay"] = 1;
                        $params["goods_id"] = $orderDetailData['goods_id'];
                        $params["mall_id"] = $orderDetailData['mall_id'];
                        $params["is_one"] = 1;
                        $sameOrderList = OrderDetail::getSameCatsGoodsOrderTotal($params,$fields);
                        $sameOrderList['total'] = $sameOrderList['total'] - $orderDetailData['num'];
                        $total = 0;
                        if(!empty($sameOrderList)){
                            $total = max(0, $sameOrderList['total']);
                        }
                        $surplus_num = $first_buy_setting['buy_num'] - $total; //可以购买次数 - 已经购买次数 = 剩余可购买次数
                        if ($surplus_num > 0) {
                            $profit_num = $surplus_num - $orderDetailData['num']; //剩余次数 - 当前购买次数 = 剩剩余可购买次数
                            if ($profit_num >= 0) {
                                $ruleData['profit_price'] = $first_buy_setting['return_commission'];
                                if($ruleData['commission_type'] == 1){ //按百分比
                                    $price = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                                }else{ //按固定值
                                    $price = (float)$ruleData['profit_price'];
                                }
                                $price = $price * intval($orderDetailData['num']);
                            } elseif ($profit_num < 0) {
                                if($ruleData['commission_type'] == 1){ //按百分比
                                    $price_one = (floatval($ruleData['commisson_value'])/100) * floatval($first_buy_setting['return_commission']);
                                }else{ //按固定值
                                    $price_one = (float)$first_buy_setting['return_commission'];
                                }
                                if($ruleData['commission_type'] == 1){ //按百分比
                                    $price_two = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                                }else{ //按固定值
                                    $price_two = (float)$ruleData['commisson_value'];
                                }
                                $ruleData['profit_price'] = $first_buy_setting['return_commission'] * $surplus_num + $ruleData['profit_price'] * abs($profit_num);
                                $price = $surplus_num * $price_one + intval(abs($profit_num)) * $price_two;
                            }
                        }
                    }
                }

                //生成分佣记录
                if($price > 0){
                    $newPriceLogFunc($parentData['id'], 0, $price, $parentData['total_income'], $ruleData, $orderDetailData);
                }
            }
        }catch (\Exception $e){
            $this->controller->commandOut($e->getMessage());
        }

        //更新为已处理
        OrderDetail::updateAll(["commission_status" => 1], ["id" => $orderDetailData['order_detail_id']]);

        return true;
    }

    /**
     * 商品订单状态改变，更新分佣记录
     * @return boolean
     */
    private function doStatusChanged(){
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin("{{%order}} o", "o.id=od.order_id");
        $query->innerJoin("{{%plugin_commission_goods_price_log}} cgpl", "cgpl.order_detail_id=od.id");
        $query->andWhere([
            "AND",
            ["cgpl.status" => 0]
        ])->orderBy("cgpl.updated_at ASC")->asArray();
        $query->select(["cgpl.*"]);

        //商品订单已确认收货，分佣到账
        $newQuery = clone $query;
        $this->doStatusSuccess($newQuery);

        //商品订单退款、取消，分佣扣除
        $newQuery = clone $query;
        $this->doStatusCancel($newQuery);
    }

    //商品订单已确认收货，分佣到账
    private function doStatusSuccess(ActiveQuery $query){
        $priceLogs = $query->andWhere([
            "AND",
            ["IN", "o.status", [3, 6, 7, 8]],
            "(od.is_refund='0' OR (od.is_refund='1' AND od.refund_status='21'))"
        ])->limit(10)->all();
        if($priceLogs){
            $priceLogIds = [];
            //先更新时间
            foreach($priceLogs as $priceLog){
                $priceLogIds[] = $priceLog['id'];
            }
            CommissionGoodsPriceLog::updateAll(["updated_at" => time()], "id IN(".implode(",", $priceLogIds).")");

            //开始分佣到账
            foreach($priceLogs as $priceLog){
                $trans = \Yii::$app->db->beginTransaction();
                try {

                    //更新分佣记录为已完成
                    CommissionGoodsPriceLog::updateAll([
                        "status" => 1
                    ], ["id" => $priceLog['id']]);

                    //取消收入记录的冻结状态
                    $incomeLog = IncomeLog::findOne([
                        "source_id"   => $priceLog['id'],
                        "source_type" => "goods",
                        "flag"        => 0
                    ]);
                    if($incomeLog){
                        $incomeLog->flag = 1;
                        $incomeLog->updated_at = time();
                        if(!$incomeLog->save()){
                            throw new \Exception(json_encode($incomeLog->getErrors()));
                        }
                        //更新用户收入信息
                        User::updateAllCounters([
                            "income"        => $priceLog['price'],
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }

                    $trans->commit();

                    $this->controller->commandOut("分佣记录 [ID:".$priceLog['id']."] 已完成");
                }catch (\Exception $e){
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }

        }
    }

    //商品订单退款、取消，分佣扣除
    private function doStatusCancel(ActiveQuery $query){
        $priceLogs = $query->andWhere([
            "OR",
            ["od.is_refund" => 1],
            ["IN", "od.refund_status", [20]]
        ])->limit(10)->all();
        if($priceLogs){
            $priceLogIds = [];
            //先更新时间
            foreach($priceLogs as $priceLog){
                $priceLogIds[] = $priceLog['id'];
            }
            CommissionGoodsPriceLog::updateAll(["updated_at" => time()], "id IN(".implode(",", $priceLogIds).")");

            //取消分佣
            foreach($priceLogs as $priceLog){
                $trans = \Yii::$app->db->beginTransaction();
                try {

                    //更新分佣记录为已取消
                    CommissionGoodsPriceLog::updateAll([
                        "status" => -1
                    ], ["id" => $priceLog['id']]);

                    //新增一条收入支出记录
                    $user = User::findOne($priceLog['user_id']);
                    if($user){
                        $totalIncome = $user->income + $user->income_frozen;
                        $incomeLog = new IncomeLog([
                            'mall_id'     => $priceLog['mall_id'],
                            'user_id'     => $priceLog['user_id'],
                            'type'        => 2,
                            'money'       => $totalIncome,
                            'income'      => $priceLog['price'],
                            'desc'        => "分佣记录 [ID:".$priceLog['id']."] 已取消，扣除冻结佣金",
                            'flag'        => 0, //冻结
                            'source_id'   => $priceLog['id'],
                            'source_type' => 'goods',
                            'created_at'  => time(),
                            'updated_at'  => time()
                        ]);
                        if(!$incomeLog->save()){
                            throw new \Exception(json_encode($incomeLog->getErrors()));
                        }

                        //更新用户收入信息
                        User::updateAllCounters([
                            "total_income"  => -1 * abs($priceLog['price']),
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }


                    $trans->commit();

                    $this->controller->commandOut("分佣记录 [ID:".$priceLog['id']."] 已取消");
                }catch (\Exception $e){
                    $trans->rollBack();
                    $this->controller->commandOut($e->getMessage());
                }
            }
        }
    }
}