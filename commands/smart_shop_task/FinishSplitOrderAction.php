<?php

namespace app\commands\smart_shop_task;

use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\WechatPaySdkApi;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

class FinishSplitOrderAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " FinishSplitOrderAction start");
        $shop = new SmartShop();
        $sleep = 3;
        while (true) {
            try {
                $orderIds = Order::find()->select(["id"])->where([
                    "status"    => Order::STATUS_PROCESSING,
                    "is_delete" => 0
                ])->orderBy("updated_at ASC")->limit(1)->column();
                if($orderIds){
                    $sleep = max(1, --$sleep);
                    $shop->getDB(true);
                    $shop->initSetting(); //刷新下配置

                    Order::updateAll(["updated_at" => time()], "id IN(".implode(",", $orderIds).")");

                    foreach($orderIds as $orderId){
                        $this->finishOrder($shop, $orderId);
                    }
                }else{
                    $sleep = min(30, ++$sleep);
                }

            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep($sleep);
        }
    }

    /**
     * 判断订单状态，更新分账订单
     * @param SmartShop $shop
     * @param $orderId
     */
    private function finishOrder(SmartShop $shop, $orderId){
        try {
            $order = Order::findOne($orderId);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->status != Order::STATUS_PROCESSING){
                throw new \Exception("订单状态异常");
            }

            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);
            if($detail['pay_type'] == 1){

                if(!$this->cancelOrder($order, $shop, $detail) && $detail['order_status'] == 3){
                    $this->finishDone($order, $shop, $detail); //订单已完成
                }

                $this->controller->commandOut("订单[ID:{$order->id}]分账处理成功");
            }else{
                throw new \Exception("暂未实现支付宝分账");
            }

        }catch (\Exception $e){
            $this->controller->commandOut($e->getMessage());
            Order::updateAll([
                "status"     => Order::STATUS_CANCELED,
                "error_text" => json_encode([
                    "message" => $e->getMessage(),
                    "line"    => $e->getLine(),
                    "file"    => $e->getFile()
                ])
            ], ["id" => $orderId]);
        }
    }

    /**
     * 订单已完成
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @throws \yii\db\Exception
     */
    private function finishDone(Order $order, SmartShop $shop, $detail){
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order->status     = Order::STATUS_FINISHED;
            $order->updated_at = time();
            if(!$order->save()){
                throw new \Exception(json_encode($order->getErrors()));
            }

            $wechatPay = new WechatPaySdkApi([
                "mchid"          => $shop->setting['sp_mchid'],
                "serial"         => $shop->setting['cert_serial'],
                "privateKeyPath" => $shop->setting['apiclient_key'],
                "wechatCertPath" => $shop->setting['wechat_cert']
            ]);

            $splitData = !empty($order->split_data) ? json_decode($order->split_data, true) : [];

            //解冻资金
            $data = $wechatPay->post("v3/profitsharing/orders/unfreeze", [
                "sub_mchid"      => (string)$detail['mno'],
                "transaction_id" => (string)$splitData['transaction_id'],
                "out_order_no"   => (string)$splitData['out_order_no'],
                "description"    => "微信支付"
            ]);
            if(!isset($data['state']) || !in_array($data['state'], ["PROCESSING", "FINISHED"])){
                throw new \Exception("解冻资金失败：". json_encode(is_array($data) ? $data : []));
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            throw $e;
        }

    }

    /**
     * 根据当前订单状态决定是否取消订单
     * @param Order $order
     * @param SmartShop $shop
     * @param $detail
     * @return boolean
     */
    private function cancelOrder(Order $order, SmartShop $shop, $detail){

        if(in_array($detail['order_status'], [1, 2, 3]) && in_array($detail['cancel_status'], [0, 3]) && $detail['is_pay'] == 1){
            return false;
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $splitData = !empty($order->split_data) ? json_decode($order->split_data, true) : [];
            if(empty($splitData['out_return_no'])){
                $splitData['out_return_no'] = "rt" . md5(uniqid() . rand(0, 10000));
            }

            $order->status     = Order::STATUS_CANCELED;
            $order->updated_at = time();
            $order->split_data = json_encode($splitData);
            if(!$order->save()){
                throw new \Exception(json_encode($order->getErrors()));
            }

            $wechatPay = new WechatPaySdkApi([
                "mchid"          => $shop->setting['sp_mchid'],
                "serial"         => $shop->setting['cert_serial'],
                "privateKeyPath" => $shop->setting['apiclient_key'],
                "wechatCertPath" => $shop->setting['wechat_cert']
            ]);

            if($order->split_amount > 0){ //请求分账回退
                $res = $wechatPay->post("v3/profitsharing/return-orders", [
                    "sub_mchid"     => (string)$detail['mno'],
                    "out_order_no"  => (string)$splitData['out_order_no'],
                    "out_return_no" => (string)$splitData['out_return_no'],
                    "return_mchid"  => (string)$shop->setting['wechat_fz_account'],
                    "amount"        => (int)($order->split_amount * 100),
                    "description"   => "用户退款"
                ]);
                if(!isset($res['result']) || $res['result'] == "FAILED"){
                    throw new \Exception("请求分账回退失败：" . json_encode(is_array($res) ? $res : []));
                }
            }

            //解冻资金
            $res = $wechatPay->post("v3/profitsharing/orders/unfreeze", [
                "sub_mchid"      => (string)$detail['mno'],
                "transaction_id" => (string)$splitData['transaction_id'],
                "out_order_no"   => (string)$splitData['out_order_no'],
                "description"    => "微信支付"
            ]);
            if(!isset($res['state']) || !in_array($res['state'], ["PROCESSING", "FINISHED"])){
                throw new \Exception("解冻资金失败：". json_encode(is_array($res) ? $res : []));
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            throw $e;
        }

        return true;
    }
}