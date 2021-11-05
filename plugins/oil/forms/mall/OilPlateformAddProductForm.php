<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;

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

            $product = OilProduct::findOne([
                "plat_id" => $plateform->id,
                "name"    => $this->product_key
            ]);
            if(!$product){
                $product = new OilProduct([
                    "mall_id"    => $plateform->mall_id,
                    "plat_id"    => $plateform->id,
                    "name"       => $this->product_key,
                    "created_at" => time()
                ]);
            }
            $product->price      = $this->product_price;
            $product->sort       = $this->sort;
            $product->updated_at = time();
            $product->status     = 1;
            $product->is_delete  = 0;

            if (!$product->save()){
                throw new \Exception($product->getErrorMessage());
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '添加成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }
}