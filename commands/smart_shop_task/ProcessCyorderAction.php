<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\forms\common\StoreAccountBalanceModifyForm;
use app\plugins\smart_shop\models\Cyorder;
use app\plugins\smart_shop\models\StoreAccount;
use app\plugins\smart_shop\models\StoreSet;

class ProcessCyorderAction extends BaseAction{

    public function run() {
        $shop = new SmartShop();
        while (true) {
            sleep($this->sleepTime);
            try {
                $orderIds = Cyorder::find()->select(["id"])->andWhere([
                    "AND",
                    ["status" => 0],
                    ["<", "created_at", time() - 20],
                ])->orderBy("updated_at ASC")->limit(1)->column();
                if($orderIds){
                    $this->activeTime();
                    $shop->initSetting(); //刷新下配置
                    foreach($orderIds as $orderId){
                        $this->processCyorder($shop, $orderId);
                    }
                }else{
                    $this->negativeTime();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }

    /**
     * 处理订单
     * @param SmartShop $shop
     * @param $orderId
     */
    private function processCyorder(SmartShop $shop, $orderId){
        try {

            $localCyorder = Cyorder::findOne($orderId);
            if(!$localCyorder){
                throw new \Exception("数据异常，订单[ID:".$orderId."]不存在");
            }

            $smartCyorder = $shop->getCyorderDetail($localCyorder->cyorder_id);
            if(!in_array($smartCyorder['order_status'], [1, 2, 3, 7]) ||
                !$smartCyorder['is_pay'] || $smartCyorder['is_cancel'] != 0 ||
                !in_array($smartCyorder['cancel_status'], [0, 3])){
                throw new \Exception("订单[ID:".$orderId."]状态异常");
            }

            //订单状态已完成，或者超过1天状态未变更
            //更新任务记录为已完成
            if($smartCyorder['order_status'] == 3 || (time() - $localCyorder->created_at) > 1800 * 24){
                Cyorder::updateAll(["status" => 1, "updated_at" => time()], ["id" => $localCyorder->id]);
                $this->controller->commandOut("ProcessCyorderAction::processCyorder>>订单ID:".$localCyorder->id."处理完成");
            }else{
                $this->negativeTime();
            }

        }catch (\Exception $e){
            $this->controller->commandOut($e->getMessage());
            Cyorder::updateAll([
                "status" => 2,
                "error_text" => json_encode([
                    "message" => $e->getMessage(),
                    "line"    => $e->getLine(),
                    "file"    => $e->getFile()
                ], JSON_UNESCAPED_UNICODE)
            ], ["id" => $orderId]);
        }
    }
}