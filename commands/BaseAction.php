<?php

namespace app\commands;

use yii\base\Action;

class BaseAction extends Action{

    protected $sleepTime = 1;

    /**
     * 设置活跃度
     * @param $val
     */
    protected function activeTime(){
        $this->sleepTime = max(1, $this->sleepTime - 10);
    }

    /**
     * 设置活跃度
     * @param $val
     */
    protected function negativeTime(){
        $this->sleepTime = min(20, $this->sleepTime + 1);
    }
}