<?php
/**
 * Created by PhpStorm.
 * User: wmc
 * Date: 21/7/31
 * Time: 上午11:20
 */

namespace App\Services\Response;

class ResponseService extends ResponseBase
{
    /**
     * 返回正确的json格式
     *
     * @param string $data
     * @param string $msg
     * @return
     */
    public static function success($data = '', $msg = '')
    {
        $errorNo = 0;
        $res = [
            'code' => $errorNo,
            'msg' => empty($msg) ? self::CODE[$errorNo] : $msg,
            'data' => $data
        ];
        $res = json_encode($res);

        self::writeLog($errorNo, $res);

        return $res;
    }

    /**
     * 错误返回json格式
     *
     * @param string $msg
     * @param int $errorNo
     */
    public static function error($msg = '', $errorNo = 1)
    {
        if (!is_numeric($errorNo)) {
            $errorNo = 1;
        }
        $code = empty($errorNo) ? 1 : $errorNo;
        $res = [
            'code' => $code,
            'msg' => !empty($msg) ? $msg : (isset(self::CODE[$errorNo]) ? self::CODE[$errorNo] : "操作失败!")
        ];
        $res = json_encode($res);

        self::writeLog($code, $res);

        return $res;
    }

    /**
     * response记录接口请求的日志
     *
     * @param $handleCode
     * @param $response
     */
    private static function writeLog($handleCode, $response)
    {
        // 有代理ip填代理ip，没有填客户端ip
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $sessionId = !isset($_COOKIE['wechat_session']) ? '' : substr($_COOKIE['wechat_session'], 0, 16);
        $uri = $_SERVER['REQUEST_URI'];
        $startTime = $_SERVER['REQUEST_TIME_FLOAT'];
        $nowTime = microtime(true);
        $consumeTime = bcsub($nowTime, $startTime, 3) * 1000;

        $resData = [
            'req' => $_REQUEST
        ];
        // 只有错误请求时，才将响应包写入日志
        if ($handleCode != 0) {
            $resData['res'] = $response;
        }

        \Yii::error("「sessionId：{$sessionId}」「请求：{$uri}」「耗时：{$consumeTime}ms」「ip：{$ip}」", $resData);
    }
}