<?php
namespace app\commands;



use app\component\lib\LockTools;
use yii\base\InvalidRouteException;

class HotelTaskController extends BaseCommandController {

    public $tasks = [
        ["num" => 1,  "name" => "Query", "action" => "query"],
        ["num" => 50, "name" => "Search", "action" => "search"]
    ];

    public function actions(){
        return [
            "query" => "app\\commands\\hotel_task_action\\QueryAction",
            "search" => "app\\commands\\hotel_task_action\\SearchAction"
        ];
    }

    public function actionStart(){
        $pm = new \Swoole\Process\ProcessManager();
        $lock = new LockTools();
        foreach($this->tasks as $task){
            for($i=0; $i < $task['num']; $i++){
                $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($task, $lock) {
                    $taskName = $task['name'];
                    $this->commandOut("[Worker #{$workerId}] WorkerStart, Task:{$taskName}, pid: " . posix_getpid());
                    if(!defined("Yii")){
                        require_once(__DIR__ . '/../vendor/autoload.php');
                        require_once __DIR__ . '/../config/const.php';
                        new \app\core\ConsoleApplication();
                    }
                    try {
                        $this->runAction($task['action'], [$lock]);
                    }catch (InvalidRouteException $e){
                        $this->commandOut("[Worker #{$workerId}] Task:{$taskName}, " . $e->getMessage());
                        while(true){}
                    }
                });
            }
        }
        $pm->start();
    }
}