<?php
class Efalipay {
    //测试环境接口路径
//    protected $gateway = 'https://test-efps.epaylinks.cn/api/txs/pay/NativePayment';
    protected $gateway = 'https://test-efps.epaylinks.cn/api/txs/split/accountSplit';
    //生产环境接口路径
    //protected $gateway = 'https://efps.epaylinks.cn/api/txs/pay/NativePayment';
    //私钥文件路径
    public $rsaPrivateKeyFilePath = __DIR__ . '/user.pfx';
    //易票联公钥    
    public $publicKeyFilePath = __DIR__ . '/efps.cer';
    //证书序列号
    public  $sign_no='562769003068874001';
    //证书密码
    public $password='Epaylinks@EFPS2018';
    //编码格式
    public $charset = "UTF-8";    
    public  $signType = "RSA2";    
    //商户号
    protected $config     = array(
        'customer_code'   => '562769003068874',
        'notify_url' => 'http://www.baidu.com',
        'return_url' => 'http://www.baidu.com'
    );
    
    
    public function check() {
        if (!$this->config['customer_code'] ) {
            E("支付设置有误！");
        }
        return true;
    }
    
    public function buildRequestForm() {
        $orderNo = "123456".date('YmdHis');
        
        echo '订单号:'.$orderNo;
        echo '<br>';
        $client_ip = "127.0.0.1";
        if (getenv('HTTP_CLIENT_IP')) {
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $orderInfo=array();
        $orderInfo['Id'] = $orderNo;
        $orderInfo['businessType'] = '130001';
        $orderInfo['goodsList'] = array(array('name'=>'pay','number'=>'one','amount'=>1));
        //$orderInfo = json_encode($orderInfo);
        
        $param = array(
            'outTradeNo' => $orderNo,
            'customerCode' => $this->config['customer_code'],
            'terminalNo' => '10001',
            'clientIp' => $client_ip,
            'orderInfo' => $orderInfo,
            'payMethod'  => 7,
            'payAmount' => 10,
            'payCurrency' => 'CNY',
            'channelType' =>'02',
            'notifyUrl' =>$this->config['notify_url'],
            'redirectUrl' =>$this->config['return_url'],
            'transactionStartTime' =>date('YmdHis'),
            'nonceStr' => 'pay'.rand(100,999),
            'version' => '3.0'
        );
        
        $sign = $this->sign(json_encode($param));

        echo '发送的参数'.json_encode($param);
        echo '<br>签名值'.$sign;

        $request = $this->http_post_json($this->gateway,json_encode($param),$sign);
        if($request && $request[0] == 200){
 //           $re_data = json_decode($request[1],true);
 //           if($re_data['returnCode'] == '0000'){
//                $payurl = $re_data['codeUrl'];
       //         $sHtml="<script language='javascript' type='text/javascript'>window.location.href='{$payurl}';</script>";
                echo '<br>'.'获取到的参数：';
 //               echo $request[1];
                return $request[1];
/*             }else{
                echo $request[1];
                exit;
            } */
            
        }else{
            print_r($request);
            exit;
        }
        exit;
        //return "";
    }
    
    
    public function generateSign($params) {
        return $this->sign($this->getSignContent($params));
    }
    
    public function rsaSign($params) {
        return $this->sign($this->getSignContent($params));
    }
    
    protected function getSignContent($params) {
        ksort($params);
        
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->charset);
                
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                
                $i++;
            }
        }
        
        unset ($k, $v);
        
        return $stringToBeSigned;
    }
    
    protected function sign($data) {
        
        $certs = array();
        openssl_pkcs12_read(file_get_contents($this->rsaPrivateKeyFilePath), $certs, $this->password); //其中password为你的证书密码
        
        ($certs) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        
        openssl_sign($data, $sign, $certs['pkey'],OPENSSL_ALGO_SHA256);
        
        $sign = base64_encode($sign);
        return $sign;
    }
    
    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
            if ($value === null)
                return true;
                if (trim($value) === "")
                    return true;
                    
                    return false;
    }
    
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return
     */
    public function verifyNotify($notify) {
        
        $ispost = $notify['ispost'];
        unset($notify['ispost']);
        $info = array();
        
        if($ispost){
            $param = array(
                'amount' => intval($notify['amount']),
                'procedureFee' => intval($notify['procedureFee']),
                'payTime' => intval($notify['payTime']),
                'settCycle' => $notify['settCycle'],
                'settCycleInterval' => $notify['settCycleInterval'],
                'outTradeNo' => $notify['outTradeNo'],
                'transactionNo' => $notify['transactionNo'],
                'customerCode' => $notify['customerCode'],
                'payState' =>$notify['payState'],
                'nonceStr' =>$notify['nonceStr']
            );
            
            $fp = fopen(ROOT_PATH."/data/runtime/iossdk/verifyNotify1_log.txt","a");
            flock($fp, LOCK_EX) ;
            fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".json_encode($param)."\n");
            flock($fp, LOCK_UN);
            fclose($fp);
            
            $sign = $notify['signature']['x-efps-sign'];
            
            $fp = fopen(ROOT_PATH."/data/runtime/iossdk/verifyNotify2_log.txt","a");
            flock($fp, LOCK_EX) ;
            fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$sign."\n");
            flock($fp, LOCK_UN);
            fclose($fp);
            
            $rs = $this->rsaCheckV2(json_encode($param),$this->publicKeyFilePath,$sign);
            
            if($rs && $notify['payState'] == 00){
                $out_trade_no = $notify['outTradeNo'];
                //支付宝交易号
                $trade_no = $notify['transactionNo'];
                //交易状态
                $trade_status = $notify['payState'];
                
                $info['status']       = ($trade_status == 00) ? 1 : 0;
                $info['trade_no']     = $trade_no;
                $info['out_trade_no'] = $out_trade_no;
                $info['total_amount'] = $notify['amount']/100;
                $info['ispost'] = 1;
                return $info;
            }
        }else{
            if($notify['payState'] == 00){
                $orderid = $notify['outTradeNo'];
                $info['status']       = 1;
                $info['out_trade_no'] = $orderid;
                
                return $info;
            }
        }
        
        
        $info['status']       = 2;
        $info['out_trade_no'] = '';
        return $info;
        
    }
    
    public function rsaCheckV2($params, $rsaPublicKeyFilePath,$sign) {
        //$sign = $params['sign'];
        //$params['sign'] = null;
        
        return $this->verify($params, $sign, $rsaPublicKeyFilePath);
    }
    
    function verify($data, $sign, $rsaPublicKeyFilePath) {
        
        //读取公钥文件
        $pubKey = file_get_contents($rsaPublicKeyFilePath);
        
        $res = openssl_get_publickey($pubKey);
        
        ($res) or die('RSA公钥错误。请检查公钥文件格式是否正确');
        //调用openssl内置方法验签，返回bool值
        
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        
        if(!$this->checkEmpty($this->publicKeyFilePath)) {
            //释放资源
            openssl_free_key($res);
        }
        
        return $result;
    }
    
    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
        
        
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                
                $data = mb_convert_encoding($data, $targetCharset);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        
        
        return $data;
    }
    
    protected function getParam($para) {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg.=$key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, -1);
        return $arg;
    }
    
    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id
     * @return
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    protected function getResponse2($Params) {
        $veryfy_url = $this->gateway . "?" . $Params;
        $responseTxt = $this->fsockOpen($veryfy_url);
        return $responseTxt;
    }
    
    protected function http_post_json($url, $jsonStr,$sign)
    {
        $ch = curl_init();
        $headers = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr),
            'x-efps-sign-no:'.$this->sign_no,
            'x-efps-sign-type:SHA256withRSA',
            'x-efps-sign:'.$sign,
            'x-efps-timestamp:'.date('YmdHis'),
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 跳过检查        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 跳过检查
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        return array($httpCode, $response);
    }
    
}


$efalipay = new Efalipay();

echo $efalipay->buildRequestForm();
?>
