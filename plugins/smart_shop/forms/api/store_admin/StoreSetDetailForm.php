<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;
use app\plugins\smart_shop\models\StoreSet;

class StoreSetDetailForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required']
        ];
    }

    public function get(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $row = Merchant::find()->alias("mf")
                ->innerJoin(["mfl" => MerchantFzlist::tableName()], "mfl.bsh_mch_id=mf.bsh_mch_id")
                ->innerJoin(["m" => Mch::tableName()], "m.id=mf.bsh_mch_id")
                ->where([
                    "m.review_status" => Mch::REVIEW_STATUS_CHECKED,
                    "m.is_delete"     => 0,
                    "mf.is_delete"    => 0,
                    "mfl.is_delete"   => 0,
                    "mfl.ss_store_id" => $this->store_id
                ])->asArray()->select(["mf.bsh_mch_id"])->one();

            if(!$row){
                throw new \Exception("请先入驻补商汇平台");
            }

            $set = StoreSet::findOne([
                "bsh_mch_id"  => $row['bsh_mch_id'],
                "ss_mch_id"   => $this->merchant_id,
                "ss_store_id" => $this->store_id
            ]);
            if(!$set){
                $set = [
                    "transfer_rate" => ''
                ];
            }else{
                $set = $set->getAttributes();
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "setting" => $set
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }

}