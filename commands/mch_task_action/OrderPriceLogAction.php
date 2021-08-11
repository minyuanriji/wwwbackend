<?php

namespace app\commands\mch_task_action;

use app\models\Order;
use app\models\OrderDetail;
use app\plugins\mch\models\MchPriceLog;

class OrderPriceLogAction extends BasePriceLogAction{


    /**
     * 获取可结算记录
     * @return MchPriceLog
     */
    public function getPriceLog(){
        $query = MchPriceLog::find()->alias("mpl")
                    ->innerJoin(["od" => OrderDetail::tableName()], "mpl.source_id=od.id")
                    ->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->andWhere([
            "AND",
            ["mpl.status" => "unconfirmed"],
            ["mpl.source_type" => "order_detail"],
            "od.is_refund=0 OR (od.is_refund=1 AND od.refund_status=21)",
            ["o.is_delete" => 0],
            ["o.is_recycle" => 0],
            ["IN", "o.status", [1, 2, 3, 6, 7, 8]],
            ["o.is_confirm" => 1]
        ]);

        $data = $query->select(["mpl.id"])->asArray()->orderBy("mpl.updated_at ASC")->one();

        if(!$data){
            return false;
        }

        return MchPriceLog::findOne($data['id']);
    }

    /**
     * 设置结算失败
     * @return boolean
     */
    protected function doCanceled(){
        $query = MchPriceLog::find()->alias("mpl")
            ->innerJoin(["od" => OrderDetail::tableName()], "mpl.source_id=od.id")
            ->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->where([
            "mpl.status" => "unconfirmed",
            "mpl.source_type" => "order_detail"
        ]);
        $query->andWhere([
            "OR",
            ["o.is_delete" => 1],
            ["o.is_recycle" => 1],
            ["IN", "o.status", [5]],
            "od.is_refund=1 AND (od.refund_status IN(11, 12, 20))"
        ]);

        $idArray = $query->select(["mpl.id"])->column();
        if($idArray){
            MchPriceLog::updateAll([
                "status" => "canceled",
                "updated_at" => time()
            ], ["IN", "id", $idArray]);
            return true;
        }
        return false;
    }
}