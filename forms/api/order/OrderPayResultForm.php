<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-01
 * Time: 21:49
 */

namespace app\forms\api\order;


use app\forms\api\share\ShareApplyForm;
use app\models\AdminInfo;
use app\models\Model;
use app\models\Order;
use app\models\OrderPayResult;

class OrderPayResultForm extends Model
{
    public $payment_order_union_id;

    public function rules()
    {
        return [
            [['payment_order_union_id'], 'required'],
            [['payment_order_union_id'], 'integer'],
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        $paymentOrderUnion = \Yii::$app->payment->getPaymentOrderUnion([
            'id' => $this->payment_order_union_id,
            'user_id' => \Yii::$app->admin->id,
        ]);
        if (!$paymentOrderUnion) {
            return [
                'code' => 1,
                'msg' => '无效的payment_order_union_id。'
            ];
        }
        $paymentOrders = \Yii::$app->payment->getPaymentOrders($this->payment_order_union_id);
        $cardList = [];
        $userCouponList = [];
        $sendData = [];
        $orderList = [];
        $orderPageUrl = null;

        foreach ($paymentOrders as $paymentOrder) {
            $order = Order::findOne([
                'order_no' => $paymentOrder->order_no,
            ]);
            $orderPayResult = OrderPayResult::findOne(['order_id' => $order->id,]);
            if (!$orderPayResult) {
                continue;
            }
            $data = $orderPayResult->decodeData($orderPayResult->data);
            $cardList = array_merge($cardList, $data['card_list']);
            $userCouponList = array_merge($userCouponList, $data['user_coupon_list']);

            $orderList[] = [
                'id' => $order->id,
                'sign' => $order->sign,
            ];

            if ($order->sign === 'spell_group') {
                $por = \app\plugins\spell_group\models\SpellGroupOrderRelation::findOne([
                    'order_id' => $order->id,
                    'is_delete' => 0,
                ]);
                if ($por) {
                    $orderPageUrl = "/plugins/pt/detail/detail?spell_group_order_id={$por->spell_group_order_id}&id={$por->spell_group_order_id}";
                }
            }

            // TODO 此代码应写在插件上
            if (isset($data['send_data'])) {
                $sendData = $data['send_data'];
            }
        }

        // 校验用户是否满足申请分销商
        $shareCheck = false;
        try {
            if (\Yii::$app->admin->identity->identity->is_distributor != 1) {
                $shareApplyForm = new ShareApplyForm();
                $shareApplyForm->mall = \Yii::$app->mall;
                $shareCheck = $shareApplyForm->checkApply();
            }
        } catch (\Exception $exception) {
        }


        return [
            'code' => 0,
            'data' => [
                'total_pay_price' => price_format($paymentOrderUnion->amount),
                'card_list' => $cardList,
                'user_coupon_list' => $userCouponList,
                'send_data' => $sendData,
                'goods_list' => [],
                'plugins' => \Yii::$app->branch->childPermission(AdminInfo::findOne(\Yii::$app->mall->id)),
                'order_list' => $orderList,
                'order_page_url' => $orderPageUrl,
                'shareCheck' => $shareCheck
            ],
        ];
    }
}
