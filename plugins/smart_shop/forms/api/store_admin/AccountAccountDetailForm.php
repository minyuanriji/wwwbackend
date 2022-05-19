<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\StoreAccount;

class AccountAccountDetailForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required'],
            [[], 'number']
        ];
    }

    public function getDetail(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            //门店账户
            $account = StoreAccount::findOne([
                "mall_id"     => \Yii::$app->mall->id,
                "ss_mch_id"   => $this->merchant_id,
                "ss_store_id" => $this->store_id
            ]);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "balance" => $account ? $account->balance : 0,
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}