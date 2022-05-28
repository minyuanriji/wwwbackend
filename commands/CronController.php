<?php

namespace app\commands;

use Yii;

class CronController extends BaseCommandController{

    public static $handlers = [
        "perform-distributiont-task" => PerformDistributiontTaskController::class, //业绩分配奖励
        "smart-shop-task" => SmartShopTaskController::class, //智慧门店
        "commission" => CommissionController::class, //分佣任务
        "shopping-voucher-send-task" => ShoppingVoucherSendTaskController::class, //红包（购物券）发放任务
        "score-send-task" => ScoreSendTaskController::class //积分发放任务
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