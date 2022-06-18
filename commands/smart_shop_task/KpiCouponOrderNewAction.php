<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseTaskAction;
use app\models\Mall;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\SmartShopKPI;

class KpiCouponOrderNewAction  extends BaseTaskAction {

    private $shop;

    public function beforeWhile(){
        $this->shop = new SmartShop();
    }

    public function whileRun() {
        $this->shop->initSetting();

        $selects = ["o.id", "o.store_id", "m.id AS merchant_id", "o.kpi_inviter_mobile", "o.mobile"];
        $rows = $this->shop->getCouponOrders($selects, [
            "o.kpi_new_status=0 AND o.kpi_inviter_mobile is not null AND o.kpi_inviter_mobile <> ''"
        ], 5);
        if(!$rows){
            $this->negativeTime();
            return;
        }

        $this->activeTime();

        \Yii::$app->setMall(Mall::findOne(MAIN_MALL_ID));

        //先把状态更新了
        $orderIds = [];
        foreach($rows as $row){
            $orderIds[] = $row['id'];
        }

        $this->shop->batchSetCouponOrderKpiNewStatus($orderIds, 1);
        $kpi = new SmartShopKPI();
        foreach($rows as $row){
            $res = $kpi->newOrder("store_usercoupons", $row['store_id'], $row['merchant_id'], $row['id'], $row['mobile'], $row['kpi_inviter_mobile']);
            if(!$res){
                $this->controller->commandOut($kpi->getError());
            }
        }

        $this->controller->commandOut("KPI用户新订单统计完成（store_usercoupons）");
    }

}