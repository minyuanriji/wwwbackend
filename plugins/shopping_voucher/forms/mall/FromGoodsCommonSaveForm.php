<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class FromGoodsCommonSaveForm extends BaseModel{

    public $is_open;
    public $give_value;
    public $start_at;

    public function rules(){
        return [
            [['is_open', 'give_value', 'start_at'], 'required']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $fromGoods = ShoppingVoucherFromGoods::findOne(["goods_id" => 0]);
            if(!$fromGoods){
                $fromGoods = new ShoppingVoucherFromGoods([
                    "mall_id"    => \Yii::$app->mall->id,
                    "goods_id"   => 0,
                    "created_at" => time()
                ]);
            }

            $fromGoods->give_type = 1;
            $fromGoods->give_value = max(0, min(100, $this->give_value));
            $fromGoods->updated_at = time();
            $fromGoods->is_delete  = $this->is_open ? 0 : 1;
            $fromGoods->start_at   = max(time(), strtotime($this->start_at));
            if(!$fromGoods->save()){
                throw new \Exception($this->responseErrorMsg($fromGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}