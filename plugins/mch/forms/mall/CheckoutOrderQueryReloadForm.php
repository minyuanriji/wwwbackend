<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\core\payment\PaymentNotify;
use app\models\BaseModel;
use app\models\PaymentOrder;
use app\plugins\mch\controllers\mall\CheckoutOrderDetailForm;

class CheckoutOrderQueryReloadForm extends BaseModel{
    public $id;

    public function rules(){
        return array_merge(parent::rules(), [
            [["id"], "integer"]
        ]);
    }

    public function execute(){
        try {
            $form = new CheckoutOrderDetailForm();
            $form->id = $this->id;
            $res = $form->getDetail();

            $detail = $res['data']['detail'];
            if(!$detail['is_pay']){
                $paymentOrder = PaymentOrder::findOne([
                    "order_no" => $detail['order_no']
                ]);
                if($paymentOrder){
                    $paymentOrderUnion = $paymentOrder->paymentOrderUnion;
                    if($paymentOrderUnion /*&& $paymentOrderUnion->pay_type == 1*/){
                        $app = \Yii::$app->wechat;
                        $orderNo = $paymentOrderUnion->order_no;
                        $res = $app->payment->order->queryByOutTradeNumber($orderNo);
                        if(isset($res['trade_state']) && $res['trade_state'] =="SUCCESS"){
                            $paymentOrderUnion->is_pay = 1;
                            $paymentOrderUnion->pay_type = 1;
                            if (!$paymentOrderUnion->save()) {
                                \Yii::error("pay_notify ".$paymentOrderUnion->getFirstErrors());
                                throw new \Exception($paymentOrderUnion->getFirstErrors());
                            }
                            foreach ([$paymentOrder] as $paymentOrder) {
                                $Class = $paymentOrder->notify_class;
                                if (!class_exists($Class)) {
                                    continue;
                                }
                                $paymentOrder->is_pay = 1;
                                $paymentOrder->pay_type = 1;
                                if (!$paymentOrder->save()) {
                                    throw new \Exception($paymentOrder->getFirstErrors());
                                }
                                /** @var PaymentNotify $notify */
                                $notify = new $Class();
                                try {
                                    $po = new \app\core\payment\PaymentOrder([
                                        'orderNo' => $paymentOrder->order_no,
                                        'amount' => (float)$paymentOrder->amount,
                                        'title' => $paymentOrder->title,
                                        'notifyClass' => $paymentOrder->notify_class,
                                        'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_WECHAT
                                    ]);
                                    $notify->notify($po);

                                    $detail = $form->getDetail();

                                } catch (\Exception $e) {
                                   throw new \Exception("支付订单更新失败 ".$e->getMessage());
                                }
                            }

                        }
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }

    }
}