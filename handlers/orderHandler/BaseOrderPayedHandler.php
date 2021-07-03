<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付触发处理基础类
 * Author: zal
 * Date: 2020-04-21
 * Time: 16:10
 */

namespace app\handlers\orderHandler;
use app\forms\common\UserRoleTypeEditForm;
use app\helpers\SerializeHelper;
use app\core\mail\SendMail;
use app\forms\api\order\CommonOrderForm;
use app\forms\common\card\SendCommon;
use app\forms\mall\statistics\CouponAutoSendForm;
use app\helpers\sms\Sms;
use app\forms\common\goods\CommonGoods;
use app\forms\common\template\tplmsg\Tplmsg;
use app\logic\AppConfigLogic;
use app\logic\IntegralLogic;

use app\models\CommonOrder;
use app\models\CouponAutoSend;
use app\models\OrderPayResult;
use app\models\OrderDetail;
use app\models\OrderRefund;

use app\models\User;
use app\models\UserCard;
use app\forms\efps\distribute\EfpsDistributeForm;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

/**
 * @property User $user
 */
abstract class BaseOrderPayedHandler extends BaseOrderHandler
{
    /* @var Order $order */
    public $order;
    /* @var User $user */
    public $user;
    /* @var OrderDetail[] $orderDetailList */
    public $orderDetailList;

    protected function paid()
    {
        /* @var OrderEvent $event */
        $event = $this->event;
        \Yii::$app->setMchId($event->order->mch_id);
        \Yii::warning('=============订单支付事件开始执行===========');

        try {
            $this->order = $event->order;
            
            $this->user = User::find()->where(['id' => $this->order->user_id])->one();

            $orderRefundList = OrderRefund::find()->where([
                'order_id' => $this->order->id,
                'is_delete' => 0
            ])->all();
            // 已退款的订单详情id列表
            $notOrderDetailIdList = [];
            if ($orderRefundList) {
                /* @var OrderRefund[] $orderRefundList */
                foreach ($orderRefundList as $orderRefund) {
                    if ($orderRefund->is_confirm == 0) {
                        return false;
                    } else {
                        if ($orderRefund->type == 1 && $orderRefund->status == 3) {
                            $notOrderDetailIdList[] = $orderRefund->order_detail_id;
                        }
                    }
                }
            }
            $this->orderDetailList = OrderDetail::find()->where(['order_id' => $this->order->id, 'is_delete' => 0])
                ->with('goods')
                ->keyword(!empty($notOrderDetailIdList), ['not in', 'id', $notOrderDetailIdList])->all();

            
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    protected function action()
    {

        //赠送积分
        IntegralLogic::sendScore($this->order);

        // 发放积分
       //$this->giveIntegral();
        // 发放积分券
        // echo '支付后发放积分券'.PHP_EOL;
        //IntegralLogic::shopSendScore($this->order,'paid');
        // 发放红包券
        // echo '支付后发放红包券'.PHP_EOL;
        IntegralLogic::shopSendIntegral($this->order,'paid');

        // 消费升级会员等级
        $this->upLevel();

        //分账
        EfpsDistributeForm::goodsOrder($this->order);

        //多商户订单结算
        //GoodsOrderAutoSettleForm::settle($this->order);
    }

    /**
     * 消费升级会员等级
     * @return void
     */
    protected function upLevel(){
        $details = $this->order->detail;
        $roleTypes = [];
        foreach($details as $detail){
            $goods = $detail->goods;
            if($goods && $goods->enable_upgrade_user_role
                && $goods->upgrade_user_role_type){
                $roleTypes[] = $goods->upgrade_user_role_type;
            }
        }
        $roleType = null;
        if(in_array("branch_office", $roleTypes)){
            $roleType = "branch_office";
        }elseif(in_array("partner", $roleTypes)){
            $roleType = "partner";
        }elseif(in_array("store", $roleTypes)){
            $roleType = "store";
        }

        if(!empty($roleType)){
            $form = new UserRoleTypeEditForm([
                "id"          => $this->order->user_id,
                "role_type"   => $roleType,
                "action"      => UserRoleTypeEditForm::ACTION_UPGRADE,
                "source_type" => "order",
                "source_id"   => $this->order->id,
                "content"     => "消费升级，订单[ID:".$this->order->id."]"
            ]);
            $form->save();
        }
    }

    /**
     * 积分发放
     * @return bool
     */
    protected function giveIntegral()
    {
        try {
            $integral = 0;
            foreach ($this->orderDetailList as $orderDetail) {
                if($orderDetail->goods->enable_score){ //积分券开启跳过
                    continue;
                }

                $is_order_paid = $orderDetail->goods->is_order_paid || 0;//商品订单设置支付状态
                $order_paid = $orderDetail->goods->order_paid ? SerializeHelper::decode($orderDetail->goods->order_paid) : [];//商品订单设置支付参数

                if($is_order_paid && $order_paid['is_score']){
                    if (!in_array($orderDetail->refund_status, OrderDetail::ALLOW_ADD_SCORE_REFUND_STATUS)) {
                        continue;
                    }
    
                    if ($orderDetail->goods->give_score_type == 1) {
                        $integral += ($orderDetail->goods->give_score * $orderDetail->num);
                    } else {
                        $integral += (intval($orderDetail->goods->give_score * $orderDetail->total_price / 100));
                    }
                }
            }
            if ($integral > 0) {
                //                启动订单结束后返回积分支付积分
                // \Yii::$app->currency->setUser($this->user)->score->add($integral, '订单购买赠送积分');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        $cardList = $this->sendCard();
        $userCouponList = $this->sendCoupon();
        $data = [
            'card_list' => $cardList,
            'user_coupon_list' => $userCouponList,
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    /**
     * @return array
     * 向用户发送商品卡券
     */
    protected function sendCard()
    {
        try {
            $cardSendForm = new SendCommon();
            $cardSendForm->mall_id = \Yii::$app->mall->id;
            $cardSendForm->user_id = $this->event->order->user_id;
            $cardSendForm->order_id = $this->event->order->id;
            /** @var UserCard[] $userCardList */
            $userCardList = $cardSendForm->save();
            $cardList = [];
            foreach ($userCardList as $userCard) {
                $cardList[] = $userCard->attributes;
            }
        } catch (\Exception $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * @return array
     * 向用户发送优惠券（自动发送方案--订单支付成功发送优惠券）
     */
    protected function sendCoupon()
    {
        try {
            $couponSendForm = new CouponAutoSendForm();
            $couponSendForm->event = CouponAutoSend::PAY;
            $couponSendForm->user = $this->user;
            $couponSendForm->mall = $this->mall;
            $userCouponList = $couponSendForm->send();
        } catch (\Exception $exception) {
            \Yii::error('优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    /**
     * @return $this
     * 短信发送--新订单通知
     */
    protected function sendSms()
    {
        try {
            if ($this->orderConfig->is_sms != 1) {
                throw new \Exception('未开启短信提醒');
            }
            $sms = new Sms();
            $smsConfig = AppConfigLogic::getSmsConfig();
            if ($smsConfig['status'] == 1 && $smsConfig['mobile_list']) {
                $sms->sendOrderMessage($smsConfig['mobile_list'], $this->event->order->id);
            }
        } catch (NoGatewayAvailableException $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        } catch (\Exception $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 邮件发送--新订单通知
     */
    protected function sendMail()
    {
        // 发送邮件
        try {
            if ($this->orderConfig->is_mail != 1) {
                throw new \Exception('未开启邮件提醒');
            }
            $mailer = new SendMail();
            $mailer->mall = $this->mall;
            $mailer->order = $this->event->order;
            $mailer->orderPayMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过小程序模板消息发送给用户支付成功通知
     */
    protected function sendTemplate()
    {
        try {
            $template = new Tplmsg();
            $template->orderPayMsg($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过公众号向商家发送公众号消息
     */
    protected function sendMpTemplate()
    {
        return $this;
        $goodsName = '';
        foreach ($this->event->order->detail as $detail) {
            $goodsName .= $detail->goods->name;
        }
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'newOrderTpl';
            $tplMsg->params = [
                'sign' => $this->event->order->sign,
                'goods' => $goodsName,
                'time' => date('Y-m-d H:i:s'),
                'user' => $this->user->nickname,
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function setGoods()
    {
        try {
            CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'add');
        } catch (\Exception $exception) {
            \Yii::error('商品支付信息设置');
            \Yii::error($exception);
        }
        return $this;
    }

    /**
     * 更新公共订单表
     */
    protected function setCommonOrder(){
        try {
            CommonOrderForm::updateCommonOrder(["status" => CommonOrder::STATUS_IS_PAY],["order_id" => $this->event->order->id]);
        } catch (\Exception $exception) {
            \Yii::error('公共订单支付状态更新失败');
            \Yii::error($exception);
        }
    }
}
