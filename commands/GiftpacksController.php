<?php

namespace app\commands;


class GiftpacksController extends BaseCommandController{

    public function actions(){
        return [
            "group-cancel" => "app\\commands\\giftpacks_action\\GroupCancelAction"
        ];
    }

    public function actionStart(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 大礼包守候程序启动...完成\n";

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