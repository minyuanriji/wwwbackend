<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\WechatPaySdkApi;
use app\plugins\smart_shop\exception\WxNoOperateMoneyException;
use app\plugins\smart_shop\models\Order;

class OrderUnfreezeForm extends BaseModel {

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function unfreeze(){

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
            if($detail['pay_type'] == 1){
                static::wechatUnfreeze($order, $shop, $detail);
            }else{
                static::alipayUnfreeze($order, $shop, $detail);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 解除微信冻结资金
     * @param Order $order
     * @param $shop
     * @param $detail
     * @throws \Exception
     */
    public static function wechatUnfreeze(Order $order, $shop, $detail){

        $unsplitAmount = OrderSplitInfoForm::getWechat($order, $shop, $detail);

        if($unsplitAmount > 0){

            $wechatPay = new WechatPaySdkApi([
                "mchid"          => $shop->setting['sp_mchid'],
                "serial"         => $shop->setting['cert_serial'],
                "privateKeyPath" => $shop->setting['apiclient_key'],
                "wechatCertPath" => $shop->setting['wechat_cert']
            ]);

            $splitData = !empty($order->split_data) ? json_decode($order->split_data, true) : [];
            if(empty($splitData['out_order_no'])){
                $splitData['out_order_no'] = "wx" . md5(uniqid() . rand(0, 10000));
            }

            $outOrderNo = isset($splitData['order_id']) && !empty($splitData['order_id']) ? $splitData['order_id'] : $splitData['out_order_no'];

            $data = $wechatPay->post("v3/profitsharing/orders/unfreeze", [
                "sub_mchid"      => (string)$detail['mno'],
                "transaction_id" => (string)$detail['transaction_id'],
                "out_order_no"   => (string)$outOrderNo,
                "description"    => "微信支付"
            ]);
            if(!isset($data['state']) || !in_array($data['state'], ["PROCESSING", "FINISHED"])){
                throw new \Exception("解除冻结资金失败：" . json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            if(isset($data['order_id'])){
                $splitData['order_id'] = $data['order_id'];
            }

            $order->updated_at     = time();
            $order->split_data     = json_encode($splitData);
            if(!$order->save()){
                throw new \Exception(json_encode($order->getErrors()));
            }
        }else{
            throw new WxNoOperateMoneyException("无可操作金额");
        }
    }

    public static function alipayUnfreeze($order, $shop, $detail){}
}