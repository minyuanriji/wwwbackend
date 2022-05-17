<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;

class AccountRechargeSubmitForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $recharge_money;

    public function rules(){
        return [
            [['merchant_id', 'store_id', 'recharge_money'], 'required'],
            [['recharge_money'], 'number']
        ];
    }

    public function submit(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if($this->recharge_money <= 0){
                throw new \Exception("充值金额不能小于0");
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [

            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}