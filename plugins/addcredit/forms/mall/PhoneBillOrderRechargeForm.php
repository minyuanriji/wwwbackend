<?php

namespace app\plugins\addcredit\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditOrderThirdParty;
use app\plugins\addcredit\models\AddcreditPlateforms;

class PhoneBillOrderRechargeForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function doRecharge(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $order = AddcreditOrder::findOne($this->id);
            if(!$order){
                throw new \Exception("订单不存在");
            }

            if($order->pay_status != "paid"){
                throw new \Exception("订单未支付或退款中");
            }

            if($order->is_manual){
                throw new \Exception("订单已通过手动充值过一次了");
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

            //充值记录，仍在充值中的无法操作
            $models = AddcreditOrderThirdParty::find()->where([
                "order_id" => $order->id
            ])->orderBy("id DESC")->all();
            if($models){
                foreach($models as &$model){
                    $order->order_no = $model->unique_order_no;
                    $res = $platClass->query2($order, $platModel);
                    if($res){
                        if($res->status == "waiting"){
                            $model->process_status = "processing";
                        }elseif($res->status == "fail"){
                            $model->process_status = "fail";
                        }elseif($res->status == "success"){
                            $model->process_status = "success";
                        }
                        $model->updated_at = time();
                        $model->save();
                    }
                    if(in_array($model->process_status, ["success", "processing"])){
                        throw new \Exception("无法操作！该笔订单已成功或仍在充值中");
                    }
                }
            }

            $order->is_manual  = 1;
            $order->updated_at = time();
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            //生成一条新的充值记录
            /*$orderNo = substr(md5(uniqid()), -4) . date("ymdhis") . rand(100000, 999999);
            $model = new AddcreditOrderThirdParty([
                "mall_id"         => $order->mall_id,
                "order_id"        => $order->id,
                "process_status"  => "processing",
                "unique_order_no" => $orderNo,
                "created_at"      => (time() + 15 * 60)
            ]);
            if (!$model->save()) {
                throw new \Exception(json_encode($model->getErrors()));
            }
            $order->order_no = $orderNo;
            $res = $platClass->submit($order, $platModel, false);
            if(!$res){
                throw new \Exception("充值失败！");
            }

            if($res && isset($res->response_content)){
                //保存请求数据、返回数据
                $order->plateform_request_data  = $res->request_data;
                $order->plateform_response_data = $res->response_content;
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }
            }*/

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}