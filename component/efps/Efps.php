<?php
namespace app\component\efps;


use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\MerchantApply;
use app\component\efps\lib\MerchantQuery;
use yii\base\Component;

class Efps extends Component{

    const CODE_SUCCESS  = 0;
    const CODE_FALI     = -1;

    public $on_dev = true;
    public $charset = "UTF-8";      //编码格式
    public $signType = "RSA2";      //签名方式

    /**
     * 测试环境接口路径
     */
    public $gateway_dev = "http://test-efps.epaylinks.cn";

    /**
     * 生产环境接口路径
     */
    public $gateway = "https://efps.epaylinks.cn";

    public $main_config = [
        "rsaPrivateKeyFilePath" => "", //私钥文件路径
        "publicKeyFilePath"     => "", //易票联公钥
        "sign_no"               => "", //证书序列号
        "password"              => "", //证书密码
        "acq_sp_id"             => ""  //平台商户号
    ];

    public $notify_url;
    public $return_url;

    /**
     * 商户信息进件
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function merchantApply($params){
        return $this->request((new MerchantApply())->build($params));
    }

    /**
     * 商户信息进件
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function merchantQuery($params){
        return $this->request((new MerchantQuery())->build($params));
    }

    public function sign($data) {
        $certs = [];
        openssl_pkcs12_read(file_get_contents($this->main_config['rsaPrivateKeyFilePath']), $certs, $this->main_config['password']); //其中password为你的证书密码

        if(empty($certs)){
            throw new \Exception("您使用的私钥格式错误，请检查RSA私钥配置");
        }

        openssl_sign($data, $sign, $certs['pkey'],OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);
        return $sign;
    }

    public function request(InterfaceEfps $api){
        try {
            $params = $api->getParam();
            $params['acqSpId'] = $this->main_config['acq_sp_id'];

            $jsonStr = json_encode($params, JSON_UNESCAPED_UNICODE);
            $sign = $this->sign($jsonStr);

            $headers = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonStr),
                'x-efps-sign-no:' . $this->main_config['sign_no'],
                'x-efps-sign-type:SHA256withRSA',
                'x-efps-sign:'.$sign,
                'x-efps-timestamp:'.date('YmdHis'),
            );
            $url = ($this->on_dev ? $this->gateway_dev : $this->gateway) . $api->getApi();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 跳过检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 跳过检查
            //curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_VERBOSE, true);


            $resText = curl_exec($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            @curl_close($ch);

            if(!empty($resText)){
                $resObj = @json_decode($resText);
                if(is_object($resObj)){
                    if($resObj->respCode != "0000"){
                        $errorText = isset($resObj->respMsg) ? $resObj->respMsg : "未知错误";
                        throw new \Exception($errorText);
                    }
                }else{
                    throw new \Exception("请求结果异常");
                }
            }
        }catch (\Exception $e){
            return ["code" => self::CODE_FALI, "msg" => $e->getMessage()];
        }

        return ["code" => self::CODE_SUCCESS, "data" => json_decode($resText, true)];
    }
}