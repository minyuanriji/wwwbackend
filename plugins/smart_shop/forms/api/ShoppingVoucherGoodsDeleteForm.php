<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\AlibabaShoppingVoucherGoods;

class ShoppingVoucherGoodsDeleteForm extends BaseModel{

    public $id;
    public $token;
    public $ss_store_id;

    public function rules(){
        return [
            [['id', 'ss_store_id', 'token'], 'required'],
            [['token'], 'trim'],
            [['ss_store_id', 'id'], 'integer']
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $smartShop = new SmartShop();
            if(!$smartShop->validateToken($this->token, $admin, $merchant, $store)){
                throw new \Exception("无权限操作");
            }

            $model = AlibabaShoppingVoucherGoods::findOne([
                "alibaba_goods_id" => $this->id,
                "ss_store_id"      => $this->ss_store_id
            ]);
            if(!$model || $model->is_delete){
                throw new \Exception("商品不存在");
            }

            $model->is_delete = 1;
            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}