<?php

namespace app\commands\smart_shop_task;

use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\MerchantFzlist;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

class NewSplitOrderAction extends Action{

    public function run(){
        $shop = new SmartShop();
        while (true){
            try {
                $shop->initSetting();
                $selects = [
                    "o.id as from_table_record_id",
                    "s.id as ss_store_id",
                    "m.id as ss_mch_id",
                    "u.mobile as pay_user_mobile",
                    "o.pay_price"
                ];
                $rows = $shop->getCyorders($selects, [
                    "o.is_split=1 AND o.split_status=0 AND o.create_time > s.split_start_at", //必须是分账订单
                    "o.order_status IN(1, 2, 3, 7) AND o.is_pay=1", //订单状态满足
                    "o.cancel_status IN(0, 3) AND o.is_cancel=0", //订单未取消未退款
                    "o.pay_time < '".(time() - 10)."'"
                ], 1);

                //先把分账状态更新了
                $orderIds = [];
                foreach($rows as $row){
                    $orderIds[] = $row['from_table_record_id'];
                }
                if($orderIds){
                    $shop->batchSetCyorderSplitStatus($orderIds, 1);
                }

                //生成分账待处理记录
                $merchantFzList = [];
                foreach($rows as $row){
                    $fzKey = $row['ss_mch_id'] . "#" . $row['ss_store_id'];
                    if(!isset($merchantFzList[$fzKey])){
                        $merchantFzList[$fzKey] = MerchantFzlist::findOne([
                            "ss_mch_id"   => $row['ss_mch_id'],
                            "ss_store_id" => $row['ss_store_id'],
                            "is_delete"   => 0
                        ]);
                        if(!$merchantFzList[$fzKey]) continue;
                    }
                    $model = new Order([
                        "mall_id"              => $merchantFzList[$fzKey]->mall_id,
                        "bsh_mch_id"           => $merchantFzList[$fzKey]->bsh_mch_id,
                        "created_at"           => time(),
                        "updated_at"           => time(),
                        "from_table_name"      => "cyorder",
                        "from_table_record_id" => $row['from_table_record_id'],
                        "ss_mch_id"            => $row['ss_mch_id'],
                        "ss_store_id"          => $row['ss_store_id'],
                        "status"               => Order::STATUS_UNCONFIRMED,
                        "pay_price"            => $row['pay_price'],
                        "pay_user_mobile"      => $row['pay_user_mobile']
                    ]);
                    if(!$model->save()){
                        throw new \Exception(json_encode($model->getErrors()));
                    }
                    $this->controller->commandOut("订单分账记录[ID:".$model->id."]创建成功");
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

}