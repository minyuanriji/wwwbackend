<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftpacksOrderSubmitForm extends BaseModel{

    public $pack_id;

    public function rules(){
        return [
            [['pack_id'], 'required']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            static::check($giftpacks);

            $order = new GiftpacksOrder([
                "mall_id"     => \Yii::$app->mall->id,
                "pack_id"     => $this->pack_id,
                "user_id"     => \Yii::$app->user->id,
                "order_sn"    => "GP" . date("YmdHis") . rand(10, 99),
                "order_price" => $giftpacks->price,
                "created_at"  => time(),
                "updated_at"  => time(),
                "pay_status"  => "unpaid"
            ]);

            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'order_id' => $order->id
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    //检查是否可以下单支付
    public static function check(Giftpacks $giftpacks){
        $soldNum = (int)GiftpacksOrder::find()->where([
            "pack_id"    => $giftpacks->id,
            "pay_status" => "paid",
            "is_delete"  => 0
        ])->count();
        if($giftpacks->max_stock <= $soldNum){
            throw new \Exception("大礼包".$giftpacks->id."已售罄");
        }
    }
}