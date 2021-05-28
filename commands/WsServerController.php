<?php
namespace app\commands;


class WsServerController extends BaseCommandController {

    const CLIENT_REL_MOBILE_CACHE_KEY_PREFIX = "WEBSOCKET_CLIENT_LIST:";

    public function actionListen(){

        global $ws;

        //创建WebSocket Server对象，监听0.0.0.0:9515端口
        $ws = new \Swoole\WebSocket\Server('0.0.0.0', 9515);

        //监听WebSocket连接打开事件
        $ws->on('Open', function ($ws, $request) {
            $this->commandOut("客户端：" . $request->fd . "连接成功");
        });

        //监听WebSocket消息事件
        $ws->on('Message', function ($ws, $frame) {

            if(!static::authMessageCheck($ws, $frame)){
                return;
            }

            $data = (array)@json_decode($frame->data, true);

            $action = !empty($data['action']) ? "messageAction" . $data['action'] : "invalidAction";
            if($this->hasMethod($action)){
                $this->$action([
                    "ws"    => $ws,
                    "frame" => $frame,
                    "data"  => $data
                ]);
            }
        });

        //监听WebSocket连接关闭事件
        $ws->on('Close', function ($ws, $fd) {
            $this->commandOut("客户端：{$fd}已断开");
        });

        $ws->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            global $ws;//调用外部的server

            $this->commandOut("收到请求");

            if(!static::authRequestCheck($request, $response)){
                return;
            }

            $action = !empty($request->get['action']) ? "requestAction" . $request->get['action'] : "invalidAction";

            if($this->hasMethod($action)){
                $this->$action([
                    "request"  => $request,
                    "ws"       => $ws,
                    "response" => $response
                ]);
            }
        });

        $ws->start();
    }

    /**
     * 通知商户付款成功了
     * @param $param
     */
    public function requestActionMchPaidNotify($param){

        $text = !empty($param['request']->get['notify_data']) ? $param['request']->get['notify_data'] : "";
        $cacheKey = self::CLIENT_REL_MOBILE_CACHE_KEY_PREFIX . $param['request']->get['notify_mobile'];
        $cache = \Yii::$app->getCache();
        $fd = $cache->get($cacheKey);

        if(empty($fd)){
            $this->commandOut("无法找到客户端");
        }elseif(!$param['ws']->isEstablished($fd)){
            $this->commandOut("客户端：" . $fd . "已断开");
        }else{
            $this->commandOut("通知商户已付款：" . $text);
            $param['ws']->push($fd, $text);
        }
    }

    /**
     * 客户端绑定手机号
     * @param $param
     */
    public function messageActionRelMobile($param){
        if(!empty($param['data']['content'])){
            $cache = \Yii::$app->getCache();
            $mobile = $param['data']['content'];
            $cacheKey = self::CLIENT_REL_MOBILE_CACHE_KEY_PREFIX . $mobile;
            $cache->set($cacheKey, $param['frame']->fd);
            $this->commandOut("客户端：".$param['frame']->fd."手机关联成功");
            $param['ws']->push($param['frame']->fd, "手机关联成功\n");
        }
    }

    /**
     * 消息权限控制
     * @param $data
     * @return boolean
     */
    public static function authMessageCheck($ws, $frame){
        return true;
    }

    /**
     * 请求权限控制
     * @param $request
     * @param $response
     * @return boolean
     */
    public static function authRequestCheck($request, $response){
        return true;
    }

    /**
     * 控制台输出
     * @param $message
     */
    public function commandOut($message){
        $message = "[" . date("Y-m-d H:i:s") . "]{$message}";
        parent::commandOut($message);
    }
}

