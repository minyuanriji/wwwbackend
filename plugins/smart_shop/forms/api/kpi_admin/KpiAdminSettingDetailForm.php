<?php

namespace app\plugins\smart_shop\forms\api\kpi_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\Setting;

class KpiAdminSettingDetailForm extends BaseModel{

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $detail = [];
            $setting = Setting::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "key"     => "kpi_setting"
            ]);
            if($setting){
                $detail = $setting->value ? @json_decode($setting->value, true) : [];
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                "detail" => $detail ? $detail : []
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}