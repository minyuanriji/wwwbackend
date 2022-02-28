<?php


namespace app\plugins\giftpacks\forms\common;


use app\forms\common\UserIntegralModifyForm;
use app\forms\common\UserScoreModifyForm;
use app\models\BaseModel;
use app\models\Integral;
use app\models\User;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderSubmitForm;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\forms\common\price_log\PriceLogNewGiftpacksOrderItemForm;

class GiftpacksOrderPaidProcessForm extends BaseModel{

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
     * 支付前检查
     * @param User $user
     * @param Giftpacks $giftpacks
     * @param GiftpacksOrder $order
     * @throws \Exception
     */
    public function checkBefore(User $user, Giftpacks $giftpacks, GiftpacksOrder $order){

        //检查订单状态
        if($order->pay_status != "unpaid"){
            throw new \Exception("订单当前状态无法支付");
        }

        //检查是否可以下单
        GiftpacksOrderSubmitForm::check($giftpacks);

        //判断支付用户
        if($user->id != $order->user_id){
            throw new \Exception("下单用户与支付用户不一致");
        }

    }

    /**
     * 支付完成操作
     * @param Giftpacks $giftpacks
     * @param GiftpacksOrder $order
     * @throws \Exception
     */
    public function doProcess(Giftpacks $giftpacks, GiftpacksOrder $order){

        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        //更新订单信息
        $order->pay_status               = "paid"; //已支付
        $order->updated_at               = time();
        $order->pay_at                   = date("Y-m-d H:i:s", time());
        $order->pay_type                 = $this->pay_type;
        $order->pay_price                = $this->pay_price;
        $order->integral_deduction_price = $this->integral_deduction_price;
        $order->integral_fee_rate        = $this->integral_fee_rate;
        if(!$order->save()){
            throw new \Exception($this->responseErrorMsg($order));
        }

        //赠送金豆
        static::giveIntegral($giftpacks, $order);

        //赠送积分
        static::giveScore($giftpacks, $order);

        //为用户生成礼包信息
        $query = GiftpacksDetailForm::availableItemsQuery($giftpacks);
        $selects = ["s.mch_id", "gpi.id", "gpi.store_id", "gpi.item_price", "gpi.usable_times", "gpi.expired_at", "gpi.limit_time", "gpi.max_stock"];
        $items = $query->asArray()->select($selects)->all();
        foreach($items as $item){

            //去除过期时间的
            $expireTime = $item['expired_at'];
            if($expireTime > 0){
                if($item['limit_time'] > 0){
                    $endTime = strtotime(date("Y-m-d 00:00:00")) + intval($item['limit_time']) * 3600 * 24;
                    if($endTime < $expireTime){
                        $expireTime = $endTime;
                    }
                }
                if($expireTime < time()) continue;
            }

            //生成大礼包订单商品记录
            $otherData = [
                'mch_id'     => $item['mch_id'],
                'store_id'   => $item['store_id'],
                'item_price' => $item['item_price']
            ];
            $orderItem = new GiftpacksOrderItem([
                'mall_id'         => $order->mall_id,
                'order_id'        => $order->id,
                'pack_item_id'    => $item['id'],
                'max_num'         => $item['usable_times'],
                'current_num'     => $item['usable_times'],
                'expired_at'      => $expireTime,
                'other_json_data' => json_encode($otherData)
            ]);
            if(!$orderItem->save()){
                throw new \Exception($this->responseErrorMsg($orderItem));
            }

            //生成大礼包订单商户结算记录
            PriceLogNewGiftpacksOrderItemForm::create($orderItem);
        }
    }

    /**
     * 赠送金豆
     * @param Giftpacks $giftpacks
     * @param GiftpacksOrder $order
     * @throws \Exception
     */
    public static function giveIntegral(Giftpacks $giftpacks, GiftpacksOrder $order){
        if($giftpacks->integral_enable && $giftpacks->integral_give_num > 0){
            $modifyForm = new UserIntegralModifyForm([
                "type"        => 1,
                "integral"    => min($order->order_price, $giftpacks->integral_give_num),
                "is_manual"   => 0,
                "desc"        => "购买大礼包“".$giftpacks->title."”赠送金豆",
                "source_id"   => $order->id,
                "source_type" => "giftpacks_order"
            ]);
            $modifyForm->modify(User::findOne([
                "id" => $order->user_id,
                "is_delete" => 0
            ]));
        }
    }

    /**
     * 赠送积分
     * @param Giftpacks $giftpacks
     * @param GiftpacksOrder $order
     * @throws \Exception
     */
    public static function giveScore(Giftpacks $giftpacks, GiftpacksOrder $order){
        if($giftpacks->score_enable ){
            $scoreGiveSettings = !empty($giftpacks->score_give_settings) ? (array)@json_decode($giftpacks->score_give_settings) : [];
            if(isset($scoreGiveSettings['is_permanent']) && $scoreGiveSettings['is_permanent'] == 1){ //赠送永久积分
                $integralNum = isset($scoreGiveSettings['integral_num']) ? $scoreGiveSettings['integral_num'] : 0;
                if($integralNum > 0){
                    $desc = "购买大礼包“".$giftpacks->title."”赠送积分";
                    $modifyForm = new UserScoreModifyForm([
                        "type"        => 1,
                        "score"       => $integralNum,
                        "desc"        => $desc,
                        "custom_desc" => $desc,
                        "source_type" => "giftpacks_order"
                    ]);
                    $modifyForm->modify(User::findOne([
                        "id" => $order->user_id,
                        "is_delete" => 0
                    ]));
                }
            }else{ //限时积分
                $scoreSetting = [
                    "integral_num" => isset($scoreGiveSettings['integral_num']) ? $scoreGiveSettings['integral_num'] : 0,
                    "period"       => isset($scoreGiveSettings['period']) ? $scoreGiveSettings['period'] : 0,
                    "period_unit"  => isset($scoreGiveSettings['period_unit']) ? $scoreGiveSettings['period_unit'] : 'month',
                    "expire"       => isset($scoreGiveSettings['expire']) ? $scoreGiveSettings['expire'] : 0,
                    "source_type"  => "giftpacks_order",
                    "source_id"    => $order->id
                ];
                Integral::addIntegralPlan($order->user_id, $scoreSetting, '购买大礼包赠送积分券', '0');
            }
        }
    }
}