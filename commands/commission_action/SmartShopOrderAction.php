<?php

namespace app\commands\commission_action;

use app\forms\common\UserIncomeModifyForm;
use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionRules;
use app\plugins\commission\models\CommissionSmartshopPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

/**
 * @deprecated
 */
class SmartShopOrderAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " SmartShopOrderAction start");
        $sleep = 1;
        while (true){
            try {
                $query = Order::find()->alias("o")
                    ->innerJoin(["m" => Mch::tableName()], "m.id=o.bsh_mch_id")
                    ->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id")
                    ->innerJoin(["u" => User::tableName()], "u.id=m.user_id")
                    ->innerJoin(["p" => User::tableName()], "p.id=u.parent_id");
                $query->andWhere([
                    "AND",
                    ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
                    ["m.is_delete" => 0],
                    ["o.is_delete" => 0],
                    ["o.status" => Order::STATUS_FINISHED],
                    [">", "o.split_amount", 0],
                    ["o.commission_status" => 0]
                ]);
                $query->select(["o.id", "o.mall_id", "o.from_table_name", "o.from_table_record_id", "o.bsh_mch_id", "s.id as bsh_store_id", "o.ss_mch_id", "o.ss_store_id",
                    "o.split_amount", "o.wx_got_amount", "o.ali_got_amount", "s.name as store_name", "m.transfer_rate", "m.integral_fee_rate", "m.user_id as mch_user_id", "p.id as parent_id"]);
                $orders = $query->asArray()->orderBy("o.updated_at ASC")->limit(1)->all();
                if($orders){
                    $sleep = max(1, --$sleep);
                    foreach($orders as $order){
                        $this->processNew($order);
                    }
                }else{
                    $sleep = min(30, ++$sleep);
                }
            }catch (\Exception $e){
                $this->controller->commandOut("SmartShopOrderAction::run>>" . $e->getMessage());
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
            $parent = User::findOne($order['parent_id']);
            if (!$parent || $parent->is_delete){
                throw new \Exception("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>推荐人[ID:".$order['parent_id']."]不存在");
            }
            if ($parent->role_type == 'user'){
                throw new \Exception("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>普通用户不分佣");
            }

            //获取当前店铺分佣规则
            $query = CommissionRules::find()->alias("cr")
                ->innerJoin("{{%plugin_commission_rule_chain}} crc", "cr.id=crc.rule_id");
            $newQuery = clone $query;
            $query->andWhere([
                "AND",
                ["cr.item_type"  => 'store'],
                ["cr.item_id"    => $order['bsh_store_id']],
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
                    throw new \Exception("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>没有分佣规则");
                }
            }

            //新公式
            $commissionRule['role_type']       = $parent->role_type;
            $commissionRule['ver']             = "2021/10/25";
            $commissionRule['commisson_value'] = min(0.02, (float)($commissionRule['commisson_value']/100));
            $commissionRule['profit_price']    = max(0, $order['split_amount'] - ($order['wx_got_amount'] + $order['ali_got_amount']));
            $price = $commissionRule['commisson_value'] * $commissionRule['profit_price'];

            if($price <= 0){
                throw new \Exception("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>分佣金额小于0");
            }

            $priceLog = CommissionSmartshopPriceLog::findOne([
                "user_id"  => $order['parent_id'],
                "order_id" => $order['id']
            ]);

            //生成分佣记录
            !$priceLog && $this->newCommissionPriceLog($order, $price, $commissionRule);

            Order::updateAll([
                "updated_at"        => time(),
                "commission_status" => 1
            ], ["id" => $order['id']]);

            $t->commit();

        }catch (\Exception $e){
            $t->rollBack();
            Order::updateAll([
                "updated_at"        => time(),
                "commission_status" => 1,
                "error_text"        => json_encode([
                    "msg"  => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ])
            ], ["id" => $order['id']]);
            $this->controller->commandOut("SmartShopOrderAction::processNew>>" . $e->getMessage());
        }

        $this->controller->commandOut("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>直推分佣记录处理完毕");
    }

    /**
     * 新增分佣收益
     * @param $order
     * @param $price
     * @param $commissionRule
     * @throws \Exception
     */
    private function newCommissionPriceLog($order, $price, $commissionRule){
        try {
            $priceLog = new CommissionSmartshopPriceLog([
                "mall_id"           => $order['mall_id'],
                "user_id"           => $order['parent_id'],
                "order_id"          => $order['id'],
                "price"             => round($price, 5),
                "status"            => 1,
                "created_at"        => time(),
                "updated_at"        => time(),
                "rule_data_json"    => json_encode($commissionRule)
            ]);
            if(!$priceLog->save()){
                throw new \Exception("智慧门店小程序订单推荐分佣>>订单[ID:".$order['id']."]>>".json_encode($priceLog->getErrors()));
            }

            $user = User::findOne($order['parent_id']);

            $incomeForm = new UserIncomeModifyForm([
                "type"        => 1,
                "price"       => $priceLog->price,
                "flag"        => 1,
                "source_id"   => $order['id'],
                "source_type" => "smart_shop_order",
                "desc"        => "来自智慧经营门店订单的分佣记录"
            ]);
            $incomeForm->modify($user, false);

        }catch (\Exception $e){
            throw $e;
        }
    }
}