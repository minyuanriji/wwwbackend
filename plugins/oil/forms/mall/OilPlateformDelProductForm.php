<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

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

            $products = !empty($plateform->product_json_data) ? json_decode($plateform->product_json_data, true) : [];
            $newProduct = [];
            foreach($products as $product){
                if($product['product_key'] != $this->product_key){
                    $newProduct[] = $product;
                }
            }

            $plateform->product_json_data = json_encode($newProduct);
            $plateform->updated_at = time();
            if (!$plateform->save()){
                throw new \Exception($plateform->getErrorMessage());
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '操作成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }
}