<?php

namespace app\commands;

class SmartShopTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'new-split-order'     => 'app\commands\smart_shop_task\NewSplitOrderAction',
            'process-split-order' => 'app\commands\smart_shop_task\ProcessSplitOrderAction',
            'finish-split-order'  => 'app\commands\smart_shop_task\FinishSplitOrderAction',
        ];
    }

}