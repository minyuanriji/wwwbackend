<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\Config;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromAddcredit;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;


class AddcreditOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " AddcreditOrderSendAction start");
        while (true){
            try {
                if(!$this->newAction()){
                    $this->sendAction();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){
        $sendLogs = ShoppingVoucherSendLog::find()->where(["status" => "waiting", "source_type" => "from_addcredit_order"])
            ->select(["id", "user_id", "source_id", "source_type", "money"])
            ->orderBy("updated_at ASC")
            ->asArray()->limit(10)->all();
        $sendLogIds = [];
        foreach($sendLogs as $sendLog){
            try {
                $user = User::findOne($sendLog['user_id']);
                if(!$user || $user->is_delete){
                    throw new \Exception("用户不存在");
                }
                $modifyForm = new ShoppingVoucherLogModifiyForm([
                    "money"       => $sendLog['money'],
                    "desc"        => "话费充值获得赠送红包",
                    "source_id"   => $sendLog['source_id'],
                    "source_type" => $sendLog['source_type']
                ]);
                $modifyForm->add($user, true);
                $sendLogIds[] = $sendLog['id'];
                $this->controller->commandOut("红包发放记录ID:" . $sendLog['id'] . "处理完成");
            }catch (\Exception $e){
                $remark = implode("\n", [$e->getMessage(), "line:" . $e->getLine(), "file:".$e->getFile()]);
                ShoppingVoucherSendLog::updateAll([
                    "status" => "invalid",
                    "remark" => $remark
                ], ["id" => $sendLog['id']]);
                $this->controller->commandOut($remark);
            }
        }
        if($sendLogIds){
            ShoppingVoucherSendLog::updateAll(["status" => "success"], "id IN (".implode(",", $sendLogIds).")");
        }
    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = AddcreditOrder::find()->alias("ao");
        $query->leftJoin(["apf" => AddcreditPlateforms::tableName()], "ao.plateform_id=apf.id");
        $query->innerJoin(["svfa" => ShoppingVoucherFromAddcredit::tableName()], "(svfa.sdk_key=apf.sdk_dir) AND svfa.is_delete=0");
        $query->leftJoin(["svs" => ShoppingVoucherSendLog::tableName()], "svs.source_id=ao.id AND svs.source_type='from_addcredit_order'");
        $query->andWhere([
            "AND",
            "ao.pay_price > 0",
            "svs.id IS NULL",
            ["ao.pay_status" => 'paid'],
            //["ao.order_status" => "success"],
        ]);
        $query->orderBy("ao.updated_at ASC");

        $selects = ["ao.id", "ao.mall_id", "ao.mobile", "ao.user_id", "ao.pay_price", "svfa.param_data_json", 'ao.product_id', 'apf.product_json_data'];

        $AddcreditOrder = $query->select($selects)->asArray()->limit(10)->all();
        if(!$AddcreditOrder)
            return false;

        $AddcreditOrderIds = [];
        foreach($AddcreditOrder as $item){
            $AddcreditOrderIds[] = $item['id'];
        }
        AddcreditOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $AddcreditOrderIds).")");

        foreach($AddcreditOrder as $value){
            $ruleData = json_decode($value['param_data_json'], true);
            if (!$ruleData) {
                continue;
            }

            $productData = json_decode($value['product_json_data'], true);
            if (!$productData)
                continue;

            $mobile_count = AddcreditOrder::find()->where(['mobile' => $value['mobile'], 'pay_status' => 'paid'])->count();

            $productData = array_combine(array_column($productData, 'product_id'), $productData);

            if (!isset($productData[$value['product_id']]))
                continue;

            if ($productData[$value['product_id']]['type'] == 'fast') {
                $charge = 1;
            } else {
                $charge = 0;
            }

            /*if (in_array($value['product_id'], Config::FAST_CHARGING)) {
                $charge = 1;
            } else {
                $charge = 0;
            }*/

            if ($mobile_count > 1) {
                if ($charge) {
                    $ratio = $ruleData['fast_follow_give'];
                } else {
                    $ratio = $ruleData['slow_follow_give'];
                }
            } else {
                if ($charge) {
                    $ratio = $ruleData['fast_one_give'];
                } else {
                    $ratio = $ruleData['slow_one_give'];
                }
            }


            $money = $value['pay_price'] * (floatval($ratio)/100);

            //慢充，新用户第一次红包赠超过100部分按50%赠送
            if ($mobile_count <=1 && $money > 100 && $productData[$value['product_id']]['type'] != 'fast') {
                $money = 100 + ($money - 100) * 0.5;
            }

            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $value['mall_id'],
                "user_id"     => $value['user_id'],
                "source_id"   => $value['id'],
                "source_type" => "from_addcredit_order",
                "status"      => "waiting",
                "money"       => $money,
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($value)
            ]);

            if($sendLog->save()){
                $this->controller->commandOut("红包发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }

}