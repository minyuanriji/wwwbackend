<?php
namespace app\commands;


class HotelSearchController extends BaseCommandController {

    public function actionFilterTask(){
        \Swoole\Coroutine\run(function(){
            echo 'here';
        });
    }
}