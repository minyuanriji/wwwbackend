<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\core\payment\PaymentOrder;
use app\helpers\ArrayHelper;
use app\logic\AppConfigLogic;
use app\logic\OrderLogic;
use app\mch\events\CheckoutOrderPaidEvent;
use app\mch\payment\CheckoutOrderPayNotify;
use app\models\BaseModel;
use app\models\Order;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class CheckoutOrderPayForm extends BaseModel {

    public $id;
    public $use_integral;
    public $use_score;
    public $order_price;

    public function rules(){
        return [
            [['id'], 'integer'],
            [['use_integral', 'order_price'], 'number', 'min' => 0]
        ];
    }

    public function attributeLabels(){
        return [
            'id'           => '商户ID',
            'use_integral' => '使用抵扣券',
            'order_price'  => '付款金额'
        ];
    }

    /**
     * 生成结账单
     * @return object
     */
    public function create(){

        try {
            //获取商户信息
            $mchModel = Mch::findOne([
                'id'            => $this->id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ]);
            if(!$mchModel){
                throw new \Exception('商户信息不存在');
            }

            //获取未支付的
            $checkoutOrder = MchCheckoutOrder::find()->where([
                'is_pay'      => 0,
                'mall_id'     => \Yii::$app->mall->id,
                'mch_id'      => $mchModel->id,
                'pay_user_id' => \Yii::$app->user->id
            ])->one();

            if(!$checkoutOrder){
                $checkoutOrder = new MchCheckoutOrder();
                $checkoutOrder->mall_id     = \Yii::$app->mall->id;
                $checkoutOrder->mch_id      = $mchModel->id;
                $checkoutOrder->order_no    = Order::getOrderNo('MS');
                $checkoutOrder->pay_user_id = \Yii::$app->user->id;
            }

            $checkoutOrder->order_price              = $this->order_price;
            $checkoutOrder->pay_price                = 0;
            $checkoutOrder->is_pay                   = 0;
            $checkoutOrder->pay_at                   = 0;
            $checkoutOrder->score_deduction_price    = 0;
            $checkoutOrder->integral_deduction_price = 0;
            $checkoutOrder->created_at               = time();
            $checkoutOrder->updated_at               = time();
            $checkoutOrder->is_delete                = 0;
            if (!$checkoutOrder->save()) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,(new BaseModel())->responseErrorMsg($checkoutOrder));
            }

            $detail = ArrayHelper::toArray($checkoutOrder);
            $detail['format_date'] = date("Y-m-d H:i:s", $detail['updated_at']);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"", $detail);

        }catch (\Exception $e){
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

    public function pay(){

        try {

            $checkoutOrder = MchCheckoutOrder::findOne($this->id);
            if(!$checkoutOrder){
                throw new \Exception('无法获取到结账单');
            }
            if($checkoutOrder->is_pay){
                throw new \Exception('结账单已支付成功');
            }

            $mch = $checkoutOrder->mch;
            if(!$mch){
                throw new \Exception('无法获取到商家信息');
            }

            //购物卷抵扣
            $integralFeeRate = $mch->integral_fee_rate;
            $userIntegral = User::getCanUseIntegral(\Yii::$app->user->id);
            $integralDeductionPrice = min($userIntegral, min($checkoutOrder->order_price, $this->use_integral));
            if(($integralDeductionPrice + intval($integralDeductionPrice * ($integralFeeRate/100))) > $userIntegral){
                $integralDeductionPrice = $userIntegral/(1+($integralFeeRate/100));
            }
            $integralServiceFee = intval($integralDeductionPrice * ($integralFeeRate/100));

            $payPrice = max(0, $checkoutOrder->order_price - $integralDeductionPrice);

            $checkoutOrder->updated_at               = time();
            $checkoutOrder->integral_deduction_price = (int)$integralDeductionPrice + $integralServiceFee;
            $checkoutOrder->integral_fee_rate        = $integralFeeRate;

            if(!$checkoutOrder->save()){
                throw new \Exception('结账单更新失败');
            }

            $supportPayTypes = OrderLogic::getPaymentTypeConfig();
            $union_id = 0;

            if($payPrice <= 0){ //通过抵扣卷支付成功
                $event = new CheckoutOrderPaidEvent();
                $event->checkoutOrder = $checkoutOrder;
                $event->amount        = 0;
                $event->sender        = $this;
                \Yii::$app->trigger(MchCheckoutOrder::EVENT_PAYED, $event);
            }else{
                $paymentOrder = new PaymentOrder([
                    'title'             => "Checkout for mch id:" . $mch->id,
                    'amount'            => (float)$payPrice,
                    'orderNo'           => $checkoutOrder->order_no,
                    'notifyClass'       => CheckoutOrderPayNotify::class,
                    'supportPayTypes'   => $supportPayTypes,
                ]);

                $union_id = \Yii::$app->payment->createOrder([$paymentOrder]);
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
            $data['detail']              = ArrayHelper::toArray($checkoutOrder);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"", $data);

        }catch(\Exception $e){
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

}