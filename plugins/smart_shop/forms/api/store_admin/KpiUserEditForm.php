<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\helpers\MobileHelper;
use app\models\BaseModel;
use app\plugins\smart_shop\models\KpiUser;

class KpiUserEditForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $realname;
    public $mobile;

    public function rules() {
        return [
            [['merchant_id', 'store_id', 'realname', 'mobile'], 'required'],
            [['realname', 'mobile'], 'trim']
        ];
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if(!MobileHelper::isMobile($this->mobile)){
                throw new \Exception("手机号码格式有误");
            }

            $uniqueData = [
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "mobile"       => $this->mobile
            ];
            $kpiUser = KpiUser::findOne($uniqueData);
            if(!$kpiUser){
                $kpiUser = new KpiUser(array_merge($uniqueData, [
                    "created_at" => time()
                ]));
            }
            $kpiUser->updated_at = time();
            $kpiUser->realname   = $this->realname;
            $kpiUser->is_delete  = 0;
            if(!$kpiUser->save()){
                throw new \Exception($this->responseErrorMsg($kpiUser));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}