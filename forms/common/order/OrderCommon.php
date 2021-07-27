<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单公共处理类
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:16
 */

namespace app\forms\common\order;


use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\events\OrderEvent;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\SmsCommon;
use app\handlers\orderHandler\OrderHandler;
use app\logic\AppConfigLogic;
use app\logic\IntegralLogic;
use app\models\BaseModel;
use app\forms\OrderConfig;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use Overtrue\EasySms\Message;
use yii\helpers\ArrayHelper;

class OrderCommon extends BaseModel
{
    public static function getCommonOrder($sign)
    {
        $self = new self();
        $self->sign = $sign;
        return $self;
    }

    /**
     * 获取订单的配置
     * @return OrderConfig
     */
    public function getOrderConfig()
    {
        $sign = $this->sign;
        try {
            if ($sign) {
                $config = \Yii::$app->plugin->getPlugin($sign)->getOrderConfig();
            } else {
                throw new \Exception('不是插件订单');
            }
        } catch (\Exception $exception) {
            \Yii::error('--order config--' . $exception->getMessage());
            $config = new OrderConfig();
            $config->setOrder();
        }
        return $config;
    }

    /**
     * 获取订单事件
     * @return
     *
     */
    public function getOrderHandler()
    {
        $orderHandler = new OrderHandler();
        $orderHandler->sign = $this->sign;
        return $orderHandler;
    }

    /**
     * 订单确认收货
     * @param Order $order
     * @throws \Exception
     *
     */
    public function confirm($order)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($order->is_send != 1) {
                throw new \Exception('订单未发货，无法收货');
            }
            if ($order->is_confirm == 1) {
                throw new \Exception('订单已确认收货,无需重复');
            }
            if ($order->pay_type != 2 && $order->is_pay != 1) {
                throw new \Exception('订单未支付');
            }
            // 货到付款订单 确认收货时即支付
            if ($order->pay_type == 2 && $order->is_pay == 0) {
                $order->is_pay = 1;
                $order->pay_at = time();
                $order->status = Order::STATUS_WAIT_COMMENT;
            }
            $order->status = Order::STATUS_WAIT_COMMENT;
            $order->is_confirm = 1;
            $order->confirm_at = time();
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }

            //赠送积分
            IntegralLogic::sendScore($order);

            $t->commit();
            if ($order->pay_type == 2) {
                // 货到付款的订单 确认收货需要触发支付完成事件
                \Yii::$app->trigger(Order::EVENT_PAYED, new OrderEvent([
                    'order' => $order
                ]));
            }
            \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent(['order' => $order]));
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * 获取核销订单不同状态下数量
     * @return array
     */
    public function getOfflineOrderInfoCount()
    {
        if (\Yii::$app->user->isGuest) {
            return [0, 0];
        }

        $form = new OrderListCommon();
        $form->user_id    = \Yii::$app->user->id;
        $form->mall_id    = \Yii::$app->mall->id;
        $form->is_recycle = 0;
        $form->only_offline_order = 1;

        //待使用
        $form->sale_status = Order::SALE_STATUS_NO;
        $form->status = 1;
        $form->getQuery();
        $waitSend = $form->query->count();

        return [$waitSend, 0];
    }

    /**
     * 获取不同订单状态下的订单数
     * @return array
     */
    public function getOrderInfoCount()
    {
        if (\Yii::$app->user->isGuest) {
            return [0, 0, 0, 0, 0];
        }
        $form = new OrderListCommon();
        $form->user_id = \Yii::$app->user->id;
        $form->mall_id = \Yii::$app->mall->id;
        $form->is_recycle = 0;
        $form->mch_id = 0;
        $form->orderType = ['express_baopin', 'express_normal'];

        // TODO 售后状态暂时没加
        // 'is_sale' => 0,
        $form->status =0;
        $form->getQuery();
        $waitPay = $form->query->count();

        $form->sale_status = Order::SALE_STATUS_NO;
        $form->status = 1;
        $form->getQuery();
        $waitSend = $form->query->count();

        $form->status = 2;
        $form->getQuery();
        $waitConfirm = $form->query->count();

        $form->status = 3;
        $form->getQuery();
        $waitComment = $form->query->count();

        $refundList = OrderRefund::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->andWhere([
            'or',
            ['status' => 1],
            ['status' => 2]
        ])->andWhere([
            'or',
            ['is_confirm' => 0],
            ['is_refund' => 0],
            ['is_refund' => 2]
        ])->with('order')->all();

        $newList = [];
        /** @var OrderRefund $item */
        foreach ($refundList as $item) {
            $newItem = ArrayHelper::toArray($item);
            // 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
            if ($item->is_refund == 2) {
                /** @var PaymentOrder $paymentOrder */
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                if (!$paymentRefund) {
                    $newItem['is_refund'] = 0;
                }
            }
            $newList[] = $newItem;
        }
        $waitRefund = 0;
        foreach ($newList as $item) {
            if (($item['type'] == 0 || $item['type'] == 1 || $item['type'] == 2) && $item['is_refund'] == 0) {
                $waitRefund += 1;
            }
        }

        return [$waitPay, $waitSend, $waitConfirm, $waitComment, $waitRefund];
    }

    /**
     * 发送邮件
     * @param $order
     */
    public static function sendMail($order)
    {
        try {
            $mailer = new SendMail();
            $mailer->mall = \Yii::$app->mall;
            $mailer->order = $order;
            $mailer->refundMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送:' . $exception->getMessage());
        }
    }

    /**
     * 发送公众号消息
     * @param Order $order
     */
    public static function sendMpTpl($order)
    {
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'cancelOrderTpl';
            $tplMsg->params = [
                'order_no' => $order->order_no,
                'price' => $order->total_goods_price,
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * 发送短信提醒
     * xuyaoxiang:这个函数不要用，统一调sms里的
     * @return array
     */
    public static function sendRefundSms()
    {
        try {
            $smsConfig = AppConfigLogic::getSmsConfig();
            if ($smsConfig['status'] != 1) {
                throw new \Exception('短信功能未开启');
            }
            if (!is_array($smsConfig['mobile_list']) || count($smsConfig['mobile_list']) <= 0) {
                throw new \Exception('接收短信手机号不正确');
            }
            $setting = SmsCommon::getCommon()->getSetting();
            if (!(isset($smsConfig['order_refund'])
                && isset($smsConfig['order_refund']['template_id'])
                && $smsConfig['order_refund']['template_id'])) {
                throw new \Exception($setting['order_refund']['title'] . '模板ID未设置');
            }
            $data = [];
            foreach ($setting['order_refund']['variable'] as $value) {
                $data[$smsConfig['order_refund'][$value['key']]] = '89757';
            }
            $message = new Message([
                'template' => $smsConfig['order_refund']['template_id'],
                'data' => $data
            ]);
            /** @var Sms $sms */
            $sms = \Yii::$app->sms->module('mall');
            foreach ($smsConfig['mobile_list'] as $mobile) {
                $sms->send($mobile, $message);
            }
        } catch (\Exception $exception) {
            \Yii::error('生成售后订单：' . $exception->getMessage());
        }
    }

    /**
     * 检测是否绑定手机
     * @return array
     * @throws \Exception
     */
    public static function checkIsBindMobile()
    {
        $phoneConfig = AppConfigLogic::getPhoneConfig();
        $mobile = \Yii::$app->user->identity->mobile;
        if ($phoneConfig["all_network_enable"] == 1 && empty($mobile)) {
            return false;
        }
        return true;
    }

    /**
     * 获取自定订单退款状态
     * @param OrderDetail $orderDetail
     * @return int
     */
    public static function getDiyOrderRefundStatus($orderDetail){
        if(!is_array($orderDetail)){
            $refund = $orderDetail->refund ? ArrayHelper::toArray($orderDetail->refund) : [];
            $orderDetail = ArrayHelper::toArray($orderDetail);
            if(!empty($refund)){
                $orderDetail['refund'] = $refund;
            }
        }
        //自定义订单退款状态1退款中2已退款3退款退货中4已退款退货5换货中6换货完成
        $orderRefundStatus = 0;
        if(isset($orderDetail["refund"])){
            $refund = $orderDetail["refund"];
            $type = $refund["type"];
            $isReceipt = $refund["is_receipt"];
            $isRefund = $orderDetail["is_refund"];
            $refundStatus = $orderDetail["refund_status"];
            if($type == OrderRefund::TYPE_ONLY_REFUND && $isReceipt == OrderRefund::IS_RECEIPT_NO){
                if($isRefund == OrderDetail::IS_REFUND_YES){
                    $orderRefundStatus = 2;
                }else{
                    $orderRefundStatus = 1;
                }
            }else if($type == OrderRefund::TYPE_REFUND_RETURN || $isReceipt == OrderRefund::IS_RECEIPT_YES){
                if($isRefund == OrderDetail::IS_REFUND_YES){
                    $orderRefundStatus = 4;
                }else{
                    $orderRefundStatus = 3;
                }
            }else if($type == OrderRefund::TYPE_EXCHANGE || $isReceipt == OrderRefund::IS_RECEIPT_YES){
                if($refundStatus == OrderDetail::REFUND_STATUS_SALES){
                    $orderRefundStatus = 5;
                }else{
                    $orderRefundStatus = 6;
                }
            }
        }

        return $orderRefundStatus;
    }
}
