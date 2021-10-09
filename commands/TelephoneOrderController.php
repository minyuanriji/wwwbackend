<?php

namespace app\commands;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\User;
use app\plugins\addcredit\forms\api\order\PhoneOrderRefundForm;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\Code;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\PlateForm as kcb_PlateForm;

class TelephoneOrderController extends BaseCommandController{

    public function actions(){
        return [
            //设置待处理任务
            "set" => "app\\commands\\telephone_order_task\\SetProcessingAction",
            //处理任务
            "do" => "app\\commands\\telephone_order_task\\DoProcessingAction",
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

    public function actionMaintantJob(){
        $this->actionStart();
    }

}
