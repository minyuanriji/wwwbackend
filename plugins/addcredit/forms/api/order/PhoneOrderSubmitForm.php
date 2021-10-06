<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\qyj_sdk\PlateForm;
use app\plugins\sign_in\models\User;

class PhoneOrderSubmitForm extends BaseModel
{

    public $plateform_id;
    public $mobile;
    public $order_price;
    public $integral_deduction_price;
    public $gid;
    public $specId;

    public function rules(){
        return [
            [['plateform_id', 'mobile', 'order_price', 'integral_deduction_price', 'gid', 'specId'], 'required'],
        ];
    }

    public function save()
    {
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            $plate = AddcreditPlateforms::findOne($this->plateform_id);
            if (!$plate) {
                throw new \Exception('平台不存在！');
            }

            $mobile = $this->validatePhone($this->mobile);
            if (!$mobile) {
                throw new \Exception('手机号码错误,请重新输入！');
            }

            $user = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
            if (!$user) {
                throw new \Exception('账户不存在！');
            }

            if ($this->order_price > $user->static_integral) {
                throw new \Exception('账户红包不足！');
            }

            //生成订单
            $order = new AddcreditOrder([
                "mall_id"                   => \Yii::$app->mall->id,
                "plateform_id"              => $this->plateform_id,
                "user_id"                   => \Yii::$app->user->id,
                "mobile"                    => $this->mobile,
                "order_no"                  => "HF" . $this->plateform_id . date("ymdHis") . rand(100, 999),
                "order_price"               => $this->order_price,
                "created_at"                => time(),
                "updated_at"                => time(),
                "integral_deduction_price"  => $this->integral_deduction_price,
                "order_status"              => 'unpaid',
                "pay_status"                => 'unpaid',
            ]);
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            $PlateForm = new PlateForm();
            $jsonParam = json_decode($plate->json_param, true);
            $params['orderInfo'] = [
                'appId' => $jsonParam['id'],
                'gid' => $this->gid,
                'outOrderNum' => $order->order_no,
                'rechargeAccount' => $this->mobile,
                'sign' => strtolower(md5($jsonParam['id'] . "!@#" . $jsonParam['secret_key'])),
                'specId' => $this->specId,
                'app_key' => $jsonParam['secret_key'],
            ];
            $createOrder = $PlateForm->getCreateOrder($params);
            if ($createOrder->code == ApiCode::CODE_FAIL) {
                throw new \Exception($createOrder->message);
            }
            $order->plateform_request_data = $createOrder->request_data;
            $order->plateform_response_data = $createOrder->response_content;
            $order->qyj_order_num = $createOrder->response_content->data;
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data'  => [
                    "order_id"    => $order->id,
                    "order_no"    => $order->order_no,
                    "qyj_order_num"=> $createOrder->response_content->data,
                    "order_price" => round($order->order_price, 2)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /* *
     * 验证手机号是否正确
     *
     * */
    private function validatePhone ($mobile)
    {
        if (preg_match("/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/", $mobile)) {
            return true;
        } else {
            return false;
        }
    }

}