<?php
namespace app\commands;


class WsClientController extends BaseCommandController {

    public function actionListen(){
        \Swoole\Coroutine\run(function () {
            $cli = new \Swoole\Coroutine\Http\Client('81.71.7.222', 9515);
            $data = "PAID:" . json_encode(["text" => "11111", "url" => "https://img-qn.51miz.com/preview/sound/00/27/20/51miz-S272046-BC428C3F.mp3"]);
            $cli->get('/?MCH_PAID_NOTIFY_MOBILE=13422078495&MCH_PAID_NOTIFY_DATA=' . $data);
            $cli->close();
        });
    }

}
