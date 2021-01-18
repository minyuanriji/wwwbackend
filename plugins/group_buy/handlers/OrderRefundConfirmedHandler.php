<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼团订单退款确认
 * Author: xuyaoxiang
 * Date: 2020/9/25
 * Time: 10:59
 */

namespace app\plugins\group_buy\handlers;

use app\handlers\BaseHandler;
use app\models\OrderRefund;
use app\plugins\group_buy\services\GroupBuyGoodsAttrServices;

class OrderRefundConfirmedHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            $order = $event->order_refund;

            if ($order->detail->sign != 'group_buy') {
                return false;
            }

            if (($event->order_refund->type == OrderRefund::TYPE_ONLY_REFUND || $event->order_refund->type == OrderRefund::TYPE_REFUND_RETURN) && $event->order_refund->status == OrderRefund::STATUS_AGREE) {
                $GiveScoreServices = new GroupBuyGoodsAttrServices();
                $return            = $GiveScoreServices->goodsAddStockForOrderRefund($order);
                if (!$return) {
                    \Yii::error("拼团订单售后退款确认，退库存失败");
                }
            }
        }
        );
    }
}

