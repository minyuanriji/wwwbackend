<?php

namespace app\forms\api\order;

use app\component\efps\Efps;
use app\core\ApiCode;
use app\core\payment\PaymentOrder;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\EfpsPaymentOrder;
use app\models\Order;
use app\models\PaymentOrderUnion;
use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\mch\models\MchCheckoutOrder;

/**
 * @deprecated
 */
class EfpsPayForm extends BaseModel{

    public $union_id;

    public $stands_mall_id;

    public $wx_type;

    public static $notifyUri = "/web/pay-notify/efps.php";

    public function rules(){
        return [
            [['union_id'], 'required'],
            [['union_id','stands_mall_id'], 'integer'],
            [['wx_type'], 'safe'],
        ];
    }

    /**
     * 余额支付
     * @return array
     */
    public function balancePay(){
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (\Yii::$app->user->isGuest) {
                throw new \Exception('用户未登录。');
            }

            $user = \Yii::$app->user->identity;
            $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $this->union_id]);
            if (!$paymentOrderUnion) {
                throw new \Exception('待支付订单不存在。');
            }

            if($paymentOrderUnion->is_pay == 1){
                throw new \Exception('请勿重复支付');
            }

            $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
            if (!empty($supportPayTypes) && is_array($supportPayTypes) && !in_array("balance", $supportPayTypes)) {
                if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用余额支付
                    throw new \Exception('暂不支持余额支付。');
                }
            }

            $paymentConfigs = AppConfigLogic::getPaymentConfig();
            $pay_password_status = isset($paymentConfigs["pay_password_status"]) ? $paymentConfigs["pay_password_status"] : 0;
            if($pay_password_status == 1){
                if(empty($user->transaction_password)){
                    throw new \Exception('您未设置支付密码');
                }
                if(empty($transaction_password)){
                    throw new \Exception('请输入交易密码');
                }
                if (!\Yii::$app->getSecurity()->validatePassword(trim($transaction_password), $user->transaction_password)) {
                    throw new \Exception('支付密码错误');
                }
            }

            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            $totalAmount = 0;
            foreach ($paymentOrders as $paymentOrder) {
                $totalAmount += $paymentOrder->amount;
            }
            $balanceAmount = \Yii::$app->currency->setUser($user)->balance->select();
            if ($balanceAmount < $totalAmount) {
                throw new \Exception('账户余额不足。');
            }
            $paymentOrderUnion->is_pay   = 1;
            $paymentOrderUnion->pay_type = 3;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }

            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay   = 1;
                $paymentOrder->pay_type = 3;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                if (substr($paymentOrder->order_no, 0, 2) == "MS") {
                    $mchOrder = MchCheckoutOrder::findOne(['order_no' => $paymentOrder->order_no]);
                    if (!$mchOrder) throw new \Exception('商家扫码订单不存在！');
                    $store = Store::findOne(['mch_id' => $mchOrder->mch_id]);
                    if (!$store) throw new \Exception('商户不存在！');
                    $desc = '支付商家二维码';
                    $source_type = 'mch_checkout_order';
                    $source_id = $mchOrder->id;
                } elseif(substr($paymentOrder->order_no, 0, 4) == "ALIS") {
                    $order = AlibabaDistributionOrder::findOne(['order_no' => $paymentOrder->order_no]);
                    if (!$order) throw new \Exception('订单不存在！');
                    $desc = '支付1688分销订单';
                    $source_type = '1688_distribution_order';
                    $source_id = $order->id;
                }elseif(substr($paymentOrder->order_no, 0, 2) == "HO"){
                    $order = HotelOrder::findOne(['order_no' => $paymentOrder->order_no]);
                    if (!$order) throw new \Exception('订单不存在！');
                    $desc = '支付酒店订单';
                    $source_type = 'hotel_order';
                    $source_id   = $order->id;
                }elseif(substr($paymentOrder->order_no, 0, 2) == "HF"){
                    $order = AddcreditOrder::findOne(['order_no' => $paymentOrder->order_no]);
                    if (!$order) throw new \Exception('订单不存在！');
                    $desc = '支付话费订单';
                    $source_type = 'addcredit_order';
                    $source_id   = $order->id;
                }else {
                    $order = Order::findOne(['order_no' => $paymentOrder->order_no]);
                    if (!$order) throw new \Exception('订单不存在！');
                    $desc = '来自订单商品' . " ‘" . $paymentOrder->title . "‘ 账户余额支付：" . (float)$paymentOrder->amount . '元';
                    $source_type = 'order';
                    $source_id = $order->id;
                }

                $NotifyClass = $paymentOrder->notify_class;
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo'     => $paymentOrder->order_no,
                    'amount'      => (float)$paymentOrder->amount,
                    'title'       => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType'     => "balance",
                    ]);
                    if ($po->amount > 0) {
                        if (!\Yii::$app->currency->setUser($user)->balance->sub(
                            $po->amount,
                            $desc,
                            '',
                            $source_type,
                            $source_id
                        )) {
                            throw new \Exception('余额操作失败。');
                        }
                    }
                    try {
                        $notifyObject->notify($po);
                    } catch (\Exception $e) {
                        return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
                    }
            }
            $t->commit();

            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'msg'   => '支付成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    /**
     * 微信支付
     * @return array
     */
    public function wechatPay(){
        return $this->callPay("IF-WeChat-01");
    }

    /**
     * 支付宝支付
     * @return array
     */
    public function aliPay(){
        return $this->callPay("IF-QRcode-01");
    }


    public function callPay($payAPI){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            $paymentOrderUnion = PaymentOrderUnion::findOne($this->union_id);

            if(!$paymentOrderUnion){
                throw new \Exception("支付记录不存在");
            }

            if($paymentOrderUnion->is_pay){
                throw new \Exception("请勿重新支付");
            }

            $paymentOrders = $paymentOrderUnion->paymentOrder;
            if(!$paymentOrders){
                throw new \Exception("支付订单记录不存在");
            }

            $efpsPaymentOrder = EfpsPaymentOrder::findOne([
                "payment_order_union_id" => $paymentOrderUnion->id,
                "payAPI"                 => $payAPI
            ]);
            if(!$efpsPaymentOrder){
                $efpsPaymentOrder = new EfpsPaymentOrder();
                $efpsPaymentOrder->payment_order_union_id = $paymentOrderUnion->id;
                $efpsPaymentOrder->payAPI                 = $payAPI;
                $orderInfo = [
                    'Id'           => $paymentOrderUnion->id,
                    "businessType" => "100001",
                    "goodsList"    => []
                ];
                foreach($paymentOrders as $paymentOrder){
                    /*if(substr($paymentOrder->order_no, 0, 2) == "MS") { //商家结账单
                        $checkoutOrder = MchCheckoutOrder::findOne([
                            "order_no" => $paymentOrder->order_no
                        ]);
                        if (!$checkoutOrder) {
                            throw new \Exception("订单不存在");
                        }
                        $mchStore = $checkoutOrder->mchStore;
                        if (!$mchStore) {
                            throw new \Exception("无法获取店铺信息");
                        }
                        $orderInfo['goodsList'][] = [
                            "goodsId" => (string)$mchStore->mch_id,
                            "name" => $mchStore->name,
                            "price" => $checkoutOrder->order_price * 100,
                            "number" => "1",
                            "amount" => (string)$checkoutOrder->order_price * 100
                        ];
                    }elseif(substr($paymentOrder->order_no, 0, 4) == "ALIS") {
                        $order = AlibabaDistributionOrder::findOne(['order_no' => $paymentOrder->order_no]);
                        if (!$order){
                            throw new \Exception('订单不存在！');
                        }
                    }elseif(substr($paymentOrder->order_no, 0, 2) == "HO"){
                        $order = HotelOrder::findOne(['order_no' => $paymentOrder->order_no]);
                        if (!$order){
                            throw new \Exception('订单不存在！');
                        }

                    }else{
                        $order = $paymentOrder->order;
                        if(!$order){
                            throw new \Exception("订单不存在");
                        }
                        $orderDetails = $order->detail;
                        foreach($orderDetails as $detail){
                            $goodsInfo = json_decode($detail->goods_info, true);
                            $orderInfo['goodsList'][] = [
                                "goodsId" => (string)$detail->goods_id,
                                "name"    => $goodsInfo['goods_attr']['name'],
                                "price"   => $goodsInfo['goods_attr']['original_price'] * 100,
                                "number"  => (string)$detail->num,
                                "amount"  => (string)$detail->total_price * 100
                            ];
                        }
                    }*/
                    $orderInfo['goodsList'][] = [
                        "goodsId" => (string)$paymentOrder->id,
                        "name"    => $paymentOrder->order_no,
                        "price"   => $paymentOrder->amount * 100,
                        "number"  => "1",
                        "amount"  => (string)$paymentOrder->amount * 100
                    ];
                }
                $efpsPaymentOrder->orderInfo = json_encode($orderInfo);
            }

            $notifyUrl = \Yii::$app->getRequest()->getHostInfo() . static::$notifyUri;

            $efpsPaymentOrder->customerCode           = \Yii::$app->efps->getCustomerCode();
            $efpsPaymentOrder->payAmount              = $paymentOrderUnion->amount * 100;
            $efpsPaymentOrder->payCurrency            = "CNY";
            $efpsPaymentOrder->outTradeNo             = date("YmdHis") . rand(10000, 99999);
            $efpsPaymentOrder->transactionStartTime   = date("YmdHis");
            $efpsPaymentOrder->nonceStr               = md5(uniqid());
            $efpsPaymentOrder->notifyUrl              = $notifyUrl;
            $efpsPaymentOrder->update_at              = time();
            $efpsPaymentOrder->is_pay                 = 0;

            if($payAPI == "IF-WeChat-01"){ //微信公众号/小程序支付

                if ($this->wx_type == 'wechat') { //公众号
                    $platform = User::PLATFORM_WECHAT;
                    $where = [
                        "user_id"  => \Yii::$app->user->id,
                        "platform" => $platform,
                    ];
                } elseif ($this->wx_type == 'mp-wx')  {  //小程序
                    if(!$this->stands_mall_id){
                        throw new \Exception("商城ID不存在");
                    }
                    $platform = User::PLATFORM_MP_WX;
                    $where = [
                        "user_id"  => \Yii::$app->user->id,
                        "platform" => $platform,
                        "mall_id" => $this->stands_mall_id,
                    ];
                } else {  //默认 公众号
                    $platform = User::PLATFORM_WECHAT;
                    $where = [
                        "user_id"  => \Yii::$app->user->id,
                        "platform" => $platform,
                    ];
                }

                $userInfo = UserInfo::findOne($where);
                if(!$userInfo || empty($userInfo->openid)){
                    throw new \Exception("用户需要授权获取openid");
                }

                if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){ //小程序
                    $efpsPaymentOrder->payMethod = "1";
                    $efpsPaymentOrder->appId = "wx3ab6add3406998a1";
                }else{ //公众号
                    $efpsPaymentOrder->payMethod = "35";
                    $efpsPaymentOrder->appId = \Yii::$app->params['wechatConfig']['app_id'];
                }
                $efpsPaymentOrder->openId = $userInfo->openid;
            }else{ //支付宝主扫支付
                $efpsPaymentOrder->payMethod = "7";
            }

            if(!$efpsPaymentOrder->save()){
                throw new \Exception($this->responseErrorMsg($efpsPaymentOrder));
            }

            $data = [
                "outTradeNo"   => $efpsPaymentOrder->outTradeNo,
                "customerCode" => $efpsPaymentOrder->customerCode,
                "payAmount"    => $efpsPaymentOrder->payAmount,
                "notifyUrl"    => $efpsPaymentOrder->notifyUrl,
                "orderInfo"    => json_decode($efpsPaymentOrder->orderInfo, true)
            ];

            if($payAPI == "IF-WeChat-01") { //微信公众号/小程序支付
                $res = \Yii::$app->efps->payWxJSAPIPayment(array_merge($data, [
                    "appId"  => $efpsPaymentOrder->appId,
                    "openId" => $efpsPaymentOrder->openId
                ]));
            }else{ //支付宝主扫支付
                $res = \Yii::$app->efps->payAliJSAPIPayment($data);
            }
            if($res['code'] != Efps::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'msg'   => '请求成功',
                'data'  => $payAPI == "IF-WeChat-01" ? $res['data']['wxJsapiParam'] : $res['data']
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
