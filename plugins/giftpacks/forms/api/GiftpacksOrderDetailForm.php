<?php


namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftpacksOrderDetailForm extends BaseModel{

    public $order_id;

    public function rules(){
        return [
            [['order_id'], 'required']
        ];
    }

    public function getDetail(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $order = GiftpacksOrder::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            $giftpacks = Giftpacks::findOne($order->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'order_detail' => static::detail($order),
                    'giftpacks'    => GiftpacksDetailForm::detail($giftpacks)
                ]
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    public static function detail(GiftpacksOrder $order){
        $detail['order_id'] = $order->id;
        $detail['order_sn'] = $order->order_sn;
        $detail['order_price'] = $order->order_price;
        $detail['created_at'] = date("Y-m-d H:i:s", $order->created_at);
        $detail['pay_status'] = $order->pay_status;
        $detail['pay_price'] = $order->pay_price;
        $detail['pay_type'] = $order->pay_type;
        $detail['integral_deduction_price'] = $order->integral_deduction_price;
        return $detail;
    }
}