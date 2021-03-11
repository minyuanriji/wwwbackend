<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\core\payment\PaymentOrder;
use app\forms\api\order\OrderPayNotify;
use app\logic\AppConfigLogic;
use app\logic\OrderLogic;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\MchCheckoutOrder;

class CheckoutOrderPayForm extends BaseModel {

    public $id;
    public $use_integral;
    public $use_score;

    public function rules(){
        return [
            [['id', 'use_integral'], 'required'],
            [['use_integral'], 'number']
        ];
    }


    public function pay(){

        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $checkoutOrder = MchCheckoutOrder::findOne($this->id);
        try {

            if(!$checkoutOrder){
                throw new \Exception('结账单不存在');
            }

            if($checkoutOrder->is_pay){
                throw new \Exception('账单已支付');
            }

            $mch = $checkoutOrder->mch;

            //购物卷抵扣
            $userIntegral = User::getCanUseIntegral(\Yii::$app->user->id);
            $integralDeductionPrice = min($userIntegral, min($checkoutOrder->order_price, $this->use_integral));
            $payPrice = max(0, $checkoutOrder->order_price - $integralDeductionPrice);

            //更新结账单
            $checkoutOrder->is_pay                   = $payPrice <= 0 ? 1 : 0;
            $checkoutOrder->pay_user_id              = \Yii::$app->user->id;
            $checkoutOrder->updated_at               = time();
            $checkoutOrder->integral_deduction_price = $integralDeductionPrice;
            if(!$checkoutOrder->save()){

            }

            $supportPayTypes = OrderLogic::getPaymentTypeConfig();
            $union_id = 0;
            if($payPrice > 0){ //如果仍有待需要支付的金额
                $paymentOrder = new PaymentOrder([
                    'title'             => "Checkout for mch id:" . $mch->id,
                    'amount'            => (float)$payPrice,
                    'orderNo'           => $checkoutOrder->order_no,
                    'notifyClass'       => OrderPayNotify::class,
                    'supportPayTypes'   => $supportPayTypes,
                ]);

                $id = \Yii::$app->payment->createOrder([$paymentOrder]);
            }

            $userModel = new User();
            $user = $userModel->findIdentity(\Yii::$app->user->id);
            $data = [
                'balance'         => $user->balance,
                'amount'          => $payPrice,
                'orderNo'         => $checkoutOrder->order_no,
                'supportPayTypes' => $supportPayTypes,
            ];

            $paymentConfigs = AppConfigLogic::getPaymentConfig();
            $data["pay_password_status"] = isset($paymentConfigs["pay_password_status"]) ? $paymentConfigs["pay_password_status"] : 0;
            $isPayPassword = empty($userData["transaction_password"]) ? 0 : 1;
            $data["is_pay_password"]     = $isPayPassword;
            $data["union_id"]            = $union_id;

        }catch(\Exception $e){
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

}