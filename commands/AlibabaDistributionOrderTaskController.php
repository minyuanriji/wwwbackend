<?php

namespace app\commands;

class AlibabaDistributionOrderTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'order-paid' => 'app\commands\alibaba_distribution_order_task\OrderPaidAction'
        ];
    }

}