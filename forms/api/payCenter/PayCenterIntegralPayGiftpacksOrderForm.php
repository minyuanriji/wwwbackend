<?php

namespace app\forms\api\payCenter;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;
use app\plugins\giftpacks\forms\api\GiftpacksOrderSubmitForm;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\forms\common\price_log\PriceLogNewGiftpacksOrderItemForm;

class PayCenterIntegralPayGiftpacksOrderForm extends BaseModel{

    public $order_id;
    public $trade_pwd;

    public function rules(){
        return [
            [['order_id', 'trade_pwd'], 'required']
        ];
    }

    public function pay(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $order = GiftpacksOrder::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->pay_status != "unpaid"){
                throw new \Exception("订单当前状态无法支付");
            }

            $giftpacks = Giftpacks::findOne($order->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包".$order->pack_id."不存在或已下架");
            }

            //检查是否可以支付
            GiftpacksOrderSubmitForm::check($giftpacks);

            if(\Yii::$app->user->id != $order->user_id){
                throw new \Exception("下单用户与支付用户不一致");
            }

            $user = User::findOne($order->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("支付用户不一致");
            }

            //验证交易密码
            if (empty($user->transaction_password) || !\Yii::$app->getSecurity()->validatePassword($this->trade_pwd, $user->transaction_password)) {
                throw new \Exception('交易密码错误');
            }

            //验证红包够不够
            $order->integral_deduction_price = GiftpacksDetailForm::integralDeductionPrice($giftpacks, $user);
            if($user->static_integral < $order->integral_deduction_price){
                throw new \Exception('红包不足');
            }

            //红包扣取
            $res = UserIntegralForm::giftpacksOrderPaySub($order, $user, false);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            //更新订单信息
            $order->pay_status = "paid"; //已支付
            $order->updated_at = time();
            $order->pay_at     = date("Y-m-d H:i:s", time());
            $order->pay_type   = "integral";
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            //为用户生成礼包信息
            $query = GiftpacksDetailForm::availableItemsQuery($giftpacks);
            $selects = ["s.mch_id", "gpi.id", "gpi.store_id", "gpi.item_price", "gpi.usable_times", "gpi.expired_at", "gpi.max_stock"];
            $items = $query->asArray()->select($selects)->all();
            foreach($items as $item){

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
                    'expired_at'      => $item['expired_at'],
                    'other_json_data' => json_encode($otherData)
                ]);
                if(!$orderItem->save()){
                    throw new \Exception($this->responseErrorMsg($orderItem));
                }

                //生成大礼包订单商户结算记录
                PriceLogNewGiftpacksOrderItemForm::create($orderItem);
            }


            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '支付成功'
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