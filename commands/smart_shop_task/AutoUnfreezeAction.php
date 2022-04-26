<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\exception\WxNoOperateMoneyException;
use app\plugins\smart_shop\forms\mall\OrderUnfreezeForm;
use app\plugins\smart_shop\models\Order;

class AutoUnfreezeAction extends BaseAction{

    public function run() {

        while (true) {
            sleep($this->sleepTime);
            try {

                $orderIds = Order::find()->andWhere([
                    "AND",
                    "status<>0",
                    ["auto_freeze_time" => 0],
                    ["is_delete" => 0]
                ])->select(["id"])->orderBy("updated_at ASC")->limit(1)->column();
                if(!$orderIds){
                    $this->negativeTime();
                    continue;
                }

                $shop = new SmartShop();
                $shop->initSetting();
                $this->activeTime();

                Order::updateAll(["updated_at" => time()], "id IN(".implode(",", $orderIds).")");

                foreach($orderIds as $orderId){
                    $this->unfreezeOrder($shop, $orderId);
                }

            } catch (\Exception $e) {
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }

    /**
     * 解冻订单资金
     * @param SmartShop $shop
     * @param integer $orderId
     */
    private function unfreezeOrder(SmartShop $shop, $orderId){
        try {

            $order = Order::findOne($orderId);
            if (!$order || $order->is_delete) {
                throw new \Exception("订单不存在");
            }

            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);
            if(!$detail){
                throw new \Exception("[AutoUnfreezeAction::unfreezeOrder]获取订单[ID:{$orderId}]详情信息失败");
            }

            if ($detail['pay_type'] == 1) {
                OrderUnfreezeForm::wechatUnfreeze($order, $shop, $detail);
            } else {
                OrderUnfreezeForm::alipayUnfreeze($order, $shop, $detail);
            }

            Order::updateAll(["auto_freeze_time" => time()], ["id" => $orderId]);

            $this->controller->commandOut("订单[ID:{$orderId}]资金解冻成功");

        }catch (WxNoOperateMoneyException $e){
            Order::updateAll(["auto_freeze_time" => time()], ["id" => $orderId]);
            $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
        }catch (\Exception $e){
            $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
        }
    }


}