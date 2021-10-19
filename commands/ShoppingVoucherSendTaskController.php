<?php


namespace app\commands;

class ShoppingVoucherSendTaskController extends BaseCommandController {

    public function actions(){
        return [
            'mco' => 'app\commands\shopping_voucher_send_task\MchCheckoutOrderSendAction',
            'ho' => 'app\commands\shopping_voucher_send_task\HotelOrderSendAction',
            'hf' => 'app\commands\shopping_voucher_send_task\AddcreditOrderSendAction',
            'gf' => 'app\commands\shopping_voucher_send_task\GiftpacksOrderSendAction',
            'od' => 'app\commands\shopping_voucher_send_task\OrderDetailSendAction'
        ];
    }

    /**
     * 购物券发送任务
     */
    public function actionStart(){
        $pm = new \Swoole\Process\ProcessManager();
        foreach($this->actions() as $id => $class){
            $pm->add(function (\Swoole\Process\Pool $pool, int $workerId) use($id){
                if(!defined("Yii")){
                    require_once(__DIR__ . '/../vendor/autoload.php');
                    require_once __DIR__ . '/../config/const.php';
                    new \app\core\ConsoleApplication();
                }
                $this->commandOut("[Worker #{$workerId}] WorkerStart, Task:{$id}, pid: " . posix_getpid());
                $this->runAction($id);
            });
        }
        $pm->start();
    }

}