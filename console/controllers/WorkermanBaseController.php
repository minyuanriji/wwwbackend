<?php
namespace app\console\controllers;
use yii\console\Controller;
use Workerman\Worker;
use yii\helpers\Console;
use Yii;

class WorkermanBaseController extends Controller{
    public $send;
    public $daemon;
    public $gracefully;
    public function options($actionID){
        return ['send', 'daemon', 'gracefully'];
    }
    
    public function optionAliases(){
        return [
            's' => 'send',
            'd' => 'daemon',
            'g' => 'gracefully',
        ];
    }
    
    public function actionIndex(){
        if ('start' == $this->send) {
            try {
                $this->start($this->daemon);
            } catch (\Exception $e) {
                // $this->stderr($e->getMessage() . "\n", Console::FG_RED);
                $message = $e->getMessage();
                echo $message . PHP_EOL;
                Yii::error($message);
            }
        }else if ('stop' == $this->send) {
            $this->stop();
        }else if ('restart' == $this->send) {
            $this->restart();
        }else if ('reload' == $this->send) {
            $this->reload();
        }else if ('status' == $this->send) {
            $this->status();
        }else if ('connections' == $this->send) {
            $this->connections();
        }
    }
    
    public function start(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'start';
        if ($this->daemon) {
            $argv[2] = '-d';
        }
    
        // Run worker
        Worker::runAll();
    }
    
    public function restart(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'restart';
        if ($this->daemon) {
            $argv[2] = '-d';
        }
    
        if ($this->gracefully) {
            $argv[2] = '-g';
        }
    
        // Run worker
        Worker::runAll();
    }

    public function stop(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'stop';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }
        
        // Run worker
        Worker::runAll();
    }
    
    public function reload(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'reload';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }
        Worker::runAll();
    }
    
    public function status(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'status';
        if ($this->daemon) {
            $argv[2] = '-d';
        }
        Worker::runAll();
    }
    
    public function connections(){
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'connections';
        Worker::runAll();
    }
      
    public function success($code = 1,$msg='success',$data=[]){
        return compact('code','msg','data');
    }

    public function error($code,$msg = 'error',$data=[]){
        return compact('code','msg','data');
    }
}