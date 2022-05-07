<?php

namespace app\commands;

class SmartShopTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'auto-unfreeze'       => 'app\commands\smart_shop_task\AutoUnfreezeAction',
            'user-mobile-bind'    => 'app\commands\smart_shop_task\UserMobileBindAction',
            'new-split-order'     => 'app\commands\smart_shop_task\NewSplitOrderAction',
            'process-split-order' => 'app\commands\smart_shop_task\ProcessSplitOrderAction',
            'kpi-cyorder-new'     => 'app\commands\smart_shop_task\KpiCyorderNewAction',
            'kpi-czorder-new'     => 'app\commands\smart_shop_task\KpiCzorderNewAction'
        ];
    }

}