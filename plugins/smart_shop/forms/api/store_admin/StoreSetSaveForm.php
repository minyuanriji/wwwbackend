<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;
use app\plugins\smart_shop\models\StoreSet;

class StoreSetSaveForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $transfer_rate;
    public $enable_shopping_voucher;
    public $shopping_voucher_rate;
    public $enable_score;

    public function rules()
    {
        return [
            [['merchant_id', 'store_id'], 'required'],
            [['enable_shopping_voucher', 'enable_score', 'shopping_voucher_rate'], 'integer'],
            [['transfer_rate'], 'number']
        ];
    }

    public function save(){

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
                ])->asArray()->select(["m.mall_id", "mf.bsh_mch_id"])->one();

            if(!$row){
                throw new \Exception("您还未入驻补商汇平台");
            }

            $set = StoreSet::findOne([
                "bsh_mch_id"  => $row['bsh_mch_id'],
                "ss_mch_id"   => $this->merchant_id,
                "ss_store_id" => $this->store_id
            ]);
            if(!$set){
                $set = new StoreSet([
                    "mall_id"     => $row['mall_id'],
                    "bsh_mch_id"  => $row['bsh_mch_id'],
                    "ss_mch_id"   => $this->merchant_id,
                    "ss_store_id" => $this->store_id,
                    "created_at"  => time()
                ]);
            }
            $set->updated_at              = time();
            $set->transfer_rate           = $this->transfer_rate;
            $set->enable_shopping_voucher = $this->enable_shopping_voucher;
            $set->shopping_voucher_rate   = $this->shopping_voucher_rate;
            $set->enable_score            = $this->enable_score;
            if(!$set->save()){
                throw new \Exception($this->responseErrorMsg($set));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}