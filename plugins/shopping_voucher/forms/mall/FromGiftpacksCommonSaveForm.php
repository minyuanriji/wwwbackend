<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksCommonSaveForm extends BaseModel{

    public $is_open;
    public $give_type;
    public $give_value;
    public $start_at;
    public $recommender;

    public function rules(){
        return [
            [['is_open', 'give_type', 'give_value', 'start_at'], 'required'],
            [['recommender'], 'safe']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $fromGiftpacks = ShoppingVoucherFromGiftpacks::findOne(["pack_id" => 0]);
            if(!$fromGiftpacks){
                $fromGiftpacks = new ShoppingVoucherFromGiftpacks([
                    "mall_id"    => \Yii::$app->mall->id,
                    "pack_id"    => 0,
                    "created_at" => time()
                ]);
            }

            $fromGiftpacks->give_type   = (int)$this->give_type;
            $fromGiftpacks->give_value  = $this->give_type == 2 ? max(0, $this->give_value) : max(0, min(100, $this->give_value));
            $fromGiftpacks->updated_at  = time();
            $fromGiftpacks->is_delete   = $this->is_open ? 0 : 1;
            $fromGiftpacks->start_at    = max(time(), strtotime($this->start_at));
            $fromGiftpacks->recommender = @json_encode($this->recommender);
            if(!$fromGiftpacks->save()){
                throw new \Exception($this->responseErrorMsg($fromGiftpacks));
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