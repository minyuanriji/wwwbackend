<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\PlateForm as kcb_PlateForm;

class PhoneOrderPayForm extends BaseModel
{
    public $order_no;
    public $order_price;

    public function rules()
    {
        return [
            [['order_no', 'order_price'], 'required']
        ];
    }

    public function pay()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $addcredit_order = AddcreditOrder::findOne(["order_no" => $this->order_no]);
            if (!$addcredit_order) {
                throw new \Exception("订单不存在");
            }

            if ($addcredit_order->pay_status != 'unpaid') {
                throw new \Exception("订单状态错误！");
            }

            //用户
            $user = User::findOne($addcredit_order->user_id);
            if (!$user || $user->is_delete) {
                throw new \Exception("无法获取用户信息");
            }

            if ($user->static_integral < $this->order_price) {
                throw new \Exception("红包数量不足");
            }

            //扣取红包
            $res = UserIntegralForm::PhoneBillOrderPaySub($addcredit_order, $user, $addcredit_order->integral_deduction_price);
            if ($res['code'] != ApiCode::CODE_SUCCESS) {
                throw new \Exception("红包扣取失败：" . $res['msg']);
            }

            //更新订单状态为已支付
            $addcredit_order->order_status = "processing";
            $addcredit_order->pay_status = "paid";
            $addcredit_order->pay_at = time();
            if (!$addcredit_order->save()) {
                throw new \Exception($this->responseErrorMsg($addcredit_order));
            }

            //平台下单
            $plateform = AddcreditPlateforms::findOne($addcredit_order->plateform_id);
            if (!$plateform) {
                throw new \Exception("无法获取平台信息");
            }

            $plate_form = new kcb_PlateForm();
            $submit_res = $plate_form->submit($addcredit_order, $plateform);

            if (!$submit_res) {
                throw new \Exception('未知错误！');
            }
            if ($submit_res->code != ApiCode::CODE_SUCCESS) {
                throw new \Exception($submit_res->message);
            }
            $addcredit_order->plateform_request_data = $submit_res->request_data;
            $addcredit_order->plateform_response_data = $submit_res->response_content;
            if (!$addcredit_order->save()) {
                throw new \Exception($this->responseErrorMsg($addcredit_order));
            }
            $transaction->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '支付成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}