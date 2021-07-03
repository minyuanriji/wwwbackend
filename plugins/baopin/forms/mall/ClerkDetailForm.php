<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\baopin\models\BaopinMchClerkOrder;
use app\plugins\group_buy\models\Order;

class ClerkDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $result = [];

            $mchClerk = BaopinMchClerkOrder::findOne($this->id);
            if(!$mchClerk || $mchClerk->is_delete){
                throw new \Exception("无法获取商户爆品核销记录");
            }

            $store = Store::findOne($mchClerk->store_id);
            if(!$store){
                throw new \Exception("无法获取门店信息");
            }

            $result['store'] = ArrayHelper::toArray($store);

            $order = Order::findOne($mchClerk->order_id);
            if(!$order){
                throw new \Exception("无法获取订单信息");
            }

            $result['order'] = ArrayHelper::toArray($order);
            $result['details'] = [];

            $details = $order->detail;
            if($details){
                foreach($details as $detail){
                    $goodsInfo = @json_decode($detail->goods_info, true);
                    $detail->goods_info = $goodsInfo;
                    $result['details'][] = ArrayHelper::toArray($detail);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result
            ];
        }catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}