<?php

namespace app\plugins\mch\forms\common\price_log;

use app\models\BaseModel;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchPriceLog;

class PriceLogNewGiftpacksOrderItemForm extends BaseModel{

    public static function create(GiftpacksOrderItem $orderItem){

        try {
            $otherData = (array)@json_decode($orderItem->other_json_data);

            //获取商户信息
            $mch = Mch::findOne([
                "id"            => $otherData['mch_id'],
                "review_status" => 1,
                "is_delete"     => 0
            ]);
            if(!$mch){
                throw new \Exception("商户[ID:".$otherData['mch_id']."]信息不存在");
            }

            $uniqueData = [
                'mall_id'     => $orderItem->mall_id,
                'mch_id'      => $otherData['mch_id'],
                'store_id'    => $otherData['store_id'],
                'source_id'   => $orderItem->id,
                'source_type' => 'giftpacks_order_item'
            ];
            $priceLog = MchPriceLog::findOne($uniqueData);
            if(!$priceLog){

                //服务费比例
                $serviceFeeRate = 0;

                //计算要结算给商家的钱
                $amount = $otherData['item_price'];
                $serviceFee = ($serviceFeeRate/100) * floatval($amount);
                $price = $amount - $serviceFee;
                $otherData = [
                    'amount'             => $amount,
                    'transfer_rate'      => $mch->transfer_rate,
                    'service_fee'        => $serviceFee,
                    'giftpacks_order_id' => $orderItem->order_id,
                    'order_item_id'      => $orderItem->id
                ];

                //生成待结算记录
                $content = "来自大礼包订单[ID:".$orderItem->order_id."]的收益";
                $priceLog = new MchPriceLog(array_merge($uniqueData, [
                    "price"           => $price,
                    "created_at"      => time(),
                    "updated_at"      => time(),
                    "status"          => "unconfirmed",
                    "content"         => $content,
                    "other_json_data" => json_encode($otherData)
                ]));
                if(!$priceLog->save()){
                    throw new \Exception(json_encode($priceLog->getErrors()));
                }
            }
        }catch (\Exception $e){
            throw $e;
        }

    }

}