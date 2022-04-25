<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\SmartShopKPI;

class KpiCyorderNewAction extends BaseAction{

    public function run() {
        $shop = new SmartShop();
        while (true) {
            sleep($this->sleepTime);
            try {
                $shop->initSetting();

                $selects = ["o.id", "o.kpi_inviter_mobile", "u.mobile"];
                $rows = $shop->getCyorders($selects, [
                    "o.kpi_new_status=0 AND o.kpi_inviter_mobile is not null AND  o.kpi_inviter_mobile <> ''",
                    "o.is_pay=1", //必须是已支付
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
                $shop->batchSetCyorderKpiNewStatus($orderIds, 1);

                $kpi = new SmartShopKPI();
                foreach($rows as $row){
                    $res = $kpi->newOrder("cyorder", $row['id'], $row['mobile'], $row['kpi_inviter_mobile']);
                    if(!$res){
                        $this->controller->commandOut($kpi->getError());
                    }
                }

                $this->controller->commandOut("KPI用户新订单统计完成（cyorder）");
            } catch (\Exception $e) {
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }
}