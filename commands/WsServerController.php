<?php
namespace app\commands;


use app\plugins\mch\models\MchClient;

class WsServerController extends BaseCommandController {

    const CLIENT_REL_MOBILE_CACHE_KEY_PREFIX = "WEBSOCKET_CLIENT_LIST:";
    const CLIENT_LIST_CACHE_KEY              = "WEBSOCKET_CLIENT_LIST";

    public function actionListen(){

        global $ws;

        //创建WebSocket Server对象，监听0.0.0.0:9515端口
        $ws = new \Swoole\WebSocket\Server('0.0.0.0', 9515);

        //监听WebSocket连接打开事件
        $ws->on('Open', function ($ws, $request) {
            $this->commandOut("客户端[ID:{$request->fd}]已连接");
        });

        //监听WebSocket消息事件
        $ws->on('Message', function ($ws, $frame) {

            if(!static::authMessageCheck($ws, $frame)){
                return;
            }

            if(isset($frame->fd)){
                $token = $this->getClientToken($frame->fd);
                if($token){
                    $this->commandOut("收到消息[ID:{$frame->fd}，token:{$token}]：" . $frame->data);
                }
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
        $this->commandOut("通知商户付款>>>>>>开始");
        $this->commandOut("token:{$token}");
        $this->commandOut("fd:{$fd}");
        if(empty($fd) || !$param['ws']->isEstablished($fd)){
            $this->commandOut("通知商户付款>>>>>>失败");
            $param['response']->end("客户端已断开");
        }else{
            $this->commandOut("通知商户付款>>>>>>成功");
            $param['ws']->push($fd, $text);
            $param['response']->end("SUCCESS");
        }
        $this->commandOut("通知商户付款>>>>>>结束");
    }

    /**
     * 客户端绑定手机号
     * @param $param
     */
    public function messageActionRelMobile($param){
        if(!empty($param['data']['content'])){
            $mobile = $param['data']['content'];
            $this->setClientId($mobile, $param);
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
            $this->setClientId($token, $param);
            $param['ws']->push($param['frame']->fd, "客户端".$param['frame']->fd."<=>{$token}关联成功\n");
        }
    }

    /**
     * 获取客户端ID
     * @param $token
     * @return integer
     */
    public function getClientId($token){
        $mchClient = MchClient::findOne(["token" => $token]);
        return $mchClient ? $mchClient->fd : null;
    }

    /**
     * 获取客户端TOKEN
     * @param $fd
     * @return string
     */
    public function getClientToken($fd){
        $mchClient = MchClient::findOne(["fd" => $fd]);
        return $mchClient ? $mchClient->token : null;
    }

    /**
     * 设置客户端ID
     * @param $token
     * @param $param
     */
    public function setClientId($token, $param){
        $mchClient = MchClient::findOne(["token" => $token]);
        try {
            if(!$mchClient){
                $mchClient = new MchClient([
                    "token"      => $token,
                    "created_at" => time()
                ]);
            }
            $mchClient->fd         = $param['frame']->fd;
            $mchClient->updated_at = time();
            if(!$mchClient->save()){
                throw new \Exception(json_encode($mchClient->getErrors()));
            }
        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
            $param['ws']->close($mchClient->fd, true);
        }
    }

    /**
     * 删除客户端
     * @param $token
     */
    public function cleanClient($token){
        try {
            $mchClient = MchClient::findOne(["token" => $token]);
            if($mchClient){
                $mchClient->fd         = uniqid();
                $mchClient->updated_at = time();
                if(!$mchClient->save()){
                    throw new \Exception(json_encode($mchClient->getErrors()));
                }
            }
        }catch (\Exception $e){
            $this->commandOut($e->getMessage());
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

