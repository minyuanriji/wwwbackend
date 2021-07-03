<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单退款确认
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderRefundEvent;
use app\component\jobs\OrderCustomerServiceJob;
use app\forms\api\order\CommonOrderForm;
use app\logic\CommonLogic;
use app\models\CommonOrderDetail;
use app\models\ErrorLog;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\QueueData;
use app\models\UserCard;
use app\services\mall\order\OrderSaleStatusService;
use yii\db\Exception;

class OrderRefundConfirmedHandler extends BaseHandler
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            /** @var OrderRefundEvent $event */
            \Yii::$app->setMchId($event->order_refund->mch_id);
            $orderDetail = $event->order_refund->detail;
         //   $orderDetail->refund_status = 2;
            // 商家同意退款 销毁订单商品赠送的卡券
            if (($event->order_refund->type == OrderRefund::TYPE_ONLY_REFUND || $event->order_refund->type == OrderRefund::TYPE_REFUND_RETURN) && $event->order_refund->status == OrderRefund::STATUS_AGREE) {
                $orderDetail->is_refund = 1;
                $orderDetail->refund_status=OrderDetail::REFUND_STATUS_SALES_END_PAY;
                /* @var UserCard[] $userCards */
                $userCards = UserCard::find()->where([
                    'order_id' => $event->order_refund->order_id,
                    'order_detail_id' => $event->order_refund->order_detail_id
                ])->all();

                foreach ($userCards as $userCard) {
                    $userCard->is_delete = 1;
                    $userCard->card->updateCount('add', 1);
                    $res = $userCard->save();
                    if (!$res) {
                        \Yii::error('卡券销毁事件处理异常');
                    }
                }
                $this->goodsAddStock($event->order_refund);

                $orderDetail->order->save();
                //添加公共订单任务
                $commonOrderForm = new CommonOrderForm();
                $commonOrderForm->commonOrderJob($orderDetail->order_id,CommonOrderDetail::STATUS_INVALID,CommonOrderDetail::TYPE_MALL_GOODS,$event->order_refund->order->mall_id);
            }
            $orderDetail->save();

            // 判断queue队列中的售后是否已经触发
            $queueId = QueueData::select($event->order_refund->order->token);
            if ($queueId && !\Yii::$app->queue->isDone($queueId)) {
                // 若未触发
                return;
            } else {
                // 若已触发，则重新添加
                $id = \Yii::$app->queue->delay(0)->push(new OrderCustomerServiceJob([
                    'orderId' => $event->order_refund->order_id
                ]));
                QueueData::add($id, $event->order_refund->order->token);
            }
        });
    }

    /**
     * @param OrderRefund $orderRefund
     * @throws Exception
     */
    private function goodsAddStock($orderRefund)
    {
        /* @var OrderDetail $orderDetail */
        $orderDetail = $orderRefund->detail;

        if ($orderDetail->sign == 'group_buy') {
            return false;
        }

        $goodsInfo = \Yii::$app->serializer->decode($orderDetail->goods_info);
        (new GoodsAttr())->updateStock($orderDetail->num, 'add', $goodsInfo['goods_attr']['id']);
    }
}
