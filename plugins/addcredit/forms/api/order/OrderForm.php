<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\k_default\PlateForm;

class OrderForm extends BaseModel
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required']
        ];
    }

    public function OrderStatus()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = AddcreditOrder::find()
                ->where(['id' => $this->order_id])
                ->select(["id", "pay_status", "order_status"])
                ->one();

            if (!$query) {
                throw new \Exception('订单不存在！',ApiCode::CODE_FAIL);
            }
            if ($query->pay_status != 'paid') {
                throw new \Exception('订单支付状态错误！',ApiCode::CODE_FAIL);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $query->order_status,
                'msg' => '查询状态成功！'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}