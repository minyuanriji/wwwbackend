<?php
namespace app\forms\api\payCenter\giftpacks;

use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

trait CommonPayOrder
{
    public $order_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['order_id'], 'required']
        ]);
    }

    /**
     * 获取大礼包订单
     * @return GiftpacksOrder
     * @throws \Exception
     */
    private function getGiftpacksOrder(){
        static $datas;
        if(!isset($datas[$this->order_id])){
            $datas[$this->order_id] = GiftpacksOrder::findOne($this->order_id);
            if(!$datas[$this->order_id] || $datas[$this->order_id]->is_delete){
                throw new \Exception("订单不存在");
            }
        }
        return $datas[$this->order_id];
    }

    /**
     * 获取大礼包
     * @return Giftpacks
     * @throws \Exception
     */
    private function getGiftpacks(){
        static $datas;
        $order = $this->getGiftpacksOrder();
        if(!isset($datas[$order->pack_id])){
            $datas[$order->pack_id] = Giftpacks::findOne($order->pack_id);
            if(!$datas[$order->pack_id] || $datas[$order->pack_id]->is_delete){
                throw new \Exception("大礼包[ID:".$order->pack_id."]不存在或已下架");
            }
        }
        return $datas[$order->pack_id];
    }
}