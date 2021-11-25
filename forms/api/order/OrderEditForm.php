<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单查询
 * Author: zal
 * Date: 2020-05-12
 * Time: 10:55
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\helpers\sms\Sms;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\logic\OrderLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;

class OrderEditForm extends BaseModel
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    /**
     * 订单确认收货
     * @return array
     */
    public function orderConfirm()
    {
        try {
            /* @var Order $order */
            $order = Order::find()->where([
                'id' => $this->order_id,
//                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
            ])->one();

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            //待收货状态才能确认收货
            if ($order->status != Order::STATUS_WAIT_RECEIVE) {
                throw new \Exception('请核实订单状态，只要待收货状态才能确认收货');
            }

            OrderCommon::getCommonOrder($order->sign)->confirm($order);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功'
            ];
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"确认收货成功");
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 订单取消
     * @return array
     */
    public function orderCancel()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var Order $order */
            $order = Order::find()->where([
//                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'is_delete' => 0,
                'id' => $this->order_id,
                'is_send' => 0,
                'is_sale' => 0,
                'is_confirm' => 0
            ])->with(['userCards' => function ($query) {
                /** @var Query $query */
                $query->andWhere(['is_use' => 1]);
            }])->one();

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            if ($order->status > 0) {
                throw new \Exception('订单进行中,无法取消');
            }
            
            if (count($order->userCards) > 0) {
                throw new \Exception('订单赠送的卡券已使用,该订单无法取消');
            }

            // 未支付订单直接取消 无需后台审核 货到付款订单没有直接取消，只能申请取消
            if ($order->is_pay == 0 && $order->pay_type != 2) {
                $order->status = Order::STATUS_CLOSE;
                $order->cancel_status = 1;
                $order->cancel_at = time();
            } else {
                // 待后台审核
                $order->cancel_status = 2;
            }
            
            $res = $order->save();
            
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }
            
            if ($order->cancel_status == 1) {
                \Yii::$app->trigger(Order::EVENT_CANCELED, new OrderEvent(['order' => $order]));
            }
            $t->commit();

            // 发送短信
            //OrderCommon::sendRefundSms();
            // 发送邮件
            //OrderCommon::sendMail($order);
            //公众号模版消息
            //OrderCommon::sendMpTpl($order);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,$order->cancel_status == 1 ? '取消成功' : '待后台审核');
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 提醒发货
     * @return array
     */
    public function remindSend(){
        $order = Order::getOrderInfo([
//            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'id' => $this->order_id,
            'is_send' => Order::NO,
            'is_sale' => Order::IS_SALE_NO,
            'is_confirm' => Order::IS_CONFIRM_NO,
            'is_pay' => Order::IS_PAY_YES
        ]);

        if (!$order) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"订单数据异常");
        }

        $result = OrderLogic::getRemindSendCache($this->order_id);
        if($result){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"您已提醒过发货，请24小时后再来");
        }
        OrderLogic::setRemindSendCache($this->order_id,true);

        // 发送短信
        $sms = new Sms();
        $smsConfig = AppConfigLogic::getSmsConfig();
        $sms->sendOrderMessage($smsConfig['mobile_list'], $this->order_id);

        //OrderCommon::sendRefundSms();
        // 发送邮件
        //OrderCommon::sendMail($order);
        //公众号模版消息
        //OrderCommon::sendMpTpl($order);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"提醒成功");
    }

    /**
     * 订单删除
     * @return array
     */
    public function orderDelete()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var Order $order */
            $order = Order::getOrderInfo(["id" => $this->order_id,"is_delete" => 0]);

            if (!$order) {
                throw new \Exception('订单数据异常');
            }

            if ($order->status != Order::STATUS_WAIT_PAY && $order->status != Order::STATUS_CLOSE) {
                throw new \Exception('订单进行中,无法删除');
            }
            $order->is_delete = Order::IS_DELETE_YES;
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }
            $result = OrderDetail::updateAll(["is_delete" => OrderDetail::IS_DELETE_YES],["order_id" => $this->order_id]);
            if (!$result) {
                throw new \Exception($this->responseErrorMsg(OrderDetail::class));
            }
            $t->commit();
            // 发送短信
            //OrderCommon::sendRefundSms();
            // 发送邮件
            //OrderCommon::sendMail($order);
            //公众号模版消息
            //OrderCommon::sendMpTpl($order);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"删除成功");
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}