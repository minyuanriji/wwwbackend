<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单取消基础操作
 * Author: zal
 * Date: 2020-04-21
 * Time: 15:16
 */

namespace app\handlers\orderHandler;

use app\events\OrderEvent;
use app\forms\api\order\CommonOrderForm;
use app\forms\common\goods\CommonGoods;
use app\forms\common\template\tplmsg\Tplmsg;
use app\logic\IntegralLogic;
use app\models\CommonOrderDetail;
use app\models\GoodsAttr;
use app\models\Mall;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\services\wechat\WechatTemplateService;
use Yii;
use yii\db\Exception;

/**
 * @property User $user
 */
abstract class BaseOrderCanceledHandler extends BaseOrderHandler
{
    public $user;

    public function handle()
    {
        return $this->cancel();
    }

    protected function cancel()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var OrderEvent $event */
            $this->action();
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('订单取消完成事件：');
            \Yii::error($exception);
            throw $exception;
        }
    }

    protected function action()
    {
        $this->ScoreResume()->couponResume()->refund()->cardResume()->sendTemplate()->updateGoodsInfo()->goodsAddStock($this->event->order);

        if ('group_buy' != $this->event->order->sign) {
            $this->sendWechatTemp();
        }

        $order = $this->event->order;
        $mall = Mall::findOne(['id' => $order->mall_id]);
        Yii::$app->mall = $mall;
        //退还用户红包券、积分券
        $integralLogic = new IntegralLogic();
        $integralLogic->refundIntegral($order,0);
        $integralLogic->refundIntegral($order,1);
    }

    /**
     * 用户积分恢复
     */
    protected function ScoreResume()
    {
        $user = User::findOne(['id' => $this->event->order->user_id]);
        if ($this->event->order->use_score) {
            $desc = '商品订单取消，订单' . $this->event->order->order_no;
            \Yii::$app->currency->setUser($user)->score
                ->refund((int)$this->event->order->use_score, $desc);
        }
        return $this;
    }

    protected function couponResume()
    {
        /**
         * @var  $key
         * @var OrderDetail $orderDetail
         */
        foreach ($this->event->order->detail as $key => $orderDetail) {
            // 优惠券恢复
            if ($orderDetail->use_user_coupon_id) {
                $userCoupon = UserCoupon::findOne(['id' => $orderDetail->use_user_coupon_id]);
                $userCoupon->is_use = 0;
                $userCoupon->is_failure = 0;
                $userCoupon->save();
            }
        }
        return $this;
    }

    protected function refund()
    {
        //添加公共订单任务
        $commonOrderForm = new CommonOrderForm();
        $commonOrderForm->commonOrderJob($this->event->order->id,CommonOrderDetail::STATUS_INVALID,CommonOrderDetail::TYPE_MALL_GOODS,$this->event->order->mall_id);
        // 已付款就退款
        if ($this->event->order->is_pay == 1) {
            \Yii::$app->payment->refund($this->event->order->order_no, $this->event->order->total_pay_price);
        }
        return $this;
    }

    protected function cardResume()
    {
        /** @var UserCard[] $userCards */
        // 销毁发放的卡券
        $userCards = UserCard::find()->with('card')->where(['order_id' => $this->event->order->id])->all();
        foreach ($userCards as $userCard) {
            $userCard->is_delete = 1;
            $userCard->card->updateCount('add', 1);
            $res = $userCard->save();
            if (!$res) {
                \Yii::error('卡券销毁事件处理异常');
            }
        }
        return $this;
    }

    protected function sendTemplate()
    {
        try {
            $template = new Tplmsg();
            $template->orderCancelMsg($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function updateGoodsInfo()
    {
        // 修改商品支付信息
        CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'sub');

        return $this;
    }

    /**
     * @param Order $order
     * @throws Exception
     */
    protected function goodsAddStock($order)
    {
        if ($order->sign == 'group_buy') {
            return $this;
        }

        /* @var OrderDetail[] $orderDetail */
        $orderDetail = $order->detail;
        $goodsAttrIdList = [];
        $goodsNum = [];
        foreach ($orderDetail as $item) {
            $goodsInfo = \Yii::$app->serializer->decode($item->goods_info);
            $goodsAttrIdList[] = $goodsInfo['goods_attr']['id'];
            $goodsNum[$goodsInfo['goods_attr']['id']] = $item->num;
        }
        $goodsAttrList = GoodsAttr::find()->where(['id' => $goodsAttrIdList])->all();
        /* @var GoodsAttr[] $goodsAttrList */
        foreach ($goodsAttrList as $goodsAttr) {
            $goodsAttr->updateStock($goodsNum[$goodsAttr->id], 'add');
        }

        return $this;
    }

    protected function sendWechatTemp(){
        $WechatTemplateService = new WechatTemplateService($this->event->order->mall_id);

        $url = "/pages/order/detail?orderId=" . $this->event->order->id;

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        $send_data = [
            'first'    => '你好，订单已取消',
            'keyword1' => $this->event->order->order_no,
            'keyword2' => date("Y-m-d H:i:s",time()),
            'keyword3' => $this->event->order->total_pay_price,
            'remark'   => ""
        ];

        $WechatTemplateService->send($this->event->order->user_id, WechatTemplateService::TEM_KEY['order_cancel']['tem_key'], $h5_url, $send_data, $platform,$url);

        return $this;
    }
}
