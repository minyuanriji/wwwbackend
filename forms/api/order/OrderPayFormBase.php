<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付基础抽象类
 * Author: zal
 * Date: 2020-04-18
 * Time: 09:49
 */


namespace app\forms\api\order;

use app\core\ApiCode;
use app\core\payment\PaymentOrder;
use app\logic\AppConfigLogic;
use app\logic\OrderLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\notify_class\goods\OrderPayNotify;

abstract class OrderPayFormBase extends BaseModel
{
    abstract public function loadPayData();

    /**
     * 获取返回数据
     * @param $orders
     * @return array
     */
    protected function getReturnData($orders)
    {
        $paymentOrders = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $supportPayTypes = (array)$order->decodeSupportPayTypes($order->support_pay_types);
            if (count($supportPayTypes) < 1) {
                $supportPayTypes = OrderLogic::getPaymentTypeConfig();
            }
            $paymentOrder = new PaymentOrder([
                'title' => $this->getOrderTitle($order),
                'amount' => (float)$order->total_pay_price,
                'orderNo' => $order->order_no,
                'notifyClass' => OrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $paymentOrders[] = $paymentOrder;
        }
        /** @var \app\models\PaymentOrder $paymentOrderData */
        //$paymentOrderData = \app\models\PaymentOrder::getOneData(["order_no"=>$order->order_no]);
        //if(empty($paymentOrderData)){
            $id = \Yii::$app->payment->createOrder($paymentOrders);
        //}else{
            //$id = $paymentOrderData->payment_order_union_id;
        //}
        return ["id" => $id];
    }

    /**
     * 加载去支付数据
     * @Author: 广东七件事 zal
     * @Date: 2020-05-07
     * @Time: 11:20
     * @param Order[] $order
     * @param User $userData
     * @return array
     */
    public function loadOrderPayData($order, $userData = []){

        $balance = $userData["balance"];

        $supportPayTypes = OrderLogic::getPaymentTypeConfig();
        $notSupportPayTypes = [];
        if(in_array(\Yii::$app->appPlatform, [User::PLATFORM_H5, User::PLATFORM_APP]) ){
            $notSupportPayTypes[] = "wechat";
        }elseif(in_array(\Yii::$app->appPlatform, [User::PLATFORM_WECHAT, User::PLATFORM_MP_WX])){
            $notSupportPayTypes[] = "alipay";
        }else{
            $notSupportPayTypes[] = "wechat";
            $notSupportPayTypes[] = "alipay";
        }
        $supportPayTypes = array_diff($supportPayTypes, $notSupportPayTypes);


        $totalPayPrice = 0;
        $orderNo = "";
        if(is_array($order)){
            foreach($order as $item){
                $totalPayPrice += (float)$item->total_pay_price;
            }
            $orderNo = $order[0]->order_no;
            $is_send = $order[0]->send_type;
        }else{
            $totalPayPrice = (float)$order->total_pay_price;
            $orderNo = $order->order_no;
            $is_send = $order->send_type;
        }

        $data = [
                //'title' => $this->getOrderTitle($order),
                'balance'         => $balance,
                'amount'          => $totalPayPrice,
                'orderNo'         => $orderNo,
                //'notifyClass' => OrderPayNotify::class,
                'supportPayTypes' => $supportPayTypes,
        ];
        $paymentConfigs = AppConfigLogic::getPaymentConfig();
        $data["pay_password_status"] = isset($paymentConfigs["pay_password_status"]) ? $paymentConfigs["pay_password_status"] : 0;
        $isPayPassword = empty($userData["transaction_password"]) ? 0 : 1;
        $returnData = $this->getReturnData(is_array($order) ? $order : [$order]);
        $data["is_pay_password"] = $isPayPassword;
        $data["union_id"] = $returnData["id"];
        $data["is_send"] = $is_send;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",$data);
    }

    /**
     * 获取订单标题
     * @param Order $order
     * @return string
     */
    private function getOrderTitle($order)
    {
        /** @var OrderDetail[] $details */
        $details = $order->getDetail()->andWhere(['is_delete' => 0])->with('goods')->all();
        if (empty($details) || !is_array($details) || count($details) < 1) {
            return $order->order_no;
        }
        $titles = [];
        foreach ($details as $detail) {
            if (!$detail->goods) {
                continue;
            }
            $titles[] = $detail->goods->name;
        }
        $title = implode(';', $titles);
        if (mb_strlen($title) > 32) {
            return mb_substr($title, 0, 32);
        } else {
            return $title;
        }
    }
}
