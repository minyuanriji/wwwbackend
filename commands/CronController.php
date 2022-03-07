<?php

namespace app\commands;

class CronController extends BaseCommandController{

    public static function commands(){
        return [
            [

            ]
        ];
    }

    public function actionStart(){
        $controllers = static::controllers();
        $pm = new \Swoole\Process\ProcessManager();

    }

}