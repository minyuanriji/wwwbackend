<?php

namespace app\commands;

class PerformDistributiontTaskController extends SwooleProcessController {

    public function actions(){
        return [
            'ao' => 'app\commands\perform_distributiont_task\AwardOrderAction',
        ];
    }

}