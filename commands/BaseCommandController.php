<?php
namespace app\commands;


use yii\console\Controller;

class BaseCommandController extends Controller{

    public function sleep($second){
        sleep($second);
    }

    public function mutiKill(){

    }

    public function commandOut($message){
        echo "[".date("Y-m-d H:i:s", time())."]" . $message . "\n";
    }
}