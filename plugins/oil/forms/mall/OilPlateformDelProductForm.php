<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

class OilPlateformDelProductForm extends BaseModel {

    public $plateform_id;
    public $product_key;

    public function rules(){
        return [
            [['plateform_id', 'product_key'], 'required']
        ];
    }

    public function delProduct() {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $plateform = OilPlateforms::findOne($this->plateform_id);
            if (!$plateform){
                throw new \Exception("平台[ID:{$this->plateform_id}]不存在");
            }

            $product = OilProduct::findOne([
                "plat_id" => $plateform->id,
                "name"    => $this->product_key
            ]);
            if(!$product){
                throw new \Exception("产品不存在[ID:{$this->product_key}]");
            }

            $product->is_delete  = 1;
            $product->updated_at = time();
            if (!$product->save()){
                throw new \Exception($product->getErrorMessage());
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '操作成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }
}