<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrderRefund;

class PhoneOrderRefundForm extends BaseModel
{

    public $mall_id;
    public $order_id;
    public $created_at;
    public $reason;
    public $refund_price;

    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'created_at', 'refund_integral'], 'required'],
        ];
    }

    public function save($mall_id, $order_id, $refund_integral)
    {
        try {
            $AddcreditOrderRefundModel = new AddcreditOrderRefund();
            $AddcreditOrderRefundModel->mall_id = $mall_id;
            $AddcreditOrderRefundModel->order_id = $order_id;
            $AddcreditOrderRefundModel->created_at = time();
            $AddcreditOrderRefundModel->reason = '平台退款';
            $AddcreditOrderRefundModel->refund_integral = $refund_integral;
            $AddcreditOrderRefundModel->refund_price = 0;
            if (!$AddcreditOrderRefundModel->save()) {
                throw new \Exception((new BaseModel())->responseErrorInfo($AddcreditOrderRefundModel), ApiCode::CODE_FAIL);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'date' => $AddcreditOrderRefundModel,
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}