<?php
/**
 * WorkmanWebSocket 服务相关
 */
 
namespace app\console\controllers\server;

use app\component\lib\LockTools;
use app\console\controllers\WorkermanBaseController;
use app\models\Task;
use app\models\Integral;
use app\models\IntegralRecord;
use Exception;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Yii;
/**
 * WorkermanWebSocket
 */
 
class WorkermanTimerController extends WorkermanBaseController
{
   
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
        
        $worker->count = 1;
        $worker->name = 'TimerWorker';
        $worker->onWorkerStart =[$this,'onWorkerStart'];
        $worker->onConnect = [$this,'onConnect']; 
        $worker->onMessage = [$this,'onMessage']; 
        $worker->onClose = [$this,'onClose'];
    }

    public function onWorkerStart($worker){
        Timer::add(3,array($this,'sendIntegralTimer'),array($worker)); //发放积分定时任务
        Timer::add(3,array($this,'expireIntegralTimer'),array($worker)); //积分过期定时任务
        Timer::add(1,array($this,'taskRetry'),array($worker));//任务重试定时任务
    }

    //有客户端连接
    public function onConnect($connection) {
    }

    //客户端发来消息
    public function onMessage($connection, $data){
    }
    
    //客户端关闭
    public function onClose($connection) {
        $connection->close();
    }

     /**
     * 执行发放积分计划
     * @Author bing
     * @DateTime 2020-10-07 18:36:45
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function sendIntegralTimer($worker){
	file_put_contents(__DIR__ . "/DEBUG", 1);
        //获取一把锁
        $lock_tools = new LockTools();
        $lock_name = 'lock:sendIntegralTimer';
        //  $i = 0;
        // \Yii::$app->redis -> set('asda',$i++);
        if($lock_tools->lock($lock_name)){
            try{
                $res = Integral::sendIntegral();
                if($res === false) throw new Exception(Integral::getError());
                $lock_tools->unlock($lock_name);
            }catch(Exception $e){
                Yii::error('定时任务：sendIntegral,执行失败,错误信息：'.$e->getMessage());
                echo '定时任务：sendIntegral,执行失败,错误信息：'.$e->getMessage().PHP_EOL;
                $lock_tools->unlock($lock_name);
                Yii::getLogger()->flush(true);
            }
        }
    }
    
    /**
     * 积分过期处理
     * @Author bing
     * @DateTime 2020-10-08 09:14:46
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $worker
     * @return void
     */
    public function expireIntegralTimer($worker){
        //获取一把锁
        $lock_tools = new LockTools();
        $lock_name = 'lock:expireIntegralTimer';
        if($lock_tools->lock($lock_name)){
            try{
                $res = IntegralRecord::expireIntegralHandle();
                if($res === false) throw new Exception(Integral::getError());
                $lock_tools->unlock($lock_name);
            }catch(Exception $e){
                Yii::error('定时任务：sendIntegral,执行失败,错误信息：'.$e->getMessage());
                echo '定时任务：sendIntegral,执行失败,错误信息：'.$e->getMessage().PHP_EOL;
                $lock_tools->unlock($lock_name);
                Yii::getLogger()->flush(true);
            }
        }
    }

    /**
     * 异步任务失败补发
     * @Author bing
     * @DateTime 2020-10-10 10:02:53
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $worker
     * @return void
     */
    public function taskRetry($worker){
        //获取一把锁
        $lock_tools = new LockTools();
        $lock_name = 'lock:taskRetry';
        if($lock_tools->lock($lock_name)){
            try{
                $res = Task::reTryFaildTask();
                if($res === false) throw new Exception('异步任务补发失败');
                $lock_tools->unlock($lock_name);
            }catch(Exception $e){
                Yii::error('定时任务：taskRetry,执行失败,错误信息：'.$e->getMessage().PHP_EOL);
                echo '定时任务：taskRetry,执行失败,错误信息：'.$e->getMessage().PHP_EOL;
                $lock_tools->unlock($lock_name);
                Yii::getLogger()->flush(true);
            }
        }
    }
}
