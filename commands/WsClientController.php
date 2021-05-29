<?php
namespace app\commands;


use app\forms\common\WebSocketRequestForm;

class WsClientController extends BaseCommandController {

    const REQUEST_MESSAGE_CACHE_KEY = "WEBSOCKET_CLIENT_LIST:";

    public function actionListen(){
        while (true){
            $form = WebSocketRequestForm::one();
            if(!empty($form)){
                \Swoole\Coroutine\run(function () use($form){
                    $cli = new \Swoole\Coroutine\Http\Client('81.71.7.222', 9515);
                    //$data = "PAID:" . json_encode(["text" => "11111", "url" => "https://img-qn.51miz.com/preview/sound/00/27/20/51miz-S272046-BC428C3F.mp3"]);
                    //$cli->get('/?action=MchPaidNotify&notify_mobile=13422078495&notify_data=' . $data);
                    $data = $form->notify_data;
                    $cli->get('/?action='.$form->action.'&notify_mobile='.$form->notify_mobile.'&notify_data=' . $data);
                    $cli->close();
                    if($cli->body != "SUCCESS"){
                        if($form->fail_try > 3){
                            $this->commandOut("队列[ID:".$form->queue_tag."]失败超过3次，丢弃");
                        }else{
                            $this->commandOut("队列[ID:".$form->queue_tag."]失败重新加入队列");
                            $form->fail_try += 1;
                            WebSocketRequestForm::add($form);
                        }
                    }
                });
            }
            $this->sleep(1);
        }


    }


}

