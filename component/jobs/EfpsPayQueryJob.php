<?php
namespace app\component\jobs;


use app\component\efps\Efps;
use app\core\payment\PaymentNotify;
use app\models\Mall;
use app\models\EfpsPaymentOrder;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use yii\base\Component;
use yii\queue\JobInterface;

class EfpsPayQueryJob extends Component implements JobInterface{

    public $outTradeNo;

    public function execute($queue){
        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            if(empty($this->outTradeNo)){
                $efpsOrder = EfpsPaymentOrder::find()->where([
                    "is_pay" => 0
                ])->orderBy("update_at ASC")->one();
            }else{
                $efpsOrder = EfpsPaymentOrder::find()->where([
                    "is_pay"     => 0,
                    "outTradeNo" => $this->outTradeNo
                ])->one();
            }

            if(!$efpsOrder) {
                throw new \Exception("支付记录不存在");
            }

            $res = \Yii::$app->efps->payQuery([
                "customerCode" => \Yii::$app->efps->getCustomerCode(),
                "outTradeNo"   => $efpsOrder->outTradeNo
            ]);
            if($res['code'] == Efps::CODE_SUCCESS && $res['data']['payState'] == "00"){
                $efpsOrder->is_pay = 1;
            }

            $efpsOrder->update_at = time();
            if(!$efpsOrder->save()){
                throw new \Exception($efpsOrder->getFirstErrors());
            }

            if($efpsOrder->is_pay){ //支付成功
                $paymentOrderUnion = PaymentOrderUnion::findOne($efpsOrder->payment_order_union_id);
                if(!$paymentOrderUnion){
                    throw new \Exception('订单不存在: ' . $efpsOrder->payment_order_union_id);
                }

                if (!$paymentOrderUnion->is_pay) {
                    $mall = Mall::findOne($paymentOrderUnion->mall_id);
                    if (!$mall) {
                        throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
                    }

                    \Yii::$app->setMall($mall);

                    $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);

                    if($efpsOrder->payAPI == "IF-QRcode-01"){ //支付宝
                        $paymentOrderUnion->pay_type = 4;
                    }else{
                        $paymentOrderUnion->pay_type = 1;
                    }
                    $paymentOrderUnion->is_pay = 1;

                    if (!$paymentOrderUnion->save()) {
                        throw new \Exception($paymentOrderUnion->getFirstErrors());
                    }

                    foreach ($paymentOrders as $paymentOrder) {
                        $Class = $paymentOrder->notify_class;
                        if (!class_exists($Class)) {
                            continue;
                        }
                        if($efpsOrder->payAPI == "IF-QRcode-01"){ //支付宝
                            $paymentOrder->pay_type = 4;
                        }else{
                            $paymentOrder->pay_type = 1;
                        }
                        $paymentOrder->is_pay   = 1;
                        if (!$paymentOrder->save()) {
                            throw new \Exception($paymentOrder->getFirstErrors());
                        }
                        /** @var PaymentNotify $notify */
                        $notify = new $Class();
                        try {
                            $po = new \app\core\payment\PaymentOrder([
                                'orderNo'     => $paymentOrder->order_no,
                                'amount'      => (float)$paymentOrder->amount,
                                'title'       => $paymentOrder->title,
                                'notifyClass' => $paymentOrder->notify_class,
                                'payType'     => \app\core\payment\PaymentOrder::PAY_TYPE_ALIPAY
                            ]);
                            $notify->notify($po);
                        } catch (\Exception $e) {
                            \Yii::error($e);
                        }
                    }
                }
            }
            $t->commit();
        }catch (\Exception $e) {
            $t->rollBack();
            echo $e->getMessage();
            \Yii::error("查询出现异常 File=".$e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
        }
    }

}