<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromGiftpacksSaveForm extends BaseModel{

    public $id;
    public $mch_id;
    public $store_id;
    public $give_type;
    public $give_value;
    public $name;
    public $cover_url;
    public $start_at;

    public function rules(){
        return [
            [['mch_id', 'store_id', 'give_type', 'give_value', 'name', 'cover_url'], 'required'],
            [['id', 'mch_id', 'store_id'], 'integer'],
            [['start_at'], 'string']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $fromStore = ShoppingVoucherFromStore::findOne($this->id);
            if(!$fromStore){

                $exists = ShoppingVoucherFromStore::findOne([
                    "store_id" => $this->store_id
                ]);
                if($exists && !$exists->is_delete){
                    throw new \Exception("已添加过该门店了");
                }

                if(!$exists){
                    $fromStore = new ShoppingVoucherFromStore([
                        "mall_id"    => \Yii::$app->mall->id,
                        "created_at" => time()
                    ]);
                }else{
                    $fromStore = $exists;
                    $fromStore->is_delete = 0;
                    $fromStore->deleted_at = 0;
                }

            }

            $fromStore->mch_id     = $this->mch_id;
            $fromStore->store_id   = $this->store_id;
            $fromStore->give_type  = 1;
            $fromStore->give_value = max(min($this->give_value, 100), 0);
            $fromStore->updated_at = time();
            $fromStore->name       = $this->name;
            $fromStore->cover_url  = $this->cover_url;
            $fromStore->start_at   = strtotime($this->start_at);

            if(!$fromStore->save()){
                throw new \Exception($this->responseErrorMsg($fromStore));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}