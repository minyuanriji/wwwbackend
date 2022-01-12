<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Order;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

class OrderSplitInfoForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getInfo(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $order = Order::findOne($this->id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            $shop = new SmartShop();
            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);

            $info['total_price'] = $detail['total_price'];
            $info['pay_price']   = $detail['pay_price'];
            $info['pay_type']    = $detail['pay_type'];

            if($info['pay_type'] == 1){
                $info['unsplit_amount'] = $this->getWechat($order, $shop, $detail);
            }else{
                $info['unsplit_amount'] = 0;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'info' => $info
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 获取微信支付待分账金额
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \Exception
     */
    private function getWechat(Order $order, SmartShop $shop, $detail){

// 商户号
        $merchantId = "1617888245";

// 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = file_get_contents("C:\\LINO1O\\公司文件\\智慧经营\\WXCertUtil\\cert\\1617888245_20211223_cert\\apiclient_key.pem");
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

// 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = "1B923E4B41FB7387551B0F175E7346ECF688F804";

// 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = file_get_contents("C:\\LINO1O\\公司文件\\智慧经营\\WXCertUtil\\cert\\1617888245_20211223_cert\\wechatpay_32632D72F5DED9D95B8E4B9E1B65BEBCCE03F31C.pem");
        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

// 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

// 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid'      => (string)$merchantId,
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ]
        ]);

        $resp = $instance->chain('v3/profitsharing/transactions/42000012982022201125214615308/amounts')
            ->get(['query' => [], 'curl' => [CURLOPT_SSL_VERIFYPEER => false]]);

        $data = @json_decode($resp->getBody(), true);
        if(!isset($data['unsplit_amount'])){
            throw new \Exception("待分账金额查询失败");
        }

        return $data['unsplit_amount'];
    }
}