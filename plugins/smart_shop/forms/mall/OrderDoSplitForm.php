<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\mch\models\Mch;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\AlipaySdkApi;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\WechatPaySdkApi;
use app\plugins\smart_shop\models\Order;

class OrderDoSplitForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function split(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $order = Order::findOne($this->id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->status != Order::STATUS_UNCONFIRMED){
                throw new \Exception("状态异常");
            }

            $mch = Mch::findOne($order->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("无法获取到商户信息");
            }

            $shop = new SmartShop();
            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);

            if($detail['pay_type'] == 1){
                static::wechatSplit($mch, $order, $shop, $detail);
            }else{
                static::alipaySplit($mch, $order, $shop, $detail);
            }

            $order->status     = Order::STATUS_FINISHED;
            $order->updated_at = time();
            $order->save();


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
     * 支付宝分账
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \Exception
     */
    public static function alipaySplit(Mch $mch, Order $order, SmartShop $shop, $detail){
        $aliPay = new AlipaySdkApi([
            "appId"                  => $shop->setting['ali_sp_appid'],
            "rsaPrivateKeyPath"      => $shop->setting['ali_rsaPrivateKeyPath'],
            "alipayrsaPublicKeyPath" => $shop->setting['ali_alipayrsaPublicKeyPath']
        ]);

        //获取订单详情
        $res = $aliPay->tradeQuery([
            "out_trade_no" => $detail['transaction_id']
        ], $detail['mno_ali']);
        if(!isset($res['trade_status']) || $res['trade_status'] != "TRADE_SUCCESS"){
            throw new \Exception("无法获取到订单信息");
        }

        $tradeNo = $res['trade_no']; //支付宝交易号

        //计算出支付公司扣取的费用
        $rate = isset($shop->setting['ali_rate']) ? max(0, floatval($shop->setting['ali_rate'])) : 0;
        $deductedAmount = $detail['pay_price'] * ($rate/100);

        //可分账金额（单位分）
        $unsplitAmount = intval(floatval($res['receipt_amount']) * 100);
        if(!$unsplitAmount){
            throw new \Exception("无可分账金额");
        }

        //如果支付用户手机号为空，就不分帐
        $amount = !empty($order->pay_user_mobile) ? round((($mch->transfer_rate/100) * $unsplitAmount)/100, 2) : 0;

        $splitData['receivers'] = isset($splitData['receivers']) && !empty($splitData['receivers']) ? $splitData['receivers'] : [];

        if(empty($splitData['out_request_no'])){
            $splitData['out_request_no'] = "ali" . date("ymdhis") . rand(0, 10000);
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if($amount > 0){
                $receiver = ["trans_in_type" => (string)$shop->setting['ali_fz_type'], "trans_in" => (string)$shop->setting['ali_fz_account'], "amount" => $amount, "description" => "商户服务费收取"];
                $splitData['receivers'] = [$receiver];
            }
            $order->status        = $detail['order_status'] == 3 ? Order::STATUS_FINISHED : Order::STATUS_PROCESSING;
            $order->updated_at    = time();
            $order->split_data    = json_encode($splitData);
            $order->split_amount  = $amount;
            if(!$order->save()){
                throw new \Exception(json_encode($order->getErrors()));
            }

            if($amount > 0){

                //绑定分账关系
                $aliPay->tradeRoyaltyRelationBind($detail['mno_ali'], [
                    ["type" => (string)$shop->setting['ali_fz_type'], "account" => (string)$shop->setting['ali_fz_account']]
                ], $splitData['out_request_no']);

                //执行分账请求
                $aliPay->tradeOrderSettle($detail['mno_ali'], $splitData['out_request_no'], $tradeNo, [$receiver]);
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            throw $e;
        }

    }

    /**
     * 微信支付分账
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \Exception
     */
    public static function wechatSplit(Mch $mch, Order $order, SmartShop $shop, $detail){

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
        if(!isset($res['trade_state']) || $res['trade_state'] != "SUCCESS"){
            throw new \Exception("订单未支付成功");
        }
        $detail['transaction_id'] = $res['transaction_id'];

        //实际支付金额（分）
        $realAmount = (float)$res['amount']['total'];

        //计算出微信扣取的费用（分）
        $rate = isset($shop->setting['wechat_rate']) ? max(0, floatval($shop->setting['wechat_rate'])) : 0;
        $wxGotAmount = $realAmount * ($rate/100);

        //获取可分账金额
        $unsplitAmount = OrderSplitInfoForm::getWechat($order, $shop, $detail);
        if(!$unsplitAmount){
            throw new \Exception("无可分账金额");
        }

        $splitData = !empty($order->split_data) ? json_decode($order->split_data, true) : [];
        if(empty($splitData['out_order_no'])){
            $splitData['out_order_no'] = "wx" . md5(uniqid() . rand(0, 10000));
        }

        //计算手续费（分）
        //如果支付用户手机号为空，就不分帐
        //手续费还要减去微信扣取的费用
        $amount = !empty($order->pay_user_mobile) ? (int)(($mch->transfer_rate/100) * $unsplitAmount) : 0;
        $amount = (int)max(0, $amount - $wxGotAmount);

        $splitData['transfer_rate'] = $mch->transfer_rate;
        $splitData['receivers'] = isset($splitData['receivers']) && !empty($splitData['receivers']) ? $splitData['receivers'] : [];
        $splitData['transaction_id'] = $detail['transaction_id'];

        $t = \Yii::$app->db->beginTransaction();
        try {

            $option = [
                "sub_mchid"     => (string)$detail['mno'],
                "appid"         => (string)$shop->setting['sp_appid'],
                "type"          => (string)$shop->setting['wechat_fz_type'],
                "name"          => "ENC:" . $shop->setting['wechat_fz_name'],
                "account"       => (string)$shop->setting['wechat_fz_account'],
                "relation_type" => "SERVICE_PROVIDER"
            ];

            if($amount > 0){
                $receiver = ["type" => $shop->setting['wechat_fz_type'], "account" => (string)$shop->setting['wechat_fz_account'], "amount" => $amount, "description" => "商户服务费收取"];
                $splitData['receivers'] = [$receiver];
            }

            $order->status        = $detail['order_status'] == 3 ? Order::STATUS_FINISHED : Order::STATUS_PROCESSING;
            $order->updated_at    = time();
            $order->split_data    = json_encode($splitData);
            $order->split_amount  = floatval($amount/100);
            if(!$order->save()){
                throw new \Exception(json_encode($order->getErrors()));
            }

            if($amount > 0){

                //绑定分账关系
                $wechatPay->post("v3/profitsharing/receivers/add", $option);

                $data = $wechatPay->post("v3/profitsharing/orders", [
                    "sub_mchid"        => (string)$detail['mno'],
                    "appid"            => (string)$shop->setting['sp_appid'],
                    "transaction_id"   => (string)$detail['transaction_id'],
                    "out_order_no"     => $splitData['out_order_no'],
                    "receivers"        => [$receiver],
                    "unfreeze_unsplit" => true
                ]);
                if(!isset($data['state']) || !in_array($data['state'], ["PROCESSING", "FINISHED"])){
                    throw new \Exception("分账失败");
                }
            }else{
                $data = $wechatPay->post("v3/profitsharing/orders/unfreeze", [
                    "sub_mchid"      => (string)$detail['mno'],
                    "transaction_id" => (string)$detail['transaction_id'],
                    "out_order_no"   => (string)$splitData['out_order_no'],
                    "description"    => "微信支付"
                ]);
                if(!isset($data['state']) || !in_array($data['state'], ["PROCESSING", "FINISHED"])){
                    throw new \Exception("分账失败");
                }
            }

            //保存微信接口请求返回订单号信息
            $splitData['out_order_no'] = $data['out_order_no'];
            $splitData['order_id']     = $data['order_id'];
            $order->split_data = json_encode($splitData);
            $order->save();

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            throw $e;
        }
    }
}