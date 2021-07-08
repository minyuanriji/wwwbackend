<?php
namespace app\commands;


class EfpsTaskController extends BaseCommandController{

    public function actions(){
        return [
            "mch-cash-transfer-commit" => "app\\commands\\efps_task_action\\MchCashTransferCommitAction",
            "mch-cash-transfer-query"  => "app\\commands\\efps_task_action\\MchCashTransferQueryAction"
        ];
    }

    public function actionStart(){
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