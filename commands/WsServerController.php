<?php
namespace app\commands;


class WsServerController extends BaseCommandController {

    const CLIENT_REL_MOBILE_CACHE_KEY_PREFIX = "WEBSOCKET_CLIENT_LIST:";
    const CLIENT_LIST_CACHE_KEY              = "WEBSOCKET_CLIENT_LIST";

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

			$this->commandOut($frame->data);
	
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
            $token = $this->getClientToken($fd);
            $this->cleanClient($token);
            $this->commandOut("客户端{$token}已断开");
        });

        $ws->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            global $ws;//调用外部的server

            $this->commandOut("收到请求");

            if(!static::authRequestCheck($request, $response)){
                return;
            }

            $action = !empty($request->post['action']) ? "requestAction" . $request->post['action'] : "invalidAction";

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
        $text = !empty($param['request']->post['notify_data']) ? $param['request']->post['notify_data'] : "";
        $token = $param['request']->post['notify_mobile'];
        $fd = $this->getClientId($token);
        $this->commandOut("客户端：" . $token);
        if(empty($fd) || !$param['ws']->isEstablished($fd)){
            $this->commandOut("客户端：" . $token. "已断开");
            $param['response']->end("ERROR");
        }else{
            $this->commandOut("客户端：" . $token. "付款成功");
            $param['ws']->push($fd, $text);
            $param['response']->end("SUCCESS");
        }
    }

    /**
     * 客户端绑定手机号
     * @param $param
     */
    public function messageActionRelMobile($param){
        if(!empty($param['data']['content'])){
            $mobile = $param['data']['content'];
            $this->setClientId($mobile, $param['frame']->fd);
            $this->commandOut("客户端" . $param['frame']->fd."<=>手机{$mobile}关联成功");
            $param['ws']->push($param['frame']->fd, "客户端".$param['frame']->fd . "<=>{$mobile}关联成功\n");
        }
    }

    /**
     * 客户端绑定Token
     * @param $param
     */
    public function messageActionRelToken($param){
        if(!empty($param['data']['content'])){
            $token = $param['data']['content'];
            $this->setClientId($token, $param['frame']->fd);
            $this->commandOut("客户端" . $param['frame']->fd."<=>".$token."关联成功");
            $param['ws']->push($param['frame']->fd, "客户端".$param['frame']->fd."<=>{$token}关联成功\n");
        }
    }

    /**
     * 获取客户端ID
     * @param $token
     * @return integer
     */
    public function getClientId($token){
        $cache = \Yii::$app->getCache();
        $content = $cache->get(self::CLIENT_LIST_CACHE_KEY);
        $clientDatas = $content ? json_decode($content, true) : [];
        return isset($clientDatas[$token]) ? $clientDatas[$token] : null;
    }

    /**
     * 获取客户端TOKEN
     * @param $fd
     * @return string
     */
    public function getClientToken($fd){
        $cache = \Yii::$app->getCache();
        $content = $cache->get(self::CLIENT_LIST_CACHE_KEY);
        $tokenDatas = array_flip(($content ? json_decode($content, true) : []));
        return isset($tokenDatas[$fd]) ? $tokenDatas[$fd] : null;
    }

    /**
     * 设置客户端ID
     * @param $token
     * @param $fd
     */
    public function setClientId($token, $fd){
        $content = \Yii::$app->getCache()->get(self::CLIENT_LIST_CACHE_KEY);
        $clientDatas = $content ? json_decode($content, true) : [];
        $clientDatas[$token] = $fd;
        \Yii::$app->getCache()->set(self::CLIENT_LIST_CACHE_KEY, json_encode($clientDatas));
    }

    /**
     * 删除客户端
     * @param $token
     */
    public function cleanClient($token){
        $content = \Yii::$app->getCache()->get(self::CLIENT_LIST_CACHE_KEY);
        $clientDatas = $content ? json_decode($content, true) : [];
        if(isset($clientDatas[$token])){
            unset($clientDatas[$token]);
        }
        \Yii::$app->getCache()->set(self::CLIENT_LIST_CACHE_KEY, json_encode($clientDatas));
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

