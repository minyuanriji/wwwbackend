<?php

namespace app\commands;

class TaobaoController extends SwooleProcessController{

    public function actions(){
        return [
            'check-goods' => 'app\commands\taobao\CheckGoodsAction'
        ];
    }

}