<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-22
 * Time: 16:07
 */

namespace app\helpers;


use app\core\payment\PaymentException;
use Yii;


/**
 * Class WechatHelper
 * @package app\helpers
 * @Notes 微信助手类
 */
class WechatHelper
{
    const TRADE_TYPE_JSAPI = "JSAPI";
    const SIGN_TYPE_MD5 = "md5";

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:29
     * @Note:验证token是否一致
     * @param $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数
     * @param $timestamp 时间戳
     * @param $nonce 随机数
     * @return bool
     */
    public static function verifyToken($signature, $timestamp, $nonce)
    {
        $config = Yii::$app->params['wechatConfig'];
        $token = $config['token'] ?? '';
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        return $tmpStr == $signature ? true : false;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:29
     * @Note:告诉微信已经成功了
     * @return bool|string
     */
    public static function success()
    {
        return ArrayHelper::toXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-22
     * @Time: 16:29
     * @Note:告诉微信失败了
     * @return bool|string
     */
    public static function fail()
    {
        return ArrayHelper::toXml(['return_code' => 'FAIL', 'return_msg' => 'OK']);
    }

    /**
     * 数组转换成XML数据
     * @param $array
     * @return string
     * @throws PaymentException
     */
    public static function arrayToXml($array)
    {
        if (!is_array($array)) {
            throw new PaymentException('`$arr`不是有效的array。');
        }
        $xml = "<xml>\r\n";
        $xml .= self::arrayToXmlSub($array);
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 数组转xml公共方法
     * @param $array
     * @return string
     * @throws PaymentException
     */
    private static function arrayToXmlSub($array)
    {
        if (!is_array($array)) {
            throw new PaymentException('`$array`不是有效的array。');
        }
        $xml = "";
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if (is_numeric($key)) {
                    $xml .= self::arrayToXmlSub($val);
                } else {
                    $xml .= "<" . $key . ">" . self::arrayToXmlSub($val) . "</" . $key . ">\r\n";
                }
            } elseif (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">\r\n";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">\r\n";
            }
        }
        return $xml;
    }

    /**
     * XML数据转换成array数组
     * @param string $xml
     * @return array
     */
    public static function xmlToArray($xml)
    {
        $res = [];
        if(self::isXmlString($xml)){
            // 禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $res = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return (array)$res;
    }

    /**
     * 是否是xml
     * @param $str
     * @return bool|mixed
     */
    public static function isXmlString($str)
    {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $str, true)) {
            xml_parser_free($xml_parser);
            return false;
        } else {
            return (json_decode(json_encode(simplexml_load_string($str)), true));
        }
    }
}