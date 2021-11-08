<?php

namespace app\commands;

class OilTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'confirm' => 'app\commands\oil_task\ConfirmAction'
        ];
    }

}