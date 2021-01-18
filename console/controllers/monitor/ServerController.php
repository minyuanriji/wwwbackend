<?php
namespace app\console\controllers\monitor;

use app\console\controllers\WorkermanBaseController;
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\TcpConnection;
use Yii;

class ServerController extends WorkermanBaseController{
    public static $db;
    public $heart_beat_time = 55;//心跳检测时间
    
    // 这里不需要设置，会读取配置文件中的配置
    public $config = [];
    private $ip = '';
    private $port = '';
    
    public function initWorker(){

        $ip = isset($this->config['ip']) ? $this->config['ip'] : $this->ip;
        $port = isset($this->config['port']) ? $this->config['port'] : $this->port;
        $worker = new Worker("websocket://{$ip}:{$port}");
        
        $worker->count = 1;
        $worker->name = 'monitorWorker';
        $worker->onWorkerStart =[$this,'onWorkerStart'];
        $worker->onConnect = [$this,'onConnect']; 
        $worker->onMessage = [$this,'onMessage']; 
        $worker->onClose = [$this,'onClose'];
        $worker->onWorkerStop = [$this,'onWorkerStop'];
    }

    public function onWorkerStart($worker){
        $worker->process_handle = popen('vmstat 2', 'r');
        if($worker->process_handle)
        {
            $process_connection = new TcpConnection($worker->process_handle);
            $process_connection->onMessage = function($process_connection, $rs)use($worker){
                if(!is_array($rs)){
                    $rs =  str_replace('procs -----------memory---------- ---swap-- -----io---- -system-- ------cpu-----','',trim($rs));
                    $rs = explode(' ',str_replace('r  b   swpd   free   buff  cache   si   so    bi    bo   in   cs us sy id wa st','',trim($rs)));
                    $item_values = array();
                    $item_keys = explode(' ','r b swpd free buff cache si so bi bo in cs us sy id wa st');
                    $num = 0;
                    foreach($rs as  $v){
                        if(trim($v) !== ''){
                            $item_values[$item_keys[$num]] = $v;
                            $num ++;
                        }   
                    }
                }
                $connections_count = count($worker->connections);
                echo $connections_count;
                foreach($worker->connections as $conn){
                    $time_now = time();
                    if($item_values){
                        $data = $this->Listen($item_values);
                        $data['time'] = date('H:i:s',$time_now);
                        $data['service'][0] = array(
                            'code'=>'wtimer',
                            'title'=> '定时任务服务',
                            'port' => '9515',
                            'status' => $this->getSelfServerStatus(9515)
                        );
                        $data['service'][1] = array(
                            'code'=>'wdsp',
                            'title'=> '任务分发服务',
                            'port' => '9516',
                            'status' => $this->getSelfServerStatus(9516)
                        );
                        $data['service'][2] = array(
                            'code'=>'wtask',
                            'title'=> '异步任务服务',
                            'port' => '9517',
                            'status' => $this->getSelfServerStatus(9517)
                        );
                        $data['service'][3] = array(
                            'code'=>'wlisten',
                            'title'=> '监控服务',
                            'port' => '9518',
                            'status' => $this->getSelfServerStatus(9518)
                        );
                        $data['info'] = array(
                            'connections_cout'=>$connections_count
                        );
                        $conn->send(json_encode($data));
                    }
                    
                }
            };
        }else{
            echo "vmstat 1 fail\n";
        }
    }

    //有客户端连接
    public function onConnect($connection) {
        echo "New connection\n";
    }

    public function onMessage($connection, $data){
    }
    
    public function onClose($connection) {
        $connection->close();
        echo "Connection closed\n";
    }
    
    public function onWorkerStop($worker) {
        @shell_exec('killall vmstat');
        @pclose($worker->process_handle);
    }
    
   

    public function Listen($item_values){
        if($item_values){
            //r表示表示运行队列，b表示进程阻塞数
            $procs = array(
                'r' => $item_values['r'],
                'b' => $item_values['b']
            );  
            //swapd虚拟内存使用，free闲置的物理内存，buff缓存，cache文件做缓冲
            $memory = [
                'swpd' => $item_values['swpd'],
                'free' => $item_values['free'],
                'buff' => $item_values['buff'],
                'cache' => $item_values['cache']
            ];  
            //si每秒从磁盘读入虚拟内存的大小 so每秒虚拟内存写入磁盘的大小
            $swap = [
                'si' => $item_values['si'],
                'so' => $item_values['so']
            ];
            //bi块设备每秒接收的块数量 bo块设备每秒发送的块数量
            $io = [
                'bi' => $item_values['bi'],
                'bo' => $item_values['bo']
            ];  
            //in每秒CPU的中断次数，包括时间中断 cs每秒上下文切换次数
            $system = [
                'in' => $item_values['in'],
                'cs' => $item_values['cs']
            ];  
            //us 用户CPU时间 sy 系统CPU时间 id  空闲CPU时间 wt 等待IO CPU时间。
            $cpu = [
                'us'=>$item_values['us'],
                'sy'=>$item_values['sy'],
                'id'=>$item_values['id'],
                'wa'=>$item_values['wa'],
                'st'=>$item_values['st']
            ];
            
            //查询自己定义服务的运行状态
            return compact('procs','memory','swap','io','system','cpu');
        }
       
    }

    public function getSelfServerStatus($port){
        $shell_command = "netstat -anp|grep LISTEN|grep 0.0.0.0:$port|wc -l";
        return shell_exec($shell_command);
    }
}