<?php

namespace app\console\controllers\server;

use app\console\controllers\WorkermanBaseController;
use app\models\Task;
use Exception;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Yii;

class WorkermanTaskController extends WorkermanBaseController{
    public static $db;
    // 这里不需要设置，会读取配置文件中的配置
    public $config = array();
    private $ip = '';
    private $port = '';
    public $redis = null;
    public $heart_beat_time = 55;
    public function initWorker(){
        // task worker，使用Text协议
        $ip = isset($this->config['ip']) ? $this->config['ip'] : $this->ip;
        $port = isset($this->config['port']) ? $this->config['port'] : $this->port;
        $worker = new Worker("text://{$ip}:{$port}");

        // task进程数可以根据需要多开一些
        $worker->count = 8;
        $worker->name = 'TaskWorker';
        //只有php7才支持task->reusePort，可以让每个task进程均衡的接收任务
        $worker->reusePort = true;

        $worker->onWorkerStart = [$this, 'onWorkerStart'];
        $worker->onConnect = [$this, 'onConnect'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onClose = [$this, 'onClose'];
    }

    public function onWorkerStart($worker){
        Timer::add(1, array($this, 'heartBeat'), array($worker));
    }

    //有客户端连接
    public function onConnect($connection){
        // echo "New connection\n";
    }

    //客户端关闭
    public function onClose($connection){
        $connection->close();
        // echo "Connection closed\n";
    }

    /**
     * 监听客户端消息
     * @Author bing
     * @DateTime 2020-09-28 15:30:56
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $connection
     * @param string $task
     * @return void
     */
    public function onMessage($connection, $task){
        $task = unserialize($task);
        $worker_id = $connection->worker->id;
        if (!empty($task['handler_class']) && !empty($task['method'])) {
            //自定义了处理类
            $response = $this->customizeHandle($task);
        } else {
            //根据task_name做处理
            switch ($task['task_name']) {
                case '任务名字':
                    // todo:

                    break;
                default:
                    $response = $this->error(0, 'Unkonwn task name is recived!');
            }
        }

        //任务补发机制
        if($response['code'] == 0){
           $task_info = array(
                'task_name' => $task['task_name'],
                'handler_class' => $task['handler_class'],
                'method' => $task['method'],
                'data' => serialize($task['data']),
                'error_msg' => $response['msg'],
                'status' => 0
           );
           $task_model = new Task();
           $res = $task_model->addFailedTask($task_info);
           if($res === false) Yii::error(var_export($task_model->getErrorSummary(true)).PHP_EOL.serialize($task_info));
        }else{
            echo  'worker(',$worker_id,')has been completed',' the task [',$task['task_name'],']',PHP_EOL;
        }

        // 发送结果
        $connection->send(json_encode($response));
        $connection->close(); //关闭连接
        Yii::getLogger()->flush(true);
    }

    /**
     * 用户自定义处理方案
     * @Author bing
     * @DateTime 2020-09-28 15:37:13
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $task
     * @return void
     */
    public function customizeHandle($task){
        //用户自定义类库处理
        try {
            $class = $task['handler_class'];
            $method = $task['method'];
            $obj = new $class();
            $obj->$method($task['data']);
            return $this->success();
        } catch (Exception $e) {
            Yii::error('task ['.$task['task_name'].'] handle failed,error message:'.$e->getMessage().$e->getFile().'('.$e->getLine().')');
            echo 'task ['.$task['task_name'].'] handle failed,error message:'.$e->getMessage().$e->getFile().'('.$e->getLine().')'.PHP_EOL;
            return $this->error(0, 'task ['.$task['task_name'].'] handle failed,error message:'.$e->getMessage(),$task['data']);
        }
    }


    /**
     *心跳检测踢除非活动连接
     * @Author bing
     * @DateTime 2020-09-28 15:24:11
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $worker
     * @return void
     */
    public function heartBeat($worker){
        $time_now = time();
        foreach ($worker->connections as $connection) {
            if (empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
                continue;
            }
            if ($time_now - $connection->lastMessageTime > $this->heart_beat_time) {
                $connection->close();
            }
        }
    }
}
