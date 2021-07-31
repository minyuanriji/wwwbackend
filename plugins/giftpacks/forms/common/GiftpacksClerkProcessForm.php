<?php

namespace app\plugins\giftpacks\forms\common;

use app\forms\common\CommonClerkProcessForm;
use app\models\clerk\ClerkData;
use app\models\Store;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\models\Mch;

class GiftpacksClerkProcessForm extends CommonClerkProcessForm {

    /**
     * @param ClerkData $clerkData
     * @throws \Exception
     */
    public function process(ClerkData $clerkData){

        //获取服务订单
        $orderPackItem = GiftpacksOrderItem::findOne($clerkData->source_id);
        if(!$orderPackItem){
            throw new \Exception("服务订单不存在");
        }

        if($orderPackItem->max_num > 0 && $orderPackItem->max_num <= 0){
            throw new \Exception("服务次数已用完");
        }

        if($orderPackItem->expired_at > 0 && $orderPackItem->expired_at < time()){
            throw new \Exception("服务已过期");
        }

        //获取服务内容
        $packItem = GiftpacksItem::findOne($orderPackItem->pack_item_id);
        if(!$packItem || $packItem->is_delete){
            throw new \Exception("服务信息不存在");
        }

        //判断商户权限
        $mchData = Store::find()->alias("s")
                    ->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id")
                    ->where(["m.is_delete" => 0, "s.id" => $packItem->store_id])
                    ->select(["m.user_id"])->asArray()->one();
        if(!$mchData){
            throw new \Exception("商户信息不存在");
        }

        if($mchData['user_id'] != $this->clerk_user_id){
            throw new \Exception("[ID:" . $this->clerk_user_id . "]无核销权限");
        }

        if($orderPackItem->max_num > 0){
            $orderPackItem->current_num -= 1;
            if(!$orderPackItem->save()){
                throw new \Exception($this->responseErrorMsg($orderPackItem));
            }
        }
    }

}