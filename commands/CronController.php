<?php

namespace app\commands;

use Yii;

class CronController extends BaseCommandController{

    public static $handlers = [
        "shopping-voucher-send-task" => ShoppingVoucherSendTaskController::class
    ];

    public function actionStart(){
        $pm = new \Swoole\Process\ProcessManager();
        $controllers = [];
        foreach(static::$handlers as $id => $className){
            $controllers[$className] = Yii::createObject($className, [$id, $this]);
            foreach($controllers[$className]->actions() as $actionID => $value){
                $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($className, $actionID, $controllers){
                    $taskName = $className . "::" . $actionID;
                    try {
                        if(!defined("Yii")){
                            require_once(__DIR__ . '/../vendor/autoload.php');
                            require_once __DIR__ . '/../config/const.php';
                            new \app\core\ConsoleApplication();
                        }
                        $this->commandOut("启动任务{$taskName}");
                        $controllers[$className]->runAction($actionID);
                    }catch (\Exception $e){
                        $this->commandOut("任务{$taskName} message:" . $e->getMessage() . " file:" . $e->getFile() . " line:" . $e->getLine());
                    }
                });
            }
        }
        $pm->start();
    }

}