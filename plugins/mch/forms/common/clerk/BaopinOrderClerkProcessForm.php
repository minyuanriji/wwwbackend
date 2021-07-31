<?php

namespace app\plugins\mch\forms\common\clerk;

use app\forms\common\CommonClerkProcessForm;
use app\models\clerk\ClerkData;
use app\models\Order;
use app\models\Store;
use app\plugins\baopin\models\BaopinMchClerkOrder;
use app\plugins\baopin\models\BaopinMchGoods;
use app\plugins\mch\models\Mch;

class BaopinOrderClerkProcessForm extends CommonClerkProcessForm
{

    /**
     * 核销处理
     * @param ClerkData $clerkData
     * @throws \Exception
     */
    public function process(ClerkData $clerkData){

        $order = Order::find()->with(['detail.goods.goodsWarehouse'])->where([
            "id" => $clerkData->source_id
        ])->one();
        if (!$order) {
            throw new \Exception('订单不存在');
        }

        //检查订单
        OrderClerkProcessForm::checkOrder($order);

        //获取订单所属商户
        $mch = Mch::findOne($order->mch_id);
        if(!$mch || $mch->is_delete){
            throw new \Exception("商户[ID:".$order->mch_id."]不存在");
        }

        //获取门店
        $store = Store::findOne(["mch_id" => $mch->id]);
        if(!$store || $store->is_delete){
            throw new \Exception("无法获取门店信息");
        }

        //检查是否有爆品的核销权限
        $details = $order->detail;
        if(!$details){
            throw new \Exception('数据异常，无法获取到详情记录');
        }
        $hasPermission = true;
        foreach($details as $detail){
            //查找商家爆品库
            $baopinMchGoods = BaopinMchGoods::findOne([
                "mch_id"    => $mch->id,
                "store_id"  => $store->id,
                "goods_id"  => $detail->goods_id,
                "is_delete" => 0
            ]);
            if(!$baopinMchGoods){
                $hasPermission = false;
                break;
            }
            //判断是否有库存
            if($baopinMchGoods->stock_num < $detail->num){
                $hasPermission = false;
                break;
            }
            $baopinMchGoodsList[] = [$detail->num, $baopinMchGoods];
        }
        if (!$hasPermission) {
            throw new \Exception("[ID:".$mch->id."]商户无爆品核销权限");
        }

        //更新商户爆品库
        foreach($baopinMchGoodsList as list($num, $item)){
            $baopinMchClerkOrder = new BaopinMchClerkOrder([
                "mall_id"    => $item->mall_id,
                "order_id"   => $order->id,
                "goods_id"   => $item->goods_id,
                "created_at" => time(),
                "updated_at" => time(),
                "mch_id"     => $item->mch_id,
                "store_id"   => $item->store_id
            ]);
            if(!$baopinMchClerkOrder->save()){
                throw new \Exception($this->responseErrorMsg($baopinMchClerkOrder));
            }

            $item->stock_num -= intval($num);
            $item->updated_at = time();
            if(!$item->save()){
                throw new \Exception($this->responseErrorMsg($item));
            }
        }

        //核销订单
        OrderClerkProcessForm::clerkOrder($this->clerk_user_id, $store, $order);
    }
}