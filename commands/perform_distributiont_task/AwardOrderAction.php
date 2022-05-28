<?php

namespace app\commands\perform_distributiont_task;

use app\commands\BaseTaskAction;
use app\models\IncomeLog;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\perform_distribution\models\AwardOrder;
use yii\db\ActiveQuery;

class AwardOrderAction extends BaseTaskAction {

    /**
     * 处理奖励订单
     * @return void
     */
    public function whileRun() {
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->innerJoin(["ao" => AwardOrder::tableName()], "ao.order_id=o.id");
        $query->andWhere([
            "AND",
            ["ao.status" => 0]
        ])->orderBy("ao.updated_at ASC")->asArray();
        $query->select(["ao.*"]);

        //商品订单已确认收货，奖励到账
        $newQuery = clone $query;
        $this->doStatusSuccess($newQuery);

        //商品订单退款、取消，取消奖励
        $newQuery = clone $query;
        $this->doStatusCancel($newQuery);
    }

    //商品订单已确认收货，分佣到账
    private function doStatusSuccess(ActiveQuery $query){
        $awardOrders = $query->andWhere([
            "AND",
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["IN", "o.status", [3, 6, 7, 8]],
            "(od.is_refund='0' OR (od.is_refund='1' AND od.refund_status='21'))"
        ])->limit(10)->all();
        if(!$awardOrders){
            $this->negativeTime();
            return;
        }

        $this->activeTime();

        //先更新时间
        $awardOrderIds = [];
        foreach($awardOrders as $awardOrder){
            $awardOrderIds[] = $awardOrder['id'];
        }
        AwardOrder::updateAll(["updated_at" => time()], "id IN(".implode(",", $awardOrderIds).")");

        //奖励到账
        foreach($awardOrders as $awardOrder){
            $t = \Yii::$app->db->beginTransaction();
            try {

                //更新待结算奖励订单为为已结算
                AwardOrder::updateAll([
                    "status" => AwardOrder::STATUS_SUCCESS
                ], ["id" => $awardOrder['id']]);

                //设置待结算记录的为已结算
                $incomeLog = IncomeLog::findOne([
                    "source_id"   => $awardOrder['id'],
                    "source_type" => "perform_distribution_award_order",
                    "flag"        => 0
                ]);
                if($incomeLog){
                    $incomeLog->flag       = 1;
                    $incomeLog->updated_at = time();
                    if(!$incomeLog->save()){
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    //更新用户收益信息
                    User::updateAllCounters([
                        "income"        => $incomeLog->income,
                        "income_frozen" => -1 * abs($incomeLog->income)
                    ], ["id" => $incomeLog->user_id]);
                }

                $this->controller->commandOut("业绩分配奖励待结算收益订单[ID:".$awardOrder['id']."]处理完成");

                $t->commit();
            }catch (\Exception $e){
                $t->rollBack();
                $this->controller->commandOut($e->getMessage());
            }
        }
    }

    //商品订单退款、取消，分佣扣除
    private function doStatusCancel(ActiveQuery $query){
        $awardOrders = $query->andWhere([
            "OR",
            ["o.is_delete" => 1],
            ["o.is_recycle" => 1],
            ["od.is_refund" => 1],
            ["IN", "od.refund_status", [20]]
        ])->limit(10)->all();
        if(!$awardOrders){
            $this->negativeTime();
            return;
        }

        $this->activeTime();

        //先更新时间
        $awardOrderIds = [];
        foreach($awardOrders as $awardOrder){
            $awardOrderIds[] = $awardOrder['id'];
        }
        AwardOrder::updateAll(["updated_at" => time()], "id IN(".implode(",", $awardOrderIds).")");

        //取消奖励
        foreach($awardOrders as $awardOrder){
            $t = \Yii::$app->db->beginTransaction();
            try {

                //更新待结算奖励订单为为无效
                AwardOrder::updateAll([
                    "status" => AwardOrder::STATUS_INVALID
                ], ["id" => $awardOrder['id']]);

                //设置待结算收益记录为无效
                $incomeLog = IncomeLog::findOne([
                    "source_id"   => $awardOrder['id'],
                    "source_type" => "perform_distribution_award_order",
                    "flag"        => 0
                ]);
                if($incomeLog){
                    $incomeLog->flag       = -1;
                    $incomeLog->updated_at = time();
                    if(!$incomeLog->save()){
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    //更新用户收益信息
                    User::updateAllCounters([
                        "total_income"  => -1 * abs($incomeLog->income),
                        "income_frozen" => -1 * abs($incomeLog->income)
                    ], ["id" => $incomeLog->user_id]);
                }

                $t->commit();

                $this->controller->commandOut("业绩分配奖励待结算收益订单[ID:".$awardOrder['id']."]取消");

            }catch (\Exception $e){
                $t->rollBack();
                $this->controller->commandOut($e->getMessage());
            }
        }

    }
}