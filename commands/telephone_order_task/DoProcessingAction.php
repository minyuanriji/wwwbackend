<?php

namespace app\commands\telephone_order_task;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\result\QueryResult;
use yii\base\Action;

class DoProcessingAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " DoProcessingAction start");
        while (true){
            try {
                $models = AddcreditOrderThirdParty::find()->where(["process_status" => 'processing'])
                            ->andWhere([
                                "AND",
                                "created_at < '".time()."'",
                                "next_query_time < '".time()."'"
                            ])
                            ->orderBy("updated_at ASC")->limit(1)->all();
                if($models){
                    $ids = [];
                    foreach($models as $model){
                        $ids[] = $model->id;
                    }
                    AddcreditOrderThirdParty::updateAll([
                        "updated_at" => time(),
                        "next_query_time" => (time() + 300)
                    ], "id IN(".implode(",", $ids).")");
                    foreach($models as $model){
                        $this->process($model);
                    }
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(3);
        }
    }

    /**
     * 任务处理
     * @param AddcreditOrderThirdParty $model
     * @return void
     */
    private function process(AddcreditOrderThirdParty $model){
        $order = null;
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order = AddcreditOrder::findOne($model->order_id);
            if(!$order){
                throw new \Exception("话费充值订单不存在");
            }

            $platModel = AddcreditPlateforms::findOne($order->plateform_id);
            if (!$platModel) {
                throw new \Exception("无法获取平台信息");
            }

            //查询平台状态
            $order->order_no = $model->unique_order_no;
            $className = $platModel->class_dir;
            if(!class_exists($className)){
                throw new \Exception("充值类{$className}文件不存在");
            }
            $platClass = new $className();
            $res = $platClass->query2($order, $platModel);

            //保存请求数据、返回数据
            $model->plateform_request_data  = $res->request_data;
            $model->plateform_response_data = $res->response_content;
            if(!$model->save()){
                throw new \Exception(json_encode($model->getErrors()));
            }

            //查询失败处理
            if ($res->code != QueryResult::CODE_SUCC) {
                throw new \Exception($res->message);
            }

            if($res->status == "success"){ //充值成功
                $model->process_status = "success";
                $model->save();
            }elseif($res->status == "fail"){ //充值失败
                throw new \Exception($res->message);
            }

            $this->controller->commandOut("话费充值任务[ID:{$model->id}]处理完成");

            $t->commit();
        }catch (\Exception $e){

            $t->rollBack();

            $errContent = implode(";", [
                "message" => $e->getMessage(),
                "file"    => "file:" . $e->getFile(),
                "line"    => "line:" . $e->getLine()
            ]);

            //处理失败
            $model->remark         = $errContent;
            $model->process_status = "fail";
            $model->save();

            //更新订单状态为失败
            $order->order_status = "fail";
            $order->updated_at = time();
            $order->save();

            $this->controller->commandOut($errContent);
        }

    }
}