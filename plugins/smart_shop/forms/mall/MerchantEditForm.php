<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;

class MerchantEditForm extends BaseModel{

    public $shop_list;
    public $bsh_mch_id;

    public function rules(){
        return [
            [['bsh_mch_id'], 'required'],
            [['bsh_mch_id'], 'integer'],
            [['shop_list'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            if(!$this->shop_list){
                throw new \Exception("请添加要绑定的智慧门店");
            }

            $merchant = Merchant::findOne(["bsh_mch_id" => $this->bsh_mch_id]);
            if(!$merchant){
                $merchant = new Merchant([
                    "mall_id"    => \Yii::$app->mall->id,
                    "bsh_mch_id" => $this->bsh_mch_id,
                    "created_at" => time()
                ]);
            }
            $merchant->updated_at = time();
            $merchant->is_delete  = 0;
            if(!$merchant->save()){
                throw new \Exception($this->responseErrorMsg($merchant));
            }

            MerchantFzlist::updateAll(["is_delete" => 1], ["bsh_mch_id" => $merchant->bsh_mch_id]);

            foreach($this->shop_list as $shop){
                $model = MerchantFzlist::findOne([
                    "bsh_mch_id"  => $merchant->bsh_mch_id,
                    "ss_mch_id"   => $shop['ss_mch_id'],
                    "ss_store_id" => $shop['ss_store_id']
                ]);
                if(!$model){
                    $model = new MerchantFzlist([
                        "mall_id"     => $merchant->mall_id,
                        "bsh_mch_id"  => $merchant->bsh_mch_id,
                        "ss_mch_id"   => $shop['ss_mch_id'],
                        "ss_store_id" => $shop['ss_store_id']
                    ]);
                }
                $model->name      = $shop['name'];
                $model->logo      = $shop['logo'];
                $model->mobile    = $shop['mobile'];
                $model->address   = $shop['address'];
                $model->is_delete = 0;
                if(!$model->save()){
                    throw new \Exception($this->responseErrorMsg($model));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,

            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}