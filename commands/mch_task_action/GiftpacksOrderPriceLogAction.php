<?php

namespace app\commands\mch_task_action;


use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\models\MchPriceLog;

class GiftpacksOrderPriceLogAction extends BasePriceLogAction {

    /**
     * 获取可结算记录
     * @return MchPriceLog
     */
    public function getPriceLog(){
        $query = MchPriceLog::find()->alias("mpl")
                    ->innerJoin(["goi" => GiftpacksOrderItem::tableName()], "goi.id=mpl.source_id")
                    ->innerJoin(["go" => GiftpacksOrder::tableName()], "go.id=goi.order_id");
        $query->where([
            "mpl.status"      => "unconfirmed",
            "mpl.source_type" => "giftpacks_order_item",
            "go.pay_status"   => "paid",
            "go.is_delete"    => 0
        ]);

        $data = $query->select(["mpl.id"])->asArray()->orderBy("mpl.updated_at ASC")->one();
        if(!$data){
            return false;
        }

        return MchPriceLog::findOne($data['id']);
    }
}