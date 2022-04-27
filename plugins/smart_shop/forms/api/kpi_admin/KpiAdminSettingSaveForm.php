<?php

namespace app\plugins\smart_shop\forms\api\kpi_admin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\smart_shop\models\Setting;

class KpiAdminSettingSaveForm extends BaseModel{

    public $form;

    public function rules(){
        return [
            [['form'], 'string']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $this->form = @json_decode($this->form, true);

            $setting = Setting::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "key"     => "kpi_setting"
            ]);
            if(!$setting){
                $setting = new Setting([
                    "mall_id"   => \Yii::$app->mall->id,
                    "key"       => "kpi_setting",
                    "is_delete" => 0
                ]);
            }
            $form = json_encode(is_array($this->form) ? $this->form : [], JSON_UNESCAPED_UNICODE);
            $setting->value = $form;
            if(!$setting->save()){
                throw new \Exception($this->responseErrorMsg($setting));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}