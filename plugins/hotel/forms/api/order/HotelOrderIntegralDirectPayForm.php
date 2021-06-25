<?php
namespace app\plugins\hotel\forms\api\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\models\HotelOrder;

class HotelOrderIntegralDirectPayForm extends BaseModel{

    public $order_no;

    public function rules(){
        return [
            [['order_no'], 'required']
        ];
    }

    public function pay(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->db->beginTransaction();
        try {

            $hotelOrder = HotelOrder::findOne(["order_no" => $this->order_no]);
            if(!$hotelOrder){
                throw new \Exception("订单不存在");
            }

            if($hotelOrder->order_status){

            }

            $trans->commit();
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}