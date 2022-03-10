<?php

namespace app\commands;

class CronController extends BaseCommandController{

    public static $handlers = [
        ShoppingVoucherSendTaskController::class
    ];

    public function actionStart(){
        if(!empty($tasks)){
            $pm = new \Swoole\Process\ProcessManager();
            foreach(static::$handlers as $className){
                $controller = new $className();
                foreach($controller->actions() as $actionID => $value){
                    $taskName = $className . "::" . $actionID;
                    $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($taskName, $actionID, $controller){
                        try {
                            require_once(__DIR__ . '/../vendor/autoload.php');
                            require_once __DIR__ . '/../config/const.php';
                            new \app\core\ConsoleApplication();
                            $this->commandOut("启动任务{$taskName}");
                            $controller->runAction($actionID);
                        }catch (\Exception $e){
                            $this->commandOut("任务{$taskName} message:" . $e->getMessage() . " file:" . $e->getFile() . " line:" . $e->getLine());
                        }
                    });
                }
            }
            $pm->start();
        }
    }

}