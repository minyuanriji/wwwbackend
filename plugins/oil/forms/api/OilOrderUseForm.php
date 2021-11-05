<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilOrderUseForm extends BaseModel{
    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function toUse(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $order = OilOrders::findOne($this->id);
        $responseData = [];

        $t = \Yii::$app->db->beginTransaction();
        try {


            if(!$order || $order->pay_status != "paid"){
                throw new \Exception("订单不存在或未支付");
            }

            $status = OilOrders::getStatusInfo($order->order_status, $order->pay_status, $order->created_at);
            if(!in_array($status['status'], ["unconfirmed", "wait", "finished"])){
                throw new \Exception("订单[ID:{$order->id}]状态异常 {$status}");
            }

            $product = OilProduct::findOne($order->product_id);
            if(!$product){
                throw new \Exception("订单产品[ID:{$order->product_id}]信息不存在");
            }

            $platModel = OilPlateforms::findOne($product->plat_id);
            if(!$platModel || $platModel->is_delete || !$platModel->is_enabled){
                throw new \Exception("平台[ID:{$product->plat_id}信息不存在");
            }

            if(in_array($status['status'], ["unconfirmed", "wait"])){

                if(empty($platModel->class_dir) || !class_exists($platModel->class_dir)){
                    throw new \Exception("类{$platModel->class_dir}丢失");
                }

                $platObj = new $platModel->class_dir($platModel);
                $res = $platObj->submit($order, $product);
                if($res){
                    $responseData = @json_decode($res, true);
                }

                $data = isset($responseData['data']) ? $responseData['data'] : [];
                if(!isset($data['couponCode']) || empty($data['couponCode'])){
                    throw new \Exception("数据请求失败！请联系客服进行处理");
                }

                $order->order_status       = "finished";
                $order->updated_at         = time();
                $order->plat_response_data = json_encode($responseData);
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }
            }

            $t->commit();

            $config = $platModel->getParams();
            $responseData = json_decode($order->plat_response_data, true);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "couponCode"  => $responseData['data']['couponCode'],
                    "mpwx_path"   => isset($config['mpwx_path']) ? $config['mpwx_path'] : "",
                    "mpwx_app_id" => isset($config['appId']) ? $config['appId'] : "",
                    "mpwx_pic"    => isset($config['mpwx_pic']) ? $config['mpwx_pic'] : ""
                ]
            ];
        }catch (\Exception $e){
            $t->rollBack();

            $order->plat_response_data = json_encode($responseData);
            $order->updated_at         = time();
            $order->save();

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}