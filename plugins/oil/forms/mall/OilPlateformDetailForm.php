<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

class OilPlateformDetailForm extends BaseModel{

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
            $plateform = OilPlateforms::findOne($this->id);
            if(!$plateform){
                throw new \Exception("平台不存在");
            }

            $detail = $plateform->getAttributes();
            $detail['region_deny'] = !empty($plateform->region_deny) ? json_decode($plateform->region_deny, true) : [];
            $detail['products'] = !empty($detail['product_json_data']) ? json_decode($detail['product_json_data'], true) : [];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $detail
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => '请求失败',
            ];
        }
    }

}