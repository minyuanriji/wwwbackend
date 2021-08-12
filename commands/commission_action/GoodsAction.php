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
        $query->innerJoin("{{%goods}} g", "g.id=od.goods_id");
        $query->innerJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->andWhere([
            "AND",
            ["o.is_pay" => 1],
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["od.is_delete" => 0],
            ["od.commission_status" => 0]
        ]);
        $query->select(["od.id as order_detail_id", "od.num", "od.is_refund", "od.refund_status", "od.created_at", "od.updated_at", "o.mall_id", "o.user_id", "od.order_id", "od.goods_id", "od.total_original_price", "od.total_price", "g.profit_price", "gw.name","g.first_buy_setting"]);
        $orderDetailData = $query->asArray()->one();
        if(!$orderDetailData){
            return false;
        }
        try {
            if($orderDetailData['is_refund'] && $orderDetailData['refund_status'] == 20){
                throw new \Exception("订单商品[ID:".$orderDetailData['order_detail_id']."]已退款");
            }

            $parentDatas = $this->controller->getCommissionParentRuleDatas($orderDetailData['user_id'], $orderDetailData['goods_id'], 'goods');

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData){

                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if(!$ruleData) continue;

                //计算分佣金额
                $ruleData['profit_price'] = $orderDetailData['profit_price'];
                if($ruleData['commission_type'] == 1){ //按百分比
                    $price = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                }else{ //按固定值
                    $price = (float)$ruleData['commisson_value'];
                }
                $price = $price * intval($orderDetailData['num']);

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
                    $priceLog = CommissionGoodsPriceLog::findOne([
                        "order_id"        => $orderDetailData['order_id'],
                        "order_detail_id" => $orderDetailData['order_detail_id'],
                        "goods_id"        => $orderDetailData['goods_id'],
                        "user_id"         => $parentData['id'],
                    ]);
                    if(!$priceLog){ //没有生成过再去生成
                        $trans = \Yii::$app->db->beginTransaction();
                        try {
                            $priceLog = new CommissionGoodsPriceLog([
                                "mall_id"         => $orderDetailData['mall_id'],
                                "order_id"        => $orderDetailData['order_id'],
                                "order_detail_id" => $orderDetailData['order_detail_id'],
                                "goods_id"        => $orderDetailData['goods_id'],
                                "user_id"         => $parentData['id'],
                                "price"           => round($price, 5),
                                "status"          => 0,
                                "created_at"      => $orderDetailData['created_at'],
                                "updated_at"      => $orderDetailData['updated_at'],
                                "rule_data_json"  => json_encode($ruleData)
                            ]);
                            if(!$priceLog->save()){
                                throw new \Exception(json_encode($priceLog->getErrors()));
                            }
                            $this->controller->commandOut("生成分佣记录 [ID:".$priceLog->id."]");

                            //收入记录
                            $incomeLog = new IncomeLog([
                                'mall_id'     => $orderDetailData['mall_id'],
                                'user_id'     => $parentData['id'],
                                'type'        => 1,
                                'money'       => $parentData['total_income'],
                                'income'      => $priceLog->price,
                                'desc'        => "来自商品“".$orderDetailData['name']."”分佣记录[ID:".$priceLog->id."]",
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
                            ], ["id" => $parentData['id']]);

                            $trans->commit();
                        }catch (\Exception $e){
                            $trans->rollBack();
                            $this->controller->commandOut($e->getMessage());
                        }

                    }
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
                    IncomeLog::updateAll([
                        "flag" => 1
                    ], ["source_id" => $priceLog['id'], "source_type" => "goods"]);

                    //更新用户收入信息
                    User::updateAllCounters([
                        "income" => $priceLog['price'],
                        "income_frozen" => -1 * abs($priceLog['price'])
                    ], ["id" => $priceLog['user_id']]);

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
                    $userData = User::find()->where(["id" => $priceLog['user_id']])->select(["total_income"])->one();
                    $totalIncome = $userData ? $userData['total_income'] : 0;
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