<?php
/**
 * WorkmanWebSocket 服务相关
 */
 
namespace app\console\controllers\server;

use app\console\controllers\WorkermanBaseController;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\AsyncTcpConnection;

 
class WorkermanDispatchController extends WorkermanBaseController{
   
    public static $db;
    public $heart_beat_time = 55;//心跳检测时间
    
    // 这里不需要设置，会读取配置文件中的配置
    public $config = array();
    private $ip = '';
    private $port = '';
    public function initWorker(){
        $ip = isset($this->config['ip']) ? $this->config['ip'] : $this->ip;
        $port = isset($this->config['port']) ? $this->config['port'] : $this->port;
        $worker = new Worker("text://{$ip}:{$port}");
        
        $worker->count = 4;
        $worker->name = 'DspWorker';
        $worker->reusePort = true;
        $worker->onWorkerStart =[$this,'onWorkerStart'];
        $worker->onConnect = [$this,'onConnect']; 
        $worker->onMessage = [$this,'onMessage']; 
        $worker->onClose = [$this,'onClose'];
    }

    public function onWorkerStart($worker){
        Timer::add(1,array($this,'heartBeat'),array($worker));
    }

    //有客户端连接
    public function onConnect($connection) {
        // echo "New connection\n";
    }

    //客户端发来消息
    public function onMessage($connection, $data){
        $connection->lastMessageTime = time();
        $this->async_task($connection,$data);
        $connection->send(json_encode($this->success(1,'异步任务添加成功')));
        $connection->close();
    }
    
    //客户端关闭
    public function onClose($connection) {
        $connection->close();
        // echo "Connection closed\n";
    }

    //心跳检测踢除非活动连接 
    public function heartBeat($worker){
        $time_now = time();
        foreach($worker->connections as $connection) {
            if (empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
                continue;
            }
            if ($time_now - $connection->lastMessageTime > $this->heart_beat_time) {
                $connection->close();
            }
        }
    }
    
    /**
     * 异步任务处理
     * @Author bing
     * @DateTime 2020-09-28 12:31:54
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $connection
     * @param [type] $task_data
     * @return void
     */
    public function async_task($connection,$task_data){
        $worker_id = $connection->worker->id;
        $task_connection = new AsyncTcpConnection('text://'.SERVICE_IP_ADDR.':9517');
        $task_connection->send($task_data);
        echo 'Worker('.$worker_id.') dispatched an asynchronous task!',PHP_EOL;
        $task_connection->onMessage = function($task_connection, $task_result)use($connection){
            // 获得结果关闭异步连接
            echo 'Worker(',$connection->worker->id,')finished,Results of task execution:',$task_result,PHP_EOL;
            $task_connection->close();
            $connection->send($task_result);
        };
        // 执行异步连接
        $task_connection->connect();
    }

}