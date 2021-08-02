<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\GiftpacksItem;

class GiftpacksDeleteItemForm extends BaseModel {

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $item = GiftpacksItem::findOne($this->id);
            if(!$item || $item->is_delete){
                throw new \Exception("商品不存在");
            }

            $item->is_delete = 1;
            if(!$item->save()){
                throw new \Exception($this->responseErrorMsg($item));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }


    }
}