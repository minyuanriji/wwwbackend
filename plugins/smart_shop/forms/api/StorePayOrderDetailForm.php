<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\StorePayOrder;

class StorePayOrderDetailForm extends BaseModel{

    public $order_id;

    public function rules() {
        return [
            [['order_id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $detail = StorePayOrder::findOne($this->order_id);
            if(!$detail){
                throw new \Exception("数据异常，订单不存在");
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'detail' => $detail->getAttributes()
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}