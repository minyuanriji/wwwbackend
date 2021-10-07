<?php

namespace app\commands\shopping_voucher_send_task;

use yii\base\Action;

class AddcreditOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " AddcreditOrderSendAction start");
        while (true){
            try {
                if(!$this->newAction()){
                    $this->sendAction();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){

    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){

    }

}