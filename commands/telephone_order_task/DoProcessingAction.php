<?php

namespace app\commands\telephone_order_task;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use app\plugins\addcredit\plateform\result\QueryResult;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\Code;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\PlateForm as kcb_PlateForm;
use yii\base\Action;

class DoProcessingAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " DoProcessingAction start");
        while (true){
            try {
                $models = AddcreditOrderThirdParty::find()->where(["process_status" => 'processing'])
                            ->andWhere("created_at < '".time()."'")
                            ->orderBy("updated_at ASC")->limit(10)->all();
                if($models){
                    $ids = [];
                    foreach($models as $model){
                        $ids[] = $model->id;
                    }
                    AddcreditOrderThirdParty::updateAll([
                        "updated_at" => time()
                    ], "id IN(".implode(",", $ids).")");
                    foreach($models as $model){
                        $this->process($model);
                    }
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
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

            //先假设充值是成功的
            $model->process_status = "success";
            if(!$model->save()){
                throw new \Exception(json_encode($model->getErrors()));
            }

            //查询平台状态
            $order->order_no = $model->unique_order_no;
            $plateForm = new kcb_PlateForm();
            $res = $plateForm->query($order);
            if(!$res) {
                throw new \Exception('未知错误！');
            }

            //保存请求数据、返回数据
            $model->plateform_request_data  = $res->request_data;
            $model->plateform_response_data = $res->response_content;

            //查询失败处理
            if ($res->code != QueryResult::CODE_SUCC) {
                throw new \Exception($res->message);
            }

            //判断是否充值成功
            $content = json_decode($res->response_content,true);
            if(!isset($content['data']) || empty($content['data']) || $content['data'][0]['state'] != Code::QUERY_SUCCESS){
                throw new \Exception(isset($content['errmsg']) ? $content['errmsg'] : json_encode($content));
            }

            //保存结果
            if(!$model->save()){
                throw new \Exception(json_encode($model->getErrors()));
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

            //如果失败次数超过3次，就不再处理
            //if($order && $order->request_num > 3){
                $order->order_status = "fail";
                $order->updated_at = time();
            //}
            $order->save();

            $this->controller->commandOut($errContent);
        }

    }
}