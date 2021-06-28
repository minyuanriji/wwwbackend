<?php
namespace app\commands;




class HotelTaskController extends BaseCommandController {

    public $tasks = [
        ["num" => 1,  "name" => "Query",  "workerIds" => []],
        ["num" => 50, "name" => "Search", "workerIds" => []]
    ];

    public function start(){
        $num = 0;
        foreach($this->tasks as $task){
            $num += $task['num'];
        }
        $pool = new \Swoole\Process\Pool($num);
        $pool->set(['enable_coroutine' => true]);
        $pool->on('WorkerStart', function (\Swoole\Process\Pool $pool, $workerId){
            echo("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
            foreach($this->tasks as $key => $task){
                if(count($task['workerIds']) < $task['num']){
                    $this->tasks[$key]['workerIds'][$workerId] = 1;
                    break;
                }
            }
            foreach($this->tasks as $task){
                $workerIds = array_keys($task['workerIds']);
                if(in_array($workerId, $workerIds)){
                    while(true){
                        echo $task['name'] . "\n";
                        sleep(1);
                    }
                }
            }
        });
        $pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
            echo("[Worker #{$workerId}] WorkerStop\n");
            foreach($this->tasks as $key => $task){
                $workerIds = array_keys($task['workerIds']);
                if(in_array($workerId, $workerIds)){
                    "Remove From " . $task['name'] . "\n";
                    unset($this->tasks[$key]['workerIds'][$workerId]);
                    break;
                }
            }
        });
        $pool->start();
    }

}