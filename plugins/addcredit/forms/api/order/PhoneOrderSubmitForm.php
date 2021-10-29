<?php

namespace app\plugins\addcredit\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\sign_in\models\User;

class PhoneOrderSubmitForm extends BaseModel
{
    public $plateform_id;
    public $mobile;
    public $order_price;
    public $integral_deduction_price;
    public $product_id;
    public $pay_type;

    public function rules(){
        return [
            [['plateform_id','mobile', 'order_price', 'integral_deduction_price', 'product_id'], 'required'],
            [['pay_type'], 'safe']
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

            $products = @json_decode($plate->product_json_data, true);
            $product = null;
            if($products){
                foreach($products as $item){
                    if($item['product_id'] == $this->product_id){
                        $product = $item;
                        break;
                    }
                }
            }
            if(!$product){
                throw new \Exception("产品[ID:{$this->product_id}]不存在");
            }


            //生成订单
            $order = new AddcreditOrder([
                "mall_id"       => \Yii::$app->mall->id,
                "plateform_id"  => $this->plateform_id,
                "user_id"       => \Yii::$app->user->id,
                "mobile"        => $this->mobile,
                "order_no"      => "HF" . $this->plateform_id . date("ymdHis") . rand(100, 999),
                "order_price"   => $product['price'],
                "created_at"    => time(),
                "updated_at"    => time(),
                "order_status"  => 'unpaid',
                "pay_status"    => 'unpaid',
                "product_id"    => $this->product_id,
                "recharge_type" => $product['type']
            ]);

            //红包
            if ($this->pay_type == 2) {
                $user = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
                if (!$user) {
                    throw new \Exception('账户不存在！');
                }
                if ($this->order_price > $user->static_integral) {
                    throw new \Exception('账户红包不足！');
                }
                $order->integral_deduction_price = floatval($product['price']) + floatval($product['price']) * ($plate->ratio / 100);
            } elseif ($this->pay_type == 1) {
                $order->pay_type = 'cash';
                $order->pay_price = $this->order_price;
            } else {
                throw new \Exception('支付类型不存在！');
            }

            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data'  => [
                    "order_id"    => $order->id,
                    "order_no"    => $order->order_no,
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