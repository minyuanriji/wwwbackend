<?php
namespace app\controllers\business;
use app\models\mysql\UserInfo;

class GetAttentionWeChat{
    public function getUserAttentionWeChatInfo($user_id){
        if(empty($user_id)){
            return [
                'status' => 2
            ];
        }
        $access_token = \Yii::$app->redis -> get('Attention_WeChat_token');
        if(empty($access_token)){
            $token = $this -> SetToken();
        }else{
            $token = $access_token;
        }
        $user_openid = (new UserInfo()) -> getUserOpenid($user_id);
        $subscribe_msg = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid={$user_openid['openid']}";
        $subscribe = json_decode(file_get_contents($subscribe_msg),true);
        return [
            'subscribe' => $subscribe['subscribe'],
            'status' => 1
        ];
    }

    public function SetToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxd7ac6d41d564256c&secret=0e450b0ae95a035b4aab4de88b6f3aa7';
        $json = $this -> http_get($url);
        \Yii::$app->redis -> set('Attention_WeChat_token',json_decode($json,true)['access_token']);
        \Yii::$app->redis->expire('Attention_WeChat_token', 7100);
        return json_decode($json,true)['access_token'];
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