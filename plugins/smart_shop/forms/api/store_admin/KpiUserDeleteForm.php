<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiUser;

class KpiUserDeleteForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $id;

    public function rules() {
        return [
            [['merchant_id', 'store_id', 'id'], 'required'],
        ];
    }

    public function delete(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $kpiUser = KpiUser::findOne([
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "id"           => $this->id,
            ]);
            if(!$kpiUser){
                throw new \Exception("推广员信息不存在");
            }

            $kpiUser->is_delete = 1;
            if(!$kpiUser->save()){
                throw new \Exception($this->responseErrorMsg($kpiUser));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}