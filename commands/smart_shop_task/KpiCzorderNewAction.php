<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\SmartShopKPI;

class KpiCzorderNewAction extends BaseAction{

    public function run() {
        $shop = new SmartShop();
        while (true) {
            sleep($this->sleepTime);
            try {
                $shop->initSetting();

                $selects = ["o.id", "o.store_id", "m.id as merchant_id", "o.kpi_inviter_mobile", "u.mobile"];
                $rows = $shop->getCzorders($selects, [
                    "o.kpi_new_status=0 AND o.kpi_inviter_mobile is not null AND  o.kpi_inviter_mobile <> ''",
                    "o.state=2", //必须是已支付
                ], 5);
                if(!$rows){
                    $this->negativeTime();
                    continue;
                }

                $this->activeTime();

                //先把状态更新了
                $orderIds = [];
                foreach($rows as $row){
                    $orderIds[] = $row['id'];
                }
                $shop->batchSetCzorderKpiNewStatus($orderIds, 1);

                $kpi = new SmartShopKPI();
                foreach($rows as $row){
                    $kpi->newOrder("czorder", $row['store_id'], $row['merchant_id'], $row['id'], $row['mobile'], $row['kpi_inviter_mobile']);
                }

                $this->controller->commandOut("KPI用户新订单统计完成（czorder）");

            } catch (\Exception $e) {
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }
}