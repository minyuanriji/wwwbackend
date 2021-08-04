<?php

namespace app\commands;


class MchTaskController extends BaseCommandController{

    public function actions(){
        return [
            "order-price-log" => "app\\commands\\mch_task_action\\OrderPriceLogAction"
        ];
    }

    public function actionStart(){
        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 商户任务守候程序启动...完成\n";

        $pm = new \Swoole\Process\ProcessManager();
        foreach($this->actions() as $id => $class){
            $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($id){
                if(!defined("Yii")){
                    require_once(__DIR__ . '/../vendor/autoload.php');
                    require_once __DIR__ . '/../config/const.php';
                    new \app\core\ConsoleApplication();
                }
                $this->commandOut("[Worker #{$workerId}] WorkerStart, Task:{$id}, pid: " . posix_getpid());
                $this->runAction($id);
            });
        }
        $pm->start();
    }

}