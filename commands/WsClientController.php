<?php
namespace app\commands;


class WsClientController extends BaseCommandController {

    public function actionListen(){
        \Swoole\Coroutine\run(function () {
            $cli = new \Swoole\Coroutine\Http\Client('81.71.7.222', 9515);
            $data = "PAID:" . json_encode(["text" => "11111", "url" => "https://img-qn.51miz.com/preview/sound/00/27/20/51miz-S272046-BC428C3F.mp3"]);
            $cli->get('/?action=MchPaidNotify&notify_mobile=13422078495&notify_data=' . $data);
            $cli->close();
        });
    }


}

