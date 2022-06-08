<?php

namespace app\commands;

class SmartShopTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'kpi-giftpack-order-new' => 'app\commands\smart_shop_task\KpiGiftpackOrderNewAction',
            'kpi-cyorder-new'        => 'app\commands\smart_shop_task\KpiCyorderNewAction',
            'kpi-czorder-new'        => 'app\commands\smart_shop_task\KpiCzorderNewAction',

            'user-mobile-bind'       => 'app\commands\smart_shop_task\UserMobileBindAction',
            'new-cyorder'            => 'app\commands\smart_shop_task\NewCyorderAction',
            'process-cyorder'        => 'app\commands\smart_shop_task\ProcessCyorderAction',

            //'new-split-order'      => 'app\commands\smart_shop_task\NewSplitOrderAction',
            'auto-unfreeze'          => 'app\commands\smart_shop_task\AutoUnfreezeAction',
            'process-split-order'    => 'app\commands\smart_shop_task\ProcessSplitOrderAction',

        ];
    }

}