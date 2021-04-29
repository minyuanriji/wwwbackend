<?php
namespace app\commands;


use app\models\IncomeLog;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\commission\models\CommissionCheckoutPriceLog;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\db\ActiveQuery;

class CommissionController extends BaseCommandController{

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 分佣守候程序启动...完成\n";

        while(true){
            $this->sleep(1);
            try {
                //商品订单分佣。先检查新增情况，再处理状态改变
                if(!$this->goodsOrderNew()){
                    $this->goodsStatusChanged();
                }

                //店铺二维码收款分佣
                $this->checkoutOrderNew();

            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
            }
        }
    }

    /**
     * 二维码收款订单，新增分佣记录
     * @return boolean
     */
    private function checkoutOrderNew(){
        $query = MchCheckoutOrder::find()->alias("mco");
        $query->innerJoin("{{%store}} s", "s.id=mco.store_id");
        $query->innerJoin("{{%plugin_mch}} m", "m.id=s.mch_id");
        $query->andWhere([
            "AND",
            ["mco.is_pay" => 1],
            ["mco.is_delete" => 0],
            ["mco.commission_status" => 0]
        ]);
        $query->select(["mco.*", "s.name", "m.transfer_rate"]);
        $checkoutOrders = $query->asArray()->limit(10)->all();
        if(!$checkoutOrders){
            return false;
        }

        foreach($checkoutOrders as $checkoutOrder){

            try {
                $parentDatas = $this->getCommissionParentRuleDatas($checkoutOrder['pay_user_id'], $checkoutOrder['store_id'], 'checkout');

                //通过相关规则键获取分佣规则进行分佣
                foreach($parentDatas as $parentData) {

                    $ruleData = $parentData['rule_data'];

                    //无分佣规则 跳过
                    if (!$ruleData) continue;

                    //计算分佣金额
                    $transferRate = (int)$checkoutOrder['transfer_rate'];
                    $ruleData['profit_price'] = ($transferRate/100) * $checkoutOrder['order_price'];
                    if($ruleData['commission_type'] == 1){ //按百分比
                        $price = (floatval($ruleData['commisson_value'])/100) * floatval($ruleData['profit_price']);
                    }else{ //按固定值
                        $price = (float)$ruleData['commisson_value'];
                    }

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
                                $this->commandOut("生成分佣记录 [ID:".$priceLog->id."]");

                                //收入记录
                                $incomeLog = new IncomeLog([
                                    'mall_id'     => $checkoutOrder['mall_id'],
                                    'user_id'     => $parentData['id'],
                                    'type'        => 1,
                                    'money'       => $parentData['total_income'],
                                    'income'      => $priceLog->price,
                                    'desc'        => "来自店铺“".$checkoutOrder['name']."”的分佣记录[ID:".$priceLog->id."]",
                                    'flag'        => 1, //到账
                                    'source_id'   => $priceLog->id,
                                    'source_type' => 'checkout',
                                    'created_at'  => $checkoutOrder['created_at'],
                                    'updated_at'  => $checkoutOrder['updated_at']
                                ]);
                                if(!$incomeLog->save()){
                                    throw new \Exception(json_encode($incomeLog->getErrors()));
                                }

                                User::updateAllCounters([
                                    "total_income"  => $priceLog->price,
                                    "income" => $priceLog->price
                                ], ["id" => $parentData['id']]);

                                $trans->commit();
                            }catch (\Exception $e){
                                $trans->rollBack();
                                $this->commandOut($e->getMessage());
                            }
                        }
                    }


                }
            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
            }



            MchCheckoutOrder::updateAll([
                "commission_status" => 1
            ], ["id" => $checkoutOrder['id']]);
        }

        return true;
    }

    /**
     * 获取要分佣的父级列表
     * @return array
     */
    private function getCommissionParentRuleDatas($user_id, $item_id, $item_type){



        //获取支付用户信息
        $user = User::findOne($user_id);
        $userLink = UserRelationshipLink::findOne(["user_id" => $user_id]);
        if(!$user || !$userLink){
            throw new \Exception("支付用户[ID:".($user ? $user->id : 0)."]不存在或关系链异常");
        }

        $query = User::find()->alias("u")
            ->leftJoin("{{%user_relationship_link}} url", "url.user_id=u.id");
        $query->andWhere([
            "AND",
            ["u.is_delete" => 0],
            ["IN", "u.role_type", ["store", "partner", "branch_office"]],
            ("url.`left` < '".$userLink->left."' AND url.`right` > '".$userLink->right."'")
        ])->select(["u.id", "u.total_income", "u.role_type", "u.nickname"])->orderBy("url.`left` DESC");

        $parentDatas = $query->asArray()->all();
        if(!$parentDatas){
            throw new \Exception("无法获取上级[ID:".$userLink->parent_id."]信息");
        }

        //对获取的所有上级进行处理
        $existData = $newParentDatas = [];
        $partner2 = null;
        foreach($parentDatas as $parentData){
            if(count($existData) >= 3) break;
            if($parentData['role_type'] == "partner" && isset($existData['partner'])){
                $partner2 = $parentData['id'];
                continue;
            }
            if(!isset($existData[$parentData['role_type']])){
                $existData[$parentData['role_type']] = $parentData['id'];
                $newParentDatas[] = $parentData;
            }
        }

        //生成相关规则键
        $parentDatas = [];
        $newParentDatas = array_reverse($newParentDatas);
        while(!empty($newParentDatas)){
            $relKeys = [];
            foreach($newParentDatas as $newParentData){
                $relKeys[] = $newParentData['role_type'] . "#all";
            }
            $parentData = array_shift($newParentDatas);
            $parentData['rel_keys'] = $relKeys;
            $parentDatas[] = $parentData;
        }

        $getChainRuleData = function(ActiveQuery $query, $item_id){
            //商品独立设置规则
            $newQuery = clone $query;
            $newQuery->andWhere([
                "AND",
                ['cr.apply_all_item' => 0],
                ['cr.item_id' => $item_id]
            ]);
            $ruleData = $newQuery->one();

            //无独立规则，使用全局规则
            if(!$ruleData){
                $newQuery = clone $query;
                $newQuery->andWhere(['cr.apply_all_item' => 1]);
                $ruleData = $newQuery->one();
            }

            return $ruleData;
        };

        $this->commandOut(json_encode($parentDatas));
        $currentLevel = count($parentDatas);
        foreach($parentDatas as $key => $parentData){

            $query = CommissionRuleChain::find()->alias("crc");
            $query->leftJoin("{{%plugin_commission_rules}} cr", "cr.id=crc.rule_id");
            $query->andWhere([
                "AND",
                ["cr.is_delete"  => 0],
                ['cr.item_type'  => $item_type],
                ['crc.role_type' => $parentData['role_type']],
                ['crc.level'     => $currentLevel]
            ]);
            $query->orderBy("crc.level DESC");
            $query->select(["cr.commission_type", "crc.level", "crc.commisson_value"]);
            $query->asArray();

            //查找规则
            $relKeys = array_reverse($parentData['rel_keys']);
            $ruleData = null;
            foreach($relKeys as $relKey){
                $newQuery = clone $query;
                $newQuery->andWhere("crc.unique_key LIKE '%{$relKey}'" );
                $ruleData = $getChainRuleData($newQuery, $item_id);

                $this->commandOut("current LEVEL:" . $currentLevel);
                $this->commandOut($newQuery->createCommand()->getRawSql());
                $this->commandOut(json_encode($ruleData));
                if($ruleData) break;
            }

            $parentDatas[$key]['rule_data'] = $ruleData ? $ruleData : null;

            $currentLevel--;
        }

        return $parentDatas;
    }

    /**
     * 商品订单，新增分佣记录
     * @return boolean
     */
    private function goodsOrderNew(){

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
        $query->select(["od.id as order_detail_id", "od.is_refund", "od.refund_status", "od.created_at", "od.updated_at", "o.mall_id", "o.user_id", "od.order_id", "od.goods_id", "od.total_original_price", "od.total_price", "g.profit_price", "gw.name"]);
        $orderDetailData = $query->asArray()->one();
        if(!$orderDetailData){
            return false;
        }


        try {

            if($orderDetailData['is_refund'] && $orderDetailData['refund_status'] == 20){
                throw new \Exception("订单商品[ID:".$orderDetailData['order_detail_id']."]已退款");
            }

            $parentDatas = $this->getCommissionParentRuleDatas($orderDetailData['user_id'], $orderDetailData['goods_id'], 'goods');

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
                            $this->commandOut("生成分佣记录 [ID:".$priceLog->id."]");

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
                            $this->commandOut($e->getMessage());
                        }

                    }
                }

            }
        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
        }

        //更新为已处理
        OrderDetail::updateAll(["commission_status" => 1], ["id" => $orderDetailData['order_detail_id']]);
    
        return true;
    }

    /**
     * 商品订单状态改变，更新分佣记录
     * @return boolean
     */
    private function goodsStatusChanged(){
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
        $this->goodsStatusSuccess($newQuery);

        //商品订单退款、取消，分佣扣除
        $newQuery = clone $query;
        $this->goodsStatusCancel($newQuery);
    }

    //商品订单已确认收货，分佣到账
    private function goodsStatusSuccess(ActiveQuery $query){
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

                    $this->commandOut("分佣记录 [ID:".$priceLog['id']."] 已完成");
                }catch (\Exception $e){
                    $trans->rollBack();
                    $this->commandOut($e->getMessage());
                }
            }

        }
    }

    //商品订单退款、取消，分佣扣除
    private function goodsStatusCancel(ActiveQuery $query){
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

                    $this->commandOut("分佣记录 [ID:".$priceLog['id']."] 已取消");
                }catch (\Exception $e){
                    $trans->rollBack();
                    $this->commandOut($e->getMessage());
                }
            }
        }
    }
}