<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\PaymentPrepare;
use app\plugins\smart_shop\models\StorePayOrder;

class AccountRechargeSubmitForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $recharge_money;

    public function rules(){
        return [
            [['merchant_id', 'store_id', 'recharge_money'], 'required'],
            [['recharge_money'], 'number']
        ];
    }

    public function submit(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if($this->recharge_money <= 0){
                throw new \Exception("充值金额不能小于0");
            }

            $payOrder = new StorePayOrder([
                "mall_id"        => \Yii::$app->mall->id,
                "ss_mch_id"      => $this->merchant_id,
                "ss_store_id"    => $this->store_id,
                "business_scene" => "shopping_voucher",
                "created_at"     => time(),
                "updated_at"     => time(),
                "order_no"       => "SSPO" . date("YmdHis") . rand(1000, 9999),
                "order_status"   => "unpaid",
                "order_price"    => $this->recharge_money,
                "pay_status"     => "unpaid",
                "pay_type"       => "wechat",
                "pay_price"      => 0,
                "pay_time"       => 0,
                "pay_uid"        => 0
            ]);
            if(!$payOrder->save()){
                throw new \Exception($this->responseErrorMsg($payOrder));
            }

            $paymentPrepare = new PaymentPrepare([
                "mall_id"       => \Yii::$app->mall->id,
                "created_at"    => time(),
                "source_table"  => "store_pay_order",
                "prepare_class" => AccountRechargeOrderPayPrepareForm::class,
                "token"         => "",
                "order_id"      => $payOrder->id
            ]);
            if(!$paymentPrepare->save()){
                throw new \Exception($this->responseErrorMsg($paymentPrepare));
            }

            $dir = 'smartshop/pay/store_pay_' . $this->store_id . '.jpg';

            $path = "/h5/#/pay/pay?source_table=store_pay_order&orderId=" . $payOrder->id;
            $file = CommonLogic::createQrcode(null, $this, $path, $dir);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "order_no"    => $payOrder->order_no,
                "order_id"    => $payOrder->id,
                "order_price" => $payOrder->order_price,
                "qrcode"      => base64_encode(file_get_contents($file))
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}