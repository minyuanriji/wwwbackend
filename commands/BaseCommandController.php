<?php
namespace app\commands;


use yii\console\Controller;

class BaseCommandController extends Controller{

    protected function sleep($second){

    }

    protected function mutiKill(){

    }

    protected function commandOut($message){
        echo $message . "\n";
    }
}