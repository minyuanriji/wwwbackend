<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单售后任务类（处理订单是否过售后期）
 * Author: zal
 * Date: 2020-04-21
 * Time: 15:16
 */

namespace app\component\jobs;

use app\events\CommonOrderEvent;
use app\events\OrderEvent;
use app\forms\api\order\CommonOrderForm;
use app\logic\IntegralLogic;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\Order;
use app\models\OrderRefund;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;

class OrderCustomerServiceJob extends Component implements JobInterface
{
    public $orderId;

    public function execute($queue)
    {
        try{
            \Yii::warning('OrderCustomerServiceJob ->>' . $this->orderId);
            $order = Order::findOne([
                'id' => $this->orderId,
                'is_delete' => 0,
                'is_send' => 1,
                'is_confirm' => 1,
                'is_sale' => 0
            ]);
            if (!$order) {
                return;
            }
            \Yii::warning('OrderCustomerServiceJob order ->>' . var_export($order,true));
            $mall = Mall::findOne(['id' => $order->mall_id]);
            \Yii::$app->setMall($mall);

            $orderRefundList = OrderRefund::find()->where(['order_id' => $order->id, 'is_delete' => 0])->all();
            if ($orderRefundList) {
                /* @var OrderRefund[] $orderRefundList */
                foreach ($orderRefundList as $orderRefund) {
                    if ($orderRefund->is_confirm == 0) {
                        return false;
                    }
                }
            }

            $order->is_sale = 1;
            $order->complete_at = time();
            $order->status = Order::STATUS_COMPLETE;
//            if($order->sale_status != Order::IS_SALE_YES){
//                $order->status = Order::STATUS_COMPLETE;
//            }

            if ($order->save()) {
                /** @var CommonOrder $commonOrders */
//                $commonOrders = CommonOrder::find()->where(["order_id" => $order->id])->one();
//                if(!empty($commonOrders)){
//                    $commonOrders->status = CommonOrder::STATUS_IS_COMPLETE;
//                    $commonOrders->save();
//                }
                //售后完成
                if($order->status == Order::STATUS_COMPLETE){
                    //添加公共订单任务
                    $commonOrderForm = new CommonOrderForm();
                    $commonOrderForm->commonOrderJob($order->id,CommonOrderDetail::STATUS_COMPLETE,CommonOrderDetail::TYPE_MALL_GOODS,$order->mall_id);
                }

                $event = new OrderEvent([
                    'order' => $order
                ]);
                \Yii::$app->trigger(Order::EVENT_SALES, $event);
            }
        }catch (\Exception $ex){
            \Yii::error('OrderCustomerServiceJob error=' . $ex->getFile().";line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }
}
