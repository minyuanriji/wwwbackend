<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\AlipaySdkApi;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\WechatPaySdkApi;
use app\plugins\smart_shop\models\Order;

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

            $mch = Mch::findOne($order->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("无法获取到商户信息");
            }

            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store || $store->is_delete){
                throw new \Exception("无法获取门店信息");
            }

            $shop = new SmartShop();
            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);
            $info['id']          = $order->id;
            $info['total_price'] = $detail['total_price'];
            $info['pay_price']   = $detail['pay_price'];
            $info['pay_type']    = $detail['pay_type'];

            if($info['pay_type'] == 1){
                $info['unsplit_amount'] = static::getWechat($order, $shop, $detail);
            }else{
                $info['unsplit_amount'] = static::getAlipay($order, $shop, $detail);
            }

            $info['split_account'] = [
                ['name' => $detail['merchant_name'], 'amount' => round((1 - $mch->transfer_rate/100) * ($info['unsplit_amount']/100), 6)],
                ['name' => '平台', 'amount' => round(($mch->transfer_rate/100) * ($info['unsplit_amount']/100), 6)]
            ];

            $info['unsplit_amount'] = round($info['unsplit_amount']/100, 2);

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
     * 获取支付宝待分账金额（单位：分）
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \Exception
     */
    public static function getAlipay(Order $order, SmartShop $shop, $detail){
        $aliPay = new AlipaySdkApi([
            "appId"                  => $shop->setting['ali_sp_appid'],
            "rsaPrivateKeyPath"      => $shop->setting['ali_rsaPrivateKeyPath'],
            "alipayrsaPublicKeyPath" => $shop->setting['ali_alipayrsaPublicKeyPath']
        ]);

        //获取订单详情
        $data = $aliPay->tradeQuery([
            "out_trade_no" => $detail['transaction_id']
        ], $detail['mno_ali']);

        return isset($data['receipt_amount']) ? intval(floatval($data['receipt_amount']) * 100) : 0;
    }

    /**
     * 获取微信支付待分账金额（单位：分）
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \Exception
     */
    public static function getWechat(Order $order, SmartShop $shop, $detail){

        $wechatPay = new WechatPaySdkApi([
            "mchid"          => $shop->setting['sp_mchid'],
            "serial"         => $shop->setting['cert_serial'],
            "privateKeyPath" => $shop->setting['apiclient_key'],
            "wechatCertPath" => $shop->setting['wechat_cert']
        ]);

        //获取订单详情
        $res = $wechatPay->get("v3/pay/partner/transactions/out-trade-no/" . $detail['order_no'], [
            "sp_mchid"     => (string)$shop->setting['sp_mchid'],
            "sub_mchid"    => (string)$detail['mno']
        ]);
        if(!isset($res['transaction_id'])){
            throw new \Exception("无法获取到微信支付订单记录");
        }
        $detail['transaction_id'] = $res['transaction_id'];

        $data = $wechatPay->get('v3/profitsharing/transactions/'.$detail['transaction_id'].'/amounts');
        if(!isset($data['unsplit_amount'])){
            throw new \Exception("待分账金额查询失败");
        }
        
        return $data['unsplit_amount'];
    }
}