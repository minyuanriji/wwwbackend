<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 更新订单收货地址
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Order;
use app\validators\PhoneNumberValidator;

class OrderUpdateAddressForm extends BaseModel
{
    public $order_id;
    public $name;
    public $mobile;
    public $address;
    public $province;
    public $city;
    public $district;

    public function rules()
    {
        return [
            [['order_id', 'name', 'mobile', 'address'], 'required'],
            [['order_id'], 'integer'],
            [['mobile'], PhoneNumberValidator::className()],
            [['name', 'address', 'province', 'city', 'district'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'address' => '详细地址',
            'mobile' => '手机号',
            'name' => '收件人',
        ];
    }

    //更新收货地址
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $order = Order::findOne(['id' => $this->order_id, 'mall_id' => \Yii::$app->mall->id]);
        if (!$order) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '订单不存在，请刷新后重试',
            ];
        }

        if ($order->is_send == 0 && $order->send_type == 1) {
            $order->send_type = 0;
        }

        $districtArr = new DistrictArr();
        $order->province_id = $districtArr->getId($this->province);
        $order->name = $this->name;
        $order->mobile = $this->mobile;
        $order->address = $this->province . ' ' . $this->city . ' ' . $this->district . ' ' . $this->address;
        if ($order->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } else {
            return $this->responseErrorInfo($order);
        }
    }
}
