<?php
namespace app\controllers\business;
use yii;
class WechatTemplate{


    public function sendTemplate(){
//        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this -> getToken();
//        var_dump($url);
        $data = [
            "touser" => "ohQHU50WSUDWOG3fhf2-nJhwBUck",
            "template_id" => "ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
            "url" => "http://weixin.qq.com/download",
            "miniprogram" => [
                "appid" => "xiaochengxuappid12345",
                "pagepath" => "index?foo=bar"
            ],
            "data" => [
                "first" => [
                    "value" => "恭喜你购买成功！",
                    "color" => "#173177"
                ]
            ],
        ];

        $result = $this -> http_post($url,json_encode($data));
        var_dump($result);

    }

    // 发送post请求
    public function http_post($url,$ret){
//        var_dump($ret);
        //1. 初始化
        $ch = curl_init();
//1.2 设置请求的url地址
        curl_setopt($ch,CURLOPT_URL,$url);
//1.6 请求头关闭
        curl_setopt($ch,CURLOPT_HEADER,0);
//1.5 请求得到的结果不直接输出，而是以字符串结果返回
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//1.7 设置请求超时时间,单位为秒
        curl_setopt($ch,CURLOPT_TIMEOUT,30);
//1.8 设置浏览器型号,可要可不要
//    curl_setopt($ch,CURLOPT_USERAGENT,'MSIE001');


//2.1 证书不检查
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        //设置为post请求,设置1为开启post
        curl_setopt($ch,CURLOPT_POST,1);
        //post请求的数据
        curl_setopt($ch,CURLOPT_POSTFIELDS,$ret);

        //1.3发送请求
        $data = curl_exec($ch);
//        var_dump($data);
//1.9 获取是否有异常,大于0就是有异常
//        echo curl_error($ch);
        if(curl_errno($ch) > 0){
            //2. 输出错误信息
            echo curl_error($ch);
        };

//1.4 关闭请求
        curl_close($ch);
        return $data;
    }

    public function http_get($url){
        //1. 初始化
        $ch = curl_init();
        //1.2 设置请求的url地址
        curl_setopt($ch,CURLOPT_URL,$url);
        //1.6 请求头关闭
        curl_setopt($ch,CURLOPT_HEADER,0);
        //1.5 请求得到的结果不直接输出，而是以字符串结果返回
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        //1.7 设置请求超时时间,单位为秒
        curl_setopt($ch,CURLOPT_TIMEOUT,30);
        //1.8 设置浏览器型号,可要可不要
        //    curl_setopt($ch,CURLOPT_USERAGENT,'MSIE001');
        //1.3发送请求
        $data = curl_exec($ch);

        //2.1 证书不检查
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        //1.9 获取是否有异常,大于0就是有异常
        echo curl_error($ch);
        if(curl_errno($ch) > 0){
            //2. 输出错误信息
            echo curl_error($ch);
        };

        //1.4 关闭请求
        curl_close($ch);
        return $data;
    }


}

