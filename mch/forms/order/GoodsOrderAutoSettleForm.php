<?php
namespace app\mch\forms\order;

use app\models\Order;

class GoodsOrderAutoSettleForm extends MchAutoSettleForm {

    /**
     * 多商户订单商品结算
     * @param Order $order
     */
    public static function settle(Order $order){
        $settleForm = new GoodsOrderAutoSettleForm([
            "price"  => $order->total_goods_original_price,
            "mch_id" => $order->mch_id,
            "desc"   => "商品订单（".$order->id."）结算"
        ]);
        return $settleForm->save();
    }
}