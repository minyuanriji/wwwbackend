<?php

namespace app\commands\telephone_order_task;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use yii\base\Action;

class SetProcessingAction extends Action{

    const VER_START_TIME = "2021-10-09 10:20:00";

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " SetProcessingAction start");
        while (true){
            try {
                $query = AddcreditOrder::find()->alias("o")->andWhere([
                    'o.pay_status'   => AddcreditOrder::PAY_TYPE_PAID,
                    'o.order_status' => AddcreditOrder::ORDER_STATUS_PRO,
                ]);
                $query->leftJoin(["otp" => AddcreditOrderThirdParty::tableName()], "otp.order_id=o.id AND (otp.process_status='processing' OR otp.process_status='success')");
                $query->andWhere("otp.id IS NULL");
                $query->andWhere("o.created_at > '".strtotime(self::VER_START_TIME)."'");

                $query->select(["o.id", "o.mall_id"]);
                $rows = $query->asArray()->limit(10)->all();
                if ($rows) {
                    foreach ($rows as $row) {
                        $model = new AddcreditOrderThirdParty([
                            "mall_id"         => $row['mall_id'],
                            "order_id"        => $row['id'],
                            "process_status"  => "processing",
                            "unique_order_no" => substr(md5(uniqid()), -4) . date("ymdhis") . rand(100000, 999999),
                            "created_at"      => time()
                        ]);
                        if (!$model->save()) {
                            $this->controller->commandOut(json_encode($model->getErrors()));
                        }
                        $this->controller->commandOut("话费订单[ID:".$row['id']."]待处理任务添加成功");
                    }
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }
}