<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace lin010\mobile\plat\tx_cloud_15700;

use lin010\mobile\IService;
use lin010\mobile\QueryMobileInfoResult;

class TenCloud15700 implements IService
{
    /**
     * 获取手机号归属地信息
     * @return QueryMobileInfoResult
     */
    public function queryMobileInfo($mobile)
    {
        $secretId = Config::$secretID;
        $secretKey = Config::$secretKey;
        $source = 'market';

        //生成签名
        $datetime = gmdate('D, d M Y H:i:s T');
        $signStr = sprintf("x-date: %s\nx-source: %s", $datetime, $source);
        $sign = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));
        $auth = sprintf('hmac id="%s", algorithm="hmac-sha1", headers="x-date x-source", signature="%s"', $secretId, $sign);

        // 请求头
        $headers = array(
            'X-Source' => $source,
            'X-Date' => $datetime,
            'Authorization' => $auth
        );

        $url = 'https://service-av27cw4h-1257598706.ap-shanghai.apigateway.myqcloud.com/release/mobile?mobile=' . $mobile;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($v, $k) {
            return $k . ': ' . $v;
        }, array_values($headers), array_keys($headers)));

        $queryResult = new QueryMobileInfoResult();

        try {
            $content = curl_exec($ch);
            $errno   = curl_errno($ch);
            $error   = curl_error($ch);
            @curl_close($ch);

            if(!empty($error)){
                throw new \Exception($error . " " . $errno);
            }

            if(!$content){
                throw new \Exception("接口查询失败");
            }

            $data = @json_decode($content, true);
            if(!isset($data['code']) || $data['code'] != 200){
                throw new \Exception(isset($data['message']) ? $data['message'] : "接口返回错误");
            }

            $data = isset($data['data']) ? $data['data'] : null;
            if(!$data){
                throw new \Exception("接口信息返回错误");
            }

            $queryResult->code     = QueryMobileInfoResult::CODE_SUCC;
            $queryResult->platName = $data['isp'];
            $queryResult->province = $data['prov'];
            $queryResult->city     = $data['city'];

            if(preg_match("/电信/", $queryResult->platName)){
                $queryResult->platCode = "10000";
            }elseif(preg_match("/移动/", $queryResult->platName)) {
                $queryResult->platCode = "10086";
            }elseif(preg_match("/联通/", $queryResult->platName)){
                $queryResult->platCode = "10010";
            }
        }catch (\Exception $e){
            $queryResult->code    = QueryMobileInfoResult::CODE_FAIL;
            $queryResult->message = $e->getMessage();
        }

        return $queryResult;
    }
}