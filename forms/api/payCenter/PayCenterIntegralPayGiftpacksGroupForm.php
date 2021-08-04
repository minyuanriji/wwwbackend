<?php

namespace app\forms\api\payCenter;


use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
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

class PayCenterIntegralPayGiftpacksGroupForm extends BaseModel{

    public $group_id;
    public $trade_pwd;

    public function rules(){
        return [
            [['group_id', 'trade_pwd'], 'required']
        ];
    }

    public function pay(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //获取拼单信息
            $group = GiftpacksGroup::findOne($this->group_id);
            if(!$group){
                throw new \Exception("拼单信息不存在");
            }

            //用户信息判断
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息");
            }

            //验证交易密码
            if (empty($user->transaction_password) || !\Yii::$app->getSecurity()->validatePassword($this->trade_pwd, $user->transaction_password)) {
                throw new \Exception('交易密码错误');
            }

            //大礼包信息判断
            $giftpacks = Giftpacks::findOne($group->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            GiftpacksOrderSubmitForm::check($giftpacks);

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

            //支付信息判断
            $payOrder = GiftpacksGroupPayOrder::findOne([
                "mall_id"    => \Yii::$app->mall->id,
                "user_id"    => \Yii::$app->user->id,
                "group_id"   => $this->group_id
            ]);
            if(!$payOrder){
                throw new \Exception("无法获取到支付记录信息");
            }
            if($payOrder->pay_status != "unpaid"){
                throw new \Exception("请勿重复支付操作");
            }

            //使用红包需要的数量
            $integralDeductionPrice = GiftpacksDetailForm::groupIntegralDeductionPrice($giftpacks, $user);
            if($user->static_integral < $integralDeductionPrice){
                throw new \Exception('红包不足');
            }

            $payOrder->integral_deduction_price = $integralDeductionPrice;

            //红包扣取
            $res = UserIntegralForm::giftpacksGroupPaySub($payOrder, $user, false);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            //保存拼单信息
            $group->status     = "sharing";
            $group->updated_at = time();
            $group->user_num   = $group->user_num + 1; //人数加1
            if(!$group->save()){
                throw new \Exception($this->responseErrorMsg($group));
            }

            //保存支付记录信息
            $payOrder->pay_status = "paid";
            $payOrder->pay_at     = date("Y-m-d H:i:s", time());
            $payOrder->pay_type   = "integral";
            if(!$payOrder->save()){
                throw new \Exception($this->responseErrorMsg($payOrder));
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
                    "group_id"   => $this->group_id,
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
                    $userOrderIds[$payOrder->user_id] = $order->id;
                }

                //生成礼包订单项记录
                $items = GiftpacksGroupPackItem::find()->alias("ggpi")
                            ->innerJoin(["gpi" => GiftpacksItem::tableName()], "gpi.id=ggpi.pack_item_id")
                            ->where([
                                "ggpi.group_id" => $group->id,
                                "ggpi.mall_id"  => $group->mall_id
                            ])->select([
                                "ggpi.pack_item_id", "gpi.usable_times", "gpi.expired_at", "gpi.max_stock", "ggpi.user_id"
                            ])->asArray()->all();
                foreach($items as $item){
                    $orderItem = new GiftpacksOrderItem([
                        'mall_id'      => $giftpacks->mall_id,
                        'order_id'     => $userOrderIds[$item['user_id']],
                        'pack_item_id' => $item['pack_item_id'],
                        'max_num'      => $item['usable_times'],
                        'current_num'  => $item['usable_times'],
                        'expired_at'   => $item['expired_at']
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

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "finished" => $finished,
                    "group_id" => (int)$this->group_id,
                    "order_id" => $finished ? $userOrderIds[\Yii::$app->user->id] : 0
                ]
            ];

        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}