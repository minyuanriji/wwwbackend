<?php

namespace app\commands\commission_action;

use app\models\Store;
use app\models\User;
use app\plugins\commission\models\CommissionSmartshopPriceLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

class SmartShopOrderAction extends Action{

    public function run(){
        while (true){
            if(!$this->doNew()){
                $this->update();
            }
        }
    }

    /**
     * 新增分佣记录
     * @return boolean
     */
    private function doNew(){
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
        $query->select(["o.id", "o.from_table_name", "o.from_table_record_id", "o.bsh_mch_id", "o.ss_mch_id", "o.ss_store_id",
            "o.split_amount", "s.name as store_name", "m.transfer_rate", "m.integral_fee_rate", "m.user_id", "p.id as parent_id"]);
        $orders = $query->asArray()->orderBy("o.updated_at ASC")->limit(1)->all();
        if(!$orders) return false;

        print_r($orders);
        exit;
    }

    private function update(){

    }
}