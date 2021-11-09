<?php

namespace app\plugins\giftpacks\forms\common;


use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderSubmitForm;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPackItem;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\forms\common\price_log\PriceLogNewGiftpacksOrderItemForm;
use app\plugins\sign_in\forms\BaseModel;

class GiftpacksGroupPaidProcessForm extends BaseModel{

    private $finished = 0;
    private $pay_order = null;
    private $order_id = 0;

    public $pay_type; //支付方式：money、integral
    public $pay_price;
    public $integral_deduction_price;
    public $integral_fee_rate;

    public function rules(){
        return [
            [['pay_type'], 'required'],
            [['pay_price', 'integral_deduction_price', 'integral_fee_rate'], 'number']
        ];
    }

    /**
     * 支付前判断
     * @param Giftpacks $giftpacks
     * @param GiftpacksGroup $group
     * @throws \Exception
     */
    public function checkBefore(Giftpacks $giftpacks, GiftpacksGroup $group){

        //检查是否可以下单
        GiftpacksOrderSubmitForm::check($giftpacks);

        //是否支持拼单功能
        if(!$giftpacks->group_enable){
            throw new \Exception("不支持拼单功能");
        }

        //拼单信息判断
        if(!in_array($group->status, ["closed", "sharing"]) || $group->expired_at < time()){
            throw new \Exception("拼单已结束或已过期");
        }

        if($group->need_num <= $group->user_num){
            throw new \Exception("拼单已达到最大人数");
        }
    }

    /**
     * 支付完成操作
     * @param User $user
     * @param Giftpacks $giftpacks
     * @param GiftpacksGroup $group
     * @param GiftpacksGroupPayOrder $payOrder
     * @throws \Exception
     */
    public function doProcess(User $user, Giftpacks $giftpacks, GiftpacksGroup $group, $payOrder = null){

        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        //支付信息判断
        if(!$payOrder){
            $payOrder = GiftpacksGroupPayOrder::findOne([
                "mall_id"    => $giftpacks->mall_id,
                "user_id"    => $user->id,
                "group_id"   => $group->id
            ]);
            if(!$payOrder){
                throw new \Exception("无法获取到支付记录信息");
            }
        }

        if($payOrder->pay_status != "unpaid"){
            throw new \Exception("请勿重复支付操作");
        }

        //保存支付记录信息
        $payOrder->pay_status               = "paid";
        $payOrder->pay_at                   = date("Y-m-d H:i:s", time());
        $payOrder->pay_type                 = $this->pay_type;
        $payOrder->pay_price                = $this->pay_price;
        $payOrder->integral_deduction_price = $this->integral_deduction_price;
        $payOrder->integral_fee_rate        = $this->integral_fee_rate;
        if(!$payOrder->save()){
            throw new \Exception($this->responseErrorMsg($payOrder));
        }

        //保存拼单信息
        $group->status     = "sharing";
        $group->updated_at = time();
        $group->user_num   = $group->user_num + 1; //人数加1
        if(!$group->save()){
            throw new \Exception($this->responseErrorMsg($group));
        }

        //为用户生成拼单大礼包物品项记录
        $query = GiftpacksDetailForm::availableItemsQuery($giftpacks);
        $selects = ["gpi.id as pack_item_id"];
        $items = $query->asArray()->select($selects)->all();
        foreach($items as $item){
            $groupPackItem = new GiftpacksGroupPackItem([
                'mall_id'      => $payOrder->mall_id,
                'group_id'     => $group->id,
                'pack_item_id' => $item['pack_item_id'],
                'user_id'      => $user->id
            ]);
            if(!$groupPackItem->save()){
                throw new \Exception($this->responseErrorMsg($groupPackItem));
            }
        }

        $finished = 0;
        $userOrderIds = [];

        //如果拼单人数已满
        if($group->need_num <= $group->user_num){

            $finished = 1;

            //为所有已支付拼单记录用户生成大礼包订单
            $payOrders = GiftpacksGroupPayOrder::find()->where([
                "mall_id"    => $group->mall_id,
                "group_id"   => $group->id,
                "pay_status" => "paid"
            ])->all();
            foreach($payOrders as $payOrder){
                $orderSn = GiftpacksOrderSubmitForm::generateUniqueOrderSn();
                $order = new GiftpacksOrder([
                    "mall_id"                  => $giftpacks->mall_id,
                    "pack_id"                  => $giftpacks->id,
                    "user_id"                  => $payOrder->user_id,
                    "order_sn"                 => $orderSn,
                    "order_price"              => $giftpacks->group_price,
                    "created_at"               => time(),
                    "updated_at"               => time(),
                    "pay_status"               => "paid",
                    "pay_at"                   => $payOrder->pay_at,
                    "pay_price"                => $payOrder->pay_price,
                    "pay_type"                 => $payOrder->pay_type,
                    "integral_deduction_price" => $payOrder->integral_deduction_price,
                    "integral_fee_rate"        => $payOrder->integral_fee_rate,
                ]);
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }

                //赠送红包
                GiftpacksOrderPaidProcessForm::giveIntegral($giftpacks, $order);

                //赠送积分
                GiftpacksOrderPaidProcessForm::giveScore($giftpacks, $order);

                $userOrderIds[$payOrder->user_id] = $order->id;
            }

            //生成礼包订单项记录
            $items = GiftpacksGroupPackItem::find()->alias("ggpi")
                ->innerJoin(["gpi" => GiftpacksItem::tableName()], "gpi.id=ggpi.pack_item_id")
                ->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id")
                ->where([
                    "ggpi.group_id" => $group->id,
                    "ggpi.mall_id"  => $group->mall_id
                ])->select([
                    "s.mch_id", "gpi.store_id", "gpi.item_price",
                    "ggpi.pack_item_id", "gpi.usable_times", "gpi.expired_at", "gpi.max_stock", "ggpi.user_id"
                ])->asArray()->all();
            foreach($items as $item){

                //生成大礼包订单商品记录
                $otherData = [
                    'mch_id'     => $item['mch_id'],
                    'store_id'   => $item['store_id'],
                    'item_price' => $item['item_price']
                ];

                $orderItem = new GiftpacksOrderItem([
                    'mall_id'         => $giftpacks->mall_id,
                    'order_id'        => $userOrderIds[$item['user_id']],
                    'pack_item_id'    => $item['pack_item_id'],
                    'max_num'         => $item['usable_times'],
                    'current_num'     => $item['usable_times'],
                    'expired_at'      => $item['expired_at'],
                    'other_json_data' => json_encode($otherData)
                ]);
                if(!$orderItem->save()){
                    throw new \Exception($this->responseErrorMsg($orderItem));
                }

                //生成大礼包订单商户结算记录
                PriceLogNewGiftpacksOrderItemForm::create($orderItem);
            }

            //结算拼单任务
            $group->status     = "success";
            $group->updated_at = time();
            if(!$group->save()){
                throw new \Exception($this->responseErrorMsg($group));
            }
        }

        $this->finished  = $finished;
        $this->pay_order = $payOrder;
        $this->order_id  = isset($userOrderIds[$payOrder->user_id]) ? $userOrderIds[$payOrder->user_id] : 0;
    }

    public function getFinished(){
        return $this->finished;
    }

    public function getPayOrder(){
        return $this->pay_order;
    }

    public function getOrderId(){
        return $this->order_id;
    }
}