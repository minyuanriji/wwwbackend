<?php
namespace app\component\efps;


use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\MerchantApply;
use app\component\efps\lib\MerchantQuery;
use app\component\efps\lib\pay\AliJSAPIPayment;
use app\component\efps\lib\pay\PaymentQuery;
use app\component\efps\lib\pay\Refund;
use app\component\efps\lib\pay\SplitOrder;
use app\component\efps\lib\pay\UnifiedPayment;
use app\component\efps\lib\pay\WithdrawalToCard;
use app\component\efps\lib\pay\WithdrawalToCardQuery;
use app\component\efps\lib\pay\WxJSAPIPayment;
use app\component\efps\lib\wechat\BindAppId;
use app\component\efps\tools\Rsa;
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

    public function getCustomerCode(){
        return $this->main_config['acq_sp_id'];
    }

    /**
     * 退款操作
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function refund($params){
        return $this->request((new Refund())->build($params));
    }

    /**
     * 商户单笔提现
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function withdrawalToCard($params){
        if(!empty($params['bankUserName'])){
            $params['bankUserName'] = $this->encriptByPublic($params['bankUserName']);
            $params['bankCardNo'] = $this->encriptByPublic($params['bankCardNo']);
        }
        return $this->request((new WithdrawalToCard())->build($params));
    }

    /**
     * 商户单笔提现查询
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function withdrawalToCardQuery($params){
        return $this->request((new WithdrawalToCardQuery())->build($params));
    }

    private function encriptByPublic($str){
        openssl_public_encrypt($str, $encrypt, file_get_contents($this->main_config['efpsPublicKeyFilePath']));
        return base64_encode($encrypt);
    }

    /**
     * 交易分账接口
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function splitOrder($params){
        return $this->request((new SplitOrder())->build($params));
    }

    /**
     * 收银台支付接口
     * 统一下单
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function payUnifiedPayment($params){
        $params['clientIp'] = \Yii::$app->getRequest()->getUserIP();
        return $this->request((new UnifiedPayment())->build($params));
    }

    /**
     * 支付结果查询
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function payQuery($params){
        return $this->request((new PaymentQuery())->build($params));
    }

    /**
     * 绑定微信APPID
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function wechatBindAppId($params){
        return $this->request((new BindAppId())->build($params));
    }

    /**
     * 微信公众号/小程序支付接口
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function payWxJSAPIPayment($params){
        $params['clientIp'] = \Yii::$app->getRequest()->getUserIP();
        return $this->request((new WxJSAPIPayment())->build($params));
    }

    /**
     * 支付宝主扫支付
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function payAliJSAPIPayment($params){
        return $this->request((new AliJSAPIPayment())->build($params));
    }

    /**
     * 商户信息进件
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function merchantApply($params){
        return $this->request((new MerchantApply())->build($params), [
            "acqSpId" => $this->getCustomerCode()
        ]);
    }

    /**
     * 商户信息进件
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function merchantQuery($params){
        return $this->request((new MerchantQuery())->build($params), [
            "acqSpId" => $this->getCustomerCode()
        ]);
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

    public function request(InterfaceEfps $api, $extraParams = []){
        try {
            $params = array_merge($api->getParam(), $extraParams);

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
            curl_setopt($ch, CURLOPT_VERBOSE, false);

            $resText = curl_exec($ch);

            $error = curl_error($ch);
            $errno = curl_errno($ch);

            @curl_close($ch);

            if(!empty($resText)){
                $resObj = @json_decode($resText);
                if(is_object($resObj)){
                    if(isset($resObj->returnCode) && $resObj->returnCode != "0000"){
                        $errorText = isset($resObj->returnMsg) ? $resObj->returnMsg : "未知错误";
                        throw new \Exception($errorText);
                    }
                    if(isset($resObj->respCode) && $resObj->respCode != "0000"){
                        $errorText = isset($resObj->respMsg) ? $resObj->respMsg : "未知错误";
                        throw new \Exception($errorText);
                    }
                }else{
                    throw new \Exception("请求结果异常");
                }
            }
        }catch (\Exception $e){
            return [
                "code"     => self::CODE_FALI,
                "msg"      => $e->getMessage(),
                "data"     => @json_decode(!empty($resText) ? $resText : "{}", true),
                "json_str" => $jsonStr
            ];
        }

        return [
            "code"     => self::CODE_SUCCESS,
            "data"     => json_decode($resText, true),
            "json_str" => $jsonStr,
            "res_text" => $resText
        ];
    }
}