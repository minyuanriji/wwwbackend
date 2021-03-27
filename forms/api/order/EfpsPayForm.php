<?php


namespace app\forms\api\order;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\PaymentEfpsOrder;
use app\models\PaymentOrderUnion;
use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class EfpsPayForm extends BaseModel{

    public $union_id;

    public static $notifyUri = "/web/pay-notify/efps.php";

    public function rules(){
        return [
            [['union_id'], 'required'],
            [['union_id'], 'integer'],
        ];
    }

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

            $paymentEfpsOrder = PaymentEfpsOrder::findOne([
                "payment_order_union_id" => $paymentOrderUnion->id,
                "payAPI"                 => $payAPI
            ]);
            if(!$paymentEfpsOrder){
                $paymentEfpsOrder = new PaymentEfpsOrder();
                $paymentEfpsOrder->payment_order_union_id = $paymentOrderUnion->id;
                $paymentEfpsOrder->payAPI                 = $payAPI;
                $orderInfo = [
                    'Id'           => $paymentOrderUnion->id,
                    "businessType" => "100001",
                    "goodsList"    => []
                ];
                foreach($paymentOrders as $paymentOrder){
                    if(substr($paymentOrder->order_no, 0, 2) == "MS"){ //商家结账单
                        $checkoutOrder = MchCheckoutOrder::findOne([
                            "order_no" => $paymentOrder->order_no
                        ]);
                        if(!$checkoutOrder){
                            throw new \Exception("订单不存在");
                        }
                        $mchStore = $checkoutOrder->mchStore;
                        if(!$mchStore){
                            throw new \Exception("无法获取店铺信息");
                        }
                        $orderInfo['goodsList'][] = [
                            "goodsId" => (string)$mchStore->mch_id,
                            "name"    => $mchStore->name,
                            "price"   => $checkoutOrder->order_price * 100,
                            "number"  => "1",
                            "amount"  => (string)$checkoutOrder->order_price * 100
                        ];
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
                    }
                }
                $paymentEfpsOrder->orderInfo = json_encode($orderInfo);
            }

            $notifyUrl = \Yii::$app->getRequest()->getHostInfo() . static::$notifyUri;

            $paymentEfpsOrder->customerCode           = \Yii::$app->efps->getCustomerCode();
            $paymentEfpsOrder->payAmount              = $paymentOrderUnion->amount * 100;
            $paymentEfpsOrder->payCurrency            = "CNY";
            $paymentEfpsOrder->outTradeNo             = date("YmdHis") . rand(10000, 99999);
            $paymentEfpsOrder->transactionStartTime   = date("YmdHis");
            $paymentEfpsOrder->nonceStr               = md5(uniqid());
            $paymentEfpsOrder->notifyUrl              = $notifyUrl;
            $paymentEfpsOrder->update_at              = time();
            $paymentEfpsOrder->is_pay                 = 0;

            if($payAPI == "IF-WeChat-01"){ //微信公众号/小程序支付

                $userInfo = UserInfo::findOne([
                    "user_id"  => \Yii::$app->user->id,
                    "platform" => User::PLATFORM_WECHAT
                ]);
                if(!$userInfo || empty($userInfo->openid)){
                    throw new \Exception("用户需要授权获取openid");
                }

                if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){ //小程序
                    $paymentEfpsOrder->payMethod = "1";
                }else{ //公众号
                    $paymentEfpsOrder->payMethod = "35";
                }
                $paymentEfpsOrder->appId = \Yii::$app->params['wechatConfig']['app_id'];
                $paymentEfpsOrder->openId = $userInfo->openid;
            }else{ //支付宝主扫支付
                $paymentEfpsOrder->payMethod = "7";
            }

            if(!$paymentEfpsOrder->save()){
                throw new \Exception($this->responseErrorMsg($paymentEfpsOrder));
            }

            $data = [
                "outTradeNo"   => $paymentEfpsOrder->outTradeNo,
                "customerCode" => $paymentEfpsOrder->customerCode,
                "payAmount"    => $paymentEfpsOrder->payAmount,
                "notifyUrl"    => $paymentEfpsOrder->notifyUrl,
                "orderInfo"    => json_decode($paymentEfpsOrder->orderInfo, true)
            ];

            if($payAPI == "IF-WeChat-01") { //微信公众号/小程序支付
                $res = \Yii::$app->efps->payWxJSAPIPayment(array_merge($data, [
                    "appId"  => $paymentEfpsOrder->appId,
                    "openId" => $paymentEfpsOrder->openId
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