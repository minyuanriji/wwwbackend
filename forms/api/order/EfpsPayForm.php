<?php


namespace app\forms\api\order;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\PaymentEfpsOrder;
use app\models\PaymentOrderUnion;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class EfpsPayForm extends BaseModel{

    public $union_id;

    public static $notifyUri = "/pay-notify/efps.php";

    public function rules(){
        return [
            [['union_id'], 'required'],
            [['union_id'], 'integer'],
        ];
    }

    public function wechatPay(){

    }

    /**
     * 支付宝支付
     * @return array
     */
    public function aliPay(){
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
                "payAPI"                 => "IF-QRcode-01"
            ]);
            if(!$paymentEfpsOrder){
                $paymentEfpsOrder = new PaymentEfpsOrder();
                $paymentEfpsOrder->payment_order_union_id = $paymentOrderUnion->id;
                $paymentEfpsOrder->payAPI                 = "IF-QRcode-01";
                $paymentEfpsOrder->customerCode           = \Yii::$app->efps->getCustomerCode();
                $paymentEfpsOrder->payAmount              = $paymentOrderUnion->amount * 100;
                $paymentEfpsOrder->payCurrency            = "CNY";
                $paymentEfpsOrder->payMethod              = "7";
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

            $notifyUrl = \Yii::$app->getRequest()->getHostName() . static::$notifyUri;

            $paymentEfpsOrder->outTradeNo           = date("YmdHis") . rand(10000, 99999);
            $paymentEfpsOrder->transactionStartTime = date("YmdHis");
            $paymentEfpsOrder->nonceStr             = md5(uniqid());
            $paymentEfpsOrder->notifyUrl            = $notifyUrl;

            if(!$paymentEfpsOrder->save()){
                throw new \Exception($this->responseErrorMsg($paymentEfpsOrder));
            }
            $res = \Yii::$app->efps->payAliJSAPIPayment([
                "outTradeNo"   => $paymentEfpsOrder->outTradeNo,
                "customerCode" => $paymentEfpsOrder->customerCode,
                "payAmount"    => $paymentEfpsOrder->payAmount,
                "notifyUrl"    => $paymentEfpsOrder->notifyUrl,
                "orderInfo"    => json_decode($paymentEfpsOrder->orderInfo, true)
            ]);
            if($res['code'] != Efps::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'msg'   => '请求成功',
                'data'  => $res['data']
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}