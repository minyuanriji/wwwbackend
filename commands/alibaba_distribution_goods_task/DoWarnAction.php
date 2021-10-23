<?php

namespace app\commands\alibaba_distribution_goods_task;

use yii\base\Action;


class DoWarnAction extends Action{

    /**
     * 阿里巴巴分销订单付款处理任务
     */
    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " SyncAction start");
        while(true){

        }
    }
}