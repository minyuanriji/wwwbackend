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
        $responseData = $responseData2 = [];

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

            //获取平台操作对象
            if(empty($platModel->class_dir) || !class_exists($platModel->class_dir)){
                throw new \Exception("类{$platModel->class_dir}丢失");
            }
            $platObj = new $platModel->class_dir($platModel);

            if($status['status'] == "unconfirmed"){ //生成兑换码
                $provinces = ["2088" => "广西", "1941" => "广东"];
                if(!isset($provinces[$this->use_province])){
                    throw new \Exception("暂只持广西、广东地区进行加油");
                }
                if($order->province_id != $this->use_province){
                    $order->province_id = $this->use_province;
                    $order->province    = $provinces[$this->use_province];
                    $order->city_id     = -1;
                    $order->city        = "-1";
                    $order->district_id = "-1";
                    $order->address     = $provinces[$this->use_province];
                    if(!$order->save()){
                        throw new \Exception($this->responseErrorMsg($order));
                    }
                }

                $res = $platObj->submit($order, $product);
                //$res = '{"code":0,"message":"\u6210\u529f","data":{"orderNo":"OIL202112131004581143910570","couponCode":"790176"},"requestId":"ac10ee02163679823918141651"}';
                if($res){
                    $responseData = @json_decode($res, true);
                }

                $data = isset($responseData['data']) ? $responseData['data'] : [];
                if(!isset($data['couponCode']) || empty($data['couponCode'])){
                    throw new \Exception("数据请求失败！请联系客服进行处理");
                }

                $order->order_status       = "wait"; //状态变更为待使用（已生成兑换码）
                $order->updated_at         = time();
                $order->plat_response_data = json_encode($responseData);
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }
            }elseif($status['status'] == "wait"){

                $responseData = json_decode($order->plat_response_data, true);

                $res = $platObj->exchange($order, $product);
                $responseData2 = $res ? @json_decode($res, true) : [];
                $responseData['exchange'] = $responseData2;

                if(!isset($responseData2['code']) || $responseData2['code'] != 0){
                    throw new \Exception(isset($responseData2['message']) ? $responseData2['message'] : "兑换失败");
                }

                $config = $platModel->getParams();

                //计算出服务费
                $transferRate = isset($config['transferRate']) ? max(8, intval($config['transferRate'])) : 8;
                $transferAmount = round(((100 - $transferRate)/100) * floatval($order->order_price), 2);

                $order->order_status       = "finished"; //状态变更为已完成
                $order->updated_at         = time();
                $order->plat_response_data = json_encode($responseData);
                $order->transfer_rate      = $transferRate;
                $order->transfer_amount    = $transferAmount;
                if(!$order->save()){
                    throw new \Exception($this->responseErrorMsg($order));
                }

                //生成打款记录
                $transferOrder = OilJiayoulaTransferOrder::findOne(["oil_order_id" => $order->id]);
                if(!$transferOrder){
                    $transferOrder = new OilJiayoulaTransferOrder([
                        "mall_id"         => $platModel->mall_id,
                        "oil_order_id"    => $order->id,
                        "order_sn"        => "JYL_" . $order->order_no,
                        "created_at"      => time(),
                        "updated_at"      => time(),
                        "status"          => "wait",
                        "amount"          => $transferAmount,
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
            }

            $t->commit();

            //兑换码获取
            $couponCode = "";
            $responseData = json_decode($order->plat_response_data, true);
            if(isset($responseData['data'])){
                $couponCode = isset($responseData['data']['couponCode']) ? $responseData['data']['couponCode'] : "";
            }

            //平台配置信息
            $config = $platModel->getParams();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "order_status" => $order->order_status,
                    "province_id"  => $order->province_id,
                    "mobile"       => $order->mobile,
                    "couponCode"   => $couponCode,
                    "mpwx_path"    => isset($config['mpwx_path']) ? $config['mpwx_path'] : "",
                    "mpwx_app_id"  => isset($config['mpwx_app_id']) ? $config['mpwx_app_id'] : "",
                    "mpwx_pic"     => isset($config['mpwx_pic']) ? $config['mpwx_pic'] : ""
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