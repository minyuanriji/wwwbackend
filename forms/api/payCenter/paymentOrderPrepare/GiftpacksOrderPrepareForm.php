<?php

namespace app\forms\api\payCenter\paymentOrderPrepare;

use app\forms\api\payCenter\notifyProcess\EfpsGiftpacksOrderPaidNotifyProcessForm;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftpacksOrderPrepareForm extends BasePrepareForm {

    public $order_id;

    public function rules(){
        return [
            [['order_id'], 'required']
        ];
    }

    /**
     * 获取订单
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
        $giftpacksOrder = $this->getGiftpacksOrder();;
        if(!isset($datas[$giftpacksOrder->pack_id])){
            $datas[$giftpacksOrder->pack_id] = Giftpacks::findOne($giftpacksOrder->pack_id);
            if(!$datas[$giftpacksOrder->pack_id] || $datas[$giftpacksOrder->pack_id]->is_delete){
                throw new \Exception("大礼包[ID:".$giftpacksOrder->pack_id."]不存在或已下架");
            }
        }
        return $datas[$giftpacksOrder->pack_id];
    }

    /**
     * 创建前检查操作
     * @return void
     * @throws \Exception
     */
    protected function checkBefore(User $user){

        //获取订单
        $giftpacksOrder = $this->getGiftpacksOrder();

        //获取大礼包
        $giftpacks = $this->getGiftpacks();

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

        //调用支付完操作类判断是否能支付
        $processClass = $giftpacksOrder->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包订单支付完成操作类<{$processClass}>不存在");
        }
        $class = new $processClass();
        $class->checkBefore($user, $giftpacks, $giftpacksOrder);
    }

    /**
     * 订单组
     * @param User $user
     * @return array
     */
    protected function getOrderArray(User $user){
        $giftpacksOrder = $this->getGiftpacksOrder();

        $orderArray['total_amount'] = $giftpacksOrder->order_price;
        $orderArray['content'] = "大礼包订单[ID:".$this->order_id."]";
        $orderArray['notify_class'] = EfpsGiftpacksOrderPaidNotifyProcessForm::class;
        $orderArray['list'] = [
            [
                'amount'   => $giftpacksOrder->order_price,
                'title'    => "大礼包订单[ID:".$this->order_id."]支付预备单",
                'order_no' => $giftpacksOrder->order_sn
            ]
        ];

        return $orderArray;
    }
}