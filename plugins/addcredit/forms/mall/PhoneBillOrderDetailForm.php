<?php

namespace app\plugins\addcredit\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use app\plugins\addcredit\models\AddcreditPlateforms;

class PhoneBillOrderDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $order = AddcreditOrder::findOne($this->id);
            if(!$order){
                throw new \Exception("订单不存在");
            }

            $platModel = AddcreditPlateforms::findOne($order->plateform_id);
            if (!$platModel) {
                throw new \Exception("无法获取平台信息");
            }

            $className = $platModel->class_dir;
            if(!class_exists($className)){
                throw new \Exception("充值类{$className}文件不存在");
            }
            $platClass = new $className();

            //充值记录
            $models = AddcreditOrderThirdParty::find()->where([
                "order_id" => $order->id
            ])->orderBy("id DESC")->all();
            $records = [];
            $orderStatus = null;
            if($models){
                foreach($models as &$model){
                    $order->order_no = $model->unique_order_no;
                    $res = $platClass->query2($order, $platModel);
                    if($res){
                        if($res->status == "waiting"){
                            $model->process_status = "processing";
                            if($orderStatus != "success"){
                                $orderStatus = "processing";
                            }
                        }elseif($res->status == "fail"){
                            $model->process_status = "fail";
                            if(in_array($orderStatus, ["success", "processing"])){
                                $orderStatus = "fail";
                            }
                        }elseif($res->status == "success"){
                            $model->process_status = "success";
                            $orderStatus = "success";
                        }
                        $model->updated_at = time();
                        $model->save();
                    }
                    $record = $model->getAttributes();
                    $record['result'] = $res && isset($res->response_content) ? $res->response_content : "无法查询到信息";
                    $records[] = $record;
                }
            }

            if($order->order_status != $orderStatus){
                $order->updated_at = time();
                $order->order_status = $orderStatus;
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'platInfo'    => $platModel->getAttributes(),
                    'records'     => $records ? $records : [],
                    'orderStatus' => $order->order_status,
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}