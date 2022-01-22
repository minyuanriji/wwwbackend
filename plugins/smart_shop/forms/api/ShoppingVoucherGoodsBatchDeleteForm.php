<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\AlibabaShoppingVoucherGoods;

class ShoppingVoucherGoodsBatchDeleteForm extends BaseModel{

    public $id;
    public $token;
    public $ss_store_id;

    public function rules(){
        return [
            [['id', 'ss_store_id', 'token'], 'required'],
            [['token'], 'trim'],
            [['ss_store_id'], 'integer']
        ];
    }

    public function delete(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            $smartShop = new SmartShop();
            if(!$smartShop->validateToken($this->token)){
                throw new \Exception("无权限操作");
            }

            $idArr = [];
            foreach($this->id as $id){
                $idArr[] = (int)$id;
            }
            AlibabaShoppingVoucherGoods::updateAll([
                "is_delete" => 1
            ], "ss_store_id='".$this->ss_store_id."' AND alibaba_goods_id IN(".implode(",", $idArr).")");

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}