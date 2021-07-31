<?php
namespace app\commands;


use yii\console\Controller;

class BaseCommandController extends Controller{

    public function sleep($second){
        sleep(1);
    }

    public function mutiKill(){

    }

    public function commandOut($message){
        echo $message . "\n";
    }
}