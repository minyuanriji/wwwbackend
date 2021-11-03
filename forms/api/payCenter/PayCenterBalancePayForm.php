<?php

namespace app\forms\api\payCenter;

use app\core\ApiCode;
use app\core\payment\PaymentOrder;
use app\forms\common\UserBalanceModifyForm;
use app\logic\AppConfigLogic;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\Order;
use app\models\PaymentOrderUnion;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\oil\models\OilOrders;

class PayCenterBalancePayForm extends BaseModel{

    public $union_id;
    public $stands_mall_id;

    public function rules(){
        return [
            [['union_id'], 'required'],
            [['union_id','stands_mall_id'], 'integer'],
            [['wx_type'], 'safe'],
        ];
    }

    /**
     * 余额支付
     * @return array
     */
    public function doPay(){

        $t = \Yii::$app->db->beginTransaction();
        try {

            if (\Yii::$app->user->isGuest) {
                throw new \Exception('用户未登录。');
            }

            $user = \Yii::$app->user->identity;
            $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $this->union_id]);
            if (!$paymentOrderUnion) {
                throw new \Exception('待支付订单不存在。');
            }

            if($paymentOrderUnion->is_pay){
                throw new \Exception('请勿重复支付');
            }

            $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
            if (!empty($supportPayTypes) && is_array($supportPayTypes) && !in_array("balance", $supportPayTypes)) {
                if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用余额支付
                    throw new \Exception('暂不支持余额支付。');
                }
            }

            $paymentConfigs = AppConfigLogic::getPaymentConfig();
            $pay_password_status = isset($paymentConfigs["pay_password_status"]) ? $paymentConfigs["pay_password_status"] : 0;
            if($pay_password_status == 1){
                if(empty($user->transaction_password)){
                    throw new \Exception('您未设置支付密码');
                }
                if(empty($transaction_password)){
                    throw new \Exception('请输入交易密码');
                }
                if (!\Yii::$app->getSecurity()->validatePassword(trim($transaction_password), $user->transaction_password)) {
                    throw new \Exception('支付密码错误');
                }
            }

            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            $totalAmount = 0;
            foreach ($paymentOrders as $paymentOrder) {
                $totalAmount += $paymentOrder->amount;
            }
            $balanceAmount = \Yii::$app->currency->setUser($user)->balance->select();
            if ($balanceAmount < $totalAmount) {
                throw new \Exception('账户余额不足。');
            }
            $paymentOrderUnion->is_pay   = 1;
            $paymentOrderUnion->pay_type = 3;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }

            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay   = 1;
                $paymentOrder->pay_type = 3;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }

                list($source_type, $source_id, $desc) = $this->getOrderSource($paymentOrder);

                $NotifyClass = $paymentOrder->notify_class;
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo'     => $paymentOrder->order_no,
                    'amount'      => (float)$paymentOrder->amount,
                    'title'       => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType'     => "balance",
                ]);
                if ($po->amount > 0) {
                    $modifyForm = new UserBalanceModifyForm([
                        "type"        => BalanceLog::TYPE_SUB,
                        "money"       => (float)$paymentOrder->amount,
                        "custom_desc" => '',
                        "source_id"   => $source_id,
                        "source_type" => $source_type,
                        "desc"        => $desc
                    ]);
                    $modifyForm->modify($user);
                }
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $e) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
                }
            }
            $t->commit();

            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'msg'   => '支付成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    /**
     * 获取来源订单信息
     * @param \app\models\PaymentOrder $paymentOrder
     * @return array
     */
    private function getOrderSource(\app\models\PaymentOrder $paymentOrder){
        if (substr($paymentOrder->order_no, 0, 2) == "MS") {
            $checkoutOrder = MchCheckoutOrder::findOne(['order_no' => $paymentOrder->order_no]);
            if(!$checkoutOrder){
                throw new \Exception("[MchCheckoutOrder]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付门店账单";
            $sourceType = "mch_checkout_order";
            $sourceId   = $checkoutOrder->id;
        } elseif(substr($paymentOrder->order_no, 0, 4) == "ALIS") {
            $order = AlibabaDistributionOrder::findOne(['order_no' => $paymentOrder->order_no]);
            if (!$order){
                throw new \Exception("[AlibabaDistributionOrder]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付1688分销订单";
            $sourceType = "1688_distribution_order";
            $sourceId   = $order->id;
        }elseif(substr($paymentOrder->order_no, 0, 2) == "HO"){
            $order = HotelOrder::findOne(['order_no' => $paymentOrder->order_no]);
            if (!$order){
                throw new \Exception("[HotelOrder]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付酒店订单";
            $sourceType = "hotel_order";
            $sourceId   = $order->id;
        }elseif(substr($paymentOrder->order_no, 0, 2) == "HF"){
            $order = AddcreditOrder::findOne(["order_no" => $paymentOrder->order_no]);
            if (!$order){
                throw new \Exception("[AddcreditOrder]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付话费订单";
            $sourceType = "addcredit_order";
            $sourceId   = $order->id;
        }elseif(substr($paymentOrder->order_no, 0, 3) == "OIL"){
            $order = OilOrders::findOne(["order_no" => $paymentOrder->order_no]);
            if (!$order){
                throw new \Exception("[OilOrders]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付加油券订单";
            $sourceType = "oil_order";
            $sourceId   = $order->id;
        }else {
            $order = Order::findOne(["order_no" => $paymentOrder->order_no]);
            if(!$order){
                throw new \Exception("[Order]订单“{$paymentOrder->order_no}”记录不存在");
            }
            $desc       = "支付商品订单";
            $sourceType = "order";
            $sourceId   = $order->id;
        }

        return [$sourceType, $sourceId, $desc];
    }
}