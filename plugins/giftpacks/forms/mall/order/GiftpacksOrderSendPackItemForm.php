<?php

namespace app\plugins\giftpacks\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksOrderSendPackItemForm extends BaseModel{

    public $pack_id;
    public $pack_item_id;
    public $order_ids;

    public function rules(){
        return [
            [['pack_id', 'pack_item_id', 'order_ids'], 'required']
        ];
    }

    public function send(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $giftpackItem = GiftpacksItem::findOne($this->pack_item_id);
            if(!$giftpackItem || $giftpackItem->is_delete){
                throw new \Exception("礼包商品不存在");
            }

            //计算剩余库存
            $orderItemTotalNum = (int)GiftpacksOrderItem::find()->where([
                "pack_item_id" => $giftpackItem->id
            ])->count();
            $stock = $giftpackItem->max_stock - $orderItemTotalNum;

            foreach($this->order_ids as $orderId){

                if($stock <= 0){
                    throw new \Exception("库存不足！无法操作");
                }

                $giftpackOrder = GiftpacksOrder::findOne($orderId);
                if(!$giftpackOrder || $giftpackOrder->pay_status != "paid")
                    continue;

                $giftpackOrderItem = GiftpacksOrderItem::findOne([
                    "order_id"     => $giftpackOrder->id,
                    "pack_item_id" => $this->pack_item_id
                ]);
                if(!$giftpackOrderItem){
                    $otherData = [
                        'store_id'   => $giftpackItem->store_id,
                        'item_price' => $giftpackItem->item_price
                    ];
                    $giftpackOrderItem = new GiftpacksOrderItem([
                        "mall_id"         => $giftpackOrder->mall_id,
                        "order_id"        => $giftpackOrder->id,
                        "pack_item_id"    => $giftpackItem->id,
                        'max_num'         => $giftpackItem->usable_times,
                        'current_num'     => $giftpackItem->usable_times,
                        'expired_at'      => $giftpackItem->expired_at,
                        'other_json_data' => json_encode($otherData)
                    ]);
                    if(!$giftpackOrderItem->save()){
                        throw new \Exception($this->responseErrorMsg($giftpackOrderItem));
                    }
                    $stock--;
                }
            }

            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '发放成功',
                [
                    'stock' => $stock
                ]
            );
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}