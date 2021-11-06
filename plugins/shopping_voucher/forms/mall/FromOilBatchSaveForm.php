<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilBatchSaveForm extends BaseModel {

    public $plat_id;
    public $first_give_type;
    public $first_give_value;
    public $second_give_type;
    public $second_give_value;
    public $start_at;

    public function rules(){
        return [
            [[ 'plat_id', 'first_give_type', 'first_give_value', 'second_give_type', 'second_give_value', 'start_at'], 'required']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $platModel = OilPlateforms::findOne($this->plat_id);
            if(!$platModel || $platModel->is_delete){
                throw new \Exception("平台[ID:{$this->plat_id}]不存在");
            }

            $fromOil = ShoppingVoucherFromOil::findOne([
                "plat_id"    => $this->plat_id,
                "product_id" => 0
            ]);
            if(!$fromOil){
                $fromOil = new ShoppingVoucherFromOil([
                    "mall_id"    => \Yii::$app->mall->id,
                    "plat_id"    => $this->plat_id,
                    "product_id" => 0,
                    "created_at" => time()
                ]);
            }

            $fromOil->first_give_type    = (int)$this->first_give_type;
            $fromOil->first_give_value   = $this->first_give_type == 2 ? max(0, $this->first_give_value) : max(0, min(100, $this->first_give_value));
            $fromOil->second_give_type   = (int)$this->second_give_type;
            $fromOil->second_give_value  = $this->second_give_type == 2 ? max(0, $this->second_give_value) : max(0, min(100, $this->second_give_value));
            $fromOil->updated_at         = time();
            $fromOil->is_delete          = 0;
            $fromOil->start_at           = max(time(), strtotime($this->start_at));
            if(!$fromOil->save()){
                throw new \Exception($this->responseErrorMsg($fromOil));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }

    }
}