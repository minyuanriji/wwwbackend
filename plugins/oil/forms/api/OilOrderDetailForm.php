<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilOrders;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilOrderDetailForm extends BaseModel{

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

            $order = OilOrders::findOne($this->id);
            if(!$order || $order->pay_status != "paid"){
                throw new \Exception("订单不存在或未支付");
            }

            $product = OilProduct::findOne($order->product_id);
            if(!$product){
                throw new \Exception("订单产品[ID:{$order->product_id}]信息不存在");
            }

            $platModel = OilPlateforms::findOne($product->plat_id);
            if(!$platModel || $platModel->is_delete || !$platModel->is_enabled){
                throw new \Exception("平台[ID:{$product->plat_id}信息不存在");
            }
            $config = $platModel->getParams();
            $responseData = json_decode($order->plat_response_data, true);
            $couponCode = "";
            if(isset($responseData['data']) && !empty($responseData['data']['couponCode'])){
                $couponCode = $responseData['data']['couponCode'];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "order_status" => $order->order_status,
                    "mobile"       => $order->mobile,
                    "couponCode"   => $couponCode,
                    "mpwx_path"    => isset($config['mpwx_path']) ? $config['mpwx_path'] : "",
                    "mpwx_app_id"  => isset($config['mpwx_app_id']) ? $config['mpwx_app_id'] : "",
                    "mpwx_pic"     => isset($config['mpwx_pic']) ? $config['mpwx_pic'] : ""
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