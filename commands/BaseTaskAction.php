<?php

namespace app\commands;

use yii\base\Action;

abstract class BaseTaskAction extends Action{

    protected $sleepTime = 1;

    /**
     * 设置活跃度
     * @param $val
     */
    public function activeTime(){
        $this->sleepTime = max(1, $this->sleepTime - 10);
    }

    /**
     * 设置活跃度
     * @param $val
     */
    public function negativeTime(){
        $this->sleepTime = min(20, $this->sleepTime + 1);
    }

    /**
     * 运行代码
     */
    public function run() {
        $this->beforeWhile();
        while (true) {
            sleep($this->sleepTime);
            try {
                $this->whileRun();
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }

    /**
     * 执行前的一些业务处理
     * @return void
     */
    public function beforeWhile(){}

    /**
     * 运行业务逻辑
     * @return void
     */
    abstract public function whileRun();
}