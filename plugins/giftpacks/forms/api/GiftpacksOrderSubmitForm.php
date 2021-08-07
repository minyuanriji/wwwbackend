<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
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
                "mall_id"       => \Yii::$app->mall->id,
                "pack_id"       => $this->pack_id,
                "user_id"       => \Yii::$app->user->id,
                "order_sn"      => static::generateUniqueOrderSn(),
                "order_price"   => $giftpacks->price,
                "created_at"    => time(),
                "updated_at"    => time(),
                "pay_status"    => "unpaid",
                "process_class" => "app\\plugins\\giftpacks\\forms\\common\\GiftpacksOrderPaidProcessForm"
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

        $soldNum = GiftpacksDetailForm::soldNum($giftpacks);
        if($giftpacks->max_stock <= $soldNum){
            throw new \Exception("大礼包“".$giftpacks->title."”已售罄");
        }

        //限购判断
        if($giftpacks->purchase_limits_num > 0){
            $orderNum = (int)GiftpacksGroupPayOrder::find()->alias("ggpo")
                            ->innerJoin(["gg" => GiftpacksGroup::tableName()], "ggpo.group_id=gg.id")
                            ->where([
                                "ggpo.user_id"    => \Yii::$app->user->id,
                                "ggpo.pay_status" => "paid",
                                "gg.pack_id"      => $giftpacks->id
                            ])->count();
            $orderNum += (int)GiftpacksOrder::find()->where([
                "user_id"    => \Yii::$app->user->id,
                "pay_status" => "paid",
                "is_delete"  => 0,
                "pack_id"    => $giftpacks->id
            ])->count();
            if($orderNum >= $giftpacks->purchase_limits_num){
                throw new \Exception("每个用户限制购买".$giftpacks->purchase_limits_num."件");
            }
        }
    }

    //生成唯一订单号
    public static function generateUniqueOrderSn(){
        $orderSn = null;
        while(true){
            $orderSn = "GP" . date("YmdHis") . rand(10, 99);
            $exists = GiftpacksOrder::find()->where([
                "order_sn" => $orderSn
            ])->exists();
            if(!$exists){
                break;
            }
        }
        return $orderSn;
    }
}