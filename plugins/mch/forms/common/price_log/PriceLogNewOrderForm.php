<?php

namespace app\plugins\mch\forms\common\price_log;

use app\models\BaseModel;
use app\models\Order;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchPriceLog;

class PriceLogNewOrderForm extends BaseModel{

    public static function create(Order $order){

        try {
            //获取商户信息
            $mch = Mch::findOne([
                "id"            => $order->mch_id,
                "review_status" => 1,
                "is_delete"     => 0
            ]);
            if(!$mch){
                throw new \Exception("商户信息不存在");
            }

            //获取门店
            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store || $store->is_delete){
                throw new \Exception("无法获取门店信息");
            }

            //服务费比例
            $serviceFeeRate = max(0, min(100, (int)$mch->transfer_rate));

            $details = $order->detail;
            foreach($details as $detail){

                //计算要结算给商家的钱
                $amount = $detail->total_original_price;
                $serviceFee = ($serviceFeeRate/100) * floatval($amount);
                $price = $amount - $serviceFee;

                //生成待结算记录
                $uniqueData = [
                    'mall_id'     => $order->mall_id,
                    'mch_id'      => $mch->id,
                    'store_id'    => $store->id,
                    'source_id'   => $detail->id,
                    'source_type' => "order_detail",
                ];
                $priceLog = MchPriceLog::findOne($uniqueData);
                $content = "来自商品订单[ID:".$order->id."]的收入";
                $otherData = [
                    'amount'          => $amount,
                    'transfer_rate'   => $mch->transfer_rate,
                    'service_fee'     => $serviceFee,
                    'order_id'        => $order->id,
                    'order_detail_id' => $detail->id
                ];
                if(!$priceLog){
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
            }
        }catch (\Exception $e){

        }
    }

}