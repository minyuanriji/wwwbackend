<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

class OilPlateformAddProductForm extends BaseModel {

    public $plateform_id;
    public $product_key;
    public $product_price;
    public $sort;

    public function rules(){
        return [
            [['plateform_id', 'product_key', 'product_price', 'sort'], 'required']
        ];
    }

    public function addProduct() {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $plateform = OilPlateforms::findOne($this->plateform_id);
            if (!$plateform){
                throw new \Exception("平台[ID:{$this->plateform_id}]不存在");
            }

            $product = !empty($plateform->product_json_data) ? json_decode($plateform->product_json_data, true) : [];
            $product[] = [
                "product_key"   => $this->product_key,
                "product_price" => $this->product_price,
                "sort"          => $this->sort
            ];

            $plateform->product_json_data = json_encode($product);
            $plateform->updated_at = time();
            if (!$plateform->save()){
                throw new \Exception($plateform->getErrorMessage());
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '添加成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }
}