<?php

namespace app\commands;

class AlibabaDistributionGoodsTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'do-warn' => 'app\commands\alibaba_distribution_goods_task\DoWarnAction'
        ];
    }

}