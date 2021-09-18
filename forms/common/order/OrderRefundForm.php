<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单退款操作
 * Author: zal
 * Date: 2020-05-08
 * Time: 16:50
 */

namespace app\forms\common\order;

use app\core\ApiCode;
use app\core\payment\Payment;
use app\forms\common\template\tplmsg\Tplmsg;
use app\helpers\sms\Sms;
use app\logic\AppConfigLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\models\RefundAddress;
use app\events\OrderRefundEvent;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\services\mall\order\OrderSaleStatusService;
use app\services\mall\order\OrderSendService;
use app\services\mall\order\OrderToSatusWaitReceive;
use yii\db\Exception;
use app\services\wechat\WechatTemplateService;
use app\controllers\business\OrderCommon;

class OrderRefundForm extends BaseModel
{
    public $order_refund_id;
    public $merchant_remark;
    public $is_agree;
    public $address_id;
    public $type;//1退货 2 换货
    public $refund; //1退货 2 退款
    public $refund_price;//退款金额
    public $customer_name;
    public $express;
    public $express_no;
    public $is_express;
    public $mch_id;
    public $express_content;

    public function rules()
    {
        return [
            [['type', 'is_agree', 'order_refund_id'], 'required'],
            [['order_refund_id', 'address_id', 'is_agree', 'type', 'refund', 'is_express', 'mch_id'], 'integer'],
            [['refund_price'], 'number'],
            [['refund_price'], 'default', 'value' => 0],
            [['merchant_remark', 'express', 'express_no', 'customer_name', 'express_content'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $mchId = $this->mch_id ?: \Yii::$app->admin->identity->mch_id;
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'id' => $this->order_refund_id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $mchId ?: 0,
                'is_delete' => 0
            ])
                ->with('detail.goods')
                ->with('order')
                ->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            switch ($orderRefund->status) {
                case 1:
                    if ($this->is_agree) {
                        return $this->agree($orderRefund, '已同意售后申请');
                    } else {
                        return $this->refuse($orderRefund);
                    }
                    break;
                case 2:
                    if ($this->is_agree == 2) {
                        return $this->refuse($orderRefund);
                    }
                    if ($orderRefund->type == OrderRefund::TYPE_REFUND_RETURN || $orderRefund->type == OrderRefund::TYPE_ONLY_REFUND) {
                        return $this->refund($orderRefund);
                    }
                    if ($orderRefund->type == 2) {
                        return $this->confirm($orderRefund);
                    }
                    break;
                case 3:
                    throw new \Exception('售后订单已拒绝，请勿重复操作');
                    break;
                default:
                    throw new \Exception('错误的售后订单');
            }
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => "file:".$e->getFile().";Line:".$e->getLine()."message:".$e->getMessage()
            ];
        }
    }

    private function sendMsg($orderRefund, $refund_price, $remark)
    {
        return ;
        $tplMsg = new Tplmsg();
        $tplMsg->orderRefundMsg($orderRefund, $refund_price, $remark);
    }

    public function sendWechatTempAgree($orderRefund, $refund_price, $remark)
    {
        $WechatTemplateService = new WechatTemplateService($orderRefund->mall_id);

        //退货退款
        if($orderRefund->type==1 or $orderRefund->is_receipt==1){
            $url = "/pages/order/refund/detail?id=".$orderRefund->order_detail_id;
            $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;
        }else{
            //仅退款
            $url = "";
            $h5_url = "";
        }

        $platform = $WechatTemplateService->getPlatForm();

        $OrderDetail = new OrderDetail();
        $goods_info    = $OrderDetail->decodeGoodsInfo($orderRefund->detail['goods_info']);

        $send_data = [
            'first'    => '您的退货申请已通过',
            'keyword1' => $refund_price."元",
            'keyword2' => $orderRefund->order_no,
            'keyword3' => $goods_info['goods_attr']['name'],
            'remark'   => $remark
        ];

        return $WechatTemplateService->send($orderRefund->order->user->id, WechatTemplateService::TEM_KEY['order_refund_agree']['tem_key'], $h5_url, $send_data, $platform, $url);
    }

    /**
     * 拒绝售后申请
     * @param OrderRefund $orderRefund
     * @return array
     * @throws \Exception
     *
     */
    private function refuse($orderRefund)
    {
        $orderRefund->status = OrderRefund::STATUS_REFUSE;
        $orderRefund->is_confirm = OrderRefund::YES;
        //$orderRefund->reason = $this->merchant_remark ? $this->merchant_remark : '卖家拒绝了您的售后申请';
        $orderRefund->merchant_remark = $this->merchant_remark ? $this->merchant_remark : '卖家拒绝了您的售后申请';
        $orderRefund->status_at = time();
        if (!$orderRefund->save()) {
            return $this->responseErrorInfo($orderRefund);
        }
        //更新订单详情状态
        $orderRefund->detail->refund_status = OrderDetail::REFUND_STATUS_SALES_END_REJECT;
        if (!$orderRefund->detail->save()) {
            throw new Exception("更新订单详情状态失败");
        }

        $this->sendWechatTempRefuse($orderRefund,$orderRefund->merchant_remark);

        //更新订单售后状态,不能写在事务里
        $OrderSaleStatusService = new OrderSaleStatusService();
        $OrderSaleStatusService->updateOrderSaleStatus($orderRefund->order);

        \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent(['order_refund' => $orderRefund]));
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功,已拒绝售后申请'
        ];
    }

    public function sendWechatTempRefuse($orderRefund, $remark)
    {
        $WechatTemplateService = new WechatTemplateService($orderRefund->mall_id);

        $url = "/pages/order/refund/detail?id=".$orderRefund->order_detail_id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $OrderDetail = new OrderDetail();
        $goods_info    = $OrderDetail->decodeGoodsInfo($orderRefund->detail['goods_info']);

        $send_data = [
            'first'    => '售后申请结果通知',
            'keyword1' => $orderRefund->order_no,
            'keyword2' => $goods_info['goods_attr']['name'],
            'keyword3' => $remark,
            'keyword4' => date("Y-m-d H:i:s",time()),
        ];

        return $WechatTemplateService->send($orderRefund->order->user->id, WechatTemplateService::TEM_KEY['order_refund_refuse']['tem_key'], $h5_url, $send_data, $platform, $url);
    }

    /**
     * 同意售后申请
     * @param OrderRefund $orderRefund
     * @param $remark
     * @return array
     *
     */
    private function agree($orderRefund, $remark)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $address = RefundAddress::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->address_id,
                'is_delete' => 0
            ]);
            if (($orderRefund->type != OrderRefund::TYPE_ONLY_REFUND || $orderRefund->is_receipt == OrderRefund::IS_REFUND_YES) && !$address) {
                throw new \Exception('退货地址不能为空');
            }
            $orderRefund->merchant_remark = $remark;
            $orderRefund->status = OrderRefund::STATUS_AGREE;
            $orderRefund->address_id = $orderRefund->type == OrderRefund::TYPE_ONLY_REFUND && $orderRefund->is_receipt == OrderRefund::IS_REFUND_NO ? 0 : $address->id;
            $orderRefund->status_at = time();
            if (!$orderRefund->save()) {
                throw new Exception($this->responseErrorMsg($orderRefund));
            }

            //更新订单详情状态
            if (OrderRefund::TYPE_REFUND_RETURN == $orderRefund->type) {
                $orderRefund->detail->refund_status = OrderDetail::REFUND_STATUS_SALES_SEND_AGREE;
            } elseif (OrderRefund::TYPE_ONLY_REFUND == $orderRefund->type) {
                $orderRefund->detail->refund_status = OrderDetail::REFUND_STATUS_SALES_AGREE;
            }

            if (!$orderRefund->detail->save()) {
                throw new Exception("更新订单详情状态失败");
            }
            //微信通知
            $this->sendWechatTempAgree($orderRefund, $orderRefund->refund_price, $remark);
            //退积分
            (new OrderCommon()) -> returnIntegral($orderRefund->detail,$orderRefund -> order_no);
            $this->returnScore($orderRefund->detail);

            //退购物券
            $this->returnShoppingVoucher($orderRefund);

            $transaction->commit();
            //更新订单售后状态,不能写在事务里
            $OrderSaleStatusService = new OrderSaleStatusService();
            $OrderSaleStatusService->updateOrderSaleStatus($orderRefund->order);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功,已同意售后申请',
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * 退回积分
     * @param OrderDetail $order_detail
     */
    public function returnScore(OrderDetail $order_detail)
    {
        if ($order_detail->use_score == 1) {
            $user = User::findOne($order_detail->order->user_id);
            \Yii::$app->currency->setUser($user)->score->add($order_detail->use_score_price,"售后退款");
        }
    }

    /**
     * 退回购物券
     * @param OrderRefund $refund
     */
    public function returnShoppingVoucher(OrderRefund $refund)
    {
        $orderDetail = $refund->detail;
        if($orderDetail->shopping_voucher_num > 0){
            $user = User::findOne($refund->user_id);
            $modifyForm = new ShoppingVoucherLogModifiyForm([
                "money"       => $orderDetail->shopping_voucher_num,
                "desc"        => "订单退款，购物券返还",
                "source_id"   => $refund->id,
                "source_type" => "from_order_refund"
            ]);
            $modifyForm->add($user);
        }
    }

    /**
     * 确认退款
     * @param OrderRefund $orderRefund
     * @return array
     *
     */
    private function refund($orderRefund)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            /*if (($orderRefund->type == OrderRefund::TYPE_REFUND_RETURN || $orderRefund->type == OrderRefund::TYPE_EXCHANGE) && $orderRefund->is_confirm == 0) {
                throw new \Exception('售后订单未确认收货');
            }*/

            /** @var PaymentOrder $paymentOrder */
            $paymentOrder = PaymentOrder::find()->where(['order_no' => $orderRefund->order->order_no])->with('paymentOrderUnion')->one();
            $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
            if (!empty($paymentRefund) && $paymentRefund->is_pay == PaymentRefund::YES && $paymentOrder->pay_type == PaymentOrder::PAY_TYPE_WECHAT) {
                throw new \Exception('售后订单已打款！无需重复');
            }

            if($orderRefund->type == OrderRefund::TYPE_ONLY_REFUND){
                $orderRefund->is_confirm = 1;
                $orderRefund->confirm_at = time();
            }

            $orderRefund->is_refund = OrderRefund::IS_REFUND_YES;
            $orderRefund->refund_at = time();
            $user = User::findOne(['id' => $orderRefund->order->user_id]);
            //没有退款记录，说明第一次打款
            if(empty($paymentRefund)){
                // 用户抵扣积分恢复
                $goodsInfo = \Yii::$app->serializer->decode($orderRefund->detail->goods_info);
                $goodsAttr = $goodsInfo->goods_attr;
                if ($goodsAttr['use_score']) {
                    $desc = '商品订单退款，订单' . $orderRefund->order->order_no;
                    \Yii::$app->currency->setUser($user)->score->refund(
                        (int)$goodsAttr['use_score'],
                        $desc
                    );
                }
            }

            $orderRefund->reality_refund_price = $this->refund_price;

            /*if ($this->refund_price <= 0) {
                throw new \Exception('退款金额需大于零');
            }
            //卖家自定义退款金额
            if ($this->refund_price) {
                $orderRefund->reality_refund_price = $this->refund_price;
            }*/
            if (!$orderRefund->save()) {
                throw new \Exception($this->responseErrorMsg($orderRefund));
            }

            // 退款
            $advance_refund = 0;
            if ($orderRefund->order->pay_type == 2) {
                // 货到付款订单退款，线下沟通
                $msg = '订单为货到付款方式，退款金额请线下自行处理';
                //预售退款涉及退定金
                if ($orderRefund->reality_refund_price > 0) {
                    //预售订单售后退款，退款金额包含定金，判断中需要扣除
                    $order_info = Order::findOne(['order_no' => $orderRefund->order->order_no, 'sign' => 'advance']);
                    $price = $orderRefund->reality_refund_price;
                    if (!empty($order_info)) {
                        //判断是否存在插件，是否有插件权限
                        $bool = false;
                        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//取商城所属账户权限
                        if (!is_array($permission_arr) && $permission_arr) {
                            $bool = true;
                        } else {
                            foreach ($permission_arr as $value) {
                                if ($value == 'advance') {
                                    $bool = true;
                                    break;
                                }
                            }
                        }
                        if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                            \Yii::info('预售货到付款退款只退定金');
                            if ($price > $orderRefund->order->total_price) {//退款金额大于尾款金额
                                $advance_refund = $price - $orderRefund->order->total_price;
                                $price = $orderRefund->order->total_price;//总退款大于商品总价，取商品总价
                            }
                        }
                    }
                }
            } else {
                if ($orderRefund->reality_refund_price > 0) {
                    //预售订单售后退款，退款金额包含定金，判断中需要扣除
                    $order_info = Order::findOne(['order_no' => $orderRefund->order->order_no, 'sign' => 'advance']);
                    $price = $orderRefund->reality_refund_price;
                    if (!empty($order_info)) {
                        //判断是否存在插件，是否有插件权限
                        $bool = false;
                        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//取商城所属账户权限
                        if (!is_array($permission_arr) && $permission_arr) {
                            $bool = true;
                        } else {
                            foreach ($permission_arr as $value) {
                                if ($value == 'advance') {
                                    $bool = true;
                                    break;
                                }
                            }
                        }
                        if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                            $paymentOrder = \app\models\PaymentOrder::findOne([
                                'order_no' => $orderRefund->order->order_no,
                                'is_pay' => 1
                            ]);
                            if (price_format($paymentOrder->amount - $paymentOrder->refund) < price_format($orderRefund->refund_price)) {
                                \Yii::info('预售退款涉及到定金');
                                $advance_refund = $price - ($paymentOrder->amount - $paymentOrder->refund);
                                $price = price_format($paymentOrder->amount - $paymentOrder->refund);
                            }
                        }
                    }
                    /** @var Payment $paymemntModel */
                    $paymemntModel = \Yii::$app->payment;
                    $paymemntModel->refund($orderRefund->order->order_no, $price);
                }
                $msg = '处理成功，已完成退款';
                if($orderRefund->type == OrderRefund::TYPE_REFUND_RETURN){
                    $msg .= "退货";
                }
            }
            \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
                'order_refund' => $orderRefund,
                'advance_refund' => price_format($advance_refund > 0 ? $advance_refund : 0)
            ]));
            $isRefund = 1;
            /** @var OrderDetail $item */
            foreach ($orderRefund->order->detail as $item) {
                if (!$item->is_refund) {
                    $isRefund = $item->is_refund;
                }
            }
            if ($isRefund && $orderRefund->type == OrderRefund::TYPE_EXCHANGE && $orderRefund->order->is_confirm == 0) {
                OrderCommon::getCommonOrder($orderRefund->order->sign)->confirm($orderRefund->order);
            }
            $t->commit();

            //若没有可发货的订单,订单状态更新为待收货
            $order              = $orderRefund->order;
            $OrderToSatusWaitReceive = new OrderToSatusWaitReceive();
            $OrderToSatusWaitReceive->statusToSatusWaitReceive($order);

            $this->sendWechatTempRefund($orderRefund, $price, '退款已完成');

            //更新订单售后状态,不能写在事务里
            $OrderSaleStatusService = new OrderSaleStatusService();
            $OrderSaleStatusService->updateOrderSaleStatus($orderRefund->order);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg,
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param $orderRefund
     * @param $remark
     * @return array
     * @throws \Exception
     */
    public function sendWechatTempRefund($orderRefund, $price,$remark)
    {
        $WechatTemplateService = new WechatTemplateService($orderRefund->mall_id);

        $url = "/pages/order/refund/detail?id=".$orderRefund->order_detail_id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '退款成功通知',
            'keyword1' => $orderRefund->order_no,
            'keyword2' => $price,
            'remark' => $remark."\n时间:".date("Y-m-d H:i:s",time()),
        ];

        return $WechatTemplateService->send($orderRefund->order->user->id, WechatTemplateService::TEM_KEY['order_refund_money']['tem_key'], $h5_url, $send_data, $platform, $url);
    }

    /**
     * 换货确认
     * @param OrderRefund $orderRefund
     * @return array
     * @throws \Exception
     *
     */
    private function confirm($orderRefund)
    {
        /*if (substr_count($this->express, '京东') && empty($this->customer_name)) {
            throw new \Exception('京东物流必须填写京东商家编码');
        }*/
        // 用户已发货|商家确认收货
        $orderRefund->is_confirm = 1;
        $orderRefund->confirm_at = time();

        // 换货-确认收货需填写快递单号
        if ($this->is_express == 1) {
            (new Order())->validateExpress($this->express);

            if (!$this->express_no) {
                throw new \Exception('请填写快递单号');
            }

            $orderRefund->merchant_customer_name = $this->customer_name;
            $orderRefund->merchant_express = $this->express;
            $orderRefund->merchant_express_no = $this->express_no;
        } else {
            $orderRefund->merchant_express_content = $this->express_content ?: '';
        }
        $orderRefund->merchant_remark = $this->merchant_remark ?: '';
        $res = $orderRefund->save();
        if (!$res) {
            throw new \Exception($this->responseErrorMsg($orderRefund));
        }
        \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
            'order_refund' => $orderRefund
        ]));

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '确认收货成功'
        ];
    }
}
