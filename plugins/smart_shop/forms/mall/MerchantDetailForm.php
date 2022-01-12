<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;

class MerchantDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $merchant = Merchant::findOne($this->id);
            if(!$merchant || $merchant->is_delete){
                throw new \Exception("记录[ID:{$this->id}]不存在");
            }

            $mch = Mch::findOne($merchant->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户[ID:{$merchant->bsh_mch_id}]不存在");
            }

            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store || $store->is_delete){
                throw new \Exception("商户[ID:{$merchant->bsh_mch_id}]数据异常");
            }

            $shopList = MerchantFzlist::find()->where([
                "bsh_mch_id" => $merchant->bsh_mch_id,
                "is_delete"  => 0
            ])->asArray()->orderBy("id DESC")->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'merchant' => $merchant->getAttributes(),
                    'mch'      => $mch->getAttributes(),
                    'store'    => $store->getAttributes(),
                    'shopList' => $shopList ? $shopList : []
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}