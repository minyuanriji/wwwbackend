<?php

namespace app\commands\telephone_order_task;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use app\plugins\addcredit\models\AddcreditPlateforms;
use yii\base\Action;

class SetProcessingAction extends Action{

    const VER_START_TIME = "2021-10-07 13:42:00";

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

                $query->select(["o.id"]);
                $rows = $query->asArray()->limit(10)->all();
                if ($rows) {
                    foreach ($rows as $row) {

                        $addcreditOrder = AddcreditOrder::findOne($row['id']);
                        $addcreditOrder->order_status = "success";
                        $addcreditOrder->request_num += 1;
                        if(!$addcreditOrder->save()){
                            throw new \Exception(json_encode($addcreditOrder->getErrors()));
                        }

                        //平台下单
                        $plateform = AddcreditPlateforms::findOne($addcreditOrder->plateform_id);
                        if (!$plateform) {
                            throw new \Exception("无法获取平台信息");
                        }

                        //$addcreditOrder->order_no = substr(md5(uniqid()), -4) . date("ymdhis") . rand(100000, 999999);
                        $model = new AddcreditOrderThirdParty([
                            "mall_id"         => $addcreditOrder->mall_id,
                            "order_id"        => $addcreditOrder->id,
                            "process_status"  => "processing",
                            "unique_order_no" => $addcreditOrder->order_no,
                            "created_at"      => (time() + 15 * 60)
                        ]);
                        if (!$model->save()) {
                            throw new \Exception(json_encode($model->getErrors()));
                        }

                        $className = $plateform->class_dir;
                        if(!class_exists($className)){
                            throw new \Exception("充值类{$className}文件不存在");
                        }

                        $platClass = new $className();
                        $res = $platClass->submit($addcreditOrder, $plateform, false);

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