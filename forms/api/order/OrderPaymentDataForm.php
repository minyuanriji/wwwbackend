<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-不同支付方式数据
 * Author: zal
 * Date: 2020-05-14
 * Time: 11:50
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\core\payment\Payment;
use app\forms\common\order\OrderCommon;
use app\models\BaseModel;

class OrderPaymentDataForm extends BaseModel
{
    public $pay_type;
    public $union_id;
    public $pay_price = 0;
    public $transaction_password = "";
    public $openid = "";

    public function rules()
    {
        return [
            [['pay_type',"transaction_password"],'string'],
            [['union_id'], 'integer'],
            [['pay_price'], 'number'],
            [['openid'],'string']
        ];
    }


    /**
     * 获取支付平台参数数据
     * @return array
     * @throws \app\core\payment\PaymentException
     * @throws \yii\db\Exception
     */
    public function getPaymentData()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"",$this);
        }


//        if(empty(\Yii::$app->user->identity->mobile)){
//            return $this->returnApiResultData(ApiCode::CODE_FAIL,"请先绑定手机号");
//        }
        if (!OrderCommon::checkIsBindMobile()) {
            return $this->returnApiResultData(ApiCode::CODE_BIND_MOBILE, '请先绑定手机');
        }
        $payment = new Payment();

        $payment_order_union_id = !empty($this->union_id) ? $this->union_id : 0;

        $message = "";
        $code = ApiCode::CODE_SUCCESS;
        $payResult = $payment->getPayData($payment_order_union_id, $this->pay_type,$this->pay_price);
        if(!is_array($payResult)){
            $message = $payResult;
            $code = ApiCode::CODE_FAIL;
            if($payResult == false){
                $code = ApiCode::CODE_USER_NOT_AUTH;
                $message = "用户没有授权";
            }
            $payResult = [];
        }else{
            if($this->pay_type == Payment::PAY_TYPE_BALANCE){
                $message = $payment->payToBalance($payment_order_union_id,$this->transaction_password);
                if($message != "success"){
                    $code = ApiCode::CODE_FAIL;
                }else{
                    $message = "余额支付成功";
                }
                $payResult = [];
            }
        }
        return $this->returnApiResultData($code,$message,$payResult);
    }
}
