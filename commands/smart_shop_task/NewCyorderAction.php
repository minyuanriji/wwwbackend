<?php

namespace app\commands\smart_shop_task;

use app\commands\BaseAction;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Cyorder;
use app\plugins\smart_shop\models\StoreSet;

class NewCyorderAction extends BaseAction{

    public function run() {
        $shop = new SmartShop();
        while (true) {
            sleep($this->sleepTime);
            try {
                $shop->initSetting();
                $selects = [
                    "o.id as cyorder_id",
                    "s.id as ss_store_id",
                    "m.id as ss_mch_id",
                    "u.mobile as pay_user_mobile",
                    "o.pay_price"
                ];
                $rows = $shop->getCyorders($selects, [
                    "o.split_status=0 AND o.create_time > '1652950013'", //业务开始时间：2022-05-19 16:46
                    "o.order_status IN(1, 2, 3, 7) AND o.is_pay=1", //订单状态满足
                    "o.cancel_status IN(0, 3) AND o.is_cancel=0", //订单未取消未退款
                    "o.pay_type IN(1, 2)", //微信支付或者支付宝
                    "o.pay_time < '".(time() - 10)."'"
                ], 1);
                if(!$rows){
                    $this->negativeTime();
                    continue;
                }

                $this->activeTime();

                //先把分账状态更新了
                $orderIds = [];
                foreach($rows as $row){
                    $orderIds[] = $row['cyorder_id'];
                }
                if($orderIds){
                    $shop->batchSetCyorderSplitStatus($orderIds, 1);
                }

                //生成待处理记录
                foreach($rows as $row){

                    $storeSet = StoreSet::findOne([
                        "ss_mch_id"   => $row['ss_mch_id'],
                        "ss_store_id" => $row['ss_store_id'],
                    ]);

                    //没有开通红包赠送功能的跳过
                    if(!$storeSet || !$storeSet->enable_shopping_voucher){
                        continue;
                    }

                    $model = new Cyorder([
                        "mall_id"         => $storeSet->mall_id,
                        "cyorder_id"      => $row['cyorder_id'],
                        "created_at"      => time(),
                        "updated_at"      => time(),
                        "status"          => 0,
                        "bsh_mch_id"      => $storeSet->bsh_mch_id,
                        "ss_mch_id"       => $row['ss_mch_id'],
                        "ss_store_id"     => $row['ss_store_id'],
                        "pay_price"       => $row['pay_price'],
                        "pay_user_mobile" => $row['pay_user_mobile'],
                        "transfer_rate"   => $storeSet->transfer_rate
                    ]);
                    if(!$model->save()){
                        throw new \Exception(json_encode($model->getErrors()));
                    }
                    $this->controller->commandOut("智慧经营小程序订单[ID:".$model->id."]待处理任务创建成功");
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
        }
    }

}