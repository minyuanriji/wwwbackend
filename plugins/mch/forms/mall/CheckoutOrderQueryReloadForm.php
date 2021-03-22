<?php


namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
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
                    if($paymentOrderUnion && $paymentOrderUnion->pay_type == 1){
                        $app = \Yii::$app->wechat;
                        //$paymentOrderUnion->order_no
                        $orderNo = "JX541403f8d18214058d8da812174e02";
                        $res = $app->payment->order->queryByOutTradeNumber($orderNo);
                        print_r($res);
                        exit;
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