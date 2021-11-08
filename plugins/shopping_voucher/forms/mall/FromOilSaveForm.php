<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilSaveForm extends BaseModel{

    public $plat_id;
    public $product_id;
    public $first_give_type;
    public $first_give_value;
    public $second_give_type;
    public $second_give_value;
    public $start_at;


    public function rules(){
        return [
            [['plat_id', 'product_id', 'first_give_type', 'first_give_value', 'second_give_type', 'second_give_value', 'start_at'], 'required'],
        ];
    }

    public function save(){

        if(!$this->validate())
            return $this->responseErrorInfo();

        try {
            $fromOil = ShoppingVoucherFromOil::findOne([
                'plat_id'    => $this->plat_id,
                'product_id' => $this->product_id
            ]);
            if(!$fromOil){
                $fromOil = new ShoppingVoucherFromOil([
                    'plat_id'    => $this->plat_id,
                    'product_id' => $this->product_id
                ]);
            }

            $fromOil->first_give_type   = $this->first_give_type;
            $fromOil->first_give_value  = max(min($this->first_give_value, 100), 0);
            $fromOil->second_give_type  = $this->second_give_type;
            $fromOil->second_give_value = max(min($this->second_give_value, 100), 0);
            $fromOil->updated_at        = time();
            $fromOil->start_at          = max(time(), strtotime($this->start_at));

            if(!$fromOil->save()){
                throw new \Exception($this->responseErrorMsg($fromOil));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}