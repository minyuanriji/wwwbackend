<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础消息类
 * Author: zal
 * Date: 2020-07-24
 * Time: 20:11
 */

namespace app\core\jmessage;


use GuzzleHttp\Exception\ClientException;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\Message;
use yii\base\Component;

class JmessageModule
{
    public static $msg = "";

    public static function parsingResponse($response)
    {
        $body = $response["body"];
        if(isset($body["error"])){
            self::$msg = $body["message"];
            return false;
        }else{
            return $body;
        }
    }
}
