<?php

namespace app\commands\smart_shop_task;

use app\core\ApiCode;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\forms\mall\OrderDoSplitForm;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

class ProcessSplitOrderAction extends Action{

    public function run()
    {
        $this->controller->commandOut(date("Y/m/d H:i:s") . " NewSplitOrderAction start");
        $shop = new SmartShop();
        while (true) {
            try {

                $orderIds = Order::find()->select(["id"])->where([
                    "status"    => Order::STATUS_UNCONFIRMED,
                    "is_delete" => 0
                ])->orderBy("updated_at ASC")->limit(1)->column();
                if($orderIds){
                    $shop->initSetting(); //刷新下配置

                    Order::updateAll(["updated_at" => time()], "id IN(".implode(",", $orderIds).")");

                    foreach($orderIds as $orderId){
                        $this->splitOrder($shop, $orderId);
                    }
                }

            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }

    }

    /**
     * 执行分账操作
     * @param SmartShop $shop
     * @param $orderId
     */
    private function splitOrder(SmartShop $shop, $orderId){
        try {

            $order = Order::findOne($orderId);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->status != Order::STATUS_UNCONFIRMED){
                throw new \Exception("订单状态异常");
            }

            $mch = Mch::findOne($order->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("无法获取到商户信息");
            }
            $detail = $shop->getOrderDetail($order->from_table_name, $order->from_table_record_id);

            if($detail['pay_type'] == 1){
                OrderDoSplitForm::wechatSplit($mch, $order, $shop, $detail);
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
}