<?php
namespace app\mch\forms\api;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\MchCheckoutOrder;

class CheckoutOrderInfoForm extends BaseModel {

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function info(){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $checkoutOrder = MchCheckoutOrder::findOne($this->id);
        if(!$checkoutOrder || $checkoutOrder->is_delete){
            throw new \Exception('结账单不存在');
        }

        $mchModel = $checkoutOrder->mch;
        $storeModel = $mchModel->store;

        //用户可使用抵扣卷
        $integralFeeRate = $mchModel->integral_fee_rate;
        $userIntegral = User::getCanUseIntegral(\Yii::$app->user->id);
        $integralMaxDeduction = min($checkoutOrder->order_price, $userIntegral);
        if(($integralMaxDeduction + $integralMaxDeduction * ($integralFeeRate/100)) > $userIntegral){
            $integralMaxDeduction = $userIntegral/(1+($integralFeeRate/100));
        }
        $integralServiceFee = $integralMaxDeduction * ($integralFeeRate/100);


        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"", [
            'order_info'             => ArrayHelper::toArray($checkoutOrder),
            'mch_info'               => ArrayHelper::toArray($storeModel),
            'user_integral_num'      => (float)$userIntegral,
            'integral_max_deduction' => round((float)$integralMaxDeduction, 2),
            'integral_service_fee'   => round((float)$integralServiceFee, 2)
        ]);
    }
}