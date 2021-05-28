<?php
namespace app\commands;


class WsServerController extends BaseCommandController {

    public function actionListen(){

        global $clientListCacheKeyPrefix, $ws;

        $clientListCacheKeyPrefix = "WEBSOCKET_CLIENT_LIST:";

        //创建WebSocket Server对象，监听0.0.0.0:9515端口
        $ws = new \Swoole\WebSocket\Server('0.0.0.0', 9515);

        //监听WebSocket连接打开事件
        $ws->on('Open', function ($ws, $request) {
            $ws->push($request->fd, "hello, welcome\n");
        });

        //监听WebSocket消息事件
        $ws->on('Message', function ($ws, $frame) {
            echo "client-".$frame->fd." message:\n";
            global $clientListCacheKeyPrefix;
            $data = (array)@json_decode($frame->data, true);
            if(!empty($data['action'])){
                if($data['action'] == "RelMobile" && !empty($data['content'])){
                    $cache = \Yii::$app->getCache();
                    $mobile = $data['content'];
                    $cacheKey = $clientListCacheKeyPrefix . $mobile;
                    $cache->set($cacheKey, $frame->fd);
                    $ws->push($frame->fd, "cache: " . json_encode($cache->get($cacheKey)));
                }
            }
        });

        //监听WebSocket连接关闭事件
        $ws->on('Close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            global $ws, $clientListCacheKeyPrefix;//调用外部的server

            //通知商户付款成功了
            if(!empty($request->get['MCH_PAID_NOTIFY_MOBILE'])){
                $text = !empty($request->get['MCH_PAID_NOTIFY_DATA']) ? $request->get['MCH_PAID_NOTIFY_DATA'] : "";
                $cacheKey = $clientListCacheKeyPrefix . $request->get['MCH_PAID_NOTIFY_MOBILE'];
                $cache = \Yii::$app->getCache();
                $fd = $cache->get($cacheKey);
                if(!empty($fd) && $ws->isEstablished($fd)){
                    $ws->push($fd, $text);
                }
            }

            /*if(!empty($request['get']) && !empty($request['get']['MCH_PAID_NOTIFY_MOBILE'])){
                echo $request['get']['MCH_PAID_NOTIFY_MOBILE'] . "\n";
            }*/
            /*foreach ($ws->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($ws->isEstablished($fd)) {
                    $ws->push($fd, $request->get['message']);
                }
            }*/
        });

        $ws->start();
    }

}
