<?php
namespace app\commands;

use app\models\Mall;

class HotelImportController extends BaseCommandController{

    /**
     * 酒店数据同步
     */
    public function actionStart(){
        $this->mutiKill(); //只能只有一个维护服务

        $demoData = [
            "page_num" => 5000,
            "page_size" => 5,
            "plateform_class" => "app\\plugins\\hotel\\libs\\bestwehotel\\PlateForm"
        ];

        $tasks = [];
        for($i=1; $i <= $demoData['page_num']; $i++){
            $tasks[] = [
                'page' => $i,
                'size' => $demoData['page_size'],
                'plateform_class' => $demoData['plateform_class']
            ];
        }

        $pm = new \Swoole\Process\ProcessManager();
        for($i=0; $i <= 50; $i++){
            $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($tasks){
                $this->commandOut("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid());
                if(!defined("Yii")){
                    require_once(__DIR__ . '/../vendor/autoload.php');
                    require_once __DIR__ . '/../config/const.php';
                    new \app\core\ConsoleApplication();
                }
                \Yii::$app->mall = Mall::findOne(5);
                while(true){
                    if(!empty($tasks)){
                        $task = array_shift($tasks);
                        $this->commandOut("task:" . $task['page']);
                    }
                }
            });
        }
        $pm->start();
    }
}