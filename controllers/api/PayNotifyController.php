<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付异步回调
 * Author: zal
 * Date: 2020-05-14
 * Time: 19:49
 */

namespace app\controllers\api;

use app\controllers\BaseController;
use app\core\payment\PaymentNotify;
use app\models\Mall;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use jianyan\easywechat\Wechat;

class PayNotifyController extends BaseController
{
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    /**
     * 微信支付回调
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function actionWechat()
    {
        try {
            /** @var Wechat $app */
            $app = \Yii::$app->wechat;
            $response = $app->payment->handlePaidNotify(function($notify, $successful){
                \Yii::error('pay_notify 返回的数据: ' . var_export($notify,true));
                $t = \Yii::$app->db->beginTransaction();
                try {
                    $res = $notify;
                    if ($res['return_code'] !== 'SUCCESS' && $res['result_code'] !== 'SUCCESS') {
                        \Yii::error('pay_notify 订单尚未支付: ' . $res['result_code']);
                        throw new \Exception('支付失败: ' . $res['out_trade_no']);
                    }
                    $paymentOrderUnion = PaymentOrderUnion::findOne([
                        'order_no' => $res['out_trade_no'],
                    ]);
                    if (!$paymentOrderUnion) {
                        \Yii::error('pay_notify 订单不存在: ' . $res['out_trade_no']);
                        throw new \Exception('订单不存在: ' . $res['out_trade_no']);
                    }
                    //判断是否已经支付
                    if ($paymentOrderUnion->is_pay === 1) {
                        $t->rollBack();
                        return true;
                    }
                    //判断商城是否存在
                    $mall = Mall::findOne($paymentOrderUnion->mall_id);
                    if (!$mall) {
                        \Yii::error('pay_notify 未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
                        throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
                    }
                    \Yii::$app->setMall($mall);

                    $paymentOrderUnionAmount = (doubleval($paymentOrderUnion->amount) * 100) . '';
                    if (intval($res['total_fee']) !== intval($paymentOrderUnionAmount)) {
                        \Yii::error("pay_notify 支付金额与订单金额不一致");
                        throw new \Exception('支付金额与订单金额不一致。');
                    }

                    $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
                    $paymentOrderUnion->is_pay = 1;
                    $paymentOrderUnion->pay_type = 1;
                    if (!$paymentOrderUnion->save()) {
                        \Yii::error("pay_notify ".$paymentOrderUnion->getFirstErrors());
                        throw new \Exception($paymentOrderUnion->getFirstErrors());
                    }
                    foreach ($paymentOrders as $paymentOrder) {
                        $Class = $paymentOrder->notify_class;
                        if (!class_exists($Class)) {
                            continue;
                        }
                        $paymentOrder->is_pay = 1;
                        $paymentOrder->pay_type = 1;
                        if (!$paymentOrder->save()) {
                            throw new \Exception($paymentOrder->getFirstErrors());
                        }
                        /** @var PaymentNotify $notify */
                        $notify = new $Class();
                        try {
                            $po = new \app\core\payment\PaymentOrder([
                                'orderNo' => $paymentOrder->order_no,
                                'amount' => (float)$paymentOrder->amount,
                                'title' => $paymentOrder->title,
                                'notifyClass' => $paymentOrder->notify_class,
                                'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_WECHAT
                            ]);
                            $notify->notify($po);
                        } catch (\Exception $e) {
                            \Yii::error("pay_notify 支付订单更新失败 params=".var_export($paymentOrder,true));
                            throw new \Exception("支付订单更新失败 ".$e->getMessage());
                        }
                    }
                    $t->commit();
                }catch (\Exception $exception){
                    $t->rollBack();
                    \Yii::error("pay_notify 支付插件回调处理，出现异常 File=".$exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage());
                    throw new \Exception("支付插件回调处理，更新失败 ".$e->getMessage());
                }
            });
            return $response;
        }catch (\Exception $ex){
            \Yii::error("pay_notify 支付回调出现异常 File=".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
            return false;
        }
    }

    public function actionAlipay()
    {
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['out_trade_no'])
            || empty($res['sign'])
            || empty($res['total_amount'])
        ) {
            throw new \Exception('请求数据错误' );
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['out_trade_no'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['out_trade_no']);
        }
        if ($paymentOrderUnion->is_pay === 1) {

            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $passed = \Yii::$app->plugin->getPlugin('aliapp')->checkSign();

        if ($passed) {
            $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 4;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $Class = $paymentOrder->notify_class;
                if (!class_exists($Class)) {
                    continue;
                }
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 4;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                /** @var PaymentNotify $notify */
                $notify = new $Class();
                try {
                    $po = new \app\core\payment\PaymentOrder([
                        'orderNo' => $paymentOrder->order_no,
                        'amount' => (float)$paymentOrder->amount,
                        'title' => $paymentOrder->title,
                        'notifyClass' => $paymentOrder->notify_class,
                        'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_ALIPAY
                    ]);
                    $notify->notify($po);
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
            echo "success";
            return ;
        }
    }

    public function actionBaidu()
    {
        \Yii::error('百度支付回调');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['tpOrderId'])
            || empty($res['rsaSign'])
            || empty($res['totalMoney'])
            || empty($res['orderId'])
                ) {
            throw new \Exception('请求数据错误' );
        }

        if ($res['status'] != 2) {
            throw new \Exception('订单尚未支付');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['tpOrderId'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['tpOrderId']);
        }

        $bdAppOrder = BdappOrder::findOne(['order_no' => $res['tpOrderId']]);
        if (!$bdAppOrder) {
            $bdAppOrder = new BdappOrder();
            $bdAppOrder->order_no = $res['tpOrderId'];
            $bdAppOrder->bd_order_id = $res['orderId'];
            $bdAppOrder->bd_user_id = $res['userId'];
            $bdAppOrder->save();
        } else {
            $bdAppOrder->bd_user_id = $res['userId'];
            $bdAppOrder->save();
        }

        if ($paymentOrderUnion->is_pay === 1) {
            $responseData = [
                'errno' => 0,
                'msg' => 'success',
                'data' => ['isConsumed'=>2]
            ];
            \Yii::$app->response->data = $responseData;
            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $res['sign'] = $res['rsaSign'];
        unset($res['rsaSign']);
        $truthSign = \Yii::$app->plugin->getPlugin('bdapp')->checkSignWithRsa($res);

        if (!$truthSign) {
            throw new \Exception('签名验证失败。');
        }

        $paymentOrderUnionAmount = (doubleval($paymentOrderUnion->amount) * 100) . '';
        if (intval($res['totalMoney']) !== intval($paymentOrderUnionAmount)) {
            throw new \Exception('支付金额与订单金额不一致。');
        }

        $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
        $paymentOrderUnion->is_pay = 1;
        $paymentOrderUnion->pay_type = 5;
        if (!$paymentOrderUnion->save()) {
            throw new \Exception($paymentOrderUnion->getFirstErrors());
        }
        foreach ($paymentOrders as $paymentOrder) {
            $Class = $paymentOrder->notify_class;
            if (!class_exists($Class)) {
                continue;
            }
            $paymentOrder->is_pay = 1;
            $paymentOrder->pay_type = 5;
            if (!$paymentOrder->save()) {
                throw new \Exception($paymentOrder->getFirstErrors());
            }
            /** @var PaymentNotify $notify */
            $notify = new $Class();
            try {
                $po = new \app\core\payment\PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_BAIDU
                ]);
                $notify->notify($po);
            } catch (\Exception $e) {
                \Yii::error($e);
            }
        }
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => ['isConsumed'=>2]
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionBaiduRefundVerify()
    {
        \Yii::error('百度退款审核');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['orderId'])
            || empty($res['userId'])
            || empty($res['tpOrderId'])
            || empty($res['refundBatchId'])
        ) {
            throw new \Exception('请求数据错误' );
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['tpOrderId'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['tpOrderId']);
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $res['sign'] = $res['rsaSign'];
        unset($res['rsaSign']);
        $truthSign = \Yii::$app->plugin->getPlugin('bdapp')->checkSignWithRsa($res);

        if (!$truthSign) {
            throw new \Exception('退款查询签名验证失败。');
        }

        $bdAppOrder = BdappOrder::findOne(['bd_order_id' => $res['orderId']]);
        if (!$bdAppOrder) {
            throw new \Exception('退款订单错误.');
        }

        \Yii::error('百度退款审核成功');
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => ['auditStatus'=>1,
                'calculateRes' => [
                    'refundPayMoney' => $res['applyRefundMoney']
                ]]
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionBaiduRefund()
    {
        \Yii::error('百度退款回调');
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        try {
            $bdAppOrder = BdappOrder::findOne(['bd_order_id' => $res['orderId']]);
            if (!$bdAppOrder) {
                throw new \Exception('百度订单号获取失败');
            }
            $bdAppOrder->is_refund = 1;
            $res = $bdAppOrder->save();
            if (!$res) {
                throw new \Exception((new Model())->responseErrorMsg($bdAppOrder));
            }
        } catch (\Exception $e) {
            \Yii::error($e);
        }
        $responseData = [
            'errno' => 0,
            'msg' => 'success',
            'data' => (object)null ,
        ];
        \Yii::$app->response->data = $responseData;
        return;
    }

    public function actionToutiao()
    {
        $res = \Yii::$app->request->post();
        if (!$res) {
            throw new \Exception('请求数据错误');
        }
        if (empty($res['out_trade_no'])
            || empty($res['sign'])
            || empty($res['total_amount'])
        ) {
            throw new \Exception('请求数据错误' );
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'order_no' => $res['out_trade_no'],
        ]);
        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在: ' . $res['out_trade_no']);
        }
        if ($paymentOrderUnion->is_pay === 1) {

            return;
        }
        $mall = Mall::findOne($paymentOrderUnion->mall_id);
        if (!$mall) {
            throw new \Exception('未查询到id=' . $paymentOrderUnion->id . '的商城。 ');
        }
        \Yii::$app->setMall($mall);

        $passed = \Yii::$app->plugin->getPlugin('ttapp')->checkSign();

        if ($passed) {
            $paymentOrders = PaymentOrder::findAll(['payment_order_union_id' => $paymentOrderUnion->id]);
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 6;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $Class = $paymentOrder->notify_class;
                if (!class_exists($Class)) {
                    continue;
                }
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 6;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                /** @var PaymentNotify $notify */
                $notify = new $Class();
                try {
                    $po = new \app\core\payment\PaymentOrder([
                        'orderNo' => $paymentOrder->order_no,
                        'amount' => (float)$paymentOrder->amount,
                        'title' => $paymentOrder->title,
                        'notifyClass' => $paymentOrder->notify_class,
                        'payType' => \app\core\payment\PaymentOrder::PAY_TYPE_TOUTIAO
                    ]);
                    $notify->notify($po);
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
            \Yii::$app->response->data = true;
            return true;
        }
    }
}
