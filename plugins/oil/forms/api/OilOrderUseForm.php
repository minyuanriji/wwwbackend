<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilJiayoulaTransferOrder;
use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilOrderUseForm extends BaseModel{

    public $id;
    public $use_province;

    public function rules(){
        return [
            [['id', 'use_province'], 'required']
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

                $provinces = ["2088" => "广西", "1941" => "广东"];
                if(!isset($provinces[$this->use_province])){
                    throw new \Exception("暂只持广西、广东地区进行加油");
                }
                if($platModel->province_id != $this->use_province){
                    $platModel->province_id = $this->use_province;
                    $platModel->province    = $provinces[$this->use_province];
                    $platModel->city_id     = 0;
                    $platModel->city        = "";
                    $platModel->district_id = "";
                    $platModel->address     = $provinces[$this->use_province];
                    if(!$platModel->save()){
                        throw new \Exception($this->responseErrorMsg($platModel));
                    }
                }

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

                //生成打款记录
                $config = $platModel->getParams();
                $transferRate = isset($config['transferRate']) ? max(8, intval($config['transferRate'])) : 8;
                $amount = ((100 - $transferRate)/100) * floatval($order->order_price);
                $transferOrder = new OilJiayoulaTransferOrder([
                    "mall_id"         => $platModel->mall_id,
                    "order_sn"        => "JYL" . date("ymdHis") . rand(10000, 99999),
                    "created_at"      => time(),
                    "updated_at"      => time(),
                    "status"          => "wait",
                    "amount"          => round($amount, 2),
                    "originAmount"    => $order->order_price,
                    "transferRate"    => $transferRate,
                    "bankUserName"    => isset($config['bankUserName']) ? $config['bankUserName'] : "",
                    "bankCardNo"      => isset($config['bankCardNo']) ? $config['bankCardNo'] : "",
                    "bankName"        => isset($config['bankName']) ? $config['bankName'] : "",
                    "bankAccountType" => 2
                ]);
                if(!$transferOrder->save()){
                    throw new \Exception($this->responseErrorMsg($transferOrder));
                }
            }

            $t->commit();

            $config = $platModel->getParams();
            $responseData = json_decode($order->plat_response_data, true);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "mobile"      => $order->mobile,
                    "couponCode"  => $responseData['data']['couponCode'],
                    "mpwx_path"   => isset($config['mpwx_path']) ? $config['mpwx_path'] : "",
                    "mpwx_app_id" => isset($config['mpwx_app_id']) ? $config['mpwx_app_id'] : "",
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