<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 消息发送类
 * Author: zal
 * Date: 2020-07-24
 * Time: 20:11
 */

namespace app\core\jmessage;


use app\core\jmessage\JmessageModule;
use JMessage\IM\Admin;
use JMessage\IM\Message;
use JMessage\IM\Resource;

class JmessageMessageModule extends JmessageModule
{
    public $messageModel;
    public $jm;

    public function __construct()
    {
        $this->messageModel = new Message($this->jm);
    }

    /**
     * 发送图片消息
     * @param $image
     * @param $userId
     * @return bool|mixed
     */
    public function imageMessage($image,$userId){
        $rescource = new Resource($this->jm);

        $response = $rescource->upload('image', $image);

        $result = self::parsingResponse($response);
        if($result === false){
            return $result;
        }

        $from = [
            'id'   => 'admin',
            'type' => 'admin'
        ];

        $target = [
            'id'   => 'user_'.$userId,
            'type' => 'single'
        ];

        $msg = $result;

        $response = $this->messageModel->sendImage(1, $from, $target, $msg);
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 撤回消息
     * @param $userId
     * @param $msg
     * @return bool|mixed
     */
    public function retractMessage($userId,$msg){
        $message = $this->messageModel;

        $from = [
            'id'   => 'admin',
            'type' => 'admin'
        ];

        $target = [
            'id'   => 'user_'.$userId,
            'type' => 'single'
        ];
        $msg = [
            'text' => $msg
        ];
        $response = $message->sendText(1, $from, $target, $msg);
        $result = self::parsingResponse($response);
        if($result === false){
            return false;
        }
        $msgid = $result['msg_id'];

        $response = $message->retract($msgid, 'admin');
        $result = self::parsingResponse($response);
        return $result;
    }

    /**
     * 发送文本消息
     * @param $userId
     * @param $msg
     * @return bool|mixed
     */
    public function textMessage($targetUserId,$msg){
        $from = [
            'id'   => 'admin',
            'type' => 'admin'
        ];

        $target = [
            'id'   => 'user_'.$targetUserId,
            'type' => 'single'
        ];
        $msg = [
            'text' => $msg
        ];
        $response = $this->messageModel->sendText(1, $from, $target, $msg);
        $result = self::parsingResponse($response);
        return $result;
    }


}
