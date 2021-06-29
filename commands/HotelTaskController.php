<?php
namespace app\commands;



use yii\base\InvalidRouteException;

class HotelTaskController extends BaseCommandController {

    public $tasks = [
        ["num" => 1,  "name" => "Query", "action" => "query"],
        ["num" => 50, "name" => "Search", "action" => "search"]
    ];

    public function actions(){
        return [
            'query' => 'app\hotel_task_action\QueryAction'
        ];
    }

    public function actionStart(){
        $pm = new \Swoole\Process\ProcessManager();
        foreach($this->tasks as $task){
            for($i=0; $i < $task['num']; $i++){
                $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($task) {
                    try {
                        $this->runAction($task['action']);
                        $this->commandOut($task['name'] . " task start successfully,worker:{$workerId}");
                    }catch (InvalidRouteException $e){
                        $this->commandOut($e->getMessage());
                        while(true){}
                    }
                });
            }
        }
        $pm->start();
    }
}